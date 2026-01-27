@php
    $url = empty($mi_empresa->logo) ? 'logo_sistema_codesoft.png' : $mi_empresa->logo;
    $image = file_get_contents(public_path('img/' . $url));
    $html = '<img src="data:image/png;base64,' . base64_encode($image) . '" class="logo-img">';

    public function getMoneda($moneda){
        if($moneda=='Bolivianos'){
            return 'Bs';
        }else{
            return 'Usd';
        }
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Calibri, 'Arial Rounded MT Bold', Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            padding: 5px;
            vertical-align: middle;
            border: none;
        }
        .logo-cell {
            width: 33%;
            text-align: left;
        }
        .company-cell {
            width: 40%;
            text-align: center;
        }
        .report-info-cell {
            width: 27%;
            text-align: right;
            font-size: 11px;
        }
        .logo-img {
            max-height: 50px;
            max-width: 120px;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 12px;
            font-weight: bold;
            color: #000000;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .company-details {
            font-size: 10px;
            margin: 3px 0;
            text-align: left;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
            color: #1a1a1a;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, 
        .data-table td {
            padding-top: 2px;
            padding-bottom: 2px;
            padding-left: 4px;
            padding-right: 4px;
            text-align: left;
            font-size: 11px;
            vertical-align: middle;
        }
        .data-table th {
            padding-top: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #000000;
            border-top: 1px solid #000000;
            background-color: #fffcd6cc;
            color: #000000;
            font-weight: bold;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-bold {
            font-weight: bold;
        }
        .badge {
            display: block;
            padding: 2px 6px;
            font-size: 10px;
            border-radius: 3px;
            color: #fff;
            text-align: center;
            min-width: 80px;
            font-weight:500;
        }
        .bg-info { color: #17a2b8; }
        .bg-warning { color: #ffc107; }
        .bg-primary { color: #007bff; }
        .bg-success { color: #28a745; }
        .bg-danger { color: #dc3545; }
        .bg-secondary { color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    {!! $html !!}
                    <p class="company-details">{{ empty($mi_empresa->direccion) ? 'Dirección de la Empresa' : $mi_empresa->direccion }}</p>
                    <p class="company-details">
                        Tel: {{ empty($mi_empresa->telefono) ? 'N/A' : $mi_empresa->telefono }} | 
                        Email: {{ empty($mi_empresa->email) ? 'N/A' : $mi_empresa->email }}
                    </p>
                </td>
                <td class="company-cell">
                    <p class="report-title">{{ $title }}</p>
                </td>
                <td class="report-info-cell">
                    <p class="company-details"><strong>Fecha:</strong> {{ $fecha }}</p>
                    <p class="company-details"><strong>Hora:</strong> {{ $hora }}</p>
                    <p class="company-details"><strong>Usuario:</strong> {{ $usuario }}</p>
                </td>
            </tr>
        </table>

        <!-- Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>Crédito</th>
                    <th>Cliente</th>
                    <th>Codeudores/Garantes</th>
                    <th>Monto</th>
                    <th>Cuotas</th>
                    <th>Tasa %</th>
                    <th>Inicio</th>
                    <th>Finaliza</th>
                    <th>Asesor</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($planes_pago as $plan)
                    <tr>
                        <td>{{ $plan->id_plan_pago }}</td>
                        <td class="text-bold">{{ $plan->cliente }}</td>
                        <td>
                            @if($plan->codeudores)
                                <ul style="margin: 0; padding-left: 20px;">
                                    @foreach(explode(',', $plan->codeudores) as $codeudor)
                                        <li>{{ trim($codeudor) }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span>Sin codeudores</span>
                            @endif
                        </td>
                        <td class="text-bold">{{ number_format($plan->importe_solicitud, 2, ',', '.') }} {{ $plan->moneda }}</td>
                        <td>{{ $plan->nro_cuotas }} ({{ $plan->lapso_capital }})</td>
                        <td>{{ $plan->tasa }}%</td>
                        <td>{{ \Carbon\Carbon::parse($plan->fecha_inicio)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($plan->fecha_fin)->format('d/m/Y') }}</td>
                        <td>{{ $plan->asesor }}</td>
                        <td>
                            @php
                                $estadoText = '';
                                if ($plan->estado == 1) {
                                    if ($plan->tipo_solicitud == 'Nuevo') $estadoText = 'Nuevo';
                                    elseif ($plan->tipo_solicitud == 'Reprogramada') $estadoText = 'Reprogramación';
                                    elseif ($plan->tipo_solicitud == 'Refinanciada') $estadoText = 'Refinanciada';
                                    else $estadoText = 'Nuevo';
                                } elseif ($plan->estado == 2) {
                                    if ($plan->tipo_solicitud == 'Nuevo') $estadoText = 'Normal';
                                    elseif ($plan->tipo_solicitud == 'Reprogramada') $estadoText = 'Reprogramado';
                                    elseif ($plan->tipo_solicitud == 'Refinanciada') $estadoText = 'Refinanciada';
                                    else $estadoText = 'Normal';
                                } elseif ($plan->estado == 0) {
                                    $estadoText = 'Anulado';
                                } else {
                                    $estadoText = 'Desconocido';
                                }
                                $estadoClass = '';
                                if ($plan->estado == 1) {
                                    if ($plan->tipo_solicitud == 'Nuevo') $estadoClass = 'bg-info';
                                    elseif ($plan->tipo_solicitud == 'Reprogramada') $estadoClass = 'bg-warning';
                                    elseif ($plan->tipo_solicitud == 'Refinanciada') $estadoClass = 'bg-primary';
                                    else $estadoClass = 'bg-info';
                                } elseif ($plan->estado == 2) {
                                    if ($plan->tipo_solicitud == 'Nuevo') $estadoClass = 'bg-success';
                                    elseif ($plan->tipo_solicitud == 'Reprogramada') $estadoClass = 'bg-primary';
                                    elseif ($plan->tipo_solicitud == 'Refinanciada') $estadoClass = 'bg-primary';
                                    else $estadoClass = 'bg-success';
                                } elseif ($plan->estado == 0) {
                                    $estadoClass = 'bg-danger';
                                } else {
                                    $estadoClass = 'bg-secondary';
                                }
                            @endphp
                            <span class="badge {{ $estadoClass }}">{{ $estadoText }}</span>
                            @if($plan->desembolso == 0)
                                <p class="badge" style="color:#ff7c7c; margin-left:5px" title="IMPORTE POR DESEMBOLSAR">Sin Desembolsar</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No se encontraron planes de pago</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>