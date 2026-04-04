<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\PeriodoLiquidacion;
use App\Models\BenefitLedger;
use App\Models\BenefitBalance;
use App\Services\Benefits\BenefitPaymentService;
use App\Services\Benefits\BenefitAccrualService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractTerminationService
{
    protected BenefitPaymentService $benefitService;
    protected BenefitAccrualService $accrualService;

    public function __construct(BenefitPaymentService $benefitService, BenefitAccrualService $accrualService)
    {
        $this->benefitService = $benefitService;
        $this->accrualService = $accrualService;
    }

    /**
     * Checks if a contract ends within a period and schedules benefits if no continuity.
     * Returns an array with suggested days and whether benefits were scheduled.
     */
    public function handleContractTermination(Contrato $contrato, PeriodoLiquidacion $periodo): array
    {
        $results = [
            'is_terminating' => false,
            'suggested_days' => null,
            'benefits_scheduled' => false,
            'has_continuity' => false,
        ];

        if (!$contrato->fecha_fin) {
            return $results;
        }

        $pInicio = Carbon::parse($periodo->fecha_inicio);
        $pFin = Carbon::parse($periodo->fecha_fin);
        $cFin = Carbon::parse($contrato->fecha_fin);

        // 1. Check if contract ends in this period
        if ($cFin->between($pInicio, $pFin)) {
            $results['is_terminating'] = true;
            $results['suggested_days'] = $this->calculateWorkedDaysInPeriod($contrato, $periodo);

            // 2. Check for labor continuity (new contract within 15 days)
            $hasContinuity = $this->checkLaborContinuity($contrato->doc, $cFin);
            $results['has_continuity'] = $hasContinuity;

            if (!$hasContinuity) {
                $results['benefits_scheduled'] = $this->autoScheduleAllBenefits($contrato, $periodo);
            }
        }

        return $results;
    }

    /**
     * Calculates worked days in a period, handling both start/end limits.
     */
    public function calculateWorkedDaysInPeriod(Contrato $contrato, PeriodoLiquidacion $periodo): int
    {
        $pInicio = Carbon::parse($periodo->fecha_inicio);
        $pFin = Carbon::parse($periodo->fecha_fin);
        $cInicio = Carbon::parse($contrato->fecha_inicio);
        
        // If contract starts after period ends, 0 days
        if ($cInicio->gt($pFin)) return 0;
        
        $cFin = $contrato->fecha_fin ? Carbon::parse($contrato->fecha_fin) : null;
        
        // If contract ends before period starts, 0 days
        if ($cFin && $cFin->lt($pInicio)) return 0;
        
        $efectivoInicio = $cInicio->gt($pInicio) ? $cInicio : $pInicio;
        $efectivoFin = ($cFin && $cFin->lt($pFin)) ? $cFin : $pFin;
        
        // Adjust for 31st day typical in Colombian payroll
        $diaFin = $efectivoFin->day;
        if ($diaFin === 31) {
            $efectivoFin->subDay();
        }

        $diaInicio = $efectivoInicio->day;
        if ($diaInicio === 31) {
             $efectivoInicio->subDay();
        }

        $dias = $efectivoInicio->diffInDays($efectivoFin) + 1;
        
        return min(30, max(0, $dias));
    }

    /**
     * Checks if there's a new contract starting within 15 days of the given end date.
     */
    public function checkLaborContinuity(string $doc, Carbon $endDate): bool
    {
        $limitDate = $endDate->copy()->addDays(15);

        return Contrato::where('doc', $doc)
            ->where('fecha_inicio', '>=', $endDate)
            ->where('fecha_inicio', '<=', $limitDate)
            ->where('activo', true)
            ->exists();
    }

    /**
     * Schedules all pending benefits for the employee in the given period.
     */
    public function autoScheduleAllBenefits(Contrato $contrato, PeriodoLiquidacion $periodo): bool
    {
        // 1. CAUSACIÓN PARCIAL: Antes de agendar pagos, asegurar que los días trabajados en este mes 
        // ya estén sumados al balance de prestaciones.
        $this->accrueCurrentPeriodBenefits($contrato, $periodo);

        $balance = BenefitBalance::where('employee_id', $contrato->doc)
            ->where('tenant_id', $contrato->id_empresa)
            ->first();

        if (!$balance) return false;

        // 2. CORRECCIÓN DE INTERESES: Recalcular globalmente desde el balance de cesantías.
        // La acumulación mensual produce errores de compounding. En terminación, usamos la fórmula legal exacta:
        // Intereses = Cesantías_Acumuladas × Días_Contrato × 0.12 / 360
        $cesantiasBalance = (float) $balance->cesantias_balance;
        $diasContrato = $this->accrualService->calculateDiasAcumuladosContrato($contrato);
        $interesesCorrectos = round($cesantiasBalance * $diasContrato * 0.12 / 360, 2);
        
        // Ajustar el balance de intereses al valor legal correcto
        $balance->intereses_balance = $interesesCorrectos;
        $balance->save();

        $scheduledAny = false;
        $benefitTypes = [
            BenefitLedger::TYPE_PRIMA,
            BenefitLedger::TYPE_CESANTIAS,
            BenefitLedger::TYPE_INTERESES_CESANTIAS,
            BenefitLedger::TYPE_VACACIONES
        ];

        foreach ($benefitTypes as $type) {
            $column = BenefitBalance::balanceColumn($type);
            $amount = (float) $balance->$column;

            if ($amount > 0) {
                try {
                    // Vacaciones se mantienen en días para el ledger; la conversión a pesos se hace
                    // en NominaCalculatorService al sumar al devengado.

                    // REFRESH LOGIC: Remove any existing scheduled payment for this period/benefit
                    // to ensure we use the updated balance (after Mini-Closure).
                    BenefitLedger::where('employee_id', $contrato->doc)
                        ->where('payroll_period_id', $periodo->id_periodo)
                        ->where('benefit_type', $type)
                        ->where('movement_type', BenefitLedger::MOVEMENT_SCHEDULED)
                        ->where('status', BenefitLedger::STATUS_PENDING_PAYROLL)
                        ->delete();

                    $this->benefitService->payBenefit(
                        $contrato->doc,
                        $type,
                        $amount,
                        $contrato->id_empresa,
                        BenefitLedger::PAYMENT_PAYROLL,
                        $periodo->id_periodo,
                        "Liquidación automática por retiro (Balance Actualizado)"
                    );
                    $scheduledAny = true;
                } catch (\Exception $e) {
                    Log::error("Error auto-scheduling benefit {$type} for {$contrato->doc}: " . $e->getMessage());
                }
            }
        }

        // La recalculación del salario se deja al caller original (nomina masiva, Step 3 Store, etc.)
        // para evitar un loop recursivo donde guardarNominaEmpleado → handleContractTermination → guardarNominaEmpleado.

        return $scheduledAny;
    }

    /**
     * Accrues benefits for the days worked in the current open period.
     * This "Mini-Closure" ensures the balance is current before the final payout.
     */
    private function accrueCurrentPeriodBenefits(Contrato $contrato, PeriodoLiquidacion $periodo): void
    {
        $diasTrabajados = $this->calculateWorkedDaysInPeriod($contrato, $periodo);
        
        if ($diasTrabajados <= 0) {
            return;
        }

        $salarioBase = (float) ($contrato->salario_base ?? 0);
        $tenantId = (int) $contrato->id_empresa;
        $employeeId = $contrato->doc;
        $contractId = (int) $contrato->id_contrato;

        $prima = $this->accrualService->calculatePrima($salarioBase, $diasTrabajados);
        $cesantias = $this->accrualService->calculateCesantias($salarioBase, $diasTrabajados);
        $vacaciones = $this->accrualService->calculateVacaciones($salarioBase, $diasTrabajados);

        // Intereses: Recalcular GLOBALMENTE sobre cesantías acumuladas + nuevas del periodo
        $balance = BenefitBalance::findOrCreateFor($employeeId, $tenantId);
        $cesantiasAcumuladas = (float) $balance->cesantias_balance + $cesantias;
        $diasAcumulados = $this->accrualService->calculateDiasAcumuladosContrato($contrato);
        $interesesGlobal = $this->accrualService->calculateInteresesCesantias($cesantiasAcumuladas, $diasAcumulados);
        $intereses = max(0, round($interesesGlobal - (float) $balance->intereses_balance, 2));

        $reference = sprintf(
            'Causación parcial por liquidación final (%d días en periodo %s)',
            $diasTrabajados,
            Carbon::parse($periodo->fecha_inicio)->format('d/m/Y')
        );

        $this->accrualService->createAccrualEntry($tenantId, $employeeId, $contractId, \App\Models\BenefitLedger::TYPE_PRIMA, $prima, $periodo->id_periodo, $reference);
        $this->accrualService->createAccrualEntry($tenantId, $employeeId, $contractId, \App\Models\BenefitLedger::TYPE_CESANTIAS, $cesantias, $periodo->id_periodo, $reference);
        $this->accrualService->createAccrualEntry($tenantId, $employeeId, $contractId, \App\Models\BenefitLedger::TYPE_INTERESES_CESANTIAS, $intereses, $periodo->id_periodo, $reference);
        $this->accrualService->createAccrualEntry($tenantId, $employeeId, $contractId, \App\Models\BenefitLedger::TYPE_VACACIONES, $vacaciones, $periodo->id_periodo, $reference);
    }

    /**
     * Removes auto-scheduled termination benefits for an employee.
     */
    public function removeScheduledTerminationBenefits(string $doc, int $periodId): int
    {
        return BenefitLedger::where('employee_id', $doc)
            ->where('payroll_period_id', $periodId)
            ->where('movement_type', BenefitLedger::MOVEMENT_SCHEDULED)
            ->where('status', BenefitLedger::STATUS_PENDING_PAYROLL)
            ->where('reference', 'like', '%Liquidación automática%')
            ->delete();
    }

    /**
     * Main entry point when a new contract is created/renewed.
     * Checks for previous contract and removes benefits if continuity exists.
     */
    public function handleLaborContinuity(Contrato $nuevoContrato): void
    {
        $idEmpresa = $nuevoContrato->id_empresa;
        $doc = $nuevoContrato->doc;
        $fechaInicio = Carbon::parse($nuevoContrato->fecha_inicio);

        // Find the most recent previous contract for this employee
        $contratoPrevio = Contrato::where('doc', $doc)
            ->where('id_empresa', $idEmpresa)
            ->where('id_contrato', '!=', $nuevoContrato->id_contrato)
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<=', $fechaInicio)
            ->orderByDesc('fecha_fin')
            ->first();

        if ($contratoPrevio) {
            $fechaFinPrevio = Carbon::parse($contratoPrevio->fecha_fin);
            $diasDiferencia = $fechaFinPrevio->diffInDays($fechaInicio);

            if ($diasDiferencia <= Contrato::CONTINUIDAD_DIAS_TOLERANCIA) {
                // Labor continuity found!
                // Search for an active or pending period to consolidate payroll
                $periodoActivo = PeriodoLiquidacion::where('id_empresa', $idEmpresa)
                    ->whereIn('estado', [PeriodoLiquidacion::ESTADO_ABIERTO, PeriodoLiquidacion::ESTADO_PENDIENTE])
                    ->orderByDesc('fecha_inicio')
                    ->first();

                if ($periodoActivo) {
                    // 1. Remove auto-scheduled termination benefits
                    $deleted = $this->removeScheduledTerminationBenefits($doc, $periodoActivo->id_periodo);
                    if ($deleted > 0) {
                        Log::info("Removed {$deleted} auto-scheduled benefits for {$doc} due to labor continuity.");
                    }

                    // 2. CONSOLIDATION AND RECALCULATION
                    // Search if a payroll record exists for the OLD contract in this period
                    $salario = \App\Models\Salario::where('id_contrato', $contratoPrevio->id_contrato)
                        ->where('id_periodo', $periodoActivo->id_periodo)
                        ->first();

                    if ($salario) {
                        // Transfer existing record to the new contract
                        $salario->id_contrato = $nuevoContrato->id_contrato;
                        $salario->save();
                        Log::info("Transferred salary record #{$salario->id_salario} from contract #{$contratoPrevio->id_contrato} to #{$nuevoContrato->id_contrato} due to renewal.");
                    }

                    // 3. TRIGGER RECALCULATION WITH SUMMED DAYS
                    try {
                        $calculator = app(\App\Services\NominaCalculatorService::class);
                        
                        // Calculate days for both parts of the month
                        $diasAnterior = $this->calculateWorkedDaysInPeriod($contratoPrevio, $periodoActivo);
                        $diasNuevo = $this->calculateWorkedDaysInPeriod($nuevoContrato, $periodoActivo);
                        $totalDias = min(30, $diasAnterior + $diasNuevo);

                        $calculator->guardarNominaEmpleado($nuevoContrato->id_contrato, $periodoActivo->id_periodo, [
                            'dias_trabajados' => $totalDias,
                        ], true);
                        
                        Log::info("Automatic payroll update for {$doc} on renewal. Total days: {$totalDias} (Gap: {$diasDiferencia} days).");
                    } catch (\Exception $e) {
                        Log::error("Failed to auto-recalculate payroll on renewal for {$doc}: " . $e->getMessage());
                    }
                }
            }
        }
    }
}
