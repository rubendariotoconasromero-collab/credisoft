<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Plan de Pagos</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10pt; color: #333; margin: 0; padding: 10px; }
        
        /* Cabecera Principal */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { vertical-align: middle; border: none; }
        .logo-img { max-height: 50px; max-width: 130px; margin-bottom: 5px; }
        .company-name { font-size: 12px; font-weight: bold; color: #000; margin: 0; text-transform: uppercase; }
        .company-details { font-size: 9px; color: #555; margin: 2px 0; }
        .report-title { font-size: 16px; font-weight: bold; text-align: center; text-transform: uppercase; color: #1a1a1a; padding: 8px; border-radius: 5px;}
        
        /* Paneles Estilo Tarjeta (Cards de Vue) */
        .cards-container { width: 100%; border-collapse: separate; border-spacing: 15px 0; margin-bottom: 20px; margin-left:-7px;}
        .card { padding: 0; vertical-align: top; width: 50%; }
        .card-header { font-weight: bold; font-size: 12px; text-transform: uppercase; padding: 8px;}
        .card-body { padding-top: 10px; font-size: 10px; padding-bottom: 10px; padding-left: 0px;padding-right: 0px;}
        
        /* Estilos 100% seguros para DomPDF */
        .info-row-table {
            width: 100%;
            margin-bottom: 5px;
            border-collapse: collapse;
        }

        .info-row-table td {
            border-bottom: 1px dashed #eee;
            padding-bottom: 4px;
            vertical-align: top;
        }

        .info-label {
            text-align: left;
            color: #666;
            font-weight: normal;
            width: 40%;
            font-size: 11px;
        }

        .info-value {
            text-align: right;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            width: 60%;
            font-size: 11px;
        }

        /* Tabla Principal de Cuotas */
        .main-table { width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 20px; }
        .main-table th { background-color: #003c97; color: #ffffff; padding: 6px 4px; font-weight: bold; text-align: center; border: 1px solid #e5e7eb; }
        .main-table td { padding: 6px 4px; border-bottom: 1px solid #e5e7eb; text-align: center; vertical-align: middle;}
        .main-table tbody tr:nth-child(even) { background-color: #f9fafb; }
        
        /* Colores Específicos */
        .text-primary { color: #0d6efd; }
        .text-danger { color: #dc3545; }
        .text-success { color: #198754; }
        .bg-light-success { background-color: #f0fdf4; }
        .small-text { font-size: 7px; color: #666; display: block; margin-top: 2px;}
        
        /* Sub-Tabla Historial de Pagos */
        .history-row { background-color: #f8f9fa; }
        .history-table { width: 90%; margin: 5px auto; border-collapse: collapse; font-size: 8px; background-color: #fff; border: 1px solid #198754; }
        .history-table th { background-color: #178ed3; color: #fff; padding: 4px; font-weight: bold; border: 1px solid #178ed3;}
        .history-table td { padding: 4px; border: 1px solid #ddd; }

        /* Badges */
        .badge { padding: 3px 6px; border-radius: 4px; font-weight: bold; font-size: 8px; color: #fff; }
        .bg-success { color: #198754; }
        .bg-warning { color: #ffc107; color: #000; }
        .bg-danger { color: #dc3545; }
        .bg-dark { color: #212529; }
        .bg-info { color: #0dcaf0; color: #000; }
    </style>
</head>
<body>

    <?php
        $url = empty(DB::table('mi_empresa')->get()[0]->logo) ? 'logo_sistema_codesoft.png' : DB::table('mi_empresa')->get()[0]->logo;
        $image = file_get_contents(public_path('img/' . $url));
        $logoHtml = '<img src="data:image/png;base64,' . base64_encode($image) . '" class="logo-img">';
        
        // Emulando el getEstadoCuota() de tu Vue
        function getEstadoCuotaHTML($estado, $dias_pasados) {
            if ($estado == 2) return '<span class="badge bg-success">PAGADO</span>';
            if ($estado == 3) return '<span class="badge bg-warning">PAGO PARCIAL</span>';
            if ($estado == 0) return '<span class="badge bg-dark">ANULADO</span>';
            if ($estado == 1) {
                if ($dias_pasados > 0) return '<span class="badge bg-danger">MORA</span>';
                return '<span class="badge bg-info">PENDIENTE</span>';
            }
            return '<span class="badge bg-dark">N/A</span>';
        }
    ?>

    <table class="header-table">
        <tr>
            <td width="30%">
                <?php echo $logoHtml; ?>
                <p class="company-name">{{ empty(DB::table('mi_empresa')->get()[0]->nombre) ? 'Empresa' : DB::table('mi_empresa')->get()[0]->nombre }}</p>
                <p class="company-details">{{ empty(DB::table('mi_empresa')->get()[0]->direccion) ? '' : DB::table('mi_empresa')->get()[0]->direccion }}</p>
                <p class="company-details">Tel: {{ empty(DB::table('mi_empresa')->get()[0]->telefono) ? 'N/A' : DB::table('mi_empresa')->get()[0]->telefono }}</p>
            </td>
            <td width="40%" style="text-align: center;">
                <div class="report-title">Detalle del Plan de Pagos</div>
                <div style="font-size:10px; margin-top:5px; font-weight:bold;">COD. CRÉDITO: #{{ $informacion->id }}</div>
            </td>
            <td width="30%" style="text-align: right; font-size: 9px; line-height: 1.4;">
                <strong>Fecha Impresión:</strong> {{ $fecha_reporte }}<br>
                <strong>Hora:</strong> {{ $hora_reporte }}<br>
                <strong>Usuario:</strong> {{ $usuario }}
            </td>
        </tr>
    </table>

    <table class="cards-container">
        <tr>
            <td class="card">
                {{-- <div class="card-header">Información del Cliente</div> --}}
                <div class="card-body">
                    <table class="info-row-table">
                        <tr>
                            <td class="info-label">Nombre:</td>
                            <td class="info-value">{{ $informacion->cliente }}</td>
                        </tr>
                    </table>
                    <table class="info-row-table">
                        <tr>
                            <td class="info-label">Cédula Identidad:</td>
                            <td class="info-value">{{ $informacion->ci }} {{ $informacion->lugar_expedicion }}</td>
                        </tr>
                    </table>
                    <table class="info-row-table" style="border: none;">
                        <tr>
                            <td class="info-label" style="border: none;">Asesor Asignado:</td>
                            <td class="info-value" style="border: none;">{{ $informacion->nombre_asesor }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="card">
                {{-- <div class="card-header">Detalles del Plan</div> --}}
                <div class="card-body">
                    <table class="info-row-table">
                        <tr>
                            <td class="info-label">Monto Solicitado:</td>
                            <td class="info-value text-primary">{{ number_format($informacion->importe_solicitud, 2) }} {{ $informacion->moneda }}</td>
                        </tr>
                    </table>
                    <table class="info-row-table">
                        <tr>
                            <td class="info-label">Plazo y Tasa:</td>
                            <td class="info-value">{{ $informacion->nro_cuotas }} {{ $informacion->lapso_capital }} | {{ $informacion->tasa }}%</td>
                        </tr>
                    </table>
                    <table class="info-row-table" style="border: none;">
                        <tr>
                            <td class="info-label" style="border: none;">Garantía:</td>
                            <td class="info-value" style="border: none;">{{ $informacion->tipo_garantia }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div style="font-weight: bold; font-size: 11px; margin-bottom: 5px; text-transform:uppercase;">Cronograma de Cuotas</div>
    <table class="main-table">
        <thead>
            <tr>
                <th width="3%">#</th>
                <th width="8%">F. Venc.</th>
                <th width="10%">Capital</th>
                <th width="8%">Interés</th>
                <th width="9%">Saldo Cap.</th>
                <th width="10%">Total Cuota</th>
                {{-- <th width="6%">Días Trans.</th> --}}
                {{-- <th width="9%" class="text-primary">Int. Deveng.</th> --}}
                <th width="9%" class="text-danger">Int. Mora</th>
                <th width="9%">Int. Acum.</th>
                <th width="12%">Total Pagado</th>
                <th width="7%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $cuota)
                <?php 
                    // Cálculos de total pagado igual que en el frontend
                    $totalPagado = $cuota->capital_pagado_total + $cuota->interes_pagado_total + $cuota->mora_pagada;
                ?>
                <tr>
                    <td style="font-weight: bold;">{{ $cuota->numero }}</td>
                    <td>{{ \Carbon\Carbon::parse($cuota->fecha)->format('d/m/Y') }}</td>
                    
                    <td><strong>{{ number_format($cuota->capital_neto, 2) }}</strong></td>
                    <td>{{ number_format($cuota->interes, 2) }}</td>
                    <td>{{ number_format($cuota->saldo_capital, 2) }}</td>
                    <td><strong>{{ number_format($cuota->total, 2) }}</strong></td>
                    
                    {{-- <td>{{ $cuota->estado == 0 ? '---' : $cuota->dias_transcurridos }}</td> --}}
                    
                    {{-- <td class="text-primary">{{ $cuota->estado == 0 ? '---' : number_format($cuota->interes_devengado_neto, 2) }}</td> --}}
                    <td class="text-danger"><strong>{{ $cuota->estado == 0 ? '---' : number_format($cuota->interes_moratorio_neto, 2) }}</strong></td>
                    <td class="text-primary"><strong>{{ $cuota->estado == 0 ? '---' : number_format($cuota->interes_acumulado_neto, 2) }}</strong></td>
                    
                    <td class="bg-light-success">
                        @if($totalPagado > 0)
                            <strong class="text-success">{{ number_format($totalPagado, 2) }} Bs</strong>
                            <span class="small-text">Cap: {{ number_format($cuota->capital_pagado_total, 2) }} | Int: {{ number_format($cuota->interes_pagado_total, 2) }}</span>
                        @else
                            <span style="color:#aaa; font-style:italic;">Sin pagos</span>
                        @endif
                    </td>
                    
                    <td>
                        {!! getEstadoCuotaHTML($cuota->estado, $cuota->dias_pasados) !!}
                    </td>
                </tr>

                @if(count($cuota->historial_pagos) > 0)
                    <tr class="history-row">
                        
                        <td colspan="5" style="background-color: #ffffff; border-bottom: 1px solid #e5e7eb; border-left: none; border-right: none;"></td>
                        
                        <td colspan="5" style="padding: 6px; background-color: #f8f9fa; border-left: 2px solid #178ed3; border-bottom: 1px solid #e5e7eb;">
                            
                            <table class="history-table" style="width: 100%; margin: 0; border: 1px solid #178ed3;">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">Fecha Recibo</th>
                                        <th style="text-align: right;">Abono Capital</th>
                                        <th style="text-align: right;">Abono Interés</th>
                                        <th style="text-align: right; background-color: #dc3545; border: 1px solid #dc3545;">Abono Mora</th>
                                        <th style="text-align: right;">Total Recibo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cuota->historial_pagos as $pago)
                                    <tr>
                                        <td style="text-align: left;">
                                            <strong>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</strong> 
                                            <span style="color:#666;">(#{{ $pago->id }})</span>
                                        </td>
                                        <td style="text-align: right;">{{ number_format($pago->pago_capital, 2) }}</td>
                                        <td style="text-align: right;">{{ number_format($pago->pago_interes, 2) }}</td>
                                        <td style="text-align: right; color: #dc3545;">{{ number_format($pago->pago_mora, 2) }}</td>
                                        <td style="text-align: right;"><strong>{{ number_format($pago->pago_capital + $pago->pago_interes + $pago->pago_mora, 2) }} Bs</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                        </td>
                    </tr>
                @endif
                
            @endforeach
        </tbody>
    </table>

    <div style="font-size: 9px; color: #555; text-align: center; border-top: 1px solid #ddd; padding-top: 10px;">
        Este cronograma refleja el estado actual de la deuda considerando los pagos parciales y multas acumuladas. Cualquier atraso modifica los intereses a pagar y genera interés moratorio.
    </div>

</body>
</html>