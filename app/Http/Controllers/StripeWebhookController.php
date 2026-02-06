<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Pago;
use App\Models\Licencia;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid Payload'], 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid Signature'], 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleCheckoutSessionCompleted($session);
                break;
            default:
            // Unexpected event type
            // Log::info('Received unknown event type ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleCheckoutSessionCompleted($session)
    {
        // 1. Strict Subscription Mode Validation
        if ($session->mode !== 'subscription') {
            Log::warning("Stripe Webhook: Process ignored. Invalid mode: {$session->mode} (Expected: subscription) for Session ID: {$session->id}");
            return;
        }

        if (empty($session->subscription)) {
            Log::error("Stripe Webhook: Subscription ID missing in session: {$session->id}");
            return;
        }

        // 2. Retrieve Subscription details from Stripe
        // We need the current period start/end from the subscription itself
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $subscription = $stripe->subscriptions->retrieve($session->subscription);
        } catch (\Exception $e) {
            Log::error("Stripe Webhook: Failed to retrieve subscription {$session->subscription}. Error: " . $e->getMessage());
            return;
        }

        $checkoutSessionId = $session->id;
        $paymentIntentId = $session->payment_intent; // May be null in subscription mode first call
        $subscriptionId = $session->subscription;

        // 3. Find Pago
        // We look for stripe_session_id now, or reference if we used it
        $pago = Pago::where('stripe_session_id', $checkoutSessionId)
            ->orWhere('referencia', $checkoutSessionId)
            ->first();

        if (!$pago) {
            Log::error("Stripe Webhook: Pago not found for session ID: {$checkoutSessionId}");
            return;
        }

        // 4. Idempotency Check
        if ($pago->estado_pago === 'paid') {
            Log::info("Stripe Webhook: Pago already processed for session ID: {$checkoutSessionId}");
            return;
        }

        // 5. Update Pago
        $pago->update([
            'estado_pago' => 'paid',
            // In subscriptions, invoice.payment_succeeded handles recurring payments. 
            // This event validates the setup/first payment.
            'stripe_subscription_id' => $subscriptionId,
            'fecha_pago' => now(),
        ]);

        // 6. Activate License using Stripe Dates
        $licencia = Licencia::find($pago->licencia_id);

        if ($licencia) {
            $startDate = \Carbon\Carbon::createFromTimestamp($subscription->current_period_start);
            $endDate = \Carbon\Carbon::createFromTimestamp($subscription->current_period_end);

            $licencia->update([
                'estado' => 'active',
                'fecha_inicio' => $startDate,
                'fecha_fin' => $endDate,
            ]);

            Log::info("Stripe Webhook: License activated for ID: {$licencia->id}. Valid: {$startDate} to {$endDate}");
        } else {
            Log::error("Stripe Webhook: License not found for Pago ID: {$pago->id}");
        }
    }
}
