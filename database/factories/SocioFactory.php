<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SocioFactory extends Factory
{
    public function definition()
    {
        return [
            'nombres' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName(),
            'ci' => $this->faker->unique()->randomNumber(8, true),
            'telefono' => $this->faker->phoneNumber(),
            'direccion' => $this->faker->address(),
            'email' => $this->faker->unique()->safeEmail(),
            'estado' => 1
        ];
    }
}