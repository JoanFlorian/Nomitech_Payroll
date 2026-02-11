<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Ciudad;
use App\Models\TipoDoc;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Licencia;
use App\Models\Plan;
use App\Models\Pago;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Enums\PaymentStatus;
// use Stripe\Stripe;
// use Stripe\Checkout\Session;

class RegisterController extends Controller
{
    /**
     * Show the application registration form.
     */
    public function create(Request $request)
    {
        $departamentos = Departamento::all()->pluck('nombre', 'id_departamento');
        $tiposDocumento = TipoDoc::all()->pluck('nombre', 'id_tipo_doc');

        // Load plans for selection (allow user to change plan on the register form)
        $plans = Plan::orderBy('orden', 'asc')->get();
        $selected_plan_id = $request->query('plan_id');

        return view('auth.register', compact('departamentos', 'tiposDocumento', 'plans', 'selected_plan_id'));
    }

    /**
     * Handle a registration request for the application.
     */
    public function store(RegisterRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // 1. Create Usuario first (without empresa FK)
            $usuario = Usuario::create([
                'doc' => $request->documento,
                'id_tipo_doc' => $request->id_tipo_doc,
                'primer_apellido' => $request->primer_apellido,
                'segundo_apellido' => $request->segundo_apellido,
                'primer_nombre' => $request->primer_nombre,
                'otros_nombres' => $request->otros_nombres,
                'telefono' => $request->telefono_celular,
                'correo' => $request->email,
                'direccion' => $request->direccion_empresa,
                'contrasena' => Hash::make($request->password),
                'id_rol' => 1,
            ]);

            // 2. Create Empresa (with doc_representante)
            $empresa = Empresa::create([
                'razon_social' => $request->razon_social,
                'nit' => $request->nit,
                'doc_representante' => $usuario->doc,
                'id_ciudad' => $request->id_ciudad,
                'direccion' => $request->direccion_empresa,
                'telefono' => $request->telefono_celular,
            ]);

            // 3. Resolve selected plan from request (or fallback to default ordered plan)
            $planId = $request->input('plan_id');
            $plan = null;
            if ($planId) {
                $plan = Plan::find($planId);
            }
            if (!$plan) {
                $plan = Plan::orderBy('orden', 'asc')->first();
            }

            // 4. Create Licencia (Inactive until payment)
            $licencia = Licencia::create([
                'empresa_id' => $empresa->id_empresa,
                'plan_id' => $plan->id,
                'fecha_inicio' => null,
                'fecha_fin' => null,
                // 'estado' removed - computed from dates
            ]);

            // 5. Create Pago (Pending)
            $pago = Pago::create([
                'empresa_id' => $empresa->id_empresa,
                'licencia_id' => $licencia->id,
                'referencia' => null, // Will be set on Checkout
                'proveedor_pago' => 'STRIPE',
                'valor' => $plan->valor,
                'moneda' => 'COP',
                'estado_pago' => PaymentStatus::PENDING->value,
                'stripe_payment_intent_id' => null,
                'stripe_subscription_id' => null,
                'stripe_session_id' => null,
                'fecha_pago' => null
            ]);

            // 6. Attach Empresa to Usuario (so middleware can find it)
            // Each user has exactly one company
            $usuario->empresa()->attach($empresa->id_empresa);

            // Login User (Session-based)
            Auth::login($usuario);

            // Set Auto-Selected Company in Session
            session(['empresa_id' => $empresa->id_empresa]);

            // 7. Redirect to Checkout Flow (Internal)
            return redirect()->route('checkout.show', ['pago' => $pago->id]);
        });
    }

    /**
     * Get cities by department ID.
     */
    public function getCities($departmentId)
    {
        return response()->json(
            Ciudad::where('id_departamento', $departmentId)
                ->get(['id_ciudad', 'nombre', 'id_departamento'])
        );
    }

    /**
     * Search cities by name (API).
     */
    public function searchCities(Request $request)
    {
        $query = $request->input('q');
        if (!$query) {
            return response()->json([]);
        }

        return response()->json(
            Ciudad::where('nombre', 'like', "{$query}%")
                ->limit(20)
                ->get(['id_ciudad', 'nombre', 'id_departamento'])
        );
    }

    /**
     * Get city details by ID.
     */
    public function getCityDetails($cityId)
    {
        return response()->json(
            Ciudad::find($cityId, ['id_ciudad', 'nombre', 'id_departamento'])
        );
    }
}
