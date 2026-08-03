/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp } from 'vue';
import router from './router';

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({});

import ExampleComponent from './components/ExampleComponent.vue';
import LiveSearch from './components/Shared/LiveSearch.vue';
import AppMenu from './components/layouts/AppMenu.vue';
import AppHeader from './components/layouts/AppHeader.vue';

// Los ~30 componentes de módulo (frm-caja, frm-roles, rep-extracto, etc.)
// ya NO se registran acá: se cargan bajo demanda a través de
// resources/js/router/routes.js (Vue Router), no como tags globales.
// Las vistas Blade que antes los montaban (frmCaja.blade.php, etc.) quedaron
// sin ninguna ruta que las sirva desde la migración a SPA (ver routes/web.php).

app.component('example-component', ExampleComponent);
app.component('live-search', LiveSearch);
app.component('app-menu', AppMenu);
app.component('app-header', AppHeader);

app.use(router);

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.mount('#app');
