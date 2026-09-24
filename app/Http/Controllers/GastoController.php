<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\CalculaSaldoCaja;
use DB;
class GastoController extends Controller
{
    use CalculaSaldoCaja;

    public function save(Request $request)
    {
        return app(\App\Services\Finance\CashMovements::class)->save($request, 'egreso');
    }

    public function getGastosCorrientes(Request $request){
        $gastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->select('users.name as asesor', 'egreso.fecha', 'egreso.monto as monto_gasto', 'egreso.id', 'egreso.estado', 'egreso.descripcion')
        ->where('caja.id', $request->id_caja)
        ->whereDate('egreso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('egreso.fecha', '<=', $request->fecha_final)
        ->paginate(40);

        $totalGastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->select(DB::raw('sum(egreso.monto) as totalIngresos'))
        ->where('caja.id', $request->id_caja)
        ->whereDate('egreso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('egreso.fecha', '<=', $request->fecha_final)
        ->get()[0]->totalIngresos;



        return ['gastos'=>$gastos, 'totalGastos'=>$totalGastos];

    }

    public function historialGastosListado(Request $request)
    {
        return app(\App\Services\Finance\MovementHistory::class)->list($request, 'egreso', false);
    }

    public function historialEgresosListadoCaja(Request $request)
    {
        return app(\App\Services\Finance\MovementHistory::class)->list($request, 'egreso', true);
    }

    public function anularGasto(Request $request)
    {
        return app(\App\Services\Finance\Reversals::class)->reverseSource($request, 'egreso', (int) $request->id);
    }

}
