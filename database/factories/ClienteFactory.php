<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente.
     *
     * @var string
     */
    protected $model = Cliente::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // faker->name() genera nombres aleatorios reales
            'nombre' => $this->faker->name(), 
            
            // Genera una fecha de nacimiento asegurando que tenga al menos 18 años
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            
            // Un número de CI único de 7 u 8 dígitos
            'ci' => $this->faker->unique()->numerify('########'), 
            
            // Elegimos un lugar de expedición al azar basado en tus datos
            'lugar_expedicion' => $this->faker->randomElement(['SC', 'LP', 'CB', 'OR', 'PT', 'TJ', 'BN', 'PD', 'CH']),
            
            'sexo' => $this->faker->randomElement(['Masculino', 'Femenino']),
            
            'estado_civil' => $this->faker->randomElement(['soltero/a', 'Casado/a', 'divorciado/a', 'viudo/a']),
            
            'actividad' => $this->faker->jobTitle(), // Títulos de trabajo aleatorios
            
            'vivienda' => $this->faker->randomElement(['Casa propia', 'Alquiler', 'Vivienda familiar']),
            
            'imagen' => null, // Por ahora sin imagen para las pruebas
            
            // Ingreso entre 2,000 y 25,000 Bs
            'ingreso_mensual' => $this->faker->randomFloat(2, 2000, 25000), 
            
            'estado' => 1, // Por defecto activos
        ];
    }
}