<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Login | Credisoft</title>
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

</head>

<body data-topbar="colored">

    <!-- <body data-layout="horizontal" data-topbar="colored"> -->

    <!-- Background -->
    <div class="account-pages"></div>
    <div class="wrapper-page">
        <div class="card">
            <div class="card-body">

                <div class="auth-logo">
                    <h3 class="text-center">
                        <a href="#" class="logo d-block my-1">
                            {{-- <img src="assets/images/logo-dark.png" class="logo-dark mx-auto" height="30" alt="logo-dark"> --}}
                            {{-- <img src="assets/images/logo-light.png" class="logo-light mx-auto" height="30" alt="logo-light"> --}}
                            {{-- logo oscuro --}}

                            {{-- logo claro --}}
                            <img src="img/{{empty(DB::table('mi_empresa')->get()[0]->logo)?'logo_sistema_codesoft.png':DB::table('mi_empresa')->get()[0]->logo}}" class="logo-dark mx-auto" style="width:90%"
                                alt="logo-dark">

                            <img src="img/{{empty(DB::table('mi_empresa')->get()[0]->logo)?'logo_sistema_codesoft.png':DB::table('mi_empresa')->get()[0]->logo}}" class="logo-light mx-auto" style="width:90%"
                                alt="logo-light">

                        </a>
                    </h3>
                </div>
                @if(session('mensaje'))
                <div class="alert alert-warning alert-dismissible fade show bg-info text-white" role="alert">
                    {{session('mensaje')}}.
                    {{-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button> --}}
                </div>
                @endif
                <div class="p-3">
                    {{-- <h4 class="text-muted font-size-18 text-center">Welcome Back !</h4> --}}
                    <p class="text-muted text-center">Inicia sesión para ingresar.</p>

                    <form class="form-horizontal" action="/login_process" method="post">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="username">Usuario</label>
                            <div class="input-group border border-1 border-success rounded">
                                <a class="btn btn-success -text-white" style="cursor:default">
                                    <i class="fas fa-user"></i>
                                </a>
                                <input type="text" class="form-control" id="username" name="name"
                                    placeholder="ingrese usuario">
                            </div>
                                
                                
                          
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="userpassword">Contraseña</label>
                            <div class="input-group border border-1 border-success rounded">
                                <a class="btn btn-success -text-white" style="cursor:default">
                                    <i class="fas fa-lock"></i>
                                </a>
                            <input type="password" class="form-control" id="userpassword" name="password"
                                placeholder="Ingrese contraseña">
                            </div>
                        </div>

                        <div class="my-4 row">
                            {{-- <div class="col-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="customControlInline">
                                        <label class="form-check-label" for="customControlInline">Remember
                                            me</label>
                                    </div>
                                </div> --}}
                            <div class="col-12 text-end">
                                <button class="btn btn-success w-100 waves-effect waves-light text-uppercase"
                                    type="submit">
                                    <i class="fas fa-unlock-alt"></i>
                                    Ingresar</button>
                            </div>
                        </div>

                        {{-- <div class="mb-3 row">
                                <div class="col-12">
                                    <a href="pages-recoverpw.html" class="text-muted"><i class="mdi mdi-lock"></i> Forgot your
                                        password?</a>
                                </div>
                            </div> --}}
                    </form>
                </div>

            </div>
        </div>
        <div class="text-center">
            {{-- <p class="text-white-50">Don't have an account ? <a href="pages-register.html" class="text-white"> Signup Now
                    </a> </p> --}}
            <p class="text-muted">
                ©
                <script>
                    document.write(new Date().getFullYear())

                </script> CreditSoft <i class="mdi mdi-heart text-primary"></i> by
                CodeSoft & Innovadev
            </p>
        </div>
    </div>
    <!-- Begin page -->
    {{-- <div class="wrapper-page"> --}}
    {{-- <div class="row">
            <!-- Componente principal (izquierda) -->
            <div class="col-md-7">
                <!-- Contenido principal aquí -->
            </div>
            <!-- Componente card (derecha) -->
            <div class="col-md-5">
                
            </div>
        </div> --}}




    {{-- </div> --}}


    {{-- <!-- Right Sidebar -->
    <div class="right-bar">
        <div data-simplebar class="h-100">
            <div class="rightbar-title px-3 py-4">
                <a href="javascript:void(0);" class="right-bar-toggle float-end">
                    <i class="mdi mdi-close noti-icon"></i>
                </a>
                <h5 class="m-0">Settings</h5>
            </div>

            <!-- Settings -->
            <hr class="" />
            <h6 class="text-center mb-0">Choose Layouts</h6>

            <div class="p-4">
                <div class="mb-2">
                    <img src="assets/images/layouts/layout-1.png" class="img-fluid img-thumbnail" alt="">
                </div>

                <div class="form-check form-switch mb-3">
                    <input type="checkbox" class="form-check-input theme-choice" id="light-mode-switch" checked />
                    <label class="form-check-label" for="light-mode-switch">Light Mode</label>
                </div>

                <div class="mb-2">
                    <img src="assets/images/layouts/layout-2.png" class="img-fluid img-thumbnail" alt="">
                </div>

                <div class="form-check form-switch mb-3">
                    <input type="checkbox" class="form-check-input theme-choice" id="dark-mode-switch"
                        data-bsStyle="assets/css/bootstrap-dark.min.css" data-appStyle="assets/css/app-dark.min.css" />
                    <label class="form-check-label" for="dark-mode-switch">Dark Mode</label>
                </div>

                <div class="mb-2">
                    <img src="assets/images/layouts/layout-3.png" class="img-fluid img-thumbnail" alt="">
                </div>
                <div class="form-check form-switch mb-5">
                    <input type="checkbox" class="form-check-input theme-choice" id="rtl-mode-switch"
                        data-appStyle="assets/css/app-rtl.min.css" />
                    <label class="form-check-label" for="rtl-mode-switch">RTL Mode</label>
                </div>

                <h6 class="mb-2">Select Custom Colors</h6>

                <div class="form-check form-check-inline">
                    <input class="form-check-input theme-color" type="radio" name="theme-mode" id="theme-default"
                        value="default" onchange="document.documentElement.setAttribute('data-theme-mode', 'default')"
                        checked>
                    <label class="form-check-label" for="theme-default">Default</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input theme-color" type="radio" name="theme-mode" id="theme-red"
                        value="red" onchange="document.documentElement.setAttribute('data-theme-mode', 'red')">
                    <label class="form-check-label" for="theme-red">Red</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input theme-color" type="radio" name="theme-mode" id="theme-green"
                        value="green" onchange="document.documentElement.setAttribute('data-theme-mode', 'green')">
                    <label class="form-check-label" for="theme-green">Green</label>
                </div>
            </div>

        </div>
        <!-- end slimscroll-menu-->
    </div>
    <!-- /Right-bar -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div> --}}

    <!-- JAVASCRIPT -->
    <script src="assets/libs/jquery/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>

</html>
