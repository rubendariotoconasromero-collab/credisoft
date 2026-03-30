<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermisoRolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $permisosRol = [];

        // 1. OBTENER TODOS LOS PERMISOS PARA EL ADMINISTRADOR
        // Obtenemos todos los IDs de los permisos que ya sembramos
        $permisos = DB::table('permiso')->pluck('id');

        // Asignamos cada uno de esos permisos al Rol 1 (Administrador)
        foreach ($permisos as $permisoId) {
            $permisosRol[] = [
                'id_permiso' => $permisoId,
                'id_rol' => 1, // ID del Administrador
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 2. ASIGNAR PERMISOS ESPECÍFICOS PARA EL CAJERO (id_rol = 2)
        // Según tu PermisoSeeder: 6 es 'listadopagos' y 7 es 'controlcaja'
        $permisosRol[] = [
            'id_permiso' => 6, 
            'id_rol' => 2, 
            'created_at' => $now, 
            'updated_at' => $now
        ];
        $permisosRol[] = [
            'id_permiso' => 7, 
            'id_rol' => 2, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        // 3. ASIGNAR PERMISOS ESPECÍFICOS PARA EL OFICIAL DE CRÉDITO (id_rol = 3)
        // Ejemplo: 8 es 'cliente', 9 es 'solicitudprestamos'
        $permisosRol[] = [
            'id_permiso' => 8, 
            'id_rol' => 3, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        $permisosRol[] = [
            'id_permiso' => 9, 
            'id_rol' => 3, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        $permisosRol[] = [
            'id_permiso' => 12, 
            'id_rol' => 3, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        $permisosRol[] = [
            'id_permiso' => 5, 
            'id_rol' => 3, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        // 3. ASIGNAR PERMISOS ESPECÍFICOS PARA EL ENCARGADO DE AGENCIA (id_rol = 4)
        $permisosRol[] = [
            'id_permiso' => 5, 
            'id_rol' => 4, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        $permisosRol[] = [
            'id_permiso' => 6, 
            'id_rol' => 4, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        $permisosRol[] = [
            'id_permiso' => 8, 
            'id_rol' => 4, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        $permisosRol[] = [
            'id_permiso' => 9, 
            'id_rol' => 4, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        $permisosRol[] = [
            'id_permiso' => 10, 
            'id_rol' => 4, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        $permisosRol[] = [
            'id_permiso' => 12, 
            'id_rol' => 4, 
            'created_at' => $now, 
            'updated_at' => $now
        ];

        // Insertamos toda la data en la tabla pivot 'permiso_rol'
        DB::table('permiso_rol')->insert($permisosRol);
    }
}