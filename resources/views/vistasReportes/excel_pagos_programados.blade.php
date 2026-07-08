<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
<body>
    <table>
        <tr>
            <td colspan="11" style="text-align:center; font-size:16px; font-weight:bold;">{{ strtoupper($empresa->nombre ?? 'EMPRESA') }}</td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center; font-size:14px; font-weight:bold;">REPORTE DE PAGOS PROGRAMADOS</td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center; font-size:11px;">
                Generado: {{ $fecha_reporte }} &nbsp;&nbsp; Usuario: {{ $usuario }}
                @if(!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin']))
                    &nbsp;&nbsp; Período:
                    @if(!empty($filtros['fecha_inicio'])) desde {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} @endif
                    @if(!empty($filtros['fecha_fin'])) hasta {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }} @endif
                @endif
            </td>
        </tr>
        <tr><td colspan="11"></td></tr>

        <tr>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:50px;">Cód.</th>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:180px;">Cliente</th>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:90px;">C.I.</th>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:130px;">Asesor</th>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:60px;">Nro. Cuota</th>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:80px;">Frecuencia</th>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:90px;">Fecha Venc.</th>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:100px;">Capital Pend.</th>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:100px;">Interés Pend.</th>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:100px;">Total Pend.</th>
            <th style="background-color:#1a3a5c;color:#fff;font-weight:bold;border:1px solid #0f2540;width:80px;">Días</th>
        </tr>

        @foreach($cuotas as $c)
        <tr>
            <td style="border:1px solid #ccc;text-align:center;font-weight:bold;">#{{ $c->credito_id }}</td>
            <td style="border:1px solid #ccc;text-transform:uppercase;font-weight:bold;">{{ $c->cliente_nombre }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ $c->cliente_ci }}</td>
            <td style="border:1px solid #ccc;text-transform:uppercase;">{{ $c->asesor_nombre }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ $c->nro_cuota }}/{{ $c->nro_cuotas }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ $c->lapso_capital }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ \Carbon\Carbon::parse($c->fecha_vencimiento)->format('d/m/Y') }}</td>
            <td style="border:1px solid #ccc;text-align:right;">{{ number_format($c->capital_pendiente, 2) }}</td>
            <td style="border:1px solid #ccc;text-align:right;">{{ number_format($c->interes_pendiente, 2) }}</td>
            <td style="border:1px solid #ccc;text-align:right;font-weight:bold;">{{ number_format($c->monto_pendiente, 2) }}</td>
            <td style="border:1px solid #ccc;text-align:center;@if($c->dias_para_pago < 0) color:#c0392b;font-weight:bold; @elseif($c->dias_para_pago == 0) color:#7a4d00;font-weight:bold; @else color:#1d6a32; @endif">
                @if($c->dias_para_pago > 0)
                    +{{ $c->dias_para_pago }} días
                @elseif($c->dias_para_pago == 0)
                    HOY
                @else
                    {{ $c->dias_para_pago }} días
                @endif
            </td>
        </tr>
        @endforeach

        <tr>
            <td colspan="7" style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#dbeafe;">TOTALES ({{ $cuotas->count() }} cuotas):</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#dbeafe;">{{ number_format($cuotas->sum('capital_pendiente'), 2) }}</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#dbeafe;">{{ number_format($cuotas->sum('interes_pendiente'), 2) }}</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#dbeafe;">{{ number_format($cuotas->sum('monto_pendiente'), 2) }}</td>
            <td style="border:1px solid #000;background-color:#dbeafe;"></td>
        </tr>
    </table>
</body>
</html>
