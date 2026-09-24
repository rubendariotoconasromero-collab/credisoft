<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Http\Controllers\{CajaController, BovedaController, PagoController, GastoController, IngresoController,
    HistorialPagosController, ClienteController, PlanPagoController, ConsultaFinancieraController, LoginController};
use Illuminate\Support\Facades\{Artisan, Auth, DB};
use Illuminate\Http\Request;
use Tests\Support\{IsolatedApplication, DiagnosticFixture};

$app = IsolatedApplication::create();
$options = getopt('', ['mysql-port:', 'mysql-datadir:']);
$engine = 'sqlite';
if (isset($options['mysql-port'])) {
    $port = filter_var($options['mysql-port'], FILTER_VALIDATE_INT);
    $expected = realpath($options['mysql-datadir'] ?? '');
    $allowed = realpath(storage_path('app/diagnostics'));
    if (!$port || $port < 1024 || !$expected || !$allowed ||
        !str_starts_with(strtolower($expected), strtolower($allowed.DIRECTORY_SEPARATOR))) {
        throw new RuntimeException('Only a dedicated diagnostics datadir and port are allowed.');
    }
    $pdo = new PDO("mysql:host=127.0.0.1;port=$port;charset=utf8mb4", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $actual = realpath($pdo->query('SELECT @@datadir')->fetchColumn());
    if (!$actual || strcasecmp($actual, $expected) !== 0) throw new RuntimeException('Unexpected MySQL datadir: refusing all writes.');
    // No IF NOT EXISTS: refuse to reuse a preexisting database.
    $pdo->exec('CREATE DATABASE credisoft_diagnostic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    config(['database.default' => 'diagnostic', 'database.connections' => ['diagnostic' => [
        'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => $port, 'database' => 'credisoft_diagnostic',
        'username' => 'root', 'password' => '', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '', 'strict' => true,
    ]]]);
    $engine = 'mysql';
}
$report = ['generated_at' => date(DATE_ATOM), 'engine' => $engine, 'synthetic_only' => true,
    'scope' => 'Controller-level reproductions, not browser E2E or concurrency tests', 'scenarios' => [], 'benchmarks' => []];
$started = microtime(true);
$exit = Artisan::call('migrate', ['--force' => true]);
if ($exit !== 0) throw new RuntimeException(Artisan::output());
$report['migration_count'] = DB::table('migrations')->count();
$report['migration_ms'] = round((microtime(true) - $started) * 1000, 2);
$fixture = DiagnosticFixture::seed();
$close = ['id_caja' => $fixture['cash'], 'fechahora_apertura' => '2026-09-24 08:00:00', 'monto_inicial' => 100,
    'monto_final' => 100, 'efectivo_total' => 100, 'deposito_total' => 0, 'efectivo_venta' => 0,
    'deposito_venta' => 0, 'efectivo_gasto' => 0, 'deposito_gasto' => 0, 'total_ingreso' => 0, 'total_egreso' => 0, 'diferencia' => 0];
$pay = ['id_plan_pago' => $fixture['plan'], 'monto_recibido' => 1, 'condonacion_interes' => 0, 'condonacion_mora' => 0,
    'forma_pago' => 'Efectivo', 'fecha_pago' => '2026-09-24', 'modalidad_pago' => 'completo',
    'cuotas' => [['id_cuota' => $fixture['installment'], 'capital_adeudado' => 1, 'interes_adeudado' => 0, 'mora_adeudada' => 0]]];
$run = function (string $id, callable $scenario) use (&$report, $fixture) {
    DB::beginTransaction();
    Auth::setUser($fixture['users']['cajero_a']);
    try {
        $result = $scenario();
        $report['scenarios'][$id] = ['status' => $result['defect'] ? 'reproduced' : 'not_reproduced'] + $result;
    } catch (Throwable $e) {
        $report['scenarios'][$id] = ['status' => 'execution_error', 'error' => $e->getMessage()];
    } finally {
        while (DB::transactionLevel() > 0) DB::rollBack();
        Auth::forgetGuards();
    }
};
$run('FIN-01', function () use ($close) {
    $controller = new CajaController;
    $controller->closeCaja(new Request($close));
    $first = (float) DB::table('boveda')->value('saldo_actual');
    $controller->closeCaja(new Request($close));
    $second = (float) DB::table('boveda')->value('saldo_actual');
    return ['defect' => $second !== $first, 'expected_second' => 1000, 'first' => $first, 'second' => $second];
});
$run('FIN-02', function () use ($pay, $fixture) {
    $response = (new PagoController)->pagarCuotas(new Request($pay));
    $cuota = DB::table('cuota')->find($fixture['installment']);
    return ['defect' => $cuota->estado == 2 && $cuota->capital_pagado < $cuota->capital,
        'capital' => $cuota->capital, 'capital_pagado' => $cuota->capital_pagado, 'estado' => $cuota->estado,
        'response' => is_array($response) ? $response : $response->getData(true)];
});
$run('FIN-03', function () use ($fixture) {
    DB::table('caja')->update(['estado' => 0]);
    $response = (new BovedaController)->retirarBoveda(new Request(['monto' => 50, 'descripcion' => 'Transferencia a Caja']));
    $saldo = (float) DB::table('boveda')->value('saldo_actual');
    return ['defect' => $saldo === 850.0 && DB::table('ingreso')->count() === 0,
        'http' => $response->getStatusCode(), 'vault_before' => 900, 'vault_after' => $saldo, 'income_rows' => DB::table('ingreso')->count()];
});
$run('FIN-04', function () use ($fixture) {
    (new BovedaController)->retirarBoveda(new Request(['monto' => 50, 'descripcion' => 'Transferencia a Caja']));
    (new IngresoController)->anularIngreso(new Request(['id' => DB::table('ingreso')->value('id')]));
    $saldo = (float) DB::table('boveda')->value('saldo_actual');
    return ['defect' => $saldo !== 900.0, 'expected_vault' => 900, 'actual_vault' => $saldo,
        'active_income' => DB::table('ingreso')->where('estado', 1)->count()];
});
$run('FIN-05', function () use ($fixture) {
    (new GastoController)->save(new Request(['monto' => 50, 'descripcion' => 'Transferencia a Bóveda']));
    $request = new Request(['id' => DB::table('egreso')->value('id')]);
    $controller = new GastoController;
    $controller->anularGasto($request);
    $first = (float) DB::table('boveda')->value('saldo_actual');
    $controller->anularGasto($request);
    $second = (float) DB::table('boveda')->value('saldo_actual');
    return ['defect' => $first !== $second, 'first' => $first, 'second' => $second, 'expected' => 900];
});
$run('FIN-06', function () use ($pay, $fixture) {
    (new PagoController)->pagarCuotas(new Request($pay));
    $response = (new HistorialPagosController)->anular(DB::table('pago')->value('id'));
    $cuota = DB::table('cuota')->find($fixture['installment']);
    return ['defect' => $cuota->capital_pagado > 0, 'http' => $response->getStatusCode(),
        'capital_pagado_after_cancel' => $cuota->capital_pagado, 'estado_after_cancel' => $cuota->estado];
});
$run('FIN-07', function () use ($pay, $fixture) {
    $user = $fixture['users']['cajero_b'];
    $cashB = DB::table('caja')->insertGetId(['fechahora_apertura' => now(), 'monto_inicial' => 0, 'id_usuario' => $user->id]);
    Auth::setUser($user);
    (new PagoController)->pagarCuotas(new Request($pay));
    $actual = DB::table('pago')->value('id_caja');
    return ['defect' => $actual !== null && $actual != $cashB, 'expected_cash' => $cashB, 'actual_cash' => $actual];
});
$run('FIN-08', function () use ($fixture) {
    $request = new Request(['id_plan_pago' => $fixture['plan'], 'id_caja' => $fixture['cash'], 'monto' => 10]);
    (new CajaController)->actualizarDesembolso($request);
    (new CajaController)->actualizarDesembolso($request);
    $count = DB::table('desembolso')->where('id_plan_pago', $fixture['plan'])->count();
    return ['defect' => $count > 1, 'disbursement_count' => $count, 'expected' => 1];
});
$run('SEC-01', function () {
    $request = Request::create('/login_process', 'POST', ['name' => 'diagnostico_inhabilitado', 'password' => 'solo-diagnostico-ficticio']);
    $request->setLaravelSession(app('session')->driver());
    $response = (new LoginController)->loginProcess($request);
    return ['defect' => Auth::check(), 'authenticated_after_rejected_login' => Auth::check(), 'http' => $response->getStatusCode()];
});
$run('FUN-01', function () {
    try {
        (new ClienteController)->getClientesPaginate(new Request(['opcion_asesor' => 1]));
        return ['defect' => false];
    } catch (\Illuminate\Database\QueryException $e) {
        return ['defect' => str_contains($e->getMessage(), 'users.id'), 'error' => $e->getMessage()];
    }
});
$run('FIN-09', function () use ($pay, $fixture) {
    $pay['forma_pago'] = 'Deposito';
    $pay['monto_recibido'] = 20;
    $pay['cuotas'][0]['capital_adeudado'] = 1000;
    (new PagoController)->pagarCuotas(new Request($pay));
    $calculator = new class {
        use \App\Traits\CalculaSaldoCaja;
        public function balance($id) { return $this->calcularSaldoCaja($id); }
    };
    $balance = $calculator->balance($fixture['cash']);
    return ['defect' => $balance != 100, 'physical_cash_expected' => 100, 'actual_cash' => $balance];
});
$run('SEC-02', function () use ($fixture, $app) {
    $originalPublic = public_path();
    $sandbox = storage_path('app/diagnostics/uploads-'.bin2hex(random_bytes(8)));
    mkdir($sandbox.'/img/cliente', 0777, true);
    file_put_contents($sandbox.'/harmless.txt', 'Diagnostic text, not an image and not executable.');
    try {
        $app->usePublicPath($sandbox);
        $upload = new \Illuminate\Http\UploadedFile($sandbox.'/harmless.txt', 'harmless.txt', 'text/plain', null, true);
        $request = new Request(['id_cliente' => $fixture['client']], [], [], [], ['imagen' => $upload]);
        $response = (new ClienteController)->fotoCliente($request);
        $name = DB::table('cliente')->where('id', $fixture['client'])->value('imagen');
        return ['defect' => $response->getStatusCode() === 200 && str_ends_with($name ?? '', '.txt'),
            'http' => $response->getStatusCode(), 'accepted_non_image' => (bool) $name, 'public_path_redirected_to_sandbox' => true];
    } finally { $app->usePublicPath($originalPublic); }
});
$run('SEC-03', function () use ($fixture, $app) {
    Auth::setUser($fixture['users']['sin_permisos']);
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $request = Request::create('/get_clientes_paginate', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    return ['defect' => $response->getStatusCode() === 200, 'http' => $response->getStatusCode(),
        'role_has_no_permissions' => true, 'scope' => 'HTTP route with auth middleware'];
});
if ($engine === 'mysql') {
    $run('REP-01', function () use ($close) {
        $request = new Request(['fecha_inicio' => '2026-09-01', 'fecha_final' => '2099-12-31', 'tipo_libro' => 'GENERAL']);
        $controller = new ConsultaFinancieraController;
        $before = $controller->getLibroMayor($request)->getData(true)['capital_total_real'];
        (new CajaController)->closeCaja(new Request($close));
        $after = $controller->getLibroMayor($request)->getData(true)['capital_total_real'];
        return ['defect' => $before != $after, 'before' => $before, 'after' => $after, 'expected' => $before];
    });
}

// Repeatable volume, no real customer data. Keep it available for dump/restore.
$template = (array) DB::table('cliente')->find($fixture['client']);
unset($template['id']);
for ($batch = 0; $batch < 10; $batch++) {
    $rows = [];
    for ($i = 0; $i < 100; $i++) {
        $n = 2 + $batch * 100 + $i;
        $rows[] = array_replace($template, ['nombre' => 'Cliente ficticio '.$n, 'ci' => 'TEST-'.$n]);
    }
    DB::table('cliente')->insert($rows);
}
Auth::setUser($fixture['users']['cajero_a']);
$benchmarks = [
    'clientes_1001' => fn () => (new ClienteController)->getClientesPaginate(new Request),
    'detalle_cliente' => fn () => (new ClienteController)->getFichaCompleta($fixture['client']),
];
if ($engine === 'mysql') {
    $benchmarks['amortizacion_1_cuota'] = fn () => (new PlanPagoController)->listarAmortizaciones(new Request(['id_plan_pago' => $fixture['plan']]));
    $benchmarks['libro_2_movimientos'] = fn () => (new ConsultaFinancieraController)->getLibroMayor(new Request(['fecha_inicio' => '2026-09-01', 'fecha_final' => '2099-12-31']));
}
foreach ($benchmarks as $name => $callback) {
    try {
        $callback(); // warm-up
        $samples = [];
        for ($i = 0; $i < 5; $i++) {
            DB::flushQueryLog(); DB::enableQueryLog();
            $start = microtime(true); $result = $callback();
            $samples[] = round((microtime(true) - $start) * 1000, 3);
            $queryCount = count(DB::getQueryLog()); DB::disableQueryLog();
        }
        sort($samples);
        $bytes = strlen($result instanceof \Symfony\Component\HttpFoundation\Response ? $result->getContent() : json_encode($result));
        $report['benchmarks'][$name] = ['samples_ms' => $samples, 'median_ms' => $samples[2], 'queries_last' => $queryCount, 'response_bytes' => $bytes];
    } catch (Throwable $e) { $report['benchmarks'][$name] = ['error' => $e->getMessage()]; }
}
if ($engine === 'mysql') {
    for ($batch = 0; $batch < 10; $batch++) {
        $rows = [];
        for ($i = 0; $i < 1000; $i++) {
            $rows[] = ['monto' => 1, 'descripcion' => 'Ingreso ficticio benchmark', 'fecha' => '2026-09-24',
                'id_usuario' => $fixture['users']['cajero_a']->id, 'id_caja' => $fixture['cash']];
        }
        DB::table('ingreso')->insert($rows);
    }
    $request = new Request(['fecha_inicio' => '2026-09-01', 'fecha_final' => '2099-12-31']);
    $samples = [];
    (new ConsultaFinancieraController)->getLibroMayor($request);
    for ($i = 0; $i < 5; $i++) {
        DB::flushQueryLog(); DB::enableQueryLog();
        $start = microtime(true);
        $response = (new ConsultaFinancieraController)->getLibroMayor($request);
        $samples[] = round((microtime(true) - $start) * 1000, 3);
        $queryCount = count(DB::getQueryLog()); DB::disableQueryLog();
    }
    sort($samples);
    $report['benchmarks']['libro_10002_movimientos'] = ['samples_ms' => $samples, 'median_ms' => $samples[2],
        'queries_last' => $queryCount, 'response_bytes' => strlen($response->getContent()),
        'page_rows' => count($response->getData(true)['movimientos']['data']),
        'unpaginated_income_rows' => count($response->getData(true)['ingresos_lista'])];
}
$report['fixture'] = ['clients' => DB::table('cliente')->count(), 'users' => DB::table('users')->count(), 'plans' => DB::table('plan_pago')->count()];
file_put_contents(base_path('docs/diagnostico-etapa-1/baseline-'.$engine.'.json'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);
foreach ($report['scenarios'] as $id => $scenario) echo $id.': '.$scenario['status'].PHP_EOL;
echo json_encode($report['benchmarks'], JSON_PRETTY_PRINT).PHP_EOL;
