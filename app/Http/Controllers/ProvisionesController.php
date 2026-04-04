<?php

namespace App\Http\Controllers;

use App\Models\BenefitBalance;
use App\Models\BenefitLedger;
use App\Models\CesantiasWithdrawal;
use App\Models\Contrato;
use App\Models\PeriodoLiquidacion;
use App\Models\ProvisionAutomation;
use App\Services\Benefits\BenefitPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProvisionesController extends Controller
{
    /**
     * Dashboard: balance summary cards + table of balances per employee.
     */
    public function index(Request $request)
    {
        $empresaId = session('empresa_id');
        $search = trim($request->input('search'));

        $query = BenefitBalance::where('tenant_id', $empresaId)
            ->where(function ($query) {
                $query->whereHas('usuario.contratos', function ($q) {
                    $q->whereIn('estado', [
                        Contrato::ESTADO_ACTIVO,
                        Contrato::ESTADO_POR_VENCER,
                        Contrato::ESTADO_PROGRAMADO,
                        Contrato::ESTADO_VENCIDO,
                    ]);
                })
                    ->orWhere('prima_balance', '>', 0)
                    ->orWhere('cesantias_balance', '>', 0)
                    ->orWhere('intereses_balance', '>', 0)
                    ->orWhere('vacaciones_balance', '>', 0);
            });

        // ── FILTRADO ──
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                    ->orWhereHas('usuario', function ($u) use ($search) {
                        $u->where(DB::raw("CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)"), 'like', "%{$search}%");
                    });
            });
        }

        // ── TOTALES GLOBALES (Sobre la consulta completa de la empresa) ──
        $totals = [
            'prima' => (float) $query->sum('prima_balance'),
            'cesantias' => (float) $query->sum('cesantias_balance'),
            'intereses' => (float) $query->sum('intereses_balance'),
            'vacaciones' => (float) $query->sum('vacaciones_balance'),
        ];
        $totals['total_money'] = $totals['prima'] + $totals['cesantias'] + $totals['intereses'];

        // ── PAGINACIÓN ──
        $balances = $query->with(['usuario'])
            ->orderBy('employee_id')
            ->paginate(4)
            ->appends(['search' => $search]);

        // Active payroll period (for payroll integration option)
        $activePeriod = PeriodoLiquidacion::getActivePeriod();

        // Legal date warnings (advisory only)
        $warnings = collect([
            BenefitLedger::TYPE_PRIMA => $totals['prima'] > 0,
            BenefitLedger::TYPE_INTERESES_CESANTIAS => $totals['intereses'] > 0,
            BenefitLedger::TYPE_CESANTIAS => $totals['cesantias'] > 0,
        ])->map(fn($hasPending, $type) => BenefitPaymentService::getLegalDateWarning($type, $hasPending))
            ->filter()
            ->values();

        // Automation settings
        $automations = ProvisionAutomation::where('id_empresa', $empresaId)->get()->keyBy('benefit_type');

        return view('provisiones.index', compact('balances', 'totals', 'activePeriod', 'warnings', 'automations'));
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
            $period = PeriodoLiquidacion::getActivePeriod();

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
            $period = PeriodoLiquidacion::getActivePeriod();

            $periodId = $period ? $period->id_periodo : null;

            if ($paymentMode === 'payroll' && !$periodId) {
                return back()->with('error', 'No hay un periodo de nómina abierto para integrar la liquidación masiva.');
            }

            $result = $service->liquidateMass($benefitType, $empresaId, $paymentMode, $periodId);

            // AUTO-CONSIGNMENT GENERATION FOR CESANTÍAS (Annual Process)
            if ($benefitType === BenefitLedger::TYPE_CESANTIAS) {
                $year = date('m') <= 2 ? date('Y') - 1 : date('Y');
                $batchesData = $service->generarConsignacionAnual($empresaId, $year);
                if (!empty($batchesData)) {
                    $zipPath = $service->generateConsignmentZip($batchesData, $year);
                    if ($zipPath) {
                        // Move to a persistent storage location for session download
                        $persistentPath = 'temp_consignaciones/' . basename($zipPath);
                        Storage::disk('local')->put($persistentPath, file_get_contents($zipPath));
                        session(['recent_consignacion_path' => $persistentPath]);
                    }
                }
            }

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
            $companyId = session('empresa_id');
            $year = (int) $request->input('year');

            $batchesData = $service->generarConsignacionAnual($companyId, $year);
            if (empty($batchesData)) {
                return back()->with('info', 'No hay saldos de cesantías para consignar en el año seleccionado.');
            }

            $tempPath = $service->generateConsignmentZip($batchesData, $year);
            if (!$tempPath) {
                return back()->with('error', 'No se pudo generar el archivo de consignación.');
            }

            $fileName = basename($tempPath);
            return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar consignación: ' . $e->getMessage());
        }
    }

    /**
     * Serves the recently generated consignment from session.
     */
    public function descargarConsignacionReciente()
    {
        $path = session('recent_consignacion_path');
        if (!$path || !Storage::disk('local')->exists($path)) {
            return back()->with('error', 'El archivo ya no está disponible o ha expirado.');
        }

        return Storage::disk('local')->download($path)->deleteFileAfterSend(true);
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

    /**
     * Save/Update automation settings for the company.
     */
    public function updateAutomation(Request $request)
    {
        $empresaId = session('empresa_id');
        
        $request->validate([
            'automations' => 'required|array',
            'automations.*.benefit_type' => 'required|string',
            'automations.*.is_active' => 'sometimes|boolean',
            'automations.*.payment_mode' => 'required|string|in:direct,payroll',
            'automations.*.execution_day' => 'required|integer|min:1|max:31',
            'automations.*.execution_month' => 'required|integer|min:1|max:12',
        ]);

        $validMonths = [
            'prima_1' => [6],
            'prima_2' => [12],
            'cesantias' => [1, 2],
            'intereses_cesantias' => [1],
        ];

        $demoUnlock = $request->input('demo_unlock');

        foreach ($request->input('automations') as $benefitType => $settings) {
            if (!$demoUnlock) {
                // Validate month according to benefit type
                if (isset($validMonths[$benefitType]) && !in_array($settings['execution_month'], $validMonths[$benefitType])) {
                    return redirect()->back()->with('error', "El beneficio {$benefitType} solo puede liquidarse en los meses permitidos.");
                }

                // Special restriction for Cesantias in February (max 14th)
                if ($benefitType === 'cesantias' && $settings['execution_month'] == 2 && $settings['execution_day'] > 14) {
                    return redirect()->back()->with('error', "Las cesantías solo pueden programarse hasta el 14 de febrero.");
                }
            }

            ProvisionAutomation::updateOrCreate(
                [
                    'id_empresa' => $empresaId,
                    'benefit_type' => $benefitType
                ],
                [
                    'is_active' => isset($settings['is_active']) ? (bool)$settings['is_active'] : false,
                    'payment_mode' => $settings['payment_mode'],
                    'execution_day' => $settings['execution_day'],
                    'execution_month' => $settings['execution_month'],
                ]
            );
        }

        return redirect()->back()->with('success', 'Configuración de automatización guardada correctamente.');
    }

    public function resetAutomation(Request $request)
    {
        $empresaId = session('empresa_id');
        $benefitType = $request->input('benefit_type');

        \App\Models\ProvisionAutomation::where('id_empresa', $empresaId)
            ->where('benefit_type', $benefitType)
            ->update(['last_execution_year' => null]);

        return redirect()->back()->with('success', 'Automatización reiniciada. Lista para nueva prueba.');
    }
}
