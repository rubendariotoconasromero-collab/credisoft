
<?php
use Carbon\Carbon;
use Luecano\NumeroALetras\NumeroALetras;

$url = empty(DB::table('mi_empresa')->get()[0]->logo)?'logo_sistema_codesoft.png': DB::table('mi_empresa')->get()[0]->logo;
$image = file_get_contents('img/'.$url);

$html = '<img src="data:image/png;base64,' . base64_encode($image) . '" height="30px">';

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
function evaluandoEstadoCuota($estado){
    if($estado==1){
        return 'Sin pagar';
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
        padding: 20px; /* Espaciado general */
        padding-top:0px;
    }

    .informe {
        font-size: 14px;
        margin-bottom: 10px;
        width: 50%; /* Ancho del informe */
        margin: 0 auto; /* Centra el informe en la página */
    }

    .titulo-seccion {
        font-weight: bold;
        font-size: 16px;
        text-align: left;
        padding-top: 5px;
        padding-bottom: 5px;
        background-color: #ddd;
    }

    .company-container {
        display: grid;
        grid-template-columns: 50% 50%;
    }

    .company-container>div {
        width: 100%;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    table,
    th,
    td {
        border: none;
    }

    th,
    td {
        padding: 3px;
    }

    /* Nuevo estilo para que el contenido inicie desde la izquierda */
    .informe {
        margin: 0; /* Elimina el margen para que inicie desde la izquierda */
    }

    </style>
</head>
<?php
    $date = Carbon::now();
    $monto_pago= (int )$informacion[0]->monto_pago;
    //dd($monto_pago);
    $formatter = new NumeroALetras();
    $monto_pago_literal= $formatter->toInvoice($monto_pago, 2, 'bs');
    //$monto_pago_literal = NumeroALetras::toLetters($monto_pago);


  

?>
  


<body>
    {{-- <table style="border:none">
        <tr>
            <td style="border:none" style="display: flex; align-items:center">
             
        
           
             
            </td>
            <td style="text-align:end; border:none">
                <p style="text-align:right; margin:0; text-transform: uppercase;"><strong>{{empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa': DB::table('mi_empresa')->get()[0]->nombre}}</strong></p>
                <p style="text-align:right; margin:0"><strong>Dirección: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->direccion)?'Dirección de la empresa':DB::table('mi_empresa')->get()[0]->direccion}}</p>
                <p style="text-align:right; margin:0"><strong>Teléfono: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->telefono)?'Telefono de la empresa': DB::table('mi_empresa')->get()[0]->telefono}}</p>
                <p style="text-align:right; margin:0"><strong>Correo Electrónico: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->email)?'Correo de la empresa': DB::table('mi_empresa')->get()[0]->email}}</p>
            </td>
        </tr>
    </table> --}}
    <br>
    {{-- $clientes[0]->fecha_nacimiento --}}
    <div class="informe">
        <div class="seccion">
            <table>
                <tr style="text-align: center; margin-bottom:0">
                    <?php echo $html?>
                        <p style="font-size:12px; text-align:center; margin:0; text-transform: uppercase; margin-top:10px;"><strong>{{empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa': DB::table('mi_empresa')->get()[0]->nombre}}</strong></p>
                        <p style="font-size:12px; text-align:center; margin:0"><strong>Dirección: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->direccion)?'Dirección de la empresa':DB::table('mi_empresa')->get()[0]->direccion}}</p>
                        <p style="font-size:12px; text-align:center; margin:0"><strong>Teléfono: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->telefono)?'Telefono de la empresa': DB::table('mi_empresa')->get()[0]->telefono}}</p>
                        <p style="font-size:12px; text-align:center; margin:0"><strong>Correo Electrónico: </strong>  {{empty(DB::table('mi_empresa')->get()[0]->email)?'Correo de la empresa': DB::table('mi_empresa')->get()[0]->email}}</p>
                    <h2 style="margin-bottom:0; font-size:15px;">
                        <strong>
                            BOLETA DE PAGO
                        </strong>
                    </h2>
                    
                </tr>
                {{-- <tr style="text-align: center; margin-bottom:0">
                    <h3 style="font-size:13px; margin-top:0; margin-bottom:0">
                        Creditos
                    </h3>
                </tr> --}}
                <tr style="text-align: center; margin-bottom:0; margin-top:0px">
                    <h3>
                        Bs. {{ number_format($informacion[0]->monto_pago, 2, '.', ',')}}
                    </h3>
                </tr>
            </table>
            <table style="font-size:12px">
                <tr>
                    <td colspan="1">
                        <strong>
                            Fecha pago:
                        </strong>
                        {{-- {{$date}} --}}
                        {{$informacion[0]->fecha_pago}}
                    </td>
                </tr>
            </table>
            <table style="font-size:12px">
                <tr>
                    <td>
                        <i>
                            Recibi de: <strong>{{strtoupper($informacion[0]->nombre_cliente)}} </strong>
                        </i>
                    </td>
                </tr>
            </table>
            <hr>
            <table style="font-size:12px">
                <tr>
                    <td>
                        <i>
                            la suma de : <strong>{{strtoupper($monto_pago_literal)}} </strong>
                        </i>
                    </td>
                </tr>
            </table>
            
            <hr>
            <table style="font-size:12px">
                <tr>
                    <td>
                        <i>
                            Por concepto de : 
                        </i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i>
                            Pago de cuota N° 
                            <strong>{{$informacion[0]->nro_cuota}}</strong>
                            de
                            <strong>{{$informacion[0]->cantidad_cuotas}}</strong>

                        </i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i>
                            Codigo de plan de pago:
                            <strong>{{$informacion[0]->codigo_plan}}</strong>
                        

                        </i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i>
                            Forma de pago:
                            <strong>{{$informacion[0]->forma_pago}}</strong>
                        

                        </i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i>
                            Saldo capital:
                            <strong>{{$informacion[0]->saldo_capital}}</strong>
                        

                        </i>
                    </td>
                </tr>
                <tr>
                    <td>
                        <i>
                            Multa:
                            <strong>{{empty($informacion[0]->multa)?0:$informacion[0]->multa}}</strong>
                        

                        </i>
                    </td>
                </tr>
            </table>
            <hr>

            <br><br>

            <table style="font-size:12px">
                <tr>
                    <td style="text_align:center">
                        ______________________
                    </td>
                    <td>
                        
                    </td>
                    <td style="text_align:center">
                        ______________________
                    </td>
                </tr>
                <tr>
                    <td style="text_align:center">
                        CLIENTE
                    </td>
                    <td>
                        
                    </td>
                    <td style="text_align:center">
                        CAJERO
                    </td>
                </tr>
            </table>
            
        </div>
   
        
    </div>
</body>
</html>
