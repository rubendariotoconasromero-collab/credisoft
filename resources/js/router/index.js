import { createRouter, createWebHistory } from 'vue-router';
import routes from './routes';
import { sesion, cargarSesion, tienePermiso } from './session';

/**
 * Router en modo "history" (URLs limpias, idénticas a las rutas actuales
 * de Laravel: /caja, /roles, etc.). Requiere que el backend sirva el
 * "shell" SPA para cualquiera de estas rutas al refrescar/entrar por link
 * directo (ruta catch-all — se agrega en la Fase C, todavía no existe).
 */
const router = createRouter({
    history: createWebHistory(),
    routes,
});

/**
 * Guarda de navegación: exige sesión cargada y, si la ruta pide un
 * permiso de módulo, valida contra los permisos del rol (GET /me).
 *
 * Esto es solo control de UX en el cliente — la seguridad real de cada
 * endpoint la sigue aplicando el middleware 'permiso:<nombre>' del backend.
 */
router.beforeEach(async (to) => {
    if (!sesion.cargado) {
        try {
            await cargarSesion();
        } catch (error) {
            // Sesión no disponible (401 u otro error): dejar que el backend
            // resuelva el login en la próxima navegación de página completa.
            console.error('No se pudo cargar la sesión del usuario:', error);
            return true;
        }
    }

    const permisoRequerido = to.meta?.permiso;
    if (permisoRequerido && !tienePermiso(permisoRequerido)) {
        return { name: 'prohibido' };
    }

    return true;
});

export default router;
