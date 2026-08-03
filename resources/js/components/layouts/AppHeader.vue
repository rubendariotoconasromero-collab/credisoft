<template>
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

                <!-- Dropdown de Reportes -->
                <div v-if="tienePermiso('reportes')" class="dropdown d-none d-lg-inline-block align-self-center me-3">
                    <button class="btn reports-dropdown-btn waves-effect dropdown-toggle" type="button" id="reportsDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-bar-chart-alt-2" style="color: #00ffaa;"></i>
                        <span>Reportes</span>
                        <i class="mdi mdi-chevron-down reports-chevron font-size-14 opacity-75"></i>
                    </button>
                    <ul class="dropdown-menu reports-dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="reportsDropdownBtn" style="position: absolute; right: 0;">
                        <li class="dropdown-header">Reportes Generales</li>
                        <li>
                            <router-link class="dropdown-item" to="/reportes">
                                <i class="bx bx-grid-alt" style="color: #38bdf8;"></i>
                                <span>Panel de Reportes</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/index_rep_extracto">
                                <i class="bx bx-receipt" style="color: #fbbf24;"></i>
                                <span>Extracto de Crédito</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/hist_credito_mora">
                                <i class="bx bx-time-five" style="color: #f87171;"></i>
                                <span>Historial Crédito Mora</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/cliente_mora">
                                <i class="bx bx-user-x" style="color: #ef4444;"></i>
                                <span>Clientes en Mora</span>
                            </router-link>
                        </li>

                        <li><div class="dropdown-divider"></div></li>
                        <li class="dropdown-header">Transacciones y Pagos</li>
                        <li>
                            <router-link class="dropdown-item" to="/index_rep_pagos_realizados">
                                <i class="bx bx-check-circle" style="color: #34d399;"></i>
                                <span>Pagos Realizados</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/index_pagos_programados">
                                <i class="bx bx-calendar" style="color: #60a5fa;"></i>
                                <span>Pagos Programados</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/index_movimientos_credito">
                                <i class="bx bx-transfer" style="color: #a78bfa;"></i>
                                <span>Extracto de Movimientos</span>
                            </router-link>
                        </li>

                        <li><div class="dropdown-divider"></div></li>
                        <li class="dropdown-header">Porcentajes y Desembolsos</li>
                        <li>
                            <router-link class="dropdown-item" to="/index_porcentajes_pagos">
                                <i class="bx bx-pie-chart-alt-2" style="color: #fb7185;"></i>
                                <span>Porcentaje Pagos</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/index_desembolsos">
                                <i class="bx bx-dollar-circle" style="color: #34d399;"></i>
                                <span>Desembolsos</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/index_desembolsos_oficial">
                                <i class="bx bx-user" style="color: #818cf8;"></i>
                                <span>Desembolsos por Oficial</span>
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/index_desembolsos_pendientes">
                                <i class="bx bx-hourglass-top" style="color: #fbbf24;"></i>
                                <span>Desembolsos Pendientes</span>
                            </router-link>
                        </li>
                    </ul>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="rounded-circle header-profile-user" src="img/empresa/user_img2.png" alt="Foto de perfil">
                        <span class="d-none d-xl-inline-block ms-1 text-white">{{ sesion.name }}</span>
                        <i class="fas fa-chevron-down ms-1" style="font-size: 10px; color: rgba(255,255,255,0.7);"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header">
                            <div class="dropdown-header-user">
                                <div class="dropdown-header-info">
                                    <div class="dropdown-header-name mt-2">
                                        <h6>
                                            {{ sesion.name }}
                                            <span class="d-block fw-semibold text-dark mt-1">
                                                ROL: {{ sesion.role_name }}
                                            </span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <router-link class="dropdown-item" to="/perfil">
                            <i class="mdi mdi-account-circle font-size-16 align-middle me-2 text-muted"></i><span>Perfil</span>
                        </router-link>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-primary" href="#" @click.prevent="cerrarSesion">
                            <i class="mdi mdi-power font-size-16 align-middle me-2 text-primary"></i><span>Salir</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
import axios from "axios";
import { sesion, cargarSesion, tienePermiso, resetSesion } from "../../router/session";

/**
 * Header superior (topbar). Vive dentro del mismo root de Vue que el menú y
 * el contenido (ver app.blade.php), por eso sus enlaces internos usan
 * router-link — ya no recargan la página completa como hacía la versión
 * estática (index.blade.php / app.blade.php de la Fase B).
 *
 * El logout SÍ se resuelve como una navegación de página completa real:
 * es un límite de sesión (se destruye el estado del servidor y del cliente),
 * no algo que deba resolver el router internamente.
 */
export default {
    name: "AppHeader",
    data() {
        return { sesion };
    },
    methods: {
        tienePermiso,
        async cerrarSesion() {
            try {
                await axios.post("/administracion");
            } finally {
                resetSesion();
                window.location.href = "/";
            }
        },
    },
    mounted() {
        cargarSesion();
    },
};
</script>
