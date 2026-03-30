<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Socio;

class SocioSeeder extends Seeder
{
    public function run()
    {
        // Genera 5 socios de prueba
        Socio::factory(5)->create();
    }
}