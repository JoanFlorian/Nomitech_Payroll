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

        // ... rest of function ... keeping logic but I need to make sure I don't delete too much.
        // I will use replace_file_content carefully.


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
    //Succes no debe hacer la logica de negocio
    public function success(Request $request)
    {
        if (!$request->has('session_id')) {
            return redirect()->route('licencia.pending');
        }

        return view('checkout.success');
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }
}
