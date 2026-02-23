<?php

namespace Database\Factories;

use App\Models\Telefono;
use Illuminate\Database\Eloquent\Factories\Factory;

class TelefonoFactory extends Factory
{
    protected $model = Telefono::class;

    public function definition()
    {
        // Simulamos la lógica que tienes en el frontend (Vue)
        $tipo = $this->faker->randomElement(['Numero telefono', 'Informacion contacto']);

        return [
            'tipo' => $tipo,
            'numero' => $this->faker->numerify('7#######'), // Genera celulares de 8 dígitos empezando con 7
            
            // Si es un número normal, le ponemos observación, sino nulo
            'observacion' => $tipo === 'Numero telefono' ? $this->faker->sentence(3) : null,
            
            // Si es info de contacto, llenamos nombre, apellidos y relación
            'nombre' => $tipo === 'Informacion contacto' ? $this->faker->firstName() : null,
            'apellidos' => $tipo === 'Informacion contacto' ? $this->faker->lastName() : null,
            'relacion' => $tipo === 'Informacion contacto' ? $this->faker->randomElement(['Familiar', 'Amigo', 'Compañero de Trabajo']) : null,
        ];
    }
}