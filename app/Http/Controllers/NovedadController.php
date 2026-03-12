<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNovedadEmpleadoRequest;
use App\Http\Requests\UpdateNovedadEmpleadoRequest;
use App\Models\Novedad;
use App\Models\Eps;
use App\Models\Afp;
use App\Models\Arl;
use App\Models\Salario;
use App\Models\TipoNovedad;
use App\Services\CalculoNovedadService;
use App\Models\PeriodoLiquidacion;
use Illuminate\Support\Facades\DB;

class NovedadController extends Controller
{
    private const TIPOS_NOVEDAD_LABELS = [
        'TDE' => 'TDE - Traslado desde EPS',
        'TAE' => 'TAE - Traslado a EPS',
        'TDP' => 'TDP - Traslado desde AFP',
        'TAP' => 'TAP - Traslado a AFP',
        'VSP' => 'VSP - Variación permanente de salario',
        'VST' => 'VST - Variación transitoria de salario',
        'SLN' => 'SLN - Suspensión o licencia no remunerada',
        'IGE' => 'IGE - Incapacidad enfermedad general',
        'IRL' => 'IRL - Incapacidad riesgo laboral',
        'LMAT' => 'LMAT - Licencia de maternidad',
        'LPAT' => 'LPAT - Licencia de paternidad',
        'VAC' => 'VAC - Vacaciones',
        'VCT' => 'VCT - Variación centro de trabajo',
        'INC' => 'INC - Incapacidad',
        'LIC' => 'LIC - Licencia',
    ];

    public function __construct(
        private readonly CalculoNovedadService $calculoNovedadService,
        private readonly \App\Services\NovedadHistorialService $historialService,
        private readonly \App\Services\NovedadFechasService $fechasService,
    ) {
    }

    private function getActivePeriod(): ?PeriodoLiquidacion
    {
        $periodoId = session('active_period_id');
        $empresaId = session('empresa_id');

        if ($periodoId) {
            $periodo = PeriodoLiquidacion::where('id_empresa', $empresaId)
                ->where('id_periodo', $periodoId)
                ->first();
            if ($periodo) {
                return $periodo;
            }
        }

        $periodo = PeriodoLiquidacion::where('id_empresa', $empresaId)
            ->where('estado', PeriodoLiquidacion::ESTADO_ABIERTO)
            ->orderByDesc('fecha_inicio')
            ->first();

        if ($periodo) {
            session(['active_period_id' => $periodo->id_periodo]);
        }

        return $periodo;
    }

    public function index()
    {
        $empresaId = (int) session('empresa_id');
        $catalogos = $this->catalogosNovedad();

        if ($empresaId <= 0) {
            $novedadesVacias = Novedad::query()
                ->whereRaw('1 = 0')
                ->paginate(4)
                ->withQueryString();

            return view('novedades.index', [
                'novedades' => $novedadesVacias,
                'empleadosBusqueda' => collect(),
                ...$catalogos,
            ]);
        }

        $buildEmpleadoQuery = static function (int $empresaFilterId) {
            return DB::table('usuario')
                ->join('contrato', 'contrato.doc', '=', 'usuario.doc')
                ->where('contrato.id_empresa', $empresaFilterId)
                ->where(function ($query) {
                    $query->where('contrato.activo', true)
                        ->orWhere('contrato.estado_laboral', 1)
                        ->orWhere('contrato.estado_nomina', 1);
                })
                ->selectRaw('usuario.doc as doc')
                ->selectRaw("TRIM(CONCAT_WS(' ', usuario.primer_nombre, usuario.otros_nombres, usuario.primer_apellido, usuario.segundo_apellido)) as nombre_completo")
                ->selectRaw("TRIM(CONCAT_WS(' ', usuario.primer_nombre, usuario.otros_nombres)) as nombres")
                ->selectRaw("TRIM(CONCAT_WS(' ', usuario.primer_apellido, usuario.segundo_apellido)) as apellidos")
                ->selectRaw('contrato.salario_base as salario_base')
                ->orderBy('usuario.primer_nombre')
                ->orderBy('usuario.primer_apellido')
                ->limit(800);
        };

        $empleadosRows = $buildEmpleadoQuery($empresaId)->get();

        $empleadosBusqueda = $empleadosRows
            ->map(fn ($row) => [
                'doc' => (string) $row->doc,
                'nombres' => (string) ($row->nombres ?? ''),
                'apellidos' => (string) ($row->apellidos ?? ''),
                'nombre_completo' => (string) ($row->nombre_completo ?? ''),
                'salario_base' => (float) ($row->salario_base ?? 0),
            ])
            ->values();

        $periodoActivo = $this->getActivePeriod();

        $novedades = Novedad::query()
            ->with(['tipoNovedad', 'salario.contrato.usuario'])
            ->when($empresaId > 0, function ($query) use ($empresaId) {
                $query->whereHas('salario.contrato', function ($q) use ($empresaId) {
                    $q->where('id_empresa', $empresaId);
                });
            })
            ->when($periodoActivo, function ($query) use ($periodoActivo) {
                $query->where(function ($q) use ($periodoActivo) {
                    $q->where('id_periodo', $periodoActivo->id_periodo)
                      ->orWhereNull('id_periodo');
                });
            })
            ->where(function ($q) {
                $q->where('estado', '!=', 'cerrada')->orWhereNull('estado');
            })
            ->orderByDesc('id_novedad')
            ->paginate(4)
            ->withQueryString();

        return view('novedades.index', [
            'novedades' => $novedades,
            'empleadosBusqueda' => $empleadosBusqueda,
            'periodoActivo' => $periodoActivo,
            'empresaId' => $empresaId,
            ...$catalogos,
        ]);
    }

    public function historialNovedades()
    {
        $empresaId = (int) session('empresa_id');
        $filtroPeriodo = request('periodo', 'todos');

        $periodos = PeriodoLiquidacion::query()
            ->when($empresaId > 0, fn ($q) => $q->where('id_empresa', $empresaId))
            ->orderByDesc('fecha_inicio')
            ->get(['id_periodo', 'fecha_inicio', 'fecha_fin', 'estado']);

        $novedadesQuery = Novedad::query()
            ->with(['tipoNovedad', 'salario.contrato.usuario', 'periodoLiquidacion'])
            ->when($empresaId > 0, function ($query) use ($empresaId) {
                $query->whereHas('salario.contrato', function ($q) use ($empresaId) {
                    $q->where('id_empresa', $empresaId);
                });
            });

        if ($filtroPeriodo === 'sin_periodo') {
            $novedadesQuery->whereNull('id_periodo');
        } elseif ($filtroPeriodo !== 'todos' && is_numeric($filtroPeriodo)) {
            $novedadesQuery->where('id_periodo', (int) $filtroPeriodo);
        }

        $novedades = $novedadesQuery
            ->orderByDesc('id_novedad')
            ->paginate(10)
            ->withQueryString();

        return view('novedades.historial_novedades', [
            'novedades' => $novedades,
            'periodos' => $periodos,
            'filtroPeriodo' => $filtroPeriodo,
            'empresaId' => $empresaId,
        ]);
    }

    public function historialContrato()
    {
        $empresaId = (int) session('empresa_id');
        $filtroPeriodo = request('periodo', 'todos');

        $periodos = PeriodoLiquidacion::query()
            ->when($empresaId > 0, fn ($q) => $q->where('id_empresa', $empresaId))
            ->orderByDesc('fecha_inicio')
            ->get(['id_periodo', 'fecha_inicio', 'fecha_fin', 'estado']);

        $periodoCabecera = null;
        if ($filtroPeriodo !== 'todos' && is_numeric($filtroPeriodo)) {
            $periodoCabecera = $periodos->firstWhere('id_periodo', (int) $filtroPeriodo);
        }

        $query = DB::table('historial_contrato as h')
            ->join('contrato as c', 'c.id_contrato', '=', 'h.id_contrato')
            ->leftJoin('usuario as u', 'u.doc', '=', 'c.doc')
            ->leftJoin('eps as eps_anterior', function ($join) {
                $join->on('eps_anterior.id_eps', '=', 'h.dato_anterior')
                    ->where('h.tipo_novedad', '=', 'EPS');
            })
            ->leftJoin('eps as eps_nueva', function ($join) {
                $join->on('eps_nueva.id_eps', '=', 'h.dato_nuevo')
                    ->where('h.tipo_novedad', '=', 'EPS');
            })
            ->leftJoin('afp as afp_anterior', function ($join) {
                $join->on('afp_anterior.id_afp', '=', 'h.dato_anterior')
                    ->where('h.tipo_novedad', '=', 'AFP');
            })
            ->leftJoin('afp as afp_nueva', function ($join) {
                $join->on('afp_nueva.id_afp', '=', 'h.dato_nuevo')
                    ->where('h.tipo_novedad', '=', 'AFP');
            })
            ->leftJoin('arl as arl_anterior', function ($join) {
                $join->on('arl_anterior.id_arl', '=', 'h.dato_anterior')
                    ->where('h.tipo_novedad', '=', 'ARL');
            })
            ->leftJoin('arl as arl_nueva', function ($join) {
                $join->on('arl_nueva.id_arl', '=', 'h.dato_nuevo')
                    ->where('h.tipo_novedad', '=', 'ARL');
            })
            ->select(
                'h.id_historial',
                'h.id_contrato',
                'h.dato_anterior',
                'h.dato_nuevo',
                'h.tipo_novedad',
                'h.fecha_cambio',
                'c.doc',
                DB::raw("TRIM(CONCAT_WS(' ', u.primer_nombre, u.otros_nombres, u.primer_apellido, u.segundo_apellido)) as nombre_completo"),
                DB::raw("CASE
                    WHEN h.tipo_novedad = 'EPS' THEN COALESCE(eps_anterior.nombre, h.dato_anterior)
                    WHEN h.tipo_novedad = 'AFP' THEN COALESCE(afp_anterior.nombre, h.dato_anterior)
                    WHEN h.tipo_novedad = 'ARL' THEN COALESCE(arl_anterior.nombre, h.dato_anterior)
                    ELSE h.dato_anterior
                END as dato_anterior_label"),
                DB::raw("CASE
                    WHEN h.tipo_novedad = 'EPS' THEN COALESCE(eps_nueva.nombre, h.dato_nuevo)
                    WHEN h.tipo_novedad = 'AFP' THEN COALESCE(afp_nueva.nombre, h.dato_nuevo)
                    WHEN h.tipo_novedad = 'ARL' THEN COALESCE(arl_nueva.nombre, h.dato_nuevo)
                    ELSE h.dato_nuevo
                END as dato_nuevo_label")
            )
            ->orderByDesc('h.fecha_cambio');

        if ($empresaId > 0) {
            $query->where('c.id_empresa', $empresaId);
        }

        if ($periodoCabecera) {
            $query->whereBetween('h.fecha_cambio', [
                $periodoCabecera->fecha_inicio,
                \Carbon\Carbon::parse($periodoCabecera->fecha_fin)->endOfDay(),
            ]);
        }

        $historial = $query->limit(500)->get();

        return view('novedades.historial', [
            'historial' => $historial,
            'periodos' => $periodos,
            'filtroPeriodo' => $filtroPeriodo,
            'empresaId' => $empresaId,
        ]);
    }

    public function store(StoreNovedadEmpleadoRequest $request)
    {
        $data = $request->validated();
        $salario = $this->resolveEmpleadoSalario((string) $data['empleado_id']);

        if (!$salario) {
            return $this->buildEmpleadoSalarioErrorResponse(true);
        }

        $tipoNovedad = $this->resolveTipoNovedad((string) $data['tipo_novedad']);
        $periodo = $this->getActivePeriod();
        $payload = $this->buildNovedadPayload($data, $salario, $tipoNovedad->id_tipo_novedad, $tipoNovedad->nombre, $periodo);

        $novedad = Novedad::create($payload);
        $this->historialService->registrarCreacion($novedad);
        $this->aplicarEfectosNovedad($data, $salario);

        return redirect()->route('novedades.index')->with('success', 'La novedad se registró correctamente.');
    }

    public function update(UpdateNovedadEmpleadoRequest $request, int $id_novedad)
    {
        $novedad = Novedad::query()->findOrFail($id_novedad);
        $data = $request->validated();

        $salario = $this->resolveEmpleadoSalario((string) $data['empleado_id']);
        if (!$salario) {
            return $this->buildEmpleadoSalarioErrorResponse(false);
        }

        $novedadAnterior = clone $novedad;
        $tipoNovedad = $this->resolveTipoNovedad((string) $data['tipo_novedad']);
        $payload = $this->buildNovedadPayload($data, $salario, $tipoNovedad->id_tipo_novedad, $tipoNovedad->nombre, null);

        $novedad->update($payload);
        $this->historialService->registrarActualizacion($novedadAnterior, $novedad->fresh());
        $this->aplicarEfectosNovedad($data, $salario);

        return redirect()->route('novedades.index')->with('success', 'La novedad se actualizó correctamente.');
    }

    public function destroy(int $id_novedad)
    {
        $novedad = Novedad::query()->findOrFail($id_novedad);
        $this->historialService->registrarEliminacion($novedad);
        $novedad->delete();

        return redirect()->route('novedades.index')->with('success', 'La novedad se eliminó correctamente.');
    }

    private function resolverNombreTipoNovedad(string $tipoNovedad): string
    {
        $key = strtoupper(trim($tipoNovedad));

        return self::TIPOS_NOVEDAD_LABELS[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    private function resolveEmpleadoSalario(string $empleadoId): ?Salario
    {
        return $this->calculoNovedadService->obtenerSalarioEmpleado($empleadoId);
    }

    private function buildEmpleadoSalarioErrorResponse(bool $openModal)
    {
        $response = back()
            ->withErrors(['empleado_id' => 'El empleado seleccionado no tiene una nómina registrada para asociar la novedad.'])
            ->withInput();

        return $openModal ? $response->with('open_novedad_modal', true) : $response;
    }

    private function resolveTipoNovedad(string $tipoNovedad): TipoNovedad
    {
        $nombre = $this->resolverNombreTipoNovedad($tipoNovedad);

        return TipoNovedad::firstOrCreate(['nombre' => $nombre]);
    }

    private function buildNovedadPayload(array $data, Salario $salario, int $tipoNovedadId, string $tipoNovedadNombre, ?PeriodoLiquidacion $periodo = null): array
    {
        $salarioBase = $this->calculoNovedadService->resolverSalarioBase($salario);
        $resultado = $this->calculoNovedadService->calcularNovedad(array_merge($data, ['salario_base' => $salarioBase]));

        $valorCalculado = (float) ($resultado['valor_calculado'] ?? 0);
        $tipoMovimiento = (string) ($resultado['tipo_movimiento'] ?? CalculoNovedadService::OPERACION_SIN_MOVIMIENTO);
        $valorFirmado = $tipoMovimiento === CalculoNovedadService::OPERACION_DESCUENTO ? -$valorCalculado : $valorCalculado;
        $dias = (float) ($data['dias'] ?? 0);
        $horas = (float) ($data['horas'] ?? 0);
        $tipoNovedad = (string) ($data['tipo_novedad'] ?? '');

        // Backend enforces fecha_fin for fixed-duration novelty types
        $fechaInicio = $data['fecha_inicio'];
        $fechaFin = $this->fechasService->tieneDuracionFija($tipoNovedad)
            ? ($this->fechasService->calcularFechaFin($tipoNovedad, $fechaInicio, (int) $dias ?: null) ?? $data['fecha_fin'])
            : $data['fecha_fin'];

        return [
            'id_tipo_novedad' => $tipoNovedadId,
            'id_salario' => $salario->id_salario,
            'id_periodo' => $periodo?->id_periodo ?? $salario->id_periodo ?? null,
            'empleado_id' => $data['empleado_id'],
            'estado' => Novedad::ESTADO_ACTIVA,
            'tipo_novedad_nombre' => $tipoNovedadNombre,
            'fecha' => $fechaInicio,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'unidad_cantidad' => $data['unidad_cantidad'],
            'dias' => $dias,
            'horas' => $horas,
            'cantidad' => $data['unidad_cantidad'] === 'horas' ? $horas : $dias,
            'es_remunerado' => (bool) ($data['es_remunerado'] ?? false),
            'salario_base' => $salarioBase,
            'valor_calculado' => $valorCalculado,
            'valor_novedad' => $valorCalculado,
            'pago' => $valorFirmado,
            'pago_manual' => $data['pago_manual'] ?? null,
            'tipo_movimiento' => $tipoMovimiento,
            'afecta_ibc' => (bool) ($resultado['afecta_ibc'] ?? false),
            'tipo_novedad_codigo' => $tipoNovedad,
            'tipo_licencia' => $data['tipo_licencia'] ?? null,
            'tipo_incapacidad' => $data['tipo_incapacidad'] ?? null,
            'certificado_medico' => (bool) ($data['certificado_medico'] ?? false),
            'observaciones' => $data['observaciones'] ?? null,
        ];
    }

    private function aplicarEfectosNovedad(array $data, Salario $salario): void
    {
        $tipo = strtoupper((string) ($data['tipo_novedad'] ?? ''));
        $contratoId = (int) ($salario->id_contrato ?? 0);

        if ($contratoId <= 0) {
            return;
        }

        if ($tipo === 'VSP') {
            $nuevoSalario = (float) ($data['valor_manual'] ?? $data['pago_manual'] ?? 0);
            if ($nuevoSalario > 0) {
                DB::table('contrato')
                    ->where('id_contrato', $contratoId)
                    ->update([
                        'salario_base' => $nuevoSalario,
                        'updated_at' => now(),
                    ]);
            }
        }

        if (in_array($tipo, ['TDE', 'TAE'], true) && !empty($data['id_eps'])) {
            DB::table('contrato')
                ->where('id_contrato', $contratoId)
                ->update([
                    'id_eps' => (int) $data['id_eps'],
                    'updated_at' => now(),
                ]);
        }

        if (in_array($tipo, ['TDP', 'TAP'], true) && !empty($data['id_afp'])) {
            DB::table('contrato')
                ->where('id_contrato', $contratoId)
                ->update([
                    'id_afp' => (int) $data['id_afp'],
                    'updated_at' => now(),
                ]);
        }

        if ($tipo === 'VCT' && !empty($data['id_arl'])) {
            DB::table('contrato')
                ->where('id_contrato', $contratoId)
                ->update([
                    'id_arl' => (int) $data['id_arl'],
                    'updated_at' => now(),
                ]);
        }
    }

    private function catalogosNovedad(): array
    {
        return [
            'epsList' => Eps::query()->orderBy('nombre')->get(['id_eps', 'nombre']),
            'afpList' => Afp::query()->orderBy('nombre')->get(['id_afp', 'nombre']),
            'arlList' => Arl::query()->orderBy('nombre')->get(['id_arl', 'nombre']),
        ];
    }
}
