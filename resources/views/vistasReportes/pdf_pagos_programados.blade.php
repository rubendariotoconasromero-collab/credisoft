@extends('reporte.plantilla_reporte')

@section('title', 'Reporte de Pagos Programados')
@section('report_title', 'Reporte de Pagos Programados')

@section('styles')
.header-pp {
    background-color: #ffffff;
    color: #1a3a5c;
    font-weight: bold;
    padding: 4px 6px;
    text-align: center;
    font-size: 8.5pt;
    text-transform: uppercase;
    border: 1px solid #cbd5e1;
    border-bottom: 2px solid #3b82f6;
    vertical-align: middle;
}
.fila-cuota td {
    border-bottom: 1px solid #e0e0e0;
    padding: 5px 6px;
    font-size: 9pt;
    vertical-align: middle;
}
.fila-cuota:nth-child(odd) td {
    background-color: #fdfeff;
}
.fila-cuota:nth-child(even) td {
    background-color: #f0f7ff;
}
.badge-dias-futuro  { color: #1d6a32; font-weight: bold; }
.badge-dias-hoy     { color: #7a4d00; font-weight: bold; }
.badge-dias-vencida { color: #c0392b; font-weight: bold; }
.badge-parcial      { color: #6d4c00; }
.badge-pendiente    { color: #1a3a5c; }
.totales-row td {
    background-color: #e8f0fe;
    border-top: 2px solid #3b82f6;
    font-weight: bold;
    padding: 6px 8px;
    font-size: 9pt;
}
.text-right  { text-align: right; }
.text-center { text-align: center; }
.text-blue   { color: #1a3a5c; }
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
            <span class="resumen-label">Cuotas Programadas</span>
            <span class="resumen-valor text-blue">{{ $cuotas->count() }}</span>
        </td>
        <td>
            <span class="resumen-label">Capital Total</span>
            <span class="resumen-valor" style="font-size:10pt;color:#1a3a5c;">{{ number_format($cuotas->sum('capital_pendiente'), 2) }} Bs.</span>
        </td>
        <td>
            <span class="resumen-label">Interés Total</span>
            <span class="resumen-valor" style="font-size:10pt;color:#7a4d00;">{{ number_format($cuotas->sum('interes_pendiente'), 2) }} Bs.</span>
        </td>
        <td>
            <span class="resumen-label">Total a Cobrar</span>
            <span class="resumen-valor" style="font-size:10pt;color:#1d6a32;">{{ number_format($cuotas->sum('monto_pendiente'), 2) }} Bs.</span>
        </td>
    </tr>
</table>
@endsection

@section('content')
@if(!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin']))
<p class="filtros-info">
    Período:
    @if(!empty($filtros['fecha_inicio'])) desde {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} @endif
    @if(!empty($filtros['fecha_fin'])) hasta {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }} @endif
</p>
@endif
<div class="rounded-table-wrapper">
<table class="data-table" style="width:100%;border-collapse:collapse;">
    <thead>
        <tr>
            <th class="header-pp" style="width:35px;">Cód.</th>
            <th class="header-pp" style="width:145px;">Cliente</th>
            <th class="header-pp" style="width:60px;">C.I.</th>
            <th class="header-pp" style="width:85px;">Asesor</th>
            <th class="header-pp" style="width:45px;">Cuota</th>
            <th class="header-pp" style="width:55px;">Frecuencia</th>
            <th class="header-pp" style="width:60px;">Vencimiento</th>
            <th class="header-pp" style="width:60px;">Capital</th>
            <th class="header-pp" style="width:60px;">Interés</th>
            <th class="header-pp" style="width:65px;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cuotas as $c)
        <tr class="fila-cuota">
            <td class="text-center fw-bold text-blue">#{{ $c->credito_id }}</td>
            <td class="text-upper fw-bold">{{ $c->cliente_nombre }}</td>
            <td class="text-center">{{ $c->cliente_ci }}</td>
            <td class="text-upper" style="font-size:8.5pt;">{{ $c->asesor_nombre }}</td>
            <td class="text-center">
                {{ $c->nro_cuota }}/{{ $c->nro_cuotas }}
            </td>
            <td class="text-center" style="font-size:8pt;">{{ $c->lapso_capital }}</td>
            <td class="text-center">
                {{ \Carbon\Carbon::parse($c->fecha_vencimiento)->format('d/m/Y') }}
                @if($c->dias_para_pago > 0)
                    <br><span class="badge-dias-futuro" style="font-size:7.5pt;">+{{ $c->dias_para_pago }}d</span>
                @elseif($c->dias_para_pago == 0)
                    <br><span class="badge-dias-hoy" style="font-size:7.5pt;">HOY</span>
                @else
                    <br><span class="badge-dias-vencida" style="font-size:7.5pt;">{{ $c->dias_para_pago }}d</span>
                @endif
            </td>
            <td class="text-right" style="color:#1a3a5c;">{{ number_format($c->capital_pendiente, 2) }}</td>
            <td class="text-right" style="color:#555;">{{ number_format($c->interes_pendiente, 2) }}</td>
            <td class="text-right fw-bold" style="color:#1d6a32;">{{ number_format($c->monto_pendiente, 2) }}</td>
        </tr>
        @endforeach

        <tr class="totales-row">
            <td colspan="7" class="text-right">TOTALES ({{ $cuotas->count() }} cuotas):</td>
            <td class="text-right" style="color:#1a3a5c;">{{ number_format($cuotas->sum('capital_pendiente'), 2) }}</td>
            <td class="text-right" style="color:#555;">{{ number_format($cuotas->sum('interes_pendiente'), 2) }}</td>
            <td class="text-right" style="color:#1d6a32;">{{ number_format($cuotas->sum('monto_pendiente'), 2) }}</td>
        </tr>
    </tbody>
</table>
</div>
@endsection

@section('footer_note', 'Este reporte muestra las cuotas pendientes de créditos vigentes en el período seleccionado. Los montos reflejan el saldo pendiente descontando pagos parciales ya realizados.')
