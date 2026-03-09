<?php

namespace App\Http\Controllers;

use App\Models\Salario;
use App\Models\PeriodoLiquidacion;
use App\Services\NominaCalculatorService;
use App\Services\NominaEmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NominaController extends Controller
{
    private NominaCalculatorService $calculator;
    private NominaEmployeeService $employeeService;

    public function __construct(
        NominaCalculatorService $calculator,
        NominaEmployeeService $employeeService
    ) {
        $this->calculator = $calculator;
        $this->employeeService = $employeeService;
    }

    /* ==========================
       PERIODO ACTIVO
    ========================== */

    private function getActivePeriod(): ?PeriodoLiquidacion
    {
        $periodoId = session('active_period_id');
        $empresaId = session('empresa_id');

        if ($periodoId) {
            return PeriodoLiquidacion::where('id_empresa', $empresaId)
                ->where('id_periodo', $periodoId)
                ->first();
        }

        return PeriodoLiquidacion::where('id_empresa', $empresaId)
            ->where('estado', PeriodoLiquidacion::ESTADO_ABIERTO)
            ->orderByDesc('fecha_inicio')
            ->first();
    }

    /* ==========================
       INDEX
    ========================== */

    public function index()
    {
        $empresaId = session('empresa_id');
        $periodoActivo = $this->getActivePeriod();

        $salarios = Salario::with('contrato.usuario')
            ->whereHas('contrato', function ($q) use ($empresaId) {
                $q->where('id_empresa', $empresaId);
            })
            ->when($periodoActivo, function ($q) use ($periodoActivo) {
                $q->where('id_periodo', $periodoActivo->id_periodo);
            })
            ->orderByDesc('fecha_pago')
            ->paginate(10);

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
        $periodoActivo = $this->getActivePeriod();
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

        session(['nomina.editing_id' => (int) $registro->id_salario]);
        session(['nomina.step1' => [
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
        ]]);
        session(['nomina.step2' => [
            'horas_extra' => $horasExtra,
            'recargos' => $recargos,
            'total_horas_extra' => $horasExtra,
            'total_recargos' => $recargos,
            'total_devengos_parcial' => $salarioBaseProporcional + $horasExtra + $recargos,
            'detalle_recargos' => $detalleHoras,
        ]]);
        session(['nomina.step2_ingresos' => [
            'bonificaciones' => (float) ($registro->bonificaciones ?? 0),
            'comisiones' => (float) ($registro->comisiones ?? 0),
            'otros_devengos' => (float) ($registro->otros_devengos ?? 0),
            'aplica_auxilio_transporte' => ((float) ($registro->auxilio_transporte ?? 0)) > 0 ? 1 : 0,
            'auxilio_transporte' => (float) ($registro->auxilio_transporte ?? 0),
        ]]);
        session(['nomina.step3' => [
            'retencion_fuente' => (float) ($registro->retencion_fuente ?? 0),
            'embargo_fiscal' => (float) ($registro->embargo_fiscal ?? 0),
            'pension_voluntaria' => (float) ($registro->pension_voluntaria ?? 0),
        ]]);

        return redirect()->route('nomina.step1');
    }

    public function postStep1(Request $request)
    {
        $data = $request->validate([
            'doc' => 'required|string',
            'id_contrato' => 'required|integer',
            'fecha_pago' => 'required|date',
            'dias_trabajados' => 'required|integer|min:0|max:30',
        ]);

        $empresaId = session('empresa_id');

        $empleado = $this->employeeService->buscarEmpleado(
            $data['doc'],
            $empresaId
        );

        if (!$empleado) {
            return back()->with('error', 'Empleado no encontrado.');
        }

        $diasTrabajados = max(0, min(30, (int) $data['dias_trabajados']));
        $salarioBaseMensual = (float) ($empleado->salario_base ?? 0);
        $valorDia = $salarioBaseMensual / 30;
        $salarioBaseProporcional = $valorDia * $diasTrabajados;

        $step1 = [
            'empleado_busqueda' => trim(($empleado->nombre ?? '') . ' - ' . ($empleado->doc ?? '')),
            'doc' => $empleado->doc,
            'nombre' => $empleado->nombre,
            'telefono' => $empleado->telefono,
            'id_contrato' => $empleado->id_contrato,
            'salario_base' => $salarioBaseMensual,
            'valor_dia' => $valorDia,
            'salario_base_proporcional' => $salarioBaseProporcional,
            'fecha_pago' => $data['fecha_pago'],
            'dias_trabajados' => $diasTrabajados,
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
    $periodoActivo = $this->getActivePeriod();

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
            'horas_extra' => 'nullable|string|max:30',
            'recargos' => 'nullable|string|max:30',
            'detalle_recargos' => 'nullable|array',
            'detalle_recargos.*' => 'nullable|numeric|min:0|max:744',
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

        // Fallback para compatibilidad si no llega detalle de horas.
        $horasExtra = $totalHorasExtra > 0 ? $totalHorasExtra : $this->parseMoneyInput($data['horas_extra'] ?? 0);
        $recargos = $totalRecargos > 0 ? $totalRecargos : $this->parseMoneyInput($data['recargos'] ?? 0);

        if ($horasExtra < 0 || $recargos < 0 || $horasExtra > 999999999999 || $recargos > 999999999999) {
            return back()->withErrors([
                'horas_extra' => 'Los valores de horas extra y recargos no son válidos.',
            ])->withInput();
        }

        session(['nomina.step2' => [
            'horas_extra' => $horasExtra,
            'recargos' => $recargos,
            // Claves adicionales para compatibilidad con vistas actuales.
            'total_horas_extra' => $horasExtra,
            'total_recargos' => $recargos,
            'total_devengos_parcial' => $salarioBasePaso1 + $horasExtra + $recargos,
            'detalle_recargos' => $detalleRecargos,
        ]]);

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

        if (!$s1 || !$s2) {
            return redirect()->route('nomina.step2')
                ->with('error', 'Completa primero el paso 2 de horas y recargos.');
        }

        $salarioMensual = (float) ($s1['salario_base'] ?? 0);
        $diasTrabajados = max(0, min(30, (int) ($s1['dias_trabajados'] ?? 30)));
        $salarioDiario = $salarioMensual / 30;
        $salarioDevengado = $salarioDiario * $diasTrabajados;
        $salarioBase = $salarioDevengado;
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
        }

        $empresaId = session('empresa_id');
        $periodoActivo = $this->getActivePeriod();

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

        return view('nomina.step2_ingresos', compact(
            'salarioBase',
            's2',
            'step2Ingresos',
            'salarios',
            'periodoActivo',
            'auxilioTransporteDb',
            'aplicaPorTope',
            'step2AutoRecalculated'
        ));
    }

    public function postStep2Ingresos(Request $request)
    {
        $data = $request->validate([
            'bonificaciones' => 'nullable|string|max:30',
            'comisiones' => 'nullable|string|max:30',
            'otros_devengos' => 'nullable|string|max:30',
            'aplica_auxilio_transporte' => 'required|in:0,1',
            'auxilio_transporte' => 'nullable|string|max:30',
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

        $s1 = session('nomina.step1', []);
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
        }

        $aplicaAuxilio = (int) ($data['aplica_auxilio_transporte'] ?? 0) === 1;
        $auxilioTransporte = $aplicaAuxilio ? $auxilioTransporteDb : 0;

        session(['nomina.step2_ingresos' => [
            'bonificaciones' => $bonificaciones,
            'comisiones' => $comisiones,
            'otros_devengos' => $otrosDevengos,
            'aplica_auxilio_transporte' => $aplicaAuxilio ? 1 : 0,
            'auxilio_transporte' => $auxilioTransporte,
        ]]);

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

        $salarioBase = (float) ($step1['salario_base_proporcional'] ?? $step1['salario_base'] ?? 0);

        $contribuciones = $this->calculator
            ->calcularContribuciones($salarioBase);

        $totalDevengos =
            $salarioBase +
            (float) ($step2['horas_extra'] ?? 0) +
            (float) ($step2['recargos'] ?? 0) +
            (float) ($ingresos['bonificaciones'] ?? 0) +
            (float) ($ingresos['comisiones'] ?? 0) +
            (float) ($ingresos['otros_devengos'] ?? 0) +
            (float) ($ingresos['auxilio_transporte'] ?? 0);

        $step3 = session('nomina.step3', []);
        $isEditing = (bool) session('nomina.editing_id');

        return view('nomina.step3', compact(
            'salarioBase',
            'step2',
            'ingresos',
            'contribuciones',
            'totalDevengos',
            'step3',
            'isEditing'
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
            'retencion_fuente' => 'nullable|string|max:30',
            'embargo_fiscal' => 'nullable|string|max:30',
            'pension_voluntaria' => 'nullable|string|max:30',
            'confirm_edit' => 'nullable|string|in:editar',
        ]);

        $editingId = session('nomina.editing_id');

        if ($editingId && strtolower(trim((string) $request->input('confirm_edit', ''))) !== 'editar') {
            return back()->with('error', 'Debes confirmar la edición escribiendo "editar".');
        }

        $salarioMensual = (float) ($s1['salario_base'] ?? 0);
        $diasTrabajados = max(0, min(30, (int) ($s1['dias_trabajados'] ?? 30)));
        $salarioDiario = $salarioMensual / 30;
        $salarioDevengado = $salarioDiario * $diasTrabajados;
        $horasExtra = $this->parseMoneyInput($s2['horas_extra'] ?? 0);
        $recargos = $this->parseMoneyInput($s2['recargos'] ?? 0);
        $bonificaciones = $this->parseMoneyInput($s3['bonificaciones'] ?? 0);
        $comisiones = $this->parseMoneyInput($s3['comisiones'] ?? 0);
        $otrosDevengos = $this->parseMoneyInput($s3['otros_devengos'] ?? 0);
        $auxilioTransporte = $this->parseMoneyInput($s3['auxilio_transporte'] ?? 0);

        $devengos = $this->calculator->calcularDevengos(
            $salarioDevengado,
            $horasExtra,
            $bonificaciones,
            $comisiones
        );

        $totalDevengos = $devengos
            + $recargos
            + $otrosDevengos
            + $auxilioTransporte;

        $retencionFuente = $this->parseMoneyInput($request->input('retencion_fuente'));
        $embargoFiscal = $this->parseMoneyInput($request->input('embargo_fiscal'));
        $pensionVoluntaria = $this->parseMoneyInput($request->input('pension_voluntaria'));

        // Normativa COL: deducciones del empleado sobre devengado.
        $eps = $totalDevengos * 0.04;
        $afp = $totalDevengos * 0.04;
        $seguridadSocial = $eps + $afp;

        // Aportes del empleador (no deducen salario).
        $params = app(\App\Services\NominaParameterService::class)->get();
        $arlRate = (float) ($params->arl_riesgo_1 ?? 0);
        $arl = $totalDevengos * $arlRate;

        $deducciones =
            $seguridadSocial
            + $retencionFuente
            + $embargoFiscal
            + $pensionVoluntaria;

        $neto = $this->calculator->calcularNeto($totalDevengos, $deducciones);

        $periodoId = isset($s1['id_periodo'])
            ? (int) $s1['id_periodo']
            : (int) optional($this->getActivePeriod())->id_periodo;

        if (!$periodoId) {
            return redirect()->route('nomina.index')
                ->with('error', 'No hay período activo para guardar la nómina.');
        }

        $payload = [
            'id_contrato' => $s1['id_contrato'],
            'id_periodo' => $periodoId,
            'fecha_pago' => $s1['fecha_pago'],
            'horas_extra' => $horasExtra,
            'valor_horas_extras_recargos' => $horasExtra + $recargos,
            'auxilio_transporte' => $auxilioTransporte,
            'bonificaciones' => $bonificaciones,
            'comisiones' => $comisiones,
            'otros_devengos' => $otrosDevengos,
            'eps' => $eps,
            'afp' => $afp,
            'arl' => $arl,
            'seguridad_social' => $seguridadSocial,
            'aporte_fp' => 0,
            'retencion_fuente' => $retencionFuente,
            'embargo_fiscal' => $embargoFiscal,
            'pension_voluntaria' => $pensionVoluntaria,
            'caja_compensacion' => 0,
            'dias_a_trabajar' => $diasTrabajados,
            'total_devengado' => $totalDevengos,
            'total_deducciones' => $deducciones,
            'neto_pagar' => $neto,
            'updated_at' => now(),
        ];

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

        session()->forget('nomina');

        return redirect()->route('nomina.index')
            ->with('success', $editingId ? 'Nómina actualizada correctamente' : 'Nómina guardada correctamente');
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

        return $this->employeeService
            ->buscarEmpleado($doc, $empresaId);
    }

    public function buscarEmpleados(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $empresaId = session('empresa_id');

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

    public function exportarExcel()
    {
        $salarios = Salario::with('contrato.usuario')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'Documento',
            'Empleado',
            'Devengado',
            'Deducciones',
            'Neto'
        ], null, 'A1');

        $row = 2;

        foreach ($salarios as $salario) {

            $sheet->setCellValue('A'.$row,
                $salario->contrato->usuario->doc);

            $sheet->setCellValue('B'.$row,
                $salario->contrato->usuario->nombre_completo);

            $sheet->setCellValue('C'.$row,
                $salario->total_devengado);

            $sheet->setCellValue('D'.$row,
                $salario->total_deducciones);

            $sheet->setCellValue('E'.$row,
                $salario->neto_pagar);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'nomina.xlsx');
    }

    /* ==========================
       EXPORTAR PDF
    ========================== */

    public function exportarPdf()
    {
        $salarios = Salario::with('contrato.usuario')->get();

        $pdf = Pdf::loadView('nomina.reporte-pdf', [
            'salarios' => $salarios
        ]);

        return $pdf->download('nomina.pdf');
    }

    public function exportarNominaExcel()
    {
        return $this->exportarExcel();
    }

    public function exportarNominaPdf()
    {
        return $this->exportarPdf();
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
}