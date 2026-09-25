<?php

namespace App\Services\Finance;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;

/** Server counterpart of the existing fixed-payment / equal-principal preview. */
final class ReschedulingSchedule
{
    public static function make(array $terms, int $principal): array
    {
        Validator::make($terms, [
            'nro_cuotas' => 'required|integer|min:1|max:600',
            'lapso_capital' => 'required|in:Mensual,Quincenal,Semanal',
            'tipo_tasa' => 'required|in:fija,amortizable',
            'tasa' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d{1,8})?$/'],
            'fecha_desembolso' => 'required|date_format:Y-m-d',
            'fecha_primera_cuota' => 'required|date_format:Y-m-d|after:fecha_desembolso',
        ])->validate();
        abort_unless($principal > 0, 422, 'El nuevo capital debe ser positivo.');
        $n = (int) $terms['nro_cuotas'];
        $factor = ['Mensual' => 1, 'Quincenal' => 2, 'Semanal' => 4][$terms['lapso_capital']];
        $start = CarbonImmutable::parse($terms['fecha_desembolso']);
        $date = CarbonImmutable::parse($terms['fecha_primera_cuota']);
        $rate = bcdiv((string) $terms['tasa'], '100', 12);
        $round = fn ($value) => (int) bcadd($value, '0.5', 0);
        $fixed = (float) $rate > 0
            ? (int) round($principal * (float) $rate * pow(1 + (float) $rate, $n / $factor) / (pow(1 + (float) $rate, $n / $factor) - 1) / $factor)
            : $round(bcdiv((string) $principal, (string) $n, 12));
        $balance = $principal; $blockBalance = $principal; $rows = [];
        for ($i = 1; $i <= $n; $i++) {
            if (($i - 1) % $factor === 0) $blockBalance = $balance;
            $monthly = bcmul((string) $blockBalance, $rate, 12);
            $interest = bcdiv($monthly, (string) $factor, 12);
            $days = $start->diffInDays($date);
            if ($terms['tipo_tasa'] === 'amortizable' && $i === 1) {
                $interest = bcdiv(bcmul((string) $principal, bcmul($rate, (string) $days, 12), 12), '30', 12);
            } elseif ($terms['tipo_tasa'] === 'fija' && $i === 1) {
                $standard = [1 => 30, 2 => 15, 4 => 7][$factor];
                $interest = bcadd($interest, bcdiv(bcmul($monthly, (string) ($days - $standard), 12), $factor === 4 ? '28' : '30', 12), 12);
            }
            $baseInterest = $round(bcdiv($monthly, (string) $factor, 12));
            $capital = $i === $n ? $balance : min($balance, $terms['tipo_tasa'] === 'fija' ? $fixed - $baseInterest : intdiv($principal, $n));
            abort_if($capital <= 0, 422, 'Los términos generan cuotas sin amortización de capital. Revise plazo y tasa.');
            $balance -= $capital;
            $interestCents = max(0, $round($interest));
            $rows[] = ['nro' => $i, 'fecha' => $date->toDateString(), 'capital' => Money::decimal($capital),
                'interes' => Money::decimal($interestCents), 'saldo_capital' => Money::decimal($balance), 'total_cuota' => Money::decimal($capital + $interestCents)];
            $start = $date;
            $date = match ($factor) { 1 => $date->addMonthNoOverflow(), 2 => $date->addDays(15), 4 => $date->addWeek() };
        }
        return $rows;
    }
}
