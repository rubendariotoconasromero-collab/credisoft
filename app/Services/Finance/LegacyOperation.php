<?php
namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Reconstruct only verifiable source data during an explicitly requested reversal. */
final class LegacyOperation
{
    public function link(string $table, object $source): int
    {
        $rows = $table === 'pago' && $source->codigo_transaccion
            ? DB::table('pago')->where('codigo_transaccion', $source->codigo_transaccion)->get() : collect([$source]);
        $parts = []; $amount = 0; $before = []; $planId = null;
        foreach ($rows as $row) {
            abort_unless((int) $row->id_caja === (int) $source->id_caja && (int) $row->estado === (in_array($table, ['desembolso', 'pago_administrativo'], true) ? 0 : 1), 422, 'El histórico contiene estados o cajas incompatibles.');
            $value = Money::cents($table === 'pago' ? $row->monto_pago : $row->monto); $amount += $value;
            $method = $table === 'pago' ? PaymentMethod::historical($row->forma_pago) : 'efectivo';
            abort_if($method === 'sin_clasificar', 422, 'Forma histórica no identificada. Requiere conciliación.');
            $parts[] = ['forma_pago' => $method, 'monto' => Money::decimal($value), 'entregado' => Money::decimal($value), 'referencia' => 'Histórico: no registrada'];
            if ($table === 'pago') {
                $q = DB::table('cuota')->find($row->id_cuota); $planId ??= $q->id_plan_pago;
                abort_unless((int) $q->id_plan_pago === (int) $planId, 422, 'La transacción histórica mezcla créditos.');
            }
        }
        if ($table === 'pago') {
            $plan = DB::table('plan_pago')->find($planId);
            $allIds = DB::table('cuota')->where('id_plan_pago', $planId)->pluck('id');
            abort_if(DB::table('pago')->whereIn('id_cuota', $allIds)->where('estado', 1)->whereNotIn('id', $rows->pluck('id'))
                ->where(fn ($q) => $q->where('id', '>', $rows->min('id'))->orWhereDate('fecha_pago', '>', $rows->min('fecha_pago')))->exists(), 422, 'Existen pagos posteriores en el crédito.');
            foreach ($rows->groupBy('id_cuota') as $quotaId => $payments) {
                $q = DB::table('cuota')->find($quotaId); $values = ['id' => $q->id];
                foreach (['capital', 'interes', 'mora'] as $c) {
                    $field = $c === 'mora' ? 'mora_pagada' : $c.'_pagado';
                    $deduct = $payments->sum(fn ($p) => Money::cents($p->{'pago_'.$c}) + ($c === 'capital' ? 0 : Money::cents($p->{'monto_condonado_'.$c})));
                    $new = Money::cents($q->$field) - $deduct;
                    abort_if($new < 0, 422, 'Acumulados históricos inconsistentes. Requieren conciliación.');
                    $values[$field] = Money::decimal($new);
                }
                $values['estado'] = Money::cents($values['capital_pagado']) + Money::cents($values['interes_pagado']) + Money::cents($values['mora_pagada']) > 0 ? 3 : 1;
                $before['cuotas'][] = $values;
            }
            $lastCompleted = DB::table('pago')->join('cuota', 'cuota.id', '=', 'pago.id_cuota')->where('cuota.id_plan_pago', $planId)
                ->where('pago.estado', 1)->where('cuota.estado', 2)->whereNotIn('cuota.id', $rows->pluck('id_cuota'))->max('pago.fecha_pago');
            $before['plan'] = ['saldo_pendiente' => Money::decimal(Money::cents($plan->saldo_pendiente) + $rows->sum(fn ($p) => Money::cents($p->pago_capital))), 'estado' => 1, 'fecha_ultima_amortizacion' => $lastCompleted];
        } elseif (in_array($table, ['desembolso', 'pago_administrativo'], true)) {
            $planId = $source->id_plan_pago;
            $before = ['plan' => [$table === 'desembolso' ? 'desembolso' : 'pago_administrativo' => 1]];
            if ($table === 'desembolso') $before['solicitud'] = ['desembolso' => 0];
        } else {
            abort_if(str_contains(mb_strtolower($source->descripcion), 'transferencia'), 422, 'Transferencia histórica sin vínculo verificable. Requiere conciliación antes de anular.');
        }
        $type = match ($table) { 'pago' => 'cobro', 'pago_administrativo' => 'administrativo', default => $table };
        $id = DB::table('operaciones_financieras')->insertGetId(['codigo' => (string) Str::uuid(), 'id_usuario' => $source->id_usuario,
            'clave' => 'historico-'.$table.'-'.$source->id, 'huella' => hash('sha256', $table.':'.$source->id), 'tipo' => $type,
            'id_caja' => $source->id_caja, 'id_plan_pago' => $planId, 'fecha_efectiva' => $table === 'pago' ? $source->fecha_pago : substr($source->fecha, 0, 10),
            'direccion' => in_array($table, ['desembolso', 'egreso'], true) ? 'salida' : 'ingreso', 'monto' => Money::decimal($amount),
            'partes' => json_encode($parts, JSON_THROW_ON_ERROR), 'antes' => json_encode($before, JSON_THROW_ON_ERROR),
            'created_at' => $source->created_at ?? $source->fecha ?? $source->fecha_pago, 'updated_at' => now()]);
        DB::table($table)->whereIn('id', $rows->pluck('id'))->update(['id_operacion' => $id]);
        return $id;
    }
}
