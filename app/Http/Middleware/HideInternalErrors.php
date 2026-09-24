<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HideInternalErrors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // Legacy controllers sometimes include SQL or filesystem paths in JSON errors.
        if (!config('app.debug') && $request->expectsJson() && $response->getStatusCode() >= 500) {
            return response()->json(['message' => 'No se pudo completar la operación. Contacte al administrador.'], $response->getStatusCode());
        }
        return $response;
    }
}
