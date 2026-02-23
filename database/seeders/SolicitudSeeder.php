<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Solicitud;
use App\Models\Cliente;
use App\Models\Codeudor;
use App\Models\User;
use App\Models\Garantia;
use App\Models\Respaldo;

class SolicitudSeeder extends Seeder
{
    public function run()
    {
        // 1. Obtenemos los registros que ya existen en la BD
        $clientes = Cliente::all();
        $codeudores = Codeudor::all();
        $usuarios = User::all();

        // Si no hay clientes o usuarios, detenemos el seeder para evitar errores
        if ($clientes->count() === 0 || $usuarios->count() === 0) {
            $this->command->info('Debe crear clientes y usuarios primero.');
            return;
        }

        // 2. Elegimos 30 clientes al azar para darles un crédito
        $clientesElegidos = $clientes->random(min(30, $clientes->count()));

        foreach ($clientesElegidos as $cliente) {
            
            // A. Creamos la Solicitud, adjuntándole garantías y respaldos
            $solicitud = Solicitud::factory()
                ->has(Garantia::factory()->count(rand(1, 2)), 'garantias')
                ->has(Respaldo::factory()->count(rand(1, 3)), 'respaldos')
                ->create([
                    'id_cliente' => $cliente->id, // Le asignamos el cliente del bucle
                    'id_usuario' => $usuarios->random()->id, // Un cajero/usuario al azar
                ]);

            // B. Le asignamos entre 1 y 2 Codeudores al azar (Relación Muchos a Muchos)
            if ($codeudores->count() > 0) {
                // Seleccionamos IDs de codeudores aleatorios
                $codeudoresAleatorios = $codeudores->random(rand(1, 2))->pluck('id')->toArray();
                
                // Los "enganchamos" en la tabla pivot (solicitud_codeudor)
                $solicitud->codeudores()->attach($codeudoresAleatorios);
            }
        }
    }
}