param([string]$MySqlBin = '', [switch]$SeedDevelopment, [switch]$FinanceValidation)
$ErrorActionPreference = 'Stop'
$projectRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '../..'))
if (!$MySqlBin) { $MySqlBin = Split-Path (Get-Command mysqld.exe -ErrorAction Stop).Source }
$serverBinary = Join-Path $MySqlBin 'mysqld.exe'
$dumpBinary = Join-Path $MySqlBin 'mysqldump.exe'
$adminBinary = Join-Path $MySqlBin 'mysqladmin.exe'
$runPath = Join-Path $projectRoot ('storage/app/diagnostics/mysql-' + [Guid]::NewGuid().ToString('N'))
$dataPath = Join-Path $runPath 'data'
New-Item -ItemType Directory -Path $dataPath -Force | Out-Null
$listener = [Net.Sockets.TcpListener]::new([Net.IPAddress]::Loopback, 0)
$listener.Start()
$port = $listener.LocalEndpoint.Port
$listener.Stop()
$process = $null
Push-Location $projectRoot
try {
    Write-Output 'Initializing a new disposable MySQL instance (synthetic data only).'
    & $serverBinary --no-defaults --initialize-insecure "--datadir=$dataPath" "--log-error=$runPath/initialize.log"
    if ($LASTEXITCODE -ne 0) { throw "Initialization failed; inspect $runPath/initialize.log" }
    $arguments = @('--no-defaults', '--bind-address=127.0.0.1', "--port=$port", '--mysqlx=OFF',
        '--skip-log-bin', ('--datadir="' + $dataPath + '"'), ('--log-error="' + $runPath + '/server.log"'))
    $process = Start-Process -FilePath $serverBinary -ArgumentList $arguments -WindowStyle Hidden -PassThru
    $ready = $false
    for ($attempt = 0; $attempt -lt 100; $attempt++) {
        if ($process.HasExited) { throw "Dedicated MySQL exited; inspect $runPath/server.log" }
        $client = [Net.Sockets.TcpClient]::new()
        try { $client.Connect('127.0.0.1', $port); $ready = $true } catch {} finally { $client.Dispose() }
        if ($ready) { break }
        Start-Sleep -Milliseconds 200
    }
    if (!$ready) { throw 'Dedicated MySQL did not start in 20 seconds.' }
    # Each PHP entry point verifies @@datadir before any database write.
    if ($FinanceValidation) {
        & php tools/diagnostics/verify-financial-concurrency.php "--mysql-port=$port" "--mysql-datadir=$dataPath"
        if ($LASTEXITCODE -ne 0) { throw 'Financial concurrency verification failed.' }
    } elseif ($SeedDevelopment) {
        & php tools/diagnostics/verify-development-seed.php "--mysql-port=$port" "--mysql-datadir=$dataPath"
        if ($LASTEXITCODE -ne 0) { throw 'Development seed verification failed.' }
    } else {
    & php tools/diagnostics/baseline.php "--mysql-port=$port" "--mysql-datadir=$dataPath"
    if ($LASTEXITCODE -ne 0) { throw 'MySQL baseline failed.' }
    $backupPath = Join-Path $runPath 'synthetic-backup.sql'
    & $dumpBinary --no-defaults --host=127.0.0.1 "--port=$port" --user=root --single-transaction --routines --events --triggers --set-gtid-purged=OFF "--result-file=$backupPath" credisoft_diagnostic
    if ($LASTEXITCODE -ne 0) { throw 'Synthetic backup failed.' }
    & php tools/diagnostics/restore-check.php "--mysql-port=$port" "--mysql-datadir=$dataPath" "--backup=$backupPath"
    if ($LASTEXITCODE -ne 0) { throw 'Synthetic restore verification failed.' }
    Write-Output 'Baseline and synthetic backup/restore completed.'
    }
} finally {
    if ($process -and !$process.HasExited) {
        # Only the newly spawned instance is stopped; never stop the Laragon service.
        try { & $adminBinary --no-defaults --host=127.0.0.1 "--port=$port" --user=root shutdown 2>$null }
        finally {
            if (!$process.WaitForExit(10000)) { Stop-Process -Id $process.Id -ErrorAction SilentlyContinue }
        }
    }
    # Keep synthetic dump/logs; remove only this run's own stopped server data files.
    $resolvedData = [IO.Path]::GetFullPath($dataPath)
    $resolvedRun = [IO.Path]::GetFullPath($runPath)
    $allowedRoot = [IO.Path]::GetFullPath((Join-Path $projectRoot 'storage/app/diagnostics'))
    if ($process -and $process.HasExited -and
        $resolvedRun.StartsWith($allowedRoot + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase) -and
        $resolvedData.Equals((Join-Path $resolvedRun 'data'), [StringComparison]::OrdinalIgnoreCase)) {
        Remove-Item -LiteralPath $resolvedData -Recurse -Force
    }
    Pop-Location
}
