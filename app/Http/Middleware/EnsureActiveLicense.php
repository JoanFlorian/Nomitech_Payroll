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

        // 1. Check if empresa_id is in Session
        $empresaId = session('empresa_id');

        if (!$empresaId) {
            // Auto-select if only one company
            $empresas = $user->empresas; // Assuming 'empresas' relationship exists on Usuario

            if ($empresas->count() === 1) {
                $empresaId = $empresas->first()->id_empresa;
                session(['empresa_id' => $empresaId]);
            } elseif ($empresas->count() > 1) {
                return redirect()->route('empresa.select');
            } else {
                // User has no companies (Should not happen in this flow but handle safe)
                // Maybe redirect to registration or create company?
                return redirect()->route('empresa.create_first'); // Placeholder
            }
        }

        // 2. Load Enterprise and License
        // We probably loaded it above, but let's be safe and fetch fresh if from session
        $empresa = \App\Models\Empresa::with('licencia')->find($empresaId);

        if (!$empresa) {
            // Session ID invalid?
            session()->forget('empresa_id');
            return redirect()->route('empresa.select');
        }

        $licencia = $empresa->licencia;

        // 3. Check License Status
        if (!$licencia) {
            return redirect()->route('licencia.pending');
        }

        if ($licencia->estado === 'inactive') {
            return redirect()->route('licencia.pending');
        }

        if ($licencia->estado === 'expired' || ($licencia->fecha_fin && $licencia->fecha_fin->isPast())) {
            // Optional: Allow read-only? Strict prompt says "Access controlled exclusively by active license"
            return redirect()->route('licencia.expired');
        }

        // 4. Access Granted
        return $next($request);
    }
}
