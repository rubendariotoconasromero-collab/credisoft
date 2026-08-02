<template>
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
                                {{ sesion.name }}
                                <i class="mdi mdi-chevron-down"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="javascript:void(0)" class="dropdown-item"><i class="mdi mdi-power text-muted me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                        <p class="text-white-50 m-0">{{ sesion.role_name || 'Usuario' }}</p>
                    </div>
                </div>
            </div>

            <div id="sidebar-menu">
                <ul class="metismenu list-unstyled" id="side-menu">

                    <template v-if="tienePermiso('paneladministracion')">
                        <li class="menu-title">Menu</li>
                        <li>
                            <router-link to="/administracion" class="nav-link waves-effect" :class="{ 'mm-active': esActiva('administracion') }">
                                <i class="fas fa-info-circle"></i>
                                <span>Gráficos</span>
                            </router-link>
                        </li>
                    </template>

                    <li v-if="tienePermiso('controlcaja')">
                        <a href="javascript: void(0);" class="nav-link has-arrow waves-effect" :class="{ 'mm-active': esActivaCaja }">
                            <i class="fas fa-cash-register"></i>
                            <span>Caja</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><router-link to="/caja" :class="{ 'mm-active': esActiva('caja') }">Control caja</router-link></li>
                            <li><router-link to="/historial_desembolsos_pagos" :class="{ 'mm-active': esActiva('historial_desembolsos_pagos') }">Historial Desembolsos y Pagos Administrativos</router-link></li>
                            <li><router-link to="/historial_gastos" :class="{ 'mm-active': esActiva('historial_gastos') }">Historial Ingresos y Gastos corrientes</router-link></li>
                            <li><router-link to="/historial_pagos" :class="{ 'mm-active': esActiva('historial_pagos') }">Historial Pagos de Cuotas</router-link></li>
                        </ul>
                    </li>

                    <li v-if="tienePermiso('cliente')">
                        <router-link to="/clientes" class="nav-link waves-effect" :class="{ 'mm-active': esActiva('clientes') }">
                            <i class="fas fa-user-tie"></i>
                            <span>Clientes</span>
                        </router-link>
                    </li>

                    <li v-if="tienePermiso('codeudores')">
                        <router-link to="/codeudores" class="nav-link waves-effect" :class="{ 'mm-active': esActiva('codeudores') }">
                            <i class="fas fa-user-tie"></i>
                            <span>Codeudores</span>
                        </router-link>
                    </li>

                    <li v-if="tienePermiso('consultasfinancieras')">
                        <router-link to="/consultas_financieras" class="nav-link waves-effect" :class="{ 'mm-active': esActiva('consultas_financieras') }">
                            <i class="fas fa-search-dollar"></i>
                            <span>Flujo. Financiero</span>
                        </router-link>
                    </li>

                    <li v-if="tienePermiso('solicitudprestamos')">
                        <a href="javascript: void(0);" class="nav-link has-arrow waves-effect" :class="{ 'mm-active': esActivaPrestamos }">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Gest. Préstamos</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><router-link to="/solicitud" :class="{ 'mm-active': esActiva('solicitud') }">Solicitud préstamo</router-link></li>
                            <li><router-link to="/plan_pago" :class="{ 'mm-active': esActiva('plan_pago') }">Gestión cartera</router-link></li>
                        </ul>
                    </li>

                    <li v-if="puedeAdministracion">
                        <a href="javascript: void(0);" class="nav-link has-arrow waves-effect" :class="{ 'mm-active': esActivaAdministracion }">
                            <i class="fas fa-cogs"></i>
                            <span>Administración</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li v-if="tienePermiso('informacion')"><router-link to="/informacion" :class="{ 'mm-active': esActiva('informacion') }">Información Empresa</router-link></li>
                            <li v-if="tienePermiso('usuarios')"><router-link to="/usuarios" :class="{ 'mm-active': esActiva('usuarios') }">Gestión de usuarios</router-link></li>
                            <li v-if="tienePermiso('socios')"><router-link to="/socio" :class="{ 'mm-active': esActiva('socio') }">Gestión de socios</router-link></li>
                            <li v-if="tienePermiso('roles')"><router-link to="/roles" :class="{ 'mm-active': esActiva('roles') }">Roles</router-link></li>
                            <template v-if="tienePermiso('informacion')">
                                <li><router-link to="/configuracion" :class="{ 'mm-active': esActiva('configuracion') }">Motivos Ingresos y Egresos</router-link></li>
                                <li><router-link to="/boveda" :class="{ 'mm-active': esActiva('boveda') }">Boveda</router-link></li>
                            </template>
                        </ul>
                    </li>

                    <li v-if="tienePermiso('reportes')">
                        <a href="javascript: void(0);" class="nav-link has-arrow waves-effect" :class="{ 'mm-active': esActivaReportes }">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reportes</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><router-link to="/reportes" :class="{ 'mm-active': esActiva('reportes') }">Reportes</router-link></li>
                            <li><router-link to="/index_rep_extracto" :class="{ 'mm-active': esActiva('index_rep_extracto') }">Extracto de crédito</router-link></li>
                            <li><router-link to="/hist_credito_mora" :class="{ 'mm-active': esActiva('hist_credito_mora') }">Hist. Crédito Mora</router-link></li>
                            <li><router-link to="/cliente_mora" :class="{ 'mm-active': esActiva('cliente_mora') }">Clientes en Mora</router-link></li>
                            <li><router-link to="/index_rep_pagos_realizados" :class="{ 'mm-active': esActiva('index_rep_pagos_realizados') }">Pagos Realizados</router-link></li>
                            <li><router-link to="/index_pagos_programados" :class="{ 'mm-active': esActiva('index_pagos_programados') }">Pagos Programados</router-link></li>
                            <li><router-link to="/index_movimientos_credito" :class="{ 'mm-active': esActiva('index_movimientos_credito') }">Extracto movimientos</router-link></li>
                            <li><router-link to="/index_porcentajes_pagos" :class="{ 'mm-active': esActiva('index_porcentajes_pagos') }">Porcentaje pagos</router-link></li>
                            <li><router-link to="/index_desembolsos" :class="{ 'mm-active': esActiva('index_desembolsos') }">Desembolsos</router-link></li>
                            <li><router-link to="/index_desembolsos_oficial" :class="{ 'mm-active': esActiva('index_desembolsos_oficial') }">Desembolsos por oficial</router-link></li>
                            <li><router-link to="/index_desembolsos_pendientes" :class="{ 'mm-active': esActiva('index_desembolsos_pendientes') }">Desembolsos pendientes</router-link></li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { sesion, cargarSesion, tienePermiso } from '../../router/session';

export default {
    name: 'AppMenu',
    data() {
        return { sesion };
    },
    computed: {
        esActivaCaja() {
            return this.$route.path.startsWith('/caja') || this.$route.path.startsWith('/historial_');
        },
        esActivaPrestamos() {
            return ['/solicitud', '/plan_pago'].includes(this.$route.path);
        },
        puedeAdministracion() {
            return this.tienePermiso('informacion')
                || this.tienePermiso('usuarios')
                || this.tienePermiso('socios')
                || this.tienePermiso('roles');
        },
        esActivaAdministracion() {
            return ['/informacion', '/usuarios', '/roles', '/configuracion', '/boveda'].includes(this.$route.path);
        },
        esActivaReportes() {
            return this.$route.path.startsWith('/index_')
                || ['/reportes', '/hist_credito_mora', '/cliente_mora'].includes(this.$route.path);
        },
    },
    async mounted() {
        await cargarSesion();
        await this.$nextTick();
        // Re-inicializa MetisMenu (submenús colapsables) una vez que Vue
        // terminó de renderizar los items según los permisos del usuario.
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.metisMenu) {
            window.jQuery('#side-menu').metisMenu();
        }
    },
    methods: {
        tienePermiso,
        esActiva(path) {
            return this.$route.path === '/' + path;
        },
    },
};
</script>

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
        text-decoration: none;
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
        color: #00ffaa !important;
        background-color: rgb(54, 141, 112) !important;
        font-weight: 500;
    }

    .sub-menu a.mm-active {
        color: #00ffaa !important;
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
