<?php

namespace App\Exports;

use App\Models\PlanPago;
use App\Models\Cuota;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PlanPagoExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
    * Preparamos la consulta base cargando las relaciones para optimizar (Eager Loading)
    */
    public function query()
    {
        return PlanPago::query()
            ->with([
                'solicitud', 
                'solicitud.cliente', 
                'solicitud.cliente.telefonos', 
                'solicitud.usuario', 
                'cuotas'
            ])
            ->orderBy('id', 'desc');
    }

    /**
    * Aquí realizamos la lógica de cálculo para cada fila
    * @param mixed $plan
    */
    public function map($plan): array
    {
        $solicitud = $plan->solicitud;
        $cliente = $solicitud->cliente;
        $usuario = $solicitud->usuario; // Asesor / Oficial
        $cuotas = $plan->cuotas;
        
        // Fechas
        $fechaRegistro = Carbon::parse($plan->fecha_registro);
        $fechaActual = Carbon::now();

        // 1. Teléfonos (Concatenar con salto de línea)
        $telefonos = $cliente->telefonos->pluck('numero')->implode(",\n");

        // 2. Plazo en Meses (Cálculo Inverso)
        $plazoMeses = 0;
        $nroCuotas = $plan->nro_cuotas;
        switch ($plan->lapso_capital) { // O $solicitud->lapso_capital
            case 'Semanal':
                $plazoMeses = round($nroCuotas / 4, 1);
                break;
            case 'Quincenal':
                $plazoMeses = round($nroCuotas / 2, 1);
                break;
            case 'Mensual':
                $plazoMeses = $nroCuotas;
                break;
            default:
                $plazoMeses = $nroCuotas; // Diario u otro
        }

        // 3. Cálculos de Cuotas (Pagadas, Saldo, Mora)
        // Estado 2 = Pagado, Estado 1 = Pendiente
        
        $cuotasPagadas = $cuotas->where('estado', 2);
        
        // Pendientes VIGENTES (Fecha pago >= hoy)
        $cuotasSaldo = $cuotas->filter(function ($cuota) use ($fechaActual) {
            return $cuota->estado == 1 && Carbon::parse($cuota->fecha)->startOfDay() >= $fechaActual->startOfDay();
        });

        // Pendientes VENCIDAS/MORA (Fecha pago < hoy)
        $cuotasMora = $cuotas->filter(function ($cuota) use ($fechaActual) {
            return $cuota->estado == 1 && Carbon::parse($cuota->fecha)->startOfDay() < $fechaActual->startOfDay();
        });

        // 4. Lógica de Días de Mora y Multa
        $diasMora = 0;
        if ($cuotasMora->count() > 0) {
            // Obtenemos la cuota más antigua sin pagar
            $primeraVencida = $cuotasMora->sortBy('fecha')->first();
            $fechaVencimiento = Carbon::parse($primeraVencida->fecha);
            $diasMora = $fechaVencimiento->diffInDays($fechaActual);
        }

        // Cálculo Multa (DiasMora x 0.01 segun tu requerimiento)
        $montoMulta = $diasMora * 0.01;

        // Fecha última cuota (la fecha más lejana registrada en cuotas)
        $fechaUltimaCuota = $cuotas->max('fecha');

        return [
            $plan->id,                                  // NroCred
            $fechaRegistro->year,                       // Gestion
            $fechaRegistro->month,                      // Mes
            $fechaRegistro->format('d/m/Y'),            // FechaCred
            $solicitud->fecha_desembolso ? Carbon::parse($solicitud->fecha_desembolso)->format('d/m/Y') : '', // FechaDes
            $cliente->id,                               // CodCli
            $cliente->nombre,                           // NombreCli
            $telefonos,                                 // TelfonosCli
            $cliente->actividad,                        // RubroCli
            $cliente->estado == 1 ? 'Activo' : 'Inactivo', // EstadoCli
            $usuario->id ?? '',                         // CodOfiCred
            $usuario->personal ?? '',                   // NombreOfiCred
            $solicitud->tipo_solicitud,                 // TipoCred
            $solicitud->lapso_capital,                  // FormaPago
            $solicitud->tipo_garantia,                  // Garantia
            $plazoMeses . ' Meses',                     // PlazoMeses
            $plan->estado == 1 ? 'Vigente' : ($plan->estado == 2 ? 'Cancelado' : 'Otro'), // EstadoCred
            $cuotasPagadas->count(),                    // CuotasPagada
            $solicitud->tasa,                           // Interés
            $solicitud->importe_solicitud * 0.01,       // GastosAdm
            $solicitud->importe_solicitud,              // MontoCred
            $cuotasPagadas->sum('total'),               // MontoPagado
            $cuotasSaldo->sum('total'),                 // MontoSaldo (Capital + Interes)
            $cuotasMora->sum('total'),                  // MontoSaldoMora
            $montoMulta,                                // MontoMultaMora
            $diasMora,                                  // DiasMora
            $cuotasMora->count(),                       // CuotasEnMora
            $solicitud->fecha_primera_cuota ? Carbon::parse($solicitud->fecha_primera_cuota)->format('d/m/Y') : '', // FechaPrimerCuota
            $fechaUltimaCuota ? Carbon::parse($fechaUltimaCuota)->format('d/m/Y') : '', // FechaUltimaCuota
            $plan->fecha_fin ? Carbon::parse($plan->fecha_fin)->format('d/m/Y') : '',   // FechaFinPago
        ];
    }

    public function headings(): array
    {
        return [
            'NroCred',
            'Gestion',
            'Mes',
            'FechaCred',
            'FechaDes',
            'CodCli',
            'NombreCli',
            'TelfonosCli',
            'RubroCli',
            'EstadoCli',
            'CodOfiCred',
            'NombreOfiCred',
            'TipoCred',
            'FormaPago',
            'Garantia',
            'PlazoMeses',
            'EstadoCred',
            'CuotasPagada',
            'Interés',
            'GastosAdm',
            'MontoCred',
            'MontoPagado',
            'MontoSaldo',
            'MontoSaldoMora',
            'MontoMultaMora',
            'DiasMora',
            'CuotasEnMora',
            'FechaPrimerCuota',
            'FechaUltimaCuota',
            'FechaFinPago'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Aplicar ajuste de texto (Wrap Text) a la columna de teléfonos (H -> 8) y Rubro (I -> 9)
        // Nota: En Excel las columnas son A, B, C... 
        // A=1, H=8, I=9
        
        $sheet->getStyle('H')->getAlignment()->setWrapText(true); // Teléfonos
        $sheet->getStyle('I')->getAlignment()->setWrapText(true); // Rubro (Actividad)
        
        // Alinear verticalmente al centro para que se vea bien
        $sheet->getStyle('A1:AD' . $sheet->getHighestRow())
              ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => '4F81BD']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}