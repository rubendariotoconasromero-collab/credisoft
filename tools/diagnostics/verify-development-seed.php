<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Http\Controllers\ConsultaFinancieraController;
use App\Http\Controllers\PlanPagoController;
use App\Models\User;
use App\Traits\CalculaSaldoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Artisan, Auth, DB};

\Tests\Support\IsolatedApplication::create();
$options = getopt('', ['mysql-port:', 'mysql-datadir:']);
$port = filter_var($options['mysql-port'] ?? '', FILTER_VALIDATE_INT);
$expected = realpath($options['mysql-datadir'] ?? '');
$allowed = realpath(storage_path('app/diagnostics'));
if (!$port || $port < 1024 || !$expected || !$allowed ||
    !str_starts_with(strtolower($expected), strtolower($allowed.DIRECTORY_SEPARATOR))) {
    throw new RuntimeException('Only the disposable diagnostics server is permitted.');
}
$pdo = new PDO("mysql:host=127.0.0.1;port=$port;charset=utf8mb4", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$actual = realpath($pdo->query('SELECT @@datadir')->fetchColumn());
if (!$actual || strcasecmp($actual, $expected) !== 0) throw new RuntimeException('Unexpected datadir; refusing writes.');
$pdo->exec('CREATE DATABASE credisoft_seed_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
config(['app.seed_demo_data' => true, 'database.default' => 'seed_test', 'database.connections' => ['seed_test' => [
    'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => $port, 'database' => 'credisoft_seed_test',
    'username' => 'root', 'password' => '', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '', 'strict' => true,
]]]);
$calculator = new class {
    use CalculaSaldoCaja;
    public function balance($id) { return $this->calcularSaldoCaja($id); }
};
$snapshots = [];
for ($iteration = 0; $iteration < 2; $iteration++) {
    $code = Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
    if ($code !== 0) throw new RuntimeException(Artisan::output());
    $counts = [];
    foreach (['users', 'permiso', 'cliente', 'codeudor', 'solicitud', 'plan_pago', 'cuota', 'pago',
        'desembolso', 'caja', 'movimientos_caja', 'movimientos_boveda'] as $table) $counts[$table] = DB::table($table)->count();
    Auth::setUser(User::where('name', 'administrador')->firstOrFail());
    $balances = [];
    foreach (DB::table('caja')->where('estado', 1)->get() as $cash) $balances[$cash->id_usuario] = $calculator->balance($cash->id);
    $physical = array_sum($balances) + (float) DB::table('boveda')->value('saldo_actual');
    if ($physical !== 41817.0) throw new RuntimeException('Physical total does not reconcile.');
    $book = (new ConsultaFinancieraController)->getLibroMayor(new Request([
        'fecha_inicio' => today()->subDays(60)->toDateString(), 'fecha_final' => today()->toDateString(), 'tipo_libro' => 'GENERAL',
    ]))->getData(true);
    if ((float) $book['capital_total_real'] !== $physical) throw new RuntimeException('Financial book does not reconcile.');
    $installmentsChecked = 0;
    foreach (DB::table('plan_pago')->get() as $plan) {
        $result = (new PlanPagoController)->listarAmortizaciones(new Request(['id_plan_pago' => $plan->id]));
        if ($result['cuotas']->count() !== $plan->nro_cuotas) throw new RuntimeException('Installment count mismatch.');
        $installmentsChecked += $result['cuotas']->count();
    }
    $snapshots[] = ['counts' => $counts, 'open_cash' => $balances, 'vault' => (float) DB::table('boveda')->value('saldo_actual'),
        'physical_total' => $physical, 'book_capital' => $book['capital_total_real'], 'amortization_rows_checked' => $installmentsChecked];
}
if ($snapshots[0] !== $snapshots[1]) throw new RuntimeException('Fresh seed is not reproducible.');
$report = ['generated_at' => date(DATE_ATOM), 'mysql' => $pdo->query('SELECT VERSION()')->fetchColumn(),
    'command' => 'migrate:fresh --seed (isolated MySQL; two runs)', 'success' => true, 'result' => $snapshots[1]];
file_put_contents(base_path('docs/poblacion-desarrollo-verificacion.json'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
