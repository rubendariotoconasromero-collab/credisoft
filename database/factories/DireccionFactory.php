<?php

namespace Database\Factories;

use App\Models\Direccion;
use Illuminate\Database\Eloquent\Factories\Factory;

class DireccionFactory extends Factory
{
    protected $model = Direccion::class;

    public function definition()
    {
        return [
            'tipo' => $this->faker->randomElement(['Casa', 'Oficina', 'Negocio', 'Apartamento']),
            'departamento' => $this->faker->randomElement(['Santa Cruz', 'La Paz', 'Cochabamba', 'Oruro', 'Potosí', 'Tarija', 'Chuquisaca', 'Beni', 'Pando']),
            'ciudad' => $this->faker->city(),
            'zona' => 'Zona ' . $this->faker->word(),
            'descripcion' => $this->faker->streetAddress(),
            // Coordenadas aleatorias dentro del rango de Bolivia aprox.
            'lat' => $this->faker->randomFloat(6, -22.0, -10.0), 
            'lng' => $this->faker->randomFloat(6, -69.0, -57.0),
            'referencia' => $this->faker->sentence(),
            // No ponemos 'id_cliente' ni 'id_codeudor' aquí, Laravel lo llenará automáticamente.
        ];
    }
}