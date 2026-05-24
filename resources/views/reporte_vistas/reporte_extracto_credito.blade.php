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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Extracto de Crédito</title>
    <style>
        @page {
            size: letter-landscape;
            margin: 10mm 12mm 10mm 12mm;
        }
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #1e293b;
            background-color: #ffffff;
        }
        .container {
            width: 100%;
        }
        
        /* Estilos del Encabezado */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: none;
        }
        .header-table td {
            padding: 0;
            vertical-align: middle;
            border: none;
        }
        .header-logo-section {
            width: 30%;
        }
        .header-title-section {
            width: 40%;
            text-align: center;
        }
        .header-meta-section {
            width: 30%;
            text-align: right;
            font-size: 8.5px;
            color: #64748b;
            line-height: 1.3;
        }
        .company-logo {
            height: 42px;
            width: auto;
            margin-bottom: 4px;
        }
        .company-info {
            font-size: 8px;
            color: #64748b;
            line-height: 1.2;
            margin: 0;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }
        .report-subtitle {
            font-size: 10px;
            font-weight: bold;
            color: #d97706;
            margin: 2px 0 0 0;
            text-transform: uppercase;
        }

        /* Ficha Informativa (Resumen Crédito/Cliente) */
        .summary-card {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .summary-card td {
            padding: 6px 10px;
            vertical-align: top;
            font-size: 9px;
            border: none;
        }
        .summary-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 4px;
            text-transform: uppercase;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 2px;
        }
        .info-label {
            color: #64748b;
            font-weight: normal;
        }
        .info-value {
            color: #0f172a;
            font-weight: bold;
        }

        /* Tabla de Datos Principal */
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 5px;
            font-size: 9px;
            border: 1px solid #cbd5e1;
        }
        .data-table th { 
            background-color: #1e293b; 
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            padding: 5px 6px;
            border: 1px solid #334155;
            text-align: center;
        }
        .data-table td { 
            padding: 4px 6px; 
            border: 1px solid #e2e8f0; 
            vertical-align: middle;
        }
        
        /* Celdas específicas */
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        
        /* Filas secundarias para pagos */
        .payment-row td {
            background-color: #fcfcfc;
            border-color: #f1f5f9;
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

        /* Badges de Estado */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 3px;
            text-align: center;
            width: 70px;
        }
        .badge-success { background-color: #10b981; color: #ffffff; }
        .badge-secondary { background-color: #64748b; color: #ffffff; }
        .badge-warning { background-color: #f59e0b; color: #1e293b; }
        .badge-info { background-color: #0ea5e9; color: #ffffff; }
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
        
        /* Totales del Reporte */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            border: 1px solid #cbd5e1;
        }
        .totals-table td {
            padding: 6px 10px;
            font-size: 9.5px;
            border: 1px solid #e2e8f0;
        }
        .totals-title-col {
            background-color: #f8fafc;
            font-weight: bold;
            color: #475569;
            width: 75%;
            text-align: right;
            text-transform: uppercase;
            font-size: 8.5px;
        }
        .totals-val-col {
            font-weight: bold;
            text-align: right;
            width: 25%;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- ENCABEZADO -->
        <table class="header-table">
            <tr>
                <td class="header-logo-section">
                    <img class="company-logo" src="data:image/png;base64,{{ base64_encode(file_get_contents('img/'.$url)) }}" alt="Logo">
                    <p class="company-info"><strong>Dir:</strong> {{ $direccion }}</p>
                    <p class="company-info"><strong>Tel:</strong> {{ $telefono }}</p>
                </td>
                <td class="header-title-section">
                    <h1 class="report-title">Extracto de Crédito</h1>
                    <h2 class="report-subtitle">Cronograma y Pagos Realizados</h2>
                </td>
                <td class="header-meta-section">
                    <strong>Usuario:</strong> {{ $usuario }}<br>
                    <strong>Fecha Emisión:</strong> {{ $fecha_reporte }}<br>
                    <strong>Crédito Nro:</strong> #{{ $info->id ?? 'N/A' }}
                </td>
            </tr>
        </table>

        <!-- FICHA INFORMATIVA COMPACTA -->
        <table class="summary-card">
            <tr>
                <!-- Columna Cliente -->
                <td style="width: 50%; border-right: 1px dashed #cbd5e1;">
                    <div class="summary-title">Información General del Cliente</div>
                    <table style="width: 100%; border: none;">
                        <tr style="border: none;"><td style="padding: 2px 0; border: none; width: 25%;" class="info-label">Cliente:</td><td style="padding: 2px 0; border: none; width: 75%;" class="info-value text-uppercase">{{ $info->cliente ?? 'N/A' }}</td></tr>
                        <tr style="border: none;"><td style="padding: 2px 0; border: none;" class="info-label">C.I. / R.U.N.:</td><td style="padding: 2px 0; border: none;" class="info-value">{{ ($info->ci ?? '') . ' ' . ($info->lugar_expedicion ?? '') }}</td></tr>
                        <tr style="border: none;"><td style="padding: 2px 0; border: none;" class="info-label">Asesor:</td><td style="padding: 2px 0; border: none;" class="info-value text-uppercase">{{ $info->personal ?? 'N/A' }}</td></tr>
                    </table>
                </td>
                <!-- Columna Financiera -->
                <td style="width: 50%;">
                    <div class="summary-title">Detalles Financieros del Crédito</div>
                    <table style="width: 100%; border: none;">
                        <tr style="border: none;">
                            <td style="padding: 2px 0; border: none; width: 35%;" class="info-label">Importe Préstamo:</td>
                            <td style="padding: 2px 0; border: none; width: 25%;" class="info-value text-success">{{ number_format($info->importe_solicitud ?? 0, 2, ',', '.') . ' ' . ($info->moneda ?? '') }}</td>
                            <td style="padding: 2px 0; border: none; width: 20%;" class="info-label">Tasa:</td>
                            <td style="padding: 2px 0; border: none; width: 20%;" class="info-value">{{ $info->tasa ?? 0 }}%</td>
                        </tr>
                        <tr style="border: none;">
                            <td style="padding: 2px 0; border: none;" class="info-label">Plazo y Frecuencia:</td>
                            <td style="padding: 2px 0; border: none;" class="info-value">{{ $info->nro_cuotas ?? 0 }} cuotas ({{ $info->lapso_capital ?? '' }})</td>
                            <td style="padding: 2px 0; border: none;" class="info-label">Estado:</td>
                            <td style="padding: 2px 0; border: none;">
                                <span class="badge-neutral">
                                    {{ evaluandoEstado($info->estado ?? 1) }}
                                </span>
                            </td>
                        </tr>
                        <tr style="border: none;">
                            <td style="padding: 2px 0; border: none;" class="info-label">Fecha Desembolso:</td>
                            <td style="padding: 2px 0; border: none;" class="info-value">{{ !empty($info->fecha_desembolso) ? \Carbon\Carbon::parse($info->fecha_desembolso)->format('d/m/Y') : 'N/A' }}</td>
                            <td style="padding: 2px 0; border: none;" class="info-label">Total Plan:</td>
                            <td style="padding: 2px 0; border: none;" class="info-value text-danger">{{ number_format($totalCuota, 2, ',', '.') . ' ' . ($info->moneda ?? '') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- CRONOGRAMA DE AMORTIZACIONES Y PAGOS DETALLADOS -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">Nro</th>
                    <th style="width: 11%;">Vencimiento</th>
                    <th style="width: 11%;">Capital</th>
                    <th style="width: 11%;">Interés</th>
                    <th style="width: 12%;">Total Cuota</th>
                    <th style="width: 11%;">Estado</th>
                    <th style="width: 13%;">Fecha Pago</th>
                    <th style="width: 13%;">MORA Bs.</th>
                    <th style="width: 13%;">RECIBIDO Bs.</th>
                    {{-- <th style="width: 11%;">Transacción / Forma</th> --}}
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
                        <td rowspan="{{ $rowspan }}" class="fw-bold" style="color: #0f172a;">{{ $cuota->numero }}</td>
                        <td rowspan="{{ $rowspan }}">{{ !empty($cuota->fecha) ? \Carbon\Carbon::parse($cuota->fecha)->format('d/m/Y') : 'N/A' }}</td>
                        <td rowspan="{{ $rowspan }}" class="text-end fw-bold" style="color: #475569;">{{ number_format($cuota->capital, 2, ',', '.') }}</td>
                        <td rowspan="{{ $rowspan }}" class="text-end text-muted">{{ number_format($cuota->interes, 2, ',', '.') }}</td>
                        <td rowspan="{{ $rowspan }}" class="text-end fw-bold" style="color: #1e293b;">{{ number_format($cuota->total, 2, ',', '.') }}</td>
                        <td rowspan="{{ $rowspan }}">
                            <span class="badge-neutral">
                                {{ evaluandoEstadoCuota($cuota->estado) }}
                            </span>
                        </td>
                        
                        @if($hasPagos)
                            <!-- Resumen Sumatorio en la Fila Principal de la Cuota -->
                            <td class="text-muted fw-bold bg-summary">-</td>
                            <td class="text-end fw-bold bg-summary" style="color: #1e293b;">{{ number_format($sumMora, 2, ',', '.') }}</td>
                            <td class="text-end fw-bold bg-summary" style="color: #1e293b;">{{ number_format($sumRecibido, 2, ',', '.') }}</td>
                            {{-- <td class="text-center text-muted bg-summary" style="font-size: 8px;">(Suma de Pagos)</td> --}}
                        @else
                            <!-- Si la cuota no tiene pagos registrados -->
                            <td class="text-center text-muted bg-light-gray">Sin pagos</td>
                            <td class="text-end text-muted bg-light-gray">-</td>
                            <td class="text-end text-muted bg-light-gray">-</td>
                            {{-- <td class="text-center text-muted bg-light-gray">-</td> --}}
                        @endif
                    </tr>
                    
                    @if($hasPagos)
                        <!-- Filas Secundarias Desplegadas para cada Pago Individual -->
                        @foreach($cuota->pagos as $pago)
                            <tr class="payment-row" style="text-align: center;">
                                <td>{{ !empty($pago->fecha_pago) ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : 'N/A' }}</td>
                                <td class="text-end fw-bold" style="color: #475569;">{{ number_format($pago->pago_mora, 2, ',', '.') }}</td>
                                <td class="text-end fw-bold" style="color: #475569;">{{ number_format($pago->monto_pago, 2, ',', '.') }}</td>
                                {{-- <td class="text-muted" style="font-size: 7.5px; text-transform: uppercase;">
                                    {{ $pago->codigo_transaccion ?: 'N/A' }} <span style="font-size: 7px; color: #64748b;">({{ $pago->forma_pago }})</span>
                                </td> --}}
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>

        <!-- RESUMEN GENERAL DE TOTALES -->
        <table class="totals-table">
            <tr>
                <td class="totals-title-col">Total Capital Programado</td>
                <td class="totals-val-col text-end" style="color: #1e293b;">{{ number_format($totalCapital, 2, ',', '.') }} {{ $info->moneda ?? '' }}</td>
            </tr>
            <tr>
                <td class="totals-title-col">Total Interés Programado</td>
                <td class="totals-val-col text-end" style="color: #1e293b;">{{ number_format($totalInteres, 2, ',', '.') }} {{ $info->moneda ?? '' }}</td>
            </tr>
            <tr>
                <td class="totals-title-col" style="background-color: #f8fafc;">Total General Programado (Capital + Interés)</td>
                <td class="totals-val-col text-end" style="color: #0f172a; background-color: #f8fafc;">{{ number_format($totalCuota, 2, ',', '.') }} {{ $info->moneda ?? '' }}</td>
            </tr>
            <tr>
                <td class="totals-title-col">Total Efectivamente Recibido</td>
                <td class="totals-val-col text-end" style="color: #0f172a;">{{ number_format($totalRecibido, 2, ',', '.') }} {{ $info->moneda ?? '' }}</td>
            </tr>
            <tr>
                <td class="totals-title-col">Total Intereses de Mora Cobrados</td>
                <td class="totals-val-col text-end" style="color: #1e293b;">{{ number_format($totalMora, 2, ',', '.') }} {{ $info->moneda ?? '' }}</td>
            </tr>
            <tr>
                <td class="totals-title-col" style="background-color: #f8fafc;">Saldo Pendiente de Cobro</td>
                <td class="totals-val-col text-end" style="color: #ef4444; background-color: #f8fafc;">{{ number_format($totalPendiente, 2, ',', '.') }} {{ $info->moneda ?? '' }}</td>
            </tr>
        </table>

    </div>
</body>
</html>
