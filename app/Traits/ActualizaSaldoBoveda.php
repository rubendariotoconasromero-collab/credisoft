<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Centraliza la actualización del saldo de bóveda.
 * Usar en todo controlador que registre o anule transacciones de dinero.
 */
trait ActualizaSaldoBoveda
{
    /**
     * Suma o resta $monto al saldo actual de bóveda.
     *  +monto → ingresa dinero a la empresa (cobros, ingresos)
     *  -monto → sale dinero de la empresa (gastos, anulaciones de cobro)
     */
    protected function actualizarSaldoBoveda(float $monto): void
    {
        $id_boveda = DB::table('boveda')->orderBy('id', 'desc')->value('id');

        if (!$id_boveda) {
            return;
        }

        // DB::raw con el valor ya casteado evita inyección y problemas de punto flotante
        $operacion = $monto >= 0
            ? 'saldo_actual + ' . abs($monto)
            : 'saldo_actual - ' . abs($monto);

        DB::table('boveda')
            ->where('id', $id_boveda)
            ->update(['saldo_actual' => DB::raw($operacion)]);
    }
}
