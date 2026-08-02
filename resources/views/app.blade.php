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

        @php
            use App\Http\Controllers\LoginController;
            $login_controller = new LoginController();
        @endphp

        <header id="page-topbar">
            <div class="navbar-header" style="background-color: #1e2a38;">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="/" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="img/logo_sistema_sm.png" alt="" height="25">
                            </span>
                            <span class="logo-lg">
                                <img src="img/logo_sistema.png" alt="" height="40">
                            </span>
                        </a>

                        <a href="/" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="img/logo_sistema.png" alt="" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="img/logo_sistema.png" alt="" height="40">
                            </span>
                        </a>
                    </div>

                    <!-- Menu Icon -->
                    <button type="button" class="btn px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                        <i class="mdi mdi-menu"></i>
                    </button>


                </div>

                <div class="d-flex">

                    @if($login_controller->permisoSistema('reportes', auth()->user()->id_rol))
                    <!-- Dropdown de Reportes -->
                    <div class="dropdown d-none d-lg-inline-block align-self-center me-3">
                        <button class="btn reports-dropdown-btn waves-effect dropdown-toggle" type="button" id="reportsDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-bar-chart-alt-2" style="color: #00ffaa;"></i>
                            <span>Reportes</span>
                            <i class="mdi mdi-chevron-down reports-chevron font-size-14 opacity-75"></i>
                        </button>
                        <ul class="dropdown-menu reports-dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="reportsDropdownBtn" style="position: absolute; right: 0;">
                            <li class="dropdown-header">Reportes Generales</li>
                            <li>
                                <a class="dropdown-item" href="/reportes">
                                    <i class="bx bx-grid-alt" style="color: #38bdf8;"></i>
                                    <span>Panel de Reportes</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/index_rep_extracto">
                                    <i class="bx bx-receipt" style="color: #fbbf24;"></i>
                                    <span>Extracto de Crédito</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/hist_credito_mora">
                                    <i class="bx bx-time-five" style="color: #f87171;"></i>
                                    <span>Historial Crédito Mora</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/cliente_mora">
                                    <i class="bx bx-user-x" style="color: #ef4444;"></i>
                                    <span>Clientes en Mora</span>
                                </a>
                            </li>

                            <li><div class="dropdown-divider"></div></li>
                            <li class="dropdown-header">Transacciones y Pagos</li>
                            <li>
                                <a class="dropdown-item" href="/index_rep_pagos_realizados">
                                    <i class="bx bx-check-circle" style="color: #34d399;"></i>
                                    <span>Pagos Realizados</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/index_pagos_programados">
                                    <i class="bx bx-calendar" style="color: #60a5fa;"></i>
                                    <span>Pagos Programados</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/index_movimientos_credito">
                                    <i class="bx bx-transfer" style="color: #a78bfa;"></i>
                                    <span>Extracto de Movimientos</span>
                                </a>
                            </li>

                            <li><div class="dropdown-divider"></div></li>
                            <li class="dropdown-header">Porcentajes y Desembolsos</li>
                            <li>
                                <a class="dropdown-item" href="/index_porcentajes_pagos">
                                    <i class="bx bx-pie-chart-alt-2" style="color: #fb7185;"></i>
                                    <span>Porcentaje Pagos</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/index_desembolsos">
                                    <i class="bx bx-dollar-circle" style="color: #34d399;"></i>
                                    <span>Desembolsos</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/index_desembolsos_oficial">
                                    <i class="bx bx-user" style="color: #818cf8;"></i>
                                    <span>Desembolsos por Oficial</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/index_desembolsos_pendientes">
                                    <i class="bx bx-hourglass-top" style="color: #fbbf24;"></i>
                                    <span>Desembolsos Pendientes</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    <!-- User Dropdown -->
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="img/empresa/user_img2.png" alt="Foto de perfil">
                            <span class="d-none d-xl-inline-block ms-1 text-white">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down ms-1" style="font-size: 10px; color: rgba(255,255,255,0.7);"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="dropdown-header">
                                <div class="dropdown-header-user">
                                    <div class="dropdown-header-info">
                                        <div class="dropdown-header-name mt-2">
                                            <h6>
                                                {{ auth()->user()->name }}
                                                <span class="d-block fw-semibold text-dark mt-1">
                                                    ROL: {{
                                                        DB::table('rol')
                                                        ->join('users', 'rol.id', '=', 'users.id_rol')
                                                        ->where('users.id', auth()->user()->id)
                                                        ->value('rol.nombre')
                                                    }}
                                                </span>
                                            </h6>

                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/perfil"><i class="mdi mdi-account-circle font-size-16 align-middle me-2 text-muted"></i><span>Perfil</span></a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-primary" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="mdi mdi-power font-size-16 align-middle me-2 text-primary"></i><span>Salir</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Root único de Vue: menú + contenido comparten el mismo router --}}
        <div id="app">
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
    <script src="assets/js/app.js"></script>
    <!-- Incluye el JavaScript de Bootstrap (Popper.js y Bootstrap JS) -->
    <script src="assets/js/boostrap5.2.js" crossorigin="anonymous"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @vite(['resources/js/app.js'])

</body>

</html>
