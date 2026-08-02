import { reactive } from 'vue';
import axios from 'axios';

/**
 * Estado de sesión del cliente SPA: datos del usuario autenticado y los
 * permisos de su rol (vía GET /me). Se usa para:
 *  - Armar el menú (mostrar/ocultar módulos).
 *  - Guardas de navegación de Vue Router (bloquear rutas sin permiso).
 *
 * IMPORTANTE: esto es solo control de UX en el cliente. La seguridad real
 * de cada acción/dato sigue siendo el middleware 'permiso:<nombre>' del
 * backend (routes/web.php) — este estado nunca reemplaza esa validación.
 */
const state = reactive({
    cargado: false,
    cargando: false,
    id: null,
    name: null,
    personal: null,
    id_rol: null,
    role_name: null,
    permisos: [],
});

let promesaCarga = null;

/**
 * Carga (una sola vez) los datos de sesión desde el backend.
 * Llamadas concurrentes reutilizan la misma promesa en curso.
 */
function cargarSesion() {
    if (state.cargado) return Promise.resolve(state);
    if (promesaCarga) return promesaCarga;

    state.cargando = true;
    promesaCarga = axios.get('/me')
        .then(({ data }) => {
            state.id = data.id;
            state.name = data.name;
            state.personal = data.personal;
            state.id_rol = data.id_rol;
            state.role_name = data.role_name;
            state.permisos = data.permisos || [];
            state.cargado = true;
            return state;
        })
        .finally(() => {
            state.cargando = false;
            promesaCarga = null;
        });

    return promesaCarga;
}

/** Un permiso vacío/null significa "cualquier usuario autenticado". */
function tienePermiso(nombrePermiso) {
    if (!nombrePermiso) return true;
    return state.permisos.includes(nombrePermiso);
}

function resetSesion() {
    state.cargado = false;
    state.id = null;
    state.name = null;
    state.personal = null;
    state.id_rol = null;
    state.role_name = null;
    state.permisos = [];
}

export const sesion = state;
export { cargarSesion, tienePermiso, resetSesion };
