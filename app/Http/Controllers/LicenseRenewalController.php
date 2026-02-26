<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Empresa;
use App\Models\Licencia;
use App\Models\Pago;
use App\Models\Departamento;
use App\Models\TipoDoc;
use App\Http\Requests\Auth\RegisterRequest;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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
        $empresa = Empresa::with('licencia')->find($empresaId);
        $plan = Plan::findOrFail($request->plan_id);

        return DB::transaction(function () use ($empresa, $plan) {
            $licencia = $empresa->licencia;

            if ($licencia) {
                // Actualizar la licencia existente con el nuevo plan y restablecer fechas
                $licencia->update([
                    'plan_id' => $plan->id,
                    'fecha_inicio' => null,
                    'fecha_fin' => null,
                ]);
            } else {
                // Retorno: crear una nueva licencia si no existe ninguna
                $licencia = Licencia::create([
                    'empresa_id' => $empresa->id_empresa,
                    'plan_id' => $plan->id,
                    'fecha_inicio' => null,
                    'fecha_fin' => null,
                ]);
            }

            // Crear un nuevo Pago (cada pago es una transacción distinta)
            // Se guarda plan_id para mantener trazabilidad si la licencia cambia de plan en el futuro
            $pago = Pago::create([
                'empresa_id' => $empresa->id_empresa,
                'licencia_id' => $licencia->id,
                'plan_id' => $plan->id,
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

    /**
     * Show the "Complete Registration/Payment" view.
     * Reuses the auth.register view but with pre-filled data.
     */
    public function showPending()
    {
        $user = Auth::user();
        if (!$user)
            return redirect()->route('login');

        $empresaId = session('empresa_id');
        $empresa = $empresaId ? Empresa::find($empresaId) : null;

        // Fallback: recover from user if session lost it but user is logged in
        if (!$empresa) {
            $empresa = $user->empresa()->first();
            if ($empresa) {
                session(['empresa_id' => $empresa->id_empresa]);
            }
        }

        if (!$empresa)
            return redirect()->route('login');

        $departamentos = Departamento::all()->pluck('nombre', 'id_departamento');
        $tiposDocumento = TipoDoc::all()->pluck('nombre', 'id_tipo_doc');
        $plans = Plan::orderBy('orden', 'asc')->get();

        $selected_plan_id = $empresa->licencia ? $empresa->licencia->plan_id : null;

        $initialData = [
            'nit' => old('nit', $empresa->nit),
            'nit_dv' => old('nit_dv', $empresa->nit_dv ?? ''),
            'razon_social' => old('razon_social', $empresa->razon_social),
            'id_departamento' => old('id_departamento', $empresa->ciudad?->id_departamento),
            'id_ciudad' => old('id_ciudad', $empresa->id_ciudad),
            'direccion_empresa' => old('direccion_empresa', $empresa->direccion),
            'documento' => old('documento', $user->doc),
            'id_tipo_doc' => old('id_tipo_doc', $user->id_tipo_doc),
            'primer_apellido' => old('primer_apellido', $user->primer_apellido),
            'segundo_apellido' => old('segundo_apellido', $user->segundo_apellido),
            'primer_nombre' => old('primer_nombre', $user->primer_nombre),
            'otros_nombres' => old('otros_nombres', $user->otros_nombres),
            'telefono_celular' => old('telefono_celular', $user->telefono),
            'email' => old('email', $user->correo),
            'plan_id' => old('plan_id', $selected_plan_id)
        ];

        return view('auth.register', [
            'initialData' => $initialData,
            'departamentos' => $departamentos,
            'tiposDocumento' => $tiposDocumento,
            'plans' => $plans,
            'title' => 'Completa tu Registro',
            'description' => 'Verifica tus datos y selecciona tu plan para activar tu cuenta de Nomitech.',
            'action' => route('licencia.pending.post'),
            'submitText' => 'Ir al pago',
            'isPendingPayment' => true // Flag for the view if needed
        ]);
    }

    /**
     * Process the update and redirect to checkout for pending users.
     */
    public function processPending(RegisterRequest $request)
    {
        $user = Auth::user();
        $empresaId = session('empresa_id');
        $empresa = Empresa::findOrFail($empresaId);
        $plan = Plan::findOrFail($request->plan_id);

        return DB::transaction(function () use ($request, $user, $empresa, $plan) {
            // 1. Update User (Ignoring doc PK change for stability)
            $user->update([
                'id_tipo_doc' => $request->id_tipo_doc,
                'primer_apellido' => $request->primer_apellido,
                'segundo_apellido' => $request->segundo_apellido,
                'primer_nombre' => $request->primer_nombre,
                'otros_nombres' => $request->otros_nombres,
                'telefono' => $request->telefono_celular,
                'correo' => $request->email,
                'direccion' => $request->direccion_empresa,
            ]);

            if ($request->filled('password')) {
                $user->update(['contrasena' => Hash::make($request->password)]);
            }

            // 2. Update Empresa (Ignoring NIT change for stability)
            $empresa->update([
                'razon_social' => $request->razon_social,
                'id_ciudad' => $request->id_ciudad,
                'direccion' => $request->direccion_empresa,
                'telefono' => $request->telefono_celular,
                'correo' => $request->email, // Synchronize email
            ]);

            // 3. Update Licencia
            $licencia = $empresa->licencia;
            if ($licencia) {
                $licencia->update([
                    'plan_id' => $plan->id,
                    'fecha_inicio' => null,
                    'fecha_fin' => null,
                ]);
            } else {
                $licencia = Licencia::create([
                    'empresa_id' => $empresa->id_empresa,
                    'plan_id' => $plan->id,
                ]);
            }

            // 4. Create New Payment
            $pago = Pago::create([
                'empresa_id' => $empresa->id_empresa,
                'licencia_id' => $licencia->id,
                'plan_id' => $plan->id,
                'proveedor_pago' => 'STRIPE',
                'valor' => $plan->valor,
                'moneda' => 'COP',
                'estado_pago' => PaymentStatus::PENDING->value,
            ]);

            return redirect()->route('checkout.show', ['pago' => $pago->id]);
        });
    }
}
