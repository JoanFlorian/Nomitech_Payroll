<?php

namespace App\Services\Benefits;

use App\Models\BenefitBalance;
use App\Models\BenefitLedger;
use App\Models\Contrato;
use Illuminate\Support\Facades\DB;

class BenefitPaymentService
{
    /**
     * Liquidate a specific benefit for a single employee.
     * Creates a negative payment movement and decrements the balance.
     */
    public function liquidateIndividual(
        string $employeeId,
        string $benefitType,
        float $amount,
        int $tenantId,
        ?string $reference = null
    ): BenefitLedger {
        return DB::transaction(function () use ($employeeId, $benefitType, $amount, $tenantId, $reference) {
            // Amount should be positive on input; stored as negative in ledger
            $paymentAmount = -abs($amount);

            $entry = BenefitLedger::create([
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'contract_id' => null,
                'benefit_type' => $benefitType,
                'movement_type' => BenefitLedger::MOVEMENT_PAYMENT,
                'amount' => $paymentAmount,
                'period_id' => null,
                'source' => BenefitLedger::SOURCE_LIQUIDATION,
                'reference' => $reference ?? 'Liquidación individual ' . BenefitLedger::benefitTypeLabel($benefitType),
            ]);

            $balance = BenefitBalance::findOrCreateFor($employeeId, $tenantId);
            $balance->applyMovement($benefitType, $paymentAmount);

            return $entry;
        });
    }

    /**
     * Mass liquidation: liquidate a specific benefit for all active employees of a tenant.
     * Each employee's current balance for that benefit is fully paid out.
     */
    public function liquidateMass(string $benefitType, int $tenantId): int
    {
        return DB::transaction(function () use ($benefitType, $tenantId) {
            $column = BenefitBalance::balanceColumn($benefitType);

            $balances = BenefitBalance::where('tenant_id', $tenantId)
                ->where($column, '>', 0)
                ->get();

            $count = 0;

            foreach ($balances as $balance) {
                $currentAmount = (float) $balance->$column;
                if ($currentAmount <= 0) {
                    continue;
                }

                $paymentAmount = -$currentAmount;

                BenefitLedger::create([
                    'tenant_id' => $tenantId,
                    'employee_id' => $balance->employee_id,
                    'contract_id' => null,
                    'benefit_type' => $benefitType,
                    'movement_type' => BenefitLedger::MOVEMENT_PAYMENT,
                    'amount' => $paymentAmount,
                    'period_id' => null,
                    'source' => BenefitLedger::SOURCE_LIQUIDATION,
                    'reference' => 'Liquidación masiva ' . BenefitLedger::benefitTypeLabel($benefitType),
                ]);

                $balance->applyMovement($benefitType, $paymentAmount);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Create initial balance movements for a newly registered employee.
     * Only creates entries for values > 0.
     */
    public function createInitialBalances(string $employeeId, int $tenantId, array $balances): void
    {
        DB::transaction(function () use ($employeeId, $tenantId, $balances) {
            $mapping = [
                'prima_inicial' => BenefitLedger::TYPE_PRIMA,
                'cesantias_inicial' => BenefitLedger::TYPE_CESANTIAS,
                'intereses_inicial' => BenefitLedger::TYPE_INTERESES_CESANTIAS,
                'vacaciones_inicial' => BenefitLedger::TYPE_VACACIONES,
            ];

            foreach ($mapping as $key => $benefitType) {
                $amount = (float) ($balances[$key] ?? 0);
                if ($amount <= 0) {
                    continue;
                }

                BenefitLedger::create([
                    'tenant_id' => $tenantId,
                    'employee_id' => $employeeId,
                    'contract_id' => null,
                    'benefit_type' => $benefitType,
                    'movement_type' => BenefitLedger::MOVEMENT_INITIAL,
                    'amount' => $amount,
                    'period_id' => null,
                    'source' => BenefitLedger::SOURCE_MIGRATION,
                    'reference' => 'Saldo inicial al registrar empleado',
                ]);

                $balance = BenefitBalance::findOrCreateFor($employeeId, $tenantId);
                $balance->applyMovement($benefitType, $amount);
            }
        });
    }
}
