<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;

final class CreditExtract
{
    public function build(int $planId): array
    {
        $credit = DB::table('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->where('plan_pago.id', $planId)
            ->select('plan_pago.id', 'plan_pago.total_pagar', 'plan_pago.fecha_inicio', 'plan_pago.fecha_fin',
                'plan_pago.estado', 'plan_pago.fecha_registro', 'plan_pago.desembolso',
                'solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.tasa',
                'solicitud.nro_cuotas', 'solicitud.lapso_capital', 'solicitud.fecha_desembolso',
                'solicitud.destino_prestamo', 'solicitud.tipo_garantia',
                'cliente.nombre as cliente_nombre', 'cliente.ci as cliente_ci',
                'cliente.lugar_expedicion as cliente_expedicion', 'cliente.actividad as cliente_actividad',
                'users.personal as asesor_nombre')
            ->first();
        abort_unless($credit, 404, 'Crédito no encontrado.');

        $debt = app(Debt::class)->calculate($planId);
        $quotas = $debt['cuotas'];
        $payments = DB::table('pago')
            ->leftJoin('users', 'users.id', '=', 'pago.id_usuario')
            ->whereIn('pago.id_cuota', $quotas->pluck('id'))
            ->where('pago.estado', 1)
            ->whereDate('pago.fecha_pago', '<=', $debt['fecha_corte'])
            ->orderBy('pago.fecha_pago')->orderBy('pago.id')
            ->select('pago.id', 'pago.id_cuota', 'pago.id_operacion', 'pago.codigo_transaccion',
                'pago.fecha_pago', 'pago.monto_pago', 'pago.pago_capital', 'pago.pago_interes',
                'pago.pago_mora', 'pago.monto_condonado', 'pago.monto_condonado_interes',
                'pago.monto_condonado_mora', 'pago.motivo_condonacion', 'pago.forma_pago',
                'users.personal as cajero_nombre')
            ->get();
        InterestBreakdown::attach($payments);
        $operations = DB::table('operaciones_financieras')
            ->whereIn('id', $payments->pluck('id_operacion')->filter()->unique())
            ->get(['id', 'monto', 'cambio', 'partes'])->keyBy('id');
        foreach ($payments as $payment) {
            $operation = $operations->get($payment->id_operacion);
            // These parts describe the whole receipt; one operation may cover several installments.
            $payment->partes_operacion = $operation && $operation->partes
                ? json_decode($operation->partes, true, 512, JSON_THROW_ON_ERROR) : [];
            $payment->monto_operacion = $operation?->monto;
            $payment->cambio_operacion = $operation?->cambio;
        }

        $totals = array_fill_keys(['programado', 'variacion', 'registrado', 'condonado', 'exigible', 'cobrado', 'pendiente'], 0);
        foreach ($quotas as $quota) {
            $quota->pagos = $payments->where('id_cuota', $quota->id)->values();
            // A paid installment may have only the last settlement snapshot. Build
            // the historical totals from every payment when all splits exist.
            foreach (['ordinario', 'moratorio'] as $kind) {
                $quota->{'interes_'.$kind.'_total_extracto'} = $quota->desglose_cobros_interes_completo
                    ? Money::decimal(Money::cents($quota->{'interes_'.$kind.'_cobrado'})
                        + Money::cents($quota->{'interes_'.$kind.'_condonado'})
                        + Money::cents($quota->{$kind === 'ordinario' ? 'interes_devengado_neto' : 'interes_moratorio_neto'}))
                    : null;
            }
            $scheduled = Money::cents($quota->total);
            $registered = Money::cents($quota->total_registrado_antes_condonacion);
            $forgiven = Money::cents($quota->interes_condonado_total) + Money::cents($quota->mora_condonado_total);
            $collected = Money::cents($quota->capital_cobrado_total)
                + Money::cents($quota->interes_cobrado_total) + Money::cents($quota->mora_cobrado_total);
            $due = Money::cents($quota->total_a_pagar);
            $quota->variacion_programado = Money::decimal($registered - $scheduled);
            $quota->total_condonado = Money::decimal($forgiven);
            $quota->exigible_neto = Money::decimal($registered - $forgiven);
            $quota->total_cobrado = Money::decimal($collected);
            foreach (['programado' => $scheduled, 'variacion' => $registered - $scheduled,
                'registrado' => $registered, 'condonado' => $forgiven,
                'exigible' => $registered - $forgiven, 'cobrado' => $collected, 'pendiente' => $due] as $key => $value) {
                $totals[$key] += $value;
            }
        }

        return ['credito' => $credit, 'cuotas' => $quotas,
            'totales' => array_map(fn ($value) => Money::decimal($value), $totals),
            'resumen_multa' => $debt['resumen_multa'], 'fecha_corte' => $debt['fecha_corte']];
    }
}
