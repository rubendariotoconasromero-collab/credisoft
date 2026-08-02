<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Datos de la sesión actual para el bootstrap del cliente SPA (Vue Router).
 *
 * No sustituye la seguridad de datos: los permisos aquí devueltos solo
 * sirven para que el cliente decida qué mostrar (menú, guards de rutas).
 * La protección real de cada endpoint sigue siendo el middleware
 * 'permiso:<nombre>' aplicado en routes/web.php.
 */
class SesionController extends Controller
{
    public function me()
    {
        $user = Auth::user();

        $permisos = DB::table('permiso_rol')
            ->join('permiso', 'permiso.id', '=', 'permiso_rol.id_permiso')
            ->where('permiso_rol.id_rol', $user->id_rol)
            ->where('permiso.estado', 1)
            ->pluck('permiso.nombre');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'personal' => $user->personal,
            'id_rol' => $user->id_rol,
            'role_name' => $user->role_name,
            'permisos' => $permisos,
        ]);
    }
}
