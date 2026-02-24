<?php

namespace Database\Factories;

use App\Models\Solicitud;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class SolicitudFactory extends Factory
{
    protected $model = Solicitud::class;

    public function definition()
    {
        $fechaDesembolso = $this->faker->dateTimeBetween('-1 year', 'now');
        
        return [
            'importe_solicitud' => $this->faker->randomFloat(2, 1000, 50000), // Entre 1000 y 50000 Bs
            'moneda' => 'Bs',
            'lapso_capital' => $this->faker->randomElement(['Mensual', 'Semanal', 'Diario']),
            'nro_cuotas' => $this->faker->numberBetween(1, 24),
            'tasa' => $this->faker->randomFloat(2, 1.5, 5.0), // Tasa entre 1.5% y 5%
            'fecha' => $fechaDesembolso,
            'fecha_desembolso' => $fechaDesembolso,
            'fecha_primera_cuota' => Carbon::parse($fechaDesembolso)->addMonth(),
            'destino_prestamo' => $this->faker->randomElement(['Capital de Inversión', 'Gastos Médicos', 'Compra de Vehículo', 'Refacción de Vivienda']),
            'tipo_garantia' => $this->faker->randomElement(['Personal', 'Prendaria', 'Hipotecaria', 'A Sola Firma']),
            'tipo_desembolso' => 'Efectivo',
            'tipo_tasa' => 'amortizable',
            'desembolso' => 1,
            'monto_pago_adm' => $this->faker->randomFloat(2, 50, 300), // Gastos administrativos
            'estado' => $this->faker->randomElement([1, 2]), // 1: Nuevo, 2: Aprobado
            'observacion' => $this->faker->optional()->sentence(),
            'tipo_solicitud' => 'Nuevo',
            'cantidad_reprogramaciones' => 0,
            'cantidad_refinanciamientos' => 0,
            'monto_refinanciamiento' => 0,
            // 'id_cliente' y 'id_usuario' los inyectaremos en el Seeder
        ];
    }
}