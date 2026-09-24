<?php

namespace App\Services\Finance;

final class PaymentParts
{
    public static function prepare(array $input, int $applied, bool $allowChange = false): array
    {
        abort_unless(array_is_list($input) && count($input) <= 20, 422, 'Desglose de pago inválido.');
        $parts = []; $total = 0; $cash = 0;
        foreach ($input as $part) {
            abort_unless(is_array($part), 422, 'Componente inválido.');
            $method = PaymentMethod::historical($part['forma_pago'] ?? null);
            abort_if($method === 'sin_clasificar', 422, 'Seleccione Efectivo, Depósito o Transferencia / QR.');
            $amount = Money::cents($part['monto'] ?? null);
            abort_unless($amount > 0, 422, 'Cada componente debe ser positivo.');
            $reference = trim((string) ($part['referencia'] ?? ''));
            abort_if(mb_strlen($reference) > 150 || ($method !== 'efectivo' && $reference === ''), 422, 'Depósitos y transferencias requieren referencia (hasta 150 caracteres).');
            $total += $amount;
            if ($method === 'efectivo') $cash += $amount;
            $parts[] = ['forma_pago' => $method, 'monto' => Money::decimal($amount), 'entregado' => Money::decimal($amount), 'referencia' => $reference];
        }
        $change = $total - $applied;
        abort_if($applied < 0 || $total > 999999999999 || $change < 0 || (!$allowChange && $change !== 0)
            || $change > $cash, 422, 'El desglose no coincide con el importe aplicado. No se permiten excedentes no efectivos.');
        $remaining = $change;
        foreach ($parts as &$part) {
            if ($part['forma_pago'] !== 'efectivo') continue;
            $value = Money::cents($part['monto']);
            $deduct = min($remaining, $value);
            $part['monto'] = Money::decimal($value - $deduct);
            $remaining -= $deduct;
        }
        unset($part);
        return ['partes' => $parts, 'monto' => Money::decimal($applied), 'cambio' => Money::decimal($change), 'efectivo' => Money::decimal($cash - $change)];
    }

    public static function input(\Illuminate\Http\Request $request, mixed $default): array
    {
        $input = $request->input('partes');
        if ($request->has('partes')) {
            abort_unless(is_array($input) && array_is_list($input) && count($input) <= 20, 422, 'Desglose inválido.');
            foreach ($input as $part) abort_unless(is_array($part), 422, 'Componente inválido.');
            return $input;
        }
        return Money::cents($default) === 0 ? [] : [['forma_pago' => $request->input('forma_pago', 'efectivo'), 'monto' => $default, 'referencia' => $request->input('referencia')]];
    }
}
