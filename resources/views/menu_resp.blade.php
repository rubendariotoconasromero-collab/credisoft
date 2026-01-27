@php
  use App\Http\Controllers\LoginController;   

    $login_controller = new LoginController();
@endphp
<li>
    {{-- @if($login_controller->permisoSistema('paneladministracion', auth()->user()->id_rol)) --}}
    <li>
        <a href="/administracion" class="waves-effect">
            <i class="fas fa-info-circle"></i>
            <span>Panel informativo</span>
        </a>
    </li>
    {{-- @endif --}}

    @if($login_controller->permisoSistema('controlcaja', auth()->user()->id_rol))
    <li>
        <a href="/caja" class="waves-effect">
            <i class="fas fa-cash-register"></i>
            <span>Control de caja</span>
        </a>
    </li>
    @endif

    @if($login_controller->permisoSistema('informacion', auth()->user()->id_rol)
    || $login_controller->permisoSistema('roles', auth()->user()->id_rol)
    || $login_controller->permisoSistema('usuarios', auth()->user()->id_rol))
    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="fas fa-cogs"></i>
            <span>Administracion</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @if($login_controller->permisoSistema('informacion', auth()->user()->id_rol))
            <li><a href="/informacion">
                <i class="fas fa-building"></i>
                Informacion</a>
            </li>
            @endif
            @if($login_controller->permisoSistema('usuarios', auth()->user()->id_rol))
            <li><a href="/usuarios">
                <i class="fas fa-users-cog"></i>
                Gestión de usuarios</a>
            </li>
            @endif
            @if($login_controller->permisoSistema('roles', auth()->user()->id_rol))
            <li><a href="/roles">
                <i class="fas fa-user-tag"></i>
                Roles</a>
            </li>
            @endif
        </ul>
    </li>
    @endif
   
    @if($login_controller->permisoSistema('cliente', auth()->user()->id_rol))
    <li>
        <a href="/clientes" class="waves-effect">
            <i class="fas fa-user-tie"></i>
            <span>Gestión clientes</span>
        </a>
    </li>
    @endif

    @if($login_controller->permisoSistema('cliente', auth()->user()->id_rol))
    <li>
        <a href="/codeudores" class="waves-effect">
            <i class="fas fa-user-tie"></i>
            <span>Gestión garantes</span>
        </a>
    </li>
    @endif

    @if($login_controller->permisoSistema('solicitudprestamos', auth()->user()->id_rol))
    <li>
        <a href="/solicitud" class="waves-effect">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Solicitud prestamo</span>
        </a>
    </li>
    @endif

    
    @if($login_controller->permisoSistema('planpagos', auth()->user()->id_rol))
    <li>
        <a href="/plan_pago" class="waves-effect">
            <i class="fas fa-credit-card"></i>
            <span>Gestión cartera</span>
        </a>
    </li>
    @endif

    @if($login_controller->permisoSistema('listadopagos', auth()->user()->id_rol))
    <li>
        <a href="/pago" class="waves-effect">
            <i class="fas fa-money-check-alt"></i>
            <span>Listado de pagos</span>
        </a>
    </li>
    @endif
    @if($login_controller->permisoSistema('reportes', auth()->user()->id_rol))
    <li>
        <a href="/reportes" class="waves-effect">
            <i class="fas fa-chart-bar"></i>
            <span>Reportes</span>
        </a>
    </li>
    @endif
    @if($login_controller->permisoSistema('estado_resultados', auth()->user()->id_rol))
    <li>
        <a href="/estado_resultados" class="waves-effect">
            <i class="fas fa-chart-line"></i>
            <span>Estado resultados</span>
        </a>
    </li>
    @endif

</li>