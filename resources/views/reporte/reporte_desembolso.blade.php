@php 
$url = empty(DB::table('mi_empresa')->get()[0]->logo) ? 'logo_sistema_codesoft.png' : DB::table('mi_empresa')->get()[0]->logo;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Desembolso</title>
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
            height: 10px;
            width:auto;
        }
        .header-table h1 {
            font-size: 24px;
            margin: 0;
            color: #4CAF50;
        }
        .report-info { 
            margin-top: 10;
            /* border-top: 2px solid #4CAF50; */
            /* border-bottom: 2px solid #4CAF50; */
            
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
            padding-top:2px;
            padding-bottom:2px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .total { 
            margin-top: 20px; 
            text-align: right;
            font-weight: bold;
            /* border-top: 2px solid #4CAF50; */
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
                    <img style="height:60px; margin-bottom:10px;" src="data:image/png;base64,{{ base64_encode(file_get_contents('img/'.$url)) }}" alt="Logo">
                    {{-- <p style="text-align:center; margin:0; text-transform: uppercase;"><strong>{{empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa': DB::table('mi_empresa')->get()[0]->nombre}}</strong></p> --}}
                    <div style="width:30px;">
                        <p style="font-size:11px; text-align:center; margin:0"><strong>Dirección: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->direccion)?'Dirección de la empresa':DB::table('mi_empresa')->get()[0]->direccion}}</p>
                        <p style="font-size:11px; text-align:center; margin:0"><strong>Teléfono: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->telefono)?'Telefono de la empresa': DB::table('mi_empresa')->get()[0]->telefono}}</p>
                        <p style="font-size:11px; text-align:center; margin:0"><strong>Correo Electrónico: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->email)?'Correo de la empresa': DB::table('mi_empresa')->get()[0]->email}}</p>
                    </div>
                </td>
                
                <td class="header-right">
                    <p style="font-size:12px;"><strong>Usuario:</strong> {{ $usuario }}</p>
                    <p style="font-size:12px;"><strong>Fecha de emisión:</strong> {{ $fecha_reporte }}</p>
                </td>
            </tr>
        </table>

        <table style="width:100%;">
            <tr>
                <td style="padding: 0; text-align: center; vertical-align: middle;">
                    <div style="width: 100%; height: 100%; justify-content: center; align-items: center;">
                        <h1 style="font-size: 20px; margin: 0; padding: 10px 0; text-align: center; color: rgb(0, 0, 0);">REPORTE DE DESEMBOLSO</h1>
                    </div>
                </td>
            </tr>
            
        </table>

        <div class="report-info">
            
            <p><strong>Período del reporte:</strong> Desde: {{ $fecha_inicio }} &nbsp;&nbsp;&nbsp; Hasta: {{ $fecha_final }}</p>

        </div>

        <table style="width:50%;">
            <tr style="padding-top:5px; padding-bottom:10px;">
                <td class="header-left" style="vertical-align: middle; background-color:#4CAF50; color:white">
                    <p>
                        {{-- <strong> --}}
                            Total Desembolsos
                        {{-- </strong> --}}
                    </p>
                </td>
                <td class="header-left" style="vertical-align: middle">
                    <p>
                        {{ number_format($totalDesembolsos, 2, ',', '.') }}
                    </p>
                </td>
            </tr>
            <tr style="padding-top:5px; padding-bottom:10px;">
                <td class="header-left" style="vertical-align: middle;background-color:#4CAF50; color:white">
                    <p>
                        {{-- <strong> --}}
                            Total Pagos Adm.
                        {{-- </strong> --}}
                    </p>
                </td>
                <td class="header-left" style="vertical-align: middle">
                    <p>
                        {{ number_format($totalPagosAdm, 2, ',', '.') }}
                    </p>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Cod. Crédito</th>
                    <th>Fecha</th>
                    <th>Cod. Cliente</th>
                    <th>Cliente</th>
                    <th>Garantía</th>
                    <th>Forma pago</th>
                    <th>Monto Des.</th>
                    <th>Pago Adm.</th>
                    <th>Estado.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($desembolsos as $desembolso)
                <tr>
                    <td>{{ $desembolso->id_plan_pago }}</td>
                    <td>{{ date('d/m/Y', strtotime($desembolso->fecha)) }}</td>
                    <td>{{ $desembolso->codcli }}</td>
                    <td>{{ $desembolso->cliente }}</td>
                    <td>{{ $desembolso->garantia }}</td>
                    <td>{{ $desembolso->lapso_capital }}</td>
                    <td>{{ number_format($desembolso->monto, 2, ',', '.') }}</td>
                    <td>{{ number_format($desembolso->monto_pago_adm, 2, ',', '.') }}</td>
                    <td>{{ $desembolso->estado==0?'Cancelado':'Anulado' }}</td>

                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- <div class="total">
            <p>Total Desembolsos: Bs. {{ number_format($totalDesembolsos, 2, ',', '.') }}</p>
            <p>Total Pagos Administrativos: Bs. {{ number_format($totalPagosAdm, 2, ',', '.') }}</p>
        </div> --}}
    </div>
</body>
</html>