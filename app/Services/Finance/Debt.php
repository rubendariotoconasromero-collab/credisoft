<?php

namespace App\Services\Finance;

use Carbon\CarbonImmutable as Date;
use Illuminate\Support\Facades\DB;

final class Debt
{
    private function rounded(string $cents): int
    {
        return (int) bcadd($cents, '0.5', 0);
    }

    public function calculate(int $planId, ?string $asOf = null): array
    {
        $plan = DB::table('plan_pago')->find($planId);
        abort_unless($plan, 404, 'Plan no encontrado.');
        $cutoff = Date::parse($asOf ?? today()->toDateString())->startOfDay();
        $quotas = DB::table('cuota')->where('id_plan_pago', $planId)->orderBy('numero')->get();
        $payments = DB::table('pago')->whereIn('id_cuota', $quotas->pluck('id'))->where('estado', 1)
            ->whereDate('fecha_pago', '<=', $cutoff->toDateString())->orderBy('fecha_pago')->orderBy('id')->get();
        $revision = hash('sha256', json_encode([$plan, $quotas, $payments->pluck('id'), $cutoff->toDateString()], JSON_THROW_ON_ERROR));
        $totalCapital = $quotas->sum(fn ($q) => Money::cents($q->capital));
        $first = $quotas->search(fn ($q) => in_array((int) $q->estado, [1, 3], true));
        $intervals = []; $finePaid = 0;
        foreach ($quotas as $q) {
            $last = $payments->where('id_cuota', $q->id)->last();
            $end = (int) $q->estado === 2 && $last ? Date::parse($last->fecha_pago) : $cutoff;
            $due = Date::parse($q->fecha);
            if ($end->greaterThan($due)) $intervals[] = [$due, $end->min($cutoff)];
            $finePaid += Money::cents($q->mora_pagada ?? 0);
        }
        usort($intervals, fn ($a, $b) => $a[0] <=> $b[0]);
        $days = 0; $right = null;
        foreach ($intervals as [$start, $end]) {
            if ($right && $start->lessThan($right)) $start = $right;
            if ($end->greaterThan($start)) $days += (int) $start->diffInDays($end);
            $right = $right ? $right->max($end) : $end;
        }
        $fine = max(0, $days * 300 - $finePaid);
        $scheduled = $totalCapital;
        foreach ($quotas as $index => $q) {
            $q->fecha_inicio = $plan->fecha_inicio; $q->lapso_capital = $plan->lapso_capital; $q->tasa = $plan->tasa;
            $own = $payments->where('id_cuota', $q->id);
            $q->fecha_pago_real = $own->last()?->fecha_pago;
            $start = Date::parse($index ? $quotas[$index - 1]->fecha : $plan->fecha_inicio);
            $due = Date::parse($q->fecha);
            $end = ((int) $q->estado === 2 && $q->fecha_pago_real) ? Date::parse($q->fecha_pago_real)->min($cutoff) : $cutoff;
            $period = max(1, (int) $start->diffInDays($due, false));
            $interest = Money::cents($q->interes);
            // Integrate remaining principal between payment dates. Early principal never accrues future interest.
            $normal = '0'; $cursor = $start; $limit = $end->min($due);
            $events = $payments->filter(fn ($p) => Money::cents($p->pago_capital) > 0);
            $points = $events->pluck('fecha_pago')->unique()->map(fn ($d) => Date::parse($d))
                ->filter(fn ($d) => $d->greaterThan($start) && $d->lessThan($limit))->push($limit)->sort()->values();
            foreach ($points as $point) {
                if ($point->lessThanOrEqualTo($cursor)) continue;
                $paidAtStart = $events->filter(fn ($p) => $p->fecha_pago <= $cursor->toDateString())->sum(fn ($p) => Money::cents($p->pago_capital));
                $remaining = max(0, min($scheduled, $totalCapital - $paidAtStart));
                $numerator = bcmul(bcmul((string) $interest, (string) $remaining, 12), (string) ((int) $cursor->diffInDays($point)), 12);
                $normal = bcadd($normal, bcdiv($numerator, (string) max(1, $scheduled * $period), 12), 12);
                $cursor = $point;
            }
            $normalCents = $this->rounded($normal);
            $lateDays = max(0, (int) $due->diffInDays($end, false));
            $divisor = match (mb_strtolower(trim($plan->lapso_capital))) { 'semanal' => 7, 'quincenal' => 15, default => 30 };
            $moratory = $index === $first ? $this->rounded(bcdiv(bcmul((string) $interest, (string) $lateDays, 12), (string) $divisor, 12)) : 0;
            $paidInterest = Money::cents($q->interes_pagado ?? 0);
            $q->capital_neto = Money::decimal(max(0, Money::cents($q->capital) - Money::cents($q->capital_pagado ?? 0)));
            $q->interes_devengado_neto = Money::decimal(max(0, $normalCents - $paidInterest));
            $q->interes_moratorio_neto = Money::decimal(max(0, $moratory - max(0, $paidInterest - $normalCents)));
            $q->interes_acumulado_neto = Money::decimal(max(0, $normalCents + $moratory - $paidInterest));
            $q->mora_fija_neta = Money::decimal($index === $first ? $fine : 0);
            $q->mora_bruta = Money::decimal($index === $first ? $days * 300 : 0);
            $q->dias_mora_cobro = $index === $first ? $days : 0;
            $q->dias_pasados = in_array((int) $q->estado, [1, 3], true) ? $lateDays : 0;
            $q->dias_transcurridos = max(0, min($period, (int) $start->diffInDays($end, false)));
            foreach (['capital', 'interes', 'mora'] as $component) {
                $paidField = $component === 'mora' ? 'mora_pagada' : $component.'_pagado';
                $paid = Money::cents($q->$paidField ?? 0);
                $q->{$paidField.'_total'} = Money::decimal($paid);
                $collected = $own->sum(fn ($p) => Money::cents($p->{'pago_'.$component}));
                $forgiven = $component === 'capital' ? 0 : $own->sum(fn ($p) => Money::cents($p->{'monto_condonado_'.$component}));
                $q->{$component.'_cobrado_total'} = Money::decimal($collected);
                $q->{$component.'_condonado_total'} = Money::decimal($forgiven);
                // Coverage includes forgiveness. A settled installment has no residual;
                // do not compare its historical settlement with today's ordinary interest.
                $residual = (int) $q->estado === 2 ? 0 : match ($component) {
                    'capital' => Money::cents($q->capital_neto),
                    'interes' => Money::cents($q->interes_acumulado_neto),
                    default => Money::cents($q->mora_fija_neta),
                };
                $gross = $paid + $residual;
                $q->{'porcentaje_'.$paidField} = $gross > 0 ? round($paid * 100 / $gross, 1) : 0;
            }
            if ((int) $q->estado === 2) {
                $q->capital_neto = $q->interes_devengado_neto = $q->interes_moratorio_neto = $q->interes_acumulado_neto = $q->mora_fija_neta = '0.00';
            }
            $q->total_a_pagar = Money::decimal(Money::cents($q->capital_neto) + Money::cents($q->interes_acumulado_neto) + Money::cents($q->mora_fija_neta));
            $scheduled -= Money::cents($q->capital);
        }
        return ['cuotas' => $quotas, 'dias_pasados_mora' => $days,
            'resumen_multa' => [
                'dias_historicos' => $days, 'generada' => Money::decimal($days * 300),
                'cobrada' => Money::decimal($payments->sum(fn ($p) => Money::cents($p->pago_mora))),
                'condonada' => Money::decimal($payments->sum(fn ($p) => Money::cents($p->monto_condonado_mora))),
                'pendiente' => Money::decimal($fine),
            ], 'fecha_corte' => $cutoff->toDateString(), 'revision' => $revision];
    }
}
