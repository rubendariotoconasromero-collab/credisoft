<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;

final class FinancialLock
{
    /** First lock of every money transaction, including creation when no cash/vault row exists. */
    public static function acquire(): void
    {
        if (DB::transactionLevel() < 1) throw new \LogicException('Se requiere una transacción financiera.');
        if (!DB::table('bloqueo_financiero')->where('id', 1)->lockForUpdate()->first()) {
            throw new \LogicException('Falta inicializar el bloqueo financiero.');
        }
    }
}
