/**
 * Árbol de rutas de Vue Router — Fase A (fundación).
 *
 * Cada entrada mapea la URL EXACTA que hoy sirve Laravel/Blade a su
 * componente Vue existente, sin modificar los componentes. El nombre de
 * `meta.permiso` corresponde 1:1 a la tabla `permiso` (ver PermisoSeeder) y
 * al middleware 'permiso:<nombre>' ya aplicado en routes/web.php.
 *
 * `meta.permiso = null` => cualquier usuario autenticado puede entrar
 * (no requiere un permiso de módulo específico).
 *
 * IMPORTANTE: este archivo todavía no está conectado a app.js (Fase A =
 * cimientos). La app sigue funcionando como Multi-Page-App hasta la Fase B/C.
 */

import frmAdministracion from '../components/frmAdministracion.vue';
import frmCaja from '../components/frmCaja.vue';
import frmHistorialDesPagos from '../components/frmHistorialDesPagos.vue';
import frmHistorialGastos from '../components/frmHistorialGastos.vue';
import frmHistorialPagos from '../components/frmHistorialPagos.vue';
import frmHistorialIngresos from '../components/frmHistorialIngresos.vue';
import frmCliente from '../components/frmCliente.vue';
import frmCodeudores from '../components/frmCodeudores.vue';
import frmConsultaFinanciera from '../components/frmConsultaFinanciera.vue';
import frmEstadoResultados from '../components/frmEstadoResultados.vue';
import frmSolicitud from '../components/frmSolicitud.vue';
import frmPlanPago from '../components/frmPlanPago.vue';
import frmInformacion from '../components/frmInformacion.vue';
import frmUsuarios from '../components/frmUsuarios.vue';
import frmSocio from '../components/frmSocio.vue';
import frmRoles from '../components/frmRoles.vue';
import frmConfiguracion from '../components/frmConfiguracion.vue';
import frmBoveda from '../components/frmBoveda.vue';
import frmReporte from '../components/frmReporte.vue';
import repExtracto from '../components/repExtracto.vue';
import repHistoricoCreditoMora from '../components/repHistoricoCreditoMora.vue';
import repClientesMora from '../components/repClientesMora.vue';
import repPagosRealizados from '../components/repPagosRealizados.vue';
import repPagosProgramados from '../components/repPagosProgramados.vue';
import repMovimientosCredito from '../components/repMovimientosCredito.vue';
import repAvanceCreditos from '../components/repAvanceCreditos.vue';
import repDesembolsos from '../components/repDesembolsos.vue';
import repDesembolsosOficial from '../components/repDesembolsosOficial.vue';
import repDesembolsosPendientes from '../components/repDesembolsosPendientes.vue';
import PerfilUsuarioComponent from '../components/PerfilUsuarioComponent.vue';

import Prohibido from '../components/Router/Prohibido.vue';
import NoEncontrado from '../components/Router/NoEncontrado.vue';

const routes = [
    // ------------------------------------------------------------------
    // Panel / Administración          [permiso: paneladministracion]
    // ------------------------------------------------------------------
    // Nota: el servidor deja esta ruta abierta a cualquier usuario autenticado
    // (es el destino post-login), sin exigir 'paneladministracion' — el menú
    // solo oculta el LINK si falta ese permiso. Se replica el mismo criterio
    // aquí para no introducir un bloqueo que hoy no existe.
    { path: '/administracion', name: 'administracion', component: frmAdministracion, meta: { permiso: null, titulo: 'Panel Informativo' } },

    // ------------------------------------------------------------------
    // Caja                            [permiso: controlcaja]
    // ------------------------------------------------------------------
    { path: '/caja', name: 'caja', component: frmCaja, meta: { permiso: 'controlcaja', titulo: 'Control de Caja' } },
    { path: '/historial_desembolsos_pagos', name: 'historial-desembolsos-pagos', component: frmHistorialDesPagos, meta: { permiso: 'controlcaja', titulo: 'Historial Desembolsos y Pagos Adm.' } },
    { path: '/historial_gastos', name: 'historial-gastos', component: frmHistorialGastos, meta: { permiso: 'controlcaja', titulo: 'Historial Ingresos y Gastos Corrientes' } },
    { path: '/historial_pagos', name: 'historial-pagos', component: frmHistorialPagos, meta: { permiso: 'controlcaja', titulo: 'Historial Pagos de Cuotas' } },
    // Página existente en el backend pero sin enlace en el menú actual (se preserva igual).
    { path: '/historial_ingresos', name: 'historial-ingresos', component: frmHistorialIngresos, meta: { permiso: 'controlcaja', titulo: 'Historial de Ingresos' } },

    // ------------------------------------------------------------------
    // Clientes / Codeudores            [permiso: cliente / codeudores]
    // ------------------------------------------------------------------
    { path: '/clientes', name: 'clientes', component: frmCliente, meta: { permiso: 'cliente', titulo: 'Clientes' } },
    { path: '/codeudores', name: 'codeudores', component: frmCodeudores, meta: { permiso: 'codeudores', titulo: 'Codeudores' } },

    // ------------------------------------------------------------------
    // Consultas Financieras            [permiso: consultasfinancieras]
    // ------------------------------------------------------------------
    { path: '/consultas_financieras', name: 'consultas-financieras', component: frmConsultaFinanciera, meta: { permiso: 'consultasfinancieras', titulo: 'Flujo Financiero' } },
    // Página existente en el backend pero sin enlace en el menú actual (se preserva igual).
    { path: '/estado_resultados', name: 'estado-resultados', component: frmEstadoResultados, meta: { permiso: 'consultasfinancieras', titulo: 'Estado de Resultados' } },

    // ------------------------------------------------------------------
    // Gestión de Préstamos              [permiso: solicitudprestamos / planpagos]
    // ------------------------------------------------------------------
    { path: '/solicitud', name: 'solicitud', component: frmSolicitud, meta: { permiso: 'solicitudprestamos', titulo: 'Solicitud de Préstamo' } },
    { path: '/plan_pago', name: 'plan-pago', component: frmPlanPago, meta: { permiso: 'planpagos', titulo: 'Gestión de Cartera' } },

    // ------------------------------------------------------------------
    // Administración (submenú)          [permiso: informacion / usuarios / roles / socios]
    // ------------------------------------------------------------------
    { path: '/informacion', name: 'informacion', component: frmInformacion, meta: { permiso: 'informacion', titulo: 'Información de la Empresa' } },
    { path: '/usuarios', name: 'usuarios', component: frmUsuarios, meta: { permiso: 'usuarios', titulo: 'Gestión de Usuarios' } },
    { path: '/socio', name: 'socio', component: frmSocio, meta: { permiso: 'socios', titulo: 'Gestión de Socios' } },
    { path: '/roles', name: 'roles', component: frmRoles, meta: { permiso: 'roles', titulo: 'Roles' } },
    { path: '/configuracion', name: 'configuracion', component: frmConfiguracion, meta: { permiso: 'informacion', titulo: 'Motivos de Ingresos y Egresos' } },
    { path: '/boveda', name: 'boveda', component: frmBoveda, meta: { permiso: 'informacion', titulo: 'Bóveda' } },

    // ------------------------------------------------------------------
    // Reportes                          [permiso: reportes]
    // ------------------------------------------------------------------
    { path: '/reportes', name: 'reportes', component: frmReporte, meta: { permiso: 'reportes', titulo: 'Panel de Reportes' } },
    { path: '/index_rep_extracto', name: 'rep-extracto', component: repExtracto, meta: { permiso: 'reportes', titulo: 'Extracto de Crédito' } },
    { path: '/hist_credito_mora', name: 'hist-credito-mora', component: repHistoricoCreditoMora, meta: { permiso: 'reportes', titulo: 'Historial Crédito Mora' } },
    { path: '/cliente_mora', name: 'cliente-mora', component: repClientesMora, meta: { permiso: 'reportes', titulo: 'Clientes en Mora' } },
    { path: '/index_rep_pagos_realizados', name: 'rep-pagos-realizados', component: repPagosRealizados, meta: { permiso: 'reportes', titulo: 'Pagos Realizados' } },
    { path: '/index_pagos_programados', name: 'pagos-programados', component: repPagosProgramados, meta: { permiso: 'reportes', titulo: 'Pagos Programados' } },
    { path: '/index_movimientos_credito', name: 'movimientos-credito', component: repMovimientosCredito, meta: { permiso: 'reportes', titulo: 'Extracto de Movimientos' } },
    { path: '/index_porcentajes_pagos', name: 'porcentajes-pagos', component: repAvanceCreditos, meta: { permiso: 'reportes', titulo: 'Porcentaje de Pagos' } },
    { path: '/index_desembolsos', name: 'desembolsos', component: repDesembolsos, meta: { permiso: 'reportes', titulo: 'Desembolsos' } },
    { path: '/index_desembolsos_oficial', name: 'desembolsos-oficial', component: repDesembolsosOficial, meta: { permiso: 'reportes', titulo: 'Desembolsos por Oficial' } },
    { path: '/index_desembolsos_pendientes', name: 'desembolsos-pendientes', component: repDesembolsosPendientes, meta: { permiso: 'reportes', titulo: 'Desembolsos Pendientes' } },

    // ------------------------------------------------------------------
    // Perfil — cualquier usuario autenticado (sin permiso de módulo)
    // ------------------------------------------------------------------
    { path: '/perfil', name: 'perfil', component: PerfilUsuarioComponent, meta: { permiso: null, titulo: 'Mi Perfil' } },

    // ------------------------------------------------------------------
    // Infraestructura del router
    // ------------------------------------------------------------------
    { path: '/prohibido', name: 'prohibido', component: Prohibido, meta: { permiso: null, titulo: 'Acceso no autorizado' } },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NoEncontrado, meta: { permiso: null, titulo: 'No encontrado' } },
];

export default routes;
