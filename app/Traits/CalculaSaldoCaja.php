<?php

namespace App\Traits;

use App\Services\Finance\CashSummary;

trait CalculaSaldoCaja
{
    protected function calcularSaldoCaja(int $id_caja): float
    {
        // El saldo operativo incluye todas las formas de pago. El efectivo físico
        // se conserva por separado en efectivo_esperado para el arqueo.
        return (float) app(CashSummary::class)->forCash($id_caja)['saldo_disponible'];
    }
}
