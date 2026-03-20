<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    <table>
        <tr>
            <td colspan="7" style="text-align: center; font-size: 16px; font-weight: bold;">
                {{ strtoupper(empty($empresa->nombre) ? 'Empresa' : $empresa->nombre) }}
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center; font-size: 14px; font-weight: bold;">
                LIBRO MAYOR DE CAJA
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center; font-size: 12px;">
                Rango: {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_final)->format('d/m/Y') }}
            </td>
        </tr>
        <tr><td colspan="7"></td></tr>
        
        <tr>
            <th style="background-color: #212529; color: #ffffff; font-weight: bold; border: 1px solid #000; width: 50px;">N°</th>
            <th style="background-color: #212529; color: #ffffff; font-weight: bold; border: 1px solid #000; width: 100px;">Fecha</th>
            <th style="background-color: #212529; color: #ffffff; font-weight: bold; border: 1px solid #000; width: 120px;">Tipo</th>
            <th style="background-color: #212529; color: #ffffff; font-weight: bold; border: 1px solid #000; width: 350px;">Descripción</th>
            <th style="background-color: #198754; color: #ffffff; font-weight: bold; border: 1px solid #000; width: 100px;">Ingreso (Debe)</th>
            <th style="background-color: #dc3545; color: #ffffff; font-weight: bold; border: 1px solid #000; width: 100px;">Egreso (Haber)</th>
            <th style="background-color: #212529; color: #ffffff; font-weight: bold; border: 1px solid #000; width: 120px;">Saldo</th>
        </tr>

        @foreach($movimientos as $mov)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $mov->nro }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $mov->fecha }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $mov->tipo }}</td>
            <td style="border: 1px solid #000; text-transform:uppercase;">{{ $mov->descripcion }}</td>
            
            <td style="border: 1px solid #000; text-align: right; color: #198754;">
                {{ $mov->debe > 0 ? number_format($mov->debe, 2) : '' }}
            </td>
            <td style="border: 1px solid #000; text-align: right; color: #dc3545;">
                {{ $mov->haber > 0 ? number_format($mov->haber, 2) : '' }}
            </td>
            
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">
                {{ number_format($mov->saldo, 2) }}
            </td>
        </tr>
        @endforeach

        <tr>
            <td colspan="4" style="text-align: right; font-weight: bold; border: 1px solid #000; background-color: #e9ecef;">
                TOTAL DEL PERIODO:
            </td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000; background-color: #e9ecef;">
                {{ number_format($total_ingresos, 2) }}
            </td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000; background-color: #e9ecef;">
                {{ number_format($total_egresos, 2) }}
            </td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000; background-color: #e9ecef;">
                -
            </td>
        </tr>
    </table>
</body>
</html>