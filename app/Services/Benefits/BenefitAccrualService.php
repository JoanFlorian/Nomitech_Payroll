<?php

namespace App\Services\Benefits;

use App\Models\BenefitBalance;
use App\Models\BenefitLedger;
use App\Models\PeriodoLiquidacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BenefitAccrualService
{
    /**
     * Generate accruals for all employees with settled salaries in a given period.
     * MUST be called inside a DB::transaction (typically from PeriodoLiquidacionController::close).
     */
    public function generateAccrualsForPeriod(PeriodoLiquidacion $periodo): int
    {
        $salarios = $periodo->salarios()
            ->with('contrato')
            ->get();

        $count = 0;

        foreach ($salarios as $salario) {
            $contrato = $salario->contrato;
            if (!$contrato) {
                continue;
            }

            $salarioBase = (float) $contrato->salario_base;
            $tenantId = (int) $contrato->id_empresa;
            $employeeId = $contrato->doc;
            $contractId = (int) $contrato->id_contrato;

            // dias_trabajados: field from colleague's push (fallback to dias_a_trabajar)
            $diasTrabajados = (int) ($salario->dias_trabajados ?? $salario->dias_a_trabajar ?? 30);

            // Calculate each benefit
            $prima = $this->calculatePrima($salarioBase, $diasTrabajados);
            $cesantias = $this->calculateCesantias($salarioBase, $diasTrabajados);
            $intereses = $this->calculateInteresesCesantias($cesantias);
            $vacaciones = $this->calculateVacaciones($salarioBase, $diasTrabajados);

            $reference = sprintf(
                'Causación periodo %s – %s',
                $periodo->fecha_inicio->format('d/m/Y'),
                $periodo->fecha_fin->format('d/m/Y')
            );

            // Create ledger entries and update balances incrementally
            $this->createAccrualEntry($tenantId, $employeeId, $contractId, BenefitLedger::TYPE_PRIMA, $prima, $periodo->id_periodo, $reference);
            $this->createAccrualEntry($tenantId, $employeeId, $contractId, BenefitLedger::TYPE_CESANTIAS, $cesantias, $periodo->id_periodo, $reference);
            $this->createAccrualEntry($tenantId, $employeeId, $contractId, BenefitLedger::TYPE_INTERESES_CESANTIAS, $intereses, $periodo->id_periodo, $reference);
            $this->createAccrualEntry($tenantId, $employeeId, $contractId, BenefitLedger::TYPE_VACACIONES, $vacaciones, $periodo->id_periodo, $reference);

            $count++;
        }

        return $count;
    }

    /* ── Calculation Formulas ── */

    public function calculatePrima(float $salarioBase, int $diasTrabajados): float
    {
        return round($salarioBase * $diasTrabajados / 360, 2);
    }

    public function calculateCesantias(float $salarioBase, int $diasTrabajados): float
    {
        return round($salarioBase * $diasTrabajados / 360, 2);
    }

    public function calculateInteresesCesantias(float $cesantias): float
    {
        return round($cesantias * 0.12, 2);
    }

    public function calculateVacaciones(float $salarioBase, int $diasTrabajados): float
    {
        return round($salarioBase * $diasTrabajados / 720, 2);
    }

    /* ── Ledger + Balance ── */

    /**
     * Create a single accrual entry and update the balance incrementally.
     */
    private function createAccrualEntry(
        int $tenantId,
        string $employeeId,
        int $contractId,
        string $benefitType,
        float $amount,
        int $periodId,
        string $reference
    ): void {
        if ($amount <= 0) {
            return;
        }

        BenefitLedger::create([
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'contract_id' => $contractId,
            'benefit_type' => $benefitType,
            'movement_type' => BenefitLedger::MOVEMENT_ACCRUAL,
            'amount' => $amount,
            'period_id' => $periodId,
            'source' => BenefitLedger::SOURCE_PAYROLL,
            'reference' => $reference,
        ]);

        // Incremental balance update (not full SUM recalculation)
        $balance = BenefitBalance::findOrCreateFor($employeeId, $tenantId);
        $balance->applyMovement($benefitType, $amount);
    }
}
