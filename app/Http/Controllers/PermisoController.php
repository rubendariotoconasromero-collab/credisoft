<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class PermisoController extends Controller
{
    //
    public function getPermisos(){
        return DB::table('permiso')->get();
    }
    public function getPermisoRol(Request $request){
        return DB::table('permiso')
        ->join('permiso_rol', 'permiso_rol.id_permiso', '=', 'permiso.id')
        ->join('rol', 'permiso_rol.id_rol', '=', 'rol.id')
        ->select('permiso.*')
        ->where('permiso_rol.id_rol', $request->id_rol)
        ->get();

    }
}
