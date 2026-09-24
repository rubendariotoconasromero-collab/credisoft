<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoRolSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = DB::table('permiso')->pluck('id', 'nombre');
        $roles = DB::table('rol')->pluck('id', 'nombre');
        $matrix = [
            'Administrador' => $permissions->keys()->all(),
            'Cajero' => ['paneladministracion', 'listadopagos', 'controlcaja'],
            'Oficial de Credito' => ['paneladministracion', 'cliente', 'codeudores', 'solicitudprestamos', 'planpagos'],
            'Encargado de Agencia' => ['paneladministracion', 'cliente', 'codeudores', 'solicitudprestamos',
                'planpagos', 'listadopagos', 'reportes', 'consultasfinancieras', 'boveda', 'socios'],
        ];
        foreach ($matrix as $role => $names) {
            foreach ($names as $name) {
                DB::table('permiso_rol')->updateOrInsert(
                    ['id_rol' => $roles[$role], 'id_permiso' => $permissions[$name]],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
