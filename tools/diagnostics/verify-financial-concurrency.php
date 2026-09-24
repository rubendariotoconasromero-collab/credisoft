<?php

require __DIR__.'/finance-bootstrap.php';
use App\Services\Finance\{CashSummary, FinancialLock, Money};
use Illuminate\Support\Facades\{Artisan, DB};
use Tests\Support\DiagnosticFixture;

$pdo->exec('CREATE DATABASE credisoft_finance_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$paths = array_values(array_filter(glob(database_path('migrations/*.php')), fn ($path) => !str_contains($path, '2026_09_24_')));
if (Artisan::call('migrate', ['--path' => $paths, '--realpath' => true, '--force' => true]) !== 0) throw new RuntimeException(Artisan::output());
$f = DiagnosticFixture::seed();
$before = DB::table('boveda')->select('id', 'saldo_actual')->get()->toJson();
if (Artisan::call('migrate', ['--force' => true]) !== 0) throw new RuntimeException(Artisan::output());
if ($before !== DB::table('boveda')->select('id', 'saldo_actual')->get()->toJson()) throw new RuntimeException('Incremental migration changed balances.');
$report = ['incremental_migration_preserves_balances' => true, 'independent_mysql_processes' => true];

$race = function (array $jobs) use ($port, $expected) {
    $processes = [];
    DB::beginTransaction();
    FinancialLock::acquire();
    try {
        foreach ($jobs as [$operation, $user, $cash]) {
            $ready = dirname($expected).'/ready-'.bin2hex(random_bytes(5));
            $pipes = [];
            $process = proc_open([PHP_BINARY, __DIR__.'/finance-worker.php', "--mysql-port=$port", "--mysql-datadir=$expected",
                "--worker=$operation", "--user=$user", "--cash=$cash", "--ready=$ready"],
                [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
            if (!is_resource($process)) throw new RuntimeException('Cannot start worker.');
            fclose($pipes[0]);
            $processes[] = [$process, $pipes, $ready];
        }
        $deadline = microtime(true) + 15;
        do {
            clearstatcache();
            $allReady = !array_filter($processes, fn ($process) => !is_file($process[2]));
            if (!$allReady) usleep(20000);
        } while (!$allReady && microtime(true) < $deadline);
        if (!$allReady) throw new RuntimeException('Workers did not reach barrier.');
        DB::commit();
        $results = [];
        foreach ($processes as [$process, $pipes]) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            if (proc_close($process) !== 0) throw new RuntimeException($error ?: $output);
            $results[] = json_decode($output, true, 512, JSON_THROW_ON_ERROR)['status'];
        }
        return $results;
    } finally {
        if (DB::transactionLevel()) DB::rollBack();
    }
};
$a = $f['users']['cajero_a']->id;
$b = $f['users']['cajero_b']->id;
$report['same_owner_open'] = $race([['open', $b, 0], ['open', $b, 0]]);
$statuses = $report['same_owner_open']; sort($statuses);
if ($statuses !== [200, 422] || DB::table('caja')->where('id_usuario', $b)->where('estado', 1)->count() !== 1) throw new RuntimeException('Duplicate opening.');
$report['close_vs_transfer'] = $race([['close', $a, $f['cash']], ['transfer', $a, $f['cash']]]);
if ($report['close_vs_transfer'][0] !== 200 || !in_array($report['close_vs_transfer'][1], [200, 422], true)) throw new RuntimeException('Close/transfer failed.');
$physical = Money::cents(DB::table('boveda')->value('saldo_actual'));
foreach (DB::table('caja')->where('estado', 1)->pluck('id') as $cash) $physical += Money::cents(app(CashSummary::class)->forCash($cash)['efectivo_esperado']);
if ($physical !== 100000 || DB::table('caja_cierres')->count() !== 1) throw new RuntimeException('Cash conservation failed.');
$report['physical_total_after_internal_operations'] = Money::decimal($physical);
\Illuminate\Support\Facades\Auth::setUser($f['users']['cajero_a']);
$book = (new \App\Http\Controllers\ConsultaFinancieraController)->getLibroMayor(new \Illuminate\Http\Request([
    'fecha_inicio' => '2000-01-01', 'fecha_final' => '2100-01-01', 'tipo_libro' => 'GENERAL',
]))->getData(true);
if ((float) $book['capital_total_real'] !== 1000.0) throw new RuntimeException('Closing return counted as external income.');
$report['book_total_after_close'] = $book['capital_total_real'];
$report['competing_withdrawals'] = $race([['withdraw', $a, 0], ['withdraw', $b, 0]]);
$statuses = $report['competing_withdrawals']; sort($statuses);
if ($statuses !== [200, 422] || Money::cents(DB::table('boveda')->value('saldo_actual')) !== 30000) throw new RuntimeException('Vault overdrawn.');
DB::table('plan_pago')->where('id', $f['plan'])->update(['desembolso' => 0]);
$cashB = DB::table('caja')->where('id_usuario', $b)->where('estado', 1)->value('id');
$report['competing_collections'] = $race([['collect', $b, $cashB], ['collect', $b, $cashB]]);
$statuses = $report['competing_collections']; sort($statuses);
if ($statuses !== [200, 422] || DB::table('pago')->count() !== 1 || Money::cents(DB::table('pago')->value('pago_capital')) !== 100000) throw new RuntimeException('Concurrent collection overpaid the loan.');
// New independent disbursement scenario on the same synthetic plan; no balance repair in user data.
DB::table('plan_pago')->where('id', $f['plan'])->update(['desembolso' => 1, 'estado' => 1]);
DB::table('solicitud')->where('id', $f['request'])->update(['estado' => 2]);
$report['competing_disbursements'] = $race([['disburse', $b, $cashB], ['disburse', $b, $cashB]]);
$statuses = $report['competing_disbursements']; sort($statuses);
if ($statuses !== [200, 422] || DB::table('desembolso')->count() !== 1) throw new RuntimeException('Duplicate disbursement.');
file_put_contents(base_path('docs/fase-3-concurrencia.json'), json_encode($report, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL;
