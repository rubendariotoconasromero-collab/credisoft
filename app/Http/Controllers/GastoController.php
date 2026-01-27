<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
class GastoController extends Controller
{
    //
    public function save(Request $request){
        DB::beginTransaction();
        try{
            
            $id_caja=DB::table('caja')->where('estado', 1)->get()[0]->id;
            DB::table('egreso')->insert([
                'monto'=>$request->monto,
                // 'descripcion'=>$request->descripcion,
                'descripcion'=>($request->descripcion=='Otros gastos')?$request->descripcion .' - '.$request->descripcion_otro: $request->descripcion,
                'id_usuario'=>Auth::id(),
                'fecha'=>now(),
                'id_caja'=>$id_caja,
            ]);

            // reg. mov. caja
            // Reg. en mov. caja
            DB::table('movimientos_caja')
            ->insertGetId([
                'tipo_movimiento'=>'salida',
                'monto'=>$request->monto,
                'descripcion'=>($request->descripcion=='Otros ingresos')?$request->descripcion .' - '.$request->descripcion_otro: $request->descripcion,
                'fecha'=>now(),
                'id_caja'=>$id_caja,
                'id_usuario'=>Auth::user()->id,
            ]);


            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }

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

    public function historialGastosListado(Request $request){

        $fechaInicio = $request->fecha_inicio;
        $fechaFinal = $request->fecha_final;
        $criterio = $request->criterio;
        $buscar = $request->buscar;

        $gastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->select('users.name as asesor', 'egreso.fecha', 'egreso.monto as monto_gasto', 'egreso.id', 'egreso.estado', 
        'egreso.descripcion', 'users.personal', 'caja.estado as estado_caja')
        ->whereDate('egreso.fecha', '>=',  $fechaInicio)
        ->whereDate('egreso.fecha', '<=',  $fechaFinal)
        ->where($criterio, 'like', '%'.$buscar.'%')
        ->orderBy('egreso.id', 'desc')
        ->paginate(50);

        $totalGastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->whereDate('egreso.fecha', '>=', $fechaInicio)
        ->whereDate('egreso.fecha', '<=', $fechaFinal)
        ->where('egreso.estado', '=', 1)
        ->sum('egreso.monto');

        return ['gastos'=>$gastos, 'totalGastos'=>$totalGastos];
        
    }

    public function historialEgresosListadoCaja(Request $request){

        $fechaInicio = $request->fecha_inicio;
        $fechaFinal = $request->fecha_final;
        $criterio = $request->criterio;
        $buscar = $request->buscar;

        $gastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->select('users.name as asesor', 'egreso.fecha', 'egreso.monto as monto_gasto', 'egreso.id', 'egreso.estado', 
        'egreso.descripcion', 'users.personal', 'caja.estado as estado_caja')
        ->whereDate('egreso.fecha', '>=',  $fechaInicio)
        ->whereDate('egreso.fecha', '<=',  $fechaFinal)
        ->where($criterio, 'like', '%'.$buscar.'%')    
        ->where('caja.estado', 1)
        ->orderBy('egreso.id', 'desc')
        ->paginate(50);

        $totalGastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->whereDate('egreso.fecha', '>=', $fechaInicio)
        ->whereDate('egreso.fecha', '<=', $fechaFinal)
        ->where('caja.estado', 1)
        ->where('egreso.estado', '=', 1)
        ->sum('egreso.monto');

        return ['egresos'=>$gastos, 'totalEgresos'=>$totalGastos];
        
    }

    

    public function anularGasto(Request $request){
        $anular= DB::table('egreso')
            ->join('caja', 'caja.id', '=', 'egreso.id_caja')
            ->where('egreso.id', $request->id)
            ->where('caja.estado', 1)->exists();

        if($anular){
            DB::table('egreso')
                ->join('caja', 'caja.id', '=', 'egreso.id_caja')
                ->where('egreso.id', $request->id)
                ->where('caja.estado', 1)
                ->update(['egreso.estado' => 0]);
            return response()->json(['respuesta' => 1]);
        }else{
            return response()->json(['respuesta' => 0]);
        }
    }
}
