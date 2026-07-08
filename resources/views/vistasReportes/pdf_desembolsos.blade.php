@extends('reporte.plantilla_reporte')

@section('title', 'Reporte de Desembolsos')
@section('report_title', 'Reporte de Desembolsos')

@section('styles')
.header-desembolso {
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
.fila-desembolso td {
    border-bottom: 1px solid #e0e0e0;
    padding: 5px 6px;
    font-size: 9pt;
    vertical-align: middle;
}
.fila-desembolso:nth-child(odd) td  { background-color: #fafffe; }
.fila-desembolso:nth-child(even) td { background-color: #f0fdf4; }
.totales-row td {
    background-color: #d1fae5;
    border-top: 2px solid #10b981;
    font-weight: bold;
    padding: 6px 8px;
    font-size: 9pt;
}
.text-right  { text-align: right; }
.text-center { text-align: center; }
.text-upper  { text-transform: uppercase; }
.fw-bold     { font-weight: bold; }
.text-green { color: #065f46; }
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
            <span class="resumen-label">Total Desembolsos</span>
            <span class="resumen-valor text-green">{{ $registros->count() }}</span>
        </td>
        <td>
            <span class="resumen-label">Monto Total Desembolsado</span>
            <span class="resumen-valor" style="font-size:10pt;color:#065f46;">{{ number_format($total_desembolso, 2) }} Bs.</span>
        </td>
        <td>
            <span class="resumen-label">Total Pago Administrativo</span>
            <span class="resumen-valor" style="font-size:10pt;color:#0284c7;">{{ number_format($total_pago_adm, 2) }} Bs.</span>
        </td>
        <td>
            <span class="resumen-label">Promedio por Desembolso</span>
            <span class="resumen-valor" style="font-size:10pt;color:#059669;">
                @if($registros->count() > 0)
                    {{ number_format($registros->avg('monto'), 2) }} Bs.
                @else
                    0.00 Bs.
                @endif
            </span>
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
            <th class="header-desembolso" style="width:35px;">Cód.</th>
            <th class="header-desembolso" style="width:140px;">Cliente</th>
            <th class="header-desembolso" style="width:60px;">C.I.</th>
            <th class="header-desembolso" style="width:85px;">Asesor</th>
            <th class="header-desembolso" style="width:60px;">Fecha</th>
            <th class="header-desembolso" style="width:70px;">Garantía</th>
            <th class="header-desembolso" style="width:55px;">Frec./Cuotas</th>
            <th class="header-desembolso" style="width:65px;">Pago Adm.</th>
            <th class="header-desembolso" style="width:75px;">Monto</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registros as $r)
        <tr class="fila-desembolso">
            <td class="text-center fw-bold text-green">#{{ $r->id_plan_pago }}</td>
            <td class="text-upper fw-bold">{{ $r->cliente_nombre }}</td>
            <td class="text-center">{{ $r->cliente_ci }}</td>
            <td class="text-upper" style="font-size:8.5pt;">{{ $r->asesor_nombre }}</td>
            <td class="text-center">{{ \Carbon\Carbon::parse($r->fecha_desembolso)->format('d/m/Y') }}</td>
            <td class="text-center" style="font-size:8.5pt;">{{ $r->tipo_garantia ?? '—' }}</td>
            <td class="text-center" style="font-size:8.5pt;">{{ $r->lapso_capital }} / {{ $r->nro_cuotas }}</td>
            <td class="text-right" style="color:#0284c7;">{{ number_format($r->monto_pago_adm, 2) }}</td>
            <td class="text-right fw-bold text-green">{{ number_format($r->monto, 2) }}</td>
        </tr>
        @endforeach

        <tr class="totales-row">
            <td colspan="7" class="text-right">TOTALES ({{ $registros->count() }} desembolsos):</td>
            <td class="text-right" style="color:#0284c7;">{{ number_format($total_pago_adm, 2) }}</td>
            <td class="text-right text-green">{{ number_format($total_desembolso, 2) }}</td>
        </tr>
    </tbody>
</table>
</div>
@endsection

@section('footer_note', 'Este reporte muestra los desembolsos realizados en el período seleccionado. El monto corresponde al capital entregado al cliente, excluyendo gastos administrativos.')
