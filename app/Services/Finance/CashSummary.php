<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;

final class CashSummary
{
    /** Source rows, not audit movements, determine balances to avoid counting twice. */
    public function forCash(int $id): array
    {
        $cash = DB::table('caja')->find($id);
        abort_unless($cash, 404, 'Caja no encontrada.');
        $opening = Money::cents($cash->monto_inicial);
        $physical = $opening;
        $methods = array_fill_keys(['efectivo', 'deposito', 'transferencia', 'sin_clasificar'], ['ingresos' => 0, 'egresos' => 0]);
        $internal = ['ingresos' => 0, 'egresos' => 0];
        $sources = [
            ['pago', 'monto_pago', 1, 'ingresos'],
            ['ingreso', 'monto', 1, 'ingresos'],
            ['pago_administrativo', 'monto', 0, 'ingresos'],
            ['egreso', 'monto', 1, 'egresos'],
            ['desembolso', 'monto', 0, 'egresos'],
        ];
        foreach ($sources as [$table, $column, $active, $direction]) {
            foreach (DB::table($table)->where('id_caja', $id)->where('estado', $active)->whereNull('id_operacion')->cursor() as $row) {
                $amount = Money::cents($row->$column);
                // These legacy tables represented physical operations and had no payment-method field.
                $method = property_exists($row, 'forma_pago') ? PaymentMethod::historical($row->forma_pago) : 'efectivo';
                $description = mb_strtolower($row->descripcion ?? '');
                $isInternal = ($table === 'ingreso' && (str_contains($description, 'transferencia desde bóveda') || str_contains($description, 'transferencia desde boveda')))
                    || ($table === 'egreso' && (str_contains($description, 'transferencia a bóveda') || str_contains($description, 'transferencia a boveda')));
                if ($isInternal) $internal[$direction] += $amount;
                else $methods[$method][$direction] += $amount;
                if ($method === 'efectivo') $physical += $direction === 'ingresos' ? $amount : -$amount;
            }
        }
        foreach (DB::table('operaciones_financieras')->where('id_caja', $id)->whereNull('anulado_at')->whereNotNull('direccion')->get() as $operation) {
            $direction = $operation->direccion === 'ingreso' ? 'ingresos' : 'egresos';
            foreach (json_decode($operation->partes ?? '[]', true, 512, JSON_THROW_ON_ERROR) as $part) {
                $amount = Money::cents($part['monto']);
                $method = $part['forma_pago'];
                if ($operation->interno) $internal[$direction] += $amount;
                else $methods[$method][$direction] += $amount;
                if ($method === 'efectivo') $physical += $direction === 'ingresos' ? $amount : -$amount;
            }
        }
        $income = array_sum(array_column($methods, 'ingresos'));
        $expenses = array_sum(array_column($methods, 'egresos'));
        return [
            'id_caja' => $id,
            'monto_inicial' => Money::decimal($opening),
            'efectivo_esperado' => Money::decimal($physical),
            'total_ingresos' => Money::decimal($income),
            'total_egresos' => Money::decimal($expenses),
            'formas_pago' => array_map(fn ($totals) => array_map(Money::decimal(...), $totals), $methods),
            'transferencias_internas' => array_map(Money::decimal(...), $internal),
            'requiere_revision' => $methods['sin_clasificar']['ingresos'] !== 0 || $methods['sin_clasificar']['egresos'] !== 0
                || DB::table('pago_amortizacion')->where('id_caja', $id)->exists(),
        ];
    }
}
