<?php

namespace Database\Factories;

use App\Models\Codeudor;
use Illuminate\Database\Eloquent\Factories\Factory;

class CodeudorFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente.
     *
     * @var string
     */
    protected $model = Codeudor::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nombre' => $this->faker->name(),
            // Fecha de nacimiento para que sea mayor de edad
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            
            'ci' => $this->faker->unique()->numerify('########'),
            
            'lugar_expedicion' => $this->faker->randomElement(['SC', 'LP', 'CB', 'OR', 'PT', 'TJ', 'BN', 'PD', 'CH']),
            
            'sexo' => $this->faker->randomElement(['Masculino', 'Femenino']),
            
            'estado_civil' => $this->faker->randomElement(['soltero/a', 'Casado/a', 'divorciado/a', 'viudo/a', 'separado/a', 'conviviente/a']),
            
            'actividad' => $this->faker->jobTitle(),
            
            'vivienda' => $this->faker->randomElement(['Casa propia', 'Alquiler', 'Vivienda familiar']),
            
            'imagen' => null, // Sin imagen para que tu sistema use la de por defecto
            
            // Ingreso entre 2,000 y 15,000 Bs
            'ingreso_mensual' => $this->faker->randomFloat(2, 2000, 15000), 
            
            'estado' => 1, // 1 = Activo
            
            // Aquí definimos si es Garante o Codeudor
            'tipo' => $this->faker->randomElement(['Garante', 'Codeudor']),
        ];
    }
}