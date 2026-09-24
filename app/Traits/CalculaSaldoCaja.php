<?php

namespace App\Traits;

use App\Services\Finance\CashSummary;

trait CalculaSaldoCaja
{
    protected function calcularSaldoCaja(int $id_caja): float
    {
        // Compatibility for legacy consumers; aggregation itself uses exact centavos.
        return (float) app(CashSummary::class)->forCash($id_caja)['efectivo_esperado'];
    }
}