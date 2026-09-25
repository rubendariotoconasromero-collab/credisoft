<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

final class Reversals
{
    public function reverseSource(Request $request, string $table, int $sourceId): array
    {
        abort_unless(Auth::user()->tienePermiso('anular_finanzas'), 403, 'Se requiere permiso de anulación.');
        return DB::transaction(function () use ($request, $table, $sourceId) {
            FinancialLock::acquire();
            $source = DB::table($table)->find($sourceId);
            abort_unless($source, 404, 'Operación no encontrada.');
            if (!$source->id_operacion) Operations::cash($source->id_caja);
            $id = $source->id_operacion ?? app(LegacyOperation::class)->link($table, $source);
            return $this->reverse($request, $id);
        });
    }

    public function reverse(Request $request, int $operationId): array
    {
        abort_unless(Auth::user()->tienePermiso('anular_finanzas'), 403, 'Se requiere permiso de anulación.');
        $request->validate(['motivo' => 'required|string|min:3|max:1000']);
        return app(Operations::class)->run($request, 'anulacion_'.$operationId, function ($reversalId) use ($request, $operationId) {
            $op = DB::table('operaciones_financieras')->where('id', $operationId)->lockForUpdate()->first();
            abort_unless($op, 404, 'Operación no encontrada.');
            $cash = Operations::cash($op->id_caja);
            abort_unless(in_array($op->tipo, ['cobro', 'ingreso', 'egreso', 'administrativo', 'desembolso', 'transferencia_caja'], true), 422, 'Esta operación no admite anulación.');
            if ($op->anulado_at) return ['success' => true, 'respuesta' => 1, 'ya_anulada' => true];
            $before = $op->antes ? json_decode($op->antes, true, 512, JSON_THROW_ON_ERROR) : [];
            if (isset($before['reprogramacion'])) {
                Rescheduling::assertNoNewActivity((int) $before['id_plan_nuevo']);
            } elseif ($op->id_plan_pago) {
                abort_if(DB::table('reprogramaciones_financieras')->where('id_plan_origen', $op->id_plan_pago)->whereIn('estado', ['pendiente', 'aprobada'])->exists(),
                    422, 'El crédito tiene una reprogramación vigente; no se pueden alterar sus pagos originales.');
            }
            if ($op->id_plan_pago) {
                abort_if(DB::table('operaciones_financieras')->where('id_plan_pago', $op->id_plan_pago)->where('id', '>', $op->id)->whereNull('anulado_at')->exists(), 422, 'Existen operaciones posteriores en el crédito.');
                $quotaIds = DB::table('cuota')->where('id_plan_pago', $op->id_plan_pago)->pluck('id');
                if ($op->tipo !== 'cobro') abort_if(DB::table('pago')->whereIn('id_cuota', $quotaIds)->where('estado', 1)->exists(), 422, 'El crédito ya tiene pagos; no se puede revertir esta operación anterior.');
                abort_if(DB::table('pago')->whereIn('id_cuota', $quotaIds)->where('estado', 1)->whereNull('id_operacion')
                    ->whereDate('fecha_pago', '>=', $op->fecha_efectiva)->exists(), 422, 'Existen pagos históricos posteriores o de la misma fecha que requieren revisión.');
            }
            $parts = json_decode($op->partes, true, 512, JSON_THROW_ON_ERROR);
            $physical = array_sum(array_map(fn ($p) => $p['forma_pago'] === 'efectivo' ? Money::cents($p['monto']) : 0, $parts));
            if ($op->direccion === 'ingreso') {
                $summary = app(CashSummary::class)->forCash($cash->id);
                abort_if(Money::cents($summary['saldo_disponible']) < Money::cents($op->monto), 422, 'Saldo disponible insuficiente para devolver el cobro.');
                abort_if(Money::cents($summary['efectivo_esperado']) < $physical, 422, 'Saldo físico insuficiente para devolver el componente en efectivo.');
            }
            if ($op->interno && $op->id_boveda) {
                $vault = DB::table('boveda')->where('id', $op->id_boveda)->lockForUpdate()->first();
                $delta = $op->direccion === 'ingreso' ? $physical : -$physical;
                $newBalance = Money::cents($vault->saldo_actual) + $delta;
                abort_if($newBalance < 0, 422, 'Saldo insuficiente en bóveda para revertir la transferencia.');
                DB::table('boveda')->where('id', $vault->id)->update(['saldo_actual' => Money::decimal($newBalance)]);
                DB::table('movimientos_boveda')->insert(['id_operacion' => $reversalId, 'id_boveda' => $vault->id, 'id_usuario' => Auth::id(), 'tipo_movimiento' => $delta > 0 ? 'ingreso' : 'salida', 'monto' => Money::decimal($physical), 'descripcion' => 'Reversión transferencia interna '.$op->codigo, 'fecha' => now()]);
            }
            $before = $op->antes ? json_decode($op->antes, true, 512, JSON_THROW_ON_ERROR) : [];
            if ($op->tipo === 'cobro') {
                DB::table('pago')->where('id_operacion', $op->id)->update(['estado' => 0]);
                foreach ($before['cuotas'] as $q) { $id = $q['id']; unset($q['id']); DB::table('cuota')->where('id', $id)->update($q); }
                if (isset($before['reprogramacion'])) {
                    $reprogramacion = DB::table('reprogramaciones_financieras')->find($before['reprogramacion']);
                    DB::table('orden_pago_reprogramaciones')->where('id', $reprogramacion->id_orden)->update(['estado' => 1, 'updated_at' => now()]);
                    DB::table('reprogramaciones_financieras')->where('id', $reprogramacion->id)->update(['id_operacion_cobro' => null, 'updated_at' => now()]);
                }
            } else {
                $table = match ($op->tipo) { 'administrativo' => 'pago_administrativo', 'transferencia_caja' => 'ingreso', default => $op->tipo };
                DB::table($table)->where('id_operacion', $op->id)->update(['estado' => in_array($table, ['desembolso', 'pago_administrativo'], true) ? 1 : 0]);
            }
            if ($op->id_plan_pago && isset($before['plan'])) {
                DB::table('plan_pago')->where('id', $op->id_plan_pago)->update($before['plan']);
                if (isset($before['solicitud'])) DB::table('solicitud')->where('id', DB::table('plan_pago')->where('id', $op->id_plan_pago)->value('id_solicitud'))->update($before['solicitud']);
            }
            DB::table('operaciones_financieras')->where('id', $op->id)->update(['anulado_at' => now(), 'anulado_por' => Auth::id(), 'motivo_anulacion' => $request->motivo]);
            // The original is excluded from active totals; this audit reversal is not counted again.
            DB::table('operaciones_financieras')->where('id', $reversalId)->update(['id_caja' => $cash->id, 'id_boveda' => $op->id_boveda, 'interno' => $op->interno,
                'antes' => json_encode(['id_original' => $op->id, 'partes' => $parts, 'motivo' => $request->motivo], JSON_THROW_ON_ERROR)]);
            DB::table('movimientos_caja')->insert(['id_operacion' => $reversalId, 'id_caja' => $cash->id, 'id_usuario' => Auth::id(), 'tipo_movimiento' => $op->direccion === 'ingreso' ? 'salida' : 'ingreso', 'monto' => $op->monto, 'descripcion' => 'Anulación '.$op->codigo, 'fecha' => now()]);
            return ['success' => true, 'respuesta' => 1, 'message' => 'Operación anulada con todos sus componentes.'];
        });
    }
}
