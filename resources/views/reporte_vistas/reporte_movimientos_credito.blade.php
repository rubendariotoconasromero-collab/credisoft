@php 
$url = empty(DB::table('mi_empresa')->get()[0]->logo) ? 'logo_sistema_codesoft.png' : DB::table('mi_empresa')->get()[0]->logo;


function evaluandoEstado($estado){
    if($estado==1){
        return 'En proceso';
    }else if($estado==0){
        return 'Anulado';
    }else if($estado==2){
        return 'Cancelado';
    }
    else if($estado==10){
        return 'Amortizado';
    }
}

function evaluandoEstadoCuota($estado){
    if($estado==1){
        return 'Sin pagar';
    }else if($estado==0){
        return 'Anulado';
    }else if($estado==2){
        return 'Cancelado';
    }
}
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Extracto movimientos</title>
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
                    <p style="font-size: 12px;"><strong>Hora de emisión:</strong> {{ $hora_reporte }}</p>
                </td>
            </tr>
        </table>

        <table style="width: 100%;">
            <tr>
                <td style="padding: 0; text-align: center; vertical-align: middle;">
                    <div style="width: 100%; height: 100%; justify-content: center; align-items: center;">
                        <h1 style="font-size: 20px; margin: 0; padding: 10px 0; text-align: center; color: rgb(0, 0, 0);">EXTRACTO DE MOVIMIENTOS</h1>
                    </div>
                </td>
            </tr>
        </table>


        <div class="report-info">
            <table style="width:100%;">
                <tr>
                    <td><strong>Cliente:</strong></td>
                    <td>{{$informacion[0]->cliente}}</td>
                    
                    <td><strong>CI:</strong></td>
                    <td>{{$informacion[0]->ci.' '.$informacion[0]->lugar_expedicion}}</td>
                </tr>

                <tr style="margin-top:10px; margin-bottom:10px;">
                    <td style="border-bottom: 1px solid #000; padding-top:10px; padding-bottom:10px;" ><strong>Asesor:</strong></td>
                    <td style="border-bottom: 1px solid #000; padding-top:10px; padding-bottom:10px;" >{{$informacion[0]->personal}}</td>
                    <td style="border-bottom: 1px solid #000; padding-top:10px; padding-bottom:10px;"> </td>
                    <td style="border-bottom: 1px solid #000; padding-top:10px; padding-bottom:10px;"> </td>
                </tr>

                <tr style="padding-top:10px;">
                    <td style="padding-top:10px;"><strong>Nro. cuotas:</strong></td>
                    <td style="padding-top:10px;">{{$informacion[0]->nro_cuotas}}</td>
                    <td style="padding-top:10px;"><strong>Fecha desembolso:</strong></td>
                    <td style="padding-top:10px;">{{$informacion[0]->fecha_desembolso}}</td>
                </tr>
                <tr>
                    <td><strong>Plazo:</strong></td>
                    <td>{{$informacion[0]->nro_cuotas.' '.$informacion[0]->lapso_capital}}</td>
                    <td><strong>Estado:</strong></td>
                    <td>{{evaluandoEstado($informacion[0]->estado)}}</td>
                </tr>
                <tr>
                    <td><strong>Importe solicitud:</strong></td>
                    <td>{{$informacion[0]->importe_solicitud.' '.$informacion[0]->moneda}}</td>
                    <td><strong>Forma pago:</strong></td>
                    <td>{{$informacion[0]->lapso_capital}}</td>
                </tr>
                <tr>
                    <td><strong>Garantia:</strong></td>
                    <td>{{$informacion[0]->tipo_garantia}}</td>
                    {{-- <td><strong>Estado:</strong></td>
                    <td>{{$informacion[0]->lapso_capital}}</td> --}}
                </tr>
                
            </table>
        </div>

        {{-- session a modificar --}}
        <div class="">
            <table style="width:100%;" class="data-table">
                <thead>
                    <tr>
                        {{-- <th style="width: 5%;">Nro</th> --}}
                        <th style="width: 15%;">Fecha</th>
                        <th style="width: 15%;">Tipo</th>
                        <th style="width: 35%;">Descripción</th>
                        <th style="width: 15%;">Debe</th>
                        <th style="width: 15%;">Haber</th>
                    </tr>
                </thead>
                <tbody>
                    @php $contador = 1; @endphp
                    @foreach($movimientos as $movimiento)
                        <tr>
                            {{-- <td>{{ $contador }}</td> --}}
                            <td>{{ $movimiento->fecha }}</td>
                            <td>{{ $movimiento->tipo }}</td>
                            <td>{{ $movimiento->descripcion }}</td>
                            <td>{{ $movimiento->debe > 0 ? number_format($movimiento->debe, 2) : '-' }}</td>
                            <td>{{ $movimiento->haber > 0 ? number_format($movimiento->haber, 2) : '-' }}</td>
                        </tr>
                        @php $contador++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
        

        {{-- session a modificar --}}

       
       
        
    </div>
</body>
</html>
