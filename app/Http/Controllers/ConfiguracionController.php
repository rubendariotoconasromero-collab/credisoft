<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function indexConfiguracion(){
        return view('frmConfiguracion');
    }

    public function getMotivosIngresos(Request $request){
        return DB::table('motivo_ingreso')->get();
    }

    public function getMotivosGastos(Request $request){
        return DB::table('motivo_gasto')->get();
    }

    // --- INGRESOS ---
    public function guardarMotivoIngreso(Request $request){
        DB::beginTransaction();
        try{
            DB::table('motivo_ingreso')->insertGetId([
                'nombre' => $request->nombre,
                'tipo'   => $request->tipo ?? 'caja',
            ]);
            DB::commit();
            return response()->json(['message' => 'Guardado correctamente'], 200);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function modificarMotivoIngreso(Request $request){
        DB::beginTransaction();
        try{
            DB::table('motivo_ingreso')->where('id', $request->id)->update([
                'nombre' => $request->nombre,
                'tipo'   => $request->tipo ?? 'caja', // <--- NUEVO CAMPO
            ]);
            DB::commit();
            return response()->json(['message' => 'Modificado correctamente'], 200);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // --- GASTOS / EGRESOS ---
    public function guardarMotivoGasto(Request $request){
        DB::beginTransaction();
        try{
            DB::table('motivo_gasto')->insertGetId([
                'nombre' => $request->nombre,
                'tipo'   => $request->tipo ?? 'caja', // <--- NUEVO CAMPO
            ]);
            DB::commit();
            return response()->json(['message' => 'Guardado correctamente'], 200);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function modificarMotivoGasto(Request $request){
        DB::beginTransaction();
        try{
            DB::table('motivo_gasto')->where('id', $request->id)->update([
                'nombre' => $request->nombre,
                'tipo'   => $request->tipo ?? 'caja', // <--- NUEVO CAMPO
            ]);
            DB::commit();
            return response()->json(['message' => 'Modificado correctamente'], 200);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // --- ESTADOS ---
    public function desactivarMotivoIngreso(Request $request){
        DB::table('motivo_ingreso')->where('id', $request->id)->update(['estado'=>1]);
    }
    public function activarMotivoIngreso(Request $request){
        DB::table('motivo_ingreso')->where('id', $request->id)->update(['estado'=>0]);
    }
    public function desactivarMotivoGasto(Request $request){
        DB::table('motivo_gasto')->where('id', $request->id)->update(['estado'=>1]);
    }
    public function activarMotivoGasto(Request $request){
        DB::table('motivo_gasto')->where('id', $request->id)->update(['estado'=>0]);
    }

    public function getMotivosIngresoPorTipo(Request $request)
    {
        $tipo = $request->input('tipo', 'caja'); 
        
        $motivos = DB::table('motivo_ingreso')
            ->where('estado', 0)
            ->where('tipo', $tipo)
            ->orderBy('nombre', 'asc')
            ->get();
            
        return response()->json($motivos);
    }

    public function getMotivosGastoPorTipo(Request $request)
    {
        $tipo = $request->input('tipo', 'caja');
        
        $motivos = DB::table('motivo_gasto')
            ->where('estado', 0)
            ->where('tipo', $tipo)
            ->orderBy('nombre', 'asc')
            ->get();
            
        return response()->json($motivos);
    }
}