<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Cliente</title>
    <style>
        
        
        /* body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        } */
        
        /* Header Styles */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #10b981;
        }
        
        .header-table td {
            padding: 5px;
            vertical-align: top;
            border: none;
        }
        
        .logo-cell {
            width: 25%;
        }
        
        .company-cell {
            width: 50%;
            text-align: center;
        }
        
        .report-info-cell {
            width: 25%;
            text-align: right;
            font-size:12px;
        }

    
        
        .logo-img {
            max-height: 70px;
            max-width: 100%;
        }
        
        .company-name {
            font-size: 13px;
            font-weight: bold;
            color: #000000;
            margin: 0;
            text-transform: uppercase;
        }
        
        .company-details {
            font-size: 9pt;
            margin: 2px 0;
        }
        
        .report-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
            color: #10b981;
        }
        
        /* Section Styles */
        .section-title {
            
            color: rgb(23, 23, 23);
            padding: 0px 5px;
            text-align: left;
            font-weight: bold;
            margin: 0px 0 0px;
            text-transform: uppercase;
            font-size: 14px;
            /* border-radius: 4px; */
            /* border-bottom:1px solid #000; */

        }
        
        /* Image Styles */
        .client-image-container {
            text-align: center;
            margin: 15px 0;
        }
        
        .client-image {
            max-height: 150px;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table th, 
        .data-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
            font-size: 12px;
        }
        
        .data-table th {
            background-color: #eeeeee;
            color:#000000;
            font-weight: bold;
            width: 30%;
            vertical-align: top;
            font-size: 12px;

        }
        
        /* List Styles */
        .clean-list {
            list-style-type: none;
            padding-left: 0;
            margin: 0;
        }
        
        .clean-list li {
            padding: 4px 0;
            border-bottom: 1px dotted #e5e7eb;
        }
        
        .clean-list li:last-child {
            border-bottom: none;
        }
        
        /* Two-column layout */
        .two-col-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 10px 0;
        }
        
        .two-col-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        
        /* Contact info icons */
        .contact-icon {
            font-weight: bold;
            color: #10b981;
            margin-right: 5px;
        }
        
        /* Helper classes */
        .text-muted {
            /* color: #6b7280; */
        }
        
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
    <table width="100%" style="border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <!-- Columna 1: Logo y datos de empresa -->
            <td width="33%" style="padding: 5px; vertical-align: middle;">
                <div style="text-align: center;">
                    @if ($empresa && !empty($empresa->logo))
                        <img src="{{ public_path('img/' . $empresa->logo) }}" alt="Logo de la Empresa" style="max-height: 50px; max-width: 120px; margin-bottom: 5px;">
                    @else
                        <img src="{{ public_path('img/logo_sistema_codesoft.png') }}" alt="Logo por defecto" style="max-height: 50px; max-width: 120px; margin-bottom: 5px;">
                    @endif
                    <p style="font-size: 12px; font-weight: bold; margin: 5px 0;">{{ $empresa->nombre ?? 'Nombre de la Empresa' }}</p>
                    <p style="font-size: 11px; margin: 3px 0;">{{ $empresa->direccion ?? 'Dirección de la Empresa' }}</p>
                    <p style="font-size: 11px; margin: 3px 0;">
                        Tel: {{ $empresa->telefono ?? 'N/A' }} | 
                        Email: {{ $empresa->email ?? 'N/A' }}
                    </p>
                </div>
            </td>
            
            <!-- Columna 2: Título del reporte -->
            <td width="40%" style="padding: 5px; text-align: center; vertical-align: middle;">
                <p style="font-size: 14px; font-weight: bold; margin: 0; text-transform:uppercase">{{$title}}</p>
            </td>
            
            <!-- Columna 3: Información del reporte -->
            <td width="27%" style="padding: 5px; text-align: right; vertical-align: middle;">
                <p style="font-size: 11px; margin: 3px 0;"><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
                <p style="font-size: 11px; margin: 3px 0;"><strong>Hora:</strong> {{ now()->format('H:i') }}</p>
                <p style="font-size: 11px; margin: 3px 0;"><strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</p>
            </td>
        </tr>
    </table>

    

    <!-- Personal Information Section -->
    <div class="section-title">
         Datos Personales
         <hr style="margin-top:0px; color:#000000">
    </div>
    
    <!-- Client Image -->
    <div class="client-image-container">
        @if ($cliente->imagen)
            <img class="client-image" src="{{ public_path('img/cliente/' . $cliente->imagen) }}" alt="Foto del Cliente">
        @else
            <img class="client-image" src="{{ public_path('img/cliente/default.png') }}" alt="Foto por defecto">
        @endif
    </div>

    <!-- Basic Information -->
    <table class="data-table">
        <tr>
            <th style="width:50%">Nombre Completo</th>
            <td>{{ $cliente->nombre }}</td>
        </tr>
        <tr>
            <th style="width:50%">Fecha de Nacimiento</th>
            <td>{{ $cliente->fecha_nacimiento }}</td>
        </tr>
        <tr>
            <th style="width:50%">Cédula de Identidad</th>
            <td>{{ $cliente->ci }} {{ $cliente->lugar_expedicion }}</td>
        </tr>
        <tr>
            <th style="width:50%">Sexo</th>
            <td>{{ $cliente->sexo }}</td>
        </tr>
        <tr>
            <th style="width:50%">Estado Civil</th>
            <td>{{ $cliente->estado_civil }}</td>
        </tr>
        <tr>
            <th style="width:50%">Tipo de Vivienda</th>
            <td>{{ $cliente->vivienda }}</td>
        </tr>
        <tr>
            <th style="width:50%">Ingreso Mensual</th>
            <td><span class="text-primary text-bold">{{  number_format($cliente->ingreso_mensual ?? 0, 2)  }} Bs.</span></td>
        </tr>
        <tr>
            <th style="width:50%">Actividad Profesional</th>
            <td>{{ $cliente->actividad }}</td>
        </tr>
    </table>

    <!-- Contact Information Section -->
    <div class="section-title">
         Información de Contacto
         <hr style="margin-top:0px; color:#000000">
    </div>
    
    <!-- Two-Column Layout for Contact Info -->
    <table class="two-col-table" style="border: 1px solid #10b981; padding:0px;">
        <tr>
            <td style="padding:0px;">
                <!-- Phone Numbers -->
                <table class="data-table" style="padding:0px; margin:0px;border: 1px solid #b97e10;">
                    <tr>
                        <th colspan="2">Teléfonos</th>
                    </tr>
                    @if(count($telefonos) > 0)
                        @foreach($telefonos as $telefono)
                            <tr>
                                <td colspan="2">
                                    @if($telefono->tipo == 'Numero telefono')
                                        <span class="text-bold">{{ $telefono->numero }}</span>
                                        <br>
                                        <span class="text-muted">{{ $telefono->observacion }}</span>
                                    @elseif($telefono->tipo == 'Informacion contacto')
                                        <span class="text-bold">{{ $telefono->numero }}</span>
                                        <br>
                                        <span class="text-muted">{{ $telefono->nombre }} {{ $telefono->apellidos }}</span>
                                        <br>
                                        <span class="text-primary">{{ $telefono->relacion }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" class="text-muted">No hay teléfonos registrados</td>
                        </tr>
                    @endif
                </table>
            </td>
            <td>
                <!-- Addresses -->
                <table class="data-table">
                    <tr>
                        <th colspan="2">Direcciones</th>
                    </tr>
                    @if(count($direcciones) > 0)
                        @foreach($direcciones as $direccion)
                            <tr>
                                <td colspan="2">
                                    <span class="text-bold">{{ $direccion->descripcion }}</span>
                                    <br>
                                    <span class="text-muted">{{ $direccion->departamento }}</span>
                                    <br>
                                    <span class="text-primary">{{ $direccion->tipo }}</span>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" class="text-muted">No hay direcciones registradas</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
    
    <!-- Additional information or notes can be added here -->
    @if(isset($notes) && !empty($notes))
    <div class="section-title">
        <span class="contact-icon">&#9998;</span> Notas Adicionales
    </div>
    <p>{{ $notes }}</p>
    @endif
</body>
</html>