<style scoped>
    /* Estilos base optimizados */
    .nav-link, .dropdown-item {
        color: #3c3c3c;
        transition: all 0.3s ease;
        position: relative;
    }
    
    /* Efecto hover mejorado */
    .nav-link:hover, 
    .dropdown-item:hover {
        color: #198754 !important;
    }
    
    .nav-link:hover > i,
    .dropdown-item:hover > i {
        color: #198754 !important;
    }
    
    /* Estilo para elementos activos - más específico */
    .nav-link.active,
    .navbar-nav .nav-item .nav-link.active,
    .navbar-nav .nav-item.active > .nav-link,
    .navbar-nav .nav-item.show > .nav-link,
    .dropdown-menu .dropdown-item.active {
        color: #198754 !important;
        font-weight: 500;
    }
    
    /* Iconos en elementos activos */
    .nav-link.active > i,
    .dropdown-item.active > i,
    .nav-item.show > .nav-link > i {
        color: #198754 !important;
    }
    
    /* Dropdown toggle abierto - estilo verde */
    .nav-item.dropdown.show > .nav-link,
    .nav-item.dropdown.show > .nav-link > i {
        color: #198754 !important;
    }
    
    /* Estilo para dropdown items activos */
    .dropdown-menu .dropdown-item.active {
        background-color: rgba(25, 135, 84, 0.08) !important;
    }
    
    /* Indicador visual para items activos en dropdown */
    .dropdown-menu .dropdown-item.active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background-color: #198754;
    }
    
    /* Dropdown hover mejorado */
    .dropdown-item:hover {
        background-color: rgba(25, 135, 84, 0.05) !important;
    }
    
    /* Animación para flecha del dropdown */
    .arrow-down {
        display: inline-block;
        transition: transform 0.3s ease;
        margin-left: 4px;
    }
    
    .nav-item.dropdown.show .arrow-down {
        transform: rotate(180deg);
    }
    
    /* Mejora visual para el dropdown */
    .dropdown-menu {
        border: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        padding: 0.5rem 0;
        margin-top: 0.5rem;
    }
    
    /* Espaciado mejorado para items del dropdown */
    .dropdown-item {
        padding: 0.5rem 1.5rem;
    }
</style>

@php
    use App\Http\Controllers\LoginController;
    $login_controller = new LoginController();
    $current_route = request()->path();
@endphp

<ul class="navbar-nav">
    @if($login_controller->permisoSistema('paneladministracion', auth()->user()->id_rol))
    <li class="nav-item">
        <a href="/administracion" class="nav-link {{ $current_route == 'administracion' ? 'active' : '' }}">
            <i class="fas fa-info-circle me-1"></i>
            Gráficos
        </a>
    </li>
    @endif
    
    @if($login_controller->permisoSistema('controlcaja', auth()->user()->id_rol))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle {{ str_starts_with($current_route, 'caja') || str_starts_with($current_route, 'historial_') ? 'active' : '' }}" 
           href="#" id="topnav-controlcaja" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-cash-register me-1"></i>
            Caja
            <span class="arrow-down ms-1"></span>
        </a>
        <div class="dropdown-menu" aria-labelledby="topnav-controlcaja">
            <a class="dropdown-item {{ $current_route == 'caja' ? 'active' : '' }}" href="/caja">
                <i class="fas fa-cash-register me-1"></i>Control caja
            </a>
            <a class="dropdown-item {{ $current_route == 'historial_desembolsos_pagos' ? 'active' : '' }}" href="/historial_desembolsos_pagos">
                <i class="fas fa-history me-1"></i>H. Desembolsos y Pagos
            </a>
            {{-- <a class="dropdown-item {{ $current_route == 'historial_ingresos' ? 'active' : '' }}" href="/historial_ingresos">
                <i class="fas fa-money-bill-wave me-1"></i>Historial Ingresos
            </a> --}}
            <a class="dropdown-item {{ $current_route == 'historial_gastos' ? 'active' : '' }}" href="/historial_gastos">
                <i class="fas fa-money-bill-wave-alt me-1"></i>Historial Ingresos y Gastos
            </a>
            <a class="dropdown-item {{ $current_route == 'historial_pagos' ? 'active' : '' }}" href="/historial_pagos">
                <i class="fas fa-money-check-alt me-1"></i>Historial Pagos
            </a>
        </div>
    </li>
    @endif
    
    @if($login_controller->permisoSistema('cliente', auth()->user()->id_rol))
    <li class="nav-item">
        <a href="/clientes" class="nav-link {{ $current_route == 'clientes' ? 'active' : '' }}">
            <i class="fas fa-user-tie me-1"></i>
            Clientes
        </a>
    </li>
    @endif
    
    @if($login_controller->permisoSistema('codeudores', auth()->user()->id_rol))
    <li class="nav-item">
        <a href="/codeudores" class="nav-link {{ $current_route == 'codeudores' ? 'active' : '' }}">
            <i class="fas fa-user-tie me-1"></i>
            Codeudores
        </a>
    </li>
    @endif
    
    @if($login_controller->permisoSistema('solicitudprestamos', auth()->user()->id_rol))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle {{ in_array($current_route, ['solicitud', 'plan_pago', 'pago']) ? 'active' : '' }}" 
           href="#" id="topnav-gestionprestamos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-file-invoice-dollar me-1"></i>
            Gest. Préstamos
            <span class="arrow-down ms-1"></span>
        </a>
        <div class="dropdown-menu" aria-labelledby="topnav-gestionprestamos">
            <a class="dropdown-item {{ $current_route == 'solicitud' ? 'active' : '' }}" href="/solicitud">
                <i class="fas fa-file-signature me-1"></i>Solicitud préstamo
            </a>
            <a class="dropdown-item {{ $current_route == 'plan_pago' ? 'active' : '' }}" href="/plan_pago">
                <i class="fas fa-tasks me-1"></i>Gestión cartera
            </a>
            <a class="dropdown-item {{ $current_route == 'pago' ? 'active' : '' }}" href="/pago">
                <i class="fas fa-list-alt me-1"></i>Listado de pagos
            </a>
        </div>
    </li>
    @endif
    
    @if($login_controller->permisoSistema('informacion', auth()->user()->id_rol))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle {{ in_array($current_route, ['informacion', 'usuarios', 'roles', 'configuracion', 'boveda']) ? 'active' : '' }}" 
           href="#" id="topnav-administracion" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-cogs me-1"></i>
            Administración
            <span class="arrow-down ms-1"></span>
        </a>
        <div class="dropdown-menu" aria-labelledby="topnav-administracion">
            <a class="dropdown-item {{ $current_route == 'informacion' ? 'active' : '' }}" href="/informacion">
                <i class="fas fa-building me-1"></i>Información Empresa
            </a>
            <a class="dropdown-item {{ $current_route == 'usuarios' ? 'active' : '' }}" href="/usuarios">
                <i class="fas fa-users me-1"></i>Gestión de usuarios
            </a>
            <a class="dropdown-item {{ $current_route == 'roles' ? 'active' : '' }}" href="/roles">
                <i class="fas fa-user-tag me-1"></i>Roles
            </a>
            <a class="dropdown-item {{ $current_route == 'configuracion' ? 'active' : '' }}" href="/configuracion">
                <i class="fas fa-cog me-1"></i>Configuración
            </a>
            <a class="dropdown-item {{ $current_route == 'boveda' ? 'active' : '' }}" href="/boveda">
                <i class="fas fa-wallet me-1"></i>Boveda
            </a>
        </div>
    </li>
    @endif
    
    @if($login_controller->permisoSistema('reportes', auth()->user()->id_rol))
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle {{ str_starts_with($current_route, 'index_') || in_array($current_route, ['reportes', 'hist_credito_mora', 'cliente_mora']) ? 'active' : '' }}" 
           href="#" id="topnav-reportes" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-chart-bar me-1"></i>
            Reportes
            <span class="arrow-down ms-1"></span>
        </a>
        <div class="dropdown-menu" aria-labelledby="topnav-reportes">
            <a class="dropdown-item {{ $current_route == 'reportes' ? 'active' : '' }}" href="/reportes">
                <i class="fas fa-file-alt me-1"></i>Reportes
            </a>
            <a class="dropdown-item {{ $current_route == 'index_rep_extracto' ? 'active' : '' }}" href="/index_rep_extracto">
                <i class="fas fa-file-invoice me-1"></i>Extracto de crédito
            </a>
            <a class="dropdown-item {{ $current_route == 'hist_credito_mora' ? 'active' : '' }}" href="/hist_credito_mora">
                <i class="fas fa-exclamation-circle me-1"></i>Hist. Crédito Mora
            </a>
            <a class="dropdown-item {{ $current_route == 'cliente_mora' ? 'active' : '' }}" href="/cliente_mora">
                <i class="fas fa-user-clock me-1"></i>Clientes en Mora
            </a>
            <a class="dropdown-item {{ $current_route == 'index_rep_pagos_realizados' ? 'active' : '' }}" href="/index_rep_pagos_realizados">
                <i class="fas fa-money-check me-1"></i>Pagos Realizados
            </a>
            <a class="dropdown-item {{ $current_route == 'index_pagos_programados' ? 'active' : '' }}" href="/index_pagos_programados">
                <i class="fas fa-calendar-alt me-1"></i>Pagos Programados
            </a>
            <a class="dropdown-item {{ $current_route == 'index_movimientos_credito' ? 'active' : '' }}" href="/index_movimientos_credito">
                <i class="fas fa-exchange-alt me-1"></i>Extracto movimientos
            </a>
            <a class="dropdown-item {{ $current_route == 'index_porcentajes_pagos' ? 'active' : '' }}" href="/index_porcentajes_pagos">
                <i class="fas fa-percentage me-1"></i>Porcentaje pagos
            </a>
            <a class="dropdown-item {{ $current_route == 'index_desembolsos' ? 'active' : '' }}" href="/index_desembolsos">
                <i class="fas fa-hand-holding-usd me-1"></i>Desembolsos
            </a>
            <a class="dropdown-item {{ $current_route == 'index_desembolsos_oficial' ? 'active' : '' }}" href="/index_desembolsos_oficial">
                <i class="fas fa-user-tie me-1"></i>Desembolsos por oficial
            </a>
            <a class="dropdown-item {{ $current_route == 'index_desembolsos_pendientes' ? 'active' : '' }}" href="/index_desembolsos_pendientes">
                <i class="fas fa-clock me-1"></i>Desembolsos pendientes
            </a>
        </div>
    </li>
    @endif
</ul>