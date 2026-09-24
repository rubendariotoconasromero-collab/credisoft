<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run()
    {
        foreach (['Administrador', 'Cajero', 'Oficial de Credito', 'Encargado de Agencia'] as $name) {
            DB::table('rol')->updateOrInsert(['nombre' => $name], ['estado' => 1]);
        }
    }
}
