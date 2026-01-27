<?php
use Intervention\Image\Facades\Image;
$url = empty(DB::table('mi_empresa')->get()[0]->logo) ? 'logo_sistema_codesoft.png' : DB::table('mi_empresa')->get()[0]->logo;
$image = file_get_contents('img/'.$url);

$html = '<img src="data:image/png;base64,' . base64_encode($image) . '" height="50px">';

function retornarEstado($estado) {
    if ($estado == 1) {
        return "Nuevo";
    } else if ($estado == 0) {
        return "Anulado";
    } else if ($estado == 2) {
        return "Aprobado";
    } else {
        return -1;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte general de solicitudes</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            padding: 10px;
            vertical-align: middle;
        }
        .header-left {
            width: 25%;
        }
        .header-center {
            width: 50%;
            text-align: center;
        }
        .header-right {
            width: 25%;
            text-align: right;
        }
        .header-table img {
            height: 60px;
            width: auto;
        }
        .header-table h1 {
            font-size: 24px;
            margin: 0;
            color: #4CAF50;
        }
        .report-info { 
            margin-top: 10px;
        }
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
            font-size: 11px;
        }
        .data-table, .data-table th, .data-table td { 
            border: 1px solid #ddd; 
        }
        .data-table th, .data-table td { 
            padding: 5px; 
            text-align: left; 
        }
        .data-table th { 
            background-color: #4CAF50; 
            color: white;
            padding-top: 2px;
            padding-bottom: 2px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .total { 
            margin-top: 20px; 
            text-align: right;
            font-weight: bold;
            padding-top: 10px;
        }
        .total p {
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="header-left" style="vertical-align: middle;">
                    {!! $html !!}
                    <div style="width: 100%;">
                        <p style="font-size: 11px; text-align: center; margin: 0"><strong>Dirección: </strong>{{ empty(DB::table('mi_empresa')->get()[0]->direccion) ? 'Dirección de la empresa' : DB::table('mi_empresa')->get()[0]->direccion }}</p>
                        <p style="font-size: 11px; text-align: center; margin: 0"><strong>Teléfono: </strong>{{ empty(DB::table('mi_empresa')->get()[0]->telefono) ? 'Teléfono de la empresa' : DB::table('mi_empresa')->get()[0]->telefono }}</p>
                        <p style="font-size: 11px; text-align: center; margin: 0"><strong>Correo Electrónico: </strong>{{ empty(DB::table('mi_empresa')->get()[0]->email) ? 'Correo de la empresa' : DB::table('mi_empresa')->get()[0]->email }}</p>
                    </div>
                </td>
                <td class="header-right">
                    <p style="font-size: 12px;"><strong>Usuario:</strong> {{ $usuario }}</p>
                    <p style="font-size: 12px;"><strong>Fecha de emisión:</strong> {{ $fecha_reporte }}</p>
                </td>
            </tr>
        </table>

        <table style="width: 100%;">
            <tr>
                <td style="padding: 0; text-align: center; vertical-align: middle;">
                    <div style="width: 100%; height: 100%; justify-content: center; align-items: center;">
                        <h1 style="font-size: 20px; margin: 0; padding: 10px 0; text-align: center; color: rgb(0, 0, 0);">LISTADO GENERAL DE SOLICITUDES</h1>
                    </div>
                </td>
            </tr>
        </table>

        <div class="report-info">
            <p><strong>Período del reporte:</strong> Desde: {{ $fecha_inicio }} &nbsp;&nbsp;&nbsp; Hasta: {{ $fecha_final }}</p>
            <p><strong>Estados seleccionados:</strong> 
                {{ empty($nuevo)?'':$nuevo.' - ' }}
                {{ empty($aprobado)?'':$aprobado.' - ' }}
                {{ empty($anulado)?'':$anulado.' - ' }}
            </p> <!-- Mostrar los estados seleccionados -->
        </div>

        <div class="report-info">
            @if(!$solicitudes->isEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nro</th>
                        <th>Cliente</th>
                        <th>Asesor</th>
                        <th>Importe</th>
                        <th>Nro cuotas</th>
                        <th>Tasa</th>
                        <th>Fecha desembolso</th>
                        <th>Tipo garantía</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitudes as $solicitud)
                    <tr>
                        <td>{{ $solicitud->id }}</td>
                        <td>{{ $solicitud->cliente }}</td>
                        <td>{{ $solicitud->asesor }}</td>
                        <td>{{ $solicitud->importe_solicitud }}</td>
                        <td>{{ $solicitud->nro_cuotas }}</td>
                        <td>{{ $solicitud->tasa }}</td>
                        <td>{{ $solicitud->fecha_desembolso }}</td>
                        <td>{{ $solicitud->tipo_garantia }}</td>
                        <td>{{ retornarEstado($solicitud->estado) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>Aún no se tienen registros</p>
            @endif
        </div>
    </div>
</body>
</html>
