<?php

namespace App\Traits;

use DB;
use Carbon\Carbon;

/**
 * Trait CalculaAmortizaciones
 *
 * Centraliza el cálculo en tiempo real de las cuotas de un plan de pago:
 * interés devengado, interés moratorio, interés acumulado, mora fija,
 * capital neto, días transcurridos y porcentajes pagados.
 *
 * Es la ÚNICA fuente de verdad para estos montos. Tanto la pantalla de
 * cobros (PlanPagoController::listarAmortizaciones / GestionCobros.vue) como
 * el reporte de créditos en mora (VistasReporteController) deben usar este
 * trait para que los valores (en especial "Total a Pagar") coincidan.
 *
 * Reglas clave del modelo de cobro en cascada:
 *  - El interés moratorio y la mora fija SOLO se acumulan en la PRIMERA cuota
 *    impaga del plan (firstUnpaidIndex). Las demás cuotas vencidas muestran 0.
 *  - "Total a Pagar" de una cuota = capital_neto + interes_acumulado_neto + mora_fija_neta.
 */
trait CalculaAmortizaciones
{
    /**
     * Calcula las cuotas amortizadas de un plan de pago.
     *
     * @param  int|string  $id_plan_pago
     * @return array{cuotas: \Illuminate\Support\Collection, dias_pasados_mora: int}
     */
    public function calcularAmortizacionesCuotas($id_plan_pago)
    {
        // 1. OBTENER CUOTAS
        $cuotas = DB::table('cuota')
            ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->select(
                'cuota.*',
                'plan_pago.fecha_inicio',
                'plan_pago.lapso_capital',
                'plan_pago.tasa',
                DB::raw('(SELECT MAX(fecha_pago) FROM pago WHERE pago.id_cuota = cuota.id) as fecha_pago_real'),
                DB::raw('CASE
                    WHEN cuota.estado IN (1, 3) AND cuota.id = (
                        SELECT MIN(id) FROM cuota
                        WHERE id_plan_pago = ? AND estado IN (1, 3)
                    ) THEN
                        GREATEST(0, DATEDIFF(NOW(), GREATEST(cuota.fecha, COALESCE(plan_pago.fecha_ultima_amortizacion, cuota.fecha))))
                    ELSE 0
                END as dias_mora_cobro')
            )
            ->where('cuota.id_plan_pago', $id_plan_pago)
            ->addBinding($id_plan_pago, 'select')
            ->orderBy('cuota.numero', 'asc')
            ->get();

        $firstUnpaidIndex = $cuotas->search(function ($cuota) {
            return in_array($cuota->estado, [1, 3]);
        });

        $dias_pasados_mora = 0;

        // Saldo capital base para interés moratorio = saldo_capital de la última cuota pagada (estado = 2).
        // saldo_capital almacena el saldo pendiente DESPUÉS de pagar esa cuota (generado al crear el plan).
        // Si ninguna cuota ha sido pagada (primera cuota en mora), se usa el monto total del crédito.
        $lastPaidCuota = $cuotas->last(fn($c) => $c->estado == 2);
        $saldoCapitalMora = $lastPaidCuota
            ? (float) $lastPaidCuota->saldo_capital
            : (float) $cuotas->sum('capital');

        $cuotas = $cuotas->map(function ($cuota, $index) use ($cuotas, $firstUnpaidIndex, &$dias_pasados_mora, $saldoCapitalMora) {

            // NORMALIZACIÓN DE FECHAS A 00:00:00
            $fechaCuota = Carbon::parse($cuota->fecha)->startOfDay();

            $fechaInicioCuota = $index === 0
                ? Carbon::parse($cuotas->first()->fecha_inicio)->startOfDay()
                : Carbon::parse($cuotas[$index - 1]->fecha)->startOfDay();

            // DÍAS REALES DEL PERIODO
            $diasPeriodoCuota = max(1, $fechaInicioCuota->diffInDays($fechaCuota, false));

            if ($cuota->estado == 2 && !empty($cuota->fecha_pago_real)) {
                $fechaCalculo = Carbon::parse($cuota->fecha_pago_real)->startOfDay();
            } else {
                $fechaCalculo = Carbon::now()->startOfDay();
            }

            $diasTranscurridosNormales = 0;
            $diasRetrasoCuota = 0;

            // CÁLCULO ESTRICTO DE DÍAS PASADOS (Informativo para Vue)
            if ($fechaCalculo->isAfter($fechaCuota)) {
                $diasRetrasoCuota = $fechaCuota->diffInDays($fechaCalculo, false);
            }
            $cuota->dias_pasados = in_array($cuota->estado, [1, 3]) ? $diasRetrasoCuota : 0;


            // DÍAS TRANSCURRIDOS (Para interés normal)
            if ($index === $firstUnpaidIndex && in_array($cuota->estado, [1, 3])) {
                $diasDesdeInicio = $fechaInicioCuota->diffInDays($fechaCalculo, false);
                $diasTranscurridosNormales = max(0, min($diasDesdeInicio, $diasPeriodoCuota));
                $dias_pasados_mora = $cuota->dias_mora_cobro;
            } else {
                if ($fechaCalculo->isAfter($fechaCuota) || $fechaCalculo->isSameDay($fechaCuota)) {
                    $diasTranscurridosNormales = $diasPeriodoCuota;
                } else {
                    $diasDesdeInicio = $fechaInicioCuota->diffInDays($fechaCalculo, false);
                    $diasTranscurridosNormales = max(0, min($diasDesdeInicio, $diasPeriodoCuota));
                }
            }

            // PAGOS ACUMULADOS
            $cap_pagado = isset($cuota->capital_pagado) ? (float)$cuota->capital_pagado : 0;
            $int_pagado = isset($cuota->interes_pagado) ? (float)$cuota->interes_pagado : 0;
            $mora_pagada = isset($cuota->mora_pagada) ? (float)$cuota->mora_pagada : 0;

            // INTERÉS DIARIO DE LA CUOTA — base común para devengado y moratorio
            $interesPorDiaNormal = ($diasPeriodoCuota > 0) ? ($cuota->interes / $diasPeriodoCuota) : 0;

            // INTERÉS DEVENGADO — interés diario × días transcurridos del período
            $interesDevengadoBruto = $interesPorDiaNormal * $diasTranscurridosNormales;

            // INTERÉS MORATORIO
            // Fórmula: (interés fijo de la cuota / días del lapso) × días de retraso
            if ($index === $firstUnpaidIndex && in_array($cuota->estado, [1, 3]) && $diasRetrasoCuota > 0) {
                $lapso = trim(strtolower($cuota->lapso_capital));

                if ($lapso == 'semanal') {
                    $diasDivisor = 7;
                } elseif ($lapso == 'quincenal') {
                    $diasDivisor = 15;
                } else {
                    $diasDivisor = 30;
                }

                $interesMoratorioBruto = ($cuota->interes / $diasDivisor) * $diasRetrasoCuota;
            } else {
                $interesMoratorioBruto = 0;
            }

            $interesTotalAcumuladoBruto = $interesDevengadoBruto + $interesMoratorioBruto;

            // PORCENTAJES AVANZADOS
            $porcentaje_capital = ($cuota->capital > 0) ? round(($cap_pagado / $cuota->capital) * 100, 1) : 0;
            $porcentaje_interes = ($interesTotalAcumuladoBruto > 0) ? round(($int_pagado / $interesTotalAcumuladoBruto) * 100, 1) : 0;

            // CÁLCULO DE RESTANTES (NETOS CASCADA)
            $capitalRestante = max(0, $cuota->capital - $cap_pagado);
            $intDevengadoRestante = max(0, $interesDevengadoBruto - $int_pagado);
            $excesoInt = max(0, $int_pagado - $interesDevengadoBruto);
            $intMoratorioRestante = max(0, $interesMoratorioBruto - $excesoInt);
            $interesTotalAcumuladoRestante = $intDevengadoRestante + $intMoratorioRestante;

            // MULTA FIJA ECONÓMICA
            $moraFijaBruta = ($cuota->dias_mora_cobro > 0) ? ($cuota->dias_mora_cobro * 3) : 0;
            $moraFijaRestante = max(0, $moraFijaBruta - $mora_pagada);
            $porcentaje_mora = ($moraFijaBruta > 0) ? round(($mora_pagada / $moraFijaBruta) * 100, 1) : 0;

            // ASIGNACIÓN AL OBJETO FINAL
            $cuota->dias_transcurridos = round($diasTranscurridosNormales);

            $cuota->capital_pagado_total = round($cap_pagado, 2);
            $cuota->interes_pagado_total = round($int_pagado, 2);
            $cuota->porcentaje_capital_pagado = $porcentaje_capital;
            $cuota->porcentaje_interes_pagado = $porcentaje_interes;
            $cuota->mora_pagada_total = round($mora_pagada, 2);
            $cuota->mora_bruta = round($moraFijaBruta, 2);
            $cuota->porcentaje_mora_pagada = $porcentaje_mora;

            $cuota->capital_neto = round($capitalRestante, 2);
            $cuota->interes_devengado_neto = round($intDevengadoRestante, 2);
            $cuota->interes_moratorio_neto = round($intMoratorioRestante, 2);
            $cuota->interes_acumulado_neto = round($interesTotalAcumuladoRestante, 2);
            $cuota->mora_fija_neta = round($moraFijaRestante, 2);

            // TOTAL A PAGAR de la cuota (capital + interés acumulado + mora fija, todos netos)
            $cuota->total_a_pagar = round($capitalRestante + $interesTotalAcumuladoRestante + $moraFijaRestante, 2);

            return $cuota;
        });

        return [
            'cuotas' => $cuotas,
            'dias_pasados_mora' => $dias_pasados_mora
        ];
    }
}
