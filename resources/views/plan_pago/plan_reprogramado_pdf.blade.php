<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plan de Pagos Reprogramado</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt; /* Reducido para más espacio */
            line-height: 1.4;
            color: #333;
            margin: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 0 10px;
        }
        
        /* === Header Styles (Tu Estilo) === */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
        }
        .header-table td {
            padding: 5px;
            vertical-align: middle;
            border: none;
        }
        .logo-cell { width: 30%; text-align: left; }
        .company-cell { width: 40%; text-align: center; }
        .report-info-cell { width: 30%; text-align: right; font-size: 9pt; }
        .logo-img { max-height: 50px; max-width: 120px; }
        .company-name { font-size: 11px; font-weight: bold; color: #000000; margin: 0; text-transform: uppercase; }
        .company-details { font-size: 9px; margin: 2px 0; }
        .report-title { font-size: 14px; font-weight: bold; margin: 0; text-transform: uppercase; color: #1a1a1a; }
        /* === Fin Header Styles === */
        
        .section-title {
            /* background-color: #f0f0f0; */
            padding: 5px 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            margin-top: 15px;
            margin-bottom: 10px;
            /* border: 1px solid #ddd; */
        }

        .section-info-table{
            border: 1px solid #666666;
            border-radius: 10px;
            padding-top:15px;
        }

        /* Tabla de datos (Cliente, Crédito) */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            /* border: 1px solid #ddd; */
            padding: 4px 6px;
            font-size: 11px;
        }
        .info-table td:first-child { /* Labels */
            /* background-color: #f9f9f9; */
            font-weight: bold;
            width: 25%;
        }

        /* Tabla de Cuotas */
        .cuotas-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .cuotas-table th, .cuotas-table td {
            /* border: 1px solid #aaa; */
            padding: 6px;
            text-align: center;
            font-size: 11px;
        }
        .cuotas-table th {
            /* background-color: #333; */
            color: #000000;
            font-weight: bold;

        }

        .cuotas-table thead tr{
            border: 1px solid #505050;
            border-radius:5px;
        }

      
        
        .text-end { text-align: right !important; }
        .text-bold { font-weight: bold; }
        
        .footer-note {
            font-size: 9pt;
            color: #555;
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            font-style: italic;
        }

        .footer-note-date {
            font-size: 9pt;
            color: #555;
            margin-top: 10px;
            padding-top: 5px;
            font-style: italic;
        }
    </style>
</head>
<body>
    @php
        // Lógica para obtener el logo (mejorada)
        $logo_path = 'img/logo_sistema_codesoft.png'; // Default
        if (!empty($empresa->logo) && file_exists(public_path('img/' . $empresa->logo))) {
            $logo_path = 'img/' . $empresa->logo;
        }
        $image = file_get_contents(public_path($logo_path));
        $base64_logo = 'data:image/' . pathinfo($logo_path, PATHINFO_EXTENSION) . ';base64,' . base64_encode($image);
    @endphp

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ $base64_logo }}" class="logo-img">
            </td>
            <td class="company-cell">
                <p class="company-name">{{ $empresa->nombre ?? 'Nombre de la Empresa' }}</p>
                <p class="company-details">{{ $empresa->direccion ?? 'Dirección de la Empresa' }}</p>
                <p class="company-details">
                    Tel: {{ $empresa->telefono ?? 'N/A' }} | Email: {{ $empresa->email ?? 'N/A' }}
                </p>
            </td>
            <td class="report-info-cell">
                <p class="company-details"><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
                <p class="company-details"><strong>Hora:</strong> {{ now()->format('H:i') }}</p>
                <p class="company-details"><strong>Usuario:</strong> {{ $usuario }}</p>
            </td>
        </tr>
    </table>
    
    <div class="container">
        <h3 class="report-title" style="text-align: center; margin-bottom: 20px;">
            Plan de Pagos Reprogramado
        </h3>

        <div class="section-title">Datos del Cliente</div>
        <div class="section-info-table">
            <table class="info-table">
                <tr>
                    <td>Nombre Cliente:</td>
                    <td colspan="3" class="text-bold">{{ $plan_pago->cliente }}</td>
                </tr>
                <tr>
                    <td>CI / NIT:</td>
                    <td>{{ $plan_pago->ci }} {{ $plan_pago->lugar_expedicion }}</td>
                    <td>Actividad:</td>
                    <td>{{ $plan_pago->actividad }}</td>
                </tr>
            </table>
        </div>

        <div class="section-title">Condiciones de la Reprogramación</div>
        <div class="section-info-table">
            <table class="info-table">
                <tr>
                    <td>Monto del Crédito:</td>
                    <td class="text-bold">{{ $plan_pago->moneda }} {{ number_format($importeEntero, 2) }}</td>
                    <td>Tasa de Interés:</td>
                    <td>{{ $plan_pago->tasa }}% ({{ $plan_pago->tipo_tasa }})</td>
                </tr>
                <tr>
                    <td>Forma de Pago:</td>
                    <td class="text-bold">{{ $plan_pago->forma_pago_reprogramacion }}</td>
                    <td>Plazo (Meses):</td>
                    <td class="text-bold">{{ $plan_pago->plazo_meses }}</td>
                </tr>
                <tr>
                    <td>N° Total de Cuotas:</td>
                    <td class="text-bold">{{ $plan_pago->numero_cuotas_reprogramacion }}</td>
                    <td>Tipo de Garantía:</td>
                    <td>{{ $plan_pago->tipo_garantia }}</td>
                </tr>
            </table>
        </div>
        
        <div class="section-title">Detalle del Plan de Pagos</div>
        <table class="cuotas-table">
            <thead>
                <tr>
                    <th>Nro</th>
                    <th>Fecha</th>
                    <th class="text-end">Capital</th>
                    <th class="text-end">Interés</th>
                    <th class="text-end text-bold">Total Cuota</th>
                    <th class="text-end">Saldo Capital</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_capital = 0;
                    $total_interes = 0;
                    $total_general = 0;
                @endphp
                @foreach($cuotas as $cuota)
                    @php
                        // Tus métodos usan 'capital', 'interes', 'total_cuota', 'saldo_capital'
                        $total_capital += $cuota['capital'];
                        $total_interes += $cuota['interes'];
                        $total_general += $cuota['total_cuota'];
                    @endphp
                    <tr>
                        <td>{{ $cuota['nro'] }}</td>
                        <td>{{ date('d/m/Y', strtotime($cuota['fecha'])) }}</td>
                        <td class="text-end">{{ number_format($cuota['capital'], 2) }}</td>
                        <td class="text-end">{{ number_format($cuota['interes'], 2) }}</td>
                        <td class="text-end text-bold">{{ number_format($cuota['total_cuota'], 2) }}</td>
                        <td class="text-end">{{ number_format($cuota['saldo_capital'], 2) }}</td>
                    </tr>
                @endforeach
                
            </tbody>
        </table>

        <div class="footer-note">
            El plan de pago solo es referencial, válido para el pago puntual de sus cuotas efectuado en la fecha correspondiente.
            Cualquier atraso modifica los intereses a pagar y genera interés moratorio.
        </div>

        <div class="footer-note-date">
            <strong>NOTA: </strong>
            Para motivos de cálculo la fecha de inicio y la fecha de la primera cuota se ajusto de forma autómatica, 
            la fecha de inicio se ajusto a la fecha actual, y la fecha de la primera cuota corresponde a la cantidad de dias despues de la 
            fecha de inicio según el lapso de capital.
        </div>
    </div>
</body>
</html>