<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente; // Asegúrate de importar el modelo
use App\Models\Direccion;
use App\Models\Telefono;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ¡Vamos a crear 50 clientes mágicamente!
        Cliente::factory(50)
            ->has(Direccion::factory()->count(1), 'direcciones')
            ->has(Telefono::factory()->count(2), 'telefonos')
            ->create();
    }
}