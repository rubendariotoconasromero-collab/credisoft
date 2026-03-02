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
        $importeSolicitud = $this->faker->numberBetween(3000, 50000);
        $montoPagoAdm = $importeSolicitud * 0.01;
        $fechaDesembolso = $this->faker->dateTimeBetween('-1 year', 'now');

        $lapsoSeleccionado = $this->faker->randomElement($nombresLapsos);
        $mesesBase = $this->faker->numberBetween(1, 12);
        $nroCuotas = match($lapsoSeleccionado) {
            'Semanal'   => $mesesBase * 4, // Múltiplo de 4
            'Quincenal' => $mesesBase * 2, // Múltiplo de 2
            default     => $mesesBase,     // Valor entero normal para Mensual
        };
        
        return [
            'importe_solicitud' => $importeSolicitud,
            'moneda' => 'Bolivianos',
            'lapso_capital' => $lapsoSeleccionado,
            'nro_cuotas' => $nroCuotas,
            'tasa' =>  $this->faker->numberBetween(10, 12),
            'fecha' => $fechaDesembolso,
            'fecha_desembolso' => $fechaDesembolso,
            'fecha_primera_cuota' => Carbon::parse($fechaDesembolso)->addMonth(),
            'destino_prestamo' => $this->faker->randomElement(['Capital de Inversión', 'Gastos Médicos', 'Compra de Vehículo', 'Refacción de Vivienda']),
            'tipo_garantia' => $this->faker->randomElement($nombresTiposGarantias),
            'tipo_desembolso' => $this->faker->randomElement($nombresTiposDesembolsos),
            'tipo_tasa' => $this->faker->randomElement(['fija','amortizable']),
            'desembolso' => 1,
            'monto_pago_adm' => $montoPagoAdm, // Gastos administrativos
            'estado' => 1, // 1: Nuevo, 2: Aprobado
            // 'observacion' => $this->faker->optional()->sentence(),
            'tipo_solicitud' => 'Nuevo',
            'cantidad_reprogramaciones' => 0,
            'cantidad_refinanciamientos' => 0,
            'monto_refinanciamiento' => 0,
            // 'id_cliente' y 'id_usuario' los inyectaremos en el Seeder
        ];
    }
}