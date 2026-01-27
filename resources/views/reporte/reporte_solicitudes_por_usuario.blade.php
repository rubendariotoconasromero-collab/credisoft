<?php
use Intervention\Image\Facades\Image;
$url = empty(DB::table('mi_empresa')->get()[0]->logo)?'logo_sistema_codesoft.png': DB::table('mi_empresa')->get()[0]->logo;
$image = file_get_contents('img/'.$url);

// Cargar la imagen original
// $imagen = Image::make(public_path('img/cliente/'.$clientes[0]->imagen));
// $imagen->orientate();
// $imagen_cliente = $imagen->encode('data-url')->encoded;
// $html_imagen_cliente = '<img src="data:image/png;base64,' . $imagen_cliente . '" height="300px" width="200px">';

$html = '<img src="data:image/png;base64,' . base64_encode($image) . '" height="100px">';
// $imagePath = 'img/cliente/' . $clientes[0]->imagen;

// // Obtener información de orientación de la imagen
// try {
//     $exif = exif_read_data($imagePath);
    
//     // Verificar la orientación y rotar la imagen si es necesario
//     if (!empty($exif['Orientation'])) {
//         $image = imagecreatefromjpeg($imagePath);
    
//         switch ($exif['Orientation']) {
//             case 3:
//                 $image = imagerotate($image, 180, 0);
//                 break;
//             case 6:
//                 $image = imagerotate($image, -90, 0);
//                 break;
//             case 8:
//                 $image = imagerotate($image, 90, 0);
//                 break;
//         }
    
//         // Guardar la imagen con la orientación corregida
//         imagejpeg($image, $imagePath);
//     }
// } catch (\Exception $e) {
//     // Captura cualquier excepción que se produzca al intentar leer datos EXIF
//     // Puedes manejar la excepción sin devolver un mensaje
//     // O realizar otras acciones aquí
// }
    

// $imagen_cliente = file_get_contents('img/cliente/'.(empty($clientes[0]->imagen)?'default.png':$clientes[0]->imagen));
// $html_imagen_cliente = '<img src="data:image/png;base64,' . base64_encode($imagen_cliente) . '" height="200px">';
function retornarEstado($estado){
    if($estado==1){
        return "En espera";
    }else{
        if($estado==0){
            return "Anulado";
        }else{
            if($estado==2){
                return "Aprobado";
            }else{
                return -1;
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
    <title>Reporte general de clientes</title>
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
            border-collapse: collapse;
            width: 100%;
            font-size: 10px;
            /* Tamaño de letra pequeño para el contenido de la tabla */
        }

        th,
        td {
            border: 1px solid rgb(73, 73, 73);
            padding: 8px;
            text-align: left;
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

        .company-container>div {
            width: 100%;
        }

        .div-container {
            display: flex;
            justify-content: center;
            /* Centra horizontalmente */
            align-items: center;
            /* Centra verticalmente */
            width: 300px;
            /* Ancho deseado del div */
            height: 200px;
            /* Alto deseado del div */
        }

        .div-container img {
            max-width: 100%;
            max-height: 100%;
        }

        .center-img {
            display: flex;
            justify-content: center;
            /* Centra horizontalmente */
            align-items: center;
            /* Centra verticalmente */
        }

        /* Estilos para la imagen (opcional) */
        tr.center-img img {
            max-width: 100%;
            max-height: 100%;
        }

    </style>
</head>

<body>
    <table style="border:none">
        <tr>
            <td style="border:none">
                {{-- <img src="/img/" alt="Logo de la Empresa" width="100"> --}}

                <?php echo $html?>

            </td>
            <td style="text-align:end; border:none">
                <p style="text-align:right; margin:0; text-transform: uppercase;">
                    <strong>{{empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa':DB::table('mi_empresa')->get()[0]->nombre}}</strong>
                </p>
                <p style="text-align:right; margin:0"><strong>Dirección: </strong>
                    {{empty(DB::table('mi_empresa')->get()[0]->direccion)?'Direccion de la empresa': DB::table('mi_empresa')->get()[0]->direccion }}
                </p>
                <p style="text-align:right; margin:0"><strong>Teléfono: </strong>
                    {{empty(DB::table('mi_empresa')->get()[0]->telefono)?'Teléfono de la empresa': DB::table('mi_empresa')->get()[0]->telefono}}
                </p>
                <p style="text-align:right; margin:0"><strong>Correo Electrónico: </strong>
                    {{empty(DB::table('mi_empresa')->get()[0]->email)?'Correo de la empresa': DB::table('mi_empresa')->get()[0]->email}}
                </p>
            </td>
        </tr>
    </table>
    <br>
    <div>

        <div class="seccion">
            <h4 class="card-title text-uppercase mt-3">Listado de solicitudes</h4>
            <p><strong>Usuario: </strong>{{$usuario[0]->personal}}</p>
            <div class="table-responsive">
                @if(!$solicitudes->isEmpty())
                <table class="table mb-4 table-bordered table-striped table-hover">
                    <thead class="text-uppercase bg-primary text-white">
                        <tr>
                            <th>Nro</th>
                            <th>Cliente</th>
                            <th>Asesor</th>
                            <th>Importe</th>
                            <th>Nro_cuotas</th>
                            <th>Tasa</th>
                            <th>Fecha_desembolso</th>
                            <th>Tipo garantia</th>
                            <th>Estado</th>
                     
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($solicitudes as $solicitud)
                        <tr class="">

                            <td>{{  $solicitud->id}}</td>
                            <td>{{ $solicitud->cliente }}</td>
                            <td>{{ $solicitud->asesor }}</td>
                            <td>{{ $solicitud->importe_solicitud }}</td>
                            <td>{{ $solicitud->nro_cuotas }}</td>
                            <td>{{ $solicitud->tasa }}</td>
                            <td>{{ $solicitud->fecha_desembolso }}</td>
                            <td>{{ $solicitud->tipo_garantia }}</td>


                            <td>
                                {{retornarEstado($solicitud->estado)}}
                            </td>
                           

                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else   
                    <p>Aun no se tienen registros</p>
                @endif
                

            </div>

        </div>


    </div>
</body>

</html>
