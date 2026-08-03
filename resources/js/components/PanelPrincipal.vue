<template>
    <div v-if="!cargado" class="text-center py-5">
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>
    <panel-cajero v-else-if="esCajero"></panel-cajero>
    <panel-oficial-credito v-else-if="esOficialCredito"></panel-oficial-credito>
    <frm-administracion v-else></frm-administracion>
</template>

<script>
import { defineAsyncComponent } from "vue";
import { sesion, cargarSesion } from "../router/session";

// Carga perezosa: solo se descarga el panel que realmente corresponde al
// rol del usuario, no los tres juntos en cada login.
const frmAdministracion = defineAsyncComponent(() => import("./frmAdministracion.vue"));
const PanelCajero = defineAsyncComponent(() => import("./Panel/PanelCajero.vue"));
const PanelOficialCredito = defineAsyncComponent(() => import("./Panel/PanelOficialCredito.vue"));

/**
 * Punto de entrada post-login (ruta /administracion). Decide qué panel
 * mostrar según el rol del usuario autenticado:
 *  - Cajero              -> PanelCajero.vue
 *  - Oficial de Credito  -> PanelOficialCredito.vue
 *  - Cualquier otro rol (Administrador, Encargado de Agencia, y cualquiera
 *    no contemplado, como fallback seguro) -> frmAdministracion.vue, el
 *    panel general que ya existía y no se modificó.
 */
export default {
    name: "PanelPrincipal",
    components: { frmAdministracion, PanelCajero, PanelOficialCredito },
    data() {
        return {
            sesion,
            cargado: false,
        };
    },
    computed: {
        esCajero() {
            return this.sesion.role_name === "Cajero";
        },
        esOficialCredito() {
            return this.sesion.role_name === "Oficial de Credito";
        },
    },
    async mounted() {
        await cargarSesion();
        this.cargado = true;
    },
};
</script>
