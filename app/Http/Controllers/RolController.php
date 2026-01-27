<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class RolController extends Controller
{
    //
    public function index(){
        return view('frmRoles');
    }

    // public function save(Request $request){
    //     //dd($request);
    //     DB::beginTransaction();
    //     //dd($request);
    //     try{
    //         $rolId = DB::table('rol')->insertGetId([
    //             'nombre' => $request->nombre,
    //         ]);
            
    //         foreach($request->permisos as $permiso){
    //             if($permiso['activado']){
    //                 DB::table('permiso_rol')->insert([
    //                     'id_permiso'=>$permiso['id'],
    //                     'id_rol'=>$rolId ,
    //                 ]);
    //             }
    //         }
    //         DB::commit();
    //     }catch(Exception $e){
    //         DB::rollback();
    //         //dd($e);
    //     }
        

    // }

    // public function modify(Request $request){
    //     //dd($request);
    //     DB::beginTransaction();
    //     try{
    //         DB::table('rol')->where('rol.id', $request->id_rol)->update([
    //             'nombre' => $request->nombre,
    //         ]);
            
    //         // foreach($request->permisos as $permiso){
    //             DB::table('permiso_rol')
    //             // ->where('id_permiso', $permiso['id'])
    //             ->where('id_rol', $request->id_rol )
    //             ->delete();
    //         // }
    //         foreach($request->permisos as $permiso){
    //             DB::table('permiso_rol')->insert([
    //                 'id_permiso'=>$permiso['id'],
    //                 'id_rol'=>$request->id_rol ,
    //             ]);
    //         }
    //         DB::commit();
    //     }catch(Exception $e){
    //         DB::rollback();
    //         dd($e);
    //     }
        

    // }

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();

            $rolId = DB::table('rol')->insertGetId([
                'nombre' => $request->nombre,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $activePermissions = array_filter($request->permisos, fn($permiso) => $permiso['activado']);
            $permissionsToInsert = array_map(fn($permiso) => [
                'id_permiso' => $permiso['id'],
                'id_rol' => $rolId,
                'created_at' => now(),
                'updated_at' => now(),
            ], $activePermissions);

            if (!empty($permissionsToInsert)) {
                DB::table('permiso_rol')->insert($permissionsToInsert);
            }

            DB::commit();

            return response()->json(['message' => 'Rol creado exitosamente'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al guardar el rol: ' . $e->getMessage()], 500);
        }
    }

    public function modify(Request $request)
    {
        try {
            DB::beginTransaction();

            DB::table('rol')
                ->where('id', $request->id_rol)
                ->update([
                    'nombre' => $request->nombre,
                    'updated_at' => now(),
                ]);

            DB::table('permiso_rol')
                ->where('id_rol', $request->id_rol)
                ->delete();

            $activePermissions = array_filter($request->permisos, fn($permiso) => $permiso['activado']);
            $permissionsToInsert = array_map(fn($permiso) => [
                'id_permiso' => $permiso['id'],
                'id_rol' => $request->id_rol,
                'created_at' => now(),
                'updated_at' => now(),
            ], $activePermissions);

            if (!empty($permissionsToInsert)) {
                DB::table('permiso_rol')->insert($permissionsToInsert);
            }

            DB::commit();

            return response()->json(['message' => 'Rol modificado exitosamente'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al modificar el rol: ' . $e->getMessage()], 500);
        }
    }

    public function getRoles(){
        return DB::table('rol')->get();
    }

    public function getRolesUsuarios(){
        return DB::table('rol')
        ->where('estado', 1)
        ->get();
    }

    public function activar(Request $request){
        DB::table('rol')->where('id', $request->id_rol)->update([
            'estado'=>1
        ]);
    }

    public function desactivar(Request $request){
        DB::table('rol')->where('id', $request->id_rol)->update([
            'estado'=>0
        ]);
    }
}
