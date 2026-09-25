<?php

namespace Tests\Feature;

use App\Models\User;
use App\Traits\CalculaSaldoCaja;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DevelopmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DevelopmentSeederTest extends TestCase
{
    use RefreshDatabase, CalculaSaldoCaja;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-24 15:00:00');
        config(['app.seed_demo_data' => true]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_seed_builds_relations_permissions_and_reconcilable_cash(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('cliente', 16);
        $this->assertDatabaseCount('codeudor', 8);
        $this->assertDatabaseCount('solicitud', 13);
        $this->assertDatabaseCount('plan_pago', 11);
        $this->assertDatabaseCount('cuota', 42);
        $this->assertDatabaseCount('users', 6);
        $this->assertDatabaseCount('permiso', 19);
        $this->assertGreaterThan(0, DB::table('motivo_ingreso')->where('estado', 0)->where('tipo', 'caja')->count());
        $this->assertGreaterThan(0, DB::table('motivo_gasto')->where('estado', 0)->where('tipo', 'caja')->count());
        $this->assertDatabaseCount('desembolso', 10);
        $this->assertDatabaseCount('pago_administrativo', 10);
        $this->assertSame(2, DB::table('caja')->where('estado', 1)->count());
        $this->assertEquals(38157, DB::table('boveda')->value('saldo_actual'));
        $openBalances = [];
        foreach (DB::table('caja')->where('estado', 1)->get() as $cash) {
            $openBalances[] = $this->calcularSaldoCaja($cash->id);
        }
        sort($openBalances);
        $this->assertSame([1500.0, 2160.0], $openBalances);
        $available = 50000 - DB::table('desembolso')->sum('monto') + DB::table('pago')->sum('monto_pago')
            + DB::table('pago_administrativo')->sum('monto') + DB::table('ingreso')->sum('monto') - DB::table('egreso')->sum('monto');
        $this->assertEquals(41817, $available);
        $this->assertEquals($available, array_sum($openBalances) + DB::table('boveda')->value('saldo_actual'));
        $vaultLedger = DB::table('movimientos_boveda')->where('tipo_movimiento', 'ingreso')->sum('monto')
            - DB::table('movimientos_boveda')->where('tipo_movimiento', 'salida')->sum('monto');
        $this->assertEquals(38157, $vaultLedger);
        foreach (DB::table('caja')->where('estado', 0)->get() as $cash) {
            $this->assertEquals($cash->monto_final, $this->calcularSaldoCaja($cash->id));
            $ledger = DB::table('movimientos_caja')->where('id_caja', $cash->id)->where('tipo_movimiento', 'ingreso')->sum('monto')
                - DB::table('movimientos_caja')->where('id_caja', $cash->id)->where('tipo_movimiento', 'egreso')->sum('monto');
            $this->assertEquals(0, $ledger, 'Closed cash must return all physical funds.');
        }
        foreach (User::all() as $user) $this->assertTrue(Hash::check('admin123', $user->password));
        $this->assertTrue(User::where('name', 'administrador')->first()->tienePermiso('boveda'));
        $this->assertTrue(User::where('name', 'cajero_user')->first()->tienePermiso('controlcaja'));
        $this->assertFalse(User::where('name', 'cajero_user')->first()->tienePermiso('usuarios'));
        $this->assertTrue(User::where('name', 'oficial_user')->first()->tienePermiso('codeudores'));
        $this->assertSame(24, DB::table('direccion')->count());
        $this->assertSame(24, DB::table('telefono')->count());
    }

    public function test_payments_match_installment_accumulators_and_scenario_dates(): void
    {
        $this->seed(DatabaseSeeder::class);
        foreach (DB::table('cuota')->get() as $cuota) {
            $payments = DB::table('pago')->where('id_cuota', $cuota->id)->get();
            $this->assertEquals($cuota->capital_pagado, $payments->sum('pago_capital'));
            $this->assertEquals($cuota->interes_pagado, $payments->sum('pago_interes') + $payments->sum('monto_condonado_interes'));
            $this->assertEquals($cuota->mora_pagada, $payments->sum('pago_mora') + $payments->sum('monto_condonado_mora'));
            if ($cuota->estado == 2) $this->assertEquals($cuota->capital, $cuota->capital_pagado);
            else $this->assertLessThan($cuota->capital, $cuota->capital_pagado);
        }
        foreach (DB::table('pago')->get() as $payment) {
            $this->assertEquals($payment->monto_pago, $payment->pago_capital + $payment->pago_interes + $payment->pago_mora);
            $cash = DB::table('caja')->find($payment->id_caja);
            $this->assertSame($cash->id_usuario, $payment->id_usuario);
            $this->assertSame(substr($cash->fechahora_apertura, 0, 10), substr($payment->fecha_pago, 0, 10));
        }
        $types = DB::table('pago')->distinct()->pluck('tipo_pago')->sort()->values()->all();
        $this->assertSame(['completo', 'interes_mora', 'parcial', 'solo_interes', 'solo_mora'], $types);
        $this->assertEquals(1657, DB::table('pago')->sum('monto_pago'));
        $this->assertEquals(41, DB::table('pago')->sum('monto_condonado'));
        $this->assertSame(1, DB::table('plan_pago')->where('estado', 2)->count());
        $this->assertSame(1, DB::table('plan_pago')->where('estado', 1)->whereDate('fecha_fin', '<', today())->count());
        $this->assertSame(1, DB::table('plan_pago')->where('desembolso', 1)->count());
        foreach (DB::table('plan_pago')->get() as $plan) {
            $capital = DB::table('cuota')->where('id_plan_pago', $plan->id)->sum('capital');
            $paid = DB::table('cuota')->where('id_plan_pago', $plan->id)->sum('capital_pagado');
            $this->assertEquals($plan->total_pagar, $capital);
            $this->assertEquals($plan->saldo_pendiente, $capital - $paid);
        }
    }

    public function test_fixture_dates_follow_the_seeding_day(): void
    {
        Carbon::setTestNow('2027-01-05 12:00:00');
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(2, DB::table('caja')->where('estado', 1)->whereDate('fechahora_apertura', '2027-01-05')->count());
        $this->assertSame(1, DB::table('plan_pago')->where('estado', 1)->whereDate('fecha_fin', '<', today())->count());
    }

    public function test_demo_is_disabled_outside_local_or_testing_even_if_flag_is_true(): void
    {
        $this->app->instance('env', 'production');
        config(['app.bootstrap_admin_password' => 'Testing-production-credential-123']);
        $this->assertFalse(DevelopmentSeeder::enabled());
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertExitCode(0);
        $this->assertDatabaseCount('cliente', 0);
        $this->assertDatabaseCount('caja', 0);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_flag_false_seeds_only_reference_data(): void
    {
        config(['app.seed_demo_data' => false]);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('cliente', 0);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('permiso', 19);
    }

    public function test_rerun_refuses_to_mix_or_overwrite_existing_operations(): void
    {
        $this->seed(DatabaseSeeder::class);
        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('A second demo seed must be rejected.');
        } catch (\LogicException $exception) {
            $this->assertStringContainsString('tablas de negocio vacías', $exception->getMessage());
        }
        $this->assertDatabaseCount('cliente', 16);
        $this->assertEquals(38157, DB::table('boveda')->value('saldo_actual'));
    }
    public function test_nicolas_settlement_separates_collected_and_forgiven_interest_and_historical_fine(): void
    {
        $this->seed(DatabaseSeeder::class);
        $plan = DB::table('plan_pago')->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')->where('cliente.ci', 'DEMO-C014')->value('plan_pago.id');
        $before = DB::table('pago')->get()->toJson();
        $result = app(\App\Services\Finance\Debt::class)->calculate($plan, '2026-09-24');
        $first = $result['cuotas']->first();
        $this->assertEquals(100, $first->porcentaje_interes_pagado);
        $this->assertSame('30.00', $first->interes_cobrado_total);
        $this->assertSame('20.00', $first->interes_condonado_total);
        $this->assertSame('50.00', $first->interes_total_registrado);
        $this->assertSame('registro_cobro', $first->origen_desglose_interes);
        $this->assertSame('25.00', $first->interes_ordinario_generado);
        $this->assertSame('25.00', $first->interes_moratorio_generado);
        $this->assertTrue($first->desglose_cobros_interes_completo);
        $this->assertSame('5.00', $first->interes_ordinario_cobrado);
        $this->assertSame('20.00', $first->interes_ordinario_condonado);
        $this->assertSame('25.00', $first->interes_moratorio_cobrado);
        $this->assertSame('0.00', $first->interes_moratorio_condonado);
        $this->assertSame('321.00', $first->total_registrado_antes_condonacion);
        $this->assertSame('21.00', $first->multa_total_registrada);
        $this->assertSame('0.00', $first->interes_acumulado_neto);
        $this->assertSame('0.00', $first->mora_cobrado_total);
        $this->assertSame('21.00', $first->mora_condonado_total);
        $this->assertEquals(100, $first->porcentaje_mora_pagada);
        $this->assertSame(['dias_historicos' => 8, 'generada' => '24.00', 'cobrada' => '0.00', 'condonada' => '21.00', 'pendiente' => '3.00'], $result['resumen_multa']);
        $this->assertSame('28.57', $result['cuotas'][1]->interes_acumulado_neto);
        $this->assertSame('3.00', $result['cuotas'][1]->mora_fija_neta);
        $this->assertSame($before, DB::table('pago')->get()->toJson());
    }

    public function test_existing_payment_without_snapshot_keeps_known_totals_and_unknown_split(): void
    {
        $this->seed(DatabaseSeeder::class);
        $payment = DB::table('pago')->where('monto_condonado_interes', 20)->first();
        DB::table('operaciones_financieras')->where('id', $payment->id_operacion)->update(['resultado' => null]);
        $plan = DB::table('cuota')->where('id', $payment->id_cuota)->value('id_plan_pago');
        $quota = app(\App\Services\Finance\Debt::class)->calculate($plan)['cuotas']->first();
        $this->assertSame('321.00', $quota->total_registrado_antes_condonacion);
        $this->assertSame('50.00', $quota->interes_total_registrado);
        $this->assertSame('0.00', $quota->total_a_pagar);
        $this->assertFalse($quota->desglose_cobros_interes_completo);
        $this->assertNull($quota->interes_moratorio_cobrado);
        $this->assertNull($quota->interes_moratorio_generado);
    }

}
