/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp } from 'vue';




/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */
// Configurar Axios globalmente

const app = createApp({});

import ExampleComponent from './components/ExampleComponent.vue';
import frmAdministracion from './components/frmAdministracion.vue';
import frmRoles from './components/frmRoles.vue';
import frmUsuarios from './components/frmUsuarios.vue';
import frmInformacion from './components/frmInformacion.vue';
import frmCliente from './components/frmCliente.vue';
import frmSolicitud from './components/frmSolicitud.vue';
import frmPlanPago from './components/frmPlanPago.vue';
import frmReporte from './components/frmReporte.vue';
import frmCaja from './components/frmCaja.vue';
import frmEstadoResultados from './components/frmEstadoResultados.vue';
import frmCodeudores from './components/frmCodeudores.vue';
import frmHistorialDesPagos from './components/frmHistorialDesPagos.vue';
import frmHistorialIngresos from './components/frmHistorialIngresos.vue';
import frmHistorialGastos from './components/frmHistorialGastos.vue';
import repExtracto from './components/repExtracto.vue';
import repHistoricoCreditoMora from './components/repHistoricoCreditoMora.vue';
import repClientesMora from './components/repClientesMora.vue';
import repPagosRealizados from './components/repPagosRealizados.vue';
import repPagosProgramados from './components/repPagosProgramados.vue';
import repMovimientosCredito from './components/repMovimientosCredito.vue';
import repPorcentajesVenta from './components/repPorcentajesVenta.vue';
import repDesembolsos from './components/repDesembolsos.vue';
import repDesembolsosOficial from './components/repDesembolsosOficial.vue';
import repDesembolsosPendientes from './components/repDesembolsosPendientes.vue';
import frmConfiguracion from './components/frmConfiguracion.vue';
import frmBoveda from './components/frmBoveda.vue';
import frmHistorialPagos from './components/frmHistorialPagos.vue';
import PerfilUsuarioComponent from './components/PerfilUsuarioComponent.vue';



app.component('example-component', ExampleComponent);
app.component('frm-administracion', frmAdministracion);
app.component('frm-roles', frmRoles);
app.component('frm-usuarios', frmUsuarios);
app.component('frm-miempresa', frmInformacion);
app.component('frm-cliente', frmCliente);
app.component('frm-solicitud', frmSolicitud);
app.component('frm-planpago', frmPlanPago);
app.component('frm-reporte', frmReporte);
app.component('frm-caja', frmCaja);
app.component('frm-estado_resultados', frmEstadoResultados);
app.component('frm-codeudores', frmCodeudores);
app.component('frm-historialdespagos', frmHistorialDesPagos);
app.component('frm-historialingresos', frmHistorialIngresos);
app.component('frm-historialgastos', frmHistorialGastos);
app.component('rep-extracto', repExtracto);
app.component('rep-histcreditomora', repHistoricoCreditoMora);
app.component('rep-clientesmora', repClientesMora);
app.component('rep-pagosrealizados', repPagosRealizados);
app.component('rep-pagosprogramados', repPagosProgramados);
app.component('rep-movimientoscreditos', repMovimientosCredito);
app.component('rep-porcentajesventa', repPorcentajesVenta);
app.component('rep-desembolsos', repDesembolsos);
app.component('rep-desembolsosoficial', repDesembolsosOficial);
app.component('rep-desembolsospendientes', repDesembolsosPendientes);
app.component('frm-configuracion', frmConfiguracion);
app.component('frm-boveda', frmBoveda);
app.component('frm-historialpagos', frmHistorialPagos);
app.component('perfil-usuario-component', PerfilUsuarioComponent);


























/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.mount('#app');
