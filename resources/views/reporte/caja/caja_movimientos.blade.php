<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos</title>
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
            font-size: 11px;
        }
        .data-table th {
            color: #000000;
            font-weight: bold;
            vertical-align: middle;
            background-color: #f3f4f6;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        /* Utilities */
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 10px; }
        
        /* Specific border styles based on your template */
        .border-box {
            border: 1px solid #000;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .table-borders th {
            border-top: 1px solid #2a2a2a;
            border-bottom: 1px solid #2a2a2a;
        }
        .table-borders th:first-child { border-left: 1px solid #2a2a2a; }
        .table-borders th:last-child { border-right: 1px solid #2a2a2a; }
        .table-borders td {
            border-bottom: 1px solid #eee;
        }
        .total-row td {
            border-top: 2px solid #2a2a2a;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Lógica de carga de imagen que proporcionaste
        $empresa = DB::table('mi_empresa')->first();
        $url_logo = empty($empresa->logo) ? 'logo_sistema_codesoft.png' : $empresa->logo;
        
        // Manejo seguro por si la imagen no existe en la ruta
        $path = public_path('img/' . $url_logo);
        if(file_exists($path)) {
            $image = file_get_contents($path);
            $html_logo = '<img src="data:image/png;base64,' . base64_encode($image) . '" class="logo-img">';
        } else {
            $html_logo = '<p><strong>LOGO</strong></p>';
        }

        // Determinar el título exacto según el tipo
        $titulos = [
            'ingresos_corrientes' => 'Ingresos Corrientes',
            'egresos' => 'Gastos Corrientes',
            'cobros' => 'Cobros de Cuotas',
            'desembolsos' => 'Desembolsos de Préstamos'
        ];
        $titulo_reporte = $titulos[$tipo] ?? 'Movimientos de Caja';
        ?>

        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    {!! $html_logo !!}
                    <p class="company-name">{{ empty($empresa->nombre) ? 'Nombre de la Empresa' : $empresa->nombre }}</p>
                    <p class="company-details">{{ empty($empresa->direccion) ? 'Dirección de la Empresa' : $empresa->direccion }}</p>
                    <p class="company-details">
                        Tel: {{ empty($empresa->telefono) ? 'N/A' : $empresa->telefono }} | 
                        Email: {{ empty($empresa->email) ? 'N/A' : $empresa->email }}
                    </p>
                </td>
                <td class="company-cell">
                    <p class="report-title">Reporte de {{ $titulo_reporte }}</p>
                </td>
                <td class="report-info-cell">
                    <p class="company-details"><strong>Fecha de Impresión:</strong> {{ now()->format('d/m/Y') }}</p>
                    <p class="company-details"><strong>Hora:</strong> {{ now()->format('H:i') }}</p>
                    <p class="company-details"><strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</p>
                </td>
            </tr>
        </table>

        <div class="border-box">
            <table style="width: 100%; font-size: 11px;">
                <tr>
                    <td style="width: 33%;">
                        <strong>Fecha Inicio:</strong> 
                        {{ !empty($filtros['fecha_inicio']) ? \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') : 'Todos los registros' }}
                    </td>
                    <td style="width: 33%;">
                        <strong>Fecha Fin:</strong> 
                        {{ !empty($filtros['fecha_fin']) ? \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') : 'Todos los registros' }}
                    </td>
                    <td style="width: 33%;">
                        <strong>Búsqueda:</strong> 
                        {{ !empty($filtros['buscar']) ? $filtros['buscar'] : 'Ninguna' }}
                    </td>
                </tr>
            </table>
        </div>

        <table class="data-table table-borders">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">Nro</th>
                    <th class="text-center" style="width: 15%;">Fecha/Hora</th>
                    <th style="width: 60%;">Descripción / Detalles</th>
                    <th class="text-end" style="width: 20%;">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lista as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    
                    {{-- Formateamos la fecha dependiendo de cómo venga (fecha o created_at) --}}
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($item->fecha ?? $item->created_at)->format('d/m/Y H:i') }}
                    </td>
                    
                    <td>
                        <span class="text-bold">{{ $item->descripcion ?? 'Movimiento' }}</span>
                        {{-- Si viene con un cliente (en caso de cobros o desembolsos), lo mostramos --}}
                        @if(isset($item->cliente) && !empty($item->cliente))
                            <br>
                            <span style="color: #555; font-size: 10px;">Cliente: {{ $item->cliente }}</span>
                        @endif
                    </td>
                    
                    <td class="text-end text-bold">
                        {{ number_format($item->monto ?? 0, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px;">
                        No se encontraron registros para los filtros seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-end">TOTAL GENERAL:</td>
                    <td class="text-end">{{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <p style="font-size: 10px; color: #666; text-align: center; margin-top: 30px;">
            Este documento es un reporte generado por el sistema interno. Los montos están expresados en moneda nacional (Bolivianos).
        </p>
    </div>
</body>
</html>