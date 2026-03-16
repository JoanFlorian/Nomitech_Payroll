<?php

namespace App\Http\Controllers;

use App\Models\Salario;
use App\Models\PeriodoLiquidacion;
use App\Models\BenefitLedger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class NominaElectronicaController extends Controller
{
    /**
     * Muestra la vista principal de Nómina Electrónica.
     */
    public function index(Request $request)
    {
        $empresaId = session('empresa_id');

        // Obtener años distintos para el filtro
        $anos = PeriodoLiquidacion::where('id_empresa', $empresaId)
            ->selectRaw('DISTINCT YEAR(fecha_inicio) as ano')
            ->orderByDesc('ano')
            ->pluck('ano');

        $query = PeriodoLiquidacion::where('id_empresa', $empresaId)
            ->orderByDesc('fecha_inicio');

        // Aplicar filtros si existen
        if ($request->filled('year')) {
            $query->whereYear('fecha_inicio', $request->year);
        }

        if ($request->filled('month')) {
            $query->whereMonth('fecha_inicio', $request->month);
        }

        $periodos = $query->get();

        return view('nomina-electronica.index', compact('periodos', 'anos'));
    }

    /**
     * Obtiene los detalles de salarios para un periodo específico (AJAX).
     */
    public function getDetalles($idPeriodo)
    {
        $empresaId = session('empresa_id');

        // Validar que el periodo pertenezca a la empresa
        $periodo = PeriodoLiquidacion::where('id_empresa', $empresaId)
            ->where('id_periodo', $idPeriodo)
            ->firstOrFail();

        // Obtener los salarios liquidados en este periodo
        $salarios = Salario::with(['contrato.usuario'])
            ->where('id_periodo', $idPeriodo)
            ->get()
            ->map(function ($salario) {
                return [
                    'id_salario' => $salario->id_salario,
                    'nombre_empleado' => $salario->contrato->usuario->nombre_completo ?? 'N/A',
                    'documento' => $salario->contrato->usuario->doc ?? 'N/A',
                    'estado_nomina_electronica' => 'Aceptado', // Placeholder por ahora
                    'fecha_reporte' => now()->format('d/m/Y'), // Placeholder por ahora
                ];
            });

        return response()->json($salarios);
    }

    /**
     * Genera y descarga el PDF del volante de nómina.
     */
    public function descargarPdf($idSalario)
    {
        $empresaId = session('empresa_id');

        // Obtener el salario con todas las relaciones necesarias
        $salario = Salario::with(['contrato.usuario', 'periodo', 'novedades.tipoNovedad', 'contrato.empresa.ciudad'])
            ->whereHas('contrato', function ($query) use ($empresaId) {
                $query->where('id_empresa', $empresaId);
            })
            ->where('id_salario', $idSalario)
            ->firstOrFail();

        $empresa = $salario->contrato->empresa;
        $empleado = $salario->contrato->usuario;
        $periodo = $salario->periodo;

        // 1. Recopilar Devengos
        $devengos = collect();
        
        // Salario Base (proporcional a días trabajados)
        $salarioBaseOriginal = (float) ($salario->contrato->salario_base ?? 0);
        $diasTrabajados = (int) ($salario->dias_a_trabajar ?? 30);
        $pagoDias = ($salarioBaseOriginal / 30) * $diasTrabajados;
        
        if ($pagoDias > 0) {
            $devengos->push(['concepto' => 'Sueldo Básico (' . $diasTrabajados . ' días)', 'valor' => $pagoDias]);
        }

        if ($salario->auxilio_transporte > 0) {
            $devengos->push(['concepto' => 'Auxilio de Transporte', 'valor' => $salario->auxilio_transporte]);
        }

        if ($salario->valor_horas_extras_recargos > 0) {
            $devengos->push(['concepto' => 'Horas Extras y Recargos', 'valor' => $salario->valor_horas_extras_recargos]);
        }

        if ($salario->bonificaciones > 0) {
            $devengos->push(['concepto' => 'Bonificaciones', 'valor' => $salario->bonificaciones]);
        }

        if ($salario->comisiones > 0) {
            $devengos->push(['concepto' => 'Comisiones', 'valor' => $salario->comisiones]);
        }

        if ($salario->otros_devengos > 0) {
            $devengos->push(['concepto' => 'Otros Devengos', 'valor' => $salario->otros_devengos]);
        }

        // 2. Recopilar Deducciones
        $deducciones = collect();

        if ($salario->eps > 0) {
            $deducciones->push(['concepto' => 'Salud (EPS)', 'valor' => $salario->eps]);
        }

        if ($salario->afp > 0) {
            $deducciones->push(['concepto' => 'Pensión (AFP)', 'valor' => $salario->afp]);
        }

        if ($salario->aporte_fp > 0) {
            $deducciones->push(['concepto' => 'Fondo de Solidaridad Pensional', 'valor' => $salario->aporte_fp]);
        }

        if ($salario->retencion_fuente > 0) {
            $deducciones->push(['concepto' => 'Retención en la Fuente', 'valor' => $salario->retencion_fuente]);
        }

        if ($salario->embargo_fiscal > 0) {
            $deducciones->push(['concepto' => 'Embargos', 'valor' => $salario->embargo_fiscal]);
        }

        if ($salario->pension_voluntaria > 0) {
            $deducciones->push(['concepto' => 'Pensión Voluntaria / AFC', 'valor' => $salario->pension_voluntaria]);
        }

        // 3. Agregar Novedades
        foreach ($salario->novedades as $novedad) {
            if ($novedad->pago > 0) {
                $devengos->push([
                    'concepto' => $novedad->tipoNovedad->nombre ?? $novedad->tipo_novedad_nombre ?? 'Novedad',
                    'valor' => $novedad->pago
                ]);
            } elseif ($novedad->pago < 0) {
                $deducciones->push([
                    'concepto' => $novedad->tipoNovedad->nombre ?? $novedad->tipo_novedad_nombre ?? 'Novedad (Deducción)',
                    'valor' => abs($novedad->pago)
                ]);
            }
        }

        // 4. Agregar Prestaciones Sociales Integradas (BenefitLedger)
        $prestacionesIntegradas = BenefitLedger::where('employee_id', $empleado->doc)
            ->where('payroll_period_id', $periodo->id_periodo)
            ->where('payment_method', 'payroll')
            ->where('movement_type', 'payment')
            ->get();

        foreach ($prestacionesIntegradas as $prestacion) {
            if ($prestacion->amount > 0) {
                $devengos->push([
                    'concepto' => BenefitLedger::benefitTypeLabel($prestacion->benefit_type),
                    'valor' => (float) $prestacion->amount
                ]);
            }
        }

        $totalDevengos = $devengos->sum('valor');
        $totalDeducciones = $deducciones->sum('valor');
        $netoPagar = $totalDevengos - $totalDeducciones;

        $pdf = Pdf::loadView('nomina-electronica.volante', [
            'empresa' => $empresa,
            'empleado' => $empleado,
            'periodo' => $periodo,
            'salario' => $salario,
            'devengos' => $devengos,
            'deducciones' => $deducciones,
            'totalDevengos' => $totalDevengos,
            'totalDeducciones' => $totalDeducciones,
            'netoPagar' => $netoPagar,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
        ]);

        $nombreArchivo = 'volante_' . $empleado->doc . '_' . $periodo->fecha_inicio->format('MY') . '.pdf';
        return $pdf->download($nombreArchivo);
    }
}
