@php
    use App\Http\Controllers\LoginController;
    $login_controller = new LoginController();
    $current_route = request()->path();
@endphp

<style scoped>
    /* Estilos base para fondo oscuro */
    .vertical-menu {
        background-color: #1e2a38 !important;
        color: #ffffff;
    }

    .metismenu {
        padding: 0;
    }

    /* Estilos para menu-title */
    .menu-title {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.85rem;
        text-transform: uppercase;
        padding: 1rem 1.5rem;
        letter-spacing: 0.05em;
    }

    /* Estilos base para los enlaces */
    .nav-link, .sub-menu a {
        color: rgba(255, 255, 255, 0.9) !important;
        
        transition: all 0.3s ease;
        position: relative;
        padding: 0.75rem 1.5rem;
        display: flex;
        align-items: center;
        font-size: 0.95rem;
    }

    /* Iconos */
    .nav-link i, .sub-menu a i {
        color: rgba(255, 255, 255, 0.75);
        margin-right: 1rem;
        font-size: 1.1rem;
        transition: color 0.1s ease;
        
    }

    /* Efecto hover mejorado */
    .nav-link:hover, 
    .sub-menu a:hover {
        color: #00ffaa !important;
        background-color: rgb(54, 141, 112) !important;
    }

    .nav-link:hover > i,
    .sub-menu a:hover > i {
        color: #00ffaa !important;
    }

    /* Estilo para elementos activos */
    .mm-active > .nav-link,
    .sub-menu a.mm-active {
        color: #00ffaa !important;
        background-color: rgb(54, 141, 112) !important;
        font-weight: 500;
    }

    /* Iconos en elementos activos */
    .mm-active > .nav-link > i,
    .sub-menu a.mm-active > i {
        color: #00ffaa !important;
    }


    .sub-menu a {
        text-decoration: none; /* Añade esto */
    }

    .sub-menu a.mm-active {
        text-decoration: none !important;
    }
    /* Dropdown toggle abierto */
    .has-arrow.mm-active,
    .has-arrow.show {
        color: #00ffaa !important;
        background-color: rgb(54, 141, 112) !important;
    }

    .mm-active > .nav-link{
        color: #00ffaa !important; /* Este color ya es bueno, pero puedes cambiarlo si lo deseas */
        background-color: rgb(54, 141, 112) !important;
        font-weight: 500;
    }

    .sub-menu a.mm-active {
        color: #00ffaa !important; /* Mismo color, pero puedes cambiarlo */
        background-color: rgb(54, 141, 112) !important;
        text-decoration: none !important;
    }
    .has-arrow.mm-active > i,
    .has-arrow.show > i {
        color: #00ffaa !important;
    }

    /* Submenú */
    .sub-menu {
        background-color: #2a3b50;
        padding-left: 1.5rem;
    }

    .sub-menu a {
        padding: 0.5rem 1.5rem 0.5rem 2.5rem;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.85) !important;
    }

    /* Submenú activo */
    .sub-menu a.mm-active {
        color: #00ffaa !important;
        background-color: rgb(54, 141, 112) !important;
    }

    .sub-menu a.mm-active > i {
        color: #00ffaa !important;
    }

    /* Indicador visual para items activos */
    .mm-active > .nav-link::before,
    .sub-menu a.mm-active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background-color: #00ffaa;
    }

    /* Animación para flecha del dropdown */
   



    /* User details section */
    .user-details {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    }

    .user-info .dropdown-toggle {
        color: #ffffff !important;
        font-size: 1.1rem;
        font-weight: 500;
    }

    .user-info .dropdown-toggle:hover {
        color: #00ffaa !important;
    }

    .user-info .dropdown-menu {
        background-color: #2a3b50;
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        border-radius: 6px;
        padding: 0.5rem 0;
    }

    .user-info .dropdown-item {
        color: rgba(255, 255, 255, 0.9) !important;
        padding: 0.5rem 1.5rem;
    }

    .user-info .dropdown-item:hover {
        background-color: rgba(0, 255, 170, 0.15) !important;
        color: #00ffaa !important;
    }

    .user-info .dropdown-item i {
        color: rgba(255, 255, 255, 0.75);
    }

    .user-info .dropdown-item:hover i {
        color: #00ffaa !important;
    }

    /* Scrollbar */
    [data-simplebar] {
        scrollbar-width: thin;
        scrollbar-color: #00ffaa #2a3b50;
    }

    [data-simplebar]::-webkit-scrollbar {
        width: 6px;
    }

    [data-simplebar]::-webkit-scrollbar-track {
        background: #2a3b50;
    }

    [data-simplebar]::-webkit-scrollbar-thumb {
        background: #00ffaa;
        border-radius: 3px;
    }
</style>

<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div class="user-details">
            <div class="d-flex">
                <div class="me-2">
                    <img src="assets/images/users/avatar-4.jpg" alt="" class="avatar-md rounded-circle">
                </div>
                <div class="user-info w-100">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ auth()->user()->name }}
                            <i class="mdi mdi-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="javascript:void(0)" class="dropdown-item"><i class="mdi mdi-power text-muted me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                    <p class="text-white-50 m-0">{{ auth()->user()->role_name ?? 'Administrasaubtor' }}</p>
                </div>
            </div>
        </div>

        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                @if($login_controller->permisoSistema('paneladministracion', auth()->user()->id_rol))
                <li class="menu-title">Menu</li>
                <li>
                    <a href="/administracion" class="nav-link waves-effect {{ $current_route == 'administracion' ? 'mm-active' : '' }}">
                        <i class="fas fa-info-circle"></i>
                        <span>Gráficos</span>
                    </a>
                </li>
                @endif

                @if($login_controller->permisoSistema('controlcaja', auth()->user()->id_rol))
                <li>
                    <a href="javascript: void(0);" class="nav-link has-arrow waves-effect {{ str_starts_with($current_route, 'caja') || str_starts_with($current_route, 'historial_') ? 'mm-active' : '' }}">
                        <i class="fas fa-cash-register"></i>
                        <span>Caja</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="/caja" class="{{ $current_route == 'caja' ? 'mm-active' : '' }}">Control caja</a></li>
                        <li><a href="/historial_desembolsos_pagos" class="{{ $current_route == 'historial_desembolsos_pagos' ? 'mm-active' : '' }}">Historial Desembolsos y Pagos Administrativos</a></li>
                        <li><a href="/historial_gastos" class="{{ $current_route == 'historial_gastos' ? 'mm-active' : '' }}">Historial Ingresos y Gastos corrientes</a></li>
                        <li><a href="/historial_pagos" class="{{ $current_route == 'historial_pagos' ? 'mm-active' : '' }}">Historial Pagos de Cuotas</a></li>
                    </ul>
                </li>
                @endif

                @if($login_controller->permisoSistema('cliente', auth()->user()->id_rol))
                <li>
                    <a href="/clientes" class="nav-link waves-effect {{ $current_route == 'clientes' ? 'mm-active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Clientes</span>
                    </a>
                </li>
                @endif

                @if($login_controller->permisoSistema('codeudores', auth()->user()->id_rol))
                <li>
                    <a href="/codeudores" class="nav-link waves-effect {{ $current_route == 'codeudores' ? 'mm-active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Codeudores</span>
                    </a>
                </li>
                @endif

                @if($login_controller->permisoSistema('consultasfinancieras', auth()->user()->id_rol))
                <li>
                    <a href="/consultas_financieras" class="nav-link waves-effect {{ $current_route == 'consultas_financieras' ? 'mm-active' : '' }}">
                        <i class="fas fa-search-dollar"></i>
                        <span>Adm. Financiera</span>
                    </a>
                </li>
                @endif

            
                @if($login_controller->permisoSistema('solicitudprestamos', auth()->user()->id_rol))
                <li>
                    <a href="javascript: void(0);" class="nav-link has-arrow waves-effect {{ in_array($current_route, ['solicitud', 'plan_pago', 'pago']) ? 'mm-active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Gest. Préstamos</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="/solicitud" class="{{ $current_route == 'solicitud' ? 'mm-active' : '' }}">Solicitud préstamo</a></li>
                        <li><a href="/plan_pago" class="{{ $current_route == 'plan_pago' ? 'mm-active' : '' }}">Gestión cartera</a></li>
                    </ul>
                </li>
                @endif

                @if($login_controller->permisoSistema('informacion', auth()->user()->id_rol))
                <li>
                    <a href="javascript: void(0);" class="nav-link has-arrow waves-effect {{ in_array($current_route, ['informacion', 'usuarios', 'roles', 'configuracion', 'boveda']) ? 'mm-active' : '' }}">
                        <i class="fas fa-cogs"></i>
                        <span>Administración</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="/informacion" class="{{ $current_route == 'informacion' ? 'mm-active' : '' }}">Información Empresa</a></li>
                        <li><a href="/usuarios" class="{{ $current_route == 'usuarios' ? 'mm-active' : '' }}">Gestión de usuarios</a></li>
                        <li><a href="/socio" class="{{ $current_route == 'socio' ? 'mm-active' : '' }}">Gestión de socios</a></li>
                        <li><a href="/roles" class="{{ $current_route == 'roles' ? 'mm-active' : '' }}">Roles</a></li>
                        <li><a href="/configuracion" class="{{ $current_route == 'configuracion' ? 'mm-active' : '' }}">Configuración</a></li>
                        <li><a href="/boveda" class="{{ $current_route == 'boveda' ? 'mm-active' : '' }}">Boveda</a></li>
                    </ul>
                </li>
                @endif

                @if($login_controller->permisoSistema('reportes', auth()->user()->id_rol))
                <li>
                    <a href="javascript: void(0);" class="nav-link has-arrow waves-effect {{ str_starts_with($current_route, 'index_') || in_array($current_route, ['reportes', 'hist_credito_mora', 'cliente_mora']) ? 'mm-active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="/reportes" class="{{ $current_route == 'reportes' ? 'mm-active' : '' }}">Reportes</a></li>
                        <li><a href="/index_rep_extracto" class="{{ $current_route == 'index_rep_extracto' ? 'mm-active' : '' }}">Extracto de crédito</a></li>
                        <li><a href="/hist_credito_mora" class="{{ $current_route == 'hist_credito_mora' ? 'mm-active' : '' }}">Hist. Crédito Mora</a></li>
                        <li><a href="/cliente_mora" class="{{ $current_route == 'cliente_mora' ? 'mm-active' : '' }}">Clientes en Mora</a></li>
                        <li><a href="/index_rep_pagos_realizados" class="{{ $current_route == 'index_rep_pagos_realizados' ? 'mm-active' : '' }}">Pagos Realizados</a></li>
                        <li><a href="/index_pagos_programados" class="{{ $current_route == 'index_pagos_programados' ? 'mm-active' : '' }}">Pagos Programados</a></li>
                        <li><a href="/index_movimientos_credito" class="{{ $current_route == 'index_movimientos_credito' ? 'mm-active' : '' }}">Extracto movimientos</a></li>
                        <li><a href="/index_porcentajes_pagos" class="{{ $current_route == 'index_porcentajes_pagos' ? 'mm-active' : '' }}">Porcentaje pagos</a></li>
                        <li><a href="/index_desembolsos" class="{{ $current_route == 'index_desembolsos' ? 'mm-active' : '' }}">Desembolsos</a></li>
                        <li><a href="/index_desembolsos_oficial" class="{{ $current_route == 'index_desembolsos_oficial' ? 'mm-active' : '' }}">Desembolsos por oficial</a></li>
                        <li><a href="/index_desembolsos_pendientes" class="{{ $current_route == 'index_desembolsos_pendientes' ? 'mm-active' : '' }}">Desembolsos pendientes</a></li>
                    </ul>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>