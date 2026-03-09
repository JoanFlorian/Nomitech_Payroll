<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Licencia;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\Payroll\PeriodoAutomationService;

class PaymentService
{
    protected $periodoAutomationService;

    public function __construct(PeriodoAutomationService $periodoAutomationService)
    {
        $this->periodoAutomationService = $periodoAutomationService;
    }

    /**
     * Completa el proceso de pago y activa la licencia de forma segura.
     * Centraliza la lógica para ser usada por Webhooks y Polling.
     */
    public function completePayment(Pago $pago, string $subscriptionId, $stripeSubscription = null): bool
    {
        // Bloqueo atómico para evitar race conditions (Webhook vs Polling)
        return Cache::lock('activate_pago_' . $pago->id, 10)->get(function () use ($pago, $subscriptionId, $stripeSubscription) {

            return DB::transaction(function () use ($pago, $subscriptionId, $stripeSubscription) {
                // Re-obtener el pago para tener el estado más fresco dentro de la transacción
                $pago = $pago->fresh(['licencia.plan', 'plan']);

                if ($pago->estado_pago === PaymentStatus::PAID->value) {
                    Log::info("PaymentService: Pago already processed for ID: {$pago->id}");
                    return true;
                }

                // 1. Actualizar el pago
                $pago->update([
                    'estado_pago' => PaymentStatus::PAID->value,
                    'stripe_subscription_id' => $subscriptionId,
                    'fecha_pago' => now(),
                ]);

                // 2. Activar la licencia
                $licencia = $pago->licencia;
                if ($licencia) {
                    $startDate = now();
                    $endDate = null;

                    // Si tenemos el objeto de suscripción de Stripe, priorizamos sus fechas
                    if ($stripeSubscription && isset($stripeSubscription->current_period_end)) {
                        $startDate = Carbon::createFromTimestamp($stripeSubscription->current_period_start);
                        $endDate = Carbon::createFromTimestamp($stripeSubscription->current_period_end);
                    } else {
                        // Si no, usamos la duración configurada en nuestro Plan (nuestro arreglo anterior)
                        $planDuracion = $pago->plan->duracion ?? 30;
                        $endDate = now()->addDays((int) $planDuracion);
                    }

                    $licencia->update([
                        'fecha_inicio' => $startDate,
                        'fecha_fin' => $endDate,
                    ]);

                    // 2b. Auto-generar periodo de liquidación inicial/siguiente
                    $this->periodoAutomationService->handleLicenseActivation($pago->empresa);

                    Log::info("PaymentService: License activated proactively/webhook.", [
                        'pago_id' => $pago->id,
                        'licencia_id' => $licencia->id,
                        'fecha_fin' => $endDate->toDateTimeString()
                    ]);
                }

                return true;
            });
        }) ?? false;
    }
}
