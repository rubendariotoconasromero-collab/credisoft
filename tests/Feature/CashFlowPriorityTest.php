<?php

namespace Tests\Feature;

use App\Services\Finance\Debt;
use App\Services\Finance\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\DiagnosticFixture;

class CashFlowPriorityTest extends \Tests\TestCase
{
    use RefreshDatabase;

    public function test_legacy_combined_retry_recovers_children_without_reapplying_money(): void
    {
        $f = DiagnosticFixture::seed();
        $this->actingAs($f['users']['cajero_a']);
        DB::table('caja')->where('id', $f['cash'])->update(['monto_inicial' => 1000]);
        DB::table('solicitud')->where('id', $f['request'])->update(['estado' => 2]);
        DB::table('plan_pago')->where('id', $f['plan'])->update(['desembolso' => 1, 'pago_administrativo' => 1]);
        $payload = ['id_plan_pago' => $f['plan'], 'id_caja' => $f['cash'], 'monto_pago_adm' => '10.00', 'monto_desembolso' => '1000.00',
            'partes_pago_adm' => [['forma_pago' => 'efectivo', 'monto' => '10.00']],
            'partes_desembolso' => [['forma_pago' => 'efectivo', 'monto' => '1000.00']]];
        $original = $this->withHeader('Idempotency-Key', 'legacy-combined-0001')->postJson('/registrar_pago_adm_desembolso', $payload)->assertOk()->json();
        // Synthetic fixture for the previous format: two children, no parent.
        DB::table('operaciones_financieras')->where('id', $original['id_operacion'])->delete();
        $this->postJson('/registrar_pago_adm_desembolso', array_replace($payload, ['monto_pago_adm' => '11.00']))->assertConflict();
        $recovered = $this->postJson('/registrar_pago_adm_desembolso', $payload)->assertOk()->json();
        $this->assertSame($original['pago_administrativo'], $recovered['pago_administrativo']);
        $this->assertSame($original['desembolso'], $recovered['desembolso']);
        $this->assertDatabaseCount('desembolso', 1);
        $this->assertDatabaseCount('pago_administrativo', 1);
    }

    public function test_interest_only_payment_on_multiple_overdue_quotas_is_not_charged_again(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-09-24 12:00:00'));
        $f = DiagnosticFixture::seed();
        $this->actingAs($f['users']['cajero_a']);
        DB::table('plan_pago')->where('id', $f['plan'])->update(['desembolso' => 0, 'nro_cuotas' => 2]);
        DB::table('cuota')->where('id', $f['installment'])->update(['fecha' => '2026-09-10']);
        $second = DB::table('cuota')->insertGetId(['numero' => 2, 'fecha' => '2026-09-17', 'capital' => 1000, 'interes' => 100, 'saldo_capital' => 0, 'total' => 1100, 'id_plan_pago' => $f['plan']]);
        $payload = ['id_plan_pago' => $f['plan'], 'fecha_pago' => '2026-09-24', 'modalidad_pago' => 'solo_interes',
            'cuotas' => [['id_cuota' => $f['installment']], ['id_cuota' => $second]], 'monto_recibido' => '270.00'];
        $this->postJson('/pagar_cuotas', $payload)->assertOk();
        $quotas = app(Debt::class)->calculate($f['plan'])['cuotas'];
        $this->assertSame('0.00', $quotas[0]->interes_acumulado_neto);
        $this->assertSame('0.00', $quotas[1]->interes_acumulado_neto);
        $this->postJson('/pagar_cuotas', array_replace($payload, ['modalidad_pago' => 'completo', 'cuotas' => [['id_cuota' => $f['installment']]], 'monto_recibido' => $quotas[0]->total_a_pagar]))->assertOk();
        $this->assertSame('0.00', app(Debt::class)->calculate($f['plan'])['cuotas']->last()->interes_acumulado_neto);
        $this->assertEquals(270, DB::table('pago')->sum('pago_interes'));
    }

    public function test_combined_disbursement_retry_returns_original_result(): void
    {
        $f = DiagnosticFixture::seed();
        $this->actingAs($f['users']['cajero_a']);
        DB::table('caja')->where('id', $f['cash'])->update(['monto_inicial' => 1000]);
        DB::table('plan_pago')->where('id', $f['plan'])->update(['desembolso' => 1, 'pago_administrativo' => 1]);
        DB::table('solicitud')->where('id', $f['request'])->update(['estado' => 2]);
        $payload = ['id_plan_pago' => $f['plan'], 'id_caja' => $f['cash'], 'monto_pago_adm' => '10.00', 'monto_desembolso' => '1000.00',
            'partes_pago_adm' => [['forma_pago' => 'efectivo', 'monto' => '10.00']],
            'partes_desembolso' => [['forma_pago' => 'efectivo', 'monto' => '1000.00']]];
        $original = $this->withHeader('Idempotency-Key', 'diagnostic-retry-0001')->postJson('/registrar_pago_adm_desembolso', $payload)->assertOk()->json();
        $retry = $this->postJson('/registrar_pago_adm_desembolso', $payload)->assertOk();
        $this->assertSame($original, $retry->json());
        $this->assertDatabaseCount('desembolso', 1);
        $this->assertDatabaseCount('pago_administrativo', 1);
        $this->assertSame('10.00', app(\App\Services\Finance\CashSummary::class)->forCash($f['cash'])['saldo_disponible']);
        $this->postJson('/registrar_pago_adm_desembolso', array_replace($payload, ['monto_pago_adm' => '11.00']))->assertConflict();
        // Returning a persisted response remains safe after cash closure.
        $this->withHeader('Idempotency-Key', 'diagnostic-close-0001')->postJson('/close_caja', ['id_caja' => $f['cash'], 'efectivo_contado' => '10.00'])->assertOk();
        $this->withHeader('Idempotency-Key', 'diagnostic-retry-0001')->postJson('/registrar_pago_adm_desembolso', $payload)->assertOk()->assertExactJson($original);
    }

    public function test_compare_batch_and_separate_overdue_collections(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-09-24 12:00:00'));
        $f = DiagnosticFixture::seed();
        $this->actingAs($f['users']['cajero_a']);
        DB::table('plan_pago')->where('id', $f['plan'])->update(['desembolso' => 0, 'nro_cuotas' => 2]);
        DB::table('cuota')->where('id', $f['installment'])->update(['fecha' => '2026-09-10']);
        $second = DB::table('cuota')->insertGetId(['numero' => 2, 'fecha' => '2026-09-17', 'capital' => 1000, 'interes' => 100, 'saldo_capital' => 0, 'total' => 1100, 'id_plan_pago' => $f['plan']]);
        $before = app(Debt::class)->calculate($f['plan'])['cuotas'];
        $batchAmount = Money::decimal($before->sum(fn ($q) => Money::cents($q->total_a_pagar)));
        $payload = fn ($ids, $amount) => ['id_plan_pago' => $f['plan'], 'fecha_pago' => '2026-09-24', 'modalidad_pago' => 'completo', 'cuotas' => array_map(fn ($id) => ['id_cuota' => $id], $ids), 'monto_recibido' => $amount];
        DB::beginTransaction();
        $this->postJson('/pagar_cuotas', $payload([$f['installment'], $second], $batchAmount))->assertOk()->assertJsonPath('estado_plan', 2);
        $batchCollected = DB::table('pago')->sum('monto_pago');
        DB::rollBack();
        $this->postJson('/pagar_cuotas', $payload([$f['installment']], $before[0]->total_a_pagar))->assertOk();
        $afterFirst = app(Debt::class)->calculate($f['plan'])['cuotas']->last();
        $this->postJson('/pagar_cuotas', $payload([$second], $afterFirst->total_a_pagar))->assertOk()->assertJsonPath('estado_plan', 2);
        $separateCollected = DB::table('pago')->sum('monto_pago');
        $this->assertEquals(2312, $batchCollected); $this->assertEquals($batchCollected, $separateCollected);
        $this->assertEquals(42, DB::table('pago')->sum('pago_mora'));
    }
}

