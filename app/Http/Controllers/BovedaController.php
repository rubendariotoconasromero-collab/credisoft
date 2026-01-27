<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;


class BovedaController extends Controller
{
    //
    public function indexBoveda(){
        return view('frmBoveda');
    }

    public function getBoveda(Request $request){
        $boveda=DB::table('boveda')
        ->first();

        return [
            'saldo_actual'=>empty($boveda)?0:$boveda->saldo_actual,
            'fecha_apertura'=>empty($boveda)?0:$boveda->fecha_apertura,
            'id_boveda'=>empty($boveda)?0:$boveda->id,
        ];
    }

    public function getMovimientosBoveda(Request $request){
        $query = DB::table('movimientos_boveda')
        ->join('users', 'users.id', '=', 'movimientos_boveda.id_usuario')
        ->select('movimientos_boveda.*', 'users.personal');

        // Filtrar por tipo de movimiento (ingreso/salida/todos)
        if ($request->has('tipo') && $request->tipo != 'todos') {
            $query->where('movimientos_boveda.tipo_movimiento', $request->tipo);
        }

        // Filtrar por rango de fechas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereDate('movimientos_boveda.fecha', '>=', $request->fecha_inicio)
            ->whereDate('movimientos_boveda.fecha', '<=', $request->fecha_fin);
        }

        // Paginación
        $registros = $query->paginate(40);

        // Calcular totales
        $ingresos = $query->where('movimientos_boveda.tipo_movimiento', 'ingreso')->sum('monto');
        $salidas = $query->where('movimientos_boveda.tipo_movimiento', 'salida')->sum('monto');

        return response()->json([
            'movimientos' => $registros,
            'totales' => [
                'ingresos' => $ingresos,
                'salidas' => $salidas,
            ],
        ]);
    }

    public function ingresarBoveda(Request $request){
        $boveda_abierta=DB::table('boveda')->count();

        if($boveda_abierta<=0){
            return 0;
        }

        DB::beginTransaction();
        try{

            $id_boveda=DB::table('boveda')->orderBy('boveda.id', 'desc')->get()[0]->id;
    
            DB::table('movimientos_boveda')
            ->insertGetId([
                'tipo_movimiento'=>'ingreso',
                'monto'=>$request->monto,
                'descripcion'=>$request->descripcion,
                'fecha'=>now(),
                'id_boveda'=>$id_boveda,
                'id_usuario'=>Auth::user()->id,
            ]);
            

            DB::table('boveda')->where('id', $id_boveda)->update([
                'saldo_actual' => DB::raw('saldo_actual + ' . $request->monto)
            ]);

            DB::commit();

        }catch(Exception $e){
            DB::rollback();
        }
    }

    public function retirarBoveda(Request $request){
        $boveda_abierta=DB::table('boveda')->count();

        if($boveda_abierta<=0){
            return 0;
        }

        DB::beginTransaction();
        try{

            $id_boveda=DB::table('boveda')->orderBy('boveda.id', 'desc')->get()[0]->id;
    
            DB::table('movimientos_boveda')
            ->insertGetId([
                'tipo_movimiento'=>'salida',
                'monto'=>$request->monto,
                'descripcion'=>$request->descripcion,
                'fecha'=>now(),
                'id_boveda'=>$id_boveda,
                'id_usuario'=>Auth::user()->id,
            ]);

            DB::table('boveda')->where('id', $id_boveda)->update([
                'saldo_actual' => DB::raw('saldo_actual - ' . $request->monto)
            ]);

            DB::commit();

        }catch(Exception $e){
            DB::rollback();
        }
    }

    public function aperturarBoveda(Request $request){
        

        DB::beginTransaction();
        try{

        
            DB::table('boveda')->insertGetId([
                'saldo_actual' => 0,
                'fecha_apertura' => now(),
            ]);

            DB::commit();

        }catch(Exception $e){
            DB::rollback();
        }
    }
}
