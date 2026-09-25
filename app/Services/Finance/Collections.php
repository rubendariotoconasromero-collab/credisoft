<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

final class Collections
{
    public function collect(Request $request): array
    {
        $request->validate(['id_plan_pago' => 'required|integer', 'cuotas' => 'required|array|min:1',
            'cuotas.*.id_cuota' => 'required|integer|distinct', 'fecha_pago' => 'required|date_format:Y-m-d|before_or_equal:today',
            'modalidad_pago' => 'required|in:completo,parcial,solo_interes,solo_mora,interes_mora']);
        return app(Operations::class)->run($request, 'cobro', function ($id, $code) use ($request) {
            $cash = Operations::cash($request->id_caja);
            \App\Services\CarteraAccess::assertRecord('plan_pago', $request->id_plan_pago);
            $plan = DB::table('plan_pago')->where('id', $request->id_plan_pago)->lockForUpdate()->first();
            abort_unless($plan && (int) $plan->estado === 1 && (int) $plan->desembolso === 0, 422, 'El crédito no está activo y desembolsado.');
            abort_unless(in_array(mb_strtolower($plan->moneda), ['bs', 'bob', 'bolivianos'], true), 422, 'La caja opera en bolivianos; no se pueden mezclar monedas.');
            abort_if($request->fecha_pago < $plan->fecha_inicio, 422, 'El pago no puede preceder al inicio del crédito.');
            if ($request->fecha_pago !== today()->toDateString()) abort_unless(Auth::user()->tienePermiso('pagos_retroactivos'), 403, 'Se requiere permiso para fechas anteriores.');
            // Backdating belongs to today's open cash session; never edit a past cash close.
            $all = DB::table('cuota')->where('id_plan_pago', $plan->id)->orderBy('numero')->get();
            abort_if(DB::table('pago')->whereIn('id_cuota', $all->pluck('id'))->where('estado', 1)->whereDate('fecha_pago', '>', $request->fecha_pago)->exists(), 422, 'Existen pagos posteriores. No se puede insertar un pago que cambie su cálculo.');
            $ids = array_map('intval', array_column($request->cuotas, 'id_cuota'));
            $pending = $all->filter(fn ($q) => in_array((int) $q->estado, [1, 3], true))->pluck('id')->values()->all();
            abort_unless($ids === array_slice($pending, 0, count($ids)), 422, 'Seleccione cuotas consecutivas desde la primera pendiente.');
            $calculation = app(Debt::class)->calculate($plan->id, $request->fecha_pago);
            abort_if($request->filled('revision') && !hash_equals($calculation['revision'], $request->revision), 409, 'La deuda cambió. Actualice el detalle del crédito antes de confirmar.');
            $calculated = $calculation['cuotas']->whereIn('id', $ids);
            $components = match ($request->modalidad_pago) {
                'solo_interes' => ['interes'], 'solo_mora' => ['mora'], 'interes_mora' => ['mora', 'interes'], default => ['mora', 'interes', 'capital'],
            };
            $fields = ['capital' => 'capital_neto', 'interes' => 'interes_acumulado_neto', 'mora' => 'mora_fija_neta'];
            $condone = ['interes' => Money::cents($request->input('condonacion_interes', 0)), 'mora' => Money::cents($request->input('condonacion_mora', 0))];
            foreach ($condone as $component => $value) {
                abort_if($value < 0 || ($value > 0 && !in_array($component, $components, true)), 422, 'Condonación incompatible con la modalidad.');
                if ($value > 0) {
                    abort_unless(Auth::user()->tienePermiso('condonar'), 403, 'Se requiere permiso de condonación.');
                    $request->validate(['motivo_condonacion_'.$component => 'required|string|min:3|max:500']);
                    abort_if($value > $calculated->sum(fn ($q) => Money::cents($q->{$fields[$component]})), 422, 'La condonación supera la deuda.');
                }
            }
            $maximum = $calculated->sum(fn ($q) => array_sum(array_map(fn ($c) => Money::cents($q->{$fields[$c]}), $components))) - array_sum($condone);
            $input = PaymentParts::input($request, $request->input('monto_recibido', 0));
            $received = array_sum(array_map(fn ($p) => Money::cents($p['monto'] ?? null), $input));
            $applied = $request->filled('monto_aplicado') ? Money::cents($request->monto_aplicado) : min($received, $maximum);
            abort_if($applied < 0 || $applied > $maximum || $applied + array_sum($condone) <= 0, 422, 'Importe aplicado inválido para la deuda actual.');
            $parts = PaymentParts::prepare($input, $applied, true);
            $before = ['cuotas' => $all->whereIn('id', $ids)->map(fn ($q) => ['id' => $q->id, 'capital_pagado' => $q->capital_pagado, 'interes_pagado' => $q->interes_pagado, 'mora_pagada' => $q->mora_pagada, 'estado' => $q->estado])->values()->all(),
                'plan' => ['saldo_pendiente' => $plan->saldo_pendiente, 'estado' => $plan->estado, 'fecha_ultima_amortizacion' => $plan->fecha_ultima_amortizacion]];
            Operations::record($id, $cash->id, 'ingreso', $parts, ['id_plan_pago' => $plan->id, 'fecha_efectiva' => $request->fecha_pago, 'antes' => json_encode($before, JSON_THROW_ON_ERROR)]);
            $remaining = $applied; $completed = false; $details = [];
            foreach ($calculated as $q) {
                $paid = ['capital' => 0, 'interes' => 0, 'mora' => 0]; $forgiven = ['interes' => 0, 'mora' => 0];
                $debt = array_map(fn ($field) => Money::cents($q->$field), $fields);
                foreach ($components as $c) {
                    if (isset($condone[$c])) { $forgiven[$c] = min($condone[$c], $debt[$c]); $condone[$c] -= $forgiven[$c]; $debt[$c] -= $forgiven[$c]; }
                    $paid[$c] = min($remaining, $debt[$c]); $remaining -= $paid[$c]; $debt[$c] -= $paid[$c];
                }
                if (array_sum($paid) + array_sum($forgiven) === 0) continue;
                $state = array_sum($debt) === 0 ? 2 : 3; $completed |= $state === 2;
                $row = ['codigo_transaccion' => $code, 'id_operacion' => $id, 'id_cuota' => $q->id, 'id_caja' => $cash->id, 'id_usuario' => Auth::id(),
                    'fecha_pago' => $request->fecha_pago, 'estado' => 1, 'tipo_pago' => $request->modalidad_pago,
                    'forma_pago' => count($parts['partes']) === 1 ? $parts['partes'][0]['forma_pago'] : 'mixto',
                    'monto_pago' => Money::decimal(array_sum($paid)), 'monto_condonado' => Money::decimal(array_sum($forgiven)),
                    'motivo_condonacion' => implode(' | ', array_filter([$request->motivo_condonacion_interes, $request->motivo_condonacion_mora])), 'created_at' => now(), 'updated_at' => now()];
                $update = ['estado' => $state, 'updated_at' => now()];
                foreach ($paid as $c => $value) { $field = $c === 'mora' ? 'mora_pagada' : $c.'_pagado'; $row['pago_'.$c] = Money::decimal($value); $update[$field] = Money::decimal(Money::cents($q->$field) + $value + ($forgiven[$c] ?? 0)); }
                foreach ($forgiven as $c => $value) $row['monto_condonado_'.$c] = Money::decimal($value);
                DB::table('pago')->insert($row); DB::table('cuota')->where('id', $q->id)->update($update);
                $details[] = ['cuota' => $q->numero, 'id_cuota' => $q->id,
                    'desglose_interes' => InterestBreakdown::allocate($q, $paid['interes'], $forgiven['interes']),
                    'interes_ordinario_generado' => $q->interes_ordinario_generado,
                    'interes_moratorio_generado' => $q->interes_moratorio_generado,
                    'pagado' => $row['monto_pago'], 'capital' => $row['pago_capital'], 'interes' => $row['pago_interes'], 'mora' => $row['pago_mora'], 'condonado' => $row['monto_condonado']];
            }
            abort_if($remaining !== 0 || array_sum($condone) !== 0, 422, 'No se pudo distribuir todo el importe.');
            $state = DB::table('cuota')->where('id_plan_pago', $plan->id)->whereIn('estado', [1, 3])->exists() ? 1 : 2;
            $capitalPending = DB::table('cuota')->where('id_plan_pago', $plan->id)->get()->sum(fn ($q) => max(0, Money::cents($q->capital) - Money::cents($q->capital_pagado)));
            $updatePlan = ['estado' => $state, 'saldo_pendiente' => Money::decimal($capitalPending)];
            if ($completed) $updatePlan['fecha_ultima_amortizacion'] = $request->fecha_pago;
            DB::table('plan_pago')->where('id', $plan->id)->update($updatePlan);
            DB::table('movimientos_caja')->insert(['id_operacion' => $id, 'id_caja' => $cash->id, 'id_usuario' => Auth::id(), 'tipo_movimiento' => 'ingreso', 'monto' => $parts['monto'], 'descripcion' => 'Cobro '.$code, 'fecha' => now()]);
            return $parts + ['estado_plan' => $state, 'detalles' => $details, 'fecha_pago' => $request->fecha_pago];
        });
    }
}
