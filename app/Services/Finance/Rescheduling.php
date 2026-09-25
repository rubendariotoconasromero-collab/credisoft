<?php

namespace App\Services\Finance;

use App\Services\CarteraAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

final class Rescheduling
{
    public function previewDraft(Request $request): array
    {
        $request->validate(['plan_pago' => 'required|array', 'plan_pago.id_plan_pago' => 'required|integer',
            'tipo_operacion' => 'required|in:REPROGRAMACION,REFINANCIAMIENTO', 'pago_intereses' => 'nullable|array',
            'pago_intereses.ids_cuotas_interes' => 'nullable|array', 'pago_intereses.ids_cuotas_mora' => 'nullable|array',
            'pago_intereses.ids_cuotas_interes.*' => 'integer|distinct', 'pago_intereses.ids_cuotas_mora.*' => 'integer|distinct']);
        $input = $request->plan_pago;
        CarteraAccess::assertRecord('plan_pago', $input['id_plan_pago']);
        $plan = DB::table('plan_pago')->find($input['id_plan_pago']);
        abort_unless($plan && (int) $plan->estado === 1 && (int) $plan->desembolso === 0, 422, 'Seleccione un crédito activo y desembolsado.');
        $credit = DB::table('solicitud')->find($plan->id_solicitud);
        $debt = app(Debt::class)->calculate($plan->id);
        $selection = $request->input('pago_intereses') ?? [];
        $idsInterest = !empty($selection['seleccionado']) ? array_map('intval', $selection['ids_cuotas_interes'] ?? []) : [];
        $idsFine = !empty($selection['seleccionado']) ? array_map('intval', $selection['ids_cuotas_mora'] ?? []) : [];
        abort_if(array_diff(array_merge($idsInterest, $idsFine), $debt['cuotas']->pluck('id')->all()), 422, 'Las cuotas seleccionadas no pertenecen al crédito.');
        $principal = $debt['cuotas']->whereIn('estado', [1, 3])->sum(fn ($q) => Money::cents($q->total_a_pagar)
            - (in_array($q->id, $idsInterest) ? Money::cents($q->interes_acumulado_neto) : 0)
            - (in_array($q->id, $idsFine) ? Money::cents($q->mora_fija_neta) : 0));
        $additional = $request->tipo_operacion === 'REFINANCIAMIENTO' ? Money::cents($request->input('monto_adicional', 0)) : 0;
        abort_if($additional < 0 || ($request->tipo_operacion === 'REFINANCIAMIENTO' && $additional === 0), 422, 'Ingrese un importe adicional positivo.');
        $principal += $additional;
        $terms = ['tasa' => $credit->tasa, 'tipo_tasa' => $credit->tipo_tasa, 'fecha_desembolso' => $debt['fecha_corte'],
            'fecha_primera_cuota' => $input['fecha_primera_cuota'] ?? null, 'lapso_capital' => $input['forma_pago_reprogramacion'] ?? null,
            'nro_cuotas' => $input['numero_cuotas_reprogramacion'] ?? null];
        return ['cuotas' => ReschedulingSchedule::make($terms, $principal), 'importe_solicitud' => Money::decimal($principal),
            'revision' => $debt['revision'], 'fecha_corte' => $debt['fecha_corte']];
    }

    public function register(Request $request, callable $copyAttachments): array
    {
        $request->validate(['tipo_operacion' => 'required|in:REPROGRAMACION,REFINANCIAMIENTO',
            'plan_pago' => 'required|array', 'plan_pago.id_solicitud' => 'required|integer',
            'plan_pago.forma_pago_reprogramacion' => 'required|in:Mensual,Quincenal,Semanal',
            'plan_pago.numero_cuotas_reprogramacion' => 'required|integer|min:1|max:600',
            'plan_pago.fecha_primera_cuota' => 'required|date_format:Y-m-d|after:today',
            'pago_intereses' => 'nullable|array', 'pago_intereses.ids_cuotas_interes' => 'nullable|array',
            'pago_intereses.ids_cuotas_mora' => 'nullable|array',
            'pago_intereses.ids_cuotas_interes.*' => 'integer|distinct', 'pago_intereses.ids_cuotas_mora.*' => 'integer|distinct']);
        return app(Operations::class)->run($request, 'solicitud_especial', function () use ($request, $copyAttachments) {
            $input = $request->plan_pago;
            $planId = $input['id_plan_pago'] ?? $input['id'] ?? 0;
            CarteraAccess::assertRecord('plan_pago', $planId);
            $plan = DB::table('plan_pago')->find($planId);
            self::assertReady((int) $planId);
            abort_unless($plan && (int) $plan->id_solicitud === (int) $input['id_solicitud'] && (int) $plan->estado === 1 && (int) $plan->desembolso === 0, 422, 'Seleccione un crédito activo y desembolsado.');
            $credit = DB::table('solicitud')->find($plan->id_solicitud);
            abort_unless(in_array(mb_strtolower($plan->moneda), ['bs', 'bob', 'bolivianos']), 422, 'Solo se admiten bolivianos.');
            $debt = app(Debt::class)->calculate($plan->id);
            abort_if($request->filled('revision') && !hash_equals($debt['revision'], (string) $request->revision), 409, 'La deuda cambió. Vuelva a consultar el crédito antes de reprogramar.');
            $quotas = $debt['cuotas'];
            $selection = $request->input('pago_intereses') ?? [];
            $selected = !empty($selection['seleccionado']);
            $idsInterest = $selected ? array_map('intval', $selection['ids_cuotas_interes'] ?? []) : [];
            $idsFine = $selected ? array_map('intval', $selection['ids_cuotas_mora'] ?? []) : [];
            abort_if(array_diff(array_merge($idsInterest, $idsFine), $quotas->pluck('id')->all()), 422, 'Las cuotas seleccionadas no pertenecen al crédito.');
            $rows = $quotas->filter(fn ($q) => in_array((int) $q->estado, [1, 3]))->map(fn ($q) => [
                'cuota' => (array) $q,
                'interes' => in_array($q->id, $idsInterest) ? Money::cents($q->interes_acumulado_neto) : 0,
                'mora' => in_array($q->id, $idsFine) ? Money::cents($q->mora_fija_neta) : 0,
            ])->values()->all();
            $interest = array_sum(array_column($rows, 'interes')); $fine = array_sum(array_column($rows, 'mora'));
            $forgivenInterest = $selected && !empty($selection['condonar_interes']) ? Money::cents($selection['monto_condonar_interes'] ?? 0) : 0;
            $forgivenFine = $selected && !empty($selection['condonar_mora']) ? Money::cents($selection['monto_condonar_mora'] ?? 0) : 0;
            abort_if($forgivenInterest < 0 || $forgivenFine < 0 || $forgivenInterest > $interest || $forgivenFine > $fine, 422, 'La condonación supera la deuda seleccionada.');
            if ($forgivenInterest + $forgivenFine > 0) {
                abort_unless(Auth::user()->tienePermiso('condonar'), 403, 'Se requiere permiso de condonación.');
                $request->validate(['pago_intereses.motivo_condonacion' => 'required|string|min:3|max:255']);
            }
            $additional = $request->tipo_operacion === 'REFINANCIAMIENTO' ? Money::cents($request->input('monto_adicional', 0)) : 0;
            abort_if($additional < 0 || ($request->tipo_operacion === 'REFINANCIAMIENTO' && $additional === 0), 422, 'El refinanciamiento requiere un importe adicional positivo.');
            $principal = $quotas->whereIn('estado', [1, 3])->sum(fn ($q) => Money::cents($q->total_a_pagar)) - $interest - $fine + $additional;
            abort_unless($principal > 0, 422, 'No hay saldo para reprogramar.');
            $newCredit = (array) $credit;
            unset($newCredit['id']);
            $newCredit = array_replace($newCredit, ['id_usuario' => $credit->id_usuario,
                'id_solicitud_origen' => $credit->id, 'tipo_solicitud' => $additional > 0 ? 'Refinanciamiento' : 'Reprogramacion',
                'cantidad_reprogramaciones' => $credit->cantidad_reprogramaciones + ($additional === 0 ? 1 : 0),
                'cantidad_refinanciamientos' => $credit->cantidad_refinanciamientos + ($additional > 0 ? 1 : 0),
                'monto_refinanciamiento' => Money::decimal($additional), 'importe_solicitud' => Money::decimal($principal),
                'monto_pago_adm' => AdministrativeFee::decimalFromAmount(Money::decimal($additional > 0 ? $additional : $principal)),
                'lapso_capital' => $input['forma_pago_reprogramacion'], 'nro_cuotas' => $input['numero_cuotas_reprogramacion'],
                'fecha' => today()->toDateString(), 'fecha_desembolso' => today()->toDateString(),
                'fecha_primera_cuota' => $input['fecha_primera_cuota'], 'estado' => 1, 'desembolso' => 0,
                'observacion' => null, 'created_at' => now(), 'updated_at' => now()]);
            $newId = DB::table('solicitud')->insertGetId($newCredit);
            $copyAttachments($credit->id, $newId);
            $orderId = null;
            if ($interest + $fine > 0) {
                $orderId = DB::table('orden_pago_reprogramaciones')->insertGetId([
                    'id_solicitud_nueva' => $newId, 'id_plan_pago_origen' => $plan->id,
                    'monto_interes_calculado' => Money::decimal($interest), 'monto_mora_calculado' => Money::decimal($fine),
                    'monto_condonado_interes' => Money::decimal($forgivenInterest), 'monto_condonado_mora' => Money::decimal($forgivenFine),
                    'se_condono_interes' => $forgivenInterest > 0, 'se_condono_mora' => $forgivenFine > 0,
                    'motivo_condonacion' => $selection['motivo_condonacion'] ?? null,
                    'total_a_pagar' => Money::decimal($interest + $fine - $forgivenInterest - $forgivenFine),
                    'ids_cuotas_afectadas' => json_encode(array_values(array_unique(array_merge($idsInterest, $idsFine)))),
                    'estado' => 0, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            DB::table('reprogramaciones_financieras')->insert(['id_solicitud' => $newId, 'id_plan_origen' => $plan->id,
                'id_orden' => $orderId, 'fecha_corte' => $debt['fecha_corte'], 'id_usuario' => Auth::id(),
                'snapshot' => json_encode(['principal' => $principal, 'rows' => $rows, 'plan_estado' => $plan->estado,
                    'solicitud_estado' => $credit->estado, 'revision' => $debt['revision']], JSON_THROW_ON_ERROR),
                'created_at' => now(), 'updated_at' => now()]);
            DB::table('plan_pago')->where('id', $plan->id)->update(['estado' => 5]);
            return ['message' => 'Solicitud registrada con deuda calculada por el servidor.', 'solicitud_id' => $newId, 'importe_solicitud' => Money::decimal($principal)];
        });
    }

    public function preview(Request $request): array
    {
        $request->validate(['id_solicitud' => 'required|integer', 'datos_actualizados' => 'required|array']);
        CarteraAccess::assertRecord('solicitud', $request->id_solicitud);
        $record = DB::table('reprogramaciones_financieras')->where('id_solicitud', $request->id_solicitud)->first();
        abort_unless($record && $record->estado === 'pendiente', 422, 'La solicitud no tiene un cálculo verificable pendiente.');
        $snapshot = json_decode($record->snapshot, true, 512, JSON_THROW_ON_ERROR);
        $credit = DB::table('solicitud')->find($record->id_solicitud);
        $terms = array_replace((array) $credit, $request->datos_actualizados);
        $terms['fecha_desembolso'] = substr($terms['fecha_desembolso'], 0, 10);
        $terms['fecha_primera_cuota'] = substr($terms['fecha_primera_cuota'], 0, 10);
        abort_if($terms['fecha_desembolso'] < $record->fecha_corte, 422, 'El nuevo plan no puede comenzar antes del corte de la reprogramación.');
        $terms['importe_solicitud'] = Money::decimal($snapshot['principal']);
        $rows = ReschedulingSchedule::make($terms, $snapshot['principal']);
        return ['terms' => $terms, 'cuotas' => $rows, 'importe_solicitud' => $terms['importe_solicitud'], 'fecha_corte' => $record->fecha_corte];
    }

    public function approve(Request $request): array
    {
        abort_unless(Auth::user()->role_name === 'Administrador', 403, 'Solo un administrador puede aprobar la reprogramación.');
        return app(Operations::class)->run($request, 'aprobar_reprogramacion', function () use ($request) {
            $preview = $this->preview($request);
            $record = DB::table('reprogramaciones_financieras')->where('id_solicitud', $request->id_solicitud)->first();
            $credit = DB::table('solicitud')->find($record->id_solicitud);
            abort_unless((int) $credit->estado === 1 && !trim($credit->observacion ?? ''), 422, 'La solicitud debe estar pendiente y sin observaciones.');
            abort_unless((int) DB::table('plan_pago')->where('id', $record->id_plan_origen)->value('estado') === 5, 422, 'El crédito original no está reservado para esta solicitud.');
            $terms = $preview['terms']; $rows = $preview['cuotas'];
            $updates = array_intersect_key($terms, array_flip(['importe_solicitud', 'lapso_capital', 'nro_cuotas', 'fecha_desembolso', 'fecha_primera_cuota', 'tasa', 'tipo_tasa']));
            DB::table('solicitud')->where('id', $credit->id)->update($updates + ['estado' => 2, 'desembolso' => $credit->tipo_solicitud === 'Refinanciamiento' ? 0 : 1]);
            DB::table('solicitud')->where('id', $credit->id_solicitud_origen)->update(['estado' => 3]);
            DB::table('plan_pago')->where('id', $record->id_plan_origen)->update(['estado' => 0]);
            DB::table('cuota')->where('id_plan_pago', $record->id_plan_origen)->whereIn('estado', [1, 3])->update(['estado' => 0]);
            $planId = DB::table('plan_pago')->insertGetId(['id_solicitud' => $credit->id, 'fecha_inicio' => $terms['fecha_desembolso'],
                'fecha_fin' => end($rows)['fecha'], 'moneda' => $credit->moneda, 'lapso_capital' => $terms['lapso_capital'],
                'nro_cuotas' => $terms['nro_cuotas'], 'tasa' => $terms['tasa'], 'estado' => 1,
                'desembolso' => $credit->tipo_solicitud === 'Refinanciamiento' ? 1 : 0,
                'saldo_pendiente' => $preview['importe_solicitud'],
                'total_pagar' => Money::decimal(array_sum(array_map(fn ($row) => Money::cents($row['total_cuota']), $rows))), 'fecha_registro' => now()]);
            foreach ($rows as $row) DB::table('cuota')->insert(['id_plan_pago' => $planId, 'numero' => $row['nro'], 'fecha' => $row['fecha'],
                'capital' => $row['capital'], 'interes' => $row['interes'], 'saldo_capital' => $row['saldo_capital'], 'total' => $row['total_cuota'], 'estado' => 1]);
            DB::table('reprogramaciones_financieras')->where('id', $record->id)->update(['id_plan_nuevo' => $planId, 'estado' => 'aprobada', 'updated_at' => now()]);
            if ($record->id_orden) DB::table('orden_pago_reprogramaciones')->where('id', $record->id_orden)->update(['estado' => 1, 'updated_at' => now()]);
            return ['message' => 'Reprogramación aprobada. La orden pendiente debe liquidarse en caja antes de operar el nuevo crédito.', 'id_plan_pago' => $planId];
        });
    }

    public static function assertReady(int $planId): void
    {
        $record = DB::table('reprogramaciones_financieras')->where('id_plan_nuevo', $planId)->where('estado', 'aprobada')->first();
        if ($record && $record->id_orden) abort_unless((int) DB::table('orden_pago_reprogramaciones')->where('id', $record->id_orden)->value('estado') === 2, 422, 'Primero liquide la orden de reprogramación en Caja > Órdenes de pago.');
    }

    public function collect(Request $request): array
    {
        $request->validate(['id_orden' => 'required|integer']);
        return app(Operations::class)->run($request, 'cobro', function ($operation, $code) use ($request) {
            $cash = Operations::cash($request->integer('id_caja') ?: null);
            $record = DB::table('reprogramaciones_financieras')->where('id_orden', $request->id_orden)->first();
            abort_unless($record && $record->estado === 'aprobada', 422, 'La orden requiere un cálculo financiero verificable y aprobación.');
            CarteraAccess::assertRecord('plan_pago', $record->id_plan_origen);
            CarteraAccess::assertRecord('plan_pago', $record->id_plan_nuevo);
            $order = DB::table('orden_pago_reprogramaciones')->find($record->id_orden);
            abort_unless((int) $order->estado === 1, 422, 'La orden ya fue cobrada o no está disponible.');
            $snapshot = json_decode($record->snapshot, true, 512, JSON_THROW_ON_ERROR);
            $amount = Money::cents($order->total_a_pagar);
            $parts = PaymentParts::prepare(PaymentParts::input($request, $order->total_a_pagar), $amount, true);
            $before = ['cuotas' => [], 'reprogramacion' => $record->id, 'id_plan_nuevo' => $record->id_plan_nuevo];
            $forgiveInterest = Money::cents($order->monto_condonado_interes); $forgiveFine = Money::cents($order->monto_condonado_mora); $details = [];
            foreach ($snapshot['rows'] as $row) {
                if (!$row['interes'] && !$row['mora']) continue;
                $q = (object) $row['cuota']; $source = DB::table('cuota')->find($q->id);
                $before['cuotas'][] = ['id' => $source->id, 'capital_pagado' => $source->capital_pagado, 'interes_pagado' => $source->interes_pagado, 'mora_pagada' => $source->mora_pagada, 'estado' => $source->estado];
                $fi = min($forgiveInterest, $row['interes']); $fm = min($forgiveFine, $row['mora']); $forgiveInterest -= $fi; $forgiveFine -= $fm;
                $pi = $row['interes'] - $fi; $pm = $row['mora'] - $fm;
                DB::table('pago')->insert(['id_operacion' => $operation, 'codigo_transaccion' => $code, 'id_cuota' => $q->id, 'id_caja' => $cash->id,
                    'id_usuario' => Auth::id(), 'fecha_pago' => today()->toDateString(), 'estado' => 1, 'tipo_pago' => 'interes_mora',
                    'forma_pago' => count($parts['partes']) === 1 ? $parts['partes'][0]['forma_pago'] : 'mixto',
                    'monto_pago' => Money::decimal($pi + $pm), 'pago_capital' => '0.00', 'pago_interes' => Money::decimal($pi), 'pago_mora' => Money::decimal($pm),
                    'monto_condonado' => Money::decimal($fi + $fm), 'monto_condonado_interes' => Money::decimal($fi), 'monto_condonado_mora' => Money::decimal($fm),
                    'motivo_condonacion' => $order->motivo_condonacion ?? '', 'created_at' => now(), 'updated_at' => now()]);
                DB::table('cuota')->where('id', $q->id)->update(['interes_pagado' => Money::decimal(Money::cents($source->interes_pagado) + $row['interes']),
                    'mora_pagada' => Money::decimal(Money::cents($source->mora_pagada) + $row['mora'])]);
                $details[] = ['cuota' => $q->numero, 'id_cuota' => $q->id, 'capital' => '0.00', 'interes' => Money::decimal($pi), 'mora' => Money::decimal($pm),
                    'condonado' => Money::decimal($fi + $fm), 'desglose_interes' => InterestBreakdown::allocate($q, $pi, $fi)];
            }
            Operations::record($operation, $cash->id, 'ingreso', $parts, ['id_plan_pago' => $record->id_plan_origen, 'antes' => json_encode($before)]);
            DB::table('movimientos_caja')->insert(['id_operacion' => $operation, 'id_caja' => $cash->id, 'id_usuario' => Auth::id(), 'tipo_movimiento' => 'ingreso', 'monto' => $parts['monto'], 'descripcion' => 'Cobro orden reprogramación '.$order->id, 'fecha' => now()]);
            DB::table('orden_pago_reprogramaciones')->where('id', $order->id)->update(['estado' => 2, 'updated_at' => now()]);
            DB::table('reprogramaciones_financieras')->where('id', $record->id)->update(['id_operacion_cobro' => $operation, 'updated_at' => now()]);
            return $parts + ['success' => true, 'detalles' => $details, 'id_orden' => $order->id];
        });
    }

    public function cancel(Request $request): array
    {
        $request->validate(['id_solicitud' => 'required|integer', 'motivo' => 'required|string|min:3|max:500']);
        return app(Operations::class)->run($request, 'cancelar_reprogramacion', function () use ($request) {
            CarteraAccess::assertRecord('solicitud', $request->id_solicitud);
            $record = DB::table('reprogramaciones_financieras')->where('id_solicitud', $request->id_solicitud)->first();
            abort_unless($record && $record->estado !== 'cancelada', 422, 'No hay reprogramación verificable para cancelar.');
            if ($record->id_operacion_cobro) abort(422, 'Anule primero el cobro de la orden desde su caja.');
            if ($record->id_plan_nuevo) {
                abort_unless(Auth::user()->tienePermiso('anular_finanzas'), 403, 'Se requiere permiso de anulación.');
                self::assertNoNewActivity($record->id_plan_nuevo);
                DB::table('plan_pago')->where('id', $record->id_plan_nuevo)->update(['estado' => 0]);
                DB::table('cuota')->where('id_plan_pago', $record->id_plan_nuevo)->update(['estado' => 0]);
            }
            $snapshot = json_decode($record->snapshot, true);
            DB::table('plan_pago')->where('id', $record->id_plan_origen)->update(['estado' => $snapshot['plan_estado']]);
            foreach ($snapshot['rows'] as $row) DB::table('cuota')->where('id', $row['cuota']['id'])->update(['estado' => $row['cuota']['estado']]);
            $origin = DB::table('plan_pago')->find($record->id_plan_origen);
            DB::table('solicitud')->where('id', $origin->id_solicitud)->update(['estado' => $snapshot['solicitud_estado']]);
            DB::table('solicitud')->where('id', $record->id_solicitud)->update(['estado' => 0]);
            if ($record->id_orden) DB::table('orden_pago_reprogramaciones')->where('id', $record->id_orden)->update(['estado' => 3]);
            DB::table('reprogramaciones_financieras')->where('id', $record->id)->update(['estado' => 'cancelada', 'motivo_cancelacion' => $request->motivo, 'cancelado_por' => Auth::id(), 'updated_at' => now()]);
            return ['success' => true, 'message' => 'Reprogramación cancelada; crédito original restablecido.'];
        });
    }

    public static function assertNoNewActivity(int $planId): void
    {
        abort_if(DB::table('operaciones_financieras')->where('id_plan_pago', $planId)->whereNull('anulado_at')->exists()
            || DB::table('pago')->whereIn('id_cuota', DB::table('cuota')->where('id_plan_pago', $planId)->pluck('id'))->where('estado', 1)->exists()
            || DB::table('plan_pago')->where('id', $planId)->value('estado') != 1,
            422, 'El nuevo crédito tiene actividad posterior. No se puede revertir la reprogramación.');
    }
}
