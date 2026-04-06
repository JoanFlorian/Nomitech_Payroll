<?php

namespace App\Http\Controllers;

use App\Models\PeriodoLiquidacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\NominaExportacion;
use App\Services\Banking\BankExportService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Services\Payroll\MaternityLicenseRolloverService;

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
            if (Auth::user()->id_rol != 4) {
                $hasAccess = Auth::user()->empresa()->where('empresa.id_empresa', $empresaId)->exists();
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

            return redirect()->route('periodos.index')
                ->with('success', "Ahora está liquidando el periodo: {$fechaInicioStr} - {$fechaFinStr}");

        } catch (\Exception $e) {
            return redirect()->route('periodos.index')->with('error', 'Periodo no válido o no encontrado.');
        }
    }

    /**
     * Sugerencia de fechas para el siguiente periodo.
     */
    public function suggestNext($id)
    {
        try {
            $periodo = PeriodoLiquidacion::findOrFail($id);
            $fechaFinActual = \Carbon\Carbon::parse($periodo->fecha_fin);
            $siguienteInicio = $fechaFinActual->copy()->addDay();
            $siguienteFin = \App\Models\PeriodoLiquidacion::calculateEndDate($siguienteInicio, $periodo->tipo_frecuencia);

            return response()->json([
                'success' => true,
                'inicio_formato' => $siguienteInicio->format('d/m/Y'),
                'fin_formato' => $siguienteFin ? $siguienteFin->format('d/m/Y') : 'N/A',
                'tipo_frecuencia' => ucfirst($periodo->tipo_frecuencia)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Cierra manualmente un periodo de liquidación.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function close(
        Request $request, 
        $id, 
        \App\Services\Payroll\NextPeriodoGeneratorService $generator, 
        \App\Services\Benefits\BenefitAccrualService $accrualService, 
        \App\Services\Benefits\BenefitPaymentService $paymentService,
        \App\Services\Payroll\TransitoriaSalarioDetectionService $vstDetectionService,
        MaternityLicenseRolloverService $maternityRollover
    )
    {
        \Illuminate\Support\Facades\Log::info("Iniciando proceso de cierre para Periodo ID: {$id}");

        try {
            $periodo = PeriodoLiquidacion::where('id_periodo', $id)
                ->where('id_empresa', session('empresa_id'))
                ->firstOrFail();

            \Illuminate\Support\Facades\Log::info("Periodo encontrado. Estado actual: {$periodo->estado}");

            if ($periodo->estado === PeriodoLiquidacion::ESTADO_CERRADO) {
                \Illuminate\Support\Facades\Log::warning("Intento de cerrar un periodo ya cerrado (ID: {$id})");
                throw new \Exception('El periodo ya se encuentra cerrado.');
            }

            // Validar ventana de cierre (Fin + 10 días)
            if (!$periodo->canBeClosed()) {
                \Illuminate\Support\Facades\Log::warning("Validación canBeClosed falló para Periodo ID: {$id}");
                $fFin = \Carbon\Carbon::parse($periodo->fecha_fin);
                $fechaFinStr = $fFin->format('d/m/Y');
                $fechaLimiteStr = $fFin->copy()->addDays(10)->format('d/m/Y');
                throw new \Exception("El cierre de este periodo solo está permitido desde el {$fechaFinStr} hasta el {$fechaLimiteStr}.");
            }

            // Validar que tenga al menos un salario liquidado
            $countSalarios = $periodo->salarios()->count();
            \Illuminate\Support\Facades\Log::info("Total de registros de salario en el periodo: {$countSalarios}");

            if ($countSalarios === 0) {
                throw new \Exception('No se puede cerrar un periodo que no tiene liquidaciones.');
            }

            // Validar que todos los salarios estén en estado liquidado
            $noLiquidados = $periodo->salarios()
                ->where('estado', '!=', \App\Models\Salario::ESTADO_LIQUIDADO)
                ->count();

            if ($noLiquidados > 0) {
                \Illuminate\Support\Facades\Log::info("Autoliquidando {$noLiquidados} registros pendientes para permitir el cierre de pruebas.");
                $periodo->salarios()
                    ->where('estado', '!=', \App\Models\Salario::ESTADO_LIQUIDADO)
                    ->update(['estado' => \App\Models\Salario::ESTADO_LIQUIDADO]);
            }

            \Illuminate\Support\Facades\Log::info("Validaciones superadas o puenteadas. Iniciando transacción de cierre...");

            DB::transaction(function () use ($periodo, $request, $generator, $accrualService, $paymentService, $vstDetectionService, $maternityRollover) {
                // Actualizar todos los salarios del periodo a estado 'pagado'
                $periodo->salarios()->update([
                    'estado' => \App\Models\Salario::ESTADO_PAGADO,
                    'updated_at' => now()
                ]);

                $periodo->close();

                // 🔹 NUEVO: Procesar rollover de licencias de maternidad/paternidad
                // Esto crea novedades continuas en el próximo período si la licencia no ha terminado
                $maternityRollover->processMaternityRollover($periodo);

                $periodosEmpresaIds = \App\Models\PeriodoLiquidacion::where('id_empresa', $periodo->id_empresa)->pluck('id_periodo');

                // ✅ MODIFICADO: Cerrar novedades SOLO si su fecha_fin ya terminó (es <= fecha_fin del período)
                // Las novedades con fecha_fin > período->fecha_fin deben permanecer activas para el próximo período
                // Las novedades sin fecha_fin (fecha_fin IS NULL) nunca se cierran automáticamente
                // EXCEPTO: Las novedades LMAT/LPAT se manejan por rollover y NO se cierran aquí
                \App\Models\Novedad::where(function ($query) use ($periodosEmpresaIds) {
                        $query->whereIn('id_periodo', $periodosEmpresaIds)
                              ->orWhereNull('id_periodo');
                    })
                    ->where('estado', \App\Models\Novedad::ESTADO_ACTIVA)
                    ->whereNotIn('tipo_novedad_codigo', ['LMAT', 'LPAT'])
                    // Solo cerrar si la fecha_fin está definida Y ya terminó en este período
                    ->whereNotNull('fecha_fin')
                    ->where('fecha_fin', '<=', $periodo->fecha_fin ? $periodo->fecha_fin->format('Y-m-d') : null)
                    ->update(['estado' => \App\Models\Novedad::ESTADO_CERRADA, 'updated_at' => now()]);

                // 🔹 NUEVO: Detectar y registrar automáticamente Variación Transitoria de Salario (VST)
                // para empleados que tuvieron horas extras, bonificaciones, comisiones u otros conceptos variables
                $vstDetectionService->detectarYRegistrarVST($periodo);

                // Generate benefit accruals (provisions) for this period
                $accrualService->generateAccrualsForPeriod($periodo);

                // Finalize scheduled benefit payments for this period
                $paymentService->processScheduledPayments($periodo);

                // Auto-generación del siguiente periodo
                if ($request->boolean('generar_siguiente')) {
                    $nuevoPeriodo = $generator->generarSiguiente($periodo);
                    session(['active_period_id' => $nuevoPeriodo->id_periodo]);
                }
            });

            $mensaje = 'El periodo ha sido cerrado correctamente.';
            if ($request->boolean('generar_siguiente')) {
                $mensaje .= ' El siguiente periodo ha sido generado y seleccionado automáticamente.';
            }

            return redirect()->route('nomina.index')->with('success', $mensaje);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error Fatal al cerrar periodo: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error del sistema: ' . $e->getMessage());
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

    /**
     * Actualiza la fecha de cierre automático de un periodo.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAutoCloseDate(Request $request, $id)
    {
        try {
            $request->validate([
                'fecha_cierre_automatico' => 'nullable|date|after_or_equal:today',
            ]);

            $empresaId = session('empresa_id');
            $periodo = PeriodoLiquidacion::where('id_periodo', $id)
                ->where('id_empresa', $empresaId)
                ->firstOrFail();

            if ($periodo->estado === PeriodoLiquidacion::ESTADO_CERRADO) {
                throw new \Exception('No se puede programar el cierre de un periodo ya cerrado.');
            }

            $nuevaFecha = $request->input('fecha_cierre_automatico');
            
            // Validar que la fecha esté en un rango razonable (max 15 días después de fecha_fin)
            if ($nuevaFecha) {
                $fechaCierre = \Carbon\Carbon::parse($nuevaFecha);
                $fechaFin = \Carbon\Carbon::parse($periodo->fecha_fin);
                
                if ($fechaCierre->diffInDays($fechaFin, false) > 15) {
                    throw new \Exception('La fecha de cierre automático no puede ser superior a 15 días después del fin del periodo.');
                }
            }

            $periodo->update([
                'fecha_cierre_automatico' => $nuevaFecha
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fecha de cierre automático actualizada correctamente.',
                'fecha' => $nuevaFecha ? \Carbon\Carbon::parse($nuevaFecha)->format('d/m/Y') : 'No programado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Almacena el primer periodo basado en la elección del usuario (Mes actual o siguiente).
     */
    public function storeFirstPeriod(Request $request)
    {
        $request->validate([
            'choice' => 'required|in:current,next',
        ]);

        try {
            $empresaId = session('empresa_id');
            $empresa = \App\Models\Empresa::findOrFail($empresaId);
            $choice = $request->input('choice');

            $date = now();
            if ($choice === 'next') {
                $date = $date->addMonth();
            }

            $automationService = app(\App\Services\Payroll\PeriodoAutomationService::class);
            
            // Forzamos la creación del periodo para el mes solicitado
            // Usando el primer día del mes de la fecha calculada
            $referenceDate = $date->startOfMonth();
            
            $periodo = $automationService->handleLicenseActivation($empresa, $referenceDate);

            if ($periodo) {
                session()->forget('needs_first_period');
                return response()->json([
                    'success' => true,
                    'message' => 'Primer periodo configurado correctamente.',
                    'redirect' => route('nomina.index')
                ]);
            }

            throw new \Exception('No se pudo crear el periodo.');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
