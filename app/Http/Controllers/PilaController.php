<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\PeriodoLiquidacion;
use App\Services\NominaParameterService;
use App\Services\PilaFileGeneratorService;
use App\Services\SecuritySocialCalculator;
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
    private const TASA_SALUD = 0.04;
    private const TASA_PENSION = 0.04;
    private const TASA_CAJA = 0.04;

    private object|null $nominaParams;
    private SecuritySocialCalculator $securitySocialCalculator;
    private PilaFileGeneratorService $pilaFileGeneratorService;

    /**
     * Tasas ARL por nivel de riesgo.
     */
    private const TASA_ARL = [
        1 => 0.00522,
        2 => 0.01044,
        3 => 0.02436,
        4 => 0.04350,
        5 => 0.06960,
    ];

    public function __construct(
        NominaParameterService $nominaParameterService,
        SecuritySocialCalculator $securitySocialCalculator,
        PilaFileGeneratorService $pilaFileGeneratorService
    )
    {
        $this->nominaParams = $nominaParameterService->get();
        $this->securitySocialCalculator = $securitySocialCalculator;
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
            $hashActual = $this->generarHashPlanilla($selectedEmpresaId, $selectedPeriodoId, $detalles->all(), $totales);
            $periodoInicio = optional($periodos->firstWhere('id_periodo', $selectedPeriodoId))->fecha_inicio;
            $planilla = $this->buscarPlanillaExistente($selectedEmpresaId, $selectedPeriodoId, $periodoInicio);

            if ($planilla) {
                $planillaEstado = strtolower((string) ($planilla->estado ?? 'pendiente'));
                $archivoGenerado = $planilla->archivo_generado;

                if (
                    $planillaEstado === 'generada'
                    && $this->hasPlanillaHashColumn()
                    && (string) ($planilla->datos_hash ?? '') !== $hashActual
                ) {
                    DB::table('planilla_pila')
                        ->where('id', $planilla->id)
                        ->update([
                            'estado' => 'pendiente',
                            'updated_at' => now(),
                        ]);
                    $planillaEstado = 'pendiente';
                }

                $canGenerate = $planillaEstado !== 'generada' && $detalles->isNotEmpty();
            }
        }

        $historialPila = collect();
        if (Schema::hasTable('pila_archivos')) {
            $this->sincronizarHistorialDesdePlanilla();

            $historialPila = DB::table('pila_archivos as pa')
                ->leftJoin('periodo_liquidacion as pl', 'pl.id_periodo', '=', 'pa.periodo_id')
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
                ]);
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
            ->whereIn('estado', [PeriodoLiquidacion::ESTADO_PENDIENTE, PeriodoLiquidacion::ESTADO_ABIERTO])
            ->first();

        if (!$periodo) {
            return back()->withErrors([
                'pila' => 'El periodo seleccionado no es valido para la empresa activa o ya fue cerrado.',
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

        $sinCambios = $this->hasPlanillaHashColumn()
            ? (string) ($planillaExistente->datos_hash ?? '') === $datosHash
            : false;

        if (
            $planillaExistente
            && strtolower((string) ($planillaExistente->estado ?? '')) === 'generada'
            && $sinCambios
        ) {
            return back()->withErrors([
                'pila' => 'Ya existe una planilla generada para este periodo y no se detectaron cambios en los datos.',
            ])->withInput();
        }

        $empresa = Empresa::query()
            ->select(['id_empresa', 'razon_social', 'nit'])
            ->find($empresaId);

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
            ->whereIn('estado', [PeriodoLiquidacion::ESTADO_PENDIENTE, PeriodoLiquidacion::ESTADO_ABIERTO])
            ->first();

        if (!$periodo) {
            return back()->withErrors([
                'pila' => 'El periodo seleccionado no es valido para la empresa activa o ya fue cerrado.',
            ]);
        }

        $detalles = $this->calcularDetalleEmpleados($empresaId, $periodoId);
        if (count($detalles) === 0) {
            return back()->withErrors([
                'pila' => 'No hay empleados con nomina registrada para descargar la planilla PILA del periodo seleccionado.',
            ]);
        }

        $totales = $this->pilaFileGeneratorService->calcularTotales($detalles);

        $empresa = Empresa::query()
            ->select(['id_empresa', 'razon_social', 'nit'])
            ->find($empresaId);

        $contenidoTxt = $this->construirArchivoPlano($empresa, $periodo, $detalles, $totales);
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

    private function construirArchivoPlano(?Empresa $empresa, object $periodo, array $detalles, array $totales): string
    {
        return $this->pilaFileGeneratorService->generate($empresa, $periodo, $detalles);
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

            $yaExiste = DB::table('pila_archivos')
                ->where('empresa_id', (int) $row->id_empresa)
                ->where('periodo_id', (int) ($row->id_periodo ?? 0))
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
                'periodo_id' => (int) ($row->id_periodo ?? 0),
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

    private function parsearNivelRiesgo(mixed $valor): int
    {
        if (is_numeric($valor)) {
            $n = (int) $valor;
            return $n >= 1 && $n <= 5 ? $n : 1;
        }

        $mapa = [
            'nivel i'   => 1, 'nivel 1' => 1, 'i'   => 1,
            'nivel ii'  => 2, 'nivel 2' => 2, 'ii'  => 2,
            'nivel iii' => 3, 'nivel 3' => 3, 'iii' => 3,
            'nivel iv'  => 4, 'nivel 4' => 4, 'iv'  => 4,
            'nivel v'   => 5, 'nivel 5' => 5, 'v'   => 5,
        ];

        return $mapa[strtolower(trim((string) $valor))] ?? 1;
    }

    private function calcularAportesSeguridadSocial(float $ibc, int $nivelRiesgo): array
    {
        $ibc = round(max(0, $ibc), 2);
        $epsEmpleado = (float) ($this->nominaParams->eps_employee ?? self::TASA_SALUD);
        $pensionEmpleado = (float) ($this->nominaParams->pension_employee ?? self::TASA_PENSION);
        $aportesEmpresa = $this->securitySocialCalculator->calculate($ibc, $this->nominaParams ?? [], $nivelRiesgo);

        return [
            'ibc' => $ibc,
            'aporte_salud_empleado' => round($ibc * $epsEmpleado, 2),
            'aporte_salud_empresa' => (float) $aportesEmpresa['aporte_salud'],
            'aporte_pension_empleado' => round($ibc * $pensionEmpleado, 2),
            'aporte_pension_empresa' => (float) $aportesEmpresa['aporte_pension'],
            'aporte_arl' => (float) $aportesEmpresa['aporte_arl'],
            'aporte_caja' => (float) $aportesEmpresa['aporte_caja'],
        ];
    }
}
