<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Contrato;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckContractualAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. SuperAdmin and Representante Legal Bypass
        if (Auth::check() && in_array((int) Auth::user()->id_rol, [1, 4])) {
            return $next($request);
        }

        // 2. No session or not logged in - let other middleware handle it
        if (!Auth::check()) {
            return $next($request);
        }

        $usuario = Auth::user();
        $empresaId = session('empresa_id');

        // 3. Find contract using withoutGlobalScopes to bypass isolation and show historical/ended states
        $contrato = Contrato::withoutGlobalScopes()
            ->where('doc', $usuario->doc)
            ->where('id_empresa', $empresaId)
            ->first();

        // 4. No contract found for this company - Block access
        if (!$contrato) {
            return redirect()->route('licencia.required')->with('error', 'No tienes un contrato vinculado a esta empresa.');
        }

        // 5. Check access based on professional flow
        // A. Full Access: Active Laboral State OR Pending Payroll State
        if (
            $contrato->estado_laboral === Contrato::ESTADO_LABORAL_ACTIVO ||
            $contrato->estado_nomina === Contrato::ESTADO_NOMINA_PENDIENTE
        ) {
            return $next($request);
        }

        // B. Read-Only / Grace Period: Liquidated AND within 3 days
        if ($contrato->estado_nomina === Contrato::ESTADO_NOMINA_LIQUIDADO) {
            $fechaLiquidacion = $contrato->fecha_liquidacion_final;

            if ($fechaLiquidacion) {
                $limiteGracia = $fechaLiquidacion->copy()->addDays(Contrato::GRACE_PERIOD_DAYS);

                if (now()->lessThanOrEqualTo($limiteGracia)) {
                    // Grace period active - allow only GET requests
                    if ($request->isMethod('GET')) {
                        return $next($request);
                    }

                    return back()->with('error', 'Tu contrato ha sido liquidado. El acceso está restringido a modo lectura durante el periodo de gracia.');
                }
            }
        }

        // 6. Block all other cases (Grace period expired or no valid state)
        Auth::logout();
        session()->flush();
        return redirect()->route('login')->with('error', 'Tu acceso ha expirado debido a la finalización y liquidación de tu contrato.');
    }
}
