<?php

namespace Tests\Feature;

use App\Services\Finance\CashSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\DiagnosticFixture;
use Tests\TestCase;

class MovementHistoryTest extends TestCase
{
    use RefreshDatabase;

    private array $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-09-24 12:00:00'));
        $this->fixture = DiagnosticFixture::seed();
        $this->actingAs($this->fixture['users']['cajero_a']);
    }

    private function grantReversal(): void
    {
        DB::table('permiso_rol')->insert(['id_rol' => $this->fixture['users']['cajero_a']->id_rol,
            'id_permiso' => DB::table('permiso')->where('nombre', 'anular_finanzas')->value('id')]);
    }

    private function filters(array $extra = []): string
    {
        return '?'.http_build_query($extra + ['fecha_inicio' => '2026-09-01', 'fecha_final' => '2026-09-30', 'criterio' => 'users.name', 'buscar' => '']);
    }

    private function movement(string $type): int
    {
        $url = $type === 'ingreso' ? '/save_ingreso' : '/save_gasto';
        $this->postJson($url, ['id_caja' => $this->fixture['cash'], 'monto' => '10.00', 'descripcion' => 'Movimiento mixto',
            'partes' => [['forma_pago' => 'efectivo', 'monto' => '4.00'], ['forma_pago' => 'transferencia', 'monto' => '6.00', 'referencia' => 'QR-HISTORY']],
        ])->assertOk();
        return DB::table($type)->max('id');
    }

    public function test_all_history_lists_expose_operation_details_and_filter_totals(): void
    {
        $this->movement('ingreso');
        $this->movement('egreso');
        foreach ([['ingreso', 'ingresos', 'totalIngresos', 'historial_ingresos_listado_general'],
            ['ingreso', 'ingresos', 'totalIngresos', 'historial_ingresos_listado_caja'],
            ['egreso', 'gastos', 'totalGastos', 'historial_gastos_listado_general'],
            ['egreso', 'egresos', 'totalEgresos', 'historial_egresos_listado_caja']] as [$table, $list, $total, $route]) {
            $result = $this->getJson('/'.$route.$this->filters())->assertOk()->assertJsonPath($total, '10.00');
            $row = $result->json($list.'.data.0');
            $this->assertSame($this->fixture['users']['cajero_a']->id, $row['propietario_caja']);
            $this->assertSame($table, $row['tipo_operacion']);
            $this->getJson('/operaciones/'.$row['codigo_operacion'].'/detalle')->assertOk()
                ->assertJsonPath('partes.0.puede_adjuntar', false)->assertJsonPath('partes.1.puede_adjuntar', true);
            $this->getJson('/'.$route.$this->filters(['buscar' => 'does-not-exist']))->assertOk()
                ->assertJsonPath($total, '0.00')->assertJsonPath($list.'.total', 0);
        }
    }

    public function test_totals_span_pages_exclude_annulled_and_match_closed_box_scope(): void
    {
        for ($i = 0; $i < 52; $i++) DB::table('ingreso')->insert([
            'id_caja' => $this->fixture['cash'], 'id_usuario' => auth()->id(), 'monto' => '1.25',
            'descripcion' => 'Histórico', 'fecha' => '2026-09-24', 'estado' => $i === 51 ? 0 : 1,
        ]);
        $response = $this->getJson('/historial_ingresos_listado_general'.$this->filters(['page' => 2]))->assertOk()
            ->assertJsonPath('totalIngresos', '63.75')->assertJsonPath('ingresos.total', 52);
        $this->assertNull($response->json('ingresos.data.0.codigo_operacion'));
        DB::table('caja')->where('id', $this->fixture['cash'])->update(['estado' => 0]);
        $this->getJson('/historial_ingresos_listado_caja'.$this->filters())->assertOk()->assertJsonPath('totalIngresos', '0.00');
        $this->getJson('/historial_ingresos_listado_general'.$this->filters())->assertOk()->assertJsonPath('totalIngresos', '63.75');
        $this->getJson('/historial_ingresos_listado_general'.$this->filters(['criterio' => 'unknown_column']))->assertUnprocessable();
    }

    public function test_income_and_expense_reversals_require_permission_and_restore_mixed_amounts_once(): void
    {
        $income = $this->movement('ingreso');
        $expense = $this->movement('egreso');
        $this->postJson('/anular_ingreso_caja', ['id' => $income, 'motivo' => 'Error de registro'])->assertForbidden()
            ->assertJsonPath('message', 'Se requiere permiso de anulación.');
        $this->grantReversal();
        $this->postJson('/anular_ingreso_caja', ['id' => $income])->assertUnprocessable();
        foreach ([['/anular_ingreso_caja', $income], ['/anular_egreso_caja', $expense]] as [$url, $id]) {
            $payload = ['id' => $id, 'motivo' => 'Error de registro'];
            $this->postJson($url, $payload)->assertOk()->assertJsonPath('respuesta', 1);
            $this->postJson($url, $payload)->assertOk()->assertJsonPath('ya_anulada', true);
        }
        $this->assertSame('100.00', app(CashSummary::class)->forCash($this->fixture['cash'])['efectivo_esperado']);
        $this->getJson('/historial_ingresos_listado_general'.$this->filters())->assertOk()->assertJsonPath('totalIngresos', '0.00');
        $this->getJson('/historial_gastos_listado_general'.$this->filters())->assertOk()->assertJsonPath('totalGastos', '0.00');
    }

    public function test_reversal_reports_ownership_and_closed_cash_restrictions(): void
    {
        $id = $this->movement('ingreso');
        $this->grantReversal();
        $this->flushSession();
        $this->actingAs($this->fixture['users']['cajero_b']);
        $this->postJson('/anular_ingreso_caja', ['id' => $id, 'motivo' => 'Error de registro'])->assertForbidden()
            ->assertJsonPath('message', 'Solo el propietario puede operar su caja.');
        $this->flushSession();
        $this->actingAs($this->fixture['users']['cajero_a']);
        DB::table('caja')->where('id', $this->fixture['cash'])->update(['estado' => 0]);
        $this->postJson('/anular_ingreso_caja', ['id' => $id, 'motivo' => 'Error de registro'])->assertUnprocessable()
            ->assertJsonPath('message', 'La caja está cerrada.');
        $this->assertDatabaseHas('ingreso', ['id' => $id, 'estado' => 1]);
    }
}
