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

        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // 2. Fast Filter: Only process checkout.session.completed
        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['status' => 'ignored']);
        }

        $session = $event->data->object;
        $this->handleCheckoutSessionCompleted($session);

        return response()->json(['status' => 'success']);
    }

    protected function handleCheckoutSessionCompleted($session)
    {
        try {
            // Debug Log: Incoming Session
            Log::info("Stripe Webhook: Processing checkout.session.completed", [
                'session_id' => $session->id,
                'metadata' => $session->metadata ?? 'null',
                'client_reference_id' => $session->client_reference_id ?? 'null',
                'mode' => $session->mode
            ]);

            // 1. Strict Subscription Mode Validation
            if ($session->mode !== 'subscription') {
                Log::warning("Stripe Webhook: Process ignored. Invalid mode: {$session->mode} (Expected: subscription) for Session ID: {$session->id}");
                return;
            }

            if (empty($session->subscription)) {
                Log::error("Stripe Webhook: Subscription ID missing in session: {$session->id}");
                return;
            }

            // 2. Retrieve Pago using METADATA (primary source of truth)
            $pagoId = $session->metadata->pago_id ?? null;

            if (!$pagoId) {
                // Fallback to client_reference_id if metadata is missing
                Log::warning("Stripe Webhook: Metadata pago_id missing. Trying client_reference_id.");
                $pagoId = $session->client_reference_id;
            }

            if (!$pagoId) {
                Log::error("Stripe Webhook: CRITICAL - Pago ID missing in metadata AND client_reference_id for session ID: {$session->id}");
                return;
            }

            $pago = Pago::with('licencia')->find($pagoId);

            if (!$pago) {
                Log::error("Stripe Webhook: Pago record not found in DB for ID: {$pagoId}");
                return;
            }

            Log::info("Stripe Webhook: Pago found.", ['pago_id' => $pago->id, 'estado_actual' => $pago->estado_pago]);

            // 3. Idempotency Check
            if ($pago->estado_pago === 'paid') {
                Log::info("Stripe Webhook: Idempotency check - Pago already processed for ID: {$pago->id}");
                return;
            }

            // 4. Retrieve Subscription details from Stripe
            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                $subscription = $stripe->subscriptions->retrieve($session->subscription);
            } catch (\Exception $e) {
                Log::error("Stripe Webhook: Failed to retrieve subscription {$session->subscription}. Error: " . $e->getMessage());
                return;
            }

            if (!$subscription) {
                Log::error("Stripe Webhook: Subscription object is null for ID: {$session->subscription}");
                return;
            }

            // 5. Update Pago
            $pago->update([
                'estado_pago' => 'paid',
                'stripe_subscription_id' => $session->subscription,
                'fecha_pago' => now(),
                'stripe_session_id' => $session->id,
            ]);

            Log::info("Stripe Webhook: Pago updated to PAID.", ['pago_id' => $pago->id]);

            // 6. Activate License using Stripe Dates
            $licencia = $pago->licencia;

            if ($licencia) {
                // FALLBACK LOGIC: If Stripe dates are missing, use local dates.

                $startDate = isset($subscription->current_period_start)
                    ? \Carbon\Carbon::createFromTimestamp($subscription->current_period_start)
                    : now();

                $endDate = isset($subscription->current_period_end)
                    ? \Carbon\Carbon::createFromTimestamp($subscription->current_period_end)
                    : now()->addDays(30); // Default fallback: 30 days

                if (!isset($subscription->current_period_start) || !isset($subscription->current_period_end)) {
                    Log::warning("Stripe Webhook: Subscription dates missing from Stripe event. Applied fallback dates.", [
                        'subscription_id' => $subscription->id,
                        'fallback_start' => $startDate->toDateTimeString(),
                        'fallback_end' => $endDate->toDateTimeString()
                    ]);
                }

                $licencia->update([
                    'estado' => 'active',
                    'fecha_inicio' => $startDate,
                    'fecha_fin' => $endDate,
                ]);

                Log::info("Stripe Webhook: License activated.", [
                    'licencia_id' => $licencia->id,
                    'start' => $startDate->toDateTimeString(),
                    'end' => $endDate->toDateTimeString()
                ]);
            } else {
                Log::error("Stripe Webhook: License relation missing (NULL) for Pago ID: {$pago->id}");
                // We do NOT rollback generic payment update, as payment WAS received.
                // But this is a critical data consistency issue.
            }
        } catch (\Throwable $e) {
            // Global Catch to prevent 500 response to Stripe
            Log::critical("Stripe Webhook: EXCEPTION in handleCheckoutSessionCompleted", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
