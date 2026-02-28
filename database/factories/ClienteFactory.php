<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB; // Añadimos DB para poder consultar la tabla de actividades

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
        // 1. Ruta de tu archivo de constantes
        $jsonPath = base_path('constants.json');
        
        // 2. Leemos y decodificamos el JSON a un arreglo de PHP (¡Línea clave!)
        $constants = json_decode(file_get_contents($jsonPath), true);

        // 3. Extraemos solo la columna 'nombre' de cada arreglo de opciones
        $expeditionPlaces = array_column($constants['expeditionPlaces'], 'nombre');
        $genders = array_column($constants['genders'], 'nombre');
        $maritalStatuses = array_column($constants['maritalStatuses'], 'nombre');
        $housingTypes = array_column($constants['housingTypes'], 'nombre');

        return [
            // Datos personales básicos
            'nombre' => $this->faker->name(),
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'ci' => $this->faker->unique()->numerify('########'),
            
            // Usamos los datos extraídos del JSON
            'lugar_expedicion' => $this->faker->randomElement($expeditionPlaces),
            'sexo' => $this->faker->randomElement($genders),
            'estado_civil' => $this->faker->randomElement($maritalStatuses),
            'vivienda' => $this->faker->randomElement($housingTypes),
            
            // Consultamos una actividad al azar directamente desde la base de datos
            'actividad' => DB::table('actividades')->inRandomOrder()->value('nombre'),
            
            // Otros datos
            'imagen' => null, 
            'ingreso_mensual' => $this->faker->randomFloat(2, 2000, 25000), 
            'estado' => 1,
        ];
    }
}