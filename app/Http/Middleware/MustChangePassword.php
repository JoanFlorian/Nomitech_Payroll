<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MustChangePassword
{
    /**
     * Rutas que están exentas de esta verificación (para evitar bucles infinitos).
     */
    private const EXEMPT_ROUTES = [
        'cambiar-password',
        'cambiar-password.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && (bool) $user->must_change_password) {
            // No redirigir si ya estamos en la ruta de cambio de contraseña
            if ($request->routeIs(...self::EXEMPT_ROUTES)) {
                return $next($request);
            }

            return redirect()->route('cambiar-password')
                ->withErrors(['password' => 'Debes cambiar tu contraseña antes de continuar.']);
        }

        return $next($request);
    }
}
