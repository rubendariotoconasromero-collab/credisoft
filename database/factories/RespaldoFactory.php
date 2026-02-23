<?php

namespace Database\Factories;

use App\Models\Respaldo;
use Illuminate\Database\Eloquent\Factories\Factory;

class RespaldoFactory extends Factory
{
    protected $model = Respaldo::class;

    public function definition()
    {
        return [
            'descripcion' => $this->faker->words(3, true), // Ej: "Foto carnet anverso"
            'imagen' => 'default.png', // Simulamos que hay una imagen por defecto
        ];
    }
}