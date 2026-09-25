<?php
namespace Tests\Feature;

use App\Services\Finance\{CashSummary, Debt};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\DiagnosticFixture;
use Tests\TestCase;

class FinancialOperationsTest extends TestCase
{
    use RefreshDatabase;
    private array $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-09-24 12:00:00'));
        $this->fixture = DiagnosticFixture::seed();
        DB::table('plan_pago')->where('id', $this->fixture['plan'])->update(['desembolso' => 0]);
        $this->actingAs($this->fixture['users']['cajero_a']);
    }

    private function payload(array $extra = []): array
    {
        return $extra + ['id_plan_pago' => $this->fixture['plan'], 'fecha_pago' => '2026-09-24', 'modalidad_pago' => 'parcial',
            'cuotas' => [['id_cuota' => $this->fixture['installment'], 'capital_adeudado' => 1, 'interes_adeudado' => 0, 'mora_adeudada' => 0]],
            'monto_recibido' => '1.00', 'forma_pago' => 'efectivo'];
    }

    private function grant(string $permission): void
    {
        DB::table('permiso_rol')->insert(['id_rol' => $this->fixture['users']['cajero_a']->id_rol, 'id_permiso' => DB::table('permiso')->where('nombre', $permission)->value('id')]);
    }

    public function test_server_debt_cannot_be_replaced_by_client_amounts(): void
    {
        $this->postJson('/pagar_cuotas', $this->payload())->assertOk();
        $this->assertDatabaseHas('cuota', ['id' => $this->fixture['installment'], 'estado' => 3, 'capital_pagado' => 0, 'interes_pagado' => 1]);
        $this->assertDatabaseHas('pago', ['tipo_pago' => 'parcial', 'monto_pago' => 1]);
    }

    public function test_mixed_payment_change_and_retry_are_exact(): void
    {
        $key = (string) Str::uuid();
        $payload = $this->payload(['monto_aplicado' => '150.00', 'partes' => [
            ['forma_pago' => 'efectivo', 'monto' => '120.00'], ['forma_pago' => 'transferencia', 'monto' => '50.00', 'referencia' => 'QR-TEST'],
        ]]);
        $this->withHeader('Idempotency-Key', $key)->postJson('/pagar_cuotas', $payload)->assertOk()->assertJsonPath('cambio', '20.00');
        $this->postJson('/pagar_cuotas', $payload)->assertOk()->assertJsonPath('cambio', '20.00');
        $this->assertDatabaseCount('pago', 1);
        $summary = app(CashSummary::class)->forCash($this->fixture['cash']);
        $this->assertSame('150.00', $summary['total_ingresos']);
        $this->assertSame('200.00', $summary['efectivo_esperado']);
        $this->postJson('/pagar_cuotas', $this->payload(['monto_recibido' => '2.00']))->assertConflict();
    }

    public function test_noncash_excess_or_missing_reference_leaves_no_operation(): void
    {
        $this->postJson('/pagar_cuotas', $this->payload(['monto_aplicado' => '150.00', 'partes' => [['forma_pago' => 'deposito', 'monto' => '160.00', 'referencia' => 'D-1']]]))->assertUnprocessable();
        $this->postJson('/pagar_cuotas', $this->payload(['partes' => [['forma_pago' => 'transferencia', 'monto' => '10.00']]]))->assertUnprocessable();
        $this->assertDatabaseCount('pago', 0); $this->assertDatabaseCount('operaciones_financieras', 0);
    }

    public function test_early_principal_only_reduces_future_interest(): void
    {
        $this->grant('pagos_retroactivos');
        $this->postJson('/pagar_cuotas', $this->payload(['fecha_pago' => '2026-09-16', 'monto_recibido' => '350.00']))->assertOk();
        $this->assertDatabaseHas('pago', ['pago_interes' => 50, 'pago_capital' => 300]);
        $quota = app(Debt::class)->calculate($this->fixture['plan'], '2026-10-01')['cuotas']->first();
        $this->assertSame('700.00', $quota->capital_neto);
        $this->assertSame('35.00', $quota->interes_acumulado_neto);
    }

    public function test_condonation_and_backdating_need_distinct_permissions(): void
    {
        $this->postJson('/pagar_cuotas', $this->payload(['fecha_pago' => '2026-09-16']))->assertForbidden();
        $this->postJson('/pagar_cuotas', $this->payload(['condonacion_interes' => '10.00', 'motivo_condonacion_interes' => 'Autorizado']))->assertForbidden();
        $this->grant('condonar');
        $this->postJson('/pagar_cuotas', $this->payload(['condonacion_interes' => '10.00']))->assertUnprocessable();
        $this->postJson('/pagar_cuotas', $this->payload(['condonacion_interes' => '10.00', 'motivo_condonacion_interes' => 'Autorizado']))->assertOk();
        $this->assertSame('1.00', app(CashSummary::class)->forCash($this->fixture['cash'])['total_ingresos']);
    }

    public function test_credit_fine_does_not_multiply_with_overdue_installments(): void
    {
        DB::table('cuota')->insert(['numero' => 2, 'fecha' => '2026-11-01', 'capital' => 1000, 'interes' => 100, 'saldo_capital' => 0, 'total' => 1100, 'id_plan_pago' => $this->fixture['plan']]);
        $quotas = app(Debt::class)->calculate($this->fixture['plan'], '2026-11-06')['cuotas'];
        $this->assertSame('108.00', $quotas[0]->mora_fija_neta);
        $this->assertSame('0.00', $quotas[1]->mora_fija_neta);
    }

    public function test_reversal_restores_debt_condonation_and_all_payment_parts_once(): void
    {
        $this->grant('condonar'); $this->grant('anular_finanzas');
        $result = $this->postJson('/pagar_cuotas', $this->payload(['condonacion_interes' => '10.00', 'motivo_condonacion_interes' => 'Beneficio autorizado',
            'partes' => [['forma_pago' => 'efectivo', 'monto' => '50.00'], ['forma_pago' => 'deposito', 'monto' => '100.00', 'referencia' => 'DEP-123']]]))->assertOk()->json();
        $this->postJson('/pagos/anular-transaccion', ['codigo' => $result['codigo_transaccion'], 'motivo' => 'Registro equivocado'])->assertOk();
        $this->postJson('/pagos/anular-transaccion', ['codigo' => $result['codigo_transaccion'], 'motivo' => 'Registro equivocado'])->assertOk()->assertJsonPath('ya_anulada', true);
        $this->assertDatabaseHas('cuota', ['id' => $this->fixture['installment'], 'capital_pagado' => 0, 'interes_pagado' => 0, 'mora_pagada' => 0, 'estado' => 1]);
        $this->assertSame('100.00', app(CashSummary::class)->forCash($this->fixture['cash'])['efectivo_esperado']);
        $this->assertSame('0.00', app(CashSummary::class)->forCash($this->fixture['cash'])['total_ingresos']);
        $receipt = $this->get('/operaciones/'.$result['codigo_transaccion'].'/recibo')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $receipt->getContent());
    }

    public function test_later_payment_and_missing_permission_block_reversal(): void
    {
        $first = $this->postJson('/pagar_cuotas', $this->payload())->assertOk()->json('codigo_transaccion');
        $this->postJson('/pagos/anular-transaccion', ['codigo' => $first, 'motivo' => 'Error'])->assertForbidden();
        $this->grant('anular_finanzas');
        $this->postJson('/pagar_cuotas', $this->payload(['monto_recibido' => '2.00']))->assertOk();
        $this->postJson('/pagos/anular-transaccion', ['codigo' => $first, 'motivo' => 'Error'])->assertUnprocessable();
        $this->assertDatabaseCount('pago', 2);
        $this->assertEquals(2, DB::table('pago')->where('estado', 1)->count());
    }

    public function test_modes_restrict_the_components_and_full_payment_closes_plan(): void
    {
        $this->postJson('/pagar_cuotas', $this->payload(['modalidad_pago' => 'solo_mora', 'monto_recibido' => '1.00']))->assertUnprocessable();
        $this->postJson('/pagar_cuotas', $this->payload(['modalidad_pago' => 'solo_interes', 'monto_recibido' => '20.00']))->assertOk();
        $this->assertDatabaseHas('pago', ['tipo_pago' => 'solo_interes', 'pago_interes' => 20, 'pago_capital' => 0]);
        $this->postJson('/pagar_cuotas', $this->payload(['modalidad_pago' => 'completo', 'monto_recibido' => '1056.67']))->assertOk()->assertJsonPath('estado_plan', 2);
        $this->assertDatabaseHas('cuota', ['id' => $this->fixture['installment'], 'estado' => 2, 'capital_pagado' => 1000]);
    }

    public function test_settlement_preserves_generated_interest_and_reversal_discards_snapshot(): void
    {
        DB::table('cuota')->where('id', $this->fixture['installment'])->update(['fecha' => '2026-09-17']);
        $before = app(Debt::class)->calculate($this->fixture['plan'])['cuotas']->first();
        $this->assertGreaterThan(0, (float) $before->interes_moratorio_generado);
        $response = $this->postJson('/pagar_cuotas', $this->payload(['modalidad_pago' => 'completo', 'monto_recibido' => $before->total_a_pagar]))->assertOk();
        foreach (['2026-09-24', '2026-12-31'] as $date) {
            $settled = app(Debt::class)->calculate($this->fixture['plan'], $date)['cuotas']->first();
            $this->assertSame('registro_cobro', $settled->origen_desglose_interes);
            $this->assertSame($before->interes_ordinario_generado, $settled->interes_ordinario_generado);
            $this->assertSame($before->interes_moratorio_generado, $settled->interes_moratorio_generado);
            $this->assertSame('0.00', $settled->total_a_pagar);
            $this->assertSame($before->interes_moratorio_generado, $settled->interes_moratorio_cobrado);
            $this->assertSame('0.00', $settled->interes_moratorio_condonado);
        }
        $this->grant('anular_finanzas');
        $this->postJson('/pagos/anular-transaccion', ['codigo' => $response->json('codigo_transaccion'), 'motivo' => 'Prueba de reversión'])->assertOk();
        $restored = app(Debt::class)->calculate($this->fixture['plan'])['cuotas']->first();
        $this->assertSame('calculo_actual', $restored->origen_desglose_interes);
        $this->assertSame($before->total_a_pagar, $restored->total_a_pagar);
    }

    public function test_partial_interest_payments_keep_split_on_retry_and_reversal(): void
    {
        DB::table('cuota')->where('id', $this->fixture['installment'])->update(['fecha' => '2026-09-17']);
        $key = (string) Str::uuid();
        $payload = $this->payload(['modalidad_pago' => 'solo_interes', 'monto_recibido' => '110.00']);
        $result = $this->withHeader('Idempotency-Key', $key)->postJson('/pagar_cuotas', $payload)->assertOk();
        $result->assertJsonPath('detalles.0.desglose_interes.ordinario_cobrado', '100.00')
            ->assertJsonPath('detalles.0.desglose_interes.moratorio_cobrado', '10.00');
        $this->postJson('/pagar_cuotas', $payload)->assertOk();
        $this->assertDatabaseCount('pago', 1);
        $this->getJson('/get_pagos_cuota?id_cuota='.$this->fixture['installment'])->assertOk()
            ->assertJsonPath('0.desglose_interes.moratorio_cobrado', '10.00');
        $quota = app(Debt::class)->calculate($this->fixture['plan'])['cuotas']->first();
        $this->assertSame('10.00', $quota->interes_moratorio_cobrado);
        $this->assertSame('13.33', $quota->interes_moratorio_neto);
        $this->grant('condonar');
        $this->withHeader('Idempotency-Key', (string) Str::uuid())->postJson('/pagar_cuotas', $this->payload([
            'modalidad_pago' => 'solo_interes', 'monto_recibido' => '8.33',
            'condonacion_interes' => '5.00', 'motivo_condonacion_interes' => 'Beneficio autorizado',
        ]))->assertOk()->assertJsonPath('detalles.0.desglose_interes.moratorio_condonado', '5.00');
        $lastCode = DB::table('pago')->orderByDesc('id')->value('codigo_transaccion');
        $quota = app(Debt::class)->calculate($this->fixture['plan'])['cuotas']->first();
        $this->assertSame('18.33', $quota->interes_moratorio_cobrado);
        $this->assertSame('5.00', $quota->interes_moratorio_condonado);
        $this->assertSame('0.00', $quota->interes_moratorio_neto);
        $this->grant('anular_finanzas');
        $this->withHeader('Idempotency-Key', (string) Str::uuid())->postJson('/pagos/anular-transaccion', ['codigo' => $lastCode, 'motivo' => 'Prueba de desglose'])->assertOk();
        $quota = app(Debt::class)->calculate($this->fixture['plan'])['cuotas']->first();
        $this->assertSame('10.00', $quota->interes_moratorio_cobrado);
        $this->assertSame('0.00', $quota->interes_moratorio_condonado);
        $this->assertSame('13.33', $quota->interes_moratorio_neto);
    }

    public function test_forgiveness_crosses_ordinary_and_moratory_without_changing_total(): void
    {
        DB::table('cuota')->where('id', $this->fixture['installment'])->update(['fecha' => '2026-09-17']);
        $this->grant('condonar');
        $this->postJson('/pagar_cuotas', $this->payload([
            'modalidad_pago' => 'solo_interes', 'monto_recibido' => '13.33',
            'condonacion_interes' => '110.00', 'motivo_condonacion_interes' => 'Beneficio autorizado',
        ]))->assertOk()->assertJsonPath('detalles.0.desglose_interes', [
            'ordinario_cobrado' => '0.00', 'moratorio_cobrado' => '13.33',
            'ordinario_condonado' => '100.00', 'moratorio_condonado' => '10.00',
        ]);
        $this->assertDatabaseHas('pago', ['pago_interes' => 13.33, 'monto_condonado_interes' => 110]);
    }

    public function test_disbursement_is_exact_unique_and_checks_only_physical_component(): void
    {
        DB::table('plan_pago')->where('id', $this->fixture['plan'])->update(['desembolso' => 1]);
        DB::table('solicitud')->where('id', $this->fixture['request'])->update(['estado' => 2]);
        $payload = ['id_plan_pago' => $this->fixture['plan'], 'id_caja' => $this->fixture['cash'], 'monto' => '1000.00',
            'partes' => [['forma_pago' => 'efectivo', 'monto' => '100.00'], ['forma_pago' => 'transferencia', 'monto' => '900.00', 'referencia' => 'D-001']]];
        $this->postJson('/actualizar_desembolso', array_replace($payload, ['monto' => '900.00']))->assertUnprocessable();
        $this->postJson('/actualizar_desembolso', $payload)->assertOk();
        $this->postJson('/actualizar_desembolso', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('desembolso', 1);
        $this->assertSame('0.00', app(CashSummary::class)->forCash($this->fixture['cash'])['efectivo_esperado']);
        $this->assertEquals(900, DB::table('boveda')->value('saldo_actual'));
    }

    public function test_transfer_reversal_restores_both_sides_and_close_retry_is_safe(): void
    {
        $this->grant('anular_finanzas');
        $transfer = $this->postJson('/retirar_boveda', ['id_caja' => $this->fixture['cash'], 'monto' => '50.00', 'descripcion' => 'Transferencia a Caja'])->assertOk()->json('codigo_transaccion');
        $this->postJson('/pagos/anular-transaccion', ['codigo' => $transfer, 'motivo' => 'Transferencia equivocada'])->assertOk();
        $this->assertEquals(900, DB::table('boveda')->value('saldo_actual'));
        $key = (string) Str::uuid();
        $close = ['id_caja' => $this->fixture['cash'], 'efectivo_contado' => '100.00'];
        $this->postJson('/close_caja', array_replace($close, ['efectivo_contado' => '99.00']))->assertUnprocessable();
        $this->withHeader('Idempotency-Key', $key)->postJson('/close_caja', $close)->assertOk();
        $this->postJson('/close_caja', $close)->assertOk();
        $this->assertEquals(1000, DB::table('boveda')->value('saldo_actual'));
        $this->assertDatabaseCount('caja_cierres', 1);
    }

    public function test_stale_view_is_rejected_without_new_payment(): void
    {
        $revision = app(Debt::class)->calculate($this->fixture['plan'])['revision'];
        $this->postJson('/pagar_cuotas', $this->payload())->assertOk();
        $this->postJson('/pagar_cuotas', $this->payload(['revision' => $revision]))->assertConflict();
        $this->assertDatabaseCount('pago', 1);
    }
    public function test_mixed_receipt_history_and_private_proof(): void
    {
        $directory = storage_path('app/diagnostics/proof-test-'.Str::uuid());
        config(['filesystems.disks.expedientes.root' => $directory]);
        try {
            $response = $this->postJson('/pagar_cuotas', $this->payload(['monto_aplicado' => '10.00', 'partes' => [
                ['forma_pago' => 'efectivo', 'monto' => '4.00'],
                ['forma_pago' => 'transferencia', 'monto' => '6.00', 'referencia' => 'TEST-QR'],
            ]]))->assertOk();
            $code = $response->json('codigo_transaccion');
            $this->getJson('/historial-pagos')->assertOk();
            $this->getJson('/operaciones/'.$code.'/detalle')->assertOk()
                ->assertJsonPath('partes.0.puede_adjuntar', false)->assertJsonPath('partes.1.puede_adjuntar', true)
                ->assertJsonPath('partes.1.referencia', 'TEST-QR');
            $this->postJson('/operaciones/'.$code.'/comprobante', ['indice' => 0, 'imagen' => \Illuminate\Http\UploadedFile::fake()->image('cash.png')])->assertUnprocessable();
            $this->postJson('/operaciones/'.$code.'/comprobante', ['indice' => 1, 'imagen' => \Illuminate\Http\UploadedFile::fake()->image('proof.png')])->assertOk();
            $this->get('/operaciones/'.$code.'/comprobante/1')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->getJson('/operaciones/'.$code.'/detalle')->assertOk()->assertJsonPath('partes.1.puede_adjuntar', false);
            $this->get('/operaciones/'.$code.'/comprobante/0')->assertNotFound();
            $this->flushSession();
            $this->actingAs($this->fixture['users']['cajero_b']);
            $this->getJson('/operaciones/'.$code.'/detalle')->assertOk()->assertJsonPath('partes.1.puede_adjuntar', false);
            $this->postJson('/operaciones/'.$code.'/comprobante', ['indice' => 1, 'imagen' => \Illuminate\Http\UploadedFile::fake()->image('proof.png')])->assertForbidden();
        } finally {
            \Illuminate\Support\Facades\File::deleteDirectory($directory);
        }
    }

    public function test_failure_after_financial_write_rolls_back_entire_operation(): void
    {
        try {
            app(\App\Services\Finance\Operations::class)->run(new \Illuminate\Http\Request(['clave_operacion' => 'failure-test-operation']), 'test', function ($id) {
                DB::table('cuota')->where('id', $this->fixture['installment'])->update(['capital_pagado' => 50]);
                DB::table('movimientos_caja')->insert(['id_operacion' => $id, 'id_caja' => $this->fixture['cash'], 'id_usuario' => auth()->id(), 'monto' => 50, 'tipo_movimiento' => 'ingreso', 'descripcion' => 'Test rollback', 'fecha' => now()]);
                throw new \RuntimeException('Simulated persistence failure');
            });
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated persistence failure', $e->getMessage());
        }
        $this->assertDatabaseMissing('movimientos_caja', ['descripcion' => 'Test rollback']);
        $this->assertDatabaseCount('pago', 0);
        $this->assertDatabaseCount('operaciones_financieras', 0);
        $this->assertDatabaseHas('cuota', ['id' => $this->fixture['installment'], 'capital_pagado' => 0, 'interes_pagado' => 0]);
    }

}
