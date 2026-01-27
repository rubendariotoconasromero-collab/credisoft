<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{$title}}</title>
    <style>
        body { font-family: Calibri, 'Arial Rounded MT Bold', Arial, sans-serif; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        
        /* Header Styles */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { padding: 5px; vertical-align: middle; border: none; }
        .logo-cell { width: 33%; text-align: left; }
        .company-cell { width: 40%; text-align: center; }
        .report-info-cell { width: 27%; text-align: right; font-size: 11px; }
        .logo-img { max-height: 50px; max-width: 120px; margin-bottom: 5px; }
        .company-name { font-size: 12px; font-weight: bold; color: #000000; margin: 5px 0; text-transform: uppercase; }
        .company-details { font-size: 10px; margin: 3px 0; text-align: left; }
        .report-title { font-size: 14px; font-weight: bold; text-align: center; margin: 20px 0; text-transform: uppercase; color: #1a1a1a; }
        
        /* Table Styles */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { padding: 4px; text-align: left; font-size: 11px; }
        .data-table th { padding: 10px 4px; border-bottom: 1px solid #000000; border-top: 1px solid #000000; background-color: #fffcd6cc; color: #000000; font-weight: bold; vertical-align: middle; }
        .data-table tr:nth-child(even) { background-color: #f9fafb; }
    </style>
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if(!empty($logo_base64))
                        <img src="{{ $logo_base64 }}" class="logo-img">
                    @endif
                    <p class="company-details">{{ $mi_empresa->direccion ?? 'Dirección de la Empresa' }}</p>
                    <p class="company-details">
                        Tel: {{ $mi_empresa->telefono ?? 'N/A' }} | 
                        Email: {{ $mi_empresa->email ?? 'N/A' }}
                    </p>
                </td>
                <td class="company-cell">
                    <p class="report-title">{{$title}}</p>
                </td>
                <td class="report-info-cell">
                    <p class="company-details"><strong>Fecha:</strong> {{ $fecha }}</p>
                    <p class="company-details"><strong>Hora:</strong> {{ $hora }}</p>
                    <p class="company-details"><strong>Usuario:</strong> {{ $usuario }}</p>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Id</th>
                    <th>Nombre</th>
                    <th>CI</th>
                    <th>Actividad</th>
                    <th>Ingreso</th>
                    <th>Teléfonos</th>
                    <th>Direcciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientes as $cliente)
                {{-- 
                    ¡OPTIMIZADO! 
                    Ya no hay consultas SQL aquí. 
                    Laravel ya cargó $cliente->telefonos y $cliente->direcciones en memoria.
                --}}
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $cliente->id }}</td>
                    <td style="text-transform: capitalize; font-weight:800">{{ $cliente->nombre }}</td>
                    <td>{{ $cliente->ci }} {{ $cliente->lugar_expedicion }}</td>
                    <td>{{ $cliente->actividad }}</td>
                    <td>{{ number_format($cliente->ingreso_mensual, 2, ',', '.') }} Bs.</td>

                    <td>
                        @if($cliente->telefonos->isNotEmpty())
                            <ul style="margin: 0; padding-left: 15px;">
                            @foreach($cliente->telefonos as $telefono)
                                @if($telefono->tipo == 'Informacion contacto')
                                    <li style="text-transform:capitalize"> 
                                        {{ $telefono->relacion }}: {{ $telefono->nombre }} {{ $telefono->apellidos }} 
                                    </li>
                                    <li style="margin-left:10px"> Telf. {{ $telefono->numero }}</li>
                                @else 
                                    <li> Telf.: {{ $telefono->numero }} </li>
                                    <li style="margin-left:10px"> {{ $telefono->observacion }} </li>
                                @endif
                            @endforeach
                            </ul>
                        @else
                            <p>-</p>
                        @endif
                    </td>

                    <td>
                        @if($cliente->direcciones->isNotEmpty())
                            <ul style="margin: 0; padding-left: 15px;">
                            @foreach($cliente->direcciones as $direccion)
                                <li>{{ $direccion->tipo }}: {{ $direccion->descripcion }}</li>
                            @endforeach
                            </ul>
                        @else
                            <p>-</p>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>