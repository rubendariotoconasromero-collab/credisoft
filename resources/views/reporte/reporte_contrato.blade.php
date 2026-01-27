<?php
use Carbon\Carbon;
use Luecano\NumeroALetras\NumeroALetras;

$url = empty(DB::table('mi_empresa')->get()[0]->logo) ? 'logo_sistema_codesoft.png' : DB::table('mi_empresa')->get()[0]->logo;
$image = file_get_contents('img/' . $url);


$html = '<img src="data:image/png;base64,' . base64_encode($image) . '" height="50px">';


$monto_total_aux= (int )$monto_total;
//dd($monto_pago);
$formatter = new NumeroALetras();
$monto_pago_literal= $formatter->toInvoice($monto_total_aux, 2, 'bs');
// Información del contrato de préstamo
$contrato = [
    'titulo' => 'CONTRATO DE PRÉSTAMO DE DINERO',
    'partes' => [
        'acreedor' => [
            'nombre' => 'CREDIHOGAR S.R.L.',
            'ci' => '461446024',
            'rol' => 'ACREEDOR',
        ],
        'deudor' => [
            'nombre' => 'PEDRAZA GONZALES PORFIRIO',
            'ci' => '4701297',
            'rol' => 'DEUDOR',
        ],
        'codeudor' => [
            'nombre' => 'PEDRAZA ORTIZ LIDIA',
            'ci' => '7760636',
            'rol' => 'CODEUDOR',
        ],
    ],
    'contenido' => [
        'primera' => 'Conste por el presente documento privado, un contrato de PRESTAMO DE DINERO, que podrá ser elevado a la categoría de instrumento público con el solo reconocimiento de firmas, en el cual las partes convienen lo siguientes:',
        'primera_parte' => 'Forman parte integrante del presente contrato',
        'acreedor' => ' mayor de edad, hábil por ley, domiciliado en esta ciudad, quien en adelante 
        a los fines del presente contrato se denominará como “ACREEDOR(A)”.',
        'deudor' => ' mayor de edad hábiles por ley, domiciliado en esta ciudad, quien en adelante 
        a los fines del presente contrato se denominará como “DEUDOR(A)”.',
        'codeudor' => ' mayor de edad hábiles por ley, domiciliado en esta ciudad, quién en adelante 
        a los fines del presente contrato se denominará como “CODEUDOR(A)”.',
        'segunda_parte' => ' “EL ACREEDOR(A)” en la fecha por convenir a sus intereses y sin que 
        nadie vicio del consentimiento alguno, entrega en calidad de préstamo a favor del “DEUDOR(A)”, 
        la suma de Bs. 5800.0 (CINCO MIL OCHOCIENTOS 00/100 BOLIVIANOS), dinero que el “DEUDOR(A)” 
        declara haber recibido a su entera satisfacción y en moneda de curso legal y corriente.',
        'tercera_parte' => ' El plazo que otorga el “ACREEDOR(A)” al “DEUDOR(A)” para la cancelación de la suma del dinero señalado en la clausula SEGUNDA, 
        es por el plazo de 10 Meses calendario, computable a partir de la fecha jueves 04 de enero de 
        2024, asimismo, se establece que si el “DEUDOR(A)” no cancela la obligación contraída en la 
        fecha establecida se constituirá en mora, facultando al “ACREEDOR(A)” proceder a la ejecución 
        por la vía ejecutiva que corresponde, con imposición de intereses, daños y perjuicios.',
        'cuarta_parte' => ' “EL DEUDOR(A)” se garantiza con garantía personal con el fiel y estricto 
        cumplimientos de la presente obligación, por el cual se compromete en cancelar si hubiera 
        retraso en cuotas y con la generalidad de sus bienes, acciones y derechos, muebles e inmuebles, 
        presentes y futuro. Las garantías, sin reservas, restricciones ni limitaciones de ninguna clase, 
        quedan redactadas y grabadas a favor de CREDIHOGAR, hasta que se haga efectivo el pago total y 
        definitivo de toda obligación en caso de incumplimiento se tomaran sus bienes hasta completar 
        la deuda en especial lo que se esta dejando en su declaración patrimonial.',
        'quinta_parte' => ' La presente obligación devengará una tasa de interés que estará contemplado el monto a cancelar en el PLAN DE PAGOS, que serán pagados por PRESTATARIOS Y/O DEUDORES de conformidad con el plan de pagos. Intereses Penales. - Si la obligación no es pagada en la forma y plazo acordado, se aplicará una tasa de interés Penal de conformidad al Art. 2da del decreto supremo No 28166, de 17 de mayo del 2005.',
        'quinta_parte_a' => ' En caso de incumplimiento de la fecha de pago, por cada día de retraso se cobrará un cargo de Bs 3.0 por gastos de cobranza',
        'sexta_parte' => ' De la mora.- la falta de pago de la obligación, al vencimiento del plazo acordado en los planes de pago que son parte integrante del presente contrato, constituirá al DEUDOR en mora por el monto total de la obligación, quedando vencida, liquida y exigible, pudiendo en cualquier tiempo proceder ejecutivamente o por otra vía a su elección exclusiva inicial la correspondiente acción judicial, demandando el pago del monto total de la obligación y reportar o publicar a otras entidades financieras, comerciales o particulares los datos de la deuda incluyendo nombre del deudor, y garantía.',
        'septima_parte' => ' EL DEUDOR(A) autoriza expresamente a CREDIHOGAR S.R.L. A mantener 
        reportadas en central de riesgos al DEUDOR(A).',
        'octava_parte' => ' de las partes del Sr(a) PEDRAZA GONZALES PORFIRIO con C.I. 4701297 como
         DEUDOR(A) Sr.(a) PEDRAZA ORTIZ LIDIA con C.I. 7760636 como CODEUDOR(A), del ACREEDOR(A) 
         Sr.(a) CREDIHOGAR S.R.L. Aceptan el tenor de toda y cada una de las anteriores clausulas 
         y se comprometen a su fiel y estricto cumplimiento.',
    ],
    'fecha' => 'Jueves 04 de enero de 2024',
];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato de Préstamo</title>
    <!-- Bootstrap Css -->
    <link href="{{asset('/assets/css/bootstrap.min.css')}}" id="bootstrap-style" rel="stylesheet" type="text/css" />

    <style>
        /* Estilos CSS para el informe */
body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 40px;
    color: #000;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 10px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
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
    color: #000;
}

/* .company-container {
    display: grid;
    grid-template-columns: 50% 50%;
}

.company-container > div {
    width: 100%;
} */
.company-container {
    margin-top:60px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.bloque-firma {
    margin-top:40px;
    margin-left:180px;
    margin-right:180px;
    text-align: center;
    padding: 10px;
    line-height: 0.2; /* Ajusta el valor según sea necesario */

    /* border: 1px solid #ccc; */
}

hr{
    height: 0.1 px; /* Grosor de la línea */
    background-color: #070707; /* Color de la línea */
  
}


.informe {
    background-color: #fff;
    /* border: 1px solid #ddd; */
    /* padding: 20px; */
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    line-height: 1.3; /* Ajusta el valor según sea necesario */
    text-align:justify;
}

.encabezado {
    font-size: 24px;
    font-weight: bold;
    text-transform: uppercase;
    color: #000;
}

.titulo-seccion {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.informe p {
    margin-bottom: 10px;
    font-size: 12px;
}

/* Personalizar los estilos según sea necesario */

    </style>
</head>

<body>
    <table style="border:none">
        <tr>
            <td style="border:none">
                <?php echo $html ?>
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
    <div class="informe">
        <strong>
            <p class="encabezado text-center" style="text-align: center;"><?php echo $contrato['titulo']; ?></p>
        </strong>
        <div class="seccion">
            <p class="informe"><?= $contrato['contenido']['primera']; ?></p>
            
        </div>
        <!-- Contenido del contrato... -->
        <div class="seccion">
            
           
            <p class="informe">
                <strong>PRIMERA. - (PARTES INTERVINIENTES): </strong>
                <?= $contrato['contenido']['primera_parte']; ?>
            </p>
            
            <p class="informe">
                <strong style="text-transform:uppercase">1 {{empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa': DB::table('mi_empresa')->get()[0]->nombre}} S.R.L. con C.I. No 461446024 S.C.</strong>
                <?= $contrato['contenido']['acreedor']; ?>
            </p>
          
            <p class="informe">
                <strong style="text-transform: uppercase;">
                    2. {{$cliente}} con C.I. No {{$ci}}
                </strong>
                <?= $contrato['contenido']['deudor']; ?>
            </p>
         
            

            <p class="informe">
                <strong>
                    SEGUNDA. - (OBJETO):
                </strong>
                “EL ACREEDOR(A)” en la fecha por convenir a sus intereses y sin que 
                nadie vicio del consentimiento alguno, entrega en calidad de préstamo a favor del “DEUDOR(A)”, 
                la suma de Bs. {{$monto_total}} ({{$monto_pago_literal}}), dinero que el “DEUDOR(A)” 
                declara haber recibido a su entera satisfacción y en moneda de curso legal y corriente.
            </p>

            <p class="informe">
                <strong>
                    TERCERA.- (PLAZO):
                </strong>
                El plazo que otorga el “ACREEDOR(A)” al “DEUDOR(A)” para la cancelación de la suma del dinero señalado en la clausula SEGUNDA, 
                es por el plazo de {{$nro_cuotas}} {{$lapso_capital}} calendario, computable a partir de la fecha {{$fecha_inicio}}, asimismo, se establece que si el “DEUDOR(A)” no cancela la obligación contraída en la 
                fecha establecida se constituirá en mora, facultando al “ACREEDOR(A)” proceder a la ejecución 
                por la vía ejecutiva que corresponde, con imposición de intereses, daños y perjuicios.
            </p>

            <p class="informe">
                <strong>
                    CUARTA. - (DE LA GARANTIA):
                </strong>
                “EL DEUDOR(A)” se garantiza con garantía personal con el fiel y estricto 
                cumplimientos de la presente obligación, por el cual se compromete en cancelar si hubiera 
                retraso en cuotas y con la generalidad de sus bienes, acciones y derechos, muebles e inmuebles, 
                presentes y futuro. Las garantías, sin reservas, restricciones ni limitaciones de ninguna clase, 
                quedan redactadas y grabadas a favor de 
                <strong style="text-transform: uppercase;">
                    {{empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa': DB::table('mi_empresa')->get()[0]->nombre}}
                </strong>
                , hasta que se haga efectivo el pago total y 
                definitivo de toda obligación en caso de incumplimiento se tomaran sus bienes hasta completar 
                la deuda en especial lo que se esta dejando en su declaración patrimonial.
            </p>

            <p class="informe">
                <strong>
                    QUINTA. - (INTERES):
                </strong>
                <?= $contrato['contenido']['quinta_parte']; ?>
            </p>

            <p class="informe">
                <strong>
                    A) CARGOS. -
                </strong>
                <?= $contrato['contenido']['quinta_parte_a']; ?>
            </p>

            <p class="informe">
                <strong>
                    SEXTA.- (MORA):
                </strong>
                <?= $contrato['contenido']['sexta_parte']; ?>
            </p>

            <p class="informe">
                <strong>
                    SEPTIMA.- (AUTORIZACION):
                </strong>
                EL DEUDOR(A) autoriza expresamente a 
                <strong style="text-transform: uppercase;">
                    {{empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa': DB::table('mi_empresa')->get()[0]->nombre}} S.R.L. 
                </strong>
                A mantener 
                reportadas en central de riesgos al DEUDOR(A).
            </p>

            {{-- <p class="informe">
                <strong>
                    OCTAVA.- (ACEPTACIÓN):
                </strong>
                de las partes del Sr(a) 
                <strong style="text-transform: uppercase;">
                    {{$cliente}} 
                </strong>
                C.I. 
                {{$ci}} 
                como
                DEUDOR(A) Sr.(a) , del ACREEDOR(A) 
                Sr.(a) 
                <strong style="text-transform: uppercase;">
                    {{empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa': DB::table('mi_empresa')->get()[0]->nombre}}
                    S.R.L. 

                </strong>
                Aceptan el tenor de toda y cada una de las anteriores clausulas 
                y se comprometen a su fiel y estricto cumplimiento.
            </p> --}}

            <p class="informe">
                <strong>
                    OCTAVA.- (ACEPTACIÓN):
                </strong>
                de las partes del Sr(a) 
                <strong style="text-transform: uppercase;">
                    {{$cliente}} 
                </strong>
                C.I. 
                {{$ci}} 
                como
                DEUDOR(A)
                ,

                @foreach($codeudores as $codeudor)
                Sr.(a) 
                <strong style="text-transform: uppercase;">
                    {{$codeudor->nombre}} 
                </strong>
                con C.I. {{$codeudor->ci}} como CODEUDOR(A)
                @endforeach

                Sr.(a) 
                <strong style="text-transform: uppercase;">
                    {{$codeudor->nombre}} 
                </strong>
                con C.I. {{$codeudor->ci}} como CODEUDOR(A)

                Sr.(a) , del ACREEDOR(A) 
                Sr.(a) 
                <strong style="text-transform: uppercase;">
                    {{empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa': DB::table('mi_empresa')->get()[0]->nombre}}
                    S.R.L. 

                </strong>
                Aceptan el tenor de toda y cada una de las anteriores clausulas 
                y se comprometen a su fiel y estricto cumplimiento.
            </p>

           
        </div>

        <!-- Fecha -->
   

           
            

           

            <div class="company-container">
              
                    <div class="bloque-firma">
                        <hr>
                        <p>
                            <strong style="text-transform: uppercase;">
                                <?= $cliente; ?>
                            </strong>
                        </p>
                        <p>CI. {{$ci}} {{$lugar_expedicion}}</p>
                        <p>DEUDOR</p>
                    </div>
                    @foreach($codeudores as $codeudor)
                    <div class="bloque-firma">
                        <hr>
                        <p>
                            <strong style="text-transform: uppercase;">
                                {{ $codeudor->nombre}}
                            </strong>
                        </p>
                        <p>CI. {{$codeudor->ci}} S.C.</p>
                        <p>CODEUDOR</p>
                    </div>
                    @endforeach

                    <div class="bloque-firma">
                        <hr>
                        <p>
                            <strong style="text-transform: uppercase;">
                                {{ empty(DB::table('mi_empresa')->get()[0]->nombre)?'Nombre de la empresa': DB::table('mi_empresa')->get()[0]->nombre}}
                            </strong>
                        </p>
                        <p>CI. 461446024 S.C.</p>
                        <p>ACREEDOR</p>
                    </div>

      
            </div>


           
            
            
        <p class="titulo-seccion">
            {{
                Carbon::now();
            }}
        </p>
    </div>
</body>
</html>
