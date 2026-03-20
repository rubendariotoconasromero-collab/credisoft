<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
<body>
    <table>
        <tr><td colspan="5" style="text-align: center; font-size: 16px; font-weight: bold;">{{ strtoupper($empresa->nombre ?? 'EMPRESA') }}</td></tr>
        <tr><td colspan="5" style="text-align: center; font-size: 14px; font-weight: bold;">REPORTE DE EGRESOS</td></tr>
        <tr><td colspan="5" style="text-align: center; font-size: 12px;">Rango: {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/Y') }}</td></tr>
        <tr><td colspan="5"></td></tr>
        
        <tr>
            <th style="background-color: #dc3545; color: #fff; font-weight: bold; border: 1px solid #000; width: 50px;">N°</th>
            <th style="background-color: #dc3545; color: #fff; font-weight: bold; border: 1px solid #000; width: 100px;">Fecha</th>
            <th style="background-color: #dc3545; color: #fff; font-weight: bold; border: 1px solid #000; width: 150px;">Tipo</th>
            <th style="background-color: #dc3545; color: #fff; font-weight: bold; border: 1px solid #000; width: 350px;">Descripción</th>
            <th style="background-color: #dc3545; color: #fff; font-weight: bold; border: 1px solid #000; width: 150px;">Monto Ingresado (Bs)</th>
        </tr>

        @foreach($movimientos as $mov)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $mov->nro }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $mov->fecha }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $mov->tipo }}</td>
            <td style="border: 1px solid #000; text-transform: uppercase !important;">{{ $mov->descripcion }}</td>
            <td style="border: 1px solid #000; text-align: right; color: #dc3545; font-weight:bold;">{{ number_format($mov->monto, 2) }}</td>
        </tr>
        @endforeach

        <tr>
            <td colspan="4" style="text-align: right; font-weight: bold; border: 1px solid #000; background-color: #e9ecef;">TOTAL EGRESOS:</td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000; background-color: #e9ecef;">{{ number_format($total, 2) }}</td>
        </tr>
    </table>
</body>
</html>