<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;

final class VaultOperations
{
    public function open(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            FinancialLock::acquire();
            abort_if(DB::table('boveda')->exists(), 422, 'Ya existe una bóveda registrada.');
            DB::table('boveda')->insert(['saldo_actual' => '0.00', 'fecha_apertura' => now(), 'id_usuario' => $userId]);
        });
    }

    public function move(int $userId, string $direction, mixed $amount, string $description, ?int $cashId, ?int $partnerId, ?int $operationId = null): void
    {
        $cents = Money::cents($amount);
        abort_unless($cents > 0, 422, 'El importe debe ser mayor a cero.');
        abort_unless(in_array($direction, ['ingreso', 'salida'], true), 422, 'Dirección inválida.');
        DB::transaction(function () use ($userId, $direction, $cents, $description, $cashId, $partnerId, $operationId) {
            FinancialLock::acquire();
            $vault = DB::table('boveda')->lockForUpdate()->first();
            abort_unless($vault, 422, 'No existe una bóveda registrada.');
            $balance = Money::cents($vault->saldo_actual);
            abort_if($direction === 'salida' && $cents > $balance, 422, 'Saldo insuficiente en bóveda.');
            $transfer = str_contains(mb_strtolower($description), 'transferencia a caja');
            abort_if($direction === 'ingreso' && $transfer, 422, 'Una transferencia a caja debe registrarse como salida de bóveda.');
            if ($transfer) {
                $cash = DB::table('caja')->where('id', $cashId)->lockForUpdate()->first();
                abort_unless($cash, 422, 'Seleccione una caja abierta.');
                abort_unless((int) $cash->id_usuario === $userId, 403, 'Solo el propietario puede operar su caja.');
                abort_unless((int) $cash->estado === 1, 422, 'La caja está cerrada.');
            }
            $amount = Money::decimal($cents);
            DB::table('movimientos_boveda')->insert(['id_operacion' => $operationId, 'tipo_movimiento' => $direction, 'monto' => $amount,
                'descripcion' => $description, 'fecha' => now(), 'id_boveda' => $vault->id,
                'id_usuario' => $userId, 'id_socio' => $partnerId, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('boveda')->where('id', $vault->id)->update([
                'saldo_actual' => Money::decimal($balance + ($direction === 'ingreso' ? $cents : -$cents)),
            ]);
            if ($transfer) {
                DB::table('ingreso')->insert(['id_operacion' => $operationId, 'monto' => $amount, 'descripcion' => 'Transferencia desde Bóveda',
                    'id_usuario' => $userId, 'fecha' => now(), 'id_caja' => $cashId, 'created_at' => now(), 'updated_at' => now()]);
                DB::table('movimientos_caja')->insert(['id_operacion' => $operationId, 'tipo_movimiento' => 'ingreso', 'monto' => $amount,
                    'descripcion' => 'Transferencia desde Bóveda', 'fecha' => now(), 'id_caja' => $cashId,
                    'id_usuario' => $userId, 'created_at' => now(), 'updated_at' => now()]);
                if ($operationId) Operations::record($operationId, $cashId, 'ingreso', PaymentParts::prepare([['forma_pago' => 'efectivo', 'monto' => $amount]], $cents), ['interno' => true, 'id_boveda' => $vault->id]);
            }
        });
    }
}
