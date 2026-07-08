@php 
function evaluandoEstado($estado){
    if($estado==1){
        return 'En proceso';
    }else if($estado==0){
        return 'Anulado';
    }else if($estado==2){
        return 'Cancelado';
    }else if($estado==10){
        return 'Amortizado';
    }
    return 'N/A';
}
@endphp

@extends('reporte.plantilla_reporte')

@section('title', 'Extracto de movimientos')
@section('report_title', 'EXTRACTO DE MOVIMIENTOS')

@section('info_left')
    <table class="data-table" style="margin-bottom: 0;">
        <tr>
            <th style="width: 40%;">Cliente</th>
            <td>{{ $informacion[0]->cliente }}</td>
        </tr>
        <tr>
            <th>CI</th>
            <td>{{ $informacion[0]->ci.' '.$informacion[0]->lugar_expedicion }}</td>
        </tr>
        <tr>
            <th>Asesor</th>
            <td>{{ $informacion[0]->personal }}</td>
        </tr>
        <tr>
            <th>Garantía</th>
            <td>{{ $informacion[0]->tipo_garantia }}</td>
        </tr>
    </table>
@endsection

@section('info_right')
    <table class="data-table" style="margin-bottom: 0;">
        <tr>
            <th style="width: 40%;">Nro. cuotas</th>
            <td>{{ $informacion[0]->nro_cuotas }}</td>
        </tr>
        <tr>
            <th>Fecha desembolso</th>
            <td>{{ $informacion[0]->fecha_desembolso }}</td>
        </tr>
        <tr>
            <th>Plazo</th>
            <td>{{ $informacion[0]->nro_cuotas.' '.$informacion[0]->lapso_capital }}</td>
        </tr>
        <tr>
            <th>Importe solicitud</th>
            <td>{{ number_format($informacion[0]->importe_solicitud, 2) }} {{ $informacion[0]->moneda }}</td>
        </tr>
        <tr>
            <th>Estado</th>
            <td>{{ evaluandoEstado($informacion[0]->estado) }}</td>
        </tr>
    </table>
@endsection

@section('content')
    <div class="rounded-table-wrapper">
        <table class="data-table" style="margin-bottom: 0; border: none;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="text-align:center; border-bottom: 1px solid #a3a3a3; width: 15%;">Fecha</th>
                    <th style="text-align:left; border-bottom: 1px solid #a3a3a3; width: 15%;">Tipo</th>
                    <th style="text-align:left; border-bottom: 1px solid #a3a3a3; width: 40%;">Descripción</th>
                    <th style="text-align:right; border-bottom: 1px solid #a3a3a3; width: 15%;">Debe</th>
                    <th style="text-align:right; border-bottom: 1px solid #a3a3a3; width: 15%;">Haber</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimientos as $movimiento)
                <tr>
                    <td style="text-align:center">{{ $movimiento->fecha }}</td>
                    <td style="text-align:left; font-weight: bold; color: #555;">{{ $movimiento->tipo }}</td>
                    <td style="text-align:left; text-transform: uppercase;">{{ $movimiento->descripcion }}</td>
                    <td style="text-align:right;">{{ $movimiento->debe > 0 ? number_format($movimiento->debe, 2) : '-' }}</td>
                    <td style="text-align:right;">{{ $movimiento->haber > 0 ? number_format($movimiento->haber, 2) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('footer_note', 'El extracto de movimientos del crédito tiene carácter informativo y oficial. Cualquier inconsistencia debe ser aclarada con su asesor asignado de inmediato.')
