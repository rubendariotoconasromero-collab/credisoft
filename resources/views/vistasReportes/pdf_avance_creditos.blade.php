@extends('reporte.plantilla_reporte')

@section('title', 'Avance de Pago de Créditos')
@section('report_title', 'Avance de Pago de Créditos')

@section('styles')
.header-avance {
    background-color: #ffffff;
    color: #064e3b;
    font-weight: bold;
    padding: 4px 6px;
    text-align: center;
    font-size: 8.5pt;
    text-transform: uppercase;
    border: 1px solid #cbd5e1;
    border-bottom: 2px solid #10b981;
    vertical-align: middle;
}
.fila-credito td {
    border-bottom: 1px solid #e0e0e0;
    padding: 5px 6px;
    font-size: 9pt;
    vertical-align: middle;
}
.fila-credito:nth-child(odd) td  { background-color: #fafffe; }
.fila-credito:nth-child(even) td { background-color: #f0fdf4; }
.totales-row td {
    background-color: #d1fae5;
    border-top: 2px solid #10b981;
    font-weight: bold;
    padding: 6px 8px;
    font-size: 9pt;
}
/* Barra de progreso en PDF (usando tabla de 1 celda ancha) */
.progress-bar-container {
    width: 100%;
    background-color: #e5e7eb;
    border-radius: 4px;
    height: 7px;
    margin-bottom: 2px;
}
.progress-fill {
    height: 7px;
    border-radius: 4px;
    display: inline-block;
}
.pct-alto   { color: #059669; font-weight: bold; }
.pct-medio  { color: #0284c7; font-weight: bold; }
.pct-bajo   { color: #d97706; font-weight: bold; }
.pct-minimo { color: #dc2626; font-weight: bold; }
.badge-vigente   { color: #059669; font-weight: bold; }
.badge-terminado { color: #6b7280; font-weight: bold; }
.text-right  { text-align: right; }
.text-center { text-align: center; }
.text-upper  { text-transform: uppercase; }
.fw-bold     { font-weight: bold; }
.resumen-box {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
}
.resumen-box td {
    width: 25%;
    text-align: center;
    padding: 5px;
    border: 1px solid #ddd;
    vertical-align: middle;
}
.resumen-label { font-size: 7.5pt; color: #666; text-transform: uppercase; display: block; }
.resumen-valor { font-size: 11pt; font-weight: bold; display: block; }
.filtros-info {
    font-size: 8pt;
    color: #555;
    margin-bottom: 10px;
    padding: 5px 8px;
    border: 1px solid #e0e0e0;
    background-color: #f8f9fa;
}
@endsection

@section('totals_box')
<table class="resumen-box">
    <tr>
        <td>
            <span class="resumen-label">Créditos</span>
            <span class="resumen-valor" style="color:#064e3b;">{{ $registros->count() }}</span>
        </td>
        <td>
            <span class="resumen-label">Monto Total</span>
            <span class="resumen-valor" style="font-size:10pt;color:#1e3a8a;">{{ number_format($registros->sum('total_pagar'), 2) }} Bs.</span>
        </td>
        <td>
            <span class="resumen-label">Capital Pagado</span>
            <span class="resumen-valor" style="font-size:10pt;color:#0284c7;">{{ number_format($registros->sum('capital_pagado'), 2) }} Bs.</span>
        </td>
        <td>
            <span class="resumen-label">Promedio Avance</span>
            <span class="resumen-valor" style="font-size:10pt;color:#059669;">
                @if($registros->count() > 0)
                    {{ number_format($registros->avg('porcentaje_pagado'), 1) }}%
                @else
                    0%
                @endif
            </span>
        </td>
    </tr>
</table>
@endsection

@section('content')
@if(!empty($filtros['pct_inicio']) || !empty($filtros['pct_fin']))
<p class="filtros-info">
    Rango de avance:
    {{ $filtros['pct_inicio'] ?? 0 }}% — {{ $filtros['pct_fin'] ?? 100 }}%
    @if(!empty($filtros['estado_plan']) && $filtros['estado_plan'] !== 'todos')
        &nbsp;|&nbsp; Estado: {{ $filtros['estado_plan'] == '1' ? 'Vigente' : 'Terminado' }}
    @endif
</p>
@endif
<div class="rounded-table-wrapper">
<table class="data-table" style="width:100%;border-collapse:collapse;">
    <thead>
        <tr>
            <th class="header-avance" style="width:35px;">Cód.</th>
            <th class="header-avance" style="width:145px;">Cliente</th>
            <th class="header-avance" style="width:60px;">C.I.</th>
            <th class="header-avance" style="width:85px;">Asesor</th>
            <th class="header-avance" style="width:55px;">Frecuencia</th>
            <th class="header-avance" style="width:45px;">Cuotas</th>
            <th class="header-avance" style="width:70px;">Total Crédito</th>
            <th class="header-avance" style="width:70px;">Cap. Pagado</th>
            <th class="header-avance" style="width:80px;">Avance</th>
            <th class="header-avance" style="width:50px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registros as $r)
        @php
            $pct = (float) $r->porcentaje_pagado;
            $fillW = min($pct, 100);
            $fillColor = $pct >= 75 ? '#059669' : ($pct >= 50 ? '#0284c7' : ($pct >= 25 ? '#f59e0b' : '#dc2626'));
            $pctClass  = $pct >= 75 ? 'pct-alto' : ($pct >= 50 ? 'pct-medio' : ($pct >= 25 ? 'pct-bajo' : 'pct-minimo'));
        @endphp
        <tr class="fila-credito">
            <td class="text-center fw-bold" style="color:#064e3b;">#{{ $r->credito_id }}</td>
            <td class="text-upper fw-bold">{{ $r->cliente_nombre }}</td>
            <td class="text-center">{{ $r->cliente_ci }}</td>
            <td class="text-upper" style="font-size:8.5pt;">{{ $r->asesor_nombre }}</td>
            <td class="text-center" style="font-size:8.5pt;">{{ $r->lapso_capital }}</td>
            <td class="text-center">{{ $r->cuotas_pagadas }}/{{ $r->nro_cuotas }}</td>
            <td class="text-right" style="color:#1e3a8a;">{{ number_format($r->total_pagar, 2) }}</td>
            <td class="text-right" style="color:#0284c7;">{{ number_format($r->capital_pagado, 2) }}</td>
            <td>
                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td style="padding:0;background-color:#e5e7eb;border-radius:4px;height:7px;width:100%;">
                            <div style="width:{{ $fillW }}%;height:7px;background-color:{{ $fillColor }};border-radius:4px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:1px 0 0 0;" class="{{ $pctClass }} text-center" style="font-size:8.5pt;">{{ number_format($pct, 1) }}%</td>
                    </tr>
                </table>
            </td>
            <td class="text-center">
                @if($r->estado_plan == 2)
                    <span class="badge-terminado" style="font-size:8pt;">Terminado</span>
                @else
                    <span class="badge-vigente" style="font-size:8pt;">Vigente</span>
                @endif
            </td>
        </tr>
        @endforeach

        <tr class="totales-row">
            <td colspan="6" class="text-right">TOTALES ({{ $registros->count() }} créditos):</td>
            <td class="text-right" style="color:#1e3a8a;">{{ number_format($registros->sum('total_pagar'), 2) }}</td>
            <td class="text-right" style="color:#0284c7;">{{ number_format($registros->sum('capital_pagado'), 2) }}</td>
            <td class="text-center pct-alto">
                @if($registros->count() > 0)
                    Prom: {{ number_format($registros->avg('porcentaje_pagado'), 1) }}%
                @endif
            </td>
            <td></td>
        </tr>
    </tbody>
</table>
</div>
@endsection

@section('footer_note', 'Este reporte muestra el porcentaje de avance en el pago de capital de cada crédito activo o terminado. El porcentaje se calcula sobre el total del crédito desembolsado.')
