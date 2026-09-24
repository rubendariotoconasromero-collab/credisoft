<?php

namespace App\Services\Finance;

final class PaymentMethod
{
    /** Known historical labels only. Unknown labels must never become physical cash. */
    public static function historical(?string $value): string
    {
        return match (mb_strtolower(trim($value ?? ''))) {
            'efectivo' => 'efectivo',
            'deposito', 'depósito', 'depósito banco', 'deposito banco' => 'deposito',
            'transferencia', 'transferencia - qr', 'transferencia / qr', 'transferencias - qr' => 'transferencia',
            default => 'sin_clasificar',
        };
    }
}
