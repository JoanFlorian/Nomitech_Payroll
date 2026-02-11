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

        // Superadmin bypasses license validation completely
        if ((int) $user->id_rol === 4) {
            return $next($request);
        }

        // 1. Get empresa_id from session
        $empresaId = session('empresa_id');
        
        if (!$empresaId) {
            return redirect()->route('licencia.required');
        }

        // 2. Fetch empresa directly from tabla empresa
        $empresa = \App\Models\Empresa::find($empresaId);

        if (!$empresa) {
            return redirect()->route('licencia.required');
        }

        // 3. Load Latest License (Empresa::licencia relation already orders by latest('created_at'))
        // This ensures we always validate the most recently purchased/created license
        $licencia = $empresa->licencia;

        // 4. Validate License Existence
        if (!$licencia) {
            return redirect()->route('licencia.required');
        }

        // 5. Check License Status
        // Allow 'activa' and 'por_vencer' (active within next 10 days)
        // Reject 'vencida' (expired) and 'prueba' (trial without fecha_fin)
        $estado = $licencia->getEstadoAttribute();

        if ($estado === 'vencida') {
            return redirect()->route('licencia.expired');
        }

        if ($estado === 'prueba') {
            return redirect()->route('licencia.pending');
        }

        // Estado es 'activa' o 'por_vencer' - allow access
        return $next($request);
    }
}
