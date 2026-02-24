<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run()
    {
        DB::table('rol')->insert([
            ['nombre' => 'Administrador', 'estado' => 1],
            ['nombre' => 'Cajero', 'estado' => 1],
            ['nombre' => 'Oficial de Credito', 'estado' => 1],
        ]);
    }
}
