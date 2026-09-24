<?php

namespace App\Services\Finance;

use Illuminate\Validation\ValidationException;

/** Amounts at the persistence boundary; all arithmetic uses integer centavos. */
final class Money
{
    public static function cents(mixed $value, string $field = 'monto'): int
    {
        if ((!is_string($value) && !is_int($value) && !is_float($value))
            || !preg_match('/^(-?)(\d{1,10})(?:\.(\d{1,2}))?$/D', (string) $value, $parts)) {
            throw ValidationException::withMessages([$field => 'Use un importe decimal de hasta dos decimales.']);
        }
        $amount = ((int) $parts[2]) * 100 + (int) str_pad($parts[3] ?? '', 2, '0');
        return $parts[1] === '-' ? -$amount : $amount;
    }

    public static function decimal(int $cents): string
    {
        $absolute = abs($cents);
        return ($cents < 0 ? '-' : '').intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }
}
