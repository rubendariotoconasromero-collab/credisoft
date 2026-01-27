<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Simulación de Plan de Pagos</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }



        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        /* Header Styles */
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
            text-align: center;
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
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
            color: #1a1a1a;
        }
        /* Section Styles */
        .section-title {
            color: #171717;
            padding: 0 12px;
            text-align: left;
            font-weight: bold;
            margin: 0 0 15px;
            text-transform: uppercase;
            font-size: 12px;
        }
        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, 
        .data-table td {
            padding: 6px 8px;
            text-align: left;
            font-size: 12px;
        }
        .data-table th {
            color: #000000;
            font-weight: bold;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        /* Two-column layout */
        .two-col-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
        }
        .two-col-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        /* Helper classes */
        .text-bold {
            font-weight: bold;
        }
        .mb-0 {
            margin-bottom: 0;
        }
        .mt-0 {
            margin-top: 0;
        }
        .pt-2 {
            padding-top: 10px;
            font-size:12px;
            font-weight: 400;
        }
    </style>
</head>
<body>
    
    

    <div class="container">
        <?php
        // Lógica para obtener el logo (igual que antes)
        $url = empty(DB::table('mi_empresa')->get()[0]->logo) ? 'logo_sistema_codesoft.png' : DB::table('mi_empresa')->get()[0]->logo;
        // Nota: Asegúrate de que la ruta 'img/' sea accesible desde donde se genera el PDF
        // Si usas DomPDF en Laravel, a veces necesitas public_path('img/...')
        $imagePath = public_path('img/' . $url); 
        
        // Verificación básica para evitar errores si la imagen no existe
        if (file_exists($imagePath)) {
            $image = file_get_contents($imagePath);
            $html = '<img src="data:image/png;base64,' . base64_encode($image) . '" class="logo-img">';
        } else {
            $html = ''; // O una imagen por defecto
        }
        ?>
        
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <?php echo $html ?>
                    <p class="company-name">{{ empty(DB::table('mi_empresa')->get()[0]->nombre) ? 'Nombre de la Empresa' : DB::table('mi_empresa')->get()[0]->nombre }}</p>
                    <p class="company-details">{{ empty(DB::table('mi_empresa')->get()[0]->direccion) ? 'Dirección de la Empresa' : DB::table('mi_empresa')->get()[0]->direccion }}</p>
                    <p class="company-details">
                        Tel: {{ empty(DB::table('mi_empresa')->get()[0]->telefono) ? 'N/A' : DB::table('mi_empresa')->get()[0]->telefono }} | 
                        Email: {{ empty(DB::table('mi_empresa')->get()[0]->email) ? 'N/A' : DB::table('mi_empresa')->get()[0]->email }}
                    </p>
                </td>
                <td class="company-cell">
                    <p class="report-title">Simulación de Plan de Pagos</p>
                </td>
                <td class="report-info-cell">
                    <p class="company-details"><strong>Fecha:</strong> {{ $fecha_reporte }}</p>
                    <p class="company-details"><strong>Hora:</strong> {{ $hora_reporte }}</p>
                    <p class="company-details"><strong>Usuario:</strong> {{ $usuario }}</p>
                </td>
            </tr>
        </table>

        <table class="two-col-table">
            <tr>
                <td style="">
                    <table class="data-table">
                        <tr>
                            <th>Monto Solicitado</th>
                            <td style="text-align: right">{{ $solicitud_simulacion['importe_solicitud'] }} {{ $solicitud_simulacion['moneda'] ?? 'Bs' }}</td>
                        </tr>
                        <tr>
                            <th>Plazo</th>
                            <td style="text-align: right">{{ $solicitud_simulacion['nro_cuotas'] . ' ' . '('.$solicitud_simulacion['lapso_capital'].')'}}</td>
                        </tr>
                        <tr>
                            <th>Forma de pago</th>
                            <td style="text-align: right">{{ $solicitud_simulacion['lapso_capital'] }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <div style="border: 1px solid #000; border-radius: 10px; padding: 10px;">
                        <table class="data-table">
                            <tr>
                                <th>Tipo de Cuota</th>
                                <td style="text-align: right">{{ $solicitud_simulacion['tipo_tasa'] }}</td>
                            </tr>
                            <tr>
                                <th>Fecha Desembolso</th>
                                <td style="text-align: right">{{ $solicitud_simulacion['fecha_desembolso'] }}</td>
                            </tr>
                            <tr>
                                <th>Fecha Primera Cuota</th>
                                <td style="text-align: right">{{ $solicitud_simulacion['fecha_primera_cuota'] }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <table class="two-col-table">
            <tr>
                <td>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="text-align:center;border-top:1px solid #2a2a2a; border-bottom:1px solid #2a2a2a; border-left:1px solid #2a2a2a">Nro</th>
                                <th style="text-align:center;border-top:1px solid #2a2a2a; border-bottom:1px solid #2a2a2a;">Fecha</th>
                                <th style="text-align:center;border-top:1px solid #2a2a2a; border-bottom:1px solid #2a2a2a;">Capital</th>
                                <th style="text-align:center;border-top:1px solid #2a2a2a; border-bottom:1px solid #2a2a2a;">Interes</th>
                                <th style="text-align:center;border-top:1px solid #2a2a2a; border-bottom:1px solid #2a2a2a;">Saldo Capital</th>
                                <th style="text-align:center;border-top:1px solid #2a2a2a; border-bottom:1px solid #2a2a2a;border-right:1px solid #2a2a2a">Total Bs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detalles as $detalle)
                            <tr>
                                <td style="text-align:center">{{ $detalle['nro'] }}</td>
                                <td style="text-align:center">{{ $detalle['fecha'] }}</td>
                                <td style="text-align:center">{{ $detalle['capital'] }}</td>
                                <td style="text-align:center">{{ $detalle['interes'] }}</td>
                                <td style="text-align:center">{{ $detalle['saldo_capital'] }}</td>
                                <td style="text-align:center">{{ $detalle['total_cuota'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        <p class="pt-2">
            El plan de pago solo es referencial, válido para el pago puntual de sus cuotas efectuado en la fecha correspondiente.
            Cualquier atraso modifica los intereses a pagar y genera interés moratorio.
        </p>
    </div>
</body>
</html>