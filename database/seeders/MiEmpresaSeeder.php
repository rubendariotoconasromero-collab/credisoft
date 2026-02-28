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
        DB::table('mi_empresa')->insert([
            'id' => 1,
            'nombre' => 'Impulsa',
            'nit' => '12345678',
            'direccion' => 'Calle Avaroa # 125 - Montero - Santa Cruz',
            'telefono' => '71225632',
            'email' => 'impulsa@hotmail.com',
            'logo' => 'empresa/6878d37f35d25.png',
            'created_at' => null, // Tal como lo tienes en tu SQL
            'updated_at' => '2025-07-17 11:10:35',
        ]);
    }
}