<?php

namespace App\Services\Benefits;

use App\Models\BenefitBalance;
use App\Models\BenefitLedger;
use App\Models\CesantiasWithdrawal;
use App\Models\Empresa;
use App\Models\Novedad;
use App\Models\PeriodoLiquidacion;
use App\Models\Contrato;
use App\Models\Salario;
use App\Models\SeveranceBatch;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BenefitPaymentService
{
    // ═══════════════════════════════════════════════════════════════════════════
    //  UNIFIED ENTRY POINT — payBenefit()
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Unified method to pay any benefit (prima, cesantías, intereses, vacaciones).
     *
     * Supports two modes:
     *  - direct:  immediate ledger payment, balance reduced now
     *  - payroll: creates scheduled_payment + novedad on active period, finalized on close
     *
     * @param string      $employeeId  Employee document
     * @param string      $benefitType prima|cesantias|intereses_cesantias|vacaciones
     * @param float       $amount      Amount to pay (positive)
     * @param int         $companyId   Tenant company
     * @param string      $paymentMode direct|payroll
     * @param int|null    $periodId    Required when paymentMode = payroll
     * @param string|null $reference   Optional reference text
     *
     * @throws \Exception
     */
    public function payBenefit(
        string $employeeId,
        string $benefitType,
        float $amount,
        int $companyId,
        string $paymentMode = 'direct',
        ?int $periodId = null,
        ?string $reference = null
    ): BenefitLedger {
        if ($paymentMode === BenefitLedger::PAYMENT_PAYROLL && !$periodId) {
            throw new \Exception('Se requiere un periodo de nómina activo para integrar el pago.');
        }

        return DB::transaction(function () use ($employeeId, $benefitType, $amount, $companyId, $paymentMode, $periodId, $reference) {
            $balance = $this->lockAndValidateBalance($employeeId, $companyId, $benefitType, $amount);
            $label = BenefitLedger::benefitTypeLabel($benefitType);

            if ($paymentMode === BenefitLedger::PAYMENT_DIRECT) {
                return $this->processDirectPayment($employeeId, $benefitType, $amount, $companyId, $balance, $reference ?? "Pago inmediato {$label}", $periodId);
            }

            return $this->schedulePayrollPayment($employeeId, $benefitType, $amount, $companyId, $periodId, $balance, $reference ?? "Pago {$label} integrado a nómina");
        });
    }

    /**
     * Direct payment: immediate ledger entry + balance reduction.
     */
    private function processDirectPayment(
        string $employeeId,
        string $benefitType,
        float $amount,
        int $companyId,
        BenefitBalance $balance,
        string $reference,
        ?int $periodId = null
    ): BenefitLedger {
        $entry = BenefitLedger::create([
            'tenant_id' => $companyId,
            'employee_id' => $employeeId,
            'contract_id' => $this->getContractId($employeeId, $companyId),
            'benefit_type' => $benefitType,
            'movement_type' => BenefitLedger::MOVEMENT_PAYMENT,
            'destination' => BenefitLedger::DESTINATION_EMPLOYEE,
            'status' => BenefitLedger::STATUS_PROCESSED,
            'payment_method' => BenefitLedger::PAYMENT_DIRECT,
            'amount' => -abs($amount),
            'period_id' => $periodId,
            'source' => BenefitLedger::SOURCE_LIQUIDATION,
            'reference' => $reference,
        ]);

        $balance->applyMovement($benefitType, -abs($amount));

        return $entry;
    }

    /**
     * Payroll integration: creates a scheduled_payment ledger entry.
     * Balance is NOT reduced until the period is closed.
     *
     * Validates that:
     * 1. The employee has an active contract for this tenant.
     * 2. The employee has a salario record in the active period (pending or liquidated).
     */
    private function schedulePayrollPayment(
        string $employeeId,
        string $benefitType,
        float $amount,
        int $companyId,
        int $periodId,
        BenefitBalance $balance,
        string $reference
    ): BenefitLedger {
        // 1. Validate that the employee has a contract for this tenant
        $hasContract = Contrato::where('doc', $employeeId)
            ->where('id_empresa', $companyId)
            ->exists();

        if (!$hasContract) {
            throw new \Exception("El empleado no tiene un contrato activo asociado a esta empresa.");
        }

        // 2. Validate that the employee has a salario record in this period
        $salario = Salario::whereHas('contrato', function ($q) use ($employeeId) {
            $q->where('doc', $employeeId);
        })
            ->where('id_periodo', $periodId)
            ->first();

        if (!$salario) {
            throw new \Exception("El empleado no tiene un salario liquidado o pendiente en el periodo de nómina activo. Primero debe liquidar la nómina del empleado antes de integrar pagos de prestaciones.");
        }

        // 3. Create scheduled ledger entry (balance not affected yet)
        $entry = BenefitLedger::create([
            'tenant_id' => $companyId,
            'employee_id' => $employeeId,
            'contract_id' => $this->getContractId($employeeId, $companyId),
            'benefit_type' => $benefitType,
            'movement_type' => BenefitLedger::MOVEMENT_SCHEDULED,
            'destination' => BenefitLedger::DESTINATION_EMPLOYEE,
            'status' => BenefitLedger::STATUS_PENDING_PAYROLL,
            'payment_method' => BenefitLedger::PAYMENT_PAYROLL,
            'period_id' => $periodId,
            'payroll_period_id' => $periodId,
            'amount' => -abs($amount),
            'source' => BenefitLedger::SOURCE_LIQUIDATION,
            'reference' => $reference,
        ]);

        return $entry;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  PROCESS SCHEDULED PAYMENTS (called on period close)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Finalize all scheduled_payment entries for a closed period.
     * Converts them from scheduled → payment and applies balance changes.
     *
     * Called by PeriodoLiquidacionController::close().
     */
    public function processScheduledPayments(PeriodoLiquidacion $periodo): int
    {
        return DB::transaction(function () use ($periodo) {
            /** @var \Illuminate\Database\Eloquent\Collection|BenefitLedger[] $scheduled */
            $scheduled = BenefitLedger::where('payroll_period_id', $periodo->id_periodo)
                ->where('movement_type', BenefitLedger::MOVEMENT_SCHEDULED)
                ->where('status', BenefitLedger::STATUS_PENDING_PAYROLL)
                ->lockForUpdate()
                ->get();

            $count = 0;

            foreach ($scheduled as $entry) {
                // Convert scheduled → payment
                $entry->update([
                    'movement_type' => BenefitLedger::MOVEMENT_PAYMENT,
                    'status' => BenefitLedger::STATUS_PROCESSED,
                ]);

                // Now apply balance change
                $balance = BenefitBalance::findOrCreateFor($entry->employee_id, $entry->tenant_id);
                $balance->applyMovement($entry->benefit_type, (float) $entry->amount);

                $count++;
            }

            return $count;
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CESANTÍAS — COMPANY-PAID WITHDRAWAL (pre-deposit)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Partial withdrawal when cesantías have NOT been deposited to the fund.
     * The company pays the employee directly.
     */
    public function withdrawFromCompany(string $document, float $amount, string $reason, int $companyId): CesantiasWithdrawal
    {
        $this->validateWithdrawalReason($reason);

        return DB::transaction(function () use ($document, $amount, $reason, $companyId) {
            $balance = $this->lockAndValidateBalance($document, $companyId, BenefitLedger::TYPE_CESANTIAS, $amount);

            $withdrawal = CesantiasWithdrawal::create([
                'employee_id' => $document,
                'company_id' => $companyId,
                'amount' => $amount,
                'reason' => $reason,
                'payment_origin' => CesantiasWithdrawal::ORIGIN_COMPANY,
                'status' => CesantiasWithdrawal::STATUS_APPROVED,
            ]);

            $reasonLabel = CesantiasWithdrawal::reasonLabel($reason);
            BenefitLedger::create([
                'tenant_id' => $companyId,
                'employee_id' => $document,
                'contract_id' => $this->getContractId($document, $companyId),
                'benefit_type' => BenefitLedger::TYPE_CESANTIAS,
                'movement_type' => BenefitLedger::MOVEMENT_WITHDRAWAL,
                'destination' => BenefitLedger::DESTINATION_EMPLOYEE,
                'status' => BenefitLedger::STATUS_PROCESSED,
                'payment_method' => BenefitLedger::PAYMENT_DIRECT,
                'amount' => -abs($amount),
                'source' => BenefitLedger::SOURCE_LIQUIDATION,
                'reference' => "Retiro Cesantías Pagado por Empresa - {$reasonLabel}",
            ]);

            $balance->applyMovement(BenefitLedger::TYPE_CESANTIAS, -abs($amount));

            return $withdrawal;
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CESANTÍAS — FUND WITHDRAWAL AUTHORIZATION (post-deposit)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Authorize withdrawal when cesantías have ALREADY been deposited to the fund.
     * No balance change — fund handles the actual disbursement.
     */
    public function authorizeFundWithdrawal(string $document, float $amount, string $reason, int $companyId): CesantiasWithdrawal
    {
        $this->validateWithdrawalReason($reason);

        return DB::transaction(function () use ($document, $amount, $reason, $companyId) {
            $this->lockAndValidateBalance($document, $companyId, BenefitLedger::TYPE_CESANTIAS, $amount);

            $withdrawal = CesantiasWithdrawal::create([
                'employee_id' => $document,
                'company_id' => $companyId,
                'amount' => $amount,
                'reason' => $reason,
                'payment_origin' => CesantiasWithdrawal::ORIGIN_FUND,
                'status' => CesantiasWithdrawal::STATUS_APPROVED,
            ]);

            $reasonLabel = CesantiasWithdrawal::reasonLabel($reason);
            BenefitLedger::create([
                'tenant_id' => $companyId,
                'employee_id' => $document,
                'contract_id' => $this->getContractId($document, $companyId),
                'benefit_type' => BenefitLedger::TYPE_CESANTIAS,
                'movement_type' => BenefitLedger::MOVEMENT_AUTHORIZATION,
                'destination' => BenefitLedger::DESTINATION_FUND,
                'status' => BenefitLedger::STATUS_PROCESSED,
                'payment_method' => BenefitLedger::PAYMENT_DIRECT,
                'amount' => 0,
                'source' => BenefitLedger::SOURCE_LIQUIDATION,
                'reference' => "Autorización Retiro Fondo - {$reasonLabel} - \${$amount}",
            ]);

            $this->generateWithdrawalCertificate($withdrawal);

            return $withdrawal;
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CERTIFICATE GENERATION
    // ═══════════════════════════════════════════════════════════════════════════

    public function generateWithdrawalCertificate(CesantiasWithdrawal $withdrawal): string
    {
        $employee = Usuario::where('doc', $withdrawal->employee_id)->firstOrFail();
        $empresa = Empresa::where('id_empresa', $withdrawal->company_id)->firstOrFail();

        $balance = BenefitBalance::where('employee_id', $withdrawal->employee_id)
            ->where('tenant_id', $withdrawal->company_id)
            ->first();

        $currentBalance = $balance ? (float) $balance->cesantias_balance : 0;

        $fileName = "certificado_retiro_{$withdrawal->id}_{$withdrawal->employee_id}.html";
        $path = "certificates/{$fileName}";

        $htmlContent = view('provisiones.certificado_retiro', [
            'withdrawal' => $withdrawal,
            'employee' => $employee,
            'empresa' => $empresa,
            'currentBalance' => $currentBalance,
            'reasonLabel' => CesantiasWithdrawal::reasonLabel($withdrawal->reason),
        ])->render();

        Storage::disk('local')->put($path, $htmlContent);
        $withdrawal->update(['certificate_path' => $path]);

        return $path;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  ANNUAL SEVERANCE FUND DEPOSIT
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Calculates the consignment data for all employees with a positive severance balance.
     * Does NOT persist anything to the database.
     */
    public function calculateConsignmentData(int $companyId, int $year): array
    {
        $balances = BenefitBalance::where('tenant_id', $companyId)
            ->where('cesantias_balance', '>', 0)
            ->with(['usuario', 'usuario.contratos' => function($q) use ($companyId) {
                $q->where('id_empresa', $companyId)->orderByDesc('id_contrato');
            }])
            ->get();

        if ($balances->isEmpty()) {
            return [];
        }

        $groupedByFund = $balances->groupBy(function ($balance) {
            return $balance->usuario->fondo_cesantias ?? 'NO_ASIGNADO';
        });

        $data = [];
        foreach ($groupedByFund as $fondo => $employeeBalances) {
            $employees = [];
            foreach ($employeeBalances as $balance) {
                $employees[] = $this->mapBalanceToConsignmentData($balance, $companyId, $year);
            }

            $data[] = [
                'fund' => $fondo,
                'count' => count($employees),
                'total_amount' => array_sum(array_column($employees, 'amount')),
                'employees' => $employees,
            ];
        }

        return $data;
    }

    /**
     * Maps a single balance record to the standard consignment data format.
     */
    private function mapBalanceToConsignmentData(BenefitBalance $balance, int $companyId, int $year): array
    {
        $usuario = $balance->usuario;
        $contrato = $usuario->contratos->first(); // Loaded in calculateConsignmentData
        
        $docTypes = [1 => 'CC', 2 => 'CE', 3 => 'NIT', 4 => 'TI', 5 => 'PAS', 6 => 'RC', 7 => 'NIT_EXT'];
        $tipoDocName = $usuario && isset($docTypes[$usuario->id_tipo_doc]) ? $docTypes[$usuario->id_tipo_doc] : 'CC';

        // Compute Worked Days (Base 360)
        $diasTrabajados = 360;
        if ($contrato && $contrato->fecha_inicio) {
            $inicio = \Carbon\Carbon::parse($contrato->fecha_inicio);
            $fin = $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin) : \Carbon\Carbon::create($year, 12, 30);

            if ($inicio->year < $year) $inicio = \Carbon\Carbon::create($year, 1, 1);
            if ($fin->year > $year) $fin = \Carbon\Carbon::create($year, 12, 30);

            if ($inicio->year == $year && $inicio->lte($fin)) {
                $months = max(0, $fin->month - $inicio->month);
                $daysStart = min(30, $inicio->day);
                $daysEnd = min(30, $fin->day);
                $diasTrabajados = ($months * 30) + ($daysEnd - $daysStart) + 1;
                if ($inicio->month == 1 && $inicio->day == 1 && $fin->month == 12 && $fin->day >= 30) $diasTrabajados = 360;
            }
        }

        return [
            'tipo_doc' => $tipoDocName,
            'document_number' => $usuario->numero_documento ?? $balance->employee_id,
            'primer_apellido' => $usuario->primer_apellido ?? '',
            'segundo_apellido' => $usuario->segundo_apellido ?? '',
            'primer_nombre' => $usuario->primer_nombre ?? '',
            'otros_nombres' => $usuario->otros_nombres ?? '',
            'fecha_ingreso' => $contrato && $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio)->format('Y-m-d') : '',
            'fecha_retiro' => $contrato && $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin)->format('Y-m-d') : '',
            'tipo_trabajador' => $contrato && $contrato->tipoTrabajador ? $contrato->tipoTrabajador->nombre : 'DEPENDIENTE',
            'salario_base' => $contrato ? $contrato->salario_base : 0,
            'dias_trabajados' => max(1, min(360, $diasTrabajados)),
            'periodo_liquidacion' => "01-01-{$year} a 31-12-{$year}",
            'amount' => (float) $balance->cesantias_balance,
            'fund' => $usuario->fondo_cesantias ?? 'NO_ASIGNADO',
        ];
    }

    /**
     * Finalizes the annual consignment: creates batches, ledgers, and reduces balances.
     */
    public function generarConsignacionAnual(int $companyId, int $year): array
    {
        return DB::transaction(function () use ($companyId, $year) {
            $data = $this->calculateConsignmentData($companyId, $year);
            
            foreach ($data as &$batchData) {
                $batch = SeveranceBatch::create([
                    'empresa_id' => $companyId,
                    'year' => $year,
                    'fondo' => $batchData['fund'],
                    'generated_at' => now(),
                ]);
                $batchData['batch_id'] = $batch->id;

                foreach ($batchData['employees'] as $empData) {
                    $balance = BenefitBalance::where('employee_id', $empData['document_number'])
                        ->where('tenant_id', $companyId)
                        ->lockForUpdate()
                        ->first();

                    if ($balance) {
                        $amount = (float) $balance->cesantias_balance;
                        BenefitLedger::create([
                            'tenant_id' => $companyId,
                            'employee_id' => $balance->employee_id,
                            'contract_id' => $this->getContractId($balance->employee_id, $companyId),
                            'benefit_type' => BenefitLedger::TYPE_CESANTIAS,
                            'movement_type' => BenefitLedger::MOVEMENT_PAYMENT,
                            'destination' => BenefitLedger::DESTINATION_FUND,
                            'status' => BenefitLedger::STATUS_REPORTED,
                            'payment_method' => BenefitLedger::PAYMENT_DIRECT,
                            'amount' => -$amount,
                            'source' => BenefitLedger::SOURCE_LIQUIDATION,
                            'reference' => "Consignación Cesantías Año {$year}",
                            'batch_id' => $batch->id,
                        ]);
                        $balance->applyMovement(BenefitLedger::TYPE_CESANTIAS, -$amount);
                    }
                }
            }
            return $data;
        });
    }

    /**
     * Retrieves the employee data associated with a previously generated batch.
     */
    public function getBatchEmployees(int $batchId): array
    {
        $batch = SeveranceBatch::findOrFail($batchId);
        $ledgers = BenefitLedger::where('batch_id', $batchId)
            ->with(['usuario', 'contrato.tipoTrabajador'])
            ->get();

        $employees = [];
        $docTypes = [1 => 'CC', 2 => 'CE', 3 => 'NIT', 4 => 'TI', 5 => 'PAS', 6 => 'RC', 7 => 'NIT_EXT'];

        foreach ($ledgers as $ledger) {
            $usuario = $ledger->usuario;
            $contrato = $ledger->contrato;
            
            $employees[] = [
                'tipo_doc' => $usuario && isset($docTypes[$usuario->id_tipo_doc]) ? $docTypes[$usuario->id_tipo_doc] : 'CC',
                'document_number' => $ledger->employee_id,
                'primer_apellido' => $usuario->primer_apellido ?? '',
                'segundo_apellido' => $usuario->segundo_apellido ?? '',
                'primer_nombre' => $usuario->primer_nombre ?? '',
                'otros_nombres' => $usuario->otros_nombres ?? '',
                'fecha_ingreso' => $contrato && $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio)->format('Y-m-d') : '',
                'fecha_retiro' => $contrato && $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin)->format('Y-m-d') : '',
                'tipo_trabajador' => $contrato && $contrato->tipoTrabajador ? $contrato->tipoTrabajador->nombre : 'DEPENDIENTE',
                'salario_base' => $contrato ? $contrato->salario_base : 0,
                // We use the absolute value of the ledger amount (stored as negative for payments)
                'amount' => abs((float)$ledger->amount),
                'fund' => $batch->fondo,
                'year' => $batch->year,
                // Estimated days (since we don't store them in ledger, we approximate for the report)
                'dias_trabajados' => 360, 
                'periodo_liquidacion' => "01-01-{$batch->year} a 31-12-{$batch->year}",
            ];
        }

        return $employees;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  MASS LIQUIDATION
    // ═══════════════════════════════════════════════════════════════════════════

    public function liquidateMass(string $benefitType, int $tenantId, string $paymentMode = 'direct', ?int $periodId = null): array
    {
        if ($paymentMode === BenefitLedger::PAYMENT_PAYROLL && !$periodId) {
            throw new \Exception('Se requiere un periodo de nómina activo para integrar la liquidación masiva.');
        }

        return DB::transaction(function () use ($benefitType, $tenantId, $paymentMode, $periodId) {
            $column = BenefitBalance::balanceColumn($benefitType);
            $label = BenefitLedger::benefitTypeLabel($benefitType);

            /** @var \Illuminate\Database\Eloquent\Collection|BenefitBalance[] $balances */
            $balances = BenefitBalance::where('tenant_id', $tenantId)
                ->with(['usuario'])
                ->get();

            $count = 0;
            $skipped = [];

            foreach ($balances as $balance) {
                $currentAmount = (float) $balance->$column;

                // Validation for Payroll mode: employee must have a salary record in the period
                if ($paymentMode === BenefitLedger::PAYMENT_PAYROLL) {
                    // Exclude Cesantias and Vacations from regular payroll integrations
                    if (in_array($benefitType, [BenefitLedger::TYPE_CESANTIAS, BenefitLedger::TYPE_VACACIONES])) {
                        $skipped[] = [
                            'doc' => $balance->employee_id,
                            'name' => $balance->usuario ? $balance->usuario->nombre_completo : $balance->employee_id,
                            'reason' => "{$benefitType}_not_allowed_in_payroll"
                        ];
                        continue;
                    }

                    $salario = Salario::whereHas('contrato', function ($q) use ($balance) {
                        $q->where('doc', $balance->employee_id);
                    })
                        ->where('id_periodo', $periodId)
                        ->first();

                    if (!$salario) {
                        $skipped[] = [
                            'doc' => $balance->employee_id,
                            'name' => $balance->usuario ? $balance->usuario->nombre_completo : $balance->employee_id,
                            'reason' => 'no_salary'
                        ];
                        continue;
                    }
                }

                if ($currentAmount <= 0) {
                    $skipped[] = [
                        'doc' => $balance->employee_id,
                        'name' => $balance->usuario ? $balance->usuario->nombre_completo : $balance->employee_id,
                        'reason' => 'zero_balance'
                    ];
                    continue;
                }

                if ($paymentMode === BenefitLedger::PAYMENT_DIRECT) {
                    if ($benefitType === BenefitLedger::TYPE_CESANTIAS) {
                        // Las Cesantías en pago directo NUNCA se pagan en efectivo al empleado,
                        // sino que se consignan al Fondo de Cesantías.
                        // Saltamos el descuento aquí; "generarConsignacionAnual" se encargará.
                        $count++;
                        continue;
                    }

                    BenefitLedger::create([
                        'tenant_id' => $tenantId,
                        'employee_id' => $balance->employee_id,
                        'contract_id' => $this->getContractId($balance->employee_id, $tenantId),
                        'benefit_type' => $benefitType,
                        'movement_type' => BenefitLedger::MOVEMENT_PAYMENT,
                        'destination' => BenefitLedger::DESTINATION_EMPLOYEE,
                        'status' => BenefitLedger::STATUS_PROCESSED,
                        'payment_method' => BenefitLedger::PAYMENT_DIRECT,
                        'amount' => -$currentAmount,
                        'period_id' => $periodId,
                        'source' => BenefitLedger::SOURCE_LIQUIDATION,
                        'reference' => 'Liquidación masiva ' . $label,
                    ]);

                    $balance->applyMovement($benefitType, -$currentAmount);
                } else {
                    // PAYMENT_PAYROLL

                    // Prevent duplicates if already scheduled for this period
                    $exists = BenefitLedger::where('employee_id', $balance->employee_id)
                        ->where('payroll_period_id', $periodId)
                        ->where('benefit_type', $benefitType)
                        ->where('movement_type', BenefitLedger::MOVEMENT_SCHEDULED)
                        ->where('status', BenefitLedger::STATUS_PENDING_PAYROLL)
                        ->exists();

                    if ($exists) {
                        $skipped[] = [
                            'doc' => $balance->employee_id,
                            'name' => $balance->usuario ? $balance->usuario->nombre_completo : $balance->employee_id,
                            'reason' => 'already_scheduled'
                        ];
                        continue;
                    }

                    BenefitLedger::create([
                        'tenant_id' => $tenantId,
                        'employee_id' => $balance->employee_id,
                        'contract_id' => $this->getContractId($balance->employee_id, $tenantId),
                        'benefit_type' => $benefitType,
                        'movement_type' => BenefitLedger::MOVEMENT_SCHEDULED,
                        'destination' => BenefitLedger::DESTINATION_EMPLOYEE,
                        'status' => BenefitLedger::STATUS_PENDING_PAYROLL,
                        'payment_method' => BenefitLedger::PAYMENT_PAYROLL,
                        'period_id' => $periodId,
                        'payroll_period_id' => $periodId,
                        'amount' => -$currentAmount,
                        'source' => BenefitLedger::SOURCE_LIQUIDATION,
                        'reference' => "Liquidación masiva {$label} integrada a nómina",
                    ]);

                    // Instantly update the employee's salary total so the UI reflects the added benefit
                    $input = [
                        'fecha_pago' => $salario->fecha_pago ?? now()->toDateString(),
                        'horas_extra' => (float) ($salario->horas_extra ?? 0),
                        'recargos' => max(0, (float) ($salario->valor_horas_extras_recargos ?? 0) - (float) ($salario->horas_extra ?? 0)),
                        'bonificaciones' => (float) ($salario->bonificaciones ?? 0),
                        'comisiones' => (float) ($salario->comisiones ?? 0),
                        'otros_devengos' => (float) ($salario->otros_devengos ?? 0),
                        'auxilio_transporte' => (float) ($salario->auxilio_transporte ?? 0),
                        'retencion_fuente' => (float) ($salario->retencion_fuente ?? 0),
                        'embargo_fiscal' => (float) ($salario->embargo_fiscal ?? 0),
                        'pension_voluntaria' => (float) ($salario->pension_voluntaria ?? 0),
                        'dias_trabajados' => (int) ($salario->dias_a_trabajar ?? 30),
                        'limpiar_novedades' => true,
                    ];

                    app(\App\Services\NominaCalculatorService::class)->guardarNominaEmpleado(
                        $salario->id_contrato,
                        $periodId,
                        $input,
                        true // Force save even if estado is liquidado
                    );
                }

                $count++;
            }

            return [
                'count' => $count,
                'skipped' => $skipped
            ];
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  LEGACY ALIASES
    // ═══════════════════════════════════════════════════════════════════════════

    public function liquidarCesantiasParcial(string $document, float $amount, string $reason, int $companyId): CesantiasWithdrawal
    {
        return $this->withdrawFromCompany($document, $amount, $reason, $companyId);
    }

    public function liquidateIndividual(string $employeeId, string $benefitType, float $amount, int $tenantId, ?string $reference = null): BenefitLedger
    {
        return $this->payBenefit($employeeId, $benefitType, $amount, $tenantId, 'direct', null, $reference);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  INITIAL BALANCES (employee registration)
    // ═══════════════════════════════════════════════════════════════════════════

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
                if ($amount <= 0)
                    continue;

                BenefitLedger::create([
                    'tenant_id' => $tenantId,
                    'employee_id' => $employeeId,
                    'contract_id' => $this->getContractId($employeeId, $tenantId),
                    'benefit_type' => $benefitType,
                    'movement_type' => BenefitLedger::MOVEMENT_INITIAL,
                    'amount' => $amount,
                    'source' => BenefitLedger::SOURCE_MIGRATION,
                    'reference' => 'Saldo inicial al registrar empleado',
                ]);

                $balance = BenefitBalance::findOrCreateFor($employeeId, $tenantId);
                $balance->applyMovement($benefitType, $amount);
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  LEGAL DATE WARNINGS (advisory only, never blocking)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Returns an advisory warning string if a benefit payment is close to
     * or past its legal deadline. Never blocks the action.
     */
    public static function getLegalDateWarning(string $benefitType, bool $hasPending = true): ?string
    {
        if (!$hasPending) {
            return null;
        }

        $now = now();
        $month = (int) $now->format('m');
        $day = (int) $now->format('d');
        $year = (int) $now->format('Y');

        return match ($benefitType) {
            BenefitLedger::TYPE_PRIMA => self::getPrimaWarning($month, $day, $year),

            BenefitLedger::TYPE_INTERESES_CESANTIAS => ($month === 1)
            ? "Los intereses de cesantías deben pagarse antes del 31 de enero de {$year}."
            : null,

            BenefitLedger::TYPE_CESANTIAS => ($month === 1 || ($month === 2 && $day <= 14))
            ? "La consignación al fondo debe realizarse antes del 14 de febrero de {$year}."
            : null,

            default => null,
        };
    }

    private static function getPrimaWarning(int $month, int $day, int $year): ?string
    {
        // First semester: deadline Jun 30
        if ($month >= 5 && $month <= 6) {
            return "La prima del primer semestre debe pagarse antes del 30 de junio de {$year}.";
        }
        // Second semester: deadline Dec 20
        if ($month >= 11 && $month <= 12) {
            return "La prima del segundo semestre debe pagarse antes del 20 de diciembre de {$year}.";
        }
        return null;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════════════

    private function getContractId(string $employeeId, int $companyId): ?int
    {
        $contrato = Contrato::where('doc', $employeeId)
            ->where('id_empresa', $companyId)
            ->where('estado', Contrato::ESTADO_ACTIVO)
            ->first();

        if (!$contrato) {
            // Log fallback or get any contract if none is active
            $contrato = Contrato::where('doc', $employeeId)
                ->where('id_empresa', $companyId)
                ->orderByDesc('id_contrato')
                ->first();
        }

        return $contrato ? $contrato->id_contrato : null;
    }

    private function validateWithdrawalReason(string $reason): void
    {
        if (!in_array($reason, [CesantiasWithdrawal::REASON_HOUSING, CesantiasWithdrawal::REASON_EDUCATION], true)) {
            throw new \InvalidArgumentException("Motivo de retiro inválido: {$reason}.");
        }
    }

    private function lockAndValidateBalance(string $document, int $companyId, string $benefitType, float $amount): BenefitBalance
    {
        $balance = BenefitBalance::where('employee_id', $document)
            ->where('tenant_id', $companyId)
            ->lockForUpdate()
            ->first();

        $column = BenefitBalance::balanceColumn($benefitType);
        $current = $balance ? (float) $balance->$column : 0;

        if ($current < $amount) {
            $label = BenefitLedger::benefitTypeLabel($benefitType);
            $unit = ($benefitType === BenefitLedger::TYPE_VACACIONES) ? 'días' : '$';
            $displayAmount = ($unit === '$') ? "\${$amount}" : "{$amount} dias";
            $displayCurrent = ($unit === '$') ? "\${$current}" : "{$current} dias";
            
            throw new \Exception("El monto solicitado ({$displayAmount}) supera el saldo disponible de {$label} ({$displayCurrent}).");
        }

        return $balance;
    }

    /**
     * Generates a ZIP file containing CSVs for each severance fund for a given batch of data.
     * Returns the temporary file path.
     */
    public function generateConsignmentZip(array $batchesData, int $year): ?string
    {
        if (empty($batchesData)) {
            return null;
        }

        $files = [];
        foreach ($batchesData as $batch) {
            $fundSlug = strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $batch['fund'])));
            $fileName = "cesantias_{$fundSlug}_{$year}.csv";
            $files[$fileName] = $this->formatConsignmentCsv($batch['employees'], $year);
        }

        if (count($files) === 1) {
            $fileName = array_key_first($files);
            $tempPath = tempnam(sys_get_temp_dir(), 'ces_') . '.csv';
            file_put_contents($tempPath, $files[$fileName]);
            return $tempPath;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'ces_') . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($files as $name => $content) {
                $zip->addFromString($name, $content);
            }
            $zip->close();
        }

        return $tempPath;
    }

    /**
     * Replicates the exact CSV formatting logic from ProvisionesController.
     */
    private function formatConsignmentCsv(array $employees, int $year): string
    {
        $csvContent = "\xEF\xBB\xBF";
        $csvContent .= "Tipo de documento;Número de documento;Primer apellido;Segundo apellido;Primer nombre;Segundo nombre;Fecha de ingreso del trabajador;Fecha de retiro;Tipo de trabajador;Salario base de liquidación;Días trabajados en el período;Período de liquidación;Fondo de Cesantías;Valor de cesantías a consignar;Tipo de liquidación\n";

        foreach ($employees as $emp) {
            $tipoDoc = str_replace(';', '', $emp['tipo_doc'] ?? '');
            $document = '="' . str_replace(['"', ';'], '', $emp['document_number'] ?? '') . '"';
            $primerApellido = str_replace(';', '', $emp['primer_apellido'] ?? '');
            $segundoApellido = str_replace(';', '', $emp['segundo_apellido'] ?? '');
            $primerNombre = str_replace(';', '', $emp['primer_nombre'] ?? '');
            $otrosNombres = str_replace(';', '', $emp['otros_nombres'] ?? '');
            $fechaIngreso = str_replace(';', '', $emp['fecha_ingreso'] ?? '');
            $fechaRetiro = str_replace(';', '', $emp['fecha_retiro'] ?? '');
            $tipoTrabajador = str_replace(';', '', $emp['tipo_trabajador'] ?? '');
            $salarioBase = number_format((float) ($emp['salario_base'] ?? 0), 2, '.', '');
            $diasTrabajados = $emp['dias_trabajados'] ?? 0;
            $periodo = str_replace(';', '', $emp['periodo_liquidacion'] ?? '');
            $fondo = str_replace(';', '', $emp['fund'] ?? '');
            $amount = number_format((float) ($emp['amount'] ?? 0), 2, '.', '');
            $tipoLiquidacion = 'anual';

            $csvContent .= "{$tipoDoc};{$document};{$primerApellido};{$segundoApellido};{$primerNombre};{$otrosNombres};{$fechaIngreso};{$fechaRetiro};{$tipoTrabajador};{$salarioBase};{$diasTrabajados};{$periodo};{$fondo};{$amount};{$tipoLiquidacion}\n";
        }

        return $csvContent;
    }
}
