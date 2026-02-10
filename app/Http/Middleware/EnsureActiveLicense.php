<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Get Company Directly (Single Company Assumption)
        // We assume the user has a relationship 'empresas' and we take the first one.
        $empresa = $user->empresas()->first();

        // 2. Validate Company Existence
        if (!$empresa) {
            // No company found for this user. 
            // Redirect to a page telling them they need a company/license.
            return redirect()->route('licencia.required');
        }

        // 3. Load License
        $licencia = $empresa->licencia;

        // 4. Validate License Existence and Status
        if (!$licencia) {
            return redirect()->route('licencia.required');
        }

        // Check if expired
        // Note: is_active attribute check includes date validation
        if (!$licencia->is_active) {
            if ($licencia->fecha_fin && $licencia->fecha_fin->isPast()) {
                return redirect()->route('licencia.expired');
            }
            // If active is false but not past, it might be future or cancelled? 
            // Default to pending/required.
            return redirect()->route('licencia.pending');
        }

        // 5. Access Granted
        return $next($request);
    }
}
