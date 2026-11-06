<?php

namespace Tests\Feature;

use App\Services\Finance\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\DiagnosticFixture;
use Tests\TestCase;

class CreditExtractTest extends TestCase
{
    use RefreshDatabase;

    public function test_extract_reconciles_payment_condonation_and_pdf(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-09-24 12:00:00'));
        $fixture = DiagnosticFixture::seed();
        $user = $fixture['users']['cajero_a'];
        $this->actingAs($user);
        DB::table('plan_pago')->where('id', $fixture['plan'])->update(['desembolso' => 0]);
        foreach (['condonar', 'reportes'] as $name) {
            $permission = DB::table('permiso')->where('nombre', $name)->value('id')
                ?? DB::table('permiso')->insertGetId(['nombre' => $name, 'descripcion' => $name, 'estado' => 1]);
            DB::table('permiso_rol')->insert(['id_rol' => $user->id_rol, 'id_permiso' => $permission]);
        }

        $this->postJson('/pagar_cuotas', [
            'id_plan_pago' => $fixture['plan'], 'fecha_pago' => '2026-09-24',
            'modalidad_pago' => 'parcial', 'cuotas' => [['id_cuota' => $fixture['installment']]],
            'monto_recibido' => '100.00', 'condonacion_interes' => '10.00',
            'motivo_condonacion_interes' => 'Acuerdo documentado',
            'partes' => [
                ['forma_pago' => 'efectivo', 'monto' => '60.00'],
                ['forma_pago' => 'transferencia', 'monto' => '40.00', 'referencia' => 'QR-EXTRACTO'],
            ],
        ])->assertOk();

        $extract = $this->getJson('/get_detalle_credito_extracto?id_plan_pago='.$fixture['plan'])
            ->assertOk()->json();
        $quota = $extract['cuotas'][0];
        $this->assertSame('10.00', $quota['total_condonado']);
        $this->assertSame('100.00', $quota['total_cobrado']);
        $this->assertSame('100.00', $extract['totales']['cobrado']);
        $this->assertSame('QR-EXTRACTO', $quota['pagos'][0]['partes_operacion'][1]['referencia']);
        $this->assertTrue($quota['desglose_cobros_interes_completo']);
        foreach (['ordinario' => 'interes_devengado_neto', 'moratorio' => 'interes_moratorio_neto'] as $kind => $pendingField) {
            $this->assertSame(
                Money::cents($quota['interes_'.$kind.'_cobrado'])
                    + Money::cents($quota['interes_'.$kind.'_condonado'])
                    + Money::cents($quota[$pendingField]),
                Money::cents($quota['interes_'.$kind.'_total_extracto'])
            );
        }
        $this->assertSame(Money::cents($quota['total']) + Money::cents($quota['variacion_programado'])
            - Money::cents($quota['total_condonado']), Money::cents($quota['exigible_neto']));
        $this->assertSame(Money::cents($quota['total_cobrado']) + Money::cents($quota['total_a_pagar']),
            Money::cents($quota['exigible_neto']));

        $pdf = $this->get('/rep_extracto_credito?id_plan_pago='.$fixture['plan'])
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->assertSame(1, preg_match_all('/\/Type\s*\/Page\b/', $pdf->getContent()));

        DB::table('pago')->where('id', $quota['pagos'][0]['id'])->update(['id_operacion' => null]);
        $historic = $this->getJson('/get_detalle_credito_extracto?id_plan_pago='.$fixture['plan'])
            ->assertOk()->json('cuotas.0');
        $this->assertFalse($historic['desglose_cobros_interes_completo']);
        $this->assertNull($historic['interes_ordinario_total_extracto']);
        $this->assertNull($historic['interes_moratorio_total_extracto']);
        $historicPdf = $this->get('/rep_extracto_credito?id_plan_pago='.$fixture['plan'])
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame(1, preg_match_all('/\/Type\s*\/Page\b/', $historicPdf->getContent()));
    }
}
