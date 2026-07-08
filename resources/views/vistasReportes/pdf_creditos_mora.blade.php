@extends('reporte.plantilla_reporte')

@section('title', 'Reporte de Créditos en Mora')
@section('report_title', 'Reporte de Créditos en Mora')

@section('styles')
.header-mora {
    background-color: #f3f4f6;
    color: #222;
    font-weight: bold;
    padding: 4px 5px;
    text-align: center;
    font-size: 7.5pt;
    text-transform: uppercase;
    border: 1px solid #cbd5e1;
    border-bottom: 2px solid #a3a3a3;
    vertical-align: middle;
}
.fila-credito td {
    border-bottom: 1px solid #d9d9d9;
    padding: 5px 5px;
    font-size: 8pt;
    vertical-align: middle;
    background-color: #ffffff;
}
.fila-cuota td {
    padding: 3px 5px;
    font-size: 7pt;
    color: #444;
    border-bottom: 1px solid #f0e0e0;
    background-color: #fff7f7;
    vertical-align: middle;
}
.cuota-id { border-left: 3px solid #c0392b; }
.sub-line { font-size: 6pt; color: #777; }
.sub-dev  { color: #2980b9; }
.sub-mor  { color: #c0392b; }
.sub-pag  { color: #1e7e34; }
.badge-dias-alta   { color: #c0392b; font-weight: bold; }
.badge-dias-media  { color: #e67e22; font-weight: bold; }
.badge-dias-baja   { color: #7f8c8d; }
.totales-row td {
    background-color: #f3f4f6;
    border-top: 2px solid #c0392b;
    font-weight: bold;
    padding: 6px 6px;
    font-size: 8pt;
}
.text-right  { text-align: right; }
.text-center { text-align: center; }
.text-red    { color: #c0392b; }
.text-blue   { color: #2980b9; }
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
@endsection

@section('totals_box')
<table class="resumen-box">
    <tr>
        <td>
            <span class="resumen-label">Créditos en mora</span>
            <span class="resumen-valor text-red">{{ $creditos->count() }}</span>
        </td>
        <td>
            <span class="resumen-label">Total cuotas vencidas</span>
            <span class="resumen-valor" style="color:#e67e22;">{{ $creditos->sum('cuotas_mora_count') }}</span>
        </td>
        <td>
            <span class="resumen-label">Capital pendiente</span>
            <span class="resumen-valor" style="font-size:10pt;color:#2980b9;">{{ number_format($creditos->sum('total_capital_mora'), 2) }} Bs.</span>
        </td>
        <td>
            <span class="resumen-label">Deuda total en mora</span>
            <span class="resumen-valor text-red" style="font-size:10pt;">{{ number_format($creditos->sum('total_cuota_mora'), 2) }} Bs.</span>
        </td>
    </tr>
</table>
@endsection

@section('content')
<div class="rounded-table-wrapper">
<table class="data-table" style="width:100%;border-collapse:collapse;">
    <thead>
        <tr>
            <th class="header-mora" style="width:32px;">Cód.</th>
            <th class="header-mora" style="width:115px;">Cliente</th>
            <th class="header-mora" style="width:60px;">C.I.</th>
            <th class="header-mora" style="width:75px;">Asesor</th>
            <th class="header-mora" style="width:38px;">Cuotas</th>
            <th class="header-mora" style="width:42px;">Días Mora</th>
            <th class="header-mora" style="width:62px;">Saldo Capital</th>
            <th class="header-mora" style="width:62px;">Cap. Pendiente</th>
            <th class="header-mora" style="width:70px;">Int. Pendiente</th>
            <th class="header-mora" style="width:55px;">Mora</th>
            <th class="header-mora" style="width:65px;">Total Deuda</th>
        </tr>
    </thead>
    <tbody>
        @foreach($creditos as $c)
        <tr class="fila-credito">
            <td class="text-center fw-bold text-red">#{{ $c->credito_id }}</td>
            <td class="text-upper fw-bold">{{ $c->cliente_nombre }}</td>
            <td class="text-center">{{ $c->cliente_ci }}</td>
            <td class="text-upper" style="font-size:7.5pt;">{{ $c->asesor_nombre }}</td>
            <td class="text-center text-red fw-bold">{{ $c->cuotas_mora_count }}</td>
            <td class="text-center">
                @if($c->dias_mora_max >= 90)
                    <span class="badge-dias-alta">{{ $c->dias_mora_max }}d</span>
                @elseif($c->dias_mora_max >= 30)
                    <span class="badge-dias-media">{{ $c->dias_mora_max }}d</span>
                @else
                    <span class="badge-dias-baja">{{ $c->dias_mora_max }}d</span>
                @endif
            </td>
            <td class="text-right text-blue fw-bold">{{ number_format($c->saldo_pendiente, 2) }}</td>
            <td class="text-right">{{ number_format($c->total_capital_mora, 2) }}</td>
            <td class="text-right" style="color:#555;">{{ number_format($c->total_interes_mora, 2) }}</td>
            <td class="text-right text-red">{{ number_format($c->total_multas_mora, 2) }}</td>
            <td class="text-right fw-bold text-red">{{ number_format($c->total_cuota_mora, 2) }}</td>
        </tr>
        @foreach($c->cuotas_mora as $cuota)
        <tr class="fila-cuota">
            <td class="cuota-id"></td>
            <td colspan="4" style="padding-left:14px;">
                ↳ <strong>Cuota #{{ $cuota->numero }}</strong>
                &nbsp;·&nbsp; vence {{ \Carbon\Carbon::parse($cuota->fecha)->format('d/m/Y') }}
                &nbsp;·&nbsp;
                @if($cuota->estado == 3)
                    <span style="color:#e67e22;">Pago Parcial</span>
                @else
                    <span style="color:#c0392b;">Vencida</span>
                @endif
                <span class="sub-line">&nbsp; ({{ $cuota->dias_transcurridos }} días transc.)</span>
            </td>
            <td class="text-center">
                @if($cuota->dias_pasados >= 90)
                    <span class="badge-dias-alta">{{ $cuota->dias_pasados }}d</span>
                @elseif($cuota->dias_pasados >= 30)
                    <span class="badge-dias-media">{{ $cuota->dias_pasados }}d</span>
                @else
                    <span class="badge-dias-baja">{{ $cuota->dias_pasados }}d</span>
                @endif
            </td>
            <td class="text-right">{{ number_format($cuota->saldo_capital, 2) }}</td>
            <td class="text-right">
                {{ number_format($cuota->capital_neto, 2) }}
                @if($cuota->capital_pagado_total > 0)
                    <br><span class="sub-line sub-pag">Pag: {{ number_format($cuota->capital_pagado_total, 2) }} ({{ $cuota->porcentaje_capital_pagado }}%)</span>
                @endif
            </td>
            <td class="text-right">
                {{ number_format($cuota->interes_acumulado_neto, 2) }}
                <br><span class="sub-line"><span class="sub-dev">Dev: {{ number_format($cuota->interes_devengado_neto, 2) }}</span> · <span class="sub-mor">Mor: {{ number_format($cuota->interes_moratorio_neto, 2) }}</span></span>
                @if($cuota->interes_pagado_total > 0)
                    <br><span class="sub-line sub-pag">Pag: {{ number_format($cuota->interes_pagado_total, 2) }}</span>
                @endif
            </td>
            <td class="text-right text-red">
                {{ number_format($cuota->mora_fija_neta, 2) }}
                @if($cuota->mora_pagada_total > 0)
                    <br><span class="sub-line sub-pag">Pag: {{ number_format($cuota->mora_pagada_total, 2) }}</span>
                @endif
            </td>
            <td class="text-right fw-bold text-red">{{ number_format($cuota->total_a_pagar, 2) }}</td>
        </tr>
        @endforeach
        @endforeach

        <tr class="totales-row">
            <td colspan="6" class="text-right">TOTALES ({{ $creditos->count() }} créditos):</td>
            <td class="text-right text-blue">{{ number_format($creditos->sum('saldo_pendiente'), 2) }}</td>
            <td class="text-right">{{ number_format($creditos->sum('total_capital_mora'), 2) }}</td>
            <td class="text-right" style="color:#555;">{{ number_format($creditos->sum('total_interes_mora'), 2) }}</td>
            <td class="text-right text-red">{{ number_format($creditos->sum('total_multas_mora'), 2) }}</td>
            <td class="text-right text-red">{{ number_format($creditos->sum('total_cuota_mora'), 2) }}</td>
        </tr>
    </tbody>
</table>
</div>
@endsection

@section('footer_note', 'Este reporte muestra los créditos con al menos una cuota vencida. El interés pendiente incluye interés devengado más interés moratorio (Mor), y la Mora corresponde a la multa fija. El "Total Deuda" de cada crédito es la suma del "Total a Pagar" de sus cuotas vencidas.')
