/**
 * Árbol de rutas de Vue Router.
 *
 * Cada entrada mapea la URL EXACTA que hoy sirve Laravel/Blade a su
 * componente Vue existente. El nombre de `meta.permiso` corresponde 1:1 a
 * la tabla `permiso` (ver PermisoSeeder) y al middleware 'permiso:<nombre>'
 * ya aplicado en routes/web.php.
 *
 * `meta.permiso = null` => cualquier usuario autenticado puede entrar
 * (no requiere un permiso de módulo específico).
 *
 * Los componentes se cargan con import() dinámico (lazy loading): Vite
 * genera un chunk separado por módulo, que se descarga recién cuando se
 * visita esa ruta — en vez de traer los ~30 módulos juntos en un solo
 * bundle inicial, sin importar cuál esté usando el usuario.
 */

const routes = [
    // ------------------------------------------------------------------
    // Panel / Administración          [permiso: paneladministracion]
    // ------------------------------------------------------------------
    // Nota: el servidor deja esta ruta abierta a cualquier usuario autenticado
    // (es el destino post-login), sin exigir 'paneladministracion' — el menú
    // solo oculta el LINK si falta ese permiso. Se replica el mismo criterio
    // aquí para no introducir un bloqueo que hoy no existe.
    // PanelPrincipal decide internamente qué panel mostrar según el rol
    // (Cajero / Oficial de Credito / general para el resto).
    { path: '/administracion', name: 'administracion', component: () => import('../components/PanelPrincipal.vue'), meta: { permiso: null, titulo: 'Panel Informativo' } },

    // ------------------------------------------------------------------
    // Caja                            [permiso: controlcaja]
    // ------------------------------------------------------------------
    { path: '/caja', name: 'caja', component: () => import('../components/frmCaja.vue'), meta: { permiso: ['controlcaja', 'consultasfinancieras'], titulo: 'Control de Caja' } },
    { path: '/historial_desembolsos_pagos', name: 'historial-desembolsos-pagos', component: () => import('../components/frmHistorialDesPagos.vue'), meta: { permiso: 'controlcaja', titulo: 'Historial Desembolsos y Pagos Adm.' } },
    { path: '/historial_gastos', name: 'historial-gastos', component: () => import('../components/frmHistorialGastos.vue'), meta: { permiso: 'controlcaja', titulo: 'Historial Ingresos y Gastos Corrientes' } },
    { path: '/historial_pagos', name: 'historial-pagos', component: () => import('../components/frmHistorialPagos.vue'), meta: { permiso: 'controlcaja', titulo: 'Historial Pagos de Cuotas' } },
    // Página existente en el backend pero sin enlace en el menú actual (se preserva igual).
    { path: '/historial_ingresos', name: 'historial-ingresos', component: () => import('../components/frmHistorialIngresos.vue'), meta: { permiso: 'controlcaja', titulo: 'Historial de Ingresos' } },

    // ------------------------------------------------------------------
    // Clientes / Codeudores            [permiso: cliente / codeudores]
    // ------------------------------------------------------------------
    { path: '/clientes', name: 'clientes', component: () => import('../components/frmCliente.vue'), meta: { permiso: 'cliente', titulo: 'Clientes' } },
    { path: '/codeudores', name: 'codeudores', component: () => import('../components/frmCodeudores.vue'), meta: { permiso: 'codeudores', titulo: 'Codeudores' } },

    // ------------------------------------------------------------------
    // Consultas Financieras            [permiso: consultasfinancieras]
    // ------------------------------------------------------------------
    { path: '/consultas_financieras', name: 'consultas-financieras', component: () => import('../components/frmConsultaFinanciera.vue'), meta: { permiso: 'consultasfinancieras', titulo: 'Flujo Financiero' } },
    // Página existente en el backend pero sin enlace en el menú actual (se preserva igual).
    { path: '/estado_resultados', name: 'estado-resultados', component: () => import('../components/frmEstadoResultados.vue'), meta: { permiso: 'consultasfinancieras', titulo: 'Estado de Resultados' } },

    // ------------------------------------------------------------------
    // Gestión de Préstamos              [permiso: solicitudprestamos / planpagos]
    // ------------------------------------------------------------------
    { path: '/solicitud', name: 'solicitud', component: () => import('../components/frmSolicitud.vue'), meta: { permiso: 'solicitudprestamos', titulo: 'Solicitud de Préstamo' } },
    { path: '/plan_pago', name: 'plan-pago', component: () => import('../components/frmPlanPago.vue'), meta: { permiso: 'planpagos', titulo: 'Gestión de Cartera' } },

    // ------------------------------------------------------------------
    // Administración (submenú)          [permiso: informacion / usuarios / roles / socios / boveda / configuracion]
    // ------------------------------------------------------------------
    { path: '/informacion', name: 'informacion', component: () => import('../components/frmInformacion.vue'), meta: { permiso: 'informacion', titulo: 'Información de la Empresa' } },
    { path: '/usuarios', name: 'usuarios', component: () => import('../components/frmUsuarios.vue'), meta: { permiso: 'usuarios', titulo: 'Gestión de Usuarios' } },
    { path: '/socio', name: 'socio', component: () => import('../components/frmSocio.vue'), meta: { permiso: 'socios', titulo: 'Gestión de Socios' } },
    { path: '/roles', name: 'roles', component: () => import('../components/frmRoles.vue'), meta: { permiso: 'roles', titulo: 'Roles' } },
    { path: '/configuracion', name: 'configuracion', component: () => import('../components/frmConfiguracion.vue'), meta: { permiso: 'configuracion', titulo: 'Motivos de Ingresos y Egresos' } },
    { path: '/boveda', name: 'boveda', component: () => import('../components/frmBoveda.vue'), meta: { permiso: 'boveda', titulo: 'Bóveda' } },

    // ------------------------------------------------------------------
    // Reportes                          [permiso: reportes]
    // ------------------------------------------------------------------
    { path: '/reportes', name: 'reportes', component: () => import('../components/frmReporte.vue'), meta: { permiso: 'reportes', titulo: 'Panel de Reportes' } },
    { path: '/index_rep_extracto', name: 'rep-extracto', component: () => import('../components/repExtracto.vue'), meta: { permiso: 'reportes', titulo: 'Extracto de Crédito' } },
    { path: '/hist_credito_mora', name: 'hist-credito-mora', component: () => import('../components/repHistoricoCreditoMora.vue'), meta: { permiso: 'reportes', titulo: 'Historial Crédito Mora' } },
    { path: '/cliente_mora', name: 'cliente-mora', component: () => import('../components/repClientesMora.vue'), meta: { permiso: 'reportes', titulo: 'Clientes en Mora' } },
    { path: '/index_rep_pagos_realizados', name: 'rep-pagos-realizados', component: () => import('../components/repPagosRealizados.vue'), meta: { permiso: 'reportes', titulo: 'Pagos Realizados' } },
    { path: '/index_pagos_programados', name: 'pagos-programados', component: () => import('../components/repPagosProgramados.vue'), meta: { permiso: 'reportes', titulo: 'Pagos Programados' } },
    { path: '/index_movimientos_credito', name: 'movimientos-credito', component: () => import('../components/repMovimientosCredito.vue'), meta: { permiso: 'reportes', titulo: 'Extracto de Movimientos' } },
    { path: '/index_porcentajes_pagos', name: 'porcentajes-pagos', component: () => import('../components/repAvanceCreditos.vue'), meta: { permiso: 'reportes', titulo: 'Porcentaje de Pagos' } },
    { path: '/index_desembolsos', name: 'desembolsos', component: () => import('../components/repDesembolsos.vue'), meta: { permiso: 'reportes', titulo: 'Desembolsos' } },
    { path: '/index_desembolsos_oficial', name: 'desembolsos-oficial', component: () => import('../components/repDesembolsosOficial.vue'), meta: { permiso: 'reportes', titulo: 'Desembolsos por Oficial' } },
    { path: '/index_desembolsos_pendientes', name: 'desembolsos-pendientes', component: () => import('../components/repDesembolsosPendientes.vue'), meta: { permiso: 'reportes', titulo: 'Desembolsos Pendientes' } },

    // ------------------------------------------------------------------
    // Perfil — cualquier usuario autenticado (sin permiso de módulo)
    // ------------------------------------------------------------------
    { path: '/perfil', name: 'perfil', component: () => import('../components/PerfilUsuarioComponent.vue'), meta: { permiso: null, titulo: 'Mi Perfil' } },

    // ------------------------------------------------------------------
    // Infraestructura del router
    // ------------------------------------------------------------------
    { path: '/prohibido', name: 'prohibido', component: () => import('../components/Router/Prohibido.vue'), meta: { permiso: null, titulo: 'Acceso no autorizado' } },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../components/Router/NoEncontrado.vue'), meta: { permiso: null, titulo: 'No encontrado' } },
];

export default routes;
