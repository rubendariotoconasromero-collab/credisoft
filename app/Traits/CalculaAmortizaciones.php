<?php
namespace App\Traits;
trait CalculaAmortizaciones
{
    public function calcularAmortizacionesCuotas($id_plan_pago)
    {
        return app(\App\Services\Finance\Debt::class)->calculate((int) $id_plan_pago, request()->input('fecha_corte'));
    }
}
