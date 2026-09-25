<?php

namespace App\Services\Finance;

final class AdministrativeFee
{
    private const RATE_DIVISOR = 100; // 1 %

    public static function centsFromAmount(mixed $amount): int
    {
        $baseCents = Money::cents($amount ?? 0);

        return intdiv($baseCents + intdiv(self::RATE_DIVISOR, 2), self::RATE_DIVISOR);
    }

    public static function decimalFromAmount(mixed $amount): string
    {
        return Money::decimal(self::centsFromAmount($amount));
    }

    public static function disbursementAmount(object $credit): mixed
    {
        return $credit->tipo_solicitud === 'Refinanciamiento'
            ? ($credit->monto_refinanciamiento ?? 0)
            : ($credit->importe_solicitud ?? 0);
    }

    public static function centsForCredit(object $credit): int
    {
        return self::centsFromAmount(self::disbursementAmount($credit));
    }

    public static function decimalForCredit(object $credit): string
    {
        return Money::decimal(self::centsForCredit($credit));
    }
}
