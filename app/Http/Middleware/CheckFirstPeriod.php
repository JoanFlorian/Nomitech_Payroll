<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckFirstPeriod
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Bypass for superadmins or unauthenticated users
        if (!$user || (int) $user->id_rol === 4) {
            return $next($request);
        }

        $empresaId = session('empresa_id');
        if (!$empresaId) {
            return $next($request);
        }

        // Check if company has ANY periods
        $hasPeriods = \App\Models\PeriodoLiquidacion::where('id_empresa', $empresaId)->exists();

        if (!$hasPeriods && !$request->routeIs('periodos.store-first')) {
            // Set flag for the frontend modal
            session(['needs_first_period' => true]);
        } else {
            session()->forget('needs_first_period');
        }

        return $next($request);
    }
}
