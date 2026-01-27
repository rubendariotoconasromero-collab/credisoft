@php 
$url = empty(DB::table('mi_empresa')->get()[0]->logo) ? 'logo_sistema_codesoft.png' : DB::table('mi_empresa')->get()[0]->logo;
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Desembolsos Pendientes</title>
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
                    <img style="height: 60px; margin-bottom: 10px;" src="data:image/png;base64,{{ base64_encode(file_get_contents('img/'.$url)) }}" alt="Logo">
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
                        <h1 style="font-size: 20px; margin: 0; padding: 10px 0; text-align: center; color: rgb(0, 0, 0);">REPORTE DE DESEMBOLSOS PENDIENTES</h1>
                    </div>
                </td>
            </tr>
        </table>
        <p><strong>Período del reporte:</strong> Desde {{ $fecha_inicio }} hasta {{ $fecha_fin }} </p>
        <p><strong>Total de desembolsos:</strong> {{ number_format($total_desembolso, 2) }} Bs</p>

        <div class="">
            <table style="width:100%;" class="data-table">
                <thead>
                    <tr>
                        <th style="padding: 3px; font-size: 10px;">Crédito</th>
                        <th style="padding: 3px; font-size: 10px;">Fecha Des.</th>
                        <th style="padding: 3px; font-size: 10px;">Cliente</th>
                        <th style="padding: 3px; font-size: 10px;">CI</th>
                        <th style="padding: 3px; font-size: 10px;">Garantía</th>
                        <th style="padding: 3px; font-size: 10px;">Forma Pago</th>
                        <th style="padding: 3px; font-size: 10px;">Cuotas</th>
                        <th style="padding: 3px; font-size: 10px;">Gasto Adm</th>
                        <th style="padding: 3px; font-size: 10px;">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registros as $registro)
                        <tr>
                            <td>{{ $registro->id_plan_pago }}</td>
                            <td>{{ $registro->fecha_desembolso }}</td>
                            <td>{{ $registro->cliente }}</td>
                            <td>{{ $registro->ci }}</td>
                            <td>{{ $registro->tipo_garantia }}</td>
                            <td>{{ $registro->lapso_capital }}</td>
                            <td>{{ $registro->nro_cuotas }}</td>
                            <td>{{ number_format($registro->monto_pago_adm, 2) }} Bs</td>
                            <td>{{ number_format($registro->monto, 2) }} Bs</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
