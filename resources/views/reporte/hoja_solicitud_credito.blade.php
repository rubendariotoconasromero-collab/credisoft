<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja Solicitud del Prestamo</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
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
            font-size: 11px;
        }
        .logo-img {
            max-height: 50px;
            max-width: 120px;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 12px;
            font-weight: bold;
            color: #000000;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .company-details {
            font-size: 10px;
            margin: 3px 0;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
            color: #1a1a1a;
        }
        /* Section Styles */
        .section-title {
            color: #171717;
            padding: 0 12px;
            text-align: left;
            font-weight: bold;
            margin: 0 0 15px;
            text-transform: uppercase;
            font-size: 12px;
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
        }
        .data-table th {
            /* background-color: #4CAF50; */
            color: #000;
            font-weight: bold;
            padding-left:30px;
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
            font-size:12px;
            font-weight: 400;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        $url = empty(DB::table('mi_empresa')->get()[0]->logo) ? 'logo_sistema_codesoft.png' : DB::table('mi_empresa')->get()[0]->logo;
        $image = file_get_contents('img/' . $url);
        $html = '<img src="data:image/png;base64,' . base64_encode($image) . '" class="logo-img">';
        ?>
        <!-- Header Section -->
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <?php echo $html ?>
                    <p class="company-name">{{ empty(DB::table('mi_empresa')->get()[0]->nombre) ? 'Nombre de la Empresa' : DB::table('mi_empresa')->get()[0]->nombre }}</p>
                    <p class="company-details">{{ empty(DB::table('mi_empresa')->get()[0]->direccion) ? 'Dirección de la Empresa' : DB::table('mi_empresa')->get()[0]->direccion }}</p>
                    <p class="company-details">
                        Tel: {{ empty(DB::table('mi_empresa')->get()[0]->telefono) ? 'N/A' : DB::table('mi_empresa')->get()[0]->telefono }} | 
                        Email: {{ empty(DB::table('mi_empresa')->get()[0]->email) ? 'N/A' : DB::table('mi_empresa')->get()[0]->email }}
                    </p>
                </td>
                <td class="company-cell">
                    <p class="report-title">Hoja de Solicitud del Prestamo</p>
                </td>
                <td class="report-info-cell">
                    <p class="company-details"><strong>Fecha:</strong> {{ $fecha_reporte }}</p>
                    <p class="company-details"><strong>Hora:</strong> {{ $hora_reporte }}</p>
                    <p class="company-details"><strong>Usuario:</strong> {{ $usuario }}</p>
                </td>
            </tr>
        </table>
        

        <div style="border:1px solid #000; border-radius:20px; padding-bottom:0px; margin-left:15px; margin-right:15px;">    
            <table class="two-col-table">
                <tr>
                    <td>
                        <table class="data-table">
                            <tr>
                                <th>Solicitud</th>
                                <td>{{ $id_solicitud }}</td>
                            </tr>
                            <tr>
                                <th>Fecha Solicitud</th>
                                <td>{{ $fecha_solicitud }}</td>
                            </tr>

                            
                            {{-- Inicio seccion clients --}}
                            <tr>
                                <th>Cliente</th>
                                <td>{{ $ci }} 
                                    <strong>
                                        {{ $lugar_expedicion }}
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <th>Nombre Cliente</th>
                                <td>{{ $cliente_nombre }}</td>
                            </tr>

                            <!-- Sección optimizada de Direcciones y Teléfonos -->
                            <tr>
                                <th style="vertical-align: top; padding: 8px 30px 8px 30px; width: 30%;">Direcciones:</th>
                                <td style="vertical-align: top; padding: 8px; width: 70%;">
                                    @if(count($direcciones) > 0)
                                       @foreach($direcciones as $direccion)
                                        <div style="margin-bottom: 6px; padding: 3px 6px; background-color: #f8f9fa; font-size: 10px; line-height: 1.2;">
                                            <div style="font-weight: bold; color: #333; margin-bottom: 1px; font-size: 10px;">• {{ $direccion->tipo }}</div>
                                            <div style="margin-left: 12px; font-size: 10px; margin-bottom: 1px;">
                                                <span style="font-weight: 500;">{{ empty($direccion->descripcion) ? '' : $direccion->descripcion }}</span>{{ !empty($direccion->descripcion) && !empty($direccion->referencia) ? ', ' : '' }}<span style="font-style: italic;">{{ empty($direccion->referencia) ? '' : $direccion->referencia }}</span>
                                            </div>
                                            @if(!empty($direccion->ciudad) || !empty($direccion->departamento))
                                                <div style="margin-left: 12px; color: #666; font-size: 10px;">
                                                    {{ empty($direccion->ciudad) ? '' : $direccion->ciudad }}{{ !empty($direccion->ciudad) && !empty($direccion->departamento) ? ', ' : '' }}{{ empty($direccion->departamento) ? '' : $direccion->departamento }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                    @else
                                        <span style="color: #999; font-style: italic; font-size: 10px;">No se registraron direcciones</span>
                                    @endif
                                </td>
                            </tr>
                            
                            <tr>
                                <th style="vertical-align: top; padding: 8px 30px 8px 30px; width: 30%;">Teléfonos:</th>
                                <td style="vertical-align: top; padding: 8px; width: 70%;">
                                    @if(count($telefonos) > 0)
                                        @foreach($telefonos as $telefono)
                                           
                                            <div style="margin-bottom: 6px; padding: 3px 6px; background-color: #f8f9fa; font-size: 10px; line-height: 1.2;">
                                                @if($telefono->tipo == 'Numero telefono')
                                                    <div style="font-weight: bold; color: #333; margin-bottom: 1px; font-size: 10px;">• Nro. Telf.</div>
                                                    
                                                    {{-- En una sola línea --}}
                                                    <div style="margin-left: 12px;">
                                                        <span style="font-weight: 500; font-size: 10px;">{{ $telefono->numero }}</span>
                                                        @if(!empty($telefono->observacion))
                                                            <span style="color: #666; font-style: italic; font-size: 10px;"> - {{ $telefono->observacion }}</span>
                                                        @endif
                                                    </div>

                                                @elseif($telefono->tipo == 'Informacion contacto')
                                                    <div style="font-weight: bold; color: #333; margin-bottom: 1px; font-size: 10px;">• Contacto</div>

                                                    {{-- En una sola línea: Teléfono + Nombre/Apellidos --}}
                                                    <div style="margin-left: 12px;">
                                                        <span style="font-weight: 500; font-size: 10px;">{{ $telefono->numero }}</span>
                                                        @if(!empty($telefono->nombre) || !empty($telefono->apellidos))
                                                            <span style="color: #666; font-size: 10px;"> - {{ trim($telefono->nombre . ' ' . $telefono->apellidos) }} - {{ !empty($telefono->relacion)?$telefono->relacion:'' }}</span>
                                                        @endif
                                                    </div>

                                                    {{-- Relación en una línea opcional --}}
                                                    {{-- @if(!empty($telefono->relacion))
                                                        <div style="margin-left: 12px; font-size: 10px; font-style: italic;">
                                                            {{ $telefono->relacion }}
                                                        </div>
                                                    @endif --}}
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <span style="color: #999; font-style: italic; font-size: 10px;">No se registraron teléfonos</span>
                                    @endif
                                </td>
                            </tr>
                            
                            {{-- Sección de Codeudores --}}
                            {{-- Sección de Codeudores --}}
                            @if(count($codeudores) === 0 || (count($codeudores) === 1 && $codeudores[0]['ci'] == '0'))
                                <tr>
                                    <th>Codeudores</th>
                                    <td colspan="2" style="color: #999; font-style: italic;">Este préstamo no tiene codeudores registrados.</td>
                                </tr>

                            @else
                                @foreach($codeudores as $codeudor)
                                    <!-- Sección de Codeudor -->
                                    <tr>
                                        <th>Codeudor</th>
                                        <td>{{ $codeudor['ci'] }} 
                                            <strong>{{ $codeudor['lugar_expedicion'] ?? '' }}</strong>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Nombre Codeudor</th>
                                        <td>{{ $codeudor['nombre'] ?? '' }}</td>
                                    </tr>

                                    <!-- Sección de Direcciones -->
                                    <tr>
                                        <th style="vertical-align: top; padding: 8px 30px 8px 30px; width: 30%;">Direcciones:</th>
                                        <td style="vertical-align: top; padding: 8px; width: 70%;">
                                            @if(count($codeudor['direcciones']) > 0)
                                                @foreach($codeudor['direcciones'] as $direccion)
                                                    <div style="margin-bottom: 6px; padding: 3px 6px; background-color: #f8f9fa; font-size: 10px; line-height: 1.2;">
                                                        <div style="font-weight: bold; color: #333; margin-bottom: 1px; font-size: 10px;">• {{ $direccion->tipo }}</div>
                                                        <div style="margin-left: 12px; font-size: 10px; margin-bottom: 1px;">
                                                            <span style="font-weight: 500;">{{ empty($direccion->descripcion) ? '' : $direccion->descripcion }}</span>{{ !empty($direccion->descripcion) && !empty($direccion->referencia) ? ', ' : '' }}<span style="font-style: italic;">{{ empty($direccion->referencia) ? '' : $direccion->referencia }}</span>
                                                        </div>
                                                        @if(!empty($direccion->ciudad) || !empty($direccion->departamento))
                                                            <div style="margin-left: 12px; color: #666; font-size: 10px;">
                                                                {{ empty($direccion->ciudad) ? '' : $direccion->ciudad }}{{ !empty($direccion->ciudad) && !empty($direccion->departamento) ? ', ' : '' }}{{ empty($direccion->departamento) ? '' : $direccion->departamento }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <span style="color: #999; font-style: italic; font-size: 10px;">No se registraron direcciones</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Sección de Teléfonos -->
                                    <tr>
                                        <th style="vertical-align: top; padding: 8px 30px 8px 30px; width: 30%;">Teléfonos:</th>

                                        <td style="vertical-align: top; padding: 8px; width: 70%;">
                                            @if(count($codeudor['telefonos']) > 0)
                                                @foreach($codeudor['telefonos'] as $telefono)
                                                
                                                    <div style="margin-bottom: 6px; padding: 3px 6px; background-color: #f8f9fa; font-size: 10px; line-height: 1.2;">
                                                        @if($telefono->tipo == 'Numero telefono')
                                                            <div style="font-weight: bold; color: #333; margin-bottom: 1px; font-size: 10px;">• Nro. Telf.</div>

                                                            {{-- En una sola línea --}}
                                                            <div style="margin-left: 12px;">
                                                                <span style="font-weight: 500; font-size: 10px;">{{ $telefono->numero }}</span>
                                                                @if(!empty($telefono->observacion))
                                                                    <span style="color: #666; font-style: italic; font-size: 10px;"> - {{ $telefono->observacion }}</span>
                                                                @endif
                                                            </div>

                                                        @elseif($telefono->tipo == 'Informacion contacto')
                                                            <div style="font-weight: bold; color: #333; margin-bottom: 1px; font-size: 10px;">• Contacto</div>

                                                            {{-- En una sola línea: Teléfono + Nombre/Apellidos --}}
                                                            <div style="margin-left: 12px;">
                                                                <span style="font-weight: 500; font-size: 10px;">{{ $telefono->numero }}</span>
                                                                @if(!empty($telefono->nombre) || !empty($telefono->apellidos))
                                                                    <span style="color: #666; font-size: 10px;"> - {{ trim($telefono->nombre . ' ' . $telefono->apellidos) }} - {{ !empty($telefono->relacion)?$telefono->relacion:'' }}</span>
                                                                @endif
                                                            </div>

                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <span style="color: #999; font-style: italic; font-size: 10px;">No se registraron teléfonos</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                            <tr>
                                <th>Oficial Cred.</th>
                                <td>{{ $id_usuario }}</td>
                            </tr>

                            <tr>
                                <th>Nombre Oficial</th>
                                <td>{{ $asesor }}</td>
                            </tr>

                            <tr>
                                <th>Forma de Pago</th>
                                <td>{{ $lapso_capital }}</td>
                            </tr>

                            <tr>
                                <th>Monto</th>
                                <td>{{ number_format($importe_solicitud?? 0, 2) }} Bs.</td>
                            </tr>

                            <tr>
                                <th>Plazo ({{$lapso_capital}})</th>
                                <td>{{ $nro_cuotas }}</td>
                            </tr>
                            
                            <tr>
                                <th>Tipo Garantia</th>
                                <td>{{ $tipo_garantia }}</td>
                            </tr>

                            <tr>
                                <th>Interes %</th>
                                <td>{{ $tasa }}</td>
                            </tr>

                            <tr>
                                <th>Usuario</th>
                                <td>{{ $asesor }}</td>
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
        {{-- <table style="width: 100%; border-collapse: collapse; margin-top: 30px;">
            <tr>
                <td style="width:100%; text-align: center; padding-top: 40px;">
                   
                    <hr width="35%">
                    <p style="margin: 5px 0 0 0; font-size: 11px;">Encargado de Agencia</p>
                </td>

              
            </tr>
        </table> --}}
    </div>
</body>
</html>