<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
<body>
    <table border="0">
        <tr>
            <td colspan="13" style="text-align:center; font-size:16px; font-weight:bold;">{{ strtoupper($empresa->nombre ?? 'EMPRESA') }}</td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center; font-size:14px; font-weight:bold;">REPORTE DE CRÉDITOS EN MORA</td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center; font-size:11px;">Generado: {{ $fecha_reporte }} &nbsp;&nbsp; Usuario: {{ $usuario }}</td>
        </tr>
        <tr><td colspan="13"></td></tr>

        <tr>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Cód.</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Cliente</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">C.I.</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Asesor</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Cuotas Mora</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Días Mora</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Saldo Capital</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Cap. Pendiente</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Int. Devengado</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Int. Moratorio</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Int. Pendiente</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Mora</th>
            <th style="background-color:#c0392b;color:#fff;font-weight:bold;border:1px solid #922b21;">Total Deuda Mora</th>
        </tr>

        @foreach($creditos as $c)
        <tr>
            <td style="border:1px solid #ccc;text-align:center;font-weight:bold;">#{{ $c->credito_id }}</td>
            <td style="border:1px solid #ccc;text-transform:uppercase;font-weight:bold;">{{ $c->cliente_nombre }}</td>
            <td style="border:1px solid #ccc;text-align:center;">{{ $c->cliente_ci }}</td>
            <td style="border:1px solid #ccc;text-transform:uppercase;">{{ $c->asesor_nombre }}</td>
            <td style="border:1px solid #ccc;text-align:center;color:#c0392b;font-weight:bold;">{{ $c->cuotas_mora_count }}</td>
            <td style="border:1px solid #ccc;text-align:center;color:#c0392b;font-weight:bold;">{{ $c->dias_mora_max }} días</td>
            <td style="border:1px solid #ccc;text-align:right;color:#2980b9;font-weight:bold;">{{ number_format($c->saldo_pendiente, 2) }}</td>
            <td style="border:1px solid #ccc;text-align:right;">{{ number_format($c->total_capital_mora, 2) }}</td>
            <td style="border:1px solid #ccc;text-align:right;color:#2980b9;">{{ number_format($c->cuotas_mora->sum('interes_devengado_neto'), 2) }}</td>
            <td style="border:1px solid #ccc;text-align:right;color:#c0392b;">{{ number_format($c->cuotas_mora->sum('interes_moratorio_neto'), 2) }}</td>
            <td style="border:1px solid #ccc;text-align:right;">{{ number_format($c->total_interes_mora, 2) }}</td>
            <td style="border:1px solid #ccc;text-align:right;color:#c0392b;">{{ number_format($c->total_multas_mora, 2) }}</td>
            <td style="border:1px solid #ccc;text-align:right;font-weight:bold;color:#c0392b;">{{ number_format($c->total_cuota_mora, 2) }}</td>
        </tr>

        @foreach($c->cuotas_mora as $cuota)
        <tr>
            <td colspan="5" style="border:1px solid #eee;font-size:10px;color:#555;padding-left:14px;">
                ↳ Cuota #{{ $cuota->numero }} — Vence: {{ \Carbon\Carbon::parse($cuota->fecha)->format('d/m/Y') }}
                — @if($cuota->estado == 3) Pago Parcial @else Vencida @endif
                ({{ $cuota->dias_transcurridos }} días transc.)
            </td>
            <td style="border:1px solid #eee;text-align:center;font-size:10px;color:#c0392b;">{{ $cuota->dias_pasados }} días</td>
            <td style="border:1px solid #eee;text-align:right;font-size:10px;">{{ number_format($cuota->saldo_capital, 2) }}</td>
            <td style="border:1px solid #eee;text-align:right;font-size:10px;">{{ number_format($cuota->capital_neto, 2) }}</td>
            <td style="border:1px solid #eee;text-align:right;font-size:10px;color:#2980b9;">{{ number_format($cuota->interes_devengado_neto, 2) }}</td>
            <td style="border:1px solid #eee;text-align:right;font-size:10px;color:#c0392b;">{{ number_format($cuota->interes_moratorio_neto, 2) }}</td>
            <td style="border:1px solid #eee;text-align:right;font-size:10px;">{{ number_format($cuota->interes_acumulado_neto, 2) }}</td>
            <td style="border:1px solid #eee;text-align:right;font-size:10px;color:#c0392b;">{{ number_format($cuota->mora_fija_neta, 2) }}</td>
            <td style="border:1px solid #eee;text-align:right;font-size:10px;color:#c0392b;font-weight:bold;">{{ number_format($cuota->total_a_pagar, 2) }}</td>
        </tr>
        @endforeach
        @endforeach

        <tr>
            <td colspan="6" style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#f8d7da;">TOTALES ({{ $creditos->count() }} créditos):</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#f8d7da;">{{ number_format($creditos->sum('saldo_pendiente'), 2) }}</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#f8d7da;">{{ number_format($creditos->sum('total_capital_mora'), 2) }}</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#f8d7da;">{{ number_format($creditos->sum(fn($c) => $c->cuotas_mora->sum('interes_devengado_neto')), 2) }}</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#f8d7da;">{{ number_format($creditos->sum(fn($c) => $c->cuotas_mora->sum('interes_moratorio_neto')), 2) }}</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#f8d7da;">{{ number_format($creditos->sum('total_interes_mora'), 2) }}</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#f8d7da;">{{ number_format($creditos->sum('total_multas_mora'), 2) }}</td>
            <td style="text-align:right;font-weight:bold;border:1px solid #000;background-color:#f8d7da;">{{ number_format($creditos->sum('total_cuota_mora'), 2) }}</td>
        </tr>
    </table>
</body>
</html>
