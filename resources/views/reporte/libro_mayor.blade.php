<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Libro Mayor</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt; /* Letra pequeña para que quepa en una hoja vertical */
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        /* CABECERA */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #198754;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-table td { vertical-align: top; }
        .company-name { font-size: 14px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .company-details { font-size: 9px; color: #555; margin: 2px 0; }
        .report-title { font-size: 16px; font-weight: bold; color: #198754; margin: 0; text-transform: uppercase;}

        /* FILTROS APLICADOS */
        .filtros-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 10px;
        }

        /* TABLA PRINCIPAL */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
        }
        .ledger-table thead th {
            background-color: #212529;
            color: #ffffff;
            padding: 8px 5px;
            font-size: 9px;
            text-transform: uppercase;
            text-align: center;
            border: 1px solid #212529;
        }
        .ledger-table td {
            padding: 6px 5px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }
        .ledger-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        /* Celdas especiales */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .col-ingreso { background-color: #e8f5e9; color: #198754; font-weight: bold; }
        .col-egreso { background-color: #f8d7da; color: #dc3545; font-weight: bold; }
        .col-saldo { background-color: #f1f3f5; font-weight: bold; border-left: 2px solid #343a40 !important;}

        /* FOOTER TOTALES */
        .tfoot-row td {
            background-color: #343a40;
            color: #ffffff;
            font-weight: bold;
            padding: 8px 5px;
        }
    </style>
</head>
<body>

    <?php
        // Procesar Logo
        $url = empty($empresa->logo) ? 'logo_sistema_codesoft.png' : $empresa->logo;
        $path = public_path('img/' . $url);
        $html_logo = '';
        if(file_exists($path)){
            $image = file_get_contents($path);
            $html_logo = '<img src="data:image/png;base64,' . base64_encode($image) . '" style="max-height: 45px; max-width: 150px;">';
        }
    ?>

    <table class="header-table">
        <tr>
            <td width="33%" style="text-align: left;">
                {!! $html_logo !!}
                <p class="company-name">{{ empty($empresa->nombre) ? 'Empresa Demo' : $empresa->nombre }}</p>
                <p class="company-details">{{ empty($empresa->direccion) ? 'Sin dirección' : $empresa->direccion }}</p>
            </td>
            <td width="34%" style="text-align: center;">
                <h1 class="report-title">LIBRO MAYOR</h1>
                <p class="company-details">Estado de Ingresos y Egresos</p>
            </td>
            <td width="33%" style="text-align: right;">
                <p class="company-details"><strong>Fecha Emisión:</strong> {{ now()->format('d/m/Y') }}</p>
                <p class="company-details"><strong>Hora:</strong> {{ now()->format('H:i') }}</p>
                <p class="company-details"><strong>Cajero:</strong> {{ auth()->user()->name ?? 'Sistema' }}</p>
            </td>
        </tr>
    </table>

    <div class="filtros-box">
        <strong>Filtros Aplicados:</strong><br>
        Rango de fechas: {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/Y') }} &nbsp; | &nbsp;
        Tipo de Movimiento: {{ $tipo_filtro }}
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th width="5%">N°</th>
                <th width="10%">Fecha</th>
                <th width="15%">Tipo</th>
                <th width="35%">Descripción</th>
                <th width="11%">Ingreso (Bs)</th>
                <th width="11%">Egreso (Bs)</th>
                <th width="13%">Saldo (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $mov)
            <tr>
                <td class="text-center">{{ $mov->nro }}</td>
                <td class="text-center">{{ $mov->fecha }}</td>
                <td class="text-center" style="font-size: 8px; font-weight:bold; color:#555;">{{ $mov->tipo }}</td>
                <td class="text-left">{{ $mov->descripcion }}</td>
                
                <td class="text-right col-ingreso">{{ $mov->debe > 0 ? number_format($mov->debe, 2) : '' }}</td>
                <td class="text-right col-egreso">{{ $mov->haber > 0 ? number_format($mov->haber, 2) : '' }}</td>
                
                <td class="text-right col-saldo">{{ number_format($mov->saldo, 2) }}</td>
            </tr>
            @endforeach

            @if(count($movimientos) == 0)
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px; color: #888;">No existen movimientos en este periodo.</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="tfoot-row">
                <td colspan="4" class="text-right">TOTAL DEL PERIODO FILTRADO:</td>
                <td class="text-right">{{ number_format($total_ingresos, 2) }}</td>
                <td class="text-right">{{ number_format($total_egresos, 2) }}</td>
                <td class="text-right">-</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>