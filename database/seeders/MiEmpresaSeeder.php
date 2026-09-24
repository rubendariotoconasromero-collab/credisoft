<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MiEmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('mi_empresa')->exists()) return;
        DB::table('mi_empresa')->insert([
            'id' => 1,
            'nombre' => 'Credisoft DEMO',
            'nit' => '0000000000',
            'direccion' => 'Dirección ficticia de desarrollo - Montero',
            'telefono' => '00000000',
            'email' => 'empresa@example.test',
            'logo' => 'logo_sistema_codesoft.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
