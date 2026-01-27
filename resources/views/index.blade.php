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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDE-0UOTWtyaeBaTj4WYrA1mHmj5uUCGmw&libraries=places"></script>

    <meta content="notranslate">
   
</head>

<body data-topbar="dark">

    <!-- Begin page -->
    <div id="layout-wrapper">

        @php
            use App\Http\Controllers\LoginController;
            $login_controller = new LoginController();
            $current_route = request()->path();
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

                    <!-- Create New Dropdown -->
                    {{-- <div class="dropdown d-none d-lg-inline-block align-self-center">
                        <button class="btn btn-header waves-effect dropdown-toggle" type="button" id="createNewDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Create New<i class="mdi mdi-chevron-down ms-2"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="createNewDropdown">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                            <li><div class="dropdown-divider"></div></li>
                            <li><a class="dropdown-item" href="#">Separated link</a></li>
                        </ul>
                    </div> --}}
                </div>

                <div class="d-flex">
                    <!-- Search Dropdown (Mobile) -->
                    {{-- <div class="dropdown d-inline-block d-lg-none ms-2">
                        <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
                            <div class="p-3">
                                <div class="form-group m-0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <!-- App Search -->
                    {{-- <div class="app-search d-none d-lg-block">
                        <div class="position-relative">
                            <input type="text" class="form-control" placeholder="Search...">
                            <span class="mdi mdi-magnify"></span>
                        </div>
                    </div> --}}

                    <!-- Notification Dropdown -->
                    {{-- <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="mdi mdi-bell"></i>
                            <span class="badge bg-info rounded-pill">3</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">
                            <h5 class="p-3 text-dark mb-0">Notifications (37)</h5>
                            <div data-simplebar style="max-height: 230px;">
                                <a href="" class="text-reset notification-item">
                                    <div class="d-flex mt-3">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-success rounded-circle font-size-16">
                                                <i class="mdi mdi-cart"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">Your order is placed</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1">If several languages coalesce the grammar</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <a href="" class="text-reset notification-item">
                                    <div class="d-flex mt-3">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-warning rounded-circle font-size-16">
                                                <i class="mdi mdi-message"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">New Message received</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1">You have 87 unread messages</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <a href="" class="text-reset notification-item">
                                    <div class="d-flex mt-3">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-info rounded-circle font-size-16">
                                                <i class="mdi mdi-flag"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">Your item is shipped</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1">If several languages coalesce the grammar</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2 d-grid">
                                <a class="font-size-14 text-center" href="javascript:void(0)">View all</a>
                            </div>
                        </div>
                    </div> --}}

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
                                    {{-- <img height="22" src="img/user_img.png" alt="Foto de perfil"> --}}
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
                            {{-- <a class="dropdown-item" href="#"><i class="mdi mdi-wrench font-size-16 align-middle me-2 text-muted"></i><span>Settings</span></a>
                            <a class="dropdown-item" href="#"><i class="mdi mdi-lock-open-outline font-size-16 align-middle me-2 text-muted"></i><span>Lock screen</span></a> --}}
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

                    <!-- Settings -->
                    {{-- <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                            <i class="mdi mdi-cog bx-spin"></i>
                        </button>
                    </div> --}}
                </div>
            </div>
        </header>

        @include('menu')

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            @yield('content')

            
            {{-- <footer class="footer">
                © 2025 <span class="d-none d-sm-inline-block"> INNOVASOFT </span>
            </footer>
             --}}
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
