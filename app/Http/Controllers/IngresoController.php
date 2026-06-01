<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
class IngresoController extends Controller
{

    public function save(Request $request){
        DB::beginTransaction();
        try{

            $id_caja = DB::table('caja')->where('estado', 1)->first()?->id;

            $descripcionFinal = ($request->descripcion == 'Otros ingresos')
                ? $request->descripcion . ' - ' . $request->descripcion_otro
                : $request->descripcion;

            DB::table('ingreso')->insert([
                'monto'       => $request->monto,
                'descripcion' => $descripcionFinal,
                'id_usuario'  => Auth::id(),
                'fecha'       => now(),
                'id_caja'     => $id_caja,
            ]);

            // Reg. en mov. caja
            DB::table('movimientos_caja')
            ->insertGetId([
                'tipo_movimiento'=>'ingreso',
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
        $fechaInicio = $request->fecha_inicio;
        $fechaFinal = $request->fecha_final;
        $criterio = $request->criterio;
        $buscar = $request->buscar;

        $ingresos = DB::table('ingreso')
            ->join('users', 'ingreso.id_usuario', '=', 'users.id')
            ->join('caja', 'caja.id', '=', 'ingreso.id_caja')
            ->select('users.name as asesor', 'ingreso.fecha', 'ingreso.monto as monto_ingreso', 'ingreso.id', 'ingreso.estado', 
            'ingreso.descripcion', 'users.personal', 'caja.estado as estado_caja')
            ->whereDate('ingreso.fecha', '>=', $fechaInicio)
            ->whereDate('ingreso.fecha', '<=', $fechaFinal)
            ->where($criterio, 'like', '%'.$buscar.'%')
            ->orderBy('ingreso.id', 'desc')
            ->paginate(50);

        $totalIngresos = DB::table('ingreso')
            ->join('users', 'ingreso.id_usuario', '=', 'users.id')
            ->join('caja', 'caja.id', '=', 'ingreso.id_caja')
            ->whereDate('ingreso.fecha', '>=', $fechaInicio)
            ->whereDate('ingreso.fecha', '<=', $fechaFinal)
            ->where('ingreso.estado', '=', 1)
            ->sum('ingreso.monto');

        return [
            'ingresos' => $ingresos,
            'totalIngresos' => $totalIngresos,
        ];
    }

    public function historialIngresosListadoCaja(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFinal = $request->fecha_final;
        $criterio = $request->criterio;
        $buscar = $request->buscar;

        $ingresos = DB::table('ingreso')
            ->join('users', 'ingreso.id_usuario', '=', 'users.id')
            ->join('caja', 'caja.id', '=', 'ingreso.id_caja')
            ->select('users.name as asesor', 'ingreso.fecha', 'ingreso.monto as monto_ingreso', 'ingreso.id', 'ingreso.estado', 
            'ingreso.descripcion', 'users.personal', 'caja.estado as estado_caja')
            ->whereDate('ingreso.fecha', '>=', $fechaInicio)
            ->whereDate('ingreso.fecha', '<=', $fechaFinal)
            ->where('caja.estado', 1)
            ->where($criterio, 'like', '%'.$buscar.'%')
            ->orderBy('ingreso.id', 'desc')
            ->paginate(50);

        $totalIngresos = DB::table('ingreso')
            ->join('users', 'ingreso.id_usuario', '=', 'users.id')
            ->join('caja', 'caja.id', '=', 'ingreso.id_caja')
            ->whereDate('ingreso.fecha', '>=', $fechaInicio)
            ->whereDate('ingreso.fecha', '<=', $fechaFinal)
            ->where('ingreso.estado', '=', 1)
            ->where('caja.estado', 1)
            ->sum('ingreso.monto');

        return [
            'ingresos' => $ingresos,
            'totalIngresos' => $totalIngresos,
        ];
    }

    

    public function anularIngreso(Request $request)
    {
        $ingreso = DB::table('ingreso')->where('id', $request->id)->select('monto', 'descripcion')->first();
        $monto   = (float) ($ingreso->monto ?? 0);

        $anular = DB::table('ingreso')
            ->join('caja', 'caja.id', '=', 'ingreso.id_caja')
            ->where('ingreso.id', $request->id)
            ->where('caja.estado', 1)->exists();

        if ($anular) {
            DB::table('ingreso')
                ->join('caja', 'caja.id', '=', 'ingreso.id_caja')
                ->where('ingreso.id', $request->id)
                ->where('caja.estado', 1)
                ->update(['ingreso.estado' => 0]);

            return response()->json(['respuesta' => 1]);
        } else {
            return response()->json(['respuesta' => 0]);
        }
    }
}
