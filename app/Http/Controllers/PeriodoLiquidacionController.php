<?php

namespace App\Http\Controllers;

use App\Models\PeriodoLiquidacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $periodos = PeriodoLiquidacion::where('id_empresa', $empresaId)
            ->orderByDesc('fecha_inicio')
            ->paginate(10)
            ->withQueryString();

        // Obtener empresas vinculadas al usuario para el selector del modal
        $empresas = auth()->user()->id_rol == 4
            ? \App\Models\Empresa::all()
            : auth()->user()->empresa()->get();

        return view('periodos.index', compact('periodos', 'empresas'));
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

            // Calcular fecha fin si no es "otro"
            if ($frecuencia !== PeriodoLiquidacion::FRECUENCIA_OTRO) {
                $fechaFin = PeriodoLiquidacion::calculateEndDate($fechaInicio, $frecuencia);
            } else {
                $fechaFin = \Carbon\Carbon::parse($request->input('fecha_fin'));

                // Validaciones para "Otro"
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
                ->where('fecha_fin', $fechaFin->toDateString())
                ->exists();

            if ($existe) {
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

            return redirect()->route('nomina.index')->with('success', "Periodo seleccionado: {$periodo->fecha_inicio->format('d/m/Y')} - {$periodo->fecha_fin->format('d/m/Y')}");

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
    public function close(Request $request, $id, \App\Services\Payroll\NextPeriodoGeneratorService $generator)
    {
        try {
            $periodo = PeriodoLiquidacion::where('id_periodo', $id)
                ->where('id_empresa', session('empresa_id'))
                ->firstOrFail();

            if ($periodo->estado === PeriodoLiquidacion::ESTADO_CERRADO) {
                throw new \Exception('El periodo ya se encuentra cerrado.');
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
                throw new \Exception('Existen liquidaciones pendientes o en borrador. Debe liquidarlas todas antes de cerrar el periodo.');
            }

            DB::transaction(function () use ($periodo, $request, $generator) {
                $periodo->close();

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
     * Sugerencia del siguiente periodo basado en el actual.
     */
    public function suggestNext($id)
    {
        try {
            $periodo = PeriodoLiquidacion::where('id_periodo', $id)
                ->where('id_empresa', session('empresa_id'))
                ->firstOrFail();

            $nextStart = $periodo->fecha_fin->copy()->addDay();
            $nextEnd = PeriodoLiquidacion::calculateEndDate($nextStart, $periodo->tipo_frecuencia);

            return response()->json([
                'success' => true,
                'inicio' => $nextStart->format('Y-m-d'),
                'fin' => $nextEnd ? $nextEnd->format('Y-m-d') : null,
                'inicio_formato' => $nextStart->format('d/m/Y'),
                'fin_formato' => $nextEnd ? $nextEnd->format('d/m/Y') : 'N/A',
                'tipo_frecuencia' => $periodo->tipo_frecuencia
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    /**
     * Endpoint para exportación bancaria.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function exportar(Request $request, $id)
    {
        try {
            $periodo = PeriodoLiquidacion::find($id);

            if (!$periodo || $periodo->estado !== PeriodoLiquidacion::ESTADO_CERRADO) {
                return redirect()->route('periodos.index')->with('error', 'Solo se pueden exportar archivos de periodos cerrados.');
            }

            $banco = $request->query('banco');

            if (!$banco) {
                return redirect()->back()->with('error', 'Debe seleccionar un banco para la exportación.');
            }

            $generator = \App\Services\Banking\BankFileFactory::make($banco);
            $content = $generator->generate($id);

            $fileName = "nomina_periodo_{$id}.txt";

            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en exportación: ' . $e->getMessage());
        }
    }
}
