<?php

namespace Tests\Feature;

use App\Services\Finance\CashSummary;
use App\Services\Finance\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\DiagnosticFixture;
use Tests\TestCase;

class FinancialSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_integrated_totals_distinguish_physical_cash_and_internal_transfers(): void
    {
        $f = DiagnosticFixture::seed();
        foreach ([['efectivo', '100.01', 1], ['Depósito banco', '200.02', 1], ['transferencia - QR', '50.03', 1], ['efectivo', '900.00', 0]] as [$method, $amount, $state]) {
            DB::table('pago')->insert(['id_cuota' => $f['installment'], 'id_caja' => $f['cash'],
                'id_usuario' => $f['users']['cajero_a']->id, 'fecha_pago' => '2026-09-24',
                'monto_pago' => $amount, 'pago_capital' => $amount, 'pago_interes' => 0,
                'pago_mora' => 0, 'forma_pago' => $method, 'estado' => $state]);
        }
        DB::table('ingreso')->insert(['id_caja' => $f['cash'], 'id_usuario' => $f['users']['cajero_a']->id,
            'monto' => '50.00', 'descripcion' => 'Transferencia desde Bóveda', 'fecha' => '2026-09-24']);
        $summary = app(CashSummary::class)->forCash($f['cash']);
        $this->assertSame('250.01', $summary['efectivo_esperado']);
        $this->assertSame('500.06', $summary['saldo_disponible']);
        $this->assertSame('350.06', $summary['total_ingresos']);
        $this->assertSame('200.02', $summary['formas_pago']['deposito']['ingresos']);
        $this->assertSame('50.03', $summary['formas_pago']['transferencia']['ingresos']);
        $this->assertSame('50.00', $summary['transferencias_internas']['ingresos']);
        $this->assertFalse($summary['requiere_revision']);
    }

    public function test_unknown_method_is_reported_without_inventing_physical_cash(): void
    {
        $f = DiagnosticFixture::seed();
        DB::table('pago')->insert(['id_cuota' => $f['installment'], 'id_caja' => $f['cash'],
            'id_usuario' => $f['users']['cajero_a']->id, 'fecha_pago' => '2026-09-24',
            'monto_pago' => '40.00', 'pago_capital' => 40, 'pago_interes' => 0,
            'pago_mora' => 0, 'forma_pago' => 'Importado sin evidencia', 'estado' => 1]);
        $summary = app(CashSummary::class)->forCash($f['cash']);
        $this->assertSame('100.00', $summary['efectivo_esperado']);
        $this->assertSame('140.00', $summary['saldo_disponible']);
        $this->assertSame('40.00', $summary['total_ingresos']);
        $this->assertTrue($summary['requiere_revision']);
    }

    public function test_centavos_are_exact_and_excess_precision_is_rejected(): void
    {
        $this->assertSame('0.30', Money::decimal(Money::cents('0.10') + Money::cents('0.20')));
        $this->assertSame('-0.01', Money::decimal(Money::cents('-0.01')));
        $this->assertSame('9999999999.99', Money::decimal(Money::cents('9999999999.99')));
        $this->expectException(ValidationException::class);
        Money::cents('1.001');
    }

    public function test_diagnosis_does_not_write_financial_data(): void
    {
        DiagnosticFixture::seed();
        $writes = [];
        DB::listen(function ($query) use (&$writes) {
            if (preg_match('/^\s*(insert|update|delete|alter|drop|create)\b/i', $query->sql)) $writes[] = $query->sql;
        });
        $this->artisan('finanzas:diagnosticar')->assertSuccessful();
        $this->assertSame([], $writes);
    }
}
