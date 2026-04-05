<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\PeriodoLiquidacion;
use App\Services\PilaFileGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PilaController extends Controller
{
    private PilaFileGeneratorService $pilaFileGeneratorService;

    public function __construct(
        PilaFileGeneratorService $pilaFileGeneratorService
    )
    {
        $this->pilaFileGeneratorService = $pilaFileGeneratorService;
    }

    public function index(Request $request): View
    {
        $empresaActual = null;
        $selectedEmpresaId = (int) (session('empresa_id') ?: 0);

        if ($selectedEmpresaId > 0) {
            $empresaActual = Empresa::query()
                ->select(['id_empresa', 'razon_social', 'nit'])
                ->find($selectedEmpresaId);
        }

        if (!$empresaActual) {
            $empresaActual = Empresa::query()
                ->select(['id_empresa', 'razon_social', 'nit'])
                ->orderBy('razon_social')
                ->first();

            $selectedEmpresaId = (int) ($empresaActual->id_empresa ?? 0);
        }

        $periodos = PeriodoLiquidacion::query()
            ->when($selectedEmpresaId > 0, fn($q) => $q->where('id_empresa', $selectedEmpresaId))
            ->whereIn('estado', [PeriodoLiquidacion::ESTADO_PENDIENTE, PeriodoLiquidacion::ESTADO_ABIERTO])
            ->orderByDesc('fecha_inicio')
            ->limit(36)
            ->get(['id_periodo', 'id_empresa', 'fecha_inicio', 'fecha_fin', 'estado']);

        // Agregar períodos CERRADOS que tengan cambios (hash null o salarios más recientes)
        if ($selectedEmpresaId > 0 && Schema::hasTable('planilla_pila')) {
            $periodoCerradosConCambios = DB::table('planilla_pila as pp')
                ->join('periodo_liquidacion as pl', 'pl.id_periodo', '=', 'pp.id_periodo')
                ->where('pp.id_empresa', $selectedEmpresaId)
                ->where('pl.estado', PeriodoLiquidacion::ESTADO_CERRADO)
                ->where(function($q) {
                    // Hash null (invalidado por cambio de entidades) O salarios más recientes que la planilla
                    $q->whereNull('pp.datos_hash')
                      ->orWhereRaw('EXISTS (
                        SELECT 1 FROM salario s 
                        WHERE s.id_periodo = pp.id_periodo 
                        AND s.updated_at > pp.updated_at
                      )');
                })
                ->orderByDesc('pl.fecha_inicio')
                ->get(['pl.id_periodo', 'pl.id_empresa', 'pl.fecha_inicio', 'pl.fecha_fin', 'pl.estado']);

            $periodos = $periodos->merge($periodoCerradosConCambios);
        }

        $requestedPeriodoId = (int) $request->input('id_periodo');
        $selectedPeriodoId = ($request->filled('id_periodo') && $periodos->contains('id_periodo', $requestedPeriodoId))
            ? $requestedPeriodoId
            : 0;

        $hasCalculo = $selectedEmpresaId > 0 && $selectedPeriodoId > 0;
        $detalles = $hasCalculo
            ? collect($this->calcularDetalleEmpleados($selectedEmpresaId, $selectedPeriodoId))
            : collect();

        $totales = $hasCalculo
            ? [
                'salud' => (float) $detalles->sum('aporte_salud'),
                'pension' => (float) $detalles->sum('aporte_pension'),
                'arl' => (float) $detalles->sum('aporte_arl'),
                'caja' => (float) $detalles->sum('aporte_caja'),
            ]
            : [
                'salud' => 0.0,
                'pension' => 0.0,
                'arl' => 0.0,
                'caja' => 0.0,
            ];

        $planillaEstado = 'pendiente';
        $canGenerate = $hasCalculo && $detalles->isNotEmpty();
        $archivoGenerado = null;

        if ($hasCalculo) {
            $periodoInicio = optional($periodos->firstWhere('id_periodo', $selectedPeriodoId))->fecha_inicio;
            $planilla = $this->buscarPlanillaExistente($selectedEmpresaId, $selectedPeriodoId, $periodoInicio);

            if ($planilla) {
                $planillaEstado = strtolower((string) ($planilla->estado ?? 'pendiente'));
                $archivoGenerado = $planilla->archivo_generado;

                // Se puede permitir regeneración si hay cambios en los datos (hash diferente)
                // Para esto, el usuario siempre puede intentar generar - el sistema verificará
                // si hay cambios (hash) para permitir o bloquear la regeneración
                $canGenerate = $detalles->isNotEmpty();
            }
        }

        $historialPila = collect();
        if (Schema::hasTable('pila_archivos')) {
            try {
                $this->sincronizarHistorialDesdePlanilla();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error sincronizando historial PILA: ' . $e->getMessage());
            }

            $historialPila = DB::table('pila_archivos as pa')
                ->leftJoin('periodo_liquidacion as pl', 'pl.id_periodo', '=', 'pa.periodo_id')
                ->leftJoin('planilla_pila as pp', function($join) {
                    $join->on('pp.id_periodo', '=', 'pa.periodo_id')
                         ->on('pp.id_empresa', '=', 'pa.empresa_id');
                })
                ->when($selectedEmpresaId > 0, fn($q) => $q->where('pa.empresa_id', $selectedEmpresaId))
                ->when($selectedPeriodoId > 0, fn($q) => $q->where('pa.periodo_id', $selectedPeriodoId))
                ->orderByDesc('pa.id')
                ->limit(20)
                ->get([
                    'pa.id',
                    'pa.periodo_id',
                    'pa.empresa_id',
                    'pa.nombre_archivo',
                    'pa.ruta_archivo',
                    'pa.total_empleados',
                    'pa.created_at',
                    'pl.fecha_inicio',
                    'pl.fecha_fin',
                    'pp.datos_hash',
                    'pp.updated_at as planilla_updated_at',
                ]);

            // Agregar información de cambios pendientes a cada registro
            $historialPila = $historialPila->map(function($item) {
                $tieneChange = false;
                
                // Verificar si hash es null (invalidado)
                if ($item->datos_hash === null) {
                    $tieneChange = true;
                } else {
                    // Verificar si hay cambios en salarios posteriores a la planilla
                    $cambiosPosteriores = DB::table('salario as s')
                        ->where('s.id_periodo', $item->periodo_id)
                        ->where('s.updated_at', '>', $item->planilla_updated_at ?? $item->created_at)
                        ->exists();
                    
                    if ($cambiosPosteriores) {
                        $tieneChange = true;
                    }
                }
                
                $item->tiene_cambios = $tieneChange;
                return $item;
            });
        }

        return view('pila.index', [
            'empresaActual' => $empresaActual,
            'periodos' => $periodos,
            'selectedEmpresaId' => $selectedEmpresaId,
            'selectedPeriodoId' => $selectedPeriodoId,
            'hasCalculo' => $hasCalculo,
            'planillaEstado' => $planillaEstado,
            'canGenerate' => $canGenerate,
            'archivoGenerado' => $archivoGenerado,
            'detalles' => $detalles,
            'totales' => $totales,
            'historialPila' => $historialPila,
            'advertencias' => ($hasCalculo && $detalles->isNotEmpty() && $planillaEstado !== 'generada')
                ? $this->pilaFileGeneratorService->buildAdvertencias($detalles->all())
                : [],
        ]);
    }

    public function generar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_periodo' => ['required', 'integer'],
        ]);

        $empresaId = (int) (session('empresa_id') ?: 0);
        if ($empresaId <= 0) {
            return back()->withErrors([
                'pila' => 'No se pudo identificar la empresa activa de la sesion.',
            ])->withInput();
        }

        $periodoId = (int) $validated['id_periodo'];

        $periodo = PeriodoLiquidacion::query()
            ->where('id_periodo', $periodoId)
            ->where('id_empresa', $empresaId)
            ->first();

        if (!$periodo) {
            return back()->withErrors([
                'pila' => 'El periodo seleccionado no es valido para la empresa activa.',
            ])->withInput();
        }

        $detalles = $this->calcularDetalleEmpleados($empresaId, $periodoId);

        if (count($detalles) === 0) {
            return back()->withErrors([
                'pila' => 'No hay empleados con nomina registrada para generar la planilla PILA en el periodo seleccionado.',
            ])->withInput();
        }

        $totales = $this->pilaFileGeneratorService->calcularTotales($detalles);

        $datosHash = $this->generarHashPlanilla($empresaId, $periodoId, $detalles, $totales);
        $planillaExistente = $this->buscarPlanillaExistente($empresaId, $periodoId, $periodo->fecha_inicio);

        // Verificar si ya fue generada: solo bloquear si no hay cambios en los datos
        if ($planillaExistente && strtolower((string) ($planillaExistente->estado ?? '')) === 'generada') {
            $hashAnterior = (string) ($planillaExistente->datos_hash ?? '');
            
            // Si el hash es igual, significa que no hay cambios - bloquear
            if ($hashAnterior !== '' && $hashAnterior === $datosHash) {
                return back()->withErrors([
                    'pila' => 'Esta planilla ya fue generada sin cambios en los datos. Solo puede descargarse. Si necesita regenerarla, realice cambios en la nómina o información de seguridad social.',
                ])->withInput();
            }
            
            // Si el hash cambió, es una actualización permitida
            // (no bloqueamos, continuamos con la regeneración)
        }

        $empresa = Empresa::query()
            ->select(['id_empresa', 'razon_social', 'nit'])
            ->find($empresaId);

        // --- Validaciones de empresa ---
        $nitNumerico = preg_replace('/\D+/', '', (string) ($empresa->nit ?? ''));
        if (!$empresa || $nitNumerico === '') {
            return back()->withErrors([
                'pila' => 'La empresa no tiene NIT configurado. Configure el NIT antes de generar la planilla PILA.',
            ])->withInput();
        }

        if (trim((string) ($empresa->razon_social ?? '')) === '') {
            return back()->withErrors([
                'pila' => 'La empresa no tiene razón social configurada.',
            ])->withInput();
        }

        // --- Validaciones de empleados ---
        $sinDocumento = array_values(array_filter($detalles, fn($d) => trim((string) ($d['doc_empleado'] ?? '')) === ''));
        if (count($sinDocumento) > 0) {
            return back()->withErrors([
                'pila' => count($sinDocumento) . ' empleado(s) no tienen número de documento registrado. Corrija los datos antes de generar.',
            ])->withInput();
        }

        DB::transaction(function () use ($empresaId, $periodoId, $periodo, $detalles, $totales, $datosHash, $planillaExistente, $empresa): void {
            $now = now();
            $archivoGenerado = $this->pilaFileGeneratorService->guardarArchivoYHistorial(
                $empresaId,
                $periodoId,
                $empresa,
                $periodo,
                $detalles
            );

            $payloadPlanilla = [
                'id_empresa' => $empresaId,
                'periodo' => Carbon::parse($periodo->fecha_inicio)->format('Y-m'),
                'fecha_generacion' => $now->toDateString(),
                'total_salud' => $totales['salud'],
                'total_pension' => $totales['pension'],
                'total_arl' => $totales['arl'],
                'total_caja' => $totales['caja'],
                'estado' => 'generada',
                'archivo_generado' => $archivoGenerado['ruta_archivo'],
                'updated_at' => $now,
            ];

            if ($this->hasPlanillaPeriodoColumn()) {
                $payloadPlanilla['id_periodo'] = $periodoId;
            }

            if ($this->hasPlanillaHashColumn()) {
                $payloadPlanilla['datos_hash'] = $datosHash;
            }

            if ($planillaExistente) {
                $planillaId = (int) $planillaExistente->id;
                DB::table('planilla_pila')
                    ->where('id', $planillaId)
                    ->update($payloadPlanilla);

                DB::table('pila_detalle_empleado')
                    ->where('planilla_id', $planillaId)
                    ->delete();
            } else {
                $payloadPlanilla['created_at'] = $now;
                $planillaId = DB::table('planilla_pila')->insertGetId($payloadPlanilla);
            }

            $rows = [];
            foreach ($detalles as $detalle) {
                $rows[] = [
                    'planilla_id' => $planillaId,
                    'doc_empleado' => $detalle['doc_empleado'],
                    'ibc_salud' => $detalle['ibc_salud'],
                    'ibc_pension' => $detalle['ibc_pension'],
                    'ibc_arl' => $detalle['ibc_arl'],
                    'ibc_caja' => $detalle['ibc_caja'] ?? $detalle['ibc_salud'],
                    'nivel_riesgo_arl' => $detalle['nivel_riesgo_arl'] ?? 1,
                    'valor_arl' => $detalle['valor_arl'] ?? $detalle['aporte_arl'],
                    'aporte_salud' => $detalle['aporte_salud'],
                    'aporte_pension' => $detalle['aporte_pension'],
                    'aporte_arl' => $detalle['aporte_arl'],
                    'aporte_caja' => $detalle['aporte_caja'],
                    'dias_cotizados' => $detalle['dias_cotizados'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('pila_detalle_empleado')->insert($rows);
        });

        return redirect()
            ->route('pila.index', [
                'id_empresa' => $empresaId,
                'id_periodo' => $periodoId,
            ])
            ->with('success', 'Planilla PILA generada correctamente, guardada en base de datos y exportada en archivo plano.');
    }

    public function descargarPila(Request $request): StreamedResponse|RedirectResponse
    {
        $validated = $request->validate([
            'id_periodo' => ['required', 'integer'],
        ]);

        $empresaId = (int) (session('empresa_id') ?: 0);
        if ($empresaId <= 0) {
            return back()->withErrors([
                'pila' => 'No se pudo identificar la empresa activa de la sesion.',
            ]);
        }

        $periodoId = (int) $validated['id_periodo'];

        $periodo = PeriodoLiquidacion::query()
            ->where('id_periodo', $periodoId)
            ->where('id_empresa', $empresaId)
            ->first();

        if (!$periodo) {
            return back()->withErrors([
                'pila' => 'El periodo seleccionado no es válido para la empresa activa.',
            ]);
        }

        $empresa = Empresa::query()
            ->select(['id_empresa', 'razon_social', 'nit'])
            ->find($empresaId);

        // Prioridad 1: servir el archivo ya generado y almacenado.
        $planillaGuardada = $this->buscarPlanillaExistente($empresaId, $periodoId, $periodo->fecha_inicio);
        $rutaGuardada = (string) ($planillaGuardada->archivo_generado ?? '');

        if ($rutaGuardada !== '' && Storage::disk('local')->exists($rutaGuardada)) {
            $contenidoTxt  = Storage::disk('local')->get($rutaGuardada);
            $nombreArchivo = basename($rutaGuardada);

            return response()->streamDownload(function () use ($contenidoTxt): void {
                echo $contenidoTxt;
            }, $nombreArchivo, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        // Prioridad 2 (fallback): recalcular y generar al vuelo.
        if (!in_array($periodo->estado, [PeriodoLiquidacion::ESTADO_PENDIENTE, PeriodoLiquidacion::ESTADO_ABIERTO], true)) {
            return back()->withErrors([
                'pila' => 'El periodo ya está cerrado y no existe archivo generado para descargar.',
            ]);
        }

        $detalles = $this->calcularDetalleEmpleados($empresaId, $periodoId);
        if (count($detalles) === 0) {
            return back()->withErrors([
                'pila' => 'No hay empleados con nómina registrada para descargar la planilla PILA del periodo seleccionado.',
            ]);
        }

        $contenidoTxt = $this->pilaFileGeneratorService->generate($empresa, $periodo, $detalles);
        $empresaToken = preg_replace('/\D+/', '', (string) ($empresa->nit ?? '')) ?: (string) $empresaId;
        $periodoToken = Carbon::parse($periodo->fecha_inicio)->format('Y-m');
        $fileName = sprintf('pila_%s_%s.txt', $empresaToken, $periodoToken);

        return response()->streamDownload(function () use ($contenidoTxt): void {
            echo $contenidoTxt;
        }, $fileName, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function descargarHistorial(int $id): StreamedResponse|RedirectResponse
    {
        $empresaId = (int) (session('empresa_id') ?: 0);
        if ($empresaId <= 0) {
            return back()->withErrors([
                'pila' => 'No se pudo identificar la empresa activa de la sesion.',
            ]);
        }

        if (!Schema::hasTable('pila_archivos')) {
            return back()->withErrors([
                'pila' => 'No existe historial de archivos PILA en este entorno.',
            ]);
        }

        $archivo = DB::table('pila_archivos')
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$archivo) {
            return back()->withErrors([
                'pila' => 'No se encontro el archivo solicitado para la empresa activa.',
            ]);
        }

        $rutaArchivo = (string) ($archivo->ruta_archivo ?? '');
        if ($rutaArchivo === '' || !Storage::disk('local')->exists($rutaArchivo)) {
            return back()->withErrors([
                'pila' => 'El archivo solicitado no existe en almacenamiento local.',
            ]);
        }

        $contenidoTxt = Storage::disk('local')->get($rutaArchivo);
        $nombreArchivo = trim((string) ($archivo->nombre_archivo ?? ''));
        if ($nombreArchivo === '') {
            $nombreArchivo = basename($rutaArchivo);
        }

        return response()->streamDownload(function () use ($contenidoTxt): void {
            echo $contenidoTxt;
        }, $nombreArchivo, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    private function sincronizarHistorialDesdePlanilla(int $empresaId = 0, int $periodoId = 0): void
    {
        if (!Schema::hasTable('planilla_pila')) {
            return;
        }

        $rows = DB::table('planilla_pila')
            ->when($empresaId > 0, fn($q) => $q->where('id_empresa', $empresaId))
            ->when($periodoId > 0, fn($q) => $q->where('id_periodo', $periodoId))
            ->whereNotNull('archivo_generado')
            ->where('archivo_generado', '!=', '')
            ->orderByDesc('id')
            ->limit(50)
            ->get([
                'id',
                'id_periodo',
                'id_empresa',
                'archivo_generado',
                'created_at',
            ]);

        foreach ($rows as $row) {
            $ruta = (string) ($row->archivo_generado ?? '');
            if ($ruta === '') {
                continue;
            }

            $periodoId = (int) ($row->id_periodo ?? 0);
            if ($periodoId <= 0) {
                continue;
            }

            // Validar que el periodo realmente exista para evitar errores de llave foránea
            $periodoExiste = DB::table('periodo_liquidacion')
                ->where('id_periodo', $periodoId)
                ->exists();

            if (!$periodoExiste) {
                continue;
            }

            $yaExiste = DB::table('pila_archivos')
                ->where('empresa_id', (int) $row->id_empresa)
                ->where('periodo_id', $periodoId)
                ->where('ruta_archivo', $ruta)
                ->exists();

            if ($yaExiste) {
                continue;
            }

            $totalEmpleados = 0;
            if (Schema::hasTable('pila_detalle_empleado')) {
                $totalEmpleados = (int) DB::table('pila_detalle_empleado')
                    ->where('planilla_id', (int) $row->id)
                    ->count();
            }

            DB::table('pila_archivos')->insert([
                'periodo_id' => $periodoId,
                'empresa_id' => (int) ($row->id_empresa ?? 0),
                'nombre_archivo' => basename($ruta),
                'ruta_archivo' => $ruta,
                'total_empleados' => $totalEmpleados,
                'created_at' => $row->created_at ?? now(),
            ]);
        }
    }

    private function generarHashPlanilla(int $empresaId, int $periodoId, array $detalles, array $totales): string
    {
        usort($detalles, static function (array $a, array $b): int {
            return strcmp((string) ($a['doc_empleado'] ?? ''), (string) ($b['doc_empleado'] ?? ''));
        });

        $payload = [
            'id_empresa' => $empresaId,
            'id_periodo' => $periodoId,
            'totales' => [
                'salud' => round((float) ($totales['salud'] ?? 0), 2),
                'pension' => round((float) ($totales['pension'] ?? 0), 2),
                'arl' => (float) ($totales['arl'] ?? 0),
                'caja' => round((float) ($totales['caja'] ?? 0), 2),
            ],
            'detalles' => array_map(static function (array $detalle): array {
                return [
                    'doc' => (string) ($detalle['doc_empleado'] ?? ''),
                    'ibc' => round((float) ($detalle['ibc_salud'] ?? 0), 2),
                    'salud' => round((float) ($detalle['aporte_salud'] ?? 0), 2),
                    'pension' => round((float) ($detalle['aporte_pension'] ?? 0), 2),
                    'arl' => (float) ($detalle['aporte_arl'] ?? 0),
                    'caja' => round((float) ($detalle['aporte_caja'] ?? 0), 2),
                    'dias' => (int) ($detalle['dias_cotizados'] ?? 0),
                ];
            }, $detalles),
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function buscarPlanillaExistente(int $empresaId, int $periodoId, $fechaInicio = null): ?object
    {
        $query = DB::table('planilla_pila')->where('id_empresa', $empresaId);

        if ($this->hasPlanillaPeriodoColumn()) {
            $query->where('id_periodo', $periodoId);
        } else {
            $periodoTexto = $fechaInicio
                ? Carbon::parse($fechaInicio)->format('Y-m')
                : null;

            if ($periodoTexto === null) {
                return null;
            }

            $query->where('periodo', $periodoTexto);
        }

        return $query->first();
    }

    private function hasPlanillaPeriodoColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('planilla_pila', 'id_periodo');
        }

        return $hasColumn;
    }

    private function hasPlanillaHashColumn(): bool
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('planilla_pila', 'datos_hash');
        }

        return $hasColumn;
    }

    /**
     * Estructura de cálculo inicial, lista para reemplazar por motor normativo detallado.
     */
    private function calcularDetalleEmpleados(int $empresaId, int $periodoId): array
    {
        return $this->pilaFileGeneratorService->buildDetallesDesdeNomina($empresaId, $periodoId);
    }
}
