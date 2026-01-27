<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class ConfiguracionController extends Controller
{
    //

    public function indexConfiguracion(){
        return view('frmConfiguracion');
    }

    public function getMotivosIngresos(Request $request){
        return DB::table('motivo_ingreso')
        ->get();
    }

    public function getMotivosGastos(Request $request){
        return DB::table('motivo_gasto')
        ->get();
    }

    public function guardarMotivoIngreso(Request $request){
        DB::beginTransaction();

        try{
            DB::table('motivo_ingreso')
            ->insertGetId([
                'nombre'=>$request->nombre,
                // 'nombre'=>$request->nombre,
            ]);
            DB::commit();
        }catch(Exception $e){
            DB::rollback();

        }
    }
    public function guardarMotivoGasto(Request $request){
        DB::beginTransaction();

        try{
            DB::table('motivo_gasto')
            ->insertGetId([
                'nombre'=>$request->nombre,
                // 'nombre'=>$request->nombre,
            ]);
            DB::commit();
        }catch(Exception $e){
            DB::rollback();

        }
    }

    public function modificarMotivoIngreso(Request $request){
        DB::beginTransaction();

        try{
            DB::table('motivo_ingreso')
            ->where('id', $request->id)
            ->update([
                'nombre'=>$request->nombre,
                // 'nombre'=>$request->nombre,
            ]);
            DB::commit();
        }catch(Exception $e){
            DB::rollback();

        }
    }
    public function modificarMotivoGasto(Request $request){
        DB::beginTransaction();

        try{
            DB::table('motivo_gasto')
            ->where('id', $request->id)
            ->update([
                'nombre'=>$request->nombre,
                // 'nombre'=>$request->nombre,
            ]);
            DB::commit();
        }catch(Exception $e){
            DB::rollback();

        }
    }

    public function desactivarMotivoIngreso(Request $request){
            DB::table('motivo_ingreso')
            ->where('id', $request->id)
            ->update([
                'estado'=>1,
                // 'nombre'=>$request->nombre,
            ]);
    }
    public function activarMotivoIngreso(Request $request){
        DB::table('motivo_ingreso')
        ->where('id', $request->id)
        ->update([
            'estado'=>0,
            // 'nombre'=>$request->nombre,
        ]);
    }


    public function desactivarMotivoGasto(Request $request){
        DB::table('motivo_gasto')
        ->where('id', $request->id)
        ->update([
            'estado'=>1,
            // 'nombre'=>$request->nombre,
        ]);
    }
    public function activarMotivoGasto(Request $request){
        DB::table('motivo_gasto')
        ->where('id', $request->id)
        ->update([
            'estado'=>0,
            // 'nombre'=>$request->nombre,
        ]);
    }
}
