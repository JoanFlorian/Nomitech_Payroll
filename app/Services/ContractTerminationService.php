<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\PeriodoLiquidacion;
use App\Models\BenefitLedger;
use App\Models\BenefitBalance;
use App\Services\Benefits\BenefitPaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractTerminationService
{
    protected BenefitPaymentService $benefitService;

    public function __construct(BenefitPaymentService $benefitService)
    {
        $this->benefitService = $benefitService;
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
        $balance = BenefitBalance::where('employee_id', $contrato->doc)
            ->where('tenant_id', $contrato->id_empresa)
            ->first();

        if (!$balance) return false;

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
                    // Avoid double scheduling if already exists for this period
                    $exists = BenefitLedger::where('employee_id', $contrato->doc)
                        ->where('payroll_period_id', $periodo->id_periodo)
                        ->where('benefit_type', $type)
                        ->where('movement_type', BenefitLedger::MOVEMENT_SCHEDULED)
                        ->where('status', BenefitLedger::STATUS_PENDING_PAYROLL)
                        ->exists();

                    if (!$exists) {
                        $this->benefitService->payBenefit(
                            $contrato->doc,
                            $type,
                            $amount,
                            $contrato->id_empresa,
                            BenefitLedger::PAYMENT_PAYROLL,
                            $periodo->id_periodo,
                            "Liquidación automática por terminación de contrato (Célebre Liquidación)"
                        );
                        $scheduledAny = true;
                    }
                } catch (\Exception $e) {
                    Log::error("Error auto-scheduling benefit {$type} for {$contrato->doc}: " . $e->getMessage());
                }
            }
        }

        return $scheduledAny;
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

            if ($diasDiferencia <= 15) {
                // Labor continuity found!
                // We need to find the period where the previous contract ended to remove benefits.
                $periodoPrevio = PeriodoLiquidacion::where('id_empresa', $idEmpresa)
                    ->whereDate('fecha_inicio', '<=', $fechaFinPrevio)
                    ->whereDate('fecha_fin', '>=', $fechaFinPrevio)
                    ->first();

                if ($periodoPrevio) {
                    $deleted = $this->removeScheduledTerminationBenefits($doc, $periodoPrevio->id_periodo);
                    if ($deleted > 0) {
                        Log::info("Removed {$deleted} auto-scheduled benefits for {$doc} due to labor continuity (Gap: {$diasDiferencia} days).");

                        // Also sync the Salario record if it exists
                        $salario = \App\Models\Salario::where('id_contrato', $contratoPrevio->id_contrato)
                            ->where('id_periodo', $periodoPrevio->id_periodo)
                            ->first();
                        
                        if ($salario) {
                            // This will trigger the NominaCalculatorService if we want a full sync, 
                            // but for a quick fix we can just use the Model's logic.
                            // However, Salario model doesn't have a 'recalculate' method.
                            // We'll use the NominaCalculatorService to ensure all deductions (EPS/AFP) are also updated if they depended on total_devengado.
                            try {
                                $calculator = app(\App\Services\NominaCalculatorService::class);
                                $calculator->guardarNominaEmpleado($salario->id_contrato, $salario->id_periodo, [
                                    'fecha_pago' => $salario->fecha_pago,
                                    // Use original days to avoid resetting to 30 if it was manually adjusted
                                    'dias_trabajados' => $salario->dias_a_trabajar, 
                                ]);
                                Log::info("Recalculated salary #{$salario->id_salario} for {$doc} after benefit removal.");
                            } catch (\Exception $e) {
                                Log::error("Failed to recalculate salary after benefit removal: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }
    }
}
