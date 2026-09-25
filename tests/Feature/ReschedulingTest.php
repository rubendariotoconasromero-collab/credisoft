<?php

namespace Tests\Feature;

use App\Services\Finance\{CashSummary, Debt};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\DiagnosticFixture;
use Tests\TestCase;

class ReschedulingTest extends TestCase
{
    use RefreshDatabase;
    private array $f;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-09-24 12:00:00'));
        $this->f = DiagnosticFixture::seed();
        $this->actingAs($this->f['users']['cajero_a']);
        DB::table('rol')->where('id', $this->f['users']['cajero_a']->id_rol)->update(['nombre' => 'Administrador']);
        foreach (['solicitudprestamos', 'planpagos', 'anular_finanzas', 'condonar'] as $name) {
            $id = DB::table('permiso')->where('nombre', $name)->value('id') ?? DB::table('permiso')->insertGetId(['nombre' => $name, 'descripcion' => $name, 'estado' => 1]);
            DB::table('permiso_rol')->insert(['id_permiso' => $id, 'id_rol' => $this->f['users']['cajero_a']->id_rol]);
        }
        DB::table('plan_pago')->where('id', $this->f['plan'])->update(['desembolso' => 0]);
        DB::table('solicitud')->where('id', $this->f['request'])->update(['estado' => 2, 'tipo_tasa' => 'amortizable']);
    }

    private function payload(bool $selected = true): array
    {
        return ['tipo_operacion' => 'REPROGRAMACION', 'plan_pago' => ['id' => $this->f['plan'], 'id_solicitud' => $this->f['request'],
            'importe_solicitud' => '1.00', 'forma_pago_reprogramacion' => 'Mensual', 'numero_cuotas_reprogramacion' => 3,
            'fecha_primera_cuota' => '2026-10-24'], 'pago_intereses' => $selected ? ['seleccionado' => true,
                'ids_cuotas_interes' => [$this->f['installment']], 'ids_cuotas_mora' => [], 'detalle_interes' => '1.00', 'total_pagar' => '1.00'] : null];
    }

    private function approve(int $id): int
    {
        $body = ['id_solicitud' => $id, 'datos_actualizados' => ['fecha_desembolso' => '2026-09-24', 'fecha_primera_cuota' => '2026-10-24',
            'nro_cuotas' => 3, 'lapso_capital' => 'Mensual', 'tipo_tasa' => 'amortizable', 'tasa' => 10, 'importe_solicitud' => '1.00']];
        $preview = $this->postJson('/solicitud/previsualizar-reprogramacion', $body)->assertOk()->json();
        return $this->postJson('/solicitud/aprobar-reprogramacion-final', $body + ['cuotas' => $preview['cuotas']])->assertOk()->json('id_plan_pago');
    }

    public function test_full_order_flow_is_server_calculated_retriable_and_reversible(): void
    {
        $payload = $this->payload();
        $result = $this->withHeader('Idempotency-Key', 'reschedule-create-001')->postJson('/solicitud/registrar-especial', $payload)->assertOk()->json();
        $this->postJson('/solicitud/registrar-especial', $payload)->assertOk()->assertExactJson($result);
        $id = $result['solicitud_id'];
        $this->assertEquals('1000.00', $result['importe_solicitud']);
        $order = DB::table('orden_pago_reprogramaciones')->first();
        $this->assertEquals(76.67, $order->total_a_pagar);
        $this->withHeader('Idempotency-Key', 'reschedule-early-001')->postJson('/caja/cobrar-orden-reprogramacion', ['id_orden' => $order->id])->assertUnprocessable();
        $this->withHeader('Idempotency-Key', 'reschedule-approve-001');
        $newPlan = $this->approve($id);
        $this->getJson('/get_planespago_caja')->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $newPlan);
        $this->withHeader('Idempotency-Key', 'reschedule-admin-001')->postJson('/guardar_pago_adm', ['id_plan_pago' => $newPlan, 'monto' => '10.00'])->assertUnprocessable();
        $pay = ['id_orden' => $order->id, 'partes' => [['forma_pago' => 'efectivo', 'monto' => '50.00'], ['forma_pago' => 'transferencia', 'monto' => '30.00', 'referencia' => 'TEST-REPRO']]];
        $receipt = $this->withHeader('Idempotency-Key', 'reschedule-collect-001')->postJson('/caja/cobrar-orden-reprogramacion', $pay)->assertOk()->assertJsonPath('cambio', '3.33')->json();
        $this->postJson('/caja/cobrar-orden-reprogramacion', $pay)->assertOk()->assertExactJson($receipt);
        $this->assertDatabaseCount('pago', 1);
        $this->assertSame('176.67', app(CashSummary::class)->forCash($this->f['cash'])['saldo_disponible']);
        $this->assertSame('146.67', app(CashSummary::class)->forCash($this->f['cash'])['efectivo_esperado']);
        $this->get('/operaciones/'.$receipt['codigo_transaccion'].'/recibo')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->getJson('/caja/get-ordenes-reprogramacion')->assertOk()->assertJsonPath('0.codigo_operacion', $receipt['codigo_transaccion']);
        $this->withHeader('Idempotency-Key', 'reschedule-reverse-001')->postJson('/pagos/anular-transaccion', ['codigo' => $receipt['codigo_transaccion'], 'motivo' => 'Error de registro'])->assertOk();
        $this->assertDatabaseHas('orden_pago_reprogramaciones', ['id' => $order->id, 'estado' => 1]);
        $this->assertSame('100.00', app(CashSummary::class)->forCash($this->f['cash'])['saldo_disponible']);
        $this->withHeader('Idempotency-Key', 'reschedule-cancel-001')->postJson('/solicitud/cancelar-especial', ['id_solicitud' => $id, 'motivo' => 'Cliente desistió'])->assertOk();
        $this->assertDatabaseHas('plan_pago', ['id' => $this->f['plan'], 'estado' => 1]);
        $this->assertSame('76.67', app(Debt::class)->calculate($this->f['plan'])['cuotas']->first()->interes_acumulado_neto);
    }

    public function test_capitalization_and_refinancing_disburse_only_extra_money(): void
    {
        DB::table('caja')->where('id', $this->f['cash'])->update(['monto_inicial' => 500]);
        $payload = array_replace($this->payload(false), ['tipo_operacion' => 'REFINANCIAMIENTO', 'monto_adicional' => '200.00']);
        $id = $this->postJson('/solicitud/registrar-especial', $payload)->assertOk()->assertJsonPath('importe_solicitud', '1276.67')->json('solicitud_id');
        $plan = $this->approve($id);
        $this->assertDatabaseCount('orden_pago_reprogramaciones', 0);
        $this->assertEquals(1276.67, DB::table('cuota')->where('id_plan_pago', $plan)->sum('capital'));
        $this->postJson('/actualizar_desembolso', ['id_plan_pago' => $plan, 'monto' => '1276.67'])->assertUnprocessable();
        $this->postJson('/actualizar_desembolso', ['id_plan_pago' => $plan, 'monto' => '200.00'])->assertOk();
        $this->postJson('/solicitud/cancelar-especial', ['id_solicitud' => $id, 'motivo' => 'No permitido con desembolso'])->assertUnprocessable();
        $this->assertSame('300.00', app(CashSummary::class)->forCash($this->f['cash'])['saldo_disponible']);
    }

    public function test_failed_creation_and_forgiveness_permissions_leave_original_active(): void
    {
        $p = $this->payload();
        $p['pago_intereses']['ids_cuotas_interes'] = [99999];
        $this->postJson('/solicitud/registrar-especial', $p)->assertUnprocessable();
        $p = $this->payload();
        $p['pago_intereses'] += ['condonar_interes' => true, 'monto_condonar_interes' => '76.67', 'motivo_condonacion' => 'Beneficio autorizado'];
        DB::table('permiso_rol')->where('id_permiso', DB::table('permiso')->where('nombre', 'condonar')->value('id'))->delete();
        $this->postJson('/solicitud/registrar-especial', $p)->assertForbidden();
        $this->assertDatabaseCount('reprogramaciones_financieras', 0);
        $this->assertDatabaseHas('plan_pago', ['id' => $this->f['plan'], 'estado' => 1]);
    }

    public function test_pending_credit_search_has_no_implicit_age_limit_and_accepts_one_date_bound(): void
    {
        DB::table('plan_pago')->where('id', $this->f['plan'])->update(['fecha_inicio' => '2025-01-01']);
        $this->getJson('/get_planespago_caja?criterio=cliente.nombre')->assertOk()->assertJsonCount(1);
        $this->getJson('/get_planespago_caja?criterio=cliente.nombre&fecha_inicio=2026-01-01')->assertOk()->assertJsonCount(0);
        $this->getJson('/get_planespago_caja?criterio=cliente.nombre&fecha_fin=2025-12-31')->assertOk()->assertJsonCount(1);
    }

    public function test_full_forgiveness_records_a_zero_income_order_and_can_be_reversed(): void
    {
        $p = $this->payload();
        $p['pago_intereses'] += ['condonar_interes' => true, 'monto_condonar_interes' => '76.67', 'motivo_condonacion' => 'Beneficio autorizado'];
        $id = $this->postJson('/solicitud/registrar-especial', $p)->assertOk()->json('solicitud_id');
        $this->approve($id);
        $order = DB::table('orden_pago_reprogramaciones')->first();
        $receipt = $this->postJson('/caja/cobrar-orden-reprogramacion', ['id_orden' => $order->id, 'partes' => []])
            ->assertOk()->assertJsonPath('monto', '0.00')->json();
        $this->assertSame('100.00', app(CashSummary::class)->forCash($this->f['cash'])['saldo_disponible']);
        $this->assertDatabaseHas('pago', ['monto_pago' => 0, 'monto_condonado_interes' => 76.67]);
        $this->postJson('/pagos/anular-transaccion', ['codigo' => $receipt['codigo_transaccion'], 'motivo' => 'Corrección autorizada'])->assertOk();
        $this->assertDatabaseHas('orden_pago_reprogramaciones', ['id' => $order->id, 'estado' => 1]);
    }

    public function test_new_credit_activity_blocks_reversal_of_the_order(): void
    {
        $id = $this->postJson('/solicitud/registrar-especial', $this->payload())->assertOk()->json('solicitud_id');
        $plan = $this->approve($id);
        $order = DB::table('orden_pago_reprogramaciones')->first();
        $receipt = $this->postJson('/caja/cobrar-orden-reprogramacion', ['id_orden' => $order->id])->assertOk()->json();
        $this->postJson('/guardar_pago_adm', ['id_plan_pago' => $plan, 'monto' => '10.00'])->assertOk();
        $this->postJson('/pagos/anular-transaccion', ['codigo' => $receipt['codigo_transaccion'], 'motivo' => 'No debe permitirse'])->assertUnprocessable();
        $this->assertDatabaseHas('orden_pago_reprogramaciones', ['id' => $order->id, 'estado' => 2]);
        $this->assertDatabaseHas('pago', ['codigo_transaccion' => $receipt['codigo_transaccion'], 'estado' => 1]);
    }

    public function test_schedule_conserves_principal_for_each_frequency_and_rate_type(): void
    {
        foreach (['Mensual', 'Quincenal', 'Semanal'] as $frequency) {
            foreach (['fija', 'amortizable'] as $type) {
                foreach (['0', '2.5'] as $rate) {
                    $rows = \App\Services\Finance\ReschedulingSchedule::make(['nro_cuotas' => 7, 'lapso_capital' => $frequency,
                        'tipo_tasa' => $type, 'tasa' => $rate, 'fecha_desembolso' => '2026-09-24', 'fecha_primera_cuota' => '2026-10-24'], 123457);
                    $this->assertCount(7, $rows);
                    $this->assertSame(123457, array_sum(array_map(fn ($r) => \App\Services\Finance\Money::cents($r['capital']), $rows)));
                    $this->assertSame('0.00', end($rows)['saldo_capital']);
                    foreach ($rows as $row) $this->assertGreaterThan(0, (float) $row['capital']);
                }
            }
        }
    }

    public function test_draft_preview_ignores_browser_money_and_matches_registered_principal(): void
    {
        $payload = $this->payload(false);
        $payload['plan_pago']['id_plan_pago'] = $this->f['plan'];
        $draft = $this->postJson('/solicitud/simular-especial', $payload)->assertOk()->assertJsonPath('importe_solicitud', '1076.67')->json();
        $this->assertEquals(1076.67, array_sum(array_column($draft['cuotas'], 'capital')));
        $this->assertDatabaseCount('reprogramaciones_financieras', 0);
        $this->assertDatabaseHas('plan_pago', ['id' => $this->f['plan'], 'estado' => 1]);
        $this->postJson('/solicitud/registrar-especial', $payload + ['revision' => $draft['revision']])
            ->assertOk()->assertJsonPath('importe_solicitud', $draft['importe_solicitud']);
    }

    public function test_officer_cannot_approve_or_overwrite_special_request_with_generic_editor(): void
    {
        $id = $this->postJson('/solicitud/registrar-especial', $this->payload())->assertOk()->json('solicitud_id');
        $this->postJson('/modify_solicitud', ['id_solicitud' => $id, 'importe_solicitud' => 1])->assertUnprocessable();
        DB::table('rol')->where('id', $this->f['users']['cajero_a']->id_rol)->update(['nombre' => 'Oficial de Credito']);
        $this->f['users']['cajero_a']->unsetRelation('rol');
        $this->postJson('/solicitud/aprobar-reprogramacion-final', ['id_solicitud' => $id, 'datos_actualizados' => ['tasa' => 10]])->assertForbidden();
        $this->assertDatabaseHas('reprogramaciones_financieras', ['id_solicitud' => $id, 'estado' => 'pendiente']);
    }
}
