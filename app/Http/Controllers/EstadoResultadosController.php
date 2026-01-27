<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class EstadoResultadosController extends Controller
{
    //
    public function index(){
        return view('frmEstadoResultados');
    }

    public function getPagosAdministrativos(){
        $pagos_administrativos=DB::table('pago_administrativo')
        ->select(DB::raw('sum(pago_administrativo.monto) as sum_pago_administrativo'))
        ->get();

        return ['suma_pagos_administrativos'=> $pagos_administrativos[0]->sum_pago_administrativo];
    }

    public function getMontoIntereses(){
        $intereses_pagos_cuotas = DB::table('cuota')
        ->select(DB::raw('sum(cuota.interes) as sum_intereses_cuotas'))
        ->where('cuota.estado', 2)
        ->get();

        $intereses_amortizaciones = DB::table('pago_amortizacion')
        ->select(DB::raw('sum(pago_amortizacion.interes_pagado) as sum_intereses_amortizaciones'))
        ->get();



        return ['sum_total_interes'=> $intereses_pagos_cuotas[0]->sum_intereses_cuotas + $intereses_amortizaciones[0]->sum_intereses_amortizaciones];
    }

    public function getMontoMultas(Request $request){
        $multas_pagos = DB::table('pago')
        ->join('cuota', 'cuota.id', '=', 'pago.id_cuota')
        ->select(DB::raw('sum(pago.multa_total) as sum_multa_pagos'))
        ->where('cuota.estado', 2)
        ->get();

        $multas_amortizaciones = DB::table('pago_amortizacion')
        ->select(DB::raw('sum(pago_amortizacion.multa_pagada) as sum_multa_amortizaciones'))
        ->get();

        return ['suma_multas'=> $multas_pagos[0]->sum_multa_pagos + $multas_amortizaciones[0]->sum_multa_amortizaciones];

    }

    public function getMontoOtrosIngresos(Request $request){
        $sum_otros_ingresos = DB::table('ingreso')
        ->select(DB::raw('sum(ingreso.monto) as sum_otros_ingresos'))
        ->get();
        return ['otros_ingresos'=> $sum_otros_ingresos[0]->sum_otros_ingresos];

    }

    public function getMontoTotalEgresos(Request $request){
        $sum_egresos = DB::table('egreso')
        ->select(DB::raw('sum(egreso.monto) as sum_egresos'))
        ->get();

        $sum_desembolsos = DB::table('desembolso')
        ->select(DB::raw('sum(desembolso.monto) as sum_desembolsos'))
        ->get();
        return ['suma_total_egresos'=> $sum_egresos[0]->sum_egresos + $sum_desembolsos[0]->sum_desembolsos];
    }
}
