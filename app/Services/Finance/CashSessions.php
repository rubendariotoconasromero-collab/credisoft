<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;

final class CashSessions
{
    public function open(int $userId, mixed $amount): int
    {
        $cents = Money::cents($amount, 'monto_inicial');
        abort_if($cents < 0, 422, 'El importe de apertura no puede ser negativo.');
        return DB::transaction(function () use ($userId, $cents) {
            FinancialLock::acquire();
            abort_if(DB::table('caja')->where('id_usuario', $userId)->where('estado', 1)->exists(), 422, 'Ya tienes una caja abierta.');
            $vault = DB::table('boveda')->lockForUpdate()->first();
            abort_unless($vault, 422, 'Debe abrir la bóveda primero.');
            $balance = Money::cents($vault->saldo_actual);
            abort_if($balance < $cents, 422, 'Saldo insuficiente en bóveda.');
            $amount = Money::decimal($cents);
            $id = DB::table('caja')->insertGetId(['monto_inicial' => $amount,
                'fechahora_apertura' => now(), 'id_usuario' => $userId, 'estado' => 1,
                'created_at' => now(), 'updated_at' => now()]);
            if ($cents > 0) {
                DB::table('boveda')->where('id', $vault->id)->update(['saldo_actual' => Money::decimal($balance - $cents)]);
                DB::table('movimientos_boveda')->insert(['tipo_movimiento' => 'salida', 'monto' => $amount,
                    'descripcion' => 'Apertura de caja (Transferencia a Caja)', 'fecha' => now(),
                    'id_boveda' => $vault->id, 'id_usuario' => $userId, 'created_at' => now(), 'updated_at' => now()]);
                DB::table('movimientos_caja')->insert(['tipo_movimiento' => 'ingreso', 'monto' => $amount,
                    'descripcion' => 'Apertura de caja (Ingreso Inicial)', 'fecha' => now(),
                    'id_caja' => $id, 'id_usuario' => $userId, 'created_at' => now(), 'updated_at' => now()]);
            }
            return $id;
        });
    }

    public function close(int $userId, int $cashId, mixed $counted = null): array
    {
        return DB::transaction(function () use ($userId, $cashId, $counted) {
            FinancialLock::acquire();
            $cash = DB::table('caja')->where('id', $cashId)->lockForUpdate()->first();
            abort_unless($cash, 404, 'Caja no encontrada.');
            abort_unless((int) $cash->id_usuario === $userId, 403, 'Solo el propietario puede cerrar la caja.');
            abort_unless((int) $cash->estado === 1, 422, 'La caja ya está cerrada.');
            $vault = DB::table('boveda')->lockForUpdate()->first();
            abort_unless($vault, 422, 'No existe bóveda para recibir el efectivo.');
            $summary = app(CashSummary::class)->forCash($cashId);
            abort_if($summary['requiere_revision'], 422, 'La caja contiene operaciones históricas que requieren revisión antes del cierre.');
            $return = Money::cents($summary['saldo_disponible']);
            $expectedCash = Money::cents($summary['efectivo_esperado']);
            $countedCents = $counted === null ? $expectedCash : Money::cents($counted, 'efectivo_contado');
            abort_if($countedCents < 0 || $countedCents !== $expectedCash, 422, 'El efectivo contado no coincide con el esperado. Revise los movimientos antes de cerrar; no se generarán ajustes automáticos.');
            $summary['efectivo_contado'] = Money::decimal($countedCents);
            $summary['diferencia'] = Money::decimal($countedCents - $expectedCash);
            abort_if($return < 0, 422, 'El saldo disponible de caja es negativo. Revise los movimientos.');
            $noncash = Money::cents($summary['formas_pago']['deposito']['ingresos']) + Money::cents($summary['formas_pago']['transferencia']['ingresos']);
            DB::table('caja')->where('id', $cashId)->update([
                'fechahora_cierre' => now(), 'estado' => 0, 'monto_final' => Money::decimal($return),
                'efectivo_contado' => Money::decimal($countedCents), 'diferencia' => $summary['diferencia'],
                'efectivo_total' => Money::decimal($expectedCash), 'deposito_total' => Money::decimal($noncash),
                'efectivo_venta' => $summary['formas_pago']['efectivo']['ingresos'], 'deposito_venta' => Money::decimal($noncash),
                'efectivo_gasto' => $summary['formas_pago']['efectivo']['egresos'],
                'deposito_gasto' => Money::decimal(Money::cents($summary['formas_pago']['deposito']['egresos']) + Money::cents($summary['formas_pago']['transferencia']['egresos'])),
                'total_ingreso' => $summary['total_ingresos'], 'total_egreso' => $summary['total_egresos'],
                'updated_at' => now(),
            ]);
            DB::table('caja_cierres')->insert(['id_caja' => $cashId, 'id_boveda' => $vault->id,
                'id_usuario' => $userId, 'efectivo_retornado' => Money::decimal($return),
                'resumen' => json_encode($summary, JSON_THROW_ON_ERROR), 'created_at' => now()]);
            if ($return > 0) {
                DB::table('movimientos_boveda')->insert(['tipo_movimiento' => 'ingreso', 'monto' => Money::decimal($return),
                    'descripcion' => 'Retorno de Caja al Cierre', 'fecha' => now(), 'id_boveda' => $vault->id,
                    'id_usuario' => $userId, 'created_at' => now(), 'updated_at' => now()]);
                DB::table('boveda')->where('id', $vault->id)->update(['saldo_actual' => Money::decimal(Money::cents($vault->saldo_actual) + $return)]);
                DB::table('movimientos_caja')->insert(['tipo_movimiento' => 'salida', 'monto' => Money::decimal($return),
                    'descripcion' => 'Retorno de Caja al Cierre', 'fecha' => now(), 'id_caja' => $cashId,
                    'id_usuario' => $userId, 'created_at' => now(), 'updated_at' => now()]);
            }
            return $summary;
        });
    }
}
