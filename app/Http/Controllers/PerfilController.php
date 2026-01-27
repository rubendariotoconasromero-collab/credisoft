<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerfilController extends Controller
{
    // Obtener datos del usuario logueado
    public function index(){
        return view('PerfilUsuario');
    }

    public function getPerfil()
    {
        $user = Auth::user();
        // Cargamos el nombre del rol para mostrarlo
        $rol = DB::table('rol')->where('id', $user->id_rol)->value('nombre');
        
        // Calculamos días restantes para mostrar en el perfil
        $diasRestantes = null;
        if ($user->fecha_cambio_password && $user->dias_vigencia) {
            $fechaVencimiento = Carbon::parse($user->fecha_cambio_password)->addDays($user->dias_vigencia);
            $diasRestantes = Carbon::now()->diffInDays($fechaVencimiento, false);
        }

        return response()->json([
            'usuario' => $user,
            'rol_nombre' => $rol,
            'dias_restantes' => (int)$diasRestantes
        ]);
    }

    // Actualizar Información Básica (Sin contraseña)
    public function updateInformacion(Request $request)
    {
        $request->validate([
            'personal' => 'required|string|max:255',
            'name' => 'required|string|max:255', // Nombre de usuario
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'ci' => 'required',
            'telefono' => 'nullable'
        ]);

        DB::table('users')->where('id', Auth::id())->update([
            'personal' => $request->personal,
            'name' => $request->name,
            'email' => $request->email,
            'ci' => $request->ci,
            'telefono' => $request->telefono,
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Información actualizada correctamente.']);
    }

    // Actualizar Solo Contraseña
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // 'confirmed' busca new_password_confirmation
        ]);

        $user = Auth::user();

        // 1. Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['La contraseña actual es incorrecta.']]
            ], 422);
        }

        // 2. Actualizar contraseña y fecha de cambio
        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($request->new_password),
            'fecha_cambio_password' => Carbon::now(), // ¡Importante para renovar vigencia!
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Contraseña actualizada con éxito.']);
    }
}