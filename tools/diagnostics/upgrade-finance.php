<?php

// Incremental local upgrade. Never drops tables or seeds existing business data.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $e) { fwrite(STDERR, $e->getMessage().PHP_EOL); exit(1); });
if (!$app->environment('local')) throw new RuntimeException('This helper only supports the local development environment.');
$db = Illuminate\Support\Facades\DB::connection();
$config = $db->getConfig();
if ($config['driver'] !== 'mysql' || !in_array($config['host'], ['127.0.0.1', 'localhost'], true)) throw new RuntimeException('Expected local MySQL.');
$snapshot = static function () {
    $result = [];
    foreach (['boveda' => 'saldo_actual', 'caja' => 'monto_inicial', 'pago' => 'monto_pago', 'ingreso' => 'monto', 'egreso' => 'monto', 'desembolso' => 'monto', 'pago_administrativo' => 'monto'] as $table => $field) {
        $result[$table] = Illuminate\Support\Facades\DB::table($table)->select('id', $field)->orderBy('id')->get()->toArray();
    }
    return json_encode($result, JSON_THROW_ON_ERROR);
};
$before = $snapshot();
$directory = storage_path('app/diagnostics/finance-upgrade-'.date('Ymd-His'));
if (!mkdir($directory, 0700, true)) throw new RuntimeException('Cannot create backup directory.');
$backup = $directory.'/before.sql';
$args = ['mysqldump', '--no-defaults', '--host='.$config['host'], '--port='.($config['port'] ?? 3306), '--user='.$config['username'],
    '--single-transaction', '--routines', '--events', '--triggers', '--set-gtid-purged=OFF', '--result-file='.$backup, $config['database']];
$environment = getenv();
$environment['MYSQL_PWD'] = $config['password'];
$pipes = [];
$process = proc_open($args, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', $directory.'/backup.log', 'a']], $pipes, base_path(), $environment);
if (!is_resource($process)) throw new RuntimeException('Cannot start backup.');
fclose($pipes[0]); stream_get_contents($pipes[1]); fclose($pipes[1]);
if (proc_close($process) !== 0 || !is_file($backup) || filesize($backup) < 100) throw new RuntimeException('Backup failed; migrations not executed.');
file_put_contents($directory.'/backup.sha256', hash_file('sha256', $backup));
file_put_contents($directory.'/before.json', $before);
$status = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo Illuminate\Support\Facades\Artisan::output();
if ($status !== 0) throw new RuntimeException('Migration failed. Inspect migration status before retrying. Backup: '.$directory);
if ($before !== $snapshot()) throw new RuntimeException('Source amount changed; inspect before.json. Backup: '.$directory);
Illuminate\Support\Facades\Artisan::call('finanzas:diagnosticar');
file_put_contents(base_path('docs/fase-3-diagnostico-local.json'), Illuminate\Support\Facades\Artisan::output());
echo 'Incremental migration completed; source amounts unchanged. Backup: '.$directory.PHP_EOL;
