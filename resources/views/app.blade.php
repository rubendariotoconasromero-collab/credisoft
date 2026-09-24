{{--
    Shell SPA — Fase B (coexistencia).

    Página de prueba para Vue Router. Sirve exactamente el mismo layout que
    index.blade.php (topbar, estilos), pero el menú lateral y el contenido
    quedan bajo UN SOLO root de Vue (#app) para compartir el mismo router:
      - <app-menu> reemplaza el @include('menu') estático.
      - <router-view> reemplaza el @yield('content') estático.

    Las ~30 rutas Blade actuales (una página por módulo) NO se tocan: esta
    vista solo se sirve en /spa mientras se prueba la migración.
--}}
@php
    $mi_empresa=DB::table('mi_empresa')->first();
@endphp
<!doctype html>

<html lang="en" translate="no">

<head>

    <meta charset="utf-8" />
    <title>{{$mi_empresa->nombre}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
    <!-- Select2 CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <!-- Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <meta content="notranslate">

    <style>
        /* Estilos Premium para el Dropdown de Reportes */
        .reports-dropdown-btn {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 6px !important;
            padding: 6px 16px !important;
            font-weight: 500 !important;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reports-dropdown-btn:hover {
            background-color: rgba(255, 255, 255, 0.15) !important;
            border-color: #00ffaa !important;
            box-shadow: 0 0 12px rgba(0, 255, 170, 0.3) !important;
            transform: translateY(-1px);
        }

        .reports-dropdown-btn:active {
            transform: translateY(0);
        }

        .reports-dropdown-btn.dropdown-toggle::after {
            display: none !important;
        }

        .reports-chevron {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .dropdown.show .reports-chevron,
        .reports-dropdown-btn[aria-expanded="true"] .reports-chevron {
            transform: rotate(180deg) !important;
        }

        .reports-dropdown-menu {
            background-color: #2a3b50 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 8px !important;
            min-width: 280px !important;
            padding: 8px 0 !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3) !important;
            transition: all 0.3s ease !important;
        }

        .reports-dropdown-menu .dropdown-item {
            color: rgba(255, 255, 255, 0.85) !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            padding: 8px 16px !important;
            transition: all 0.25s ease !important;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none !important;
        }

        .reports-dropdown-menu .dropdown-item i {
            font-size: 16px;
            transition: transform 0.25s ease !important;
        }

        .reports-dropdown-menu .dropdown-item:hover {
            background-color: rgba(0, 255, 170, 0.12) !important;
            color: #00ffaa !important;
            padding-left: 20px !important;
        }

        .reports-dropdown-menu .dropdown-item:hover i {
            transform: scale(1.2);
            color: #00ffaa !important;
        }

        .reports-dropdown-menu .dropdown-header {
            color: rgba(255, 255, 255, 0.5) !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding: 8px 16px 4px 16px !important;
        }

        .reports-dropdown-menu .dropdown-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
            margin: 6px 0 !important;
        }
    </style>

</head>

<body data-topbar="dark">

    <!-- Begin page -->
    <div id="layout-wrapper">

        {{-- Root único de Vue: header + menú + contenido comparten el mismo router.
             AppHeader.vue y AppMenu.vue reemplazan el header y el @include('menu')
             estáticos — sus enlaces internos ahora son router-link, no <a href>. --}}
        <div id="app">
            <app-header></app-header>
            <app-menu></app-menu>

            <div class="main-content">
                <router-view></router-view>
            </div>
        </div>

    </div>


    <!-- END layout-wrapper -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>

    <!-- Peity JS -->
    <script src="assets/libs/peity/jquery.peity.min.js"></script>

    <script src="assets/libs/morris.js/morris.min.js"></script>
    <script src="assets/libs/raphael/raphael.min.js"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
    <!-- Incluye el JavaScript de Bootstrap (Popper.js y Bootstrap JS) -->
    <script src="assets/js/boostrap5.2.js" crossorigin="anonymous"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @vite(['resources/js/app.js'])

</body>

</html>
