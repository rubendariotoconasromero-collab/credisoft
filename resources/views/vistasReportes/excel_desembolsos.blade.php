<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
<body>
    <table>
        <tr>
            <td colspan="9" style="text-align:center; font-size:16px; font-weight:bold;">{{ strtoupper($empresa->nombre ?? 'EMPRESA') }}</td>
        </tr>
        <tr>
            <td colspan="9" style="text-align:center; font-size:14px; font-weight:bold;">REPORTE DE DESEMBOLSOS</td>
        </tr>
        <tr>
            <td colspan="9" style="text-align:center; font-size:11px;">
                Generado: {{ $fecha_reporte }} &nbsp;&nbsp; Usuario: {{ $usuario }}
                @if(!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin']))
                    &nbsp;&nbsp; Período:
                    @if(!empty($filtros['fecha_inicio'])) desde {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} @endif
                    @if(!empty($filtros['fecha_fin'])) hasta {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }} @endif
                @endif
            </td>
        </tr>
        <tr><td colspan="9"></td></tr>

        <tr>
            <th style="background-color:#065f46;color:#fff;font-weight:bold;border:1px solid #064e3b;width:50px;">Cód.</th>
            <th style="background-color:#065f46;color:#fff;font-weight:bold;border:1px solid #064e3b;width:180px;">Cliente</th>
            <th style="background-color:#065f46;color:#fff;font-weight:bold;border:1px solid #064e3b;width:90px;">C.I.</th>
            <th style="background-color:#065f46;color:#fff;font-weight:bold;border:1px solid #064e3b;width:130px;">Asesor</th>
            <th style="background-color:#065f46;color:#fff;font-weight:bold;border:1px solid #064e3b;width:90px;">Fecha Desembolso</th>
            <th style="background-color:#065f46;color:#fff;font-weight:bold;border:1px solid #064e3b;width:100px;">Garantía</th>
            <th style="background-color:#065f46;color:#fff;font-weight:bold;border:1px solid #064e3b;width:100px;">Frec. / Cuotas</th>
            <th style="background-color:#065f46;color:#fff;font-weight:bold;border:1px solid #064e3b;width:110px;">Pago Adm. (Bs.)</th>
            <th style="background-color:#065f46;color:#fff;font-weight:bold;border:1px solid #064e3b;width:110px;">Monto (Bs.)</th>
        </tr>

        @foreach($registros as $r)
        <tr>
            <td style="border:1px solid #ccc;text-align:center;font-weight:bold;">#{{ $r->id_plan_pago }}</td>
            <td style="border:1px solid #ccc;text-transform:uppercase;font-weight:bold;">{{ $r->cliente_nombre }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ $r->cliente_ci }}</td>
            <td style="border:1px solid #ccc;text-transform:uppercase;">{{ $r->asesor_nombre }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ \Carbon\Carbon::parse($r->fecha_desembolso)->format('d/m/Y') }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ $r->tipo_garantia ?? '—' }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ $r->lapso_capital }} / {{ $r->nro_cuotas }}</td>
            <td style="border:1px solid #ccc;text-align:right;">{{ number_format($r->monto_pago_adm, 2) }}</td>
            <td style="border:1px solid #ccc;text-align:right;font-weight:bold;color:#065f46;">{{ number_format($r->monto, 2) }}</td>
        </tr>
        @endforeach

        <tr>
            <td colspan="7" style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#d1fae5;">TOTALES ({{ $registros->count() }} desembolsos):</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#d1fae5;">{{ number_format($total_pago_adm, 2) }}</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#d1fae5;color:#065f46;">{{ number_format($total_desembolso, 2) }}</td>
        </tr>
    </table>
</body>
</html>
