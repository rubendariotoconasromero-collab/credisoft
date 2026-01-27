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
        // Validación
        $credenciales=[
            'name'=> $request->name,
            'password'=> $request->password,
        ];

        if(Auth::attempt($credenciales)){

            if(Auth::user()->estado==1){
                $request->session()->regenerate();
                return redirect()->intended(route('administracion'));


            }else{
                $mensaje='El usuario se encuentra INHABILITADO';
                return redirect()->route('ir_login')->with(['mensaje'=>$mensaje]);
            }
        }else{
            $mensaje='Usuario y contraseña INCORRECTOS';
            return redirect()->route('ir_login')->with(['mensaje'=>$mensaje]);
        }
    }

    public function administracion(){
        return view('frmAdministracion');
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
        ->exists();

        return $permitido;
    }
}
