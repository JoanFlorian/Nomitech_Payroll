<?php

namespace App\Services\Benefits;

use App\Models\BenefitBalance;
use App\Models\BenefitLedger;
use App\Models\PeriodoLiquidacion;
use App\Models\TipoContrato;
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

            // EXCLUSIÓN: Solo prestación de servicios no genera prestaciones sociales bajo este flujo
            if ($contrato->id_tipo_contrato === TipoContrato::TIPO_PRESTACION_SERVICIOS) {
                Log::info("Saltando causación de beneficios para contrato ID: {$contrato->id_contrato} (Tipo: {$contrato->id_tipo_contrato})");
                continue;
            }

            $salarioBase = (float) $contrato->salario_base;
            $tenantId = (int) $contrato->id_empresa;
            $employeeId = $contrato->doc;
            $contractId = (int) $contrato->id_contrato;

            // Priorizar dias_trabajados_prestacional (incluye incapacidades/licencias como laborados)
            // Fallback a dias_a_trabajar o 30 si no existe.
            $diasTrabajados = (int) ($salario->dias_trabajados_prestacional ?? $salario->dias_a_trabajar ?? 30);

            // Calculate each benefit
            $prima = $this->calculatePrima($salarioBase, $diasTrabajados);
            $cesantias = $this->calculateCesantias($salarioBase, $diasTrabajados);
            $vacaciones = $this->calculateVacaciones($salarioBase, $diasTrabajados);

            // Intereses: Se recalculan GLOBALMENTE sobre el balance acumulado + la nueva cesantía.
            // Primero obtenemos el balance actual de cesantías y los días acumulados desde inicio de contrato.
            $balance = BenefitBalance::findOrCreateFor($employeeId, $tenantId);
            $cesantiasAcumuladas = (float) $balance->cesantias_balance + $cesantias;
            $diasAcumulados = $this->calculateDiasAcumuladosContrato($contrato);
            $interesesGlobal = $this->calculateInteresesCesantias($cesantiasAcumuladas, $diasAcumulados);
            // Solo acruamos la DIFERENCIA vs lo ya acumulado en el balance
            $intereses = max(0, round($interesesGlobal - (float) $balance->intereses_balance, 2));

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

    public function calculateInteresesCesantias(float $cesantias, int $diasTrabajados = 0): float
    {
        // Nueva Fórmula: 12% directo sobre el acumulado total de cesantías
        return round($cesantias * 0.12, 2);
    }

    public function calculateVacaciones(float $salarioBase, int $diasTrabajados): float
    {
        // Fórmula en días: (Días trabajados × 15) / 360
        return round($diasTrabajados * 15 / 360, 2);
    }

    /**
     * Calcula los días acumulados desde el inicio del contrato hasta hoy.
     * Para el cálculo de intereses de cesantías proporcionales.
     */
    public function calculateDiasAcumuladosContrato(\App\Models\Contrato $contrato): int
    {
        $inicio = \Carbon\Carbon::parse($contrato->fecha_inicio);
        $fin = $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin) : now();
        
        // Convención colombiana: meses completos × 30 + días del último mes parcial
        // Ejemplo: Mar 1 → Abr 15 = 1 mes completo (30d) + 15 días = 45
        $mesesCompletos = (int) $inicio->diffInMonths($fin);
        $finMesRestante = $inicio->copy()->addMonths($mesesCompletos);
        $diasRestantes = max(0, (int) $finMesRestante->diffInDays($fin) + 1);
        
        // Si el día de inicio es > 1, ajustar los días del primer mes parcial
        if ($inicio->day > 1 && $mesesCompletos === 0) {
            $diasRestantes = min(30, $diasRestantes);
        } else {
            $diasRestantes = min(30, $diasRestantes);
        }
        
        $totalDias = ($mesesCompletos * 30) + $diasRestantes;
        
        return min(360, max(1, $totalDias));
    }

    /* ── Ledger + Balance ── */

    /**
     * Create a single accrual entry and update the balance incrementally.
     */
    public function createAccrualEntry(
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

        // IDEMPOTENCIA: No crear una nueva causación si ya existe una para este empleado y periodo.
        // Esto permite las "mini-causaciones" en liquidaciones finales sin duplicar valores al cerrar el mes.
        $exists = BenefitLedger::where('employee_id', $employeeId)
            ->where('contract_id', $contractId)
            ->where('benefit_type', $benefitType)
            ->where('period_id', $periodId)
            ->where('movement_type', BenefitLedger::MOVEMENT_ACCRUAL)
            ->exists();

        if ($exists) {
            Log::info("Causación ya existente omitida para empleado {$employeeId}, contrato {$contractId}, tipo {$benefitType}, periodo {$periodId}");
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
