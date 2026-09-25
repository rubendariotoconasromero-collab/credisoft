<?php

namespace Tests\Feature;

use App\Services\Finance\CashSessions;
use App\Services\Finance\VaultOperations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\DiagnosticFixture;
use Tests\TestCase;

class CashSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_close_ignores_tampered_totals_and_returns_the_unified_balance_once(): void
    {
        $f = DiagnosticFixture::seed();
        DB::table('pago')->insert(['id_cuota' => $f['installment'], 'id_caja' => $f['cash'],
            'id_usuario' => $f['users']['cajero_a']->id, 'fecha_pago' => '2026-09-24',
            'monto_pago' => '200.02', 'pago_capital' => '200.02', 'pago_interes' => 0,
            'pago_mora' => 0, 'forma_pago' => 'Depósito banco', 'estado' => 1]);
        $this->actingAs($f['users']['cajero_a'])->postJson('/close_caja', ['id_caja' => $f['cash'],
            'monto_inicial' => 999999, 'monto_final' => 888888, 'total_ingreso' => 555555, 'efectivo_contado' => '100.00',
            'fechahora_apertura' => '2000-01-01', 'diferencia' => 111111])
            ->assertOk()->assertJsonPath('resumen.total_ingresos', '200.02')
            ->assertJsonPath('resumen.efectivo_esperado', '100.00')
            ->assertJsonPath('resumen.saldo_disponible', '300.02');
        $cash = DB::table('caja')->find($f['cash']);
        $this->assertEquals(100, $cash->monto_inicial);
        $this->assertEquals(300.02, $cash->monto_final);
        $this->assertEquals(100, $cash->efectivo_total);
        $this->assertSame('2026-09-24 08:00:00', $cash->fechahora_apertura);
        $this->assertEquals(1200.02, DB::table('boveda')->value('saldo_actual'));
        $this->postJson('/close_caja', ['id_caja' => $f['cash']])->assertUnprocessable();
        $this->assertEquals(1200.02, DB::table('boveda')->value('saldo_actual'));
        $this->assertDatabaseCount('caja_cierres', 1);
        $this->getJson('/caja/resumen-financiero?id_caja='.$f['cash'])->assertOk()
            ->assertJsonPath('formas_pago.deposito.ingresos', '200.02');
        $this->getJson('/boveda/cierres-caja')->assertOk()
            ->assertJsonPath('data.0.resumen.formas_pago.deposito.ingresos', '200.02');
    }

    public function test_opening_checks_balance_and_one_session_per_owner(): void
    {
        $f = DiagnosticFixture::seed();
        $this->actingAs($f['users']['cajero_a'])->postJson('/caja/aperturar', ['monto_inicial' => 10])->assertUnprocessable();
        session()->flush();
        $this->actingAs($f['users']['cajero_b'])->postJson('/caja/aperturar', ['monto_inicial' => 901])->assertUnprocessable();
        $this->postJson('/caja/aperturar', ['monto_inicial' => '200.01'])->assertOk();
        $this->assertEquals('699.99', DB::table('boveda')->value('saldo_actual'));
        $this->assertDatabaseCount('caja', 2);
    }

    public function test_transfer_cannot_leave_vault_without_valid_owned_destination(): void
    {
        $f = DiagnosticFixture::seed();
        $this->actingAs($f['users']['cajero_b'])->postJson('/retirar_boveda', [
            'id_caja' => $f['cash'], 'monto' => 50, 'descripcion' => 'Transferencia a Caja',
        ])->assertForbidden();
        $this->assertEquals(900, DB::table('boveda')->value('saldo_actual'));
        $this->assertDatabaseCount('ingreso', 0);
        session()->flush();
        $this->actingAs($f['users']['cajero_a'])->postJson('/retirar_boveda', [
            'id_caja' => $f['cash'], 'monto' => '50.01', 'descripcion' => 'Transferencia a Caja',
        ])->assertOk();
        $this->assertEquals('849.99', DB::table('boveda')->value('saldo_actual'));
        $this->assertDatabaseHas('ingreso', ['id_caja' => $f['cash'], 'monto' => '50.01']);
    }

    public function test_mid_close_failure_rolls_back_cash_vault_and_snapshot(): void
    {
        $f = DiagnosticFixture::seed();
        DB::listen(function ($query) {
            if (str_starts_with($query->sql, 'insert into "caja_cierres"')) throw new \RuntimeException('Fallo simulado');
        });
        try {
            app(CashSessions::class)->close($f['users']['cajero_a']->id, $f['cash']);
            $this->fail('El fallo debía propagarse.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo simulado', $e->getMessage());
        }
        $this->assertDatabaseHas('caja', ['id' => $f['cash'], 'estado' => 1]);
        $this->assertEquals(900, DB::table('boveda')->value('saldo_actual'));
        $this->assertDatabaseCount('caja_cierres', 0);
    }

    public function test_manual_movements_reject_invalid_precision_negative_amounts_and_physical_overdraft(): void
    {
        $f = DiagnosticFixture::seed();
        $this->actingAs($f['users']['cajero_a']);
        foreach (['/save_ingreso', '/save_gasto'] as $route) {
            foreach (['-1', '0', '0.001'] as $amount) {
                $this->postJson($route, ['monto' => $amount, 'descripcion' => 'Prueba'])->assertUnprocessable();
            }
        }
        $this->postJson('/save_gasto', ['monto' => '100.01', 'descripcion' => 'Prueba'])->assertUnprocessable();
        $this->assertDatabaseCount('ingreso', 0);
        $this->assertDatabaseCount('egreso', 0);
        $this->postJson('/save_gasto', ['monto' => '0.01', 'descripcion' => 'Prueba'])->assertOk();
        $this->getJson('/caja/resumen-financiero?id_caja='.$f['cash'])->assertOk()->assertJsonPath('efectivo_esperado', '99.99');
    }
}
