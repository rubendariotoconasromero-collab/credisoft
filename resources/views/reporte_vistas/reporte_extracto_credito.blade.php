@php 
// Safe company information retrieval to prevent PHP 8 'Undefined array key 0' errors
$empresa = DB::table('mi_empresa')->first();
$url = (empty($empresa) || empty($empresa->logo)) ? 'logo_sistema_codesoft.png' : $empresa->logo;
$direccion = (empty($empresa) || empty($empresa->direccion)) ? 'Dirección Comercial' : $empresa->direccion;
$telefono = (empty($empresa) || empty($empresa->telefono)) ? 'Teléfono Contacto' : $empresa->telefono;
$email = (empty($empresa) || empty($empresa->email)) ? 'Correo Comercial' : $empresa->email;

// Safe client & credit info retrieval
$info = $informacion->first();

if (!function_exists('evaluandoEstado')) {
    function evaluandoEstado($estado){
        if($estado==1){
            return 'Vigente';
        }else if($estado==0){
            return 'Anulado';
        }else if($estado==2){
            return 'Terminado';
        }
        return 'Desconocido';
    }
}

if (!function_exists('evaluandoEstadoCuota')) {
    function evaluandoEstadoCuota($estado){
        if($estado==1){
            return 'Pendiente';
        }else if($estado==0){
            return 'Anulada';
        }else if($estado==2){
            return 'Pagada';
        }else if($estado==3){
            return 'P. Parcial';
        }
        return 'Desconocido';
    }
}

// Calculate grand totals for the footer
$totalCapital = 0;
$totalInteres = 0;
$totalCuota = 0;
$totalRecibido = 0;
$totalMora = 0;
$totalPendiente = 0;

foreach($detalles as $cuota) {
    $totalCapital += floatval($cuota->capital);
    $totalInteres += floatval($cuota->interes);
    $totalCuota += floatval($cuota->total);
    
    $cuotaRecibido = 0;
    $cuotaMora = 0;
    if (isset($cuota->pagos) && is_iterable($cuota->pagos)) {
        foreach($cuota->pagos as $pago) {
            $cuotaRecibido += floatval($pago->monto_pago);
            $cuotaMora += floatval($pago->pago_mora);
        }
    }
    
    $totalRecibido += $cuotaRecibido;
    $totalMora += $cuotaMora;
    
    // Balance calculation
    $totalPendiente += max(0, floatval($cuota->total) - $cuotaRecibido);
}
@endphp

@extends('reporte.plantilla_reporte')

@section('title', 'Extracto de Crédito')
@section('report_title', 'EXTRACTO DE CRÉDITO')

@section('styles')
    @page {
        size: letter-landscape;
        margin: 10mm 12mm 10mm 12mm;
    }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .text-right { text-align: right; }
    .fw-bold { font-weight: bold; }
    .text-muted { color: #64748b; }
    .data-table tr {
        background-color: transparent !important;
    }
    .data-table tbody tr {
        background-color: #ffffff !important;
    }
    .data-table tbody tr.payment-row {
        background-color: #f8fafc !important;
    }
    .payment-row td {
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        font-size: 8.5px;
        padding: 3px 6px;
        color: #475569;
    }
    .bg-summary {
        background-color: #ffffff;
    }
    .bg-light-gray {
        background-color: #fafafa;
        color: #64748b;
        font-size: 8.5px;
    }
    .badge-neutral {
        display: inline-block;
        padding: 2px 5px;
        font-size: 7.5px;
        font-weight: bold;
        text-transform: uppercase;
        border-radius: 3px;
        text-align: center;
        width: 70px;
        background-color: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
@endsection

@section('info_left')
    <table class="data-table" style="margin-bottom: 0;">
        <tr>
            <th style="width: 40%; font-weight: bold;">Cliente</th>
            <td class="text-bold text-uppercase">{{ $info->cliente ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold;">C.I. / R.U.N.</th>
            <td>{{ ($info->ci ?? '') . ' ' . ($info->lugar_expedicion ?? '') }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold;">Asesor</th>
            <td class="text-uppercase">{{ $info->personal ?? 'N/A' }}</td>
        </tr>
    </table>
@endsection

@section('info_right')
    <table class="data-table" style="margin-bottom: 0;">
        <tr>
            <th style="width: 45%; font-weight: bold;">Importe Préstamo</th>
            <td class="text-bold" style="color: #10b981;">{{ number_format($info->importe_solicitud ?? 0, 2, ',', '.') . ' ' . ($info->moneda ?? '') }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold;">Tasa / Plazo</th>
            <td>{{ $info->tasa ?? 0 }}% / {{ $info->nro_cuotas ?? 0 }} cuotas ({{ $info->lapso_capital ?? '' }})</td>
        </tr>
        <tr>
            <th style="font-weight: bold;">Fecha Desembolso</th>
            <td>{{ !empty($info->fecha_desembolso) ? \Carbon\Carbon::parse($info->fecha_desembolso)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold;">Total Plan</th>
            <td class="text-bold" style="color: #ef4444;">{{ number_format($totalCuota, 2, ',', '.') . ' ' . ($info->moneda ?? '') }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold;">Estado Crédito</th>
            <td>
                <span class="badge-neutral">
                    {{ evaluandoEstado($info->estado ?? 1) }}
                </span>
            </td>
        </tr>
    </table>
@endsection

@section('totals_box')
    <div style="border: 1px solid #a3a3a3; border-radius: 6px; padding: 8px; background-color: #f9fafb; margin-top: 15px; margin-bottom: 15px;">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 16.6%; text-align: center; border-right: 1px solid #cbd5e1; padding: 3px;">
                    <span style="font-size: 8px; color: #64748b; text-transform: uppercase; display: block;">Capital Programado</span>
                    <span style="font-size: 10.5px; font-weight: bold; color: #334155;">{{ number_format($totalCapital, 2, ',', '.') }} {{ $info->moneda ?? '' }}</span>
                </td>
                <td style="width: 16.6%; text-align: center; border-right: 1px solid #cbd5e1; padding: 3px;">
                    <span style="font-size: 8px; color: #64748b; text-transform: uppercase; display: block;">Interés Programado</span>
                    <span style="font-size: 10.5px; font-weight: bold; color: #334155;">{{ number_format($totalInteres, 2, ',', '.') }} {{ $info->moneda ?? '' }}</span>
                </td>
                <td style="width: 16.6%; text-align: center; border-right: 1px solid #cbd5e1; padding: 3px;">
                    <span style="font-size: 8px; color: #64748b; text-transform: uppercase; display: block;">Total Programado</span>
                    <span style="font-size: 10.5px; font-weight: bold; color: #1e3a8a;">{{ number_format($totalCuota, 2, ',', '.') }} {{ $info->moneda ?? '' }}</span>
                </td>
                <td style="width: 16.6%; text-align: center; border-right: 1px solid #cbd5e1; padding: 3px;">
                    <span style="font-size: 8px; color: #64748b; text-transform: uppercase; display: block;">Total Recibido</span>
                    <span style="font-size: 10.5px; font-weight: bold; color: #15803d;">{{ number_format($totalRecibido, 2, ',', '.') }} {{ $info->moneda ?? '' }}</span>
                </td>
                <td style="width: 16.6%; text-align: center; border-right: 1px solid #cbd5e1; padding: 3px;">
                    <span style="font-size: 8px; color: #64748b; text-transform: uppercase; display: block;">Mora Cobrada</span>
                    <span style="font-size: 10.5px; font-weight: bold; color: #b45309;">{{ number_format($totalMora, 2, ',', '.') }} {{ $info->moneda ?? '' }}</span>
                </td>
                <td style="width: 16.6%; text-align: center; padding: 3px;">
                    <span style="font-size: 8px; color: #64748b; text-transform: uppercase; display: block;">Saldo Pendiente</span>
                    <span style="font-size: 10.5px; font-weight: bold; color: #b91c1c;">{{ number_format($totalPendiente, 2, ',', '.') }} {{ $info->moneda ?? '' }}</span>
                </td>
            </tr>
        </table>
    </div>
@endsection

@section('content')
    <div class="rounded-table-wrapper">
        <table class="data-table" style="margin-bottom: 0; border: none;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="width: 5%; text-align: center; border-bottom: 1px solid #a3a3a3;">Nro</th>
                    <th style="width: 11%; text-align: center; border-bottom: 1px solid #a3a3a3;">Vencimiento</th>
                    <th style="width: 11%; text-align: right; border-bottom: 1px solid #a3a3a3;">Capital</th>
                    <th style="width: 11%; text-align: right; border-bottom: 1px solid #a3a3a3;">Interés</th>
                    <th style="width: 12%; text-align: right; border-bottom: 1px solid #a3a3a3;">Total Cuota</th>
                    <th style="width: 11%; text-align: center; border-bottom: 1px solid #a3a3a3;">Estado</th>
                    <th style="width: 13%; text-align: center; border-bottom: 1px solid #a3a3a3;">Fecha Pago</th>
                    <th style="width: 13%; text-align: right; border-bottom: 1px solid #a3a3a3;">MORA Bs.</th>
                    <th style="width: 13%; text-align: right; border-bottom: 1px solid #a3a3a3;">RECIBIDO Bs.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles as $cuota)
                    @php
                        $pagosCount = isset($cuota->pagos) ? count($cuota->pagos) : 0;
                        $hasPagos = $pagosCount > 0;
                        $rowspan = $hasPagos ? $pagosCount + 1 : 1;
                        
                        // Calculate sums of payments for the main cuota row summary
                        $sumRecibido = 0;
                        $sumMora = 0;
                        if ($hasPagos) {
                            foreach($cuota->pagos as $p) {
                                $sumRecibido += floatval($p->monto_pago);
                                $sumMora += floatval($p->pago_mora);
                            }
                        }
                    @endphp
                    
                    <!-- Fila Principal de la Cuota -->
                    <tr style="text-align: center;">
                        <td rowspan="{{ $rowspan }}" class="fw-bold" style="color: #0f172a; text-align: center; border: 1px solid #e2e8f0;">{{ $cuota->numero }}</td>
                        <td rowspan="{{ $rowspan }}" style="text-align: center; border: 1px solid #e2e8f0;">{{ !empty($cuota->fecha) ? \Carbon\Carbon::parse($cuota->fecha)->format('d/m/Y') : 'N/A' }}</td>
                        <td rowspan="{{ $rowspan }}" class="text-end fw-bold" style="color: #475569; text-align: right; border: 1px solid #e2e8f0;">{{ number_format($cuota->capital, 2, ',', '.') }}</td>
                        <td rowspan="{{ $rowspan }}" class="text-end text-muted" style="text-align: right; border: 1px solid #e2e8f0;">{{ number_format($cuota->interes, 2, ',', '.') }}</td>
                        <td rowspan="{{ $rowspan }}" class="text-end fw-bold" style="color: #1e293b; text-align: right; border: 1px solid #e2e8f0;">{{ number_format($cuota->total, 2, ',', '.') }}</td>
                        <td rowspan="{{ $rowspan }}" style="text-align: center; border: 1px solid #e2e8f0;">
                            <span class="badge-neutral">
                                {{ evaluandoEstadoCuota($cuota->estado) }}
                            </span>
                        </td>
                        
                        @if($hasPagos)
                            <!-- Resumen Sumatorio en la Fila Principal de la Cuota -->
                            <td class="text-muted fw-bold bg-summary" style="text-align: center; border: 1px solid #e2e8f0;">-</td>
                            <td class="text-end fw-bold bg-summary" style="color: #1e293b; text-align: right; border: 1px solid #e2e8f0;">{{ number_format($sumMora, 2, ',', '.') }}</td>
                            <td class="text-end fw-bold bg-summary" style="color: #1e293b; text-align: right; border: 1px solid #e2e8f0;">{{ number_format($sumRecibido, 2, ',', '.') }}</td>
                        @else
                            <!-- Si la cuota no tiene pagos registrados -->
                            <td class="text-center text-muted bg-light-gray" style="text-align: center; border: 1px solid #e2e8f0;">Sin pagos</td>
                            <td class="text-end text-muted bg-light-gray" style="text-align: right; border: 1px solid #e2e8f0;">-</td>
                            <td class="text-end text-muted bg-light-gray" style="text-align: right; border: 1px solid #e2e8f0;">-</td>
                        @endif
                    </tr>
                    
                    @if($hasPagos)
                        <!-- Filas Secundarias Desplegadas para cada Pago Individual -->
                        @foreach($cuota->pagos as $pago)
                            <tr class="payment-row" style="text-align: center;">
                                <td style="text-align: center; border: 1px solid #e2e8f0;">{{ !empty($pago->fecha_pago) ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : 'N/A' }}</td>
                                <td class="text-end fw-bold" style="color: #475569; text-align: right; border: 1px solid #e2e8f0;">{{ number_format($pago->pago_mora, 2, ',', '.') }}</td>
                                <td class="text-end fw-bold" style="color: #475569; text-align: right; border: 1px solid #e2e8f0;">{{ number_format($pago->monto_pago, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('footer_note', 'El extracto de crédito tiene carácter informativo y oficial. Cualquier inconsistencia debe ser aclarada con su asesor asignado de inmediato.')
