<?php

namespace Database\Factories;

use App\Models\Codeudor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

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
        $jsonPath = base_path('constants.json');
        $expeditionPlaces = array_column($constants['expeditionPlaces'], 'nombre');
        $genders = array_column($constants['genders'], 'nombre');
        $maritalStatuses = array_column($constants['maritalStatuses'], 'nombre');
        $housingTypes = array_column($constants['housingTypes'], 'nombre');

        return [
            'nombre' => $this->faker->name(),
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'ci' => $this->faker->unique()->numerify('########'),
            'lugar_expedicion' => $this->faker->randomElement($expeditionPlaces),
            'sexo' => $this->faker->randomElement($genders),
            'estado_civil' => $this->faker->randomElement($maritalStatuses),
            'actividad' => DB::table('actividades')->inRandomOrder()->value('nombre'),
            'vivienda' => $this->faker->randomElement($housingTypes),
            'imagen' => null, 
            'ingreso_mensual' => $this->faker->randomFloat(2, 2000, 15000), 
            'estado' => 1, // 1 = Activo
            'tipo' => $this->faker->randomElement(['Garante', 'Codeudor']),
        ];
    }
}