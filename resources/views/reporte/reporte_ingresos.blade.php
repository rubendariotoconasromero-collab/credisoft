@extends('reporte.plantilla_reporte')

@section('title', 'Reporte de Ingresos')
@section('report_title', 'Reporte de Ingresos')

@section('info_left')
    <table class="data-table" style="margin-bottom: 0;">
        <tr>
            <th style="width: 40%;">Rango de Fechas</th>
            <td>{{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Tipo de Movimiento</th>
            <td>{{ $tipo_filtro }}</td>
        </tr>
    </table>
@endsection

@section('info_right')
    <table class="data-table" style="margin-bottom: 0;">
        <tr>
            <th style="width: 40%;">NIT Empresa</th>
            <td>{{ empty($empresa->nit) ? '123456789' : $empresa->nit }}</td>
        </tr>
        <tr>
            <th>Estado Reporte</th>
            <td>CONSULTA OFICIAL</td>
        </tr>
    </table>
@endsection

@section('totals_box')
    <div style="border: 1px solid #a3a3a3; border-radius: 6px; padding: 10px; background-color: #f9fafb; margin-top: 15px; margin-bottom: 15px;">
        <table class="data-table" style="margin-bottom: 0; width: 100%; border: none;">
            <tr>
                <th style="width: 50%; text-align: center;">Total Ingresos Recaudados</th>
                <th style="width: 50%; text-align: center;">Movimientos Filtrados</th>
            </tr>
            <tr>
                <td style="text-align: center; font-size: 12px; font-weight: bold; color: #1a1a1a;">Bs {{ number_format($total, 2) }}</td>
                <td style="text-align: center; font-size: 12px; font-weight: bold; color: #1a1a1a;">{{ count($movimientos) }}</td>
            </tr>
        </table>
    </div>
@endsection

@section('content')
    <div class="rounded-table-wrapper">
        <table class="data-table" style="margin-bottom: 0; border: none;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="text-align:center; border-bottom: 1px solid #a3a3a3;">Nro</th>
                    <th style="text-align:center; border-bottom: 1px solid #a3a3a3;">Fecha</th>
                    <th style="text-align:left; border-bottom: 1px solid #a3a3a3;">Tipo de Ingreso</th>
                    <th style="text-align:left; border-bottom: 1px solid #a3a3a3;">Descripción / Detalle</th>
                    <th style="text-align:right; border-bottom: 1px solid #a3a3a3;">Monto Recaudado (Bs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimientos as $mov)
                <?php
                $nombres_tipo = [
                    'INTERES' => 'INTERÉS',
                    'MORA' => 'MORA',
                    'GASTOSADM' => 'GASTO ADM.',
                    'INGRESO_CAJA' => 'INGRESO CAJA',
                    'EGRESO_CAJA' => 'EGRESO CAJA',
                    'CAPITAL' => 'CAPITAL',
                    'DESEMBOLSO' => 'DESEMBOLSO',
                    'BOVEDA_INGRESO' => 'ING. BÓVEDA',
                    'BOVEDA_EGRESO' => 'EGR. BÓVEDA',
                    'TRANSFER_INTERNO' => 'TRANSF. INTERNA'
                ];
                $tipo_formateado = $nombres_tipo[$mov->tipo] ?? $mov->tipo;
                ?>
                <tr>
                    <td style="text-align:center">{{ $mov->nro }}</td>
                    <td style="text-align:center">{{ $mov->fecha }}</td>
                    <td style="text-align:left; font-weight: bold; color: #555;">{{ $tipo_formateado }}</td>
                    <td style="text-align:left; text-transform: uppercase;">{{ $mov->descripcion }}</td>
                    <td style="text-align:right; font-weight: bold;">{{ number_format($mov->monto, 2) }}</td>
                </tr>
                @endforeach
                <tr style="font-weight: bold; background-color: #f3f4f6;">
                    <td colspan="4" style="text-align:right; border-top: 1px solid #a3a3a3;">TOTAL INGRESOS:</td>
                    <td style="text-align:right; border-top: 1px solid #a3a3a3; color: #1a1a1a;">{{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection