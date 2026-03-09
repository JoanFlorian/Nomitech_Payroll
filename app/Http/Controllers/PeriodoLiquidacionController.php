<?php

namespace App\Http\Controllers;

use App\Models\PeriodoLiquidacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\NominaExportacion;
use App\Services\Banking\BankExportService;
use Illuminate\Support\Facades\Storage;

class PeriodoLiquidacionController extends Controller
{
    /**
     * Lista los periodos de liquidación de la empresa.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $empresaId = session('empresa_id');
        $estado = $request->input('estado');
        $mes = $request->input('mes');
        $anio = $request->input('anio');

        $query = PeriodoLiquidacion::where('id_empresa', $empresaId);

        // Filtro por Estado
        if ($estado && $estado !== 'todos') {
            $query->where('estado', $estado);
        }

        // Filtro por Mes
        if ($mes) {
            $query->whereMonth('fecha_inicio', $mes);
        }

        // Filtro por Año
        if ($anio) {
            $query->whereYear('fecha_inicio', $anio);
        }

        // --- CÁLCULO DE RANGOS PERMITIDOS PARA EL FRONTEND ---
        $empresa = \App\Models\Empresa::find($empresaId);
        $licencia = $empresa->licencia;
        $minDate = null;
        $maxDate = null;
        $isRestrictedByLicense = false;
        $licenceMessage = '';

        if ($licencia && $licencia->fecha_inicio) {
            $fechaLicStart = \Carbon\Carbon::parse($licencia->fecha_inicio);
            $minDate = $fechaLicStart->copy()->startOfMonth()->toDateString();

            // Regla del día 20
            if ($fechaLicStart->day > 20) {
                // Mes actual + Mes siguiente
                $maxDate = $fechaLicStart->copy()->addMonth()->endOfMonth()->toDateString();
                $isRestrictedByLicense = false;
                $licenceMessage = 'Su licencia (post-20) le permite crear periodos en el mes actual y el siguiente.';
            } else {
                // Solo mes actual
                $maxDate = $fechaLicStart->copy()->endOfMonth()->toDateString();
                $isRestrictedByLicense = true;
                $licenceMessage = 'Su licencia (pre-20) limita la creación de periodos al mes actual de compra/renovación.';
            }
        }
        // -----------------------------------------------------

        $periodos = $query->orderByDesc('fecha_inicio')
            ->paginate(10)
            ->withQueryString();

        return view('periodos.index', compact('periodos', 'estado', 'mes', 'anio', 'minDate', 'maxDate', 'isRestrictedByLicense', 'licenceMessage'));
    }

    /**
     * Almacena un nuevo periodo de liquidación.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_empresa' => 'required|exists:empresa,id_empresa',
            'tipo_frecuencia' => 'required|string|in:semanal,decenal,catorcenal,quincenal,mensual,otro',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required_if:tipo_frecuencia,otro|nullable|date|after_or_equal:fecha_inicio',
        ]);

        try {
            $empresaId = $request->input('id_empresa');
            $frecuencia = $request->input('tipo_frecuencia');
            $fechaInicio = \Carbon\Carbon::parse($request->input('fecha_inicio'));

            // Seguridad: Validar que el usuario tenga acceso a esa empresa (si no es SuperAdmin)
            if (auth()->user()->id_rol != 4) {
                $hasAccess = auth()->user()->empresa()->where('empresa.id_empresa', $empresaId)->exists();
                if (!$hasAccess) {
                    throw new \Exception('No tiene permisos para crear periodos en esta empresa.');
                }
            }

            $fechaFin = ($frecuencia === PeriodoLiquidacion::FRECUENCIA_OTRO)
                ? \Carbon\Carbon::parse($request->input('fecha_fin'))
                : PeriodoLiquidacion::calculateEndDate($fechaInicio, $frecuencia);

            // --- REFUERZO DE TOPE DE MES EN BACKEND (Licencia pre-20) ---
            $empresa = \App\Models\Empresa::find($empresaId);
            $licencia = $empresa->licencia;

            if ($licencia && $licencia->fecha_inicio) {
                $fechaLicStart = \Carbon\Carbon::parse($licencia->fecha_inicio);
                if ($fechaLicStart->day <= 20) {
                    $ultimoDiaMes = $fechaInicio->copy()->endOfMonth();
                    if ($fechaFin->greaterThan($ultimoDiaMes)) {
                        $fechaFin = $ultimoDiaMes;
                    }
                }
            }
            // ------------------------------------------------------------

            // Validaciones para "Otro" si la frecuencia es "otro"
            if ($frecuencia === PeriodoLiquidacion::FRECUENCIA_OTRO) {
                // 1. Duración máxima 2 meses
                if ($fechaInicio->diffInMonths($fechaFin) >= 2) {
                    throw new \Exception('La duración del periodo personalizado no puede exceder los 2 meses.');
                }

                // 2. Dentro del mes actual o siguiente
                $limiteMax = now()->startOfMonth()->addMonths(2)->endOfMonth();
                $limiteMin = now()->startOfMonth();

                if ($fechaInicio->lt($limiteMin) || $fechaFin->gt($limiteMax)) {
                    // Nota: El usuario pidió "dentro de mes actual o siguiente", permitimos un rango razonable
                    // pero mantenemos la flexibilidad si es necesario.
                }
            }

            // Evitar duplicados exactos
            $existe = PeriodoLiquidacion::where('id_empresa', $empresaId)
                ->where('fecha_inicio', $fechaInicio->toDateString())
                ->exists();

            if ($existe) {
                throw new \Exception('Ya existe un periodo con esta fecha de inicio.');
            }

            // --- RESTRICCIÓN POR FECHA DE LICENCIA (Regla del Día 20) ---
            $empresa = \App\Models\Empresa::find($empresaId);
            $licencia = $empresa->licencia;

            if ($licencia && $licencia->fecha_inicio) {
                $fechaLicStart = \Carbon\Carbon::parse($licencia->fecha_inicio);
                $diaLicencia = $fechaLicStart->day;
                $mesLicencia = $fechaLicStart->month;
                $anioLicencia = $fechaLicStart->year;

                $mesSolicitado = $fechaInicio->month;
                $anioSolicitado = $fechaInicio->year;

                // Definir meses permitidos
                // Siempre se permite el mes de compra/renovación
                $permitidoActual = ($mesSolicitado == $mesLicencia && $anioSolicitado == $anioLicencia);

                // Si es mayor a 20, se permite también el mes siguiente
                $permitidoSiguiente = false;
                if ($diaLicencia > 20) {
                    $siguienteMes = $fechaLicStart->copy()->addMonth();
                    $permitidoSiguiente = ($mesSolicitado == $siguienteMes->month && $anioSolicitado == $siguienteMes->year);
                }

                if (!$permitidoActual && !$permitidoSiguiente) {
                    $errorMsg = ($diaLicencia > 20)
                        ? 'Debido a que su licencia fue adquirida después del día 20, solo puede crear periodos para el mes actual o el siguiente.'
                        : 'Su licencia fue adquirida antes del día 20, por lo tanto solo puede crear periodos dentro del mes de la compra/renovación.';
                    throw new \Exception($errorMsg);
                }
            }
            // -----------------------------------------------------------

            // Evitar duplicados por rango de fechas
            $existeRango = PeriodoLiquidacion::where('id_empresa', $empresaId)
                ->where('fecha_inicio', $fechaInicio->toDateString())
                ->where('fecha_fin', $fechaFin->toDateString())
                ->exists();

            if ($existeRango) {
                throw new \Exception('Ya existe un periodo con el mismo rango de fechas para esta empresa.');
            }

            PeriodoLiquidacion::create([
                'id_empresa' => $empresaId,
                'tipo_frecuencia' => $frecuencia,
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_fin' => $fechaFin->toDateString(),
                'estado' => PeriodoLiquidacion::ESTADO_PENDIENTE,
            ]);

            return back()->with('success', 'Periodo creado exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear periodo: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Selecciona un periodo como activo para la liquidación de nómina.
     */
    public function select($id)
    {
        try {
            $empresaId = session('empresa_id');
            $periodo = PeriodoLiquidacion::where('id_periodo', $id)
                ->where('id_empresa', $empresaId)
                ->firstOrFail();

            if ($periodo->estado === PeriodoLiquidacion::ESTADO_CERRADO) {
                return redirect()->route('periodos.index')->with('error', 'No se puede seleccionar un periodo cerrado para liquidar.');
            }

            // Guardar en sesión
            session(['active_period_id' => $periodo->id_periodo]);

            $fechaInicioStr = \Carbon\Carbon::parse($periodo->getRawOriginal('fecha_inicio'))->format('d/m/Y');
            $fechaFinStr = \Carbon\Carbon::parse($periodo->getRawOriginal('fecha_fin'))->format('d/m/Y');

            return redirect()->route('nomina.index')->with('success', "Periodo seleccionado: {$fechaInicioStr} - {$fechaFinStr}");

        } catch (\Exception $e) {
            return redirect()->route('periodos.index')->with('error', 'Periodo no válido o no encontrado.');
        }
    }

    /**
     * Cierra manualmente un periodo de liquidación.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function close(Request $request, $id, \App\Services\Payroll\NextPeriodoGeneratorService $generator, \App\Services\Benefits\BenefitAccrualService $accrualService)
    {
        try {
            $periodo = PeriodoLiquidacion::where('id_periodo', $id)
                ->where('id_empresa', session('empresa_id'))
                ->firstOrFail();

            if ($periodo->estado === PeriodoLiquidacion::ESTADO_CERRADO) {
                throw new \Exception('El periodo ya se encuentra cerrado.');
            }

            // Validar ventana de cierre (Fin + 10 días)
            if (!$periodo->canBeClosed()) {
                $fFin = \Carbon\Carbon::parse($periodo->fecha_fin);
                $fechaFinStr = $fFin->format('d/m/Y');
                $fechaLimiteStr = $fFin->copy()->addDays(10)->format('d/m/Y');
                throw new \Exception("El cierre de este periodo solo está permitido desde el {$fechaFinStr} hasta el {$fechaLimiteStr}.");
            }

            // Validar que tenga al menos un salario liquidado
            $tieneSalarios = $periodo->salarios()->exists();
            if (!$tieneSalarios) {
                throw new \Exception('No se puede cerrar un periodo que no tiene liquidaciones.');
            }

            // Validar que todos los salarios estén en estado liquidado
            $noLiquidados = $periodo->salarios()
                ->where('estado', '!=', \App\Models\Salario::ESTADO_LIQUIDADO)
                ->exists();
            if ($noLiquidados) {
                throw new \Exception('Existen liquidaciones pendientes. Debe liquidarlas todas antes de cerrar el periodo.');
            }

            DB::transaction(function () use ($periodo, $request, $generator, $accrualService) {
                // Actualizar todos los salarios del periodo a estado 'pagado'
                $periodo->salarios()->update([
                    'estado' => \App\Models\Salario::ESTADO_PAGADO,
                    'updated_at' => now()
                ]);

                $periodo->close();

                // Generate benefit accruals (provisions) for this period
                $accrualService->generateAccrualsForPeriod($periodo);

                // Auto-generación del siguiente periodo
                if ($request->boolean('generar_siguiente')) {
                    $nuevoPeriodo = $generator->generarSiguiente($periodo);
                    // Actualizar el periodo activo en sesión al nuevo si se creó
                    session(['active_period_id' => $nuevoPeriodo->id_periodo]);
                }
            });

            $mensaje = 'El periodo ha sido cerrado correctamente.';
            if ($request->boolean('generar_siguiente')) {
                $mensaje .= ' El siguiente periodo ha sido generado y seleccionado automáticamente.';
            }

            return redirect()->route('periodos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Resumen previo para el modal de exportación.
     */
    public function getExportPreview($id)
    {
        try {
            $periodo = PeriodoLiquidacion::where('id_periodo', $id)
                ->where('id_empresa', session('empresa_id'))
                ->firstOrFail();

            $salarios = $periodo->salarios()
                ->where('estado', \App\Models\Salario::ESTADO_LIQUIDADO)
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
    public function exportar(Request $request, $id, BankExportService $service)
    {
        try {
            $result = $service->generarArchivo($id, $request->input('formato', 'CSV'));

            return response()->json([
                'success' => true,
                'message' => 'Archivo generado exitosamente.',
                'archivo_url' => $result['archivo_url'],
                'download_url' => route('periodos.exportar.descargar', $result['exportacion']->id),
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
            $export = NominaExportacion::where('id', $id)
                ->where('id_empresa', session('empresa_id'))
                ->firstOrFail();

            if (!Storage::disk('public')->exists($export->archivo_path)) {
                throw new \Exception('El archivo físico no existe.');
            }

            $fullPath = Storage::disk('public')->path($export->archivo_path);
            return response()->download($fullPath);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al descargar: ' . $e->getMessage());
        }
    }
}
