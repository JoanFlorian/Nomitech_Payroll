<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Empresa;
use App\Models\Licencia;
use App\Models\Pago;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LicenseRenewalController extends Controller
{
    /**
     * Show the expired license view with available plans.
     */
    public function showExpired()
    {
        $empresaId = session('empresa_id');
        $empresa = Empresa::with('licencia.plan')->find($empresaId);

        if (!$empresa) {
            return redirect()->route('licencia.required');
        }

        $planes = Plan::orderBy('destacado', 'desc')
            ->orderBy('orden', 'asc')
            ->get();

        $currentPlanId = $empresa->licencia ? $empresa->licencia->plan_id : null;

        return view('licencia.expired', compact('planes', 'currentPlanId'));
    }

    /**
     * Process the renewal and redirect to checkout.
     */
    public function renew(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plan,id',
        ]);

        $empresaId = session('empresa_id');
        $plan = Plan::findOrFail($request->plan_id);

        return DB::transaction(function () use ($empresaId, $plan) {
            // Create a new Licencia (Inactive/Pending)
            $licencia = Licencia::create([
                'empresa_id' => $empresaId,
                'plan_id' => $plan->id,
                'fecha_inicio' => null,
                'fecha_fin' => null,
            ]);

            // Create a new Pago (Pending)
            $pago = Pago::create([
                'empresa_id' => $empresaId,
                'licencia_id' => $licencia->id,
                'referencia' => null,
                'proveedor_pago' => 'STRIPE',
                'valor' => $plan->valor,
                'moneda' => 'COP',
                'estado_pago' => PaymentStatus::PENDING->value,
                'stripe_payment_intent_id' => null,
                'stripe_subscription_id' => null,
                'stripe_session_id' => null,
                'fecha_pago' => null
            ]);

            return redirect()->route('checkout.show', ['pago' => $pago->id]);
        });
    }
}
