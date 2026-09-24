<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
class IngresoController extends Controller
{

    public function save(Request $request)
    {
        return app(\App\Services\Finance\CashMovements::class)->save($request, 'ingreso');
    }

    public function getIngresosCorrientes(Request $request){
        $ingresos = DB::table('ingreso')
        ->join('users', 'ingreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'ingreso.id_caja')
        ->select('users.name as asesor', 'ingreso.fecha', 'ingreso.monto as monto_ingreso', 'ingreso.id', 'ingreso.estado', 'ingreso.descripcion')
        ->where('caja.id', $request->id_caja)
        ->whereDate('ingreso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('ingreso.fecha', '<=', $request->fecha_final)
        ->orderBy('ingreso.id', 'desc')
        ->paginate(50);

        $totalIngresos = DB::table('ingreso')
        ->join('users', 'ingreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'ingreso.id_caja')
        ->select(DB::raw('sum(ingreso.monto) as totalIngresos'))
        ->where('caja.id', $request->id_caja)
        ->whereDate('ingreso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('ingreso.fecha', '<=', $request->fecha_final)
        ->get()[0]->totalIngresos;

        return ['ingresos'=>$ingresos, 'totalIngresos'=>$totalIngresos];

    }

    public function historialIngresosListado(Request $request)
    {
        return app(\App\Services\Finance\MovementHistory::class)->list($request, 'ingreso', false);
    }

    public function historialIngresosListadoCaja(Request $request)
    {
        return app(\App\Services\Finance\MovementHistory::class)->list($request, 'ingreso', true);
    }

    public function anularIngreso(Request $request)
    {
        return app(\App\Services\Finance\Reversals::class)->reverseSource($request, 'ingreso', (int) $request->id);
    }

}
