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

            // Save Session ID to Pago (for tracking)
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
     * Show processing view while polling for payment confirmation.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('licencia.pending');
        }

        // Store in session for potential reference
        session(['stripe_session_id' => $sessionId]);

        return view('checkout.success', compact('sessionId'));
    }

    /**
     * API Endpoint to check payment status (Polling).
     * STRICTLY READ-ONLY for Front-End Polling.
     * Prevents 500 errors by avoiding deep nested property access on nulls.
     * Delegates activation logic to Webhook or Fallback Job.
     */
    public function checkStatus($sessionId)
    {
        // 1. Eager Load Licencia
        $pago = Pago::with('licencia')->where('stripe_session_id', $sessionId)->first();

        // Safety check: Pago not found
        if (!$pago) {
            return response()->json(['status' => 'pending']);
        }

        // 2. Check Payment Status
        $isPaid = $pago->estado_pago === PaymentStatus::PAID->value;

        // 3. Check License Status (Explicitly check dates to avoid is_active attribute ambiguity)
        $licencia = $pago->licencia;

        // Safety check: Licencia might be null if relationship broken (should not happen but safe check)
        $isLicenseActive = false;
        if ($licencia) {
            $isLicenseActive = $licencia->fecha_fin && $licencia->fecha_fin->gt(now());
        }

        // 4. Return 'paid' ONLY if both conditions met
        if ($isPaid && $isLicenseActive) {
            return response()->json([
                'status' => 'paid'
                // NO user_id returned (as requested for simplicity and safety)
            ]);
        }

        // Default
        return response()->json(['status' => 'pending']);
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }
}
