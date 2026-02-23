<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Codeudor;
use App\Models\Direccion;
use App\Models\Telefono;

class CodeudorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vamos a crear 30 codeudores de prueba
        // Cada uno tendrá 1 dirección y 1 o 2 teléfonos
        Codeudor::factory(30)
            ->has(Direccion::factory()->count(1), 'direcciones')
            ->has(Telefono::factory()->count(rand(1, 2)), 'telefonos')
            ->create();
    }
}