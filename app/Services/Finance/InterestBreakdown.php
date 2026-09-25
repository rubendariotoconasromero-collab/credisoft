<?php

namespace App\Services\Finance;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class InterestBreakdown
{
    // Preserve Debt's existing priority: ordinary interest first, then moratory.
    // Forgiveness is applied before money, as in Collections.
    public static function allocate(object $quota, int $collected, int $forgiven): array
    {
        $ordinary = Money::cents($quota->interes_devengado_neto);
        $ordinaryForgiven = min($ordinary, $forgiven);
        $ordinaryCollected = min($ordinary - $ordinaryForgiven, $collected);
        return [
            'ordinario_cobrado' => Money::decimal($ordinaryCollected),
            'moratorio_cobrado' => Money::decimal($collected - $ordinaryCollected),
            'ordinario_condonado' => Money::decimal($ordinaryForgiven),
            'moratorio_condonado' => Money::decimal($forgiven - $ordinaryForgiven),
        ];
    }

    public static function attach(Collection $payments, ?Collection $snapshots = null): void
    {
        $snapshots ??= DB::table('operaciones_financieras')
            ->whereIn('id', $payments->pluck('id_operacion')->filter()->unique())->pluck('resultado', 'id');
        $details = $snapshots->map(fn ($json) => collect(json_decode($json ?? '{}', true)['detalles'] ?? [])->keyBy('id_cuota'));
        foreach ($payments as $payment) {
            $detail = $details->get($payment->id_operacion)?->get($payment->id_cuota);
            $payment->desglose_interes = $detail['desglose_interes'] ?? null;
            // A historical payment with no interest has a known zero split.
            if ($payment->desglose_interes === null && Money::cents($payment->pago_interes) === 0
                && Money::cents($payment->monto_condonado_interes ?? 0) === 0) {
                $payment->desglose_interes = array_fill_keys(['ordinario_cobrado', 'moratorio_cobrado', 'ordinario_condonado', 'moratorio_condonado'], '0.00');
            }
        }
    }
}
