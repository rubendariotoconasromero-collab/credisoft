<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante de Desembolso</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 15px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Header Styles */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td {
            padding: 5px;
            vertical-align: middle;
            border: none;
        }

        .logo-cell {
            width: 33%;
            text-align: center;
        }

        .company-cell {
            width: 40%;
            text-align: center;
        }

        .report-info-cell {
            width: 27%;
            text-align: right;
            font-size: 10px;
        }

        .logo-img {
            max-height: 50px;
            max-width: 120px;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            margin: 5px 0;
            text-transform: uppercase;
        }

        .company-details {
            font-size: 10px;
            margin: 3px 0;
        }

        .report-title {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            text-transform: uppercase;
            color: #1a1a1a;
        }

        /* Section Styles */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #171717;
            margin-top: 20px;
            margin-bottom: 5px;
            text-transform: uppercase;
            border:none;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            /* border-collapse: separate; */
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            /* border: 1px solid #e5e7eb; */
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
            border-collapse: collapse;

        }

        .data-table th {
            /* background-color: #4CAF50; */
            color: #000;
            font-weight: bold;

        }

        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        /* Two-column layout */
        .two-col-table {
            width: 100%;
            /* border-collapse: separate; */

            border-spacing: 10px 0;



        }

        .two-col-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        /* Signature Section */
        .signature-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            padding: 10px;
            font-size: 11px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin: 10px auto;
            width: 200px;
        }

        /* Helper classes */
        .text-bold {
            font-weight: bold;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mt-0 {
            margin-top: 0;
        }

        .pt-2 {
            padding-top: 10px;
        }

    </style>
</head>

<body>
    <table class="data-table">
        
            <tr>
                <td>
                    <div class="container" style="">
                        <!-- Header Section -->
                        {{-- AQUI NO HACER CAMBIOS ESTA BIEN --}}
                        <table class="header-table">
                            <tr>
                                <td class="logo-cell">
                                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/' . $url))) }}"
                                        class="logo-img" alt="Logo">
                                    <p class="company-name">{{ $empresa->nombre ?? 'Nombre de la Empresa' }}</p>
                                    <p class="company-details">{{ $empresa->direccion ?? 'Dirección de la Empresa' }}</p>
                                    <p class="company-details">
                                        Tel: {{ $empresa->telefono ?? 'N/A' }}
                                    </p>
                                    <p>
                                        Email: {{ $empresa->email ?? 'N/A' }}
                                    </p>
                                </td>
                                <td class="company-cell">
                                    <p class="report-title">Comprobante de Desembolso</p>
                                </td>
                                <td class="report-info-cell">
                                    <p class="company-details"><strong>Fecha:</strong> {{ $fecha_reporte }}</p>
                                    <p class="company-details"><strong>Usuario:</strong> {{ $usuario }}</p>
                                </td>
                            </tr>
                        </table>
    
                        <!-- Client and Loan Information -->
                        {{-- ESTO QUE VAYA EN UNA SECCION  --}}
                        {{-- QUE TENGA UN BORDE DE CLOR NEGRO --}}
                        <div class="section-title">Información del Cliente</div>
    
                        <div style="border:1px #000 solid; border-radius:20px; padding-bottom:0px;">
                            <table class="two-col-table">
                                <tr>
                                    <td>
                                        <table class="data-table">
                                            <tr>
                                                <th>Cliente</th>
                                                <td style="text-transform: uppercase">
                                                    {{ $desembolso->cliente.' - ' }}
    
                                                    {{ $desembolso->ci_cliente }}
                                                    <strong>
                                                        {{ $desembolso->lugar_expedicion }}
    
                                                    </strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Teléfono(s)</th>
                                                <td>
                                                    @if($telefonos->isNotEmpty())
                                                    @foreach($telefonos as $telefono)
                                                    @if($telefono->tipo == 'Numero telefono')
                                                    <p class="mb-0">{{ $telefono->numero }}</p>
    
                                                    @elseif($telefono->tipo == 'Informacion contacto')
                                                    <p class="mb-0">{{ $telefono->numero }} - {{ $telefono->nombre}} -
                                                        {{ $telefono->apellidos}} - {{ $telefono->relacion}}</p>
                                                    @endif
    
                                                    @endforeach
                                                    @else
                                                    <p class="mb-0">No hay teléfonos disponibles</p>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Dirección(es)</th>
                                                <td>
                                                    @if($direcciones->isNotEmpty())
                                                    @foreach($direcciones as $direccion)
                                                    <p class="mb-0">{{ $direccion->tipo }}: {{ $direccion->descripcion }}
                                                    </p>
                                                    @endforeach
                                                    @else
                                                    <p class="mb-0">No hay direcciones disponibles</p>
                                                    @endif
                                                </td>
                                            </tr>
    
                                            <tr>
                                                <th>Actividad</th>
                                                <td style="">
                                                    {{ $desembolso->actividad}}
    
    
                                                </td>
                                            </tr>
                                        </table>
    
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="section-title">Información del Préstamo</div>
    
                        <div style="border:1px solid #000; border-radius:20px; padding-bottom:0px;">
                            <table class="two-col-table">
                                <tr>
                                    <td>
                                        <table class="data-table">
                                            <tr>
                                                <th>Nro. Crédito</th>
                                                <td>{{ $desembolso->id_plan_pago }}</td>
                                            </tr>
                                            <tr>
                                                <th>Forma de Pago</th>
                                                <td>{{ $desembolso->lapso_capital }}</td>
                                            </tr>
                                            <tr>
                                                <th>Importe Préstamo</th>
                                                <td>{{ number_format($desembolso->importe_prestamo, 2, ',', '.') }} Bs.</td>
                                            </tr>
                                            <tr>
                                                <th>Garantía</th>
                                                <td>{{ $desembolso->garantia }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha Crédito</th>
                                                <td>{{ date('d/m/Y', strtotime($desembolso->fecha_credito)) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Cuotas</th>
                                                <td>{{ $desembolso->cuotas }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha Desembolso</th>
                                                <td>{{ date('d/m/Y', strtotime($desembolso->fecha_desembolso)) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha Máx. Devolución</th>
                                                <td>{{ date('d/m/Y', strtotime($desembolso->fecha_max_devolucion)) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
    
    
                        <div class="section-title">Siguiente Pago</div>
                        <div style="border:1px solid #000; border-radius:20px; padding-bottom:0px;">
                            <table class="two-col-table">
                                <tr>
                                    <td>
                                        <table class="data-table">
                                            <tr>
                                                <th>Fecha</th>
                                                <td>{{ $fecha_primera_cuota }}</td>
                                            </tr>
                                            <tr>
                                                <th>Monto</th>
                                                <td>{{ $monto_primera_cuota }} Bs.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
    
                        </div>
    
                        <br>
                        <br>
                        <br>
                        <!-- Signature Section -->
                        <table style="width: 100%; border-collapse: collapse; margin-top: 30px;">
                            <tr>
                                <td style="width:50%; text-align: center; padding-top: 40px;">
                                    {{-- Cambiado de border-top a height y background-color --}}
                                    <hr width="80%">
                                    <p style="margin: 5px 0 0 0; font-size: 12px;">Asesor de Crédito</p>
                                </td>
    
                                <td style="width:50%; text-align: center; padding-top: 40px;">
                                    {{-- Cambiado de border-top a height y background-color --}}
                                    <hr width="80%">
                                    <p style="margin: 5px 0 0 0; font-size: 12px;">Encargado de Agencia</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td>
                    <div class="container" style="">
                        <!-- Header Section -->
                        {{-- AQUI NO HACER CAMBIOS ESTA BIEN --}}
                        <table class="header-table">
                            <tr>
                                <td class="logo-cell">
                                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/' . $url))) }}"
                                        class="logo-img" alt="Logo">
                                    <p class="company-name">{{ $empresa->nombre ?? 'Nombre de la Empresa' }}</p>
                                    <p class="company-details">{{ $empresa->direccion ?? 'Dirección de la Empresa' }}</p>
                                    <p class="company-details">
                                        Tel: {{ $empresa->telefono ?? 'N/A' }} |
                                        Email: {{ $empresa->email ?? 'N/A' }}
                                    </p>
                                </td>
                                <td class="company-cell">
                                    <p class="report-title">Comprobante de Desembolso</p>
                                </td>
                                <td class="report-info-cell">
                                    <p class="company-details"><strong>Fecha:</strong> {{ $fecha_reporte }}</p>
                                    <p class="company-details"><strong>Usuario:</strong> {{ $usuario }}</p>
                                </td>
                            </tr>
                        </table>
    
                        <!-- Client and Loan Information -->
                        {{-- ESTO QUE VAYA EN UNA SECCION  --}}
                        {{-- QUE TENGA UN BORDE DE CLOR NEGRO --}}
                        <div class="section-title">Información del Cliente</div>
    
                        <div style="border:1px solid #000; border-radius:20px; padding-bottom:0px;">
                            <table class="two-col-table">
                                <tr>
                                    <td>
                                        <table class="data-table">
                                            <tr>
                                                <th>Cliente</th>
                                                <td style="text-transform: uppercase">
                                                    {{ $desembolso->cliente.' - ' }}
    
                                                    {{ $desembolso->ci_cliente }}
                                                    <strong>
                                                        {{ $desembolso->lugar_expedicion }}
    
                                                    </strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Teléfono(s)</th>
                                                <td>
                                                    @if($telefonos->isNotEmpty())
                                                    @foreach($telefonos as $telefono)
                                                    @if($telefono->tipo == 'Numero telefono')
                                                    <p class="mb-0">{{ $telefono->numero }}</p>
    
                                                    @elseif($telefono->tipo == 'Informacion contacto')
                                                    <p class="mb-0">{{ $telefono->numero }} - {{ $telefono->nombre}} -
                                                        {{ $telefono->apellidos}} - {{ $telefono->relacion}}</p>
                                                    @endif
    
                                                    @endforeach
                                                    @else
                                                    <p class="mb-0">No hay teléfonos disponibles</p>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Dirección(es)</th>
                                                <td>
                                                    @if($direcciones->isNotEmpty())
                                                    @foreach($direcciones as $direccion)
                                                    <p class="mb-0">{{ $direccion->tipo }}: {{ $direccion->descripcion }}
                                                    </p>
                                                    @endforeach
                                                    @else
                                                    <p class="mb-0">No hay direcciones disponibles</p>
                                                    @endif
                                                </td>
                                            </tr>
    
                                            <tr>
                                                <th>Actividad</th>
                                                <td style="">
                                                    {{ $desembolso->actividad}}
    
                                                </td>
                                            </tr>
                                        </table>
    
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="section-title">Información del Préstamo</div>
    
                        <div style="border:1px solid #000; border-radius:20px; padding-bottom:0px;">
                            <table class="two-col-table">
                                <tr>
                                    <td>
                                        <table class="data-table">
                                            <tr>
                                                <th>Nro. Crédito</th>
                                                <td>{{ $desembolso->id_plan_pago }}</td>
                                            </tr>
                                            <tr>
                                                <th>Forma de Pago</th>
                                                <td>{{ $desembolso->lapso_capital }}</td>
                                            </tr>
                                            <tr>
                                                <th>Importe Préstamo</th>
                                                <td>{{ number_format($desembolso->importe_prestamo, 2, ',', '.') }} Bs.</td>
                                            </tr>
                                            <tr>
                                                <th>Garantía</th>
                                                <td>{{ $desembolso->garantia }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha Crédito</th>
                                                <td>{{ date('d/m/Y', strtotime($desembolso->fecha_credito)) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Cuotas</th>
                                                <td>{{ $desembolso->cuotas }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha Desembolso</th>
                                                <td>{{ date('d/m/Y', strtotime($desembolso->fecha_desembolso)) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha Máx. Devolución</th>
                                                <td>{{ date('d/m/Y', strtotime($desembolso->fecha_max_devolucion)) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
    
    
                        <div class="section-title">Siguiente Pago</div>
                        <div style="border:1px solid #000; border-radius:20px; padding-bottom:0px;">
                            <table class="two-col-table">
                                <tr>
                                    <td>
                                        <table class="data-table">
                                            <tr>
                                                <th>Fecha</th>
                                                <td>{{ $fecha_primera_cuota }}</td>
                                            </tr>
                                            <tr>
                                                <th>Monto</th>
                                                <td>{{ $monto_primera_cuota }} Bs.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
    
                        </div>
    
                        <br>
                        <br>
                        <br>
                        <!-- Signature Section -->
                        <table style="width: 100%; border-collapse: collapse; margin-top: 30px;">
                            <tr>
                                <td style="width:50%; text-align: center; padding-top: 40px;">
                                    {{-- Cambiado de border-top a height y background-color --}}
                                    <hr width="80%">
                                    <p style="margin: 5px 0 0 0; font-size: 12px;">Asesor de Crédito</p>
                                </td>
    
                                <td style="width:50%; text-align: center; padding-top: 40px;">
                                    {{-- Cambiado de border-top a height y background-color --}}
                                    <hr width="80%">
                                    <p style="margin: 5px 0 0 0; font-size: 12px;">Encargado de Agencia</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>

    </table>



</body>

</html>
