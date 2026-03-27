<?php

namespace App\Http\Controllers;

use App\Models\Salario;
use App\Models\BenefitLedger;
use App\Models\PeriodoLiquidacion;
use App\Models\Empresa;
use App\Services\NominaCalculatorService;
use App\Services\NominaEmployeeService;
use App\Http\Controllers\Concerns\HandlesExportResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\Contrato;
use App\Services\ContractTerminationService;

class NominaController extends Controller
{
    use HandlesExportResponses;

    private NominaCalculatorService $calculator;
    private NominaEmployeeService $employeeService;
    private \App\Services\PlanService $planService;
    private ContractTerminationService $terminationService;

    public function __construct(
        NominaCalculatorService $calculator,
        NominaEmployeeService $employeeService,
        \App\Services\PlanService $planService,
        ContractTerminationService $terminationService
    ) {
        $this->calculator = $calculator;
        $this->employeeService = $employeeService;
        $this->planService = $planService;
        $this->terminationService = $terminationService;
    }

    /* ==========================
       PERIODO ACTIVO
    ========================== */


    private function parsePeriodoRango(string $periodo): array
    {
        $periodo = trim($periodo);
        if ($periodo === '') {
            return [null, null];
        }

        $partes = preg_split('/\s+(?:to|a)\s+/i', $periodo) ?: [];

        if (count($partes) === 2) {
            $inicio = $this->parseFechaFlexible($partes[0]);
            $fin = $this->parseFechaFlexible($partes[1]);

            return [$inicio, $fin];
        }

        $fechaUnica = $this->parseFechaFlexible($periodo);

        return [$fechaUnica, $fechaUnica];
    }

    private function parseFechaFlexible(?string $fecha): ?string
    {
        $fecha = trim((string) $fecha);
        if ($fecha === '') {
            return null;
        }

        foreach (['d/m/Y', 'Y-m-d', 'Y-m'] as $formato) {
            try {
                $parsed = Carbon::createFromFormat($formato, $fecha);

                if ($formato === 'Y-m') {
                    return $parsed->startOfMonth()->toDateString();
                }

                return $parsed->toDateString();
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($fecha)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function construirConsultaNomina(Request $request)
    {
        $busqueda = trim((string) $request->input('documento', ''));
        $periodo = trim((string) $request->input('periodo', ''));
        $empresaId = session('empresa_id');

        $periodoActivo = PeriodoLiquidacion::getActivePeriod();

        $query = Salario::with('contrato.usuario')
            ->select('salario.*')
            ->addSelect([
                'total_novedades' => DB::table('novedad')
                    ->selectRaw('COALESCE(SUM(pago), 0)')
                    ->whereColumn('novedad.id_salario', 'salario.id_salario'),
                'total_novedades_devengado' => DB::table('novedad')
                    ->selectRaw('COALESCE(SUM(CASE WHEN pago > 0 THEN pago ELSE 0 END), 0)')
                    ->whereColumn('novedad.id_salario', 'salario.id_salario'),
                'total_novedades_deduccion' => DB::table('novedad')
                    ->selectRaw('COALESCE(SUM(CASE WHEN pago < 0 THEN ABS(pago) ELSE 0 END), 0)')
                    ->whereColumn('novedad.id_salario', 'salario.id_salario'),
            ])
            ->whereHas('contrato', function ($q) use ($empresaId) {
                $q->where('id_empresa', $empresaId);
            })
            ->when($periodoActivo, function ($q) use ($periodoActivo) {
                $q->where('id_periodo', $periodoActivo->id_periodo);
            })
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $term = mb_strtolower($busqueda);
                $q->whereHas('contrato.usuario', function ($u) use ($busqueda, $term) {
                    $u->where('doc', 'like', "%{$busqueda}%")
                        ->orWhereRaw(
                            "LOWER(CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)) LIKE ?",
                            ["%{$term}%"]
                        );
                });
            });

        if ($periodo !== '') {
            [$fechaInicio, $fechaFin] = $this->parsePeriodoRango($periodo);

            if ($fechaInicio && $fechaFin) {
                if ($fechaInicio > $fechaFin) {
                    [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
                }
                $query->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
            } elseif ($fechaInicio) {
                $query->whereDate('fecha_pago', $fechaInicio);
            }
        }

        return $query;
    }

    /* ==========================
       INDEX
    ========================== */

    public function index(Request $request)
    {
        $periodoActivo = PeriodoLiquidacion::getActivePeriod();

        $salarios = $this->construirConsultaNomina($request)
            ->orderBy(
                DB::table('usuario as u')
                    ->selectRaw("LOWER(TRIM(CONCAT_WS(' ', u.primer_nombre, IFNULL(u.otros_nombres, ''), u.primer_apellido, IFNULL(u.segundo_apellido, ''))))")
                    ->join('contrato as c', 'c.doc', '=', 'u.doc')
                    ->whereColumn('c.id_contrato', 'salario.id_contrato')
                    ->limit(1)
            )
            ->orderByDesc('fecha_pago')
            ->paginate(5)
            ->withQueryString();

        $salarios->setCollection(
            $salarios->getCollection()->map(function (Salario $salario) {
                $resumenNovedades = $this->obtenerResumenNovedadesPorContratoPeriodo(
                    (int) $salario->id_contrato,
                    (int) $salario->id_periodo
                );

                $totalDevNovedades =
                    (float) ($resumenNovedades['horas_extra'] ?? 0)
                    + (float) ($resumenNovedades['recargos'] ?? 0)
                    + (float) ($resumenNovedades['bonificaciones'] ?? 0)
                    + (float) ($resumenNovedades['otros_devengos'] ?? 0);

                $totalDedNovedades = (float) ($resumenNovedades['deducciones'] ?? 0);

                // Estos atributos son usados por el grid y por los accessors de Salario.
                $salario->setAttribute('total_novedades', $totalDevNovedades - $totalDedNovedades);
                $salario->setAttribute('total_novedades_devengado', $totalDevNovedades);
                $salario->setAttribute('total_novedades_deduccion', $totalDedNovedades);
                $salario->setAttribute('resumen_novedades', $resumenNovedades);

                return $salario;
            })
        );

        return view('nomina.index', compact('salarios', 'periodoActivo'));
    }

    /* ==========================
       STEP 1
    ========================== */

    public function step1(Request $request)
    {
        if ($request->boolean('fresh')) {
            session()->forget('nomina');
        }

        session(['nomina.step' => 1]);

        $step1 = session('nomina.step1', []);
        $periodoActivo = PeriodoLiquidacion::getActivePeriod();
        $isEditing = (bool) session('nomina.editing_id');

        $empresaId = session('empresa_id');
        $salarios = Salario::with('contrato.usuario')
            ->whereHas('contrato', function ($q) use ($empresaId) {
                $q->where('id_empresa', $empresaId);
            })
            ->when($periodoActivo, function ($q) use ($periodoActivo) {
                $q->where('id_periodo', $periodoActivo->id_periodo);
            })
            ->orderByDesc('fecha_pago')
            ->limit(10)
            ->get();

        return view('nomina.step1', compact('step1', 'periodoActivo', 'isEditing', 'salarios'));
    }

    public function edit(int $idSalario)
    {
        $empresaId = session('empresa_id');

        $registro = DB::table('salario as s')
            ->join('contrato as c', 'c.id_contrato', '=', 's.id_contrato')
            ->join('usuario as u', 'u.doc', '=', 'c.doc')
            ->where('s.id_salario', $idSalario)
            ->where('c.id_empresa', $empresaId)
            ->select(
                's.*',
                'c.doc',
                'c.salario_base',
                DB::raw("TRIM(CONCAT(u.primer_nombre,' ',IFNULL(u.otros_nombres,''),' ',u.primer_apellido,' ',IFNULL(u.segundo_apellido,''))) as nombre"),
                'u.telefono'
            )
            ->first();

        if (!$registro) {
            return redirect()->route('nomina.index')
                ->with('error', 'No se encontró la nómina seleccionada para edición.');
        }

        $diasTrabajados = max(0, min(30, (int) ($registro->dias_a_trabajar ?? 30)));
        $salarioBaseMensual = (float) ($registro->salario_base ?? 0);
        $valorDia = $salarioBaseMensual / 30;
        $salarioBaseProporcional = $valorDia * $diasTrabajados;

        $horasExtra = (float) ($registro->horas_extra ?? 0);
        $totalHorasRecargos = (float) ($registro->valor_horas_extras_recargos ?? 0);
        $recargos = max(0, $totalHorasRecargos - $horasExtra);
        $detalleHoras = DB::table('hora_recargo_extra')
            ->where('id_salario', $idSalario)
            ->pluck('cantidad', 'id_tipo_hora_recargo')
            ->map(fn($v) => (float) $v)
            ->toArray();

        $detalleEstimado = false;
        if (empty($detalleHoras) && ($horasExtra > 0 || $recargos > 0)) {
            // Compatibilidad para registros historicos creados antes de guardar el detalle por tipo.
            $detalleHoras = $this->estimateDetalleRecargosFromStoredValues($horasExtra, $recargos, $salarioBaseProporcional);
            $detalleEstimado = !empty($detalleHoras);
        }

        // Calcular días de suspensión (SLN) dentro del periodo
        $periodoEdit = PeriodoLiquidacion::query()->find((int) $registro->id_periodo);
        $diasSln = $periodoEdit
            ? $this->calcularDiasSlnPeriodo((int) $registro->id_contrato, $periodoEdit)
            : 0;
        $maxDias = max(0, 30 - $diasSln);

        $valorHora = $salarioBaseMensual > 0 ? ($salarioBaseMensual / 240) : 0;
        $resumenNov = $this->calculator->resumirNovedadesContratoPeriodo((int) $registro->id_contrato, (int) $registro->id_periodo, $valorHora);

        $horasExtraManual = max(0, $horasExtra - (float) ($resumenNov['horas_extra'] ?? 0));
        $recargosManual = max(0, $recargos - (float) ($resumenNov['recargos'] ?? 0));

        session(['nomina.editing_id' => (int) $registro->id_salario]);
        session([
            'nomina.step1' => [
                'empleado_busqueda' => trim(($registro->nombre ?? '') . ' - ' . ($registro->doc ?? '')),
                'doc' => $registro->doc,
                'nombre' => $registro->nombre,
                'telefono' => $registro->telefono,
                'id_contrato' => (int) $registro->id_contrato,
                'id_periodo' => (int) $registro->id_periodo,
                'salario_base' => $salarioBaseMensual,
                'valor_dia' => $valorDia,
                'salario_base_proporcional' => $salarioBaseProporcional,
                'fecha_pago' => $registro->fecha_pago,
                'dias_trabajados' => $diasTrabajados,
                'dias_sln' => $diasSln,
                'max_dias_trabajados' => $maxDias,
            ]
        ]);
        session([
            'nomina.step2' => [
                'horas_extra' => $horasExtraManual,
                'recargos' => $recargosManual,
                'total_horas_extra' => $horasExtraManual,
                'total_recargos' => $recargosManual,
                'total_devengos_parcial' => $salarioBaseProporcional + $horasExtraManual + $recargosManual,
                'detalle_recargos' => $detalleHoras,
                'detalle_recargos_estimado' => $detalleEstimado,
            ]
        ]);
        session([
            'nomina.step2_ingresos' => [
                'bonificaciones' => max(0, (float) ($registro->bonificaciones ?? 0) - (float) ($resumenNov['bonificaciones'] ?? 0)),
                'comisiones' => (float) ($registro->comisiones ?? 0),
                'otros_devengos' => max(0, (float) ($registro->otros_devengos ?? 0) - (float) ($resumenNov['otros_devengos'] ?? 0)),
                'aplica_auxilio_transporte' => ((float) ($registro->auxilio_transporte ?? 0)) > 0 ? 1 : 0,
                'auxilio_transporte' => (float) ($registro->auxilio_transporte ?? 0),
            ]
        ]);
        session([
            'nomina.step3' => [
                'retencion_fuente' => (float) ($registro->retencion_fuente ?? 0),
                'embargo_fiscal' => (float) ($registro->embargo_fiscal ?? 0),
                'pension_voluntaria' => (float) ($registro->pension_voluntaria ?? 0),
            ]
        ]);

        return redirect()->route('nomina.step1');
    }

    public function postStep1(Request $request)
    {
        $isEditing = (bool) session('nomina.editing_id');

        $data = $request->validate([
            'empleado_busqueda' => ['required', 'string', 'max:120', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-]+$/u'],
            'doc' => ['required', 'string', 'regex:/^[0-9]{5,20}$/'],
            'id_contrato' => 'required|integer|min:1',
            'fecha_pago' => $isEditing ? 'required|date' : 'required|date|after_or_equal:today',
            'dias_trabajados' => 'required|integer|min:0|max:30',
        ], [
            'empleado_busqueda.regex' => 'El campo de búsqueda solo permite letras, números, espacios y guion (-).',
            'doc.regex' => 'El documento solo debe contener números (5 a 20 dígitos).',
            'fecha_pago.after_or_equal' => 'La fecha de pago no puede ser menor a hoy.',
        ]);

        $empresaId = session('empresa_id');
        $periodoActivo = PeriodoLiquidacion::getActivePeriod();

        if (!$periodoActivo) {
            return back()->withErrors([
                'empleado_busqueda' => 'No existe un periodo activo para registrar esta nómina.',
            ])->withInput();
        }

        $empleado = $this->employeeService->buscarEmpleado(
            $data['doc'],
            $empresaId
        );

        if (!$empleado) {
            return back()->with('error', 'Empleado no encontrado.');
        }

        if ((int) $empleado->id_contrato !== (int) $data['id_contrato']) {
            return back()->withErrors([
                'empleado_busqueda' => 'El contrato seleccionado no coincide con el empleado.',
            ])->withInput();
        }

        if (!$isEditing) {
            $yaRegistrado = DB::table('salario')
                ->where('id_contrato', (int) $data['id_contrato'])
                ->where('id_periodo', (int) $periodoActivo->id_periodo)
                ->exists();

            if ($yaRegistrado) {
                return back()->withErrors([
                    'empleado_busqueda' => 'Este empleado ya tiene nómina registrada en el periodo activo.',
                ])->withInput();
            }
        }

        $diasTrabajados = max(0, min(30, (int) $data['dias_trabajados']));

        // Calcular días de suspensión (SLN) dentro del periodo activo
        $diasSln = $this->calcularDiasSlnPeriodo(
            (int) $data['id_contrato'],
            $periodoActivo
        );
        $maxDias = max(0, 30 - $diasSln);

        if ($diasTrabajados > $maxDias) {
            return back()->withErrors([
                'dias_trabajados' => "Los días trabajados no pueden exceder {$maxDias} días debido a {$diasSln} día(s) de suspensión (SLN) registrados en este periodo.",
            ])->withInput();
        }

        $salarioBaseMensual = (float) ($empleado->salario_base ?? 0);
        $valorDia = $salarioBaseMensual / 30;
        $salarioBaseProporcional = $valorDia * $diasTrabajados;

        $step1 = [
            'empleado_busqueda' => trim(($empleado->nombre ?? '') . ' - ' . ($empleado->doc ?? '')),
            'doc' => $empleado->doc,
            'nombre' => $empleado->nombre,
            'telefono' => $empleado->telefono,
            'id_contrato' => $empleado->id_contrato,
            'id_periodo' => (int) $periodoActivo->id_periodo,
            'salario_base' => $salarioBaseMensual,
            'valor_dia' => $valorDia,
            'salario_base_proporcional' => $salarioBaseProporcional,
            'fecha_pago' => $data['fecha_pago'],
            'dias_trabajados' => $diasTrabajados,
            'dias_sln' => $diasSln,
            'max_dias_trabajados' => $maxDias,
        ];

        session(['nomina.step1' => $step1]);

        return redirect()->route('nomina.step2');
    }

    /* ==========================
       STEP 2
    ========================== */

    public function step2()
    {
        session(['nomina.step' => 2]);

        $s1 = session('nomina.step1');

        if (!$s1) {
            return redirect()->route('nomina.step1')
                ->with('error', 'Completa el paso 1 primero.');
        }

        $salarioBase = (float) ($s1['salario_base_proporcional'] ?? $s1['salario_base'] ?? 0);
        $step2 = session('nomina.step2', []);

        $empresaId = session('empresa_id');
        $periodoActivo = PeriodoLiquidacion::getActivePeriod();

        $salarios = Salario::with('contrato.usuario')
            ->whereHas('contrato', function ($q) use ($empresaId) {
                $q->where('id_empresa', $empresaId);
            })
            ->when($periodoActivo, function ($q) use ($periodoActivo) {
                $q->where('id_periodo', $periodoActivo->id_periodo);
            })
            ->orderByDesc('fecha_pago')
            ->limit(10)
            ->get();

        // Cargar tipos y normalizar a multiplicadores legales (factor por hora).
        $tiposRecargo = DB::table('tipo_hora_recargo')
            ->orderBy('nombre')
            ->get()
            ->map(function ($item) {
                $item->valor = $this->normalizeRecargoMultiplier((string) ($item->nombre ?? ''), (float) ($item->valor ?? 0));
                return $item;
            });

        // 🔹 Obtener horas mes desde parámetros
        $params = app(\App\Services\NominaParameterService::class)->get();
        $horasMes = $params->horas_mes;

        return view('nomina.step2', compact(
            'salarioBase',
            'step2',
            'salarios',
            'periodoActivo',
            'tiposRecargo',
            'horasMes'
        ));
    }

    public function postStep2(Request $request)
    {
        $tiposRecargo = DB::table('tipo_hora_recargo')
            ->select('id_tipo_hora_recargo', 'nombre', 'valor')
            ->orderBy('nombre')
            ->get();

        $data = $request->validate([
            'horas_extra' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'recargos' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'total_horas_extra' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'total_recargos' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'total_devengos_parcial' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'detalle_recargos' => 'nullable|array',
            'detalle_recargos.*' => 'nullable|numeric|min:0|max:744',
        ], [
            'horas_extra.regex' => 'Horas extra solo permite números.',
            'recargos.regex' => 'Recargos solo permite números.',
            'total_horas_extra.regex' => 'Total horas extra solo permite números.',
            'total_recargos.regex' => 'Total recargos solo permite números.',
            'total_devengos_parcial.regex' => 'Total devengos parcial solo permite números.',
            'detalle_recargos.*.numeric' => 'Las cantidades de horas y recargos solo permiten números.',
            'detalle_recargos.*.min' => 'Las cantidades de horas y recargos no pueden ser negativas.',
            'detalle_recargos.*.max' => 'Las cantidades de horas y recargos no pueden superar 744.',
        ]);

        $detalleRecargos = [];
        $detalleInput = (array) ($data['detalle_recargos'] ?? []);
        $salarioBasePaso1 = (float) (session('nomina.step1.salario_base_proporcional') ?? session('nomina.step1.salario_base') ?? 0);

        foreach ($tiposRecargo as $tipo) {
            $tipoId = (string) $tipo->id_tipo_hora_recargo;
            $cantidad = (float) ($detalleInput[$tipoId] ?? 0);
            $cantidad = max(0, min(744, $cantidad));
            $detalleRecargos[(int) $tipo->id_tipo_hora_recargo] = $cantidad;
        }

        $calculoDetalle = $this->calculateStep2TotalsFromDetail($detalleRecargos, $salarioBasePaso1);
        $totalHorasExtra = $calculoDetalle['total_horas_extra'];
        $totalRecargos = $calculoDetalle['total_recargos'];

        $reqTotalHorasExtra = $this->parseMoneyInput($data['total_horas_extra'] ?? 0);
        $reqTotalRecargos = $this->parseMoneyInput($data['total_recargos'] ?? 0);
        $reqTotalParcial = $this->parseMoneyInput($data['total_devengos_parcial'] ?? 0);

        // Fallback para compatibilidad si no llega detalle de horas.
        $horasExtra = $totalHorasExtra > 0 ? $totalHorasExtra : $this->parseMoneyInput($data['horas_extra'] ?? 0);
        $recargos = $totalRecargos > 0 ? $totalRecargos : $this->parseMoneyInput($data['recargos'] ?? 0);

        if ($horasExtra <= 0 && $reqTotalHorasExtra > 0) {
            $horasExtra = $reqTotalHorasExtra;
        }

        if ($recargos <= 0 && $reqTotalRecargos > 0) {
            $recargos = $reqTotalRecargos;
        }

        $totalDevengosParcial = $salarioBasePaso1 + $horasExtra + $recargos;
        if ($totalDevengosParcial <= 0 && $reqTotalParcial > 0) {
            $totalDevengosParcial = $reqTotalParcial;
        }

        if ($horasExtra < 0 || $recargos < 0 || $horasExtra > 999999999999 || $recargos > 999999999999) {
            return back()->withErrors([
                'horas_extra' => 'Los valores de horas extra y recargos no son válidos.',
            ])->withInput();
        }

        session([
            'nomina.step2' => [
                'horas_extra' => $horasExtra,
                'recargos' => $recargos,
                // Claves adicionales para compatibilidad con vistas actuales.
                'total_horas_extra' => $horasExtra,
                'total_recargos' => $recargos,
                'total_devengos_parcial' => $totalDevengosParcial,
                'detalle_recargos' => $detalleRecargos,
            ]
        ]);

        return redirect()->route('nomina.step2.ingresos');
    }

    /* ==========================
       INGRESOS
    ========================== */

    public function step2Ingresos()
    {
        $s1 = session('nomina.step1');
        $s2 = session('nomina.step2', []);
        $step2Ingresos = session('nomina.step2_ingresos', []);
        $editingId = (int) session('nomina.editing_id', 0);

        if (!$s1 || !$s2) {
            return redirect()->route('nomina.step2')
                ->with('error', 'Completa primero el paso 2 de horas y recargos.');
        }

        $salarioMensual = (float) ($s1['salario_base'] ?? 0);
        $diasTrabajados = max(0, min(30, (int) ($s1['dias_trabajados'] ?? 30)));
        $salarioDiario = $salarioMensual / 30;
        $salarioDevengado = $salarioDiario * $diasTrabajados;
        $salarioBase = $salarioDevengado;

        // En edicion, si por alguna razon la sesion del paso 2 queda en cero,
        // recuperar montos guardados de la nomina para evitar resumen vacio.
        if ($editingId > 0) {
            $horasS2 = (float) ($s2['horas_extra'] ?? $s2['total_horas_extra'] ?? 0);
            $recargosS2 = (float) ($s2['recargos'] ?? $s2['total_recargos'] ?? 0);

            if ($horasS2 <= 0 && $recargosS2 <= 0) {
                $salarioEdit = DB::table('salario')
                    ->where('id_salario', $editingId)
                    ->first(['horas_extra', 'valor_horas_extras_recargos']);

                if ($salarioEdit) {
                    $horasBd = (float) ($salarioEdit->horas_extra ?? 0);
                    $recargosBd = max(0, (float) ($salarioEdit->valor_horas_extras_recargos ?? 0) - $horasBd);

                    $s2['horas_extra'] = $horasBd;
                    $s2['recargos'] = $recargosBd;
                    $s2['total_horas_extra'] = $horasBd;
                    $s2['total_recargos'] = $recargosBd;
                    $s2['total_devengos_parcial'] = $salarioBase + $horasBd + $recargosBd;

                    session(['nomina.step2' => $s2]);
                }
            }
        }

        $step2AutoRecalculated = false;
        $detalleRecargos = (array) ($s2['detalle_recargos'] ?? []);
        if (!empty($detalleRecargos)) {
            $prevHorasExtra = (float) ($s2['total_horas_extra'] ?? $s2['horas_extra'] ?? 0);
            $prevRecargos = (float) ($s2['total_recargos'] ?? $s2['recargos'] ?? 0);
            $prevParcial = (float) ($s2['total_devengos_parcial'] ?? 0);

            $calculoDetalle = $this->calculateStep2TotalsFromDetail($detalleRecargos, $salarioBase);
            $s2['horas_extra'] = $calculoDetalle['total_horas_extra'];
            $s2['recargos'] = $calculoDetalle['total_recargos'];
            $s2['total_horas_extra'] = $calculoDetalle['total_horas_extra'];
            $s2['total_recargos'] = $calculoDetalle['total_recargos'];
            $s2['total_devengos_parcial'] = $salarioBase + $calculoDetalle['total_horas_extra'] + $calculoDetalle['total_recargos'];

            $step2AutoRecalculated =
                abs($prevHorasExtra - $s2['total_horas_extra']) > 0.5 ||
                abs($prevRecargos - $s2['total_recargos']) > 0.5 ||
                abs($prevParcial - $s2['total_devengos_parcial']) > 0.5;

            session(['nomina.step2' => $s2]);
        } else {
            // Saneamiento defensivo cuando no hay detalle por tipo (registros antiguos o sesión dañada).
            $prevHorasExtra = (float) ($s2['total_horas_extra'] ?? $s2['horas_extra'] ?? 0);
            $prevRecargos = (float) ($s2['total_recargos'] ?? $s2['recargos'] ?? 0);

            $s2['horas_extra'] = max(0, $this->parseMoneyInput($s2['horas_extra'] ?? 0));
            $s2['recargos'] = max(0, $this->parseMoneyInput($s2['recargos'] ?? 0));

            // Topes de plausibilidad basados en normativa por hora para evitar cifras absurdas heredadas.
            $valorHora = $salarioBase > 0 ? ($salarioBase / 240) : 0;
            $maxHorasExtraPlausible = $valorHora * 2.5 * 744; // extra mas alta
            $maxRecargosPlausible = $valorHora * 0.75 * 744; // recargo mas alto solicitado

            if ($maxHorasExtraPlausible > 0 && $s2['horas_extra'] > ($maxHorasExtraPlausible * 3)) {
                $s2['horas_extra'] = 0;
                $step2AutoRecalculated = true;
            }

            if ($maxRecargosPlausible > 0 && $s2['recargos'] > ($maxRecargosPlausible * 3)) {
                $s2['recargos'] = 0;
                $step2AutoRecalculated = true;
            }

            $s2['total_horas_extra'] = $s2['horas_extra'];
            $s2['total_recargos'] = $s2['recargos'];
            $s2['total_devengos_parcial'] = $salarioBase + $s2['horas_extra'] + $s2['recargos'];

            $step2AutoRecalculated =
                abs($prevHorasExtra - $s2['total_horas_extra']) > 0.5 ||
                abs($prevRecargos - $s2['total_recargos']) > 0.5;

            session(['nomina.step2' => $s2]);
        }

        $salarioBaseMensual = (float) ($s1['salario_base'] ?? 0);

        $params = app(\App\Services\NominaParameterService::class)->get();
        $smmlv = (float) ($params->smmlv ?? 0);
        $topeAuxilio = (float) ($params->auxilio_transporte_tope ?? 0);
        $auxilioTransporteDb = (float) ($params->auxilio_transporte ?? 0);

        $aplicaPorTope = $smmlv > 0 && $topeAuxilio > 0
            ? ($salarioBaseMensual <= ($smmlv * $topeAuxilio))
            : true;

        if (!$aplicaPorTope) {
            $auxilioTransporteDb = 0;
        } else {
            // Pro-rate the allowance based on worked days (monthly amount / 30 * days)
            $auxilioTransporteDb = ($auxilioTransporteDb / 30) * $diasTrabajados;
        }

        $empresaId = session('empresa_id');
        $periodoActivo = PeriodoLiquidacion::getActivePeriod();

        $salarios = Salario::with('contrato.usuario')
            ->whereHas('contrato', function ($q) use ($empresaId) {
                $q->where('id_empresa', $empresaId);
            })
            ->when($periodoActivo, function ($q) use ($periodoActivo) {
                $q->where('id_periodo', $periodoActivo->id_periodo);
            })
            ->orderByDesc('fecha_pago')
            ->limit(10)
            ->get();

        // ── AUTO-SCHEDULE BENEFITS ON TERMINATION ──
        if ($periodoActivo && !empty($s1['id_contrato'])) {
            $contratoObj = Contrato::find($s1['id_contrato']);
            if ($contratoObj) {
                $termResult = $this->terminationService->handleContractTermination($contratoObj, $periodoActivo);
                
                // If suggested days were returned (contract ends in period), update session
                if ($termResult['is_terminating'] && $termResult['suggested_days'] !== null) {
                    $s1['dias_trabajados'] = $termResult['suggested_days'];
                    session(['nomina.step1' => $s1]);
                }
            }
        }

        // Scheduled benefit payments for this employee/period (informational)
        $benefitPayments = collect();
        $employeeDoc = $s1['doc'] ?? null;
        if ($employeeDoc && $periodoActivo) {
            $benefitPayments = BenefitLedger::where('employee_id', $employeeDoc)
                ->where('payroll_period_id', $periodoActivo->id_periodo)
                ->where('movement_type', BenefitLedger::MOVEMENT_SCHEDULED)
                ->where('status', BenefitLedger::STATUS_PENDING_PAYROLL)
                ->get()
                ->map(function ($bp) use ($salarioBase) {
                    $bp->display_amount = abs($bp->amount);
                    if ($bp->benefit_type === BenefitLedger::TYPE_VACACIONES) {
                        $bp->display_amount = (($salarioBase ?? 0) / 30) * $bp->display_amount;
                    }
                    return $bp;
                })
                ->filter(function ($bp) {
                    // Exclude Cesantías from display and total as they are paid differently
                    return trim(strtolower($bp->benefit_type)) !== 'cesantias';
                });
        }

        return view('nomina.step2_ingresos', compact(
            'salarioBase',
            's2',
            'step2Ingresos',
            'salarios',
            'periodoActivo',
            'auxilioTransporteDb',
            'aplicaPorTope',
            'step2AutoRecalculated',
            'benefitPayments'
        ));
    }

    public function postStep2Ingresos(Request $request)
    {
        $data = $request->validate([
            'bonificaciones' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'comisiones' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'otros_devengos' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'aplica_auxilio_transporte' => 'required|in:0,1',
            'auxilio_transporte' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
        ], [
            'bonificaciones.regex' => 'Bonificaciones solo permite números.',
            'comisiones.regex' => 'Comisiones solo permite números.',
            'otros_devengos.regex' => 'Otros devengos solo permite números.',
            'auxilio_transporte.regex' => 'Auxilio de transporte solo permite números.',
        ]);

        $bonificaciones = $this->parseMoneyInput($data['bonificaciones'] ?? 0);
        $comisiones = $this->parseMoneyInput($data['comisiones'] ?? 0);
        $otrosDevengos = $this->parseMoneyInput($data['otros_devengos'] ?? 0);

        $maxMoney = 999999999999;
        if (
            $bonificaciones < 0 || $bonificaciones > $maxMoney ||
            $comisiones < 0 || $comisiones > $maxMoney ||
            $otrosDevengos < 0 || $otrosDevengos > $maxMoney
        ) {
            return back()->withErrors([
                'bonificaciones' => 'Los valores monetarios deben ser no negativos y menores o iguales a 999.999.999.999.',
            ])->withInput();
        }

        $aplicaAuxilio = (int) ($data['aplica_auxilio_transporte'] ?? 0) === 1;
        $auxilioTransporte = $this->parseMoneyInput($data['auxilio_transporte'] ?? 0);

        session([
            'nomina.step2_ingresos' => [
                'bonificaciones' => $bonificaciones,
                'comisiones' => $comisiones,
                'otros_devengos' => $otrosDevengos,
                'aplica_auxilio_transporte' => $aplicaAuxilio ? 1 : 0,
                'auxilio_transporte' => $auxilioTransporte,
                'recalculate_transport_allowance' => true,
            ]
        ]);

        return redirect()->route('nomina.step3');
    }

    /* ==========================
       STEP 3
    ========================== */

    public function step3()
    {
        $step1 = session('nomina.step1');
        $step2 = session('nomina.step2');
        $ingresos = session('nomina.step2_ingresos');

        if (!$step1 || !$step2) {
            return redirect()->route('nomina.step1');
        }

        $periodoActivo = \App\Models\PeriodoLiquidacion::getActivePeriod();

        $salarioBase = (float) ($step1['salario_base_proporcional'] ?? $step1['salario_base'] ?? 0);

        $idTipoContrato = null;
        if (isset($step1['id_contrato'])) {
            $contratoSession = DB::table('contrato')->where('id_contrato', $step1['id_contrato'])->first(['id_tipo_contrato']);
            if ($contratoSession) {
                $idTipoContrato = (int) $contratoSession->id_tipo_contrato;
            }
        }

        $contribuciones = $this->calculator
            ->calcularContribuciones($salarioBase, $idTipoContrato);

        // Calculate integrated benefits sum for summary display
        $integratedBenefitsTotal = 0;
        if (isset($step1['id_contrato']) && $periodoActivo) {
            $integratedBenefitsTotal = DB::table('benefit_ledger')
                ->where('contract_id', $step1['id_contrato'])
                ->where('payroll_period_id', $periodoActivo->id_periodo)
                ->where('movement_type', \App\Models\BenefitLedger::MOVEMENT_SCHEDULED)
                ->where('status', \App\Models\BenefitLedger::STATUS_PENDING_PAYROLL)
                ->get()
                ->sum(function ($bp) use ($salarioBase) {
                    $amount = abs($bp->amount);
                    $type = trim(strtolower($bp->benefit_type));
                    if ($type === 'vacaciones') {
                        // Use pro-rated base for vacations consistent with service logic for partial months
                        return ($salarioBase / 30) * $amount;
                    }
                    if ($type === 'cesantias') {
                        return 0; // Exclude Cesantias as per user request
                    }
                    return $amount;
                });
        }

        $totalDevengos =
            $salarioBase +
            (float) ($step2['horas_extra'] ?? 0) +
            (float) ($step2['recargos'] ?? 0) +
            (float) ($ingresos['bonificaciones'] ?? 0) +
            (float) ($ingresos['comisiones'] ?? 0) +
            (float) ($ingresos['otros_devengos'] ?? 0) +
            (float) ($ingresos['auxilio_transporte'] ?? 0) +
            (float) $integratedBenefitsTotal;

        $step3 = session('nomina.step3', []);
        $isEditing = (bool) session('nomina.editing_id');

        return view('nomina.step3', compact(
            'salarioBase',
            'step2',
            'ingresos',
            'contribuciones',
            'totalDevengos',
            'step3',
            'isEditing',
            'idTipoContrato'
        ));
    }

    /* ==========================
       GUARDAR NOMINA
    ========================== */

    public function store(Request $request)
    {
        $s1 = session('nomina.step1');
        $s2 = session('nomina.step2');
        $s3 = session('nomina.step2_ingresos');

        if (!$s1 || !$s2 || !$s3) {
            return redirect()->route('nomina.step1');
        }

        $request->validate([
            'retencion_fuente' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'embargo_fiscal' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'pension_voluntaria' => ['nullable', 'string', 'max:30', 'regex:/^\d[\d.,]*$/'],
            'confirm_edit' => 'nullable|string|in:editar',
        ], [
            'retencion_fuente.regex' => 'Retención en la fuente solo permite números.',
            'embargo_fiscal.regex' => 'Embargo fiscal solo permite números.',
            'pension_voluntaria.regex' => 'Pensión voluntaria solo permite números.',
        ]);

        $editingId = session('nomina.editing_id');

        if ($editingId && strtolower(trim((string) $request->input('confirm_edit', ''))) !== 'editar') {
            return back()->with('error', 'Debes confirmar la edición escribiendo "editar".');
        }

        $periodoId = isset($s1['id_periodo'])
            ? (int) $s1['id_periodo']
            : (int) optional(PeriodoLiquidacion::getActivePeriod())->id_periodo;

        if (!$periodoId) {
            return redirect()->route('nomina.index')
                ->with('error', 'No hay período activo para guardar la nómina.');
        }

        $retencionFuente = $this->parseMoneyInput($request->input('retencion_fuente'));
        $embargoFiscal = $this->parseMoneyInput($request->input('embargo_fiscal'));
        $pensionVoluntaria = $this->parseMoneyInput($request->input('pension_voluntaria'));

        try {
            $calculo = $this->calculator->calcularNominaEmpleado((int) $s1['id_contrato'], $periodoId, [
                'fecha_pago' => $s1['fecha_pago'],
                'dias_trabajados' => (int) ($s1['dias_trabajados'] ?? 30),
                'horas_extra' => $this->parseMoneyInput($s2['horas_extra'] ?? 0),
                'recargos' => $this->parseMoneyInput($s2['recargos'] ?? 0),
                'bonificaciones' => $this->parseMoneyInput($s3['bonificaciones'] ?? 0),
                'comisiones' => $this->parseMoneyInput($s3['comisiones'] ?? 0),
                'otros_devengos' => $this->parseMoneyInput($s3['otros_devengos'] ?? 0),
                'auxilio_transporte' => $this->parseMoneyInput($s3['auxilio_transporte'] ?? 0),
                'retencion_fuente' => $retencionFuente,
                'embargo_fiscal' => $embargoFiscal,
                'pension_voluntaria' => $pensionVoluntaria,
                'recalculate_transport_allowance' => (bool) ($s3['recalculate_transport_allowance'] ?? false),
                'aplica_auxilio_transporte' => (int) ($s3['aplica_auxilio_transporte'] ?? 0),
            ]);
        } catch (\App\Exceptions\EmpleadoIncapacitadoException $e) {
            return back()->with('error', $e->getMessage());
        }
 
         $payload = [
             'id_contrato' => $s1['id_contrato'],
             'id_periodo' => $periodoId,
             'fecha_pago' => $calculo['fecha_pago'],
             'horas_extra' => $calculo['horas_extra'],
             'valor_horas_extras_recargos' => $calculo['valor_horas_extras_recargos'],
             'auxilio_transporte' => $calculo['auxilio_transporte'],
             'bonificaciones' => $calculo['bonificaciones'],
             'comisiones' => $calculo['comisiones'],
             'otros_devengos' => $calculo['otros_devengos'],
             'eps' => $calculo['eps'],
             'afp' => $calculo['afp'],
             'arl' => $calculo['arl'],
             'seguridad_social' => $calculo['seguridad_social'],
             'aporte_fp' => $calculo['aporte_fp'],
             'retencion_fuente' => $calculo['retencion_fuente'],
             'embargo_fiscal' => $calculo['embargo_fiscal'],
             'pension_voluntaria' => $calculo['pension_voluntaria'],
             'caja_compensacion' => $calculo['caja_compensacion'],
             'dias_a_trabajar' => $calculo['dias_a_trabajar'],
             'total_devengado' => $calculo['total_devengado'],
             'total_deducciones' => $calculo['total_deducciones'],
             'neto_pagar' => $calculo['neto_pagar'],
             'prestaciones_sociales' => $calculo['prestaciones_sociales'] ?? 0,
             'updated_at' => now(),
         ];

        $empresaId = (int) session('empresa_id');

        if ($editingId) {
            $registroEditable = DB::table('salario as s')
                ->join('contrato as c', 'c.id_contrato', '=', 's.id_contrato')
                ->where('s.id_salario', (int) $editingId)
                ->where('c.id_empresa', $empresaId)
                ->select('s.id_salario')
                ->first();

            if (!$registroEditable) {
                return redirect()->route('nomina.index')
                    ->with('error', 'No tienes permisos para editar esta nómina o no existe.');
            }
        } else {
            $periodoActivo = \App\Models\PeriodoLiquidacion::find($periodoId);
            $salarioExistente = DB::table('salario as s')
                ->join('contrato as c', 'c.id_contrato', '=', 's.id_contrato')
                ->where('c.doc', $s1['doc'])
                ->where('s.id_periodo', (int) $periodoId)
                ->select('s.id_salario', 's.id_contrato')
                ->first();

            if ($salarioExistente) {
                $existente = (object) $salarioExistente;
                if ((int) $existente->id_contrato !== (int) $s1['id_contrato']) {
                    // Se detectó una renovación (cambio de contrato en el mismo periodo).
                    // Consolidamos editando el registro existente en lugar de crear uno nuevo.
                    $editingId = $existente->id_salario;

                    // Al consolidar manualmente, intentamos sumar los días del anterior si es posible
                    $contratoAnterior = \App\Models\Contrato::find($existente->id_contrato);
                    if ($contratoAnterior && isset($s1['dias_trabajados'])) {
                        $diasAnterior = $this->terminationService->calculateWorkedDaysInPeriod($contratoAnterior, $periodoActivo);
                        // El usuario ya ingresó unos días para el nuevo contrato en Step 1
                        $s1['dias_trabajados'] = min(30, (int)$diasAnterior + (int)$s1['dias_trabajados']);
                        
                        // Actualizar el payload con los nuevos días consolidados
                        $payload['dias_a_trabajar'] = $s1['dias_trabajados'];
                        // Nota: El cálculo de valores (eps, afp, etc) ya se hizo en Step 2 
                        // pero aquí los estamos "forzando" al guardar. 
                        // Lo ideal es que el sistema sume los días dándole feedback al usuario.
                    }
                } else {
                    return redirect()->route('nomina.index')
                        ->with('error', 'Ya existe una nómina registrada para este empleado en el periodo activo.');
                }
            }
        }

        DB::transaction(function () use ($editingId, $payload, $s2, &$idSalarioPersistido) {
            if ($editingId) {
                DB::table('salario')
                    ->where('id_salario', (int) $editingId)
                    ->update($payload);

                $idSalarioPersistido = (int) $editingId;
            } else {
                $payload['created_at'] = now();
                $idSalarioPersistido = (int) DB::table('salario')->insertGetId($payload, 'id_salario');
            }

            $this->persistirDetalleHorasRecargos($idSalarioPersistido, (array) ($s2['detalle_recargos'] ?? []));
        });

        session()->forget('nomina');

        return redirect()->route('nomina.index')
            ->with('success', $editingId ? 'Nómina actualizada correctamente' : 'Nómina guardada correctamente');
    }

    public function realizarNominaMasiva(Request $request)
    {
        $empresaId = (int) session('empresa_id');
        $periodoActivo = PeriodoLiquidacion::getActivePeriod();

        if (!$periodoActivo) {
            return redirect()->route('nomina.index')
                ->with('error', 'No hay periodo activo para realizar la nómina masiva.');
        }

        $fechaPago = $request->input('fecha_pago');
        if (!$fechaPago) {
            $fechaPago = now()->toDateString();
        }

        $contratos = DB::table('contrato')
            ->where('id_empresa', $empresaId)
            ->where(function ($query) {
                $query->where('activo', true)
                    ->orWhereIn('estado', [
                        \App\Models\Contrato::ESTADO_ACTIVO,
                        \App\Models\Contrato::ESTADO_POR_VENCER,
                        \App\Models\Contrato::ESTADO_PROGRAMADO,
                    ]);
            })
            ->pluck('id_contrato');

        if ($contratos->isEmpty()) {
            return redirect()->route('nomina.index')
                ->with('error', 'No se encontraron contratos activos para liquidar.');
        }

        $procesados = 0;
        $omitidos   = 0;
        DB::transaction(function () use ($contratos, $periodoActivo, $fechaPago, &$procesados, &$omitidos) {
            foreach ($contratos as $idContrato) {
                try {
                    $contratoObj = Contrato::find($idContrato);
                    $inputCalculo = ['fecha_pago' => $fechaPago];

                    if ($contratoObj) {
                        // Consolidación de Renovación: Evitar duplicados para el mismo empleado en el mismo periodo
                        $idPeriodo = (int) $periodoActivo->id_periodo;
                        $doc = $contratoObj->doc;

                        // Buscar si ya existe una liquidación para este empleado en este periodo (bajo cualquier contrato)
                        $salarioExistente = \App\Models\Salario::whereHas('contrato', function ($q) use ($doc) {
                                $q->where('doc', $doc);
                            })
                            ->where('id_periodo', $idPeriodo)
                            ->first();

                        if ($salarioExistente && $salarioExistente->id_contrato != $idContrato) {
                            // Se detectó una renovación en el mismo mes. 
                            // Consolidamos: Movemos el registro existente al nuevo contrato para que solo quede uno.
                            $salarioExistente->id_contrato = $idContrato;
                            $salarioExistente->save();
                            
                            // Calculamos la suma de días de ambos contratos en este periodo
                            $contratoAnterior = \App\Models\Contrato::find($salarioExistente->getOriginal('id_contrato'));
                            $diasAnterior = $contratoAnterior ? $this->terminationService->calculateWorkedDaysInPeriod($contratoAnterior, $periodoActivo) : 0;
                            $diasNuevo = $this->terminationService->calculateWorkedDaysInPeriod($contratoObj, $periodoActivo);
                            
                            $inputCalculo['dias_trabajados'] = min(30, $diasAnterior + $diasNuevo);
                        } else {
                            // SUGGEST DAYS based on active period normally
                            $inputCalculo['dias_trabajados'] = $this->terminationService->calculateWorkedDaysInPeriod($contratoObj, $periodoActivo);
                        }
                    }

                    // STEP 1: Create/update the salary record FIRST
                    $this->calculator->guardarNominaEmpleado((int) $idContrato, (int) $periodoActivo->id_periodo, $inputCalculo);

                    // STEP 2: Auto-schedule termination benefits if contract ends in this period.
                    // handleContractTermination() internally checks fecha_fin vs period dates,
                    // so it only triggers for contracts ACTUALLY ending in this period.
                    // It also recalculates the salary with the integrated benefits.
                    if ($contratoObj) {
                        $this->terminationService->handleContractTermination($contratoObj, $periodoActivo);
                    }

                    $procesados++;
                } catch (\App\Exceptions\EmpleadoIncapacitadoException $e) {
                    // Empleado con incapacidad activa: se omite sin romper el proceso masivo.
                    $omitidos++;
                }
            }
        });

        $mensaje = "Nómina masiva realizada correctamente para {$procesados} empleado(s).";
        if ($omitidos > 0) {
            $mensaje .= " {$omitidos} empleado(s) omitido(s) por tener incapacidad activa en este periodo.";
        }

        return redirect()->route('nomina.index')
            ->with('success', $mensaje);
    }

    private function obtenerResumenNovedadesPorContratoPeriodo(int $idContrato, int $idPeriodo): array
    {
        if ($idContrato <= 0 || $idPeriodo <= 0) {
            return [
                'horas_extra' => 0.0,
                'recargos' => 0.0,
                'bonificaciones' => 0.0,
                'otros_devengos' => 0.0,
                'deducciones' => 0.0,
            ];
        }

        $contrato = Contrato::query()->find($idContrato);
        $valorHora = $contrato ? max(0, (float) ($contrato->salario_base ?? 0) / 240) : 0.0;

        return $this->calculator->resumirNovedadesContratoPeriodo($idContrato, $idPeriodo, $valorHora);
    }

    private function clasificarNovedadDevengado(string $codigo, string $nombre): string
    {
        $texto = mb_strtoupper(trim($codigo . ' ' . $nombre), 'UTF-8');

        if (preg_match('/HORA\s*EXTRA|\bHED\b|\bHEN\b|\bHEX\b|\bHE\b/u', $texto)) {
            return 'horas_extra';
        }

        if (preg_match('/RECARGO|\bRN\b|\bRND\b|\bRDF\b|\bRFD\b/u', $texto)) {
            return 'recargos';
        }

        if (preg_match('/BONIF|BONO|INCENTIVO|PREMIO/u', $texto)) {
            return 'bonificaciones';
        }

        return 'otros_devengos';
    }

    private function parseMoneyInput($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $normalized = trim((string) $value);
        $normalized = preg_replace('/[^\d,.-]/', '', $normalized);

        if ($normalized === '' || $normalized === null) {
            return 0.0;
        }

        $hasComma = str_contains($normalized, ',');
        $hasDot = str_contains($normalized, '.');

        if ($hasComma && $hasDot) {
            if (strrpos($normalized, ',') > strrpos($normalized, '.')) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasDot && !$hasComma) {
            $dotCount = substr_count($normalized, '.');
            if ($dotCount > 1) {
                $normalized = str_replace('.', '', $normalized);
            } else {
                $parts = explode('.', $normalized);
                if (count($parts) === 2 && strlen($parts[1]) === 3 && strlen($parts[0]) >= 1) {
                    $normalized = str_replace('.', '', $normalized);
                }
            }
        } elseif ($hasComma && !$hasDot) {
            $commaCount = substr_count($normalized, ',');
            if ($commaCount > 1) {
                $normalized = str_replace(',', '', $normalized);
            } else {
                $parts = explode(',', $normalized);
                if (count($parts) === 2 && strlen($parts[1]) === 3 && strlen($parts[0]) >= 1) {
                    $normalized = str_replace(',', '', $normalized);
                } else {
                    $normalized = str_replace(',', '.', $normalized);
                }
            }
        }

        $number = (float) $normalized;
        return is_finite($number) && $number >= 0 ? $number : 0.0;
    }

    private function normalizeRecargoMultiplier(string $nombre, float $valorCrudo): float
    {
        $nombreNormalizado = mb_strtolower(trim($nombre), 'UTF-8');

        // Reglas legales solicitadas para nómina Colombia.
        if (str_contains($nombreNormalizado, 'hora extra diurna dominical') || str_contains($nombreNormalizado, 'hora extra diurna festiva')) {
            return 2.00;
        }

        if (str_contains($nombreNormalizado, 'hora extra nocturna dominical') || str_contains($nombreNormalizado, 'hora extra nocturna festiva')) {
            return 2.50;
        }

        if (str_contains($nombreNormalizado, 'hora extra nocturna')) {
            return 1.75;
        }

        if (str_contains($nombreNormalizado, 'hora extra diurna')) {
            return 1.25;
        }

        if (str_contains($nombreNormalizado, 'recargo nocturno')) {
            return 0.35;
        }

        if (str_contains($nombreNormalizado, 'recargo dominical') || str_contains($nombreNormalizado, 'recargo festivo')) {
            return 0.75;
        }

        // Fallback seguro por si hay tipos nuevos en BD.
        if ($valorCrudo <= 0) {
            return 0.0;
        }

        if (str_contains($nombreNormalizado, 'recargo')) {
            return $valorCrudo > 1 ? ($valorCrudo / 100) : $valorCrudo;
        }

        if (str_contains($nombreNormalizado, 'extra')) {
            return $valorCrudo > 1 ? (1 + ($valorCrudo / 100)) : $valorCrudo;
        }

        return $valorCrudo;
    }

    private function calculateStep2TotalsFromDetail(array $detalleRecargos, float $salarioBase): array
    {
        $tipos = DB::table('tipo_hora_recargo')
            ->select('id_tipo_hora_recargo', 'nombre', 'valor')
            ->get()
            ->keyBy('id_tipo_hora_recargo');

        $valorHora = $salarioBase > 0 ? ($salarioBase / 240) : 0;
        $totalHorasExtra = 0.0;
        $totalRecargos = 0.0;

        foreach ($detalleRecargos as $tipoId => $cantidadRaw) {
            $cantidad = (float) $cantidadRaw;
            if ($cantidad <= 0) {
                continue;
            }

            $tipo = $tipos->get((int) $tipoId);
            if (!$tipo) {
                continue;
            }

            $factor = $this->normalizeRecargoMultiplier((string) ($tipo->nombre ?? ''), (float) ($tipo->valor ?? 0));
            $valorTotal = $cantidad * ($valorHora * $factor);
            $nombre = mb_strtolower((string) ($tipo->nombre ?? ''), 'UTF-8');

            if (str_contains($nombre, 'extra')) {
                $totalHorasExtra += $valorTotal;
            } else {
                $totalRecargos += $valorTotal;
            }
        }

        return [
            'total_horas_extra' => $totalHorasExtra,
            'total_recargos' => $totalRecargos,
        ];
    }

    /* ==========================
       BUSCAR EMPLEADOS
    ========================== */

    public function buscarEmpleado($doc)
    {
        $empresaId = session('empresa_id');

        $empleado = $this->employeeService
            ->buscarEmpleado($doc, $empresaId);

        if ($empleado) {
            $periodoActivo = \App\Models\PeriodoLiquidacion::getActivePeriod();
            if ($periodoActivo) {
                $contratoObj = \App\Models\Contrato::find($empleado->id_contrato);
                if ($contratoObj) {
                    $empleado->dias_sugeridos = $this->terminationService->calculateWorkedDaysInPeriod($contratoObj, $periodoActivo);
                }
            }
        }

        return $empleado;
    }

    public function buscarEmpleados(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $empresaId = session('empresa_id');
        $excludeLiquidados = filter_var($request->query('exclude_liquidados', false), FILTER_VALIDATE_BOOLEAN);
        $periodoActivo = PeriodoLiquidacion::getActivePeriod();

        $query = DB::table('usuario')
            ->join('contrato', 'usuario.doc', '=', 'contrato.doc')
            ->where('contrato.id_empresa', $empresaId)
            ->select(
                'usuario.doc',
                DB::raw("TRIM(CONCAT(usuario.primer_nombre,' ',IFNULL(usuario.otros_nombres,''),' ',usuario.primer_apellido,' ',IFNULL(usuario.segundo_apellido,''))) as nombre"),
                'usuario.telefono',
                'contrato.salario_base',
                'contrato.id_contrato'
            );

        if ($excludeLiquidados && $periodoActivo) {
            $query->whereNotExists(function ($sub) use ($periodoActivo) {
                $sub->select(DB::raw(1))
                    ->from('salario')
                    ->whereColumn('salario.id_contrato', 'contrato.id_contrato')
                    ->where('salario.id_periodo', (int) $periodoActivo->id_periodo);
            });
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('usuario.doc', 'like', "%{$q}%")
                    ->orWhereRaw(
                        "LOWER(TRIM(CONCAT(usuario.primer_nombre,' ',IFNULL(usuario.otros_nombres,''),' ',usuario.primer_apellido,' ',IFNULL(usuario.segundo_apellido,'')))) LIKE ?",
                        ['%' . mb_strtolower($q, 'UTF-8') . '%']
                    );
            });
        }

        return $query
            ->orderByDesc('contrato.activo')
            ->orderByDesc('contrato.fecha_inicio')
            ->orderBy('usuario.primer_nombre')
            ->limit(30)
            ->get();
    }

    public function checkDuplicate(int $idContrato)
    {
        $periodoId = session('active_period_id');
        if (!$periodoId) {
            return response()->json(['duplicate' => false]);
        }

        $exists = DB::table('salario')
            ->where('id_contrato', $idContrato)
            ->where('id_periodo', $periodoId)
            ->exists();

        return response()->json(['duplicate' => $exists]);
    }

    /* ==========================
       EXPORTAR EXCEL
    ========================== */

    public function exportarExcel(Request $request)
    {
        $exportRequest = $request->duplicate(
            array_merge($request->query(), [
                'documento' => '',
                'periodo' => '',
            ]),
            array_merge($request->request->all(), [
                'documento' => '',
                'periodo' => '',
            ])
        );

        $salarios = $this->construirConsultaNomina($exportRequest)
            ->orderByDesc('fecha_pago')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'Documento',
            'Empleado',
            'Fecha pago',
            'Dias',
            'Pago por dias',
            'Salario inicial',
            'Novedades',
            'Devengado',
            'Deducciones',
            'Salario neto'
        ], null, 'A1');

        $row = 2;

        foreach ($salarios as $salario) {
            $contrato = $salario->contrato;
            $usuario = $contrato?->usuario;
            $diasTrabajados = max(0, min(30, (int) ($salario->dias_a_trabajar ?? 0)));
            $salarioBase = (float) ($contrato?->salario_base ?? 0);
            $valorDia = $salarioBase / 30;
            $pagoPorDias = $valorDia * $diasTrabajados;
            $novedades = (float) ($salario->total_novedades ?? 0);

            $sheet->setCellValue(
                'A' . $row,
                $usuario?->doc ?? ''
            );

            $sheet->setCellValue(
                'B' . $row,
                $usuario?->nombre_completo ?? ''
            );

            $sheet->setCellValue(
                'C' . $row,
                $salario->fecha_pago ? Carbon::parse($salario->fecha_pago)->format('Y-m-d') : ''
            );

            $sheet->setCellValue(
                'D' . $row,
                $diasTrabajados
            );

            $sheet->setCellValue(
                'E' . $row,
                $pagoPorDias
            );

            $sheet->setCellValue(
                'F' . $row,
                $salarioBase
            );

            $sheet->setCellValue(
                'G' . $row,
                $novedades
            );

            $sheet->setCellValue(
                'H' . $row,
                $salario->total_devengado - $salario->total_novedades_devengado
            );

            $sheet->setCellValue(
                'I' . $row,
                $salario->getRawOriginal('total_deducciones')
            );

            $sheet->setCellValue(
                'J' . $row,
                $salario->neto_pagar
            );

            $row++;
        }

        return $this->streamSpreadsheetDownload($spreadsheet, 'nomina.xlsx');
    }

    /* ==========================
       EXPORTAR PDF
    ========================== */

    public function exportarPdf(Request $request)
    {
        $periodoActivo = PeriodoLiquidacion::getActivePeriod();

        // El PDF siempre se exporta completo para el periodo activo, sin filtros de búsqueda/fecha.
        $exportRequest = $request->duplicate(
            array_merge($request->query(), [
                'documento' => '',
                'periodo' => '',
            ]),
            array_merge($request->request->all(), [
                'documento' => '',
                'periodo' => '',
            ])
        );

        $salarios = $this->construirConsultaNomina($exportRequest)
            ->orderByDesc('fecha_pago')
            ->get();

        $salarios = $this->adjuntarResumenNovedades($salarios);

        $busquedaLabel = 'Todos los empleados';
        $periodoLabel = 'Sin periodo activo';
        if ($periodoActivo) {
            $periodoLabel = Carbon::parse((string) $periodoActivo->fecha_inicio)->format('d/m/Y')
                . ' - ' .
                Carbon::parse((string) $periodoActivo->fecha_fin)->format('d/m/Y');
        }

        $pdf = Pdf::loadView('nomina.reporte-pdf', [
            'salarios' => $salarios,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'busqueda' => $busquedaLabel,
            'periodo' => $periodoLabel,
        ])->setPaper('a4', 'landscape');

        return $this->downloadPdfResponse($pdf, 'nomina.pdf');
    }

    public function exportarNominaExcel(Request $request)
    {
        return $this->exportarExcel($request);
    }

    public function exportarNominaPdf(Request $request)
    {
        return $this->exportarPdf($request);
    }

    private function adjuntarResumenNovedades($salarios)
    {
        return $salarios->map(function (Salario $salario) {
            $resumenNovedades = $this->obtenerResumenNovedadesPorContratoPeriodo(
                (int) $salario->id_contrato,
                (int) $salario->id_periodo
            );

            $totalDevNovedades =
                (float) ($resumenNovedades['horas_extra'] ?? 0)
                + (float) ($resumenNovedades['recargos'] ?? 0)
                + (float) ($resumenNovedades['bonificaciones'] ?? 0)
                + (float) ($resumenNovedades['otros_devengos'] ?? 0);

            $totalDedNovedades = (float) ($resumenNovedades['deducciones'] ?? 0);

            $salario->setAttribute('total_novedades', $totalDevNovedades - $totalDedNovedades);
            $salario->setAttribute('total_novedades_devengado', $totalDevNovedades);
            $salario->setAttribute('total_novedades_deduccion', $totalDedNovedades);

            return $salario;
        });
    }

    private function persistirDetalleHorasRecargos(int $idSalario, array $detalle): void
    {
        DB::table('hora_recargo_extra')->where('id_salario', $idSalario)->delete();

        if (empty($detalle)) {
            return;
        }

        $tipos = DB::table('tipo_hora_recargo')
            ->select('id_tipo_hora_recargo', 'nombre', 'valor')
            ->get()
            ->keyBy('id_tipo_hora_recargo');

        $registro = DB::table('salario as s')
            ->join('contrato as c', 'c.id_contrato', '=', 's.id_contrato')
            ->where('s.id_salario', $idSalario)
            ->first(['s.dias_a_trabajar', 'c.salario_base']);

        $salarioMensual = (float) ($registro->salario_base ?? 0);
        $dias = max(0, min(30, (int) ($registro->dias_a_trabajar ?? 30)));
        $salarioBaseProporcional = ($salarioMensual / 30) * $dias;
        $valorHora = $salarioBaseProporcional > 0 ? ($salarioBaseProporcional / 240) : 0;

        $rows = [];
        foreach ($detalle as $tipoId => $cantidadRaw) {
            $cantidad = (float) $cantidadRaw;
            if ($cantidad <= 0) {
                continue;
            }

            $tipo = $tipos->get((int) $tipoId);
            if (!$tipo) {
                continue;
            }

            $factor = $this->normalizeRecargoMultiplier((string) ($tipo->nombre ?? ''), (float) ($tipo->valor ?? 0));
            $pago = $cantidad * ($valorHora * $factor);

            $rows[] = [
                'id_tipo_hora_recargo' => (int) $tipoId,
                'id_salario' => $idSalario,
                'cantidad' => $cantidad,
                'pago' => $pago,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($rows)) {
            DB::table('hora_recargo_extra')->insert($rows);
        }
    }

    private function estimateDetalleRecargosFromStoredValues(float $valorHorasExtra, float $valorRecargos, float $salarioBaseProporcional): array
    {
        if ($salarioBaseProporcional <= 0) {
            return [];
        }

        $tipos = DB::table('tipo_hora_recargo')
            ->select('id_tipo_hora_recargo', 'nombre', 'valor')
            ->get();

        if ($tipos->isEmpty()) {
            return [];
        }

        $valorHora = $salarioBaseProporcional / 240;
        if ($valorHora <= 0) {
            return [];
        }

        $extraTipo = $tipos->first(function ($tipo) {
            $nombre = mb_strtolower((string) ($tipo->nombre ?? ''), 'UTF-8');
            return str_contains($nombre, 'hora extra diurna');
        }) ?? $tipos->first(function ($tipo) {
            $nombre = mb_strtolower((string) ($tipo->nombre ?? ''), 'UTF-8');
            return str_contains($nombre, 'extra');
        });

        $recargoTipo = $tipos->first(function ($tipo) {
            $nombre = mb_strtolower((string) ($tipo->nombre ?? ''), 'UTF-8');
            return str_contains($nombre, 'recargo nocturno');
        }) ?? $tipos->first(function ($tipo) {
            $nombre = mb_strtolower((string) ($tipo->nombre ?? ''), 'UTF-8');
            return str_contains($nombre, 'recargo');
        });

        $detalle = [];

        if ($extraTipo && $valorHorasExtra > 0) {
            $factorExtra = $this->normalizeRecargoMultiplier((string) ($extraTipo->nombre ?? ''), (float) ($extraTipo->valor ?? 0));
            if ($factorExtra > 0) {
                $cantidadExtra = (int) round($valorHorasExtra / ($valorHora * $factorExtra));
                $cantidadExtra = max(0, min(744, $cantidadExtra));
                if ($cantidadExtra > 0) {
                    $detalle[(int) $extraTipo->id_tipo_hora_recargo] = $cantidadExtra;
                }
            }
        }

        if ($recargoTipo && $valorRecargos > 0) {
            $factorRecargo = $this->normalizeRecargoMultiplier((string) ($recargoTipo->nombre ?? ''), (float) ($recargoTipo->valor ?? 0));
            if ($factorRecargo > 0) {
                $cantidadRecargo = (int) round($valorRecargos / ($valorHora * $factorRecargo));
                $cantidadRecargo = max(0, min(744, $cantidadRecargo));
                if ($cantidadRecargo > 0) {
                    $detalle[(int) $recargoTipo->id_tipo_hora_recargo] = $cantidadRecargo;
                }
            }
        }

        return $detalle;
    }

    /**
     * Calcula los días de suspensión (SLN) que caen dentro de un periodo de
     * liquidación para un contrato dado. Los rangos de fecha de la novedad se
     * recortan a los límites del periodo.
     */
    private function calcularDiasSlnPeriodo(int $idContrato, PeriodoLiquidacion $periodo): int
    {
        $pInicio = Carbon::parse($periodo->fecha_inicio);
        $pFin = Carbon::parse($periodo->fecha_fin);

        $novedadesSln = DB::table('novedad as n')
            ->join('salario as s', 's.id_salario', '=', 'n.id_salario')
            ->where('s.id_contrato', $idContrato)
            ->where('n.tipo_novedad_codigo', 'SLN')
            ->where(function ($q) {
                $q->where('n.estado', '!=', 'cerrada')
                  ->orWhereNull('n.estado');
            })
            ->whereNotNull('n.fecha_inicio')
            ->whereNotNull('n.fecha_fin')
            ->whereDate('n.fecha_inicio', '<=', $pFin->toDateString())
            ->whereDate('n.fecha_fin', '>=', $pInicio->toDateString())
            ->select('n.fecha_inicio', 'n.fecha_fin', 'n.dias')
            ->get();

        $totalDias = 0;

        foreach ($novedadesSln as $nov) {
            $slnInicio = Carbon::parse($nov->fecha_inicio);
            $slnFin = Carbon::parse($nov->fecha_fin);

            $efectivoInicio = $slnInicio->greaterThan($pInicio) ? $slnInicio : $pInicio;
            $efectivoFin = $slnFin->lessThan($pFin) ? $slnFin : $pFin;

            $totalDias += max(0, $efectivoInicio->diffInDays($efectivoFin) + 1);
        }

        return $totalDias;
    }
}