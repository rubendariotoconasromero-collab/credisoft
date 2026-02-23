<?php

namespace Database\Factories;

use App\Models\Garantia;
use Illuminate\Database\Eloquent\Factories\Factory;

class GarantiaFactory extends Factory
{
    protected $model = Garantia::class;

    public function definition()
    {
        return [
            'descripcion' => $this->faker->sentence(10), // Una descripción aleatoria
            // 'id_solicitud' se llenará automáticamente
        ];
    }
}