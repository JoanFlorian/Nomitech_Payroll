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
use App\Services\NominaCalculatorService;
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

    /**
     * Tipos de novedad mutuamente exclusivos: no pueden coexistir en las
     * mismas fechas para un mismo empleado.
     */
    private const TIPOS_EXCLUSIVOS = ['SLN', 'IGE', 'IRL', 'LMAT', 'LPAT', 'VAC'];

    public function __construct(
        private readonly CalculoNovedadService $calculoNovedadService,
        private readonly \App\Services\NovedadHistorialService $historialService,
        private readonly \App\Services\NovedadFechasService $fechasService,
        private readonly \App\Services\Benefits\BenefitPaymentService $benefitPaymentService,
        private readonly NominaCalculatorService $calculator,
    ) {
    }


    public function index()
    {
        $empresaId = (int) session('empresa_id');
        $catalogos = $this->catalogosNovedad();
        $periodoActivo = PeriodoLiquidacion::getActivePeriod();
        $periodoId = $periodoActivo ? $periodoActivo->id_periodo : 0;

        if ($empresaId <= 0) {
            $novedadesVacias = Novedad::query()
                ->whereRaw('1 = 0')
                ->paginate(4)
                ->withQueryString();

            return view('novedades.index', [
                'novedades' => $novedadesVacias,
                'empleadosBusqueda' => collect(),
                'periodoActivo' => $periodoActivo,
                ...$catalogos,
            ]);
        }

        $buildEmpleadoQuery = static function (int $empresaFilterId) use ($periodoId) {
            return DB::table('usuario')
                ->join('contrato', 'contrato.doc', '=', 'usuario.doc')
                ->leftJoin('benefit_balance', function($join) {
                    $join->on('benefit_balance.employee_id', '=', 'usuario.doc')
                         ->on('benefit_balance.tenant_id', '=', 'contrato.id_empresa');
                })
                ->where('contrato.id_empresa', $empresaFilterId)
                ->where(function ($query) {
                    $query->where('contrato.activo', true)
                        ->orWhereIn('contrato.estado', ['ACTIVO', 'POR_VENCER', 'PROGRAMADO', 'VENCIDO']);
                })
                ->selectRaw('usuario.doc as doc')
                ->selectRaw("TRIM(CONCAT_WS(' ', usuario.primer_nombre, usuario.otros_nombres, usuario.primer_apellido, usuario.segundo_apellido)) as nombre_completo")
                ->selectRaw("TRIM(CONCAT_WS(' ', usuario.primer_nombre, usuario.otros_nombres)) as nombres")
                ->selectRaw("TRIM(CONCAT_WS(' ', usuario.primer_apellido, usuario.segundo_apellido)) as apellidos")
                ->selectRaw('contrato.salario_base as salario_base')
                ->selectRaw('COALESCE(benefit_balance.vacaciones_balance, 0) as vacaciones_balance')
                ->selectRaw('(SELECT COALESCE(SUM(n.dias), 0) FROM novedad n WHERE n.empleado_id = usuario.doc AND n.tipo_novedad_codigo = "VAC" AND n.id_periodo = ?) as vacaciones_registradas', [$periodoId])
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
                'vacaciones_balance' => (float) ($row->vacaciones_balance ?? 0),
                'vacaciones_registradas' => (float) ($row->vacaciones_registradas ?? 0),
            ])
            ->values();

        $hoy = now()->toDateString();
        $novedades = Novedad::query()
            ->with(['tipoNovedad', 'salario.contrato.usuario'])
            ->when($empresaId > 0, function ($query) use ($empresaId) {
                $query->whereHas('salario.contrato', function ($q) use ($empresaId) {
                    $q->where('id_empresa', $empresaId);
                });
            })
            ->where(function ($main) use ($periodoActivo, $hoy) {
                $main
                    // Novedades del periodo activo o sin periodo
                    ->where(function ($q) use ($periodoActivo) {
                        if ($periodoActivo) {
                            $q->where('id_periodo', $periodoActivo->id_periodo)
                              ->orWhereNull('id_periodo');
                        }
                    })
                    // O novedades de tipo IGE/IRL (o similares) que sigan vigentes por fecha
                    ->orWhere(function ($q) use ($hoy) {
                        $q->whereIn('tipo_novedad_codigo', ['IGE', 'IRL'])
                          ->whereNotNull('fecha_inicio')
                          ->whereNotNull('fecha_fin')
                          ->whereDate('fecha_inicio', '<=', $hoy)
                          ->whereDate('fecha_fin', '>=', $hoy);
                    });
            })
            ->where(function ($q) {
                $q->where('estado', '!=', 'cerrada')->orWhereNull('estado');
            })
            ->orderByDesc('id_novedad')
            ->paginate(4)
            ->withQueryString();

        $periodosCerrados = PeriodoLiquidacion::where('estado', PeriodoLiquidacion::ESTADO_CERRADO)->get(['fecha_inicio', 'fecha_fin']);

        return view('novedades.index', [
            'novedades' => $novedades,
            'empleadosBusqueda' => $empleadosBusqueda,
            'periodoActivo' => $periodoActivo,
            'periodosCerrados' => $periodosCerrados,
            'catalogos' => $this->catalogosNovedad(),
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

        // IGE e IRL requieren periodo activo para garantizar el aislamiento por periodo.
        $tipoNov = strtoupper((string) ($data['tipo_novedad'] ?? ''));
        $periodo = PeriodoLiquidacion::getActivePeriod();
        if (in_array($tipoNov, ['IGE', 'IRL'], true) && !$periodo) {
            $msg = 'No se puede registrar una incapacidad sin un periodo de liquidación activo. Active un periodo primero.';
            session()->flash('error', $msg);
            return back()->withErrors(['tipo_novedad' => $msg])->withInput()->with('open_novedad_modal', true);
        }

        // Validar coexistencia de novedades exclusivas
        $coexistenciaError = $this->verificarCoexistencia(
            (string) $data['empleado_id'],
            strtoupper((string) $data['tipo_novedad']),
            $data['fecha_inicio'],
            $data['fecha_fin']
        );
        if ($coexistenciaError) {
            session()->flash('error', $coexistenciaError);
            return back()->withErrors(['tipo_novedad' => $coexistenciaError])->withInput()->with('open_novedad_modal', true);
        }

        $tipoNovedad = $this->resolveTipoNovedad((string) $data['tipo_novedad']);
        $payload = $this->buildNovedadPayload($data, $salario, $tipoNovedad->id_tipo_novedad, $tipoNovedad->nombre, $periodo);

        $novedad = Novedad::create($payload);
        $this->historialService->registrarCreacion($novedad);
        $this->aplicarEfectosNovedad($data, $salario);

        if (strtoupper($data['tipo_novedad']) === 'VAC' && $periodo) {
            try {
                $this->benefitPaymentService->payBenefit(
                    $data['empleado_id'],
                    \App\Models\BenefitLedger::TYPE_VACACIONES,
                    (float) $data['dias'],
                    (int) session('empresa_id'),
                    'payroll',
                    $periodo->id_periodo,
                    "Novedad VAC [ID:{$novedad->id_novedad}]"
                );
            } catch (\Exception $e) {
                // If ledger fails (e.g. balance), we should probably delete the novelty or report error
                $novedad->delete();
                return back()->withErrors(['dias' => 'Error al registrar balance de vacaciones: ' . $e->getMessage()])->withInput();
            }
        }
        
        $this->refreshPayrollRecalculation((string) $data['empleado_id']);

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

        // Validar coexistencia de novedades exclusivas (excluyendo la novedad que se está editando)
        $coexistenciaError = $this->verificarCoexistencia(
            (string) $data['empleado_id'],
            strtoupper((string) $data['tipo_novedad']),
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $id_novedad
        );
        if ($coexistenciaError) {
            session()->flash('error', $coexistenciaError);
            return back()->withErrors(['tipo_novedad' => $coexistenciaError])->withInput();
        }

        $novedadAnterior = clone $novedad;

        // Validar si la fecha_inicio original pertenece a un periodo cerrado
        if ($novedadAnterior->fecha_inicio) {
            $periodoCerrado = PeriodoLiquidacion::query()
                ->where('estado', PeriodoLiquidacion::ESTADO_CERRADO)
                ->whereDate('fecha_inicio', '<=', $novedadAnterior->fecha_inicio->toDateString())
                ->whereDate('fecha_fin', '>=', $novedadAnterior->fecha_inicio->toDateString())
                ->exists();

            if ($periodoCerrado && $novedadAnterior->fecha_inicio->toDateString() !== $data['fecha_inicio']) {
                $mensaje = 'No se puede modificar la fecha de inicio de esta novedad porque ya ha sido liquidada en un periodo cerrado.';
                session()->flash('error', $mensaje);
                return back()->withErrors(['fecha_inicio' => $mensaje])->withInput();
            }
        }

        $tipoNovedad = $this->resolveTipoNovedad((string) $data['tipo_novedad']);
        $payload = $this->buildNovedadPayload($data, $salario, $tipoNovedad->id_tipo_novedad, $tipoNovedad->nombre, null);

        $novedad->update($payload);
        $this->historialService->registrarActualizacion($novedadAnterior, $novedad->fresh());
        $this->aplicarEfectosNovedad($data, $salario);

        if (strtoupper($data['tipo_novedad']) === 'VAC') {
            $periodo = PeriodoLiquidacion::getActivePeriod();
            if ($periodo) {
                // Cleanup old ledger entry
                \App\Models\BenefitLedger::where('employee_id', $novedad->empleado_id)
                    ->where('benefit_type', \App\Models\BenefitLedger::TYPE_VACACIONES)
                    ->where('movement_type', \App\Models\BenefitLedger::MOVEMENT_SCHEDULED)
                    ->where('reference', 'like', "%[ID:{$novedad->id_novedad}]%")
                    ->delete();

                try {
                    $this->benefitPaymentService->payBenefit(
                        $novedad->empleado_id,
                        \App\Models\BenefitLedger::TYPE_VACACIONES,
                        (float) $data['dias'],
                        (int) session('empresa_id'),
                        'payroll',
                        $periodo->id_periodo,
                        "Novedad VAC [ID:{$novedad->id_novedad}]"
                    );
                } catch (\Exception $e) {
                    // Revert update?
                    $novedad->update($novedadAnterior->toArray());
                    return back()->withErrors(['dias' => 'Error al actualizar balance de vacaciones: ' . $e->getMessage()])->withInput();
                }
            }
        }

        $this->refreshPayrollRecalculation((string) $data['empleado_id']);

        return redirect()->route('novedades.index')->with('success', 'La novedad se actualizó correctamente.');
    }

    public function destroy(int $id_novedad)
    {
        $novedad = Novedad::query()->findOrFail($id_novedad);

        // Validar si la fecha_inicio pertenece a un periodo cerrado
        if ($novedad->fecha_inicio) {
            $periodoCerrado = PeriodoLiquidacion::query()
                ->where('estado', PeriodoLiquidacion::ESTADO_CERRADO)
                ->whereDate('fecha_inicio', '<=', $novedad->fecha_inicio->toDateString())
                ->whereDate('fecha_fin', '>=', $novedad->fecha_inicio->toDateString())
                ->exists();

            if ($periodoCerrado) {
                $mensaje = 'No se puede eliminar esta novedad porque parte de ella ya ha sido liquidada en un periodo cerrado.';
                session()->flash('error', $mensaje);
                return back()->withErrors(['error' => $mensaje]);
            }
        }

        $this->historialService->registrarEliminacion($novedad);

        if (strtoupper($novedad->tipo_novedad_codigo) === 'VAC') {
             // Cleanup scheduled ledger entry
             \App\Models\BenefitLedger::where('employee_id', $novedad->empleado_id)
                ->where('benefit_type', \App\Models\BenefitLedger::TYPE_VACACIONES)
                ->where('movement_type', \App\Models\BenefitLedger::MOVEMENT_SCHEDULED)
                ->where('reference', 'like', "%[ID:{$novedad->id_novedad}]%")
                ->delete();
        }

        $doc = $novedad->empleado_id;
        $novedad->delete();

        $this->refreshPayrollRecalculation((string) $doc);

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
        $message = 'El empleado seleccionado no tiene una nómina activa o salario registrado en el periodo de liquidación actual para asociar la novedad.';
        session()->flash('error', $message);

        $response = back()
            ->withErrors(['empleado_id' => $message])
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
            'afecta_nomina' => true,
            'periodo_aplicado_id' => $periodo?->id_periodo ?? $salario->id_periodo ?? null,
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

    /**
     * Verifica que una novedad exclusiva no se solape con otra del mismo grupo.
     * Retorna un mensaje de error si hay conflicto, o null si todo está bien.
     */
    private function verificarCoexistencia(
        string $empleadoId,
        string $tipoNovedad,
        string $fechaInicio,
        string $fechaFin,
        ?int $excludeNovedadId = null
    ): ?string {
        if (!in_array($tipoNovedad, self::TIPOS_EXCLUSIVOS, true)) {
            return null;
        }

        $conflicto = Novedad::query()
            ->where('empleado_id', $empleadoId)
            ->whereIn('tipo_novedad_codigo', self::TIPOS_EXCLUSIVOS)
            ->where(function ($q) {
                $q->where('estado', '!=', Novedad::ESTADO_CERRADA)
                  ->orWhereNull('estado');
            })
            ->whereDate('fecha_inicio', '<=', $fechaFin)
            ->whereDate('fecha_fin', '>=', $fechaInicio)
            ->when($excludeNovedadId, function ($q, $id) {
                $q->where('id_novedad', '!=', $id);
            })
            ->first();

        if (!$conflicto) {
            return null;
        }

        $labelConflicto = self::TIPOS_NOVEDAD_LABELS[$conflicto->tipo_novedad_codigo] 
            ?? $conflicto->tipo_novedad_codigo;

        $fInicio = $conflicto->fecha_inicio ? $conflicto->fecha_inicio->format('d/m/Y') : '?';
        $fFin = $conflicto->fecha_fin ? $conflicto->fecha_fin->format('d/m/Y') : '?';

        return "No se puede registrar esta novedad porque ya existe una novedad "
            . "\"{$labelConflicto}\" registrada del {$fInicio} al {$fFin}. "
            . "Estas novedades no pueden coexistir en el mismo periodo de tiempo.";
    }

    private function catalogosNovedad(): array
    {
        return [
            'epsList' => Eps::query()->orderBy('nombre')->get(['id_eps', 'nombre']),
            'afpList' => Afp::query()->orderBy('nombre')->get(['id_afp', 'nombre']),
            'arlList' => Arl::query()->orderBy('nombre')->get(['id_arl', 'nombre']),
        ];
    }

    /**
     * Dispara el recálculo de la nómina del empleado para el periodo activo.
     */
    private function refreshPayrollRecalculation(string $doc, ?int $periodoId = null): void
    {
        if (!$periodoId) {
            $periodo = PeriodoLiquidacion::getActivePeriod();
            if (!$periodo) return;
            $periodoId = optional($periodo)->id_periodo;
        }

        $empresaId = (int) session('empresa_id');
        $contrato = DB::table('contrato')->where('doc', $doc)->where('id_empresa', $empresaId)->first();
        if (!$contrato) return;

        $salarioRaw = DB::table('salario')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('id_periodo', $periodoId)
            ->first();

        // Si no hay nómina creada, no recalculamos (se creará al liquidar masivamente o individualmente)
        if (!($salarioRaw instanceof \stdClass)) return;

        // Extraer valores actuales para no perder entradas manuales
        // EXCLUIMOS dias_trabajados para que el calculador use el valor base (30 o proporcional al contrato)
        // y reste las novedades vigentes. Si lo pasamos aquí, estaríamos pasando el RESULTADO anterior
        // como base del nuevo cálculo, lo que causaría que los días no "volvieran a la normalidad".
        $input = [
            'fecha_pago' => optional($salarioRaw)->fecha_pago ?? now()->toDateString(),
            'horas_extra' => (float) ($salarioRaw->horas_extra ?? 0),
            'recargos' => max(0, (float) ($salarioRaw->valor_horas_extras_recargos ?? 0) - (float) ($salarioRaw->horas_extra ?? 0)),
            'bonificaciones' => (float) ($salarioRaw->bonificaciones ?? 0),
            'comisiones' => (float) ($salarioRaw->comisiones ?? 0),
            'otros_devengos' => (float) ($salarioRaw->otros_devengos ?? 0),
            'auxilio_transporte' => (float) ($salarioRaw->auxilio_transporte ?? 0),
            'retencion_fuente' => (float) ($salarioRaw->retencion_fuente ?? 0),
            'embargo_fiscal' => (float) ($salarioRaw->embargo_fiscal ?? 0),
            'pension_voluntaria' => (float) ($salarioRaw->pension_voluntaria ?? 0),
        ];

        // Forzar el guardado usando el servicio de recálculo
        $this->calculator->guardarNominaEmpleado($contrato->id_contrato, $periodoId, $input);
    }
}
