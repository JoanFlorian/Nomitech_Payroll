<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    /**
     * Show checkout confirmation page.
     */
    public function show(Pago $pago)
    {
        // 1. Strict Security Check: Validate Access via Session ONLY
        if ((int) session('empresa_id') !== (int) $pago->empresa_id) {
            abort(403, 'Unauthorized access to this payment');
        }

        if ($pago->estado_pago === PaymentStatus::PAID->value) {
            return redirect()->route('empleados.index');
        }

        // 2. Correct Relationship Loading
        $pago->load('licencia.plan');
        $plan = $pago->licencia->plan;

        return view('checkout.show', compact('pago', 'plan'));
    }

    /**
     * Create Stripe Checkout Session.
     */
    public function createSession(Pago $pago)
    {
        // 1. Strict Security Check
        if ((int) session('empresa_id') !== (int) $pago->empresa_id) {
            abort(403, 'Unauthorized access to this payment');
        }

        // 2. State Validation
        if ($pago->estado_pago === PaymentStatus::PAID->value) {
            return redirect()->route('empleados.index');
        }
        if ($pago->estado_pago !== PaymentStatus::PENDING->value) {
            return redirect()->route('licencia.pending');
        }

        // 3. Load Plan correctly from Licencia
        $pago->load('licencia.plan');
        $plan = $pago->licencia->plan;

        // 3. Validate Stripe Configuration
        if (!$plan || !$plan->stripe_price_id) {
            return back()->with('error', 'Plan not configured for Stripe (Missing Price ID)');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $user = Auth::user();

            $checkoutSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price' => $plan->stripe_price_id, // ALWAYS use Price ID
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'subscription', // STRICTLY subscription mode

                // 4. Correct Success URL with Session ID
                'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.cancel'),

                'client_reference_id' => $pago->id,
                'customer_email' => $user->correo,

                // 5. Minimal Metadata
                'metadata' => [
                    'pago_id' => $pago->id,
                    'empresa_id' => $pago->empresa_id,
                    'user_id' => $user->id, // Added user_id
                ],
            ]);

            // Guardar ID de sesión en Pago (para seguimiento)
            $pago->update([
                'stripe_session_id' => $checkoutSession->id,
                'referencia' => $checkoutSession->id
            ]);

            return redirect($checkoutSession->url);

        } catch (\Exception $e) {
            return back()->with('error', 'Stripe Error: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar vista de procesamiento mientras se sondea la confirmación de pago.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('licencia.pending');
        }

        // Almacenar en sesión para referencia potencial
        session(['stripe_session_id' => $sessionId]);

        return view('checkout.success', compact('sessionId'));
    }

    /**
     * Punto final de API para verificar el estado del pago (Sondeo).
     * ESTRICTAMENTE DE SÓLO LECTURA para sondeo del front-end.
     * Previene errores 500 evitando acceso a propiedades anidadas profundas en nulos.
     * Delega la lógica de activación al webhook o trabajo de retorno.
     */
    public function checkStatus($sessionId)
    {
        // 1. Cargar con impaciencia Licencia y Plan
        $pago = Pago::with(['licencia.plan', 'plan'])->where('stripe_session_id', $sessionId)->first();

        // Verificación de seguridad: Pago no encontrado
        if (!$pago) {
            return response()->json(['status' => 'pending']);
        }

        // 2. Check Payment Status
        $isPaid = $pago->estado_pago === PaymentStatus::PAID->value;

        // --- FALLBACK PROACTIVO (Webook tardío) ---
        // Si no está pagado, y han pasado más de 20 segundos desde la última actualización (creación de sesión)
        if (!$isPaid && $pago->updated_at->diffInSeconds(Carbon::now()) > 20) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $stripeSession = StripeSession::retrieve($sessionId);

                // Si Stripe dice que ya está completado, activamos proactivamente
                if ($stripeSession->payment_status === 'paid' && $stripeSession->status === 'complete') {
                    $paymentService = app(\App\Services\PaymentService::class);
                    $paymentService->completePayment($pago, $stripeSession->subscription);

                    // Recargar pago para reflejar cambios
                    $pago = $pago->fresh(['licencia']);
                    $isPaid = true;
                }
            } catch (\Exception $e) {
                Log::error("CheckoutController: Fallback check failed for Session: {$sessionId}. Error: " . $e->getMessage());
            }
        }
        // ------------------------------------------

        // 3. Verificar estado de la licencia
        $licencia = $pago->licencia;

        $isLicenseActive = false;
        if ($licencia) {
            $isLicenseActive = $licencia->fecha_fin && Carbon::parse($licencia->fecha_fin)->gt(Carbon::now());
        }

        // 4. Devolver 'paid' SÓLO si se cumplen ambas condiciones
        if ($isPaid && $isLicenseActive) {
            return response()->json([
                'status' => 'paid'
            ]);
        }

        // Predeterminado
        return response()->json(['status' => 'pending']);
    }

    public function cancel()
    {
        return redirect('/#pricing');
    }
}
