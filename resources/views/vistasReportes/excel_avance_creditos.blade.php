<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
<body>
    <table>
        <tr>
            <td colspan="10" style="text-align:center; font-size:16px; font-weight:bold;">{{ strtoupper($empresa->nombre ?? 'EMPRESA') }}</td>
        </tr>
        <tr>
            <td colspan="10" style="text-align:center; font-size:14px; font-weight:bold;">AVANCE DE PAGO DE CRÉDITOS</td>
        </tr>
        <tr>
            <td colspan="10" style="text-align:center; font-size:11px;">
                Generado: {{ $fecha_reporte }} &nbsp;&nbsp; Usuario: {{ $usuario }}
                @if(!empty($filtros['pct_inicio']) || !empty($filtros['pct_fin']))
                    &nbsp;&nbsp; Rango: {{ $filtros['pct_inicio'] ?? 0 }}% — {{ $filtros['pct_fin'] ?? 100 }}%
                @endif
            </td>
        </tr>
        <tr><td colspan="10"></td></tr>

        <tr>
            <th style="background-color:#064e3b;color:#fff;font-weight:bold;border:1px solid #022c22;width:50px;">Cód.</th>
            <th style="background-color:#064e3b;color:#fff;font-weight:bold;border:1px solid #022c22;width:180px;">Cliente</th>
            <th style="background-color:#064e3b;color:#fff;font-weight:bold;border:1px solid #022c22;width:90px;">C.I.</th>
            <th style="background-color:#064e3b;color:#fff;font-weight:bold;border:1px solid #022c22;width:130px;">Asesor</th>
            <th style="background-color:#064e3b;color:#fff;font-weight:bold;border:1px solid #022c22;width:80px;">Frecuencia</th>
            <th style="background-color:#064e3b;color:#fff;font-weight:bold;border:1px solid #022c22;width:70px;">Cuotas Pagadas</th>
            <th style="background-color:#064e3b;color:#fff;font-weight:bold;border:1px solid #022c22;width:110px;">Total Crédito</th>
            <th style="background-color:#064e3b;color:#fff;font-weight:bold;border:1px solid #022c22;width:110px;">Capital Pagado</th>
            <th style="background-color:#064e3b;color:#fff;font-weight:bold;border:1px solid #022c22;width:80px;">% Avance</th>
            <th style="background-color:#064e3b;color:#fff;font-weight:bold;border:1px solid #022c22;width:80px;">Estado</th>
        </tr>

        @foreach($registros as $r)
        <tr>
            <td style="border:1px solid #ccc;text-align:center;font-weight:bold;">#{{ $r->credito_id }}</td>
            <td style="border:1px solid #ccc;text-transform:uppercase;font-weight:bold;">{{ $r->cliente_nombre }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ $r->cliente_ci }}</td>
            <td style="border:1px solid #ccc;text-transform:uppercase;">{{ $r->asesor_nombre }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ $r->lapso_capital }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ $r->cuotas_pagadas }}/{{ $r->nro_cuotas }}</td>
            <td style="border:1px solid #ccc;text-align:right;">{{ number_format($r->total_pagar, 2) }}</td>
            <td style="border:1px solid #ccc;text-align:right;">{{ number_format($r->capital_pagado, 2) }}</td>
            <td style="border:1px solid #ccc;text-align:center;font-weight:bold;
                @if($r->porcentaje_pagado >= 75) color:#059669;
                @elseif($r->porcentaje_pagado >= 50) color:#0284c7;
                @elseif($r->porcentaje_pagado >= 25) color:#d97706;
                @else color:#dc2626;
                @endif">
                {{ number_format($r->porcentaje_pagado, 1) }}%
            </td>
            <td style="border:1px solid #ccc;text-align:center;">
                {{ $r->estado_plan == 2 ? 'Terminado' : 'Vigente' }}
            </td>
        </tr>
        @endforeach

        <tr>
            <td colspan="6" style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#d1fae5;">TOTALES ({{ $registros->count() }} créditos):</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#d1fae5;">{{ number_format($registros->sum('total_pagar'), 2) }}</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#d1fae5;">{{ number_format($registros->sum('capital_pagado'), 2) }}</td>
            <td style="text-align:center;font-weight:bold;border:1px solid #000;background-color:#d1fae5;color:#059669;">
                @if($registros->count() > 0) Prom: {{ number_format($registros->avg('porcentaje_pagado'), 1) }}% @endif
            </td>
            <td style="border:1px solid #000;background-color:#d1fae5;"></td>
        </tr>
    </table>
</body>
</html>
