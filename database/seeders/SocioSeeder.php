<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $socios = [
            [
                'nombres' => 'CARLOS ALBERTO',
                'apellidos' => 'RODRIGUEZ MENDOZA',
                'ci' => '4587123',
                'telefono' => '78541236',
                'direccion' => 'Av. San Martín #123',
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombres' => 'MARIA ELENA',
                'apellidos' => 'GONZALES PEREZ',
                'ci' => '6985214',
                'telefono' => '65214789',
                'direccion' => 'Calle Bolívar #456',
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombres' => 'JUAN PABLO',
                'apellidos' => 'VILLAGOMEZ SUAREZ',
                'ci' => '7412589',
                'telefono' => '71236547',
                'direccion' => 'Barrio Lindo, Calle 3',
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($socios as $socio) {
            DB::table('socios')->updateOrInsert(
                ['ci' => $socio['ci']],
                $socio
            );
        }
    }
}
