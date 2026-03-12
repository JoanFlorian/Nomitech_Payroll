<?php

namespace App\Http\Controllers;

use App\Models\BenefitBalance;
use App\Models\BenefitLedger;
use App\Models\CesantiasWithdrawal;
use App\Models\Contrato;
use App\Models\PeriodoLiquidacion;
use App\Services\Benefits\BenefitPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProvisionesController extends Controller
{
    /**
     * Dashboard: balance summary cards + table of balances per employee.
     */
    public function index()
    {
        $empresaId = session('empresa_id');

        $balances = BenefitBalance::where('tenant_id', $empresaId)
            ->where(function ($query) {
                $query->whereHas('usuario.contratos', function ($q) {
                    $q->where('estado_laboral', Contrato::ESTADO_LABORAL_ACTIVO)
                        ->orWhere('estado_nomina', Contrato::ESTADO_NOMINA_PENDIENTE);
                })
                    ->orWhere('prima_balance', '>', 0)
                    ->orWhere('cesantias_balance', '>', 0)
                    ->orWhere('intereses_balance', '>', 0)
                    ->orWhere('vacaciones_balance', '>', 0);
            })
            ->with(['usuario'])
            ->orderBy('employee_id')
            ->paginate(4);

        $totals = [
            'prima' => $balances->sum('prima_balance'),
            'cesantias' => $balances->sum('cesantias_balance'),
            'intereses' => $balances->sum('intereses_balance'),
            'vacaciones' => $balances->sum('vacaciones_balance'),
        ];
        $totals['total'] = $totals['prima'] + $totals['cesantias'] + $totals['intereses'] + $totals['vacaciones'];

        // Active payroll period (for payroll integration option)
        $activePeriodId = session('active_period_id');
        $activePeriod = $activePeriodId
            ? PeriodoLiquidacion::find($activePeriodId)
            : PeriodoLiquidacion::where('id_empresa', $empresaId)
                ->where('estado', PeriodoLiquidacion::ESTADO_ABIERTO)
                ->latest('id_periodo')
                ->first();

        // Legal date warnings (advisory only)
        $warnings = collect([
            BenefitLedger::TYPE_PRIMA => $totals['prima'] > 0,
            BenefitLedger::TYPE_INTERESES_CESANTIAS => $totals['intereses'] > 0,
            BenefitLedger::TYPE_CESANTIAS => $totals['cesantias'] > 0,
        ])->map(fn($hasPending, $type) => BenefitPaymentService::getLegalDateWarning($type, $hasPending))
            ->filter()
            ->values();

        return view('provisiones.index', compact('balances', 'totals', 'activePeriod', 'warnings'));
    }

    /**
     * Ledger history for a specific employee.
     */
    public function historial(string $doc, \Illuminate\Http\Request $request)
    {
        $empresaId = session('empresa_id');

        $query = BenefitLedger::forTenant($empresaId)
            ->forEmployee($doc);

        // Filters
        if ($request->filled('type')) {
            $query->where('benefit_type', $request->type);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->filled('period')) {
            // period comes as YYYY-MM from <input type="month">
            $parts = explode('-', $request->period);
            if (count($parts) === 2) {
                $query->whereYear('created_at', $parts[0])
                    ->whereMonth('created_at', $parts[1]);
            }
        }

        $movements = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $contrato = Contrato::where('doc', $doc)
            ->where('id_empresa', $empresaId)
            ->with('usuario')
            ->first();

        $empleadoNombre = $contrato && $contrato->usuario
            ? $contrato->usuario->nombre_completo
            : $doc;

        return view('provisiones.historial', compact('movements', 'empleadoNombre', 'doc'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  UNIFIED BENEFIT PAYMENT (direct / payroll)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * POST /provisiones/pagar-prestacion
     *
     * Unified endpoint for ALL benefit payments:
     *  - Prima, Intereses, Vacaciones → direct or payroll
     *  - Cesantías → sub-modes: retiro_empresa, autorizacion_fondo
     */
    public function pagarPrestacion(Request $request, BenefitPaymentService $service)
    {
        $request->validate([
            'employee_id' => 'required|string|max:20',
            'benefit_type' => 'required|in:prima,cesantias,intereses_cesantias,vacaciones',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:direct,payroll',
            'cesantias_mode' => 'sometimes|in:retiro_empresa,autorizacion_fondo',
            'reason' => 'sometimes|required_if:cesantias_mode,retiro_empresa,autorizacion_fondo|in:housing,education',
        ]);

        try {
            $empresaId = session('empresa_id');
            $benefitType = $request->input('benefit_type');
            $paymentMode = $request->input('payment_mode');
            $employeeId = $request->input('employee_id');
            $amount = (float) $request->input('amount');

            // ── Cesantías have sub-flows ──
            if ($benefitType === 'cesantias') {
                $cesantiasMode = $request->input('cesantias_mode', 'retiro_empresa');
                $reason = $request->input('reason', 'housing');

                if ($cesantiasMode === 'autorizacion_fondo') {
                    // Post-deposit: certificate only, NO balance change
                    $withdrawal = $service->authorizeFundWithdrawal($employeeId, $amount, $reason, $empresaId);
                    return back()->with('success', 'Autorización de retiro registrada. Certificado ID #' . $withdrawal->id);
                }

                // retiro_empresa: if direct → withdrawFromCompany, if payroll → falls through to payBenefit
                if ($cesantiasMode === 'retiro_empresa' && $paymentMode === 'direct') {
                    $service->withdrawFromCompany($employeeId, $amount, $reason, $empresaId);
                    return back()->with('success', 'Retiro de cesantías (pago empresa) registrado exitosamente.');
                }

                // retiro_empresa + payroll → falls through to normal payBenefit flow below
            }

            // ── Normal flow for all other benefit types (and cesantías pago directo) ──
            $activePeriodId = session('active_period_id');
            $period = $activePeriodId
                ? PeriodoLiquidacion::find($activePeriodId)
                : PeriodoLiquidacion::where('id_empresa', $empresaId)
                    ->where('estado', PeriodoLiquidacion::ESTADO_ABIERTO)
                    ->latest('id_periodo')
                    ->first();

            $periodId = $period ? $period->id_periodo : null;

            if ($paymentMode === 'payroll' && !$periodId) {
                return back()->with('error', 'No hay un periodo de nómina abierto para integrar el pago.');
            }

            $service->payBenefit($employeeId, $benefitType, $amount, $empresaId, $paymentMode, $periodId);

            $label = BenefitLedger::benefitTypeLabel($benefitType);
            $modeLabel = $paymentMode === 'payroll' ? 'programado en nómina' : 'pago inmediato';

            return back()->with('success', "{$label} — {$modeLabel} registrado exitosamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  MASS LIQUIDATION
    // ═══════════════════════════════════════════════════════════════════════════

    public function liquidarMasivo(Request $request, BenefitPaymentService $service)
    {
        $request->validate([
            'benefit_type' => 'required|in:prima,cesantias,intereses_cesantias,vacaciones',
            'payment_mode' => 'required|in:direct,payroll',
        ]);

        try {
            $empresaId = session('empresa_id');
            $benefitType = $request->input('benefit_type');
            $paymentMode = $request->input('payment_mode');
            $activePeriodId = session('active_period_id');
            $period = $activePeriodId
                ? PeriodoLiquidacion::find($activePeriodId)
                : PeriodoLiquidacion::where('id_empresa', $empresaId)
                    ->where('estado', PeriodoLiquidacion::ESTADO_ABIERTO)
                    ->latest('id_periodo')
                    ->first();

            $periodId = $period ? $period->id_periodo : null;

            if ($paymentMode === 'payroll' && !$periodId) {
                return back()->with('error', 'No hay un periodo de nómina abierto para integrar la liquidación masiva.');
            }

            $result = $service->liquidateMass($benefitType, $empresaId, $paymentMode, $periodId);

            $label = BenefitLedger::benefitTypeLabel($benefitType);
            $modeLabel = $paymentMode === 'payroll' ? 'programado en nómina' : 'pago inmediato';

            // Flash structured data for the aesthetic modal
            session()->flash('mass_liquidation_results', [
                'processed_count' => $result['count'],
                'skipped_employees' => $result['skipped'], // Array of ['doc' => ..., 'name' => ...]
                'benefit_label' => $label,
                'mode_label' => $modeLabel,
                'payment_mode' => $paymentMode,
            ]);

            return back();
        } catch (\Exception $e) {
            return back()->with('error', 'Error en liquidación masiva: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CESANTÍAS — COMPANY-PAID WITHDRAWAL
    // ═══════════════════════════════════════════════════════════════════════════

    public function retiroEmpresa(Request $request, BenefitPaymentService $service)
    {
        $request->validate([
            'employee_id' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|in:housing,education',
        ]);

        try {
            $service->withdrawFromCompany(
                $request->input('employee_id'),
                (float) $request->input('amount'),
                $request->input('reason'),
                session('empresa_id')
            );
            return back()->with('success', 'Retiro de cesantías (pago directo empresa) registrado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar retiro: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CESANTÍAS — FUND AUTHORIZATION
    // ═══════════════════════════════════════════════════════════════════════════

    public function autorizacionFondo(Request $request, BenefitPaymentService $service)
    {
        $request->validate([
            'employee_id' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|in:housing,education',
        ]);

        try {
            $withdrawal = $service->authorizeFundWithdrawal(
                $request->input('employee_id'),
                (float) $request->input('amount'),
                $request->input('reason'),
                session('empresa_id')
            );
            return back()->with('success', 'Autorización de retiro registrada. Certificado ID #' . $withdrawal->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al autorizar retiro: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CERTIFICATE DOWNLOAD
    // ═══════════════════════════════════════════════════════════════════════════

    public function descargarCertificado($withdrawalId)
    {
        $empresaId = session('empresa_id');

        $withdrawal = CesantiasWithdrawal::where('company_id', $empresaId)
            ->findOrFail($withdrawalId);

        if (!$withdrawal->certificate_path || !Storage::disk('local')->exists($withdrawal->certificate_path)) {
            return back()->with('error', 'El certificado no se encuentra disponible.');
        }

        $content = Storage::disk('local')->get($withdrawal->certificate_path);
        return response($content)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'inline; filename="certificado_retiro_' . $withdrawal->id . '.html"');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  ANNUAL CONSIGNMENT (per-fund TXT files)
    // ═══════════════════════════════════════════════════════════════════════════

    public function generarConsignacionAnual(Request $request, BenefitPaymentService $service)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        try {
            $empresaId = session('empresa_id');
            $year = (int) $request->input('year');

            $batchesData = $service->generarConsignacionAnual($empresaId, $year);

            if (empty($batchesData)) {
                return back()->with('info', 'No hay saldos de cesantías para consignar en el año seleccionado.');
            }

            $files = [];
            foreach ($batchesData as $batch) {
                $fundSlug = strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $batch['fund'])));
                $fileName = "cesantias_{$fundSlug}_{$year}.csv";

                // Add UTF-8 BOM for Excel compatibility with special characters
                $csvContent = "\xEF\xBB\xBF";
                $csvContent .= "Tipo de documento;Número de documento;Primer apellido;Segundo apellido;Primer nombre;Segundo nombre;Fecha de ingreso del trabajador;Fecha de retiro;Tipo de trabajador;Salario base de liquidación;Días trabajados en el período;Período de liquidación;Fondo de Cesantías;Valor de cesantías a consignar;Tipo de liquidación\n";

                foreach ($batch['employees'] as $emp) {
                    $tipoDoc = str_replace(';', '', $emp['tipo_doc'] ?? '');
                    // Force Excel to treat the document as text using formula notation (prevents scientific notation)
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

                $files[$fileName] = $csvContent;
            }

            if (count($files) === 1) {
                $fileName = array_key_first($files);
                $csvContent = $files[$fileName];
                return response($csvContent)
                    ->header('Content-Type', 'text/csv; charset=UTF-8')
                    ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
            }

            $zipFileName = "cesantias_consignacion_{$year}_empresa_{$empresaId}.zip";
            $tempPath = tempnam(sys_get_temp_dir(), 'ces_') . '.zip';

            $zip = new \ZipArchive();
            if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($files as $name => $content) {
                    $zip->addFromString($name, $content);
                }
                $zip->close();
            }

            return response()->download($tempPath, $zipFileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar consignación: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  LEGACY
    // ═══════════════════════════════════════════════════════════════════════════

    public function retiroParcialCesantias(Request $request, BenefitPaymentService $service)
    {
        return $this->retiroEmpresa($request, $service);
    }

    public function liquidarIndividual(Request $request, BenefitPaymentService $service)
    {
        // Legacy — redirect to unified pagarPrestacion
        $request->merge(['payment_mode' => 'direct']);
        return $this->pagarPrestacion($request, $service);
    }

    public function descargarComprobantePrestacion($movementId)
    {
        $empresaId = session('empresa_id');

        $movement = BenefitLedger::with(['usuario', 'empresa'])
            ->where('tenant_id', $empresaId)
            ->findOrFail($movementId);

        return view('provisiones.comprobante', compact('movement'));
    }
}
