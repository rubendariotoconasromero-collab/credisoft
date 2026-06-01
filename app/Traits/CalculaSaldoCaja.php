<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Calcula el saldo de efectivo disponible en una caja abierta.
 *
 * Fórmula:
 *   saldo = monto_inicial
 *         + pagos de cuotas recibidos   (pago.estado = 1)
 *         + ingresos extras             (ingreso.estado = 1)
 *         + cobros administrativos      (pago_administrativo.estado = 0 → activo)
 *         − egresos extras              (egreso.estado = 1)
 *         − desembolsos realizados      (desembolso.estado = 0 → activo)
 */
trait CalculaSaldoCaja
{
    protected function calcularSaldoCaja(int $id_caja): float
    {
        $caja = DB::table('caja')->where('id', $id_caja)->first();

        if (!$caja) {
            return 0.0;
        }

        $pagos       = (float) (DB::table('pago')
                            ->where('id_caja', $id_caja)
                            ->where('estado', 1)
                            ->sum('monto_pago') ?? 0);

        $ingresos    = (float) (DB::table('ingreso')
                            ->where('id_caja', $id_caja)
                            ->where('estado', 1)
                            ->sum('monto') ?? 0);

        $pagoAdm     = (float) (DB::table('pago_administrativo')
                            ->where('id_caja', $id_caja)
                            ->where('estado', 0)   // 0 = activo, 1 = anulado
                            ->sum('monto') ?? 0);

        $egresos     = (float) (DB::table('egreso')
                            ->where('id_caja', $id_caja)
                            ->where('estado', 1)
                            ->sum('monto') ?? 0);

        $desembolsos = (float) (DB::table('desembolso')
                            ->where('id_caja', $id_caja)
                            ->where('estado', 0)   // 0 = activo, 1 = anulado
                            ->sum('monto') ?? 0);

        return (float) $caja->monto_inicial
             + $pagos
             + $ingresos
             + $pagoAdm
             - $egresos
             - $desembolsos;
    }
}
