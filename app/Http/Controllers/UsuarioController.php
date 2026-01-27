<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsuarioController extends Controller
{
    //
    public function index(){
        return view('frmUsuarios');
    }

    // public function getUsuarios(Request $request){
    //     return DB::table('users')->join('rol', 'users.id_rol', '=', 'rol.id')
    //     ->select('users.*', 'rol.nombre as rol')
    //     ->where($request->criterio, 'like', '%'.$request->buscar.'%')
    //     ->orderBy('users.id', 'desc')
    //     ->paginate(50);
    // }

    public function getUsuarios(Request $request){
        $usuarios = DB::table('users')->join('rol', 'users.id_rol', '=', 'rol.id')
        ->select('users.*', 'rol.nombre as rol')
        ->where($request->criterio, 'like', '%'.$request->buscar.'%')
        ->orderBy('users.id', 'desc')
        ->paginate(50);

        // Agregamos lógica para calcular días restantes
        foreach ($usuarios as $user) {
            if ($user->fecha_cambio_password && $user->dias_vigencia) {
                $fechaCambio = Carbon::parse($user->fecha_cambio_password);
                $fechaVencimiento = $fechaCambio->addDays($user->dias_vigencia);
                $diasRestantes = Carbon::now()->diffInDays($fechaVencimiento, false); // false para permitir negativos

                $user->dias_restantes = (int)$diasRestantes;
                $user->estado_password = $diasRestantes < 0 ? 'Vencida' : ($diasRestantes <= 7 ? 'Por vencer' : 'Vigente');
            } else {
                $user->dias_restantes = null;
                $user->estado_password = 'No configurado';
            }
        }

        return $usuarios;
    }

    public function activar(Request $request){
        DB::table('users')->where('users.id', $request->id_usuario)->update([
            'estado'=>1
        ]);
    }

    public function desactivar(Request $request){
        DB::table('users')->where('users.id', $request->id_usuario)->update([
            'estado'=>0
        ]);
    }

    public function save(Request $request){
        // DB::table('users')->insert([
        //     'name'=>$request->nombre,
        //     'ci'=>$request->ci,
        //     'telefono'=>$request->telefono,
        //     'personal'=>$request->personal,
        //     'email'=>$request->email,
        //     'password'=>Hash::make($request->password),
        //     'id_rol'=>$request->id_rol,
        // ]);

        DB::table('users')->insert([
            'name'=>$request->nombre,
            'ci'=>$request->ci,
            'telefono'=>$request->telefono,
            'personal'=>$request->personal,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'id_rol'=>$request->id_rol,
            // NUEVOS CAMPOS
            'dias_vigencia' => $request->dias_vigencia ?? 90, // Si no envía, pone 90
            'fecha_cambio_password' => Carbon::now() // Fecha de hoy
        ]);

    }

    public function modify(Request $request){
        // DB::table('users')->where('id', $request->id_usuario)->update([
        //     'name'=>$request->nombre,
        //     'ci'=>$request->ci,
        //     'telefono'=>$request->telefono,
        //     'personal'=>$request->personal,
        //     'email'=>$request->email,
        //     'password'=>Hash::make($request->password),
        //     'id_rol'=>$request->id_rol,
        // ]);

        $datos = [
            'name'=>$request->nombre,
            'ci'=>$request->ci,
            'telefono'=>$request->telefono,
            'personal'=>$request->personal,
            'email'=>$request->email,
            'id_rol'=>$request->id_rol,
            'dias_vigencia' => $request->dias_vigencia, // Actualizar configuración de días
        ];

        // Solo si el usuario escribió una nueva contraseña, actualizamos el hash y la fecha
        if (!empty($request->password)) {
            $datos['password'] = Hash::make($request->password);
            $datos['fecha_cambio_password'] = Carbon::now();
        }

        DB::table('users')->where('id', $request->id_usuario)->update($datos);

    }

    public function getUsuariosSin(){
        return DB::table('users')->join('rol', 'users.id_rol', '=', 'rol.id')
        ->select('users.*', 'rol.nombre as rol')->get();
    }
}
