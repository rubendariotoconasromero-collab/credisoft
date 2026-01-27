
<?php
use Carbon\Carbon;
use Luecano\NumeroALetras\NumeroALetras;

$url = empty(DB::table('mi_empresa')->get()[0]->logo)?'logo_sistema_codesoft.png': DB::table('mi_empresa')->get()[0]->logo;
$image = file_get_contents('img/'.$url);

$html = '<img src="data:image/png;base64,' . base64_encode($image) . '" height="90px">';

function evaluandoEstado($estado){
    if($estado==1){
        return 'En proceso';
    }else{
        if($estado==0){
            return 'Anulado';
        }else{
            if($estado==2){
                return 'Cancelado';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de pago</title>
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    
    <style>
        /* Estilos CSS para el informe */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px;
        }
        .encabezado {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .informe {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .seccion {
            margin-bottom: 20px;
        }
        .titulo-seccion {
            font-weight: bold;
            font-size: 18px;
            text-align: center;
            padding-top: 5px;
            padding-bottom: 5px;
            background-color: #ddd;
        }
        .dato {
            margin-top: 10px;
            margin-left: 0px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        tr:nth-child(even) {
            /* background-color: #f2f2f2; */
        }

        th {
            background-color: #ddd;
            font-weight: bold;
        }

      

        .company-info {
            margin-left: 20px;
        }

        .company-info p {
            margin: 5px 0;
        }

        .company-info p:first-child {
            font-size: 24px;
            font-weight: bold;
        }

        .company-container {
        display: grid;
        grid-template-columns: 50% 50%;
        }

        .company-container > div {
        width: 100%;
        }

        table {
        border-collapse: collapse;
        width: 100%;
        }

        table, th, td {
            border: none;
        }

        th, td {
            padding: 5px; /* Ajusta el espaciado interno de las celdas según tus preferencias */
        }

        

    </style>
</head>
<?php
    $count=0;
?>
  


<body>
    <table style="border:none">
        <tr>
            <td style="border:none">
                {{-- <img src="/img/" alt="Logo de la Empresa" width="100"> --}}
        
                <?php echo $html?>
             
            </td>
            <td style="text-align:end; border:none">
                <p style="text-align:right; margin:0; text-transform: uppercase;"><strong>{{empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa': DB::table('mi_empresa')->get()[0]->nombre}}</strong></p>
                <p style="text-align:right; margin:0"><strong>Dirección: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->direccion)?'Dirección de la empresa':DB::table('mi_empresa')->get()[0]->direccion}}</p>
                <p style="text-align:right; margin:0"><strong>Teléfono: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->telefono)?'Telefono de la empresa': DB::table('mi_empresa')->get()[0]->telefono}}</p>
                <p style="text-align:right; margin:0"><strong>Correo Electrónico: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->email)?'Correo de la empresa': DB::table('mi_empresa')->get()[0]->email}}</p>
            </td>
        </tr>
    </table>
    <br>
    {{-- $clientes[0]->fecha_nacimiento --}}
    <div class="informe">
        <div class="seccion">
            <table>
                <tr style="text-align: center; margin-bottom:0">
                    <h2 style="margin-bottom:0">
                        <strong>
                            LISTADO DE PAGOS - AMORTIZACIONES
                        </strong>
                    </h2>
                    
                </tr>
             
               
            </table>
            <table>
                <tr>
                    <td colspan="1">
                        <table>
                            <tr>
                              <td style="width:130px;padding-left:0"><strong>CODIGO CAJA:</strong></td>
                              <td>{{$codigo_caja}}</td>
                            </tr>
                            <tr>
                              <td style="width:130px;padding-left:0"><strong>TOTAL Bs.:</strong></td>
                              <td>{{$total_pagos}}</td>
                            </tr>
                          </table>
                    </td>
                </tr>
                
            </table>
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <thead>
                    <tr>
                        <th style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd; background-color: #f2f2f2;">#</th>
                        <th style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd; background-color: #f2f2f2;">Codigo</th>
                        <th style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd; background-color: #f2f2f2;">Cliente</th>
                        <th style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd; background-color: #f2f2f2;">Asesor</th>
                        <th style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd; background-color: #f2f2f2;">Total crédito</th>
                        <th style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd; background-color: #f2f2f2;">Fecha de pago</th>
                        <th style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd; background-color: #f2f2f2;">Monto de pago</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lista_pagos as $detalle)
                    <tr>
                        <td style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $count++ }}</td>
                        <td style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $detalle['id'] }}</td>
                        <td style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $detalle['cliente'] }}</td>
                        <td style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $detalle['asesor'] }}</td>
                        <td style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $detalle['total_pago_credito'] }}</td>
                        <td style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $detalle['fecha_pago'] }}</td>
                        <td style="font-size:12px;padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $detalle['monto_pago'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
          
          
            
        </div>
   
        
    </div>
</body>
</html>
