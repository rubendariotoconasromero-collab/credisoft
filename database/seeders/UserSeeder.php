<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        // La contraseña por defecto será 'password123' para todos
        $password = Hash::make('admin123'); 

        $users = [
            [
                'name' => 'administrador',
                'personal' => 'Juan Perez (Administrador)',
                'email' => 'admin@empresa.com',
                'estado' => 1,
                'password' => $password,
                'id_rol' => 1, // ID del rol Administrador
                'ci' => '12345678',
                'telefono' => '70000001',
                'fecha_cambio_password' => $now,
                'dias_vigencia' => 90,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'cajero_user',
                'personal' => 'Maria Gomez (Cajera)',
                'email' => 'cajero@empresa.com',
                'estado' => 1,
                'password' => $password,
                'id_rol' => 2, // ID del rol Cajero
                'ci' => '87654321',
                'telefono' => '70000002',
                'fecha_cambio_password' => $now,
                'dias_vigencia' => 90,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'oficial_user',
                'personal' => 'Carlos Lopez (Oficial)',
                'email' => 'oficial@empresa.com',
                'estado' => 1,
                'password' => $password,
                'id_rol' => 3, // ID del rol Oficial de Credito
                'ci' => '11223344',
                'telefono' => '70000003',
                'fecha_cambio_password' => $now,
                'dias_vigencia' => 90,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'encargado_user',
                'personal' => 'Ana Torres (Encargada)',
                'email' => 'encargado@empresa.com',
                'estado' => 1,
                'password' => $password,
                'id_rol' => 4, // ID del rol Encargado de Agencia
                'ci' => '55667788',
                'telefono' => '70000004',
                'fecha_cambio_password' => $now,
                'dias_vigencia' => 90,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('users')->insert($users);
    }
}