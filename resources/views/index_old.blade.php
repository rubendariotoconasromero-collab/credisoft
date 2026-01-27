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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDE-0UOTWtyaeBaTj4WYrA1mHmj5uUCGmw&libraries=places"></script>

    <meta content="notranslate">
    <style>
        .navbar-nav .nav-item .nav-link.active {
            background-color: #ededed;
            color: white;
        }
    </style>

    <style ref="css">
        body {
            font-family: "Roboto Condensed", sans-serif !important;
            font-optical-sizing: auto !important;
            font-weight: 300 !important;
            font-style: normal !important;
        }
    </style>

    <style scoped>
    /* Estilos para el perfil de usuario */
    .user-profile-dropdown {
        display: flex;
        align-items: center;
        position: relative;
        padding: 0.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .user-profile-dropdown:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .user-info {
        margin-right: 0;
        text-align: right;
        max-width: 160px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    
    .user-name {
        font-size: 0.9rem;
        font-weight: 500;
        color: #fff;
        margin-bottom: 2px;
        letter-spacing: 0.2px;
        opacity: 0.95;
    }
    
    .user-role {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 400;
        display: block;
        text-transform: capitalize;
    }
    
    .profile-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }
    
    .user-profile-dropdown:hover .profile-avatar {
        border-color: #198754;
    }
    
    .dropdown-toggle::after {
        display: none;  /* Ocultar flecha por defecto del dropdown */
    }
    
    .dropdown-menu {
        padding: 0.5rem 0;
        min-width: 14rem;
        border: none;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        margin-top: 0.5rem;
    }
    
    .dropdown-header {
        background-color: #f8f9fa;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    
    .dropdown-header-user {
        display: flex;
        align-items: center;
    }
    
    .dropdown-header-user img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        margin-right: 12px;
    }
    
    .dropdown-header-info {
        flex: 1;
    }
    
    .dropdown-header-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #343a40;
        margin-bottom: 2px;
    }
    
    .dropdown-header-role {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .dropdown-item {
        padding: 0.7rem 1.5rem;
        color: #495057;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }
    
    .dropdown-item i {
        margin-right: 10px;
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
    }
    
    .dropdown-item:hover {
        background-color: rgba(25, 135, 84, 0.05);
        color: #198754;
    }
    
    .dropdown-item:hover i {
        color: #198754;
    }
    
    .dropdown-item.text-danger:hover {
        background-color: rgba(220, 53, 69, 0.05);
        color: #dc3545;
    }
    
    .dropdown-item.text-danger:hover i {
        color: #dc3545;
    }
    
    .dropdown-divider {
        margin: 0.5rem 0;
    }
    
    @media (max-width: 767.98px) {
        .user-info {
            display: none;
        }
    }
</style>

</head>

<body data-topbar="colored" data-layout="horizontal">

    <!-- Begin page -->
    <div id="layout-wrapper">

        <header id="page-topbar" class="bg-dark">
            <div class="navbar-header m-0">
                <div class="d-flex">

                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="/" class="logo logo-dark ">
                            <span class="logo-sm">
                                <img src="img/logo_credisoft_sm.png" alt="" height="25">
                            </span>
                            <span class="logo-lg">
                                {{-- <img src="img/{{empty($mi_empresa->logo)?'logo_sistema_codesoft.png':$mi_empresa->logo}}" alt="" height="55"> --}}
                                <img src="img/logo_sistema.png" alt="" height="45">
                            </span>
                        </a>

                        <a href="/" class="logo logo-light ">
                            <span class="logo-sm">
                                <img src="img/logo_sistema.png" alt="" height="25">
                            </span>
                            <span class="logo-lg">
                                {{-- <img src="img/{{empty($mi_empresa->logo)?'logo_sistema_codesoft.png':$mi_empresa->logo}}" alt="" height="55"> --}}
                                <img src="img/logo_sistema.png" alt="" height="45">

                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-1 font-size-16 d-lg-none header-item waves-effect waves-light"
                        data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                        <i class="mdi mdi-menu"></i>
                    </button>



                </div>

                <div class="d-flex">

                
                    <div class="dropdown d-inline-block">
                        <!-- Botón de perfil mejorado con nombre de usuario y rol -->
                        <div class="user-profile-dropdown" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="user-info mx-2">
                                <div class="user-name">{{ auth()->user()->name }}</div>
                                <span class="user-role">
                                    {{
                                        DB::table('rol')
                                        ->join('users', 'rol.id', '=', 'users.id_rol')
                                        ->where('users.id', auth()->user()->id)
                                        ->value('rol.nombre')
                                    }}
                                </span>
                            </div>
                            <img class="profile-avatar" src="img/empresa/user_img2.png" alt="Foto de perfil">
                            <i class="fas fa-chevron-down ms-1" style="font-size: 10px; color: rgba(255,255,255,0.7);"></i>
                        </div>
                        
                        <!-- Menú desplegable mejorado -->
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- Cabecera con información del usuario -->
                            <div class="dropdown-header">
                                <div class="dropdown-header-user">
                                    <img src="img/empresa/user_img2.png" alt="Foto de perfil">
                                    <div class="dropdown-header-info">
                                        <div class="dropdown-header-name">{{ auth()->user()->name }}</div>
                                        <div class="dropdown-header-role">
                                            {{
                                                DB::table('rol')
                                                ->join('users', 'rol.id', '=', 'users.id_rol')
                                                ->where('users.id', auth()->user()->id)
                                                ->value('rol.nombre')
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="dropdown-divider"></div>
                            
                            <!-- Botón de cerrar sesión -->
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-power-off"></i>
                                <span>Cerrar sesión</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Navigation -->
            <div class="topnav mt-0">
                <div class="container-fluid">
                    <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                        <div class="collapse navbar-collapse" id="topnav-menu-content">
                            <ul class="navbar-nav">
                                @include('menu') <!-- Incluye el menú horizontal aquí -->
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </header>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            @yield('content')

            
        </div>
        <!-- end main content-->
        
    </div>
    {{-- @include('footer') --}}
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
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script> --}}

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @vite(['resources/js/app.js'])

</body>

</html>
