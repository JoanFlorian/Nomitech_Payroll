<?php

namespace App\Http\Controllers;

use App\Models\BenefitBalance;
use App\Models\BenefitLedger;
use App\Models\Contrato;
use App\Services\Benefits\BenefitPaymentService;
use Illuminate\Http\Request;

class ProvisionesController extends Controller
{
    /**
     * Dashboard: balance summary cards + table of balances per employee.
     */
    public function index()
    {
        $empresaId = session('empresa_id');

        // Get all balances for this tenant, with employee info via contrato → usuario
        $balances = BenefitBalance::where('tenant_id', $empresaId)
            ->with(['usuario'])
            ->orderBy('employee_id')
            ->get();

        // Summary totals
        $totals = [
            'prima' => $balances->sum('prima_balance'),
            'cesantias' => $balances->sum('cesantias_balance'),
            'intereses' => $balances->sum('intereses_balance'),
            'vacaciones' => $balances->sum('vacaciones_balance'),
        ];
        $totals['total'] = $totals['prima'] + $totals['cesantias'] + $totals['intereses'] + $totals['vacaciones'];

        return view('provisiones.index', compact('balances', 'totals'));
    }

    /**
     * Ledger history for a specific employee.
     */
    public function historial(string $doc)
    {
        $empresaId = session('empresa_id');

        $movements = BenefitLedger::forTenant($empresaId)
            ->forEmployee($doc)
            ->orderByDesc('created_at')
            ->paginate(20);

        // Get employee name via contrato
        $contrato = Contrato::where('doc', $doc)
            ->where('id_empresa', $empresaId)
            ->with('usuario')
            ->first();

        $empleadoNombre = $contrato && $contrato->usuario
            ? trim($contrato->usuario->nombres . ' ' . $contrato->usuario->apellidos)
            : $doc;

        return view('provisiones.historial', compact('movements', 'empleadoNombre', 'doc'));
    }

    /**
     * Individual liquidation of a benefit for a single employee.
     */
    public function liquidarIndividual(Request $request, BenefitPaymentService $service)
    {
        $request->validate([
            'employee_id' => 'required|string|max:20',
            'benefit_type' => 'required|in:prima,cesantias,intereses_cesantias,vacaciones',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $empresaId = session('empresa_id');

            $service->liquidateIndividual(
                $request->input('employee_id'),
                $request->input('benefit_type'),
                (float) $request->input('amount'),
                $empresaId
            );

            return back()->with('success', 'Prestación liquidada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al liquidar: ' . $e->getMessage());
        }
    }

    /**
     * Mass liquidation for all active employees of the tenant.
     */
    public function liquidarMasivo(Request $request, BenefitPaymentService $service)
    {
        $request->validate([
            'benefit_type' => 'required|in:prima,cesantias,intereses_cesantias,vacaciones',
        ]);

        try {
            $empresaId = session('empresa_id');
            $benefitType = $request->input('benefit_type');

            $count = $service->liquidateMass($benefitType, $empresaId);

            $label = BenefitLedger::benefitTypeLabel($benefitType);

            return back()->with('success', "Se liquidó {$label} para {$count} empleado(s).");
        } catch (\Exception $e) {
            return back()->with('error', 'Error en liquidación masiva: ' . $e->getMessage());
        }
    }
}
