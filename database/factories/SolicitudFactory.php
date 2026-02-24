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
        $jsonPath = base_path('constants.json');
        $jsonString = file_get_contents($jsonPath);
        $constants = json_decode($jsonString, true);
        $nombresLapsos = array_column($constants['lapso_capitales'], 'nombre');
        $nombresTiposGarantias = array_column($constants['tipos_garantias'], 'nombre');
        $nombresListaMonedas = array_column($constants['lista_monedas'], 'nombre');
        $nombresTiposDesembolsos = array_column($constants['tipos_desembolsos'], 'nombre');
        $nombresFormasPago = array_column($constants['formas_pago'], 'nombre');
        $importeSolicitud = $this->faker->randomFloat(2, 1000, 50000);
        $montoPagoAdm = $importeSolicitud * 0.01;
        $fechaDesembolso = $this->faker->dateTimeBetween('-1 year', 'now');
        
        return [
            'importe_solicitud' => $importeSolicitud,
            'moneda' => 'Bs',
            'lapso_capital' => $this->faker->randomElement($nombresLapsos),
            'nro_cuotas' => $this->faker->numberBetween(1, 24),
            'tasa' => $this->faker->randomFloat(2, 1.5, 5.0), // Tasa entre 1.5% y 5%
            'fecha' => $fechaDesembolso,
            'fecha_desembolso' => $fechaDesembolso,
            'fecha_primera_cuota' => Carbon::parse($fechaDesembolso)->addMonth(),
            'destino_prestamo' => $this->faker->randomElement(['Capital de Inversión', 'Gastos Médicos', 'Compra de Vehículo', 'Refacción de Vivienda']),
            'tipo_garantia' => $this->faker->randomElement($nombresTiposGarantias),
            'tipo_desembolso' => $this->faker->randomElement($nombresTiposDesembolsos),
            'tipo_tasa' => $this->faker->randomElement(['fija','amortizable']),
            'desembolso' => 1,
            'monto_pago_adm' => $montoPagoAdm, // Gastos administrativos
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