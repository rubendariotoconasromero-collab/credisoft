<?php
use Carbon\Carbon;
use Luecano\NumeroALetras\NumeroALetras;

$url = empty($empresa->logo) ? 'logo_sistema_codesoft.png' : $empresa->logo;
$path = public_path('img/'.$url);
$html_logo = '';

if (file_exists($path)) {
    $image = file_get_contents($path);
    $html_logo = '<img src="data:image/png;base64,' . base64_encode($image) . '" height="50px">';
}

$formatter = new NumeroALetras();
$monto_pago_literal = $formatter->toInvoice($total_pagado, 2, 'Bolivianos');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .ticket-container {
            width: 100%;
            max-width: 400px; /* Ancho simulado de ticketera grande o media hoja */
            margin: 0 auto;
            padding: 20px;
            border: 1px dashed #ddd; /* Quitar border si imprimes en ticketera real */
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 10px 0 5px 0;
            font-size: 18px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 2px 0;
            font-size: 11px;
            color: #555;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 15px 0;
        }
        .info-row {
            width: 100%;
            margin-bottom: 5px;
            font-size: 12px;
        }
        .info-row td {
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 35%;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }
        .table-items th {
            border-bottom: 1px solid #000;
            border-top: 1px solid #000;
            padding: 5px 0;
            text-align: right;
        }
        .table-items th.left { text-align: left; }
        .table-items td {
            padding: 5px 0;
            text-align: right;
        }
        .table-items td.left { text-align: left; }
        .total-row {
            font-size: 16px;
            font-weight: bold;
        }
        .literal {
            font-size: 11px;
            font-style: italic;
            text-align: center;
            margin-top: 10px;
            padding: 5px;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
        }
        .firmas {
            margin-top: 50px;
            width: 100%;
            text-align: center;
            font-size: 11px;
        }
        .firmas td { width: 50%; }
    </style>
</head>
<body>

    <div class="ticket-container">
        
        <div class="header">
            {!! $html_logo !!}
            <h2>RECIBO DE PAGO</h2>
            <p><strong>{{ strtoupper(empty($empresa->nombre) ? 'EMPRESA DEMO' : $empresa->nombre) }}</strong></p>
            <p>{{ empty($empresa->direccion) ? '' : $empresa->direccion }}</p>
            <p>Telf: {{ empty($empresa->telefono) ? '' : $empresa->telefono }}</p>
        </div>

        <div class="divider"></div>

        <table class="info-row">
            <tr>
                <td class="info-label">Transacción:</td>
                <td>{{ $codigo_transaccion }}</td>
            </tr>
            <tr>
                <td class="info-label">Fecha / Hora:</td>
                <td>{{ Carbon::parse($info_base->fecha_pago)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="info-label">Cliente:</td>
                <td>{{ strtoupper($info_base->cliente_nombre) }}</td>
            </tr>
            <tr>
                <td class="info-label">CI:</td>
                <td>{{ $info_base->ci }}</td>
            </tr>
            <tr>
                <td class="info-label">Cajero:</td>
                <td>{{ $info_base->cajero }}</td>
            </tr>
            <tr>
                <td class="info-label">Método Pago:</td>
                <td>{{ strtoupper($info_base->forma_pago) }}</td>
            </tr>
            <tr>
                <td class="info-label">N° Crédito:</td>
                <td><strong>{{ $info_base->codigo_plan }}</strong> (Abono a Cuotas: {{ $cuotas_texto }})</td>
            </tr>
        </table>

        <table class="table-items">
            <thead>
                <tr>
                    <th class="left">Detalle</th>
                    <th>Subtotal (Bs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $p)
                <tr>
                    <td class="left">
                        <strong>Cuota {{ $p->nro_cuota }}</strong><br>
                        <span style="font-size: 9px; color:#555;">
                            Cap: {{ number_format($p->pago_capital, 2) }} | 
                            Int: {{ number_format($p->pago_interes, 2) }}
                            @if($p->pago_mora > 0) | Mora: {{ number_format($p->pago_mora, 2) }} @endif
                        </span>
                    </td>
                    <td>{{ number_format($p->monto_pago, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table style="width: 100%; text-align: right; font-size: 13px;">
            @if($total_condonado > 0)
            <tr>
                <td>Descuentos Aplicados:</td>
                <td>- {{ number_format($total_condonado, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td style="padding-top: 5px;">TOTAL COBRADO:</td>
                <td style="padding-top: 5px;">Bs. {{ number_format($total_pagado, 2) }}</td>
            </tr>
        </table>

        <div class="literal">
            Son: {{ strtoupper($monto_pago_literal) }}
        </div>

        <table class="firmas">
            <tr>
                <td>_______________________<br>Firma Cliente</td>
                <td>_______________________<br>Firma Cajero</td>
            </tr>
        </table>

        <div class="footer">
            <p>¡Gracias por su pago y su puntualidad!</p>
            <p style="color:#888;">Impreso el {{ Carbon::now()->format('d/m/Y H:i') }}</p>
        </div>

    </div>

</body>
</html>