<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use DB;

class LoginController extends Controller
{
    //
    public function irLogin(){
        return view('login_sesion');
    }

    public function loginProcess(Request $request){
        $request->validate(['name' => 'required|string|max:255', 'password' => 'required|string|max:1024']);
        // Validación
        $credenciales=[
            'name'=> $request->name,
            'password'=> $request->password,
            'estado'=> 1,
        ];

        if(Auth::attempt($credenciales)){

            if(Auth::user()->accesoActivo()){
                $request->session()->regenerate();
                return redirect()->intended(route('administracion'));


            }else{
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $mensaje='El usuario se encuentra INHABILITADO';
                return redirect()->route('ir_login')->with(['mensaje'=>$mensaje]);
            }
        }else{
            $mensaje='Usuario y contraseña INCORRECTOS';
            return redirect()->route('ir_login')->with(['mensaje'=>$mensaje]);
        }
    }

    public function administracion(){
        // Fase C: sirve el shell SPA; Vue Router resuelve el contenido
        // ("Panel Informativo") según la URL. Reemplaza a frmAdministracion.blade.php.
        return view('app');
    }

    public function logout(Request $request){
        Auth::Logout();// Laravel cierra la sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('ir_login');
    }

    public function permisoSistema($permiso, $id_rol){
        $permitido = DB::table('users')
        ->join('rol', 'rol.id', '=', 'users.id_rol')
        ->join('permiso_rol', 'permiso_rol.id_rol', '=', 'rol.id')
        ->join('permiso', 'permiso_rol.id_permiso', '=', 'permiso.id')
        ->where('permiso.nombre', $permiso)
        ->where('users.id_rol', $id_rol)
        ->where('users.estado', 1)->where('rol.estado', 1)->where('permiso.estado', 1)
        ->exists();

        return $permitido;
    }
}
