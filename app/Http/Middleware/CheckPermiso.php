<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de control de acceso a módulos.
 *
 * Uso en rutas:  ->middleware('permiso:controlcaja')
 *
 * Verifica que el usuario autenticado tenga, a través de su rol, el permiso
 * indicado (tabla permiso_rol). Si no está autenticado, redirige al login;
 * si está autenticado pero sin el permiso, responde 403.
 */
class CheckPermiso
{
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $user = $request->user();

        // Sin sesión → al login (para peticiones normales) o 401 (para AJAX/JSON)
        if (!$user) {
            if ($request->expectsJson()) {
                abort(401, 'No autenticado.');
            }
            return redirect()->route('ir_login');
        }

        if (!$user->tienePermiso($permiso)) {
            abort(403, 'No tiene permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
