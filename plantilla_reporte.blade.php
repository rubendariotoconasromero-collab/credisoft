<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Reporte Oficial')</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 10px;
        }
        .container {
            width: 100%;
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
            text-align: left;
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
            font-size: 11px;
            font-weight: bold;
            color: #000000;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .company-details {
            font-size: 9px;
            margin: 3px 0;
        }
        .report-title {
            font-size: 12px;
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
            font-size: 11px;
        }
        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, 
        .data-table td {
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            vertical-align: middle;
        }
        .data-table th {
            color: #000000;
            font-weight: bold;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        /* Two-column layout */
        .two-col-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
        }
        .two-col-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        /* Card wrapper with rounded corners */
        .rounded-card {
            border: 1px solid #a3a3a3;
            border-radius: 6px;
            padding: 10px;
        }
        /* Details table wrapper with rounded corners */
        .rounded-table-wrapper {
            border: 1px solid #a3a3a3;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 15px;
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
            font-size: 10px;
            font-weight: 400;
            color: #555;
        }
        @yield('styles')
    </style>
</head>
<body>
    <div class="container">
        <?php
        $emp = isset($empresa) ? $empresa : DB::table('mi_empresa')->first();
        $url = empty($emp->logo) ? 'logo_sistema_codesoft.png' : $emp->logo;
        $path = public_path('img/' . $url);
        $html_logo = '';
        if(file_exists($path)){
            $image = file_get_contents($path);
            $html_logo = '<img src="data:image/png;base64,' . base64_encode($image) . '" class="logo-img">';
        }
        ?>
        <!-- Header Section -->
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    {!! $html_logo !!}
                    <p class="company-name">{{ empty($emp->nombre) ? 'Nombre de la Empresa' : $emp->nombre }}</p>
                    <p class="company-details">{{ empty($emp->direccion) ? 'Dirección de la Empresa' : $emp->direccion }}</p>
                    <p class="company-details">
                        Tel: {{ empty($emp->telefono) ? 'N/A' : $emp->telefono }} | 
                        Email: {{ empty($emp->email) ? 'N/A' : $emp->email }}
                    </p>
                </td>
                <td class="company-cell">
                    <p class="report-title">@yield('report_title', 'Reporte Oficial')</p>
                </td>
                <td class="report-info-cell">
                    <p class="company-details"><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
                    <p class="company-details"><strong>Hora:</strong> {{ now()->format('H:i') }}</p>
                    <p class="company-details"><strong>Usuario:</strong> {{ auth()->user()->name ?? 'Sistema' }}</p>
                </td>
            </tr>
        </table>

        <!-- Report Information Section (If left/right yield elements exist) -->
        @if(View::hasSection('info_left') || View::hasSection('info_right'))
        <table class="two-col-table">
            <tr>
                <td>
                    @if(View::hasSection('info_left'))
                    <div class="rounded-card">
                        @yield('info_left')
                    </div>
                    @endif
                </td>
                <td>
                    @if(View::hasSection('info_right'))
                    <div class="rounded-card">
                        @yield('info_right')
                    </div>
                    @endif
                </td>
            </tr>
        </table>
        @endif

        <!-- Totals Summary Card -->
        @yield('totals_box')

        <!-- Details Section -->
        @yield('content')

        <!-- Footer Note -->
        <p class="pt-2">
            @yield('footer_note', 'El reporte de consulta financiera es de carácter oficial para fines de auditoría y revisión interna. Cualquier inconsistencia detectada en los saldos o movimientos debe reportarse al área contable administrativa de inmediato.')
        </p>
    </div>
</body>
</html>
