<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesExportResponses;
use App\Models\Salario;
use App\Models\PeriodoLiquidacion;
use App\Models\BenefitLedger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class NominaElectronicaController extends Controller
{
    use HandlesExportResponses;

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
        return $this->downloadPdfResponse($pdf, $nombreArchivo);
    }

    /**
     * Resumen previo para el modal de exportación.
     */
    public function exportPreview($id)
    {
        try {
            $periodo = PeriodoLiquidacion::where('id_periodo', $id)
                ->where('id_empresa', session('empresa_id'))
                ->firstOrFail();

            $salarios = $periodo->salarios()
                ->where('estado', \App\Models\Salario::ESTADO_PAGADO)
                ->with('contrato')
                ->get();

            $total = $salarios->sum('salario_neto');

            return response()->json([
                'success' => true,
                'periodo' => \Carbon\Carbon::parse($periodo->fecha_inicio)->format('M Y'),
                'empleados' => $salarios->count(),
                'total' => number_format($total, 2, ',', '.')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    /**
     * Endpoint para generar exportación bancaria.
     */
    public function exportar(Request $request, $id, \App\Services\Banking\BankExportService $service)
    {
        try {
            $tipoExportacion = $request->input('tipo_exportacion', 'bank');
            $result = $service->generarArchivo($id, $request->input('formato', 'CSV'), $tipoExportacion);

            return response()->json([
                'success' => true,
                'message' => 'Archivo generado exitosamente.',
                'archivo_url' => $result['archivo_url'],
                'download_url' => route('nomina-electronica.exportar.descargar', $result['exportacion']->id),
                'total_empleados' => $result['total_empleados'],
                'total_pagado' => $result['total_pagado']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Descarga un archivo de exportación previamente generado.
     */
    public function downloadExport($id)
    {
        try {
            $export = \App\Models\NominaExportacion::where('id', $id)
                ->where('id_empresa', session('empresa_id'))
                ->firstOrFail();

            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($export->archivo_path);

            // Limpiar el buffer de salida para evitar corrupción del archivo binario
            if (ob_get_level()) {
                ob_end_clean();
            }

            return response()->download($fullPath);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al descargar: ' . $e->getMessage());
        }
    }

    /**
     * Descarga el archivo Excel de una exportación.
     */
    public function downloadExportExcel($id)
    {
        try {
            $export = \App\Models\NominaExportacion::where('id', $id)
                ->where('id_empresa', session('empresa_id'))
                ->firstOrFail();

            if (!$export->archivo_excel_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($export->archivo_excel_path)) {
                throw new \Exception('El archivo Excel no existe.');
            }

            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($export->archivo_excel_path);
            
            // Limpiar el buffer de salida para evitar corrupción del archivo binario
            if (ob_get_level()) {
                ob_end_clean();
            }

            return response()->download($fullPath);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al descargar Excel: ' . $e->getMessage());
        }
    }
    /**
     * Genera archivo PAB (Pagos Automatizados Bancolombia) para un periodo.
     */
    public function exportarPab(Request $request, $id, \App\Services\Banking\BankExportService $service)
    {
        try {
            $request->validate([
                'cuenta_debito'      => 'required|string|min:5|max:20',
                'tipo_cuenta_debito' => 'required|in:S,D',
            ], [
                'cuenta_debito.required'      => 'Debe ingresar la cuenta de débito.',
                'tipo_cuenta_debito.required'  => 'Debe seleccionar el tipo de cuenta de débito.',
            ]);

            $result = $service->generarArchivoPab(
                (int) $id,
                $request->input('cuenta_debito'),
                $request->input('tipo_cuenta_debito'),
                $request->input('secuencia', 'A1')
            );

            return response()->json([
                'success'         => true,
                'message'         => 'Archivo PAB y Resumen Excel generados exitosamente.',
                'download_url'    => route('nomina-electronica.exportar.descargar', $result['exportacion']->id),
                'download_excel_url' => route('nomina-electronica.exportar.descargar-excel', $result['exportacion']->id),
                'total_empleados' => $result['total_empleados'],
                'total_pagado'    => $result['total_pagado'],
            ]);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => collect($ve->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
