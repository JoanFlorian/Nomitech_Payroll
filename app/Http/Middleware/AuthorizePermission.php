<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user() || !$request->user()->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No tienes permisos para realizar esta acción.'], 403);
            }
            
            $safeRoute = $request->user()->getFirstAccessibleRoute();
            
            // Evitar loop si ya estamos en la ruta segura (aunque esto no debería pasar por el middleware)
            if ($request->routeIs($safeRoute)) {
                abort(403, 'No tienes permisos para acceder a esta sección.');
            }

            return redirect()->route($safeRoute)->with('error', 'No tienes permisos para acceder a la sección anterior.');
        }

        return $next($request);
    }
}
