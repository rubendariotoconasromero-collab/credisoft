<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9pt; color: #333; margin: 0; padding: 0; }
        .header-table { width: 100%; border-bottom: 2px solid #198754; padding-bottom: 10px; margin-bottom: 20px; }
        .header-table td { vertical-align: top; }
        .company-name { font-size: 14px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .company-details { font-size: 9px; color: #555; margin: 2px 0; }
        .report-title { font-size: 16px; font-weight: bold; color: #198754; margin: 0; text-transform: uppercase;}
        .filtros-box { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin-bottom: 15px; font-size: 10px; }
        .ledger-table { width: 100%; border-collapse: collapse; }
        .ledger-table thead th { background-color: #198754; color: #ffffff; padding: 8px 5px; font-size: 9px; text-transform: uppercase; text-align: center; border: 1px solid #198754; }
        .ledger-table td { padding: 6px 5px; border: 1px solid #dee2e6; vertical-align: middle; }
        .ledger-table tbody tr:nth-child(even) { background-color: #f8f9fa; }
        .text-center { text-align: center; } .text-right { text-align: right; } .text-left { text-align: left; }
        .col-monto { font-weight: bold; color: #198754; }
        .tfoot-row td { background-color: #e9ecef; color: #000; font-weight: bold; padding: 8px 5px; border-top: 2px solid #198754; }
    </style>
</head>
<body>
    <?php
        $url = empty($empresa->logo) ? 'logo_sistema_codesoft.png' : $empresa->logo;
        $path = public_path('img/' . $url);
        $html_logo = file_exists($path) ? '<img src="data:image/png;base64,' . base64_encode(file_get_contents($path)) . '" style="max-height: 45px;">' : '';
    ?>
    <table class="header-table">
        <tr>
            <td width="33%">{!! $html_logo !!}<p class="company-name">{{ $empresa->nombre ?? 'Empresa Demo' }}</p></td>
            <td width="34%" class="text-center"><h1 class="report-title">REPORTE DE INGRESOS</h1></td>
            <td width="33%" class="text-right">
                <p class="company-details"><strong>Fecha Emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                <p class="company-details"><strong>Cajero:</strong> {{ auth()->user()->name ?? 'Sistema' }}</p>
            </td>
        </tr>
    </table>
    <div class="filtros-box">
        <strong>Filtros Aplicados:</strong><br>
        Fechas: {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/Y') }} | Tipo: {{ $tipo_filtro }}
    </div>
    <table class="ledger-table">
        <thead>
            <tr>
                <th width="5%">N°</th>
                <th width="15%">Fecha</th>
                <th width="20%">Tipo de Ingreso</th>
                <th width="40%">Descripción</th>
                <th width="20%">Monto Recaudado (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
            <tr>
                <td class="text-center">{{ $mov->nro }}</td>
                <td class="text-center">{{ $mov->fecha }}</td>
                <td class="text-center">{{ $mov->tipo }}</td>
                <td class="text-left">{{ $mov->descripcion }}</td>
                <td class="text-right col-monto">{{ number_format($mov->monto, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center" style="padding:20px;">No existen ingresos en este periodo.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot-row">
                <td colspan="4" class="text-right">TOTAL INGRESOS DEL PERIODO:</td>
                <td class="text-right">{{ number_format($total, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>