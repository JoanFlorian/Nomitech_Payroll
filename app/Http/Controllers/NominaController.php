<?php

namespace App\Http\Controllers;

use App\Models\PeriodoLiquidacion;
use App\Models\Salario;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NominaController extends Controller
{
    private const VALIDATION_MESSAGES = [
        '*.numeric' => 'Este campo debe ser numérico.',
        '*.integer' => 'Este campo debe ser un número entero.',
        '*.min' => 'Este campo no puede ser negativo.',
        '*.max' => 'El valor excede el máximo permitido.',
    ];

    private const STEP2_MAX_HOURS = 744;
    private const STEP2_INGRESOS_MAX = 999999999;

    private const STEP2_RATES = [
        'horas_extra_diurnas' => 1.25,
        'horas_extra_nocturnas' => 1.75,
        'horas_extra_dominicales_diurnas' => 2.0,
        'horas_extra_dominicales_nocturnas' => 2.5,
        'recargo_nocturno' => 0.35,
        'recargo_dominical_diurno' => 0.75,
        'recargo_dominical_nocturno' => 1.10,
        'recargo_festivo_diurno' => 0.75,
        'recargo_festivo_nocturno' => 1.10,
    ];

    private const STEP2_EXTRA_KEYS = [
        'horas_extra_diurnas',
        'horas_extra_nocturnas',
        'horas_extra_dominicales_diurnas',
        'horas_extra_dominicales_nocturnas',
    ];

    private const STEP2_RECARGO_KEYS = [
        'recargo_nocturno',
        'recargo_dominical_diurno',
        'recargo_dominical_nocturno',
        'recargo_festivo_diurno',
        'recargo_festivo_nocturno',
    ];

    private function getContractContributionRules($idContrato): array
    {
        $defaultRates = [
            'eps' => (float) config('nomina.rates.eps', 0.04),
            'afp' => (float) config('nomina.rates.afp', 0.04),
            'arl' => (float) config('nomina.rates.arl', 0.00522),
            'aporte_fp' => (float) config('nomina.rates.aporte_fp', 0.01),
        ];

        $rules = [
            'slug' => 'default',
            'nombre' => 'default',
            'aplica_seguridad_social' => true,
            'rates' => $defaultRates,
            'aporte_fp_smmlv_threshold' => (float) config('nomina.aporte_fp_smmlv_threshold', 4),
        ];

        $contrato = DB::table('contrato as c')
            ->leftJoin('tipo_contrato as tc', 'tc.id_tipo_contrato', '=', 'c.id_tipo_contrato')
            ->where('c.id_contrato', $idContrato)
            ->select('tc.nombre as tipo_nombre', 'tc.seguridad_social as tipo_seguridad_social')
            ->first();

        if (!$contrato) {
            return $rules;
        }

        $tipoNombre = (string) ($contrato->tipo_nombre ?? 'default');
        $tipoSlug = Str::slug(Str::ascii($tipoNombre), '_') ?: 'default';
        $tipoConfig = (array) config("nomina.contract_types.$tipoSlug", []);

        $aplicaCatalogo = is_null($contrato->tipo_seguridad_social) ? true : (bool) $contrato->tipo_seguridad_social;
        $aplicaConfig = (bool) ($tipoConfig['aplica_seguridad_social'] ?? true);

        $rules['slug'] = $tipoSlug;
        $rules['nombre'] = $tipoNombre;
        $rules['aplica_seguridad_social'] = $aplicaCatalogo && $aplicaConfig;
        $rules['rates'] = array_merge($defaultRates, (array) ($tipoConfig['rates'] ?? []));
        $rules['aporte_fp_smmlv_threshold'] = (float) ($tipoConfig['aporte_fp_smmlv_threshold'] ?? $rules['aporte_fp_smmlv_threshold']);

        return $rules;
    }

    private function calculateContributions(float $salarioBase, array $rules): array
    {
        if (!($rules['aplica_seguridad_social'] ?? true)) {
            return [
                'eps' => 0.0,
                'afp' => 0.0,
                'arl' => 0.0,
                'seguridad_social' => 0.0,
                'aporte_fp' => 0.0,
            ];
        }

        $smmlv = (float) config('nomina.smmlv', 1423500);
        $eps = $salarioBase * (float) ($rules['rates']['eps'] ?? 0);
        $afp = $salarioBase * (float) ($rules['rates']['afp'] ?? 0);
        $arl = $salarioBase * (float) ($rules['rates']['arl'] ?? 0);
        $seguridadSocial = $eps + $afp;
        $fpThreshold = (float) ($rules['aporte_fp_smmlv_threshold'] ?? 4);
        $fpRate = (float) ($rules['rates']['aporte_fp'] ?? 0);
        $aporteFp = $salarioBase >= ($smmlv * $fpThreshold) ? ($salarioBase * $fpRate) : 0;

        return [
            'eps' => $eps,
            'afp' => $afp,
            'arl' => $arl,
            'seguridad_social' => $seguridadSocial,
            'aporte_fp' => $aporteFp,
        ];
    }

    private function getActivePeriod(): ?PeriodoLiquidacion
    {
        $activePeriodId = session('active_period_id');
        $empresaId = session('empresa_id');

        if ($activePeriodId) {
            $periodo = PeriodoLiquidacion::where('id_empresa', $empresaId)
                ->where('id_periodo', $activePeriodId)
                ->first();
            if ($periodo)
                return $periodo;
        }

        // Fallback: Último abierto
        $periodo = PeriodoLiquidacion::where('id_empresa', $empresaId)
            ->where('estado', PeriodoLiquidacion::ESTADO_ABIERTO)
            ->orderByDesc('fecha_inicio')
            ->first();

        if ($periodo) {
            session(['active_period_id' => $periodo->id_periodo]);
        }

        return $periodo;
    }


    private function parseNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        // Keep native numeric types fast; strings are normalized below to support locale formats.
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^\d,.-]/', '', (string) $value);

        $hasComma = str_contains($normalized, ',');
        $hasDot = str_contains($normalized, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($normalized, ',');
            $lastDot = strrpos($normalized, '.');

            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
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
                if (
                    count($parts) === 2 &&
                    strlen($parts[1]) === 3 &&
                    strlen($parts[0]) >= 1
                ) {
                    $normalized = str_replace('.', '', $normalized);
                }
            }
        } elseif ($hasComma && !$hasDot) {
            $commaCount = substr_count($normalized, ',');

            if ($commaCount > 1) {
                $normalized = str_replace(',', '', $normalized);
            } else {
                $parts = explode(',', $normalized);
                if (
                    count($parts) === 2 &&
                    strlen($parts[1]) === 3 &&
                    strlen($parts[0]) >= 1
                ) {
                    $normalized = str_replace(',', '', $normalized);
                } else {
                    $normalized = str_replace(',', '.', $normalized);
                }
            }
        }

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }

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

        $periodoActivo = $this->getActivePeriod();

        $query = Salario::with('contrato.usuario')
            ->select('salario.*')
            ->addSelect([
                'total_novedades' => DB::table('novedad')
                    ->selectRaw('COALESCE(SUM(pago), 0)')
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
        $periodoActivo = $this->getActivePeriod();
        $salarios = $this->construirConsultaNomina($request)
            ->orderByDesc('fecha_pago')
            ->paginate(10)
            ->withQueryString();

        return view('nomina.index', compact('salarios', 'periodoActivo'));
    }

    public function exportarNominaExcel(Request $request)
    {
        $salarios = $this->construirConsultaNomina($request)
            ->orderByDesc('fecha_pago')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nómina');

        $encabezados = [
            'Documento',
            'Empleado',
            'Fecha Pago',
            'Salario Inicial',
            'Devengos',
            'Deducciones',
            'Salario Neto',
        ];

        $sheet->fromArray($encabezados, null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $fila = 2;
        foreach ($salarios as $salario) {
            $fechaPago = $salario->fecha_pago ? Carbon::parse($salario->fecha_pago)->format('Y-m-d') : '';
            $contrato = $salario->contrato;
            $usuario = $contrato?->usuario;

            $sheet->setCellValue('A' . $fila, $usuario?->doc ?? '');
            $sheet->setCellValue('B' . $fila, $usuario?->nombre_completo ?? '');
            $sheet->setCellValue('C' . $fila, $fechaPago);
            $sheet->setCellValue('D' . $fila, (float) ($contrato?->salario_base ?? 0));
            $sheet->setCellValue('E' . $fila, (float) $salario->total_devengos);
            $sheet->setCellValue('F' . $fila, (float) $salario->total_deducciones);
            $sheet->setCellValue('G' . $fila, (float) $salario->salario_neto);
            $fila++;
        }

        foreach (range('A', 'G') as $columna) {
            $sheet->getColumnDimension($columna)->setAutoSize(true);
        }

        if ($fila > 2) {
            $sheet->getStyle('D2:G' . ($fila - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'nomina-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportarNominaPdf(Request $request)
    {
        $salarios = $this->construirConsultaNomina($request)
            ->orderByDesc('fecha_pago')
            ->get();

        $pdf = Pdf::loadView('nomina.reporte-pdf', [
            'salarios' => $salarios,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'busqueda' => $request->query('documento', 'Sin filtro'),
            'periodo' => $request->query('periodo', 'Sin filtro'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reporte-nomina-' . now()->format('Ymd_His') . '.pdf');
    }

    /* ==========================
       STEP 1
    ========================== */
    public function step1(Request $request)
    {
        if ($request->boolean('fresh')) {
            session()->forget('nomina');
            session(['nomina.step' => 1]);
            $step1 = [];
        } else {
            session(['nomina.step' => 1]);
            $step1 = session('nomina.step1', []);
        }

        $isEditing = (bool) (session('nomina.editing_id') || $request->boolean('editing'));

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

        return view('nomina.step1', compact('step1', 'isEditing', 'salarios', 'periodoActivo'));
    }

    public function postStep1(Request $request)
    {
        $isEditing = (bool) session('nomina.editing_id');

        $validated = $request->validate([
            'empleado_busqueda' => 'required|string|max:120',
            'doc' => 'required|string|max:50',
            'id_contrato' => 'required|integer|min:1',
            'fecha_pago' => 'required|date',
        ], [
            'empleado_busqueda.required' => 'Debes seleccionar un empleado válido de la lista.',
            'doc.required' => 'Debes seleccionar un empleado válido de la lista.',
            'id_contrato.required' => 'Debes seleccionar un empleado válido de la lista.',
            'id_contrato.integer' => 'Debes seleccionar un empleado válido de la lista.',
        ]);

        $empleado = $this->resolveNominaEmployeeContract(
            (string) $validated['doc'],
            (int) $validated['id_contrato'],
            !$isEditing
        );

        if (!$empleado) {
            return back()
                ->withInput()
                ->with('error', 'Debes seleccionar un empleado válido de la lista.');
        }

        $data = $request->all();
        $data['doc'] = (string) $empleado->doc;
        $data['id_contrato'] = (int) $empleado->id_contrato;
        $data['nombre'] = (string) ($empleado->nombre ?? '');
        $data['telefono'] = (string) ($empleado->telefono ?? '');
        $data['salario_base'] = $this->parseNumber($empleado->salario_base ?? 0);
        $data['empleado_busqueda'] = trim($data['nombre'] . ' - ' . $data['doc']);
        $data['fecha_pago'] = (string) $validated['fecha_pago'];

        // Phase 2: Use explicit period from session
        $periodoId = session('active_period_id');

        if (!$periodoId) {
            return redirect()->route('periodos.index')->with('error', 'Debe seleccionar un periodo antes de liquidar.');
        }

        $periodo = DB::table('periodo_liquidacion')
            ->where('id_periodo', $periodoId)
            ->select('estado')
            ->first();

        if ($periodo && $periodo->estado === \App\Models\PeriodoLiquidacion::ESTADO_CERRADO) {
            abort(403, 'El periodo seleccionado se encuentra cerrado y no permite nuevas liquidaciones o novedades.');
        }

        session(['nomina.step1' => $data]);
        return redirect()->route('nomina.step2');
    }

    public function edit(int $idSalario)
    {
        $registro = DB::table('salario as s')
            ->join('contrato as c', 'c.id_contrato', '=', 's.id_contrato')
            ->join('usuario as u', 'u.doc', '=', 'c.doc')
            ->join('periodo_liquidacion as p', 'p.id_periodo', '=', 's.id_periodo')
            ->where('s.id_salario', $idSalario)
            ->select(
                's.id_salario',
                's.id_contrato',
                's.fecha_pago',
                'p.estado as periodo_estado',
                's.valor_horas_extras_recargos',
                's.bonificaciones',
                's.comisiones',
                's.otros_devengos',
                's.retencion_fuente',
                's.embargo_fiscal',
                's.pension_voluntaria',
                'u.doc',
                DB::raw("TRIM(CONCAT(u.primer_nombre,' ',IFNULL(u.otros_nombres,''),' ',u.primer_apellido,' ',IFNULL(u.segundo_apellido,''))) as nombre"),
                'u.telefono',
                'c.salario_base'
            )
            ->first();

        if (!$registro) {
            return redirect()->route('nomina.index')->with('error', 'No se encontró el registro de nómina a editar.');
        }

        if ($registro->periodo_estado === \App\Models\PeriodoLiquidacion::ESTADO_CERRADO) {
            abort(403, 'No se puede editar una nómina de un periodo cerrado.');
        }

        session()->forget('nomina');

        session($this->buildEditingSessionPayload($registro));

        return redirect()->route('nomina.step1', ['editing' => 1])->with('success', 'Modo edición activado.');
    }

    /* ==========================
       STEP 2
    ========================== */
    public function step2()
    {
        session(['nomina.step' => 2]);

        $s1 = session('nomina.step1');
        if (!$s1) {
            return redirect()->route('nomina.step1')->with('error', 'Completa el paso 1 para continuar.');
        }

        $salarioBase = $this->parseNumber($s1['salario_base'] ?? 0);
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

        $step2Rates = self::STEP2_RATES;

        return view('nomina.step2', compact('salarioBase', 'step2', 'salarios', 'periodoActivo', 'step2Rates'));
    }

    public function postStep2(Request $request)
    {
        $request->merge(
            collect(array_keys(self::STEP2_RATES))
                ->mapWithKeys(fn(string $key) => [$key => $this->parseNumber($request->input($key))])
                ->all()
        );

        $step2Rules = collect(array_keys(self::STEP2_RATES))
            ->mapWithKeys(fn(string $key) => [$key => 'nullable|integer|min:0|max:' . self::STEP2_MAX_HOURS])
            ->all();

        $validated = $request->validate($step2Rules, self::VALIDATION_MESSAGES);

        $totalHorasMes = collect(array_keys(self::STEP2_RATES))
            ->sum(fn(string $key) => (int) ($validated[$key] ?? 0));

        if ($totalHorasMes > self::STEP2_MAX_HOURS) {
            return back()
                ->withErrors([
                    'total_horas_mes' => 'La suma total de horas no puede superar 744 horas en un mes.',
                ])
                ->withInput();
        }

        $s1 = session('nomina.step1');
        if (!$s1) {
            return redirect()->route('nomina.step1')->with('error', 'Completa el paso 1 para continuar.');
        }

        $salarioBase = $this->parseNumber($s1['salario_base'] ?? 0);
        session(['nomina.step2' => $this->buildStep2SessionData($salarioBase, $validated)]);

        return redirect()->route('nomina.step2.ingresos');
    }

    public function step2Ingresos()
    {
        session(['nomina.step' => 2]);

        $s1 = session('nomina.step1');
        $s2 = session('nomina.step2');
        if (!$s1 || !$s2) {
            return redirect()->route('nomina.step2')->with('error', 'Completa primero las horas y recargos.');
        }

        $salarioBase = $this->parseNumber($s1['salario_base'] ?? 0);
        $s2 = session('nomina.step2');
        $step2Ingresos = session('nomina.step2_ingresos', []);

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

        return view('nomina.step2_ingresos', compact('salarioBase', 's2', 'step2Ingresos', 'salarios', 'periodoActivo'));
    }

    public function postStep2Ingresos(Request $request)
    {
        $request->merge([
            'bonificaciones' => $this->parseNumber($request->input('bonificaciones')),
            'comisiones' => $this->parseNumber($request->input('comisiones')),
            'otros_devengos' => $this->parseNumber($request->input('otros_devengos')),
        ]);

        $validated = $request->validate([
            'bonificaciones' => 'nullable|numeric|min:0|max:' . self::STEP2_INGRESOS_MAX,
            'comisiones' => 'nullable|numeric|min:0|max:' . self::STEP2_INGRESOS_MAX,
            'otros_devengos' => 'nullable|numeric|min:0|max:' . self::STEP2_INGRESOS_MAX,
        ], self::VALIDATION_MESSAGES);

        $s1 = session('nomina.step1');
        $s2 = session('nomina.step2');
        if (!$s1 || !$s2) {
            return redirect()->route('nomina.step2')->with('error', 'Completa primero las horas y recargos.');
        }

        $bonificaciones = $this->parseNumber($validated['bonificaciones'] ?? 0);
        $comisiones = $this->parseNumber($validated['comisiones'] ?? 0);
        $otrosDevengos = $this->parseNumber($validated['otros_devengos'] ?? 0);

        $devengosParcial = $this->parseNumber(
            $s2['total_devengos_parcial']
            ?? (
                $this->parseNumber($s1['salario_base'] ?? 0) +
                $this->parseNumber($s2['total_horas_extra'] ?? 0) +
                $this->parseNumber($s2['total_recargos'] ?? 0)
            )
        );

        $totalDevengosFinal = $devengosParcial + $bonificaciones + $comisiones + $otrosDevengos;

        session([
            'nomina.step2_ingresos' => [
                'bonificaciones' => $bonificaciones,
                'comisiones' => $comisiones,
                'otros_devengos' => $otrosDevengos,
                'total_devengos_final' => $totalDevengosFinal,
            ]
        ]);

        return redirect()->route('nomina.step3');
    }

    /* ==========================
       STEP 3
    ========================== */
    public function step3()
    {
        session(['nomina.step' => 3]);

        $s1 = session('nomina.step1');
        $s2 = session('nomina.step2');
        $s2Ingresos = session('nomina.step2_ingresos');
        if (!$s1 || !$s2 || !$s2Ingresos) {
            return redirect()->route('nomina.step1')->with('error', 'Completa los pasos anteriores para continuar.');
        }

        $salarioBase = $this->parseNumber($s1['salario_base'] ?? 0);
        $totalDevengos = $this->parseNumber($s2Ingresos['total_devengos_final'] ?? 0);
        $rules = $this->getContractContributionRules($s1['id_contrato'] ?? null);
        $step3 = session('nomina.step3', []);
        $isEditing = (bool) session('nomina.editing_id');

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

        return view('nomina.step3', compact('salarioBase', 'totalDevengos', 'rules', 'step3', 'isEditing', 'salarios', 'periodoActivo'));
    }

    /* ==========================
       BUSCAR EMPLEADO
    ========================== */
    public function buscarEmpleado($doc)
    {
        $empresaId = session('empresa_id');
        return DB::table('usuario')
            ->join('contrato', 'usuario.doc', '=', 'contrato.doc')
            ->where('usuario.doc', $doc)
            ->where('contrato.id_empresa', $empresaId)
            ->where(function ($q) {
                $q->where('contrato.estado_laboral', \App\Models\Contrato::ESTADO_LABORAL_ACTIVO)
                    ->orWhere('contrato.estado_nomina', \App\Models\Contrato::ESTADO_NOMINA_PENDIENTE);
            })
            ->select(
                'usuario.doc',
                DB::raw("CONCAT(
                    usuario.primer_nombre,' ',
                    IFNULL(usuario.otros_nombres,''),' ',
                    usuario.primer_apellido,' ',
                    IFNULL(usuario.segundo_apellido,'')
                ) as nombre"),
                'usuario.telefono',
                'contrato.salario_base',
                'contrato.id_contrato'
            )
            ->first();
    }

    public function buscarEmpleados(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $qDoc = preg_replace('/\D+/', '', $q);
        $empresaId = session('empresa_id');

        $nombreExpr = "TRIM(CONCAT(
            usuario.primer_nombre,' ',
            IFNULL(usuario.otros_nombres,''),' ',
            usuario.primer_apellido,' ',
            IFNULL(usuario.segundo_apellido,'')
        ))";

        $query = DB::table('usuario')
            ->join('contrato', 'usuario.doc', '=', 'contrato.doc')
            ->where('contrato.id_empresa', $empresaId)
            ->where(function ($q) {
                $q->where('contrato.estado_laboral', \App\Models\Contrato::ESTADO_LABORAL_ACTIVO)
                    ->orWhere('contrato.estado_nomina', \App\Models\Contrato::ESTADO_NOMINA_PENDIENTE);
            })
            ->select(
                'usuario.doc',
                DB::raw("{$nombreExpr} as nombre"),
                'usuario.telefono',
                'contrato.salario_base',
                'contrato.id_contrato'
            );

        if ($q !== '') {
            $query->where(function ($sub) use ($q, $qDoc, $nombreExpr) {
                $sub->where('usuario.doc', 'like', "%{$q}%")
                    ->orWhereRaw("{$nombreExpr} LIKE ?", ["%{$q}%"]);

                if ($qDoc !== '') {
                    $sub->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(usuario.doc, '.', ''), '-', ''), ' ', '') LIKE ?",
                        ["%{$qDoc}%"]
                    );
                }
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
       GUARDAR NÓMINA
    ========================== */
    public function store(Request $request)
    {
        try {
            $s1 = session('nomina.step1');
            $s2 = session('nomina.step2');
            $s2Ingresos = session('nomina.step2_ingresos');

            if (!$s1 || !$s2 || !$s2Ingresos) {
                return redirect()->route('nomina.step1')->with('error', 'Sesión expirada o datos incompletos.');
            }

            $isEditing = (bool) session('nomina.editing_id');

            $empleado = $this->resolveNominaEmployeeContract(
                (string) ($s1['doc'] ?? ''),
                (int) ($s1['id_contrato'] ?? 0),
                !$isEditing
            );

            if (!$empleado) {
                return redirect()->route('nomina.step1')
                    ->with('error', 'Debes seleccionar un empleado válido de la lista.');
            }

            $request->merge([
                'retencion_fuente' => $this->parseNumber($request->input('retencion_fuente')),
                'embargo_fiscal' => $this->parseNumber($request->input('embargo_fiscal')),
                'pension_voluntaria' => $this->parseNumber($request->input('pension_voluntaria')),
            ]);

            $validated = $request->validate([
                'retencion_fuente' => 'nullable|numeric|min:0',
                'embargo_fiscal' => 'nullable|numeric|min:0',
                'pension_voluntaria' => 'nullable|numeric|min:0',
                'confirm_edit' => $isEditing ? 'required|string|in:editar' : 'nullable|string',
            ], [
                ...self::VALIDATION_MESSAGES,
                'confirm_edit.required' => 'Debes confirmar la edición escribiendo "editar".',
                'confirm_edit.in' => 'Para editar debes escribir exactamente "editar".',
            ]);

            $salarioBase = $this->parseNumber($s1['salario_base'] ?? 0);
            $rules = $this->getContractContributionRules($s1['id_contrato'] ?? null);
            $contributions = $this->calculateContributions($salarioBase, $rules);

            $fechaPago = $s1['fecha_pago'] ?? now()->toDateString();
            $periodoId = session('active_period_id');

            if (!$periodoId) {
                throw new \Exception('Debe seleccionar un periodo antes de liquidar.');
            }

            $estado = \App\Models\Salario::ESTADO_LIQUIDADO;

            if (!$isEditing) {
                // 1. Validar pago duplicado PRIMERO
                $existe = DB::table('salario')
                    ->where('id_contrato', $s1['id_contrato'])
                    ->where('id_periodo', $periodoId)
                    ->exists();

                if ($existe) {
                    return back()->with('error', 'Ya existe un registro de nómina para este empleado en el periodo seleccionado (Mes ' . date('m/Y', strtotime($fechaPago)) . ').');
                }

                // 2. Verificar estado del periodo
                $periodo = DB::table('periodo_liquidacion')
                    ->where('id_periodo', $periodoId)
                    ->select('estado')
                    ->first();

                if ($periodo && $periodo->estado === \App\Models\PeriodoLiquidacion::ESTADO_CERRADO) {
                    abort(403, 'El periodo seleccionado se encuentra cerrado y no permite liquidaciones o novedades.');
                }
            }

            $retencionFuente = $this->parseNumber($validated['retencion_fuente'] ?? 0);
            $embargoFiscal = $this->parseNumber($validated['embargo_fiscal'] ?? 0);
            $pensionVoluntaria = $this->parseNumber($validated['pension_voluntaria'] ?? 0);

            $payload = $this->buildSalarioPayload(
                $s1,
                $s2,
                $s2Ingresos,
                $periodoId,
                $estado,
                $contributions,
                $fechaPago,
                $retencionFuente,
                $embargoFiscal,
                $pensionVoluntaria
            );

            // 3. Persistencia Transaccional
            DB::transaction(function () use ($isEditing, $payload, $periodoId) {
                if (!$isEditing) {
                    $periodo = DB::table('periodo_liquidacion')
                        ->where('id_periodo', $periodoId)
                        ->select('estado')
                        ->lockForUpdate()
                        ->first();

                    if ($periodo && $periodo->estado === \App\Models\PeriodoLiquidacion::ESTADO_PENDIENTE) {
                        // La primera liquidación abre el periodo automáticamente
                        DB::table('periodo_liquidacion')
                            ->where('id_periodo', $periodoId)
                            ->update([
                                'estado' => \App\Models\PeriodoLiquidacion::ESTADO_ABIERTO,
                                'updated_at' => now()
                            ]);
                    }

                    DB::table('salario')->insert(array_merge($payload, [
                        'created_at' => now(),
                    ]));
                } else {
                    $periodo = DB::table('periodo_liquidacion')
                        ->where('id_periodo', $periodoId)
                        ->lockForUpdate()
                        ->first();

                    if ($periodo && $periodo->estado === \App\Models\PeriodoLiquidacion::ESTADO_CERRADO) {
                        abort(403, 'No se puede modificar una nómina de un periodo que ha sido cerrado recientemente.');
                    }

                    DB::table('salario')
                        ->where('id_salario', (int) session('nomina.editing_id'))
                        ->update($payload);
                }
            });

            session()->forget('nomina');

            return redirect()->route('nomina.index')
                ->with('success', $isEditing ? 'Nómina actualizada correctamente' : 'Nómina guardada correctamente');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al guardar nómina: " . $e->getMessage());
            return back()->with('error', 'Hubo un error al procesar la nómina. Por favor, intente nuevamente.');
        }
    }

    private function resolveNominaEmployeeContract(string $doc, int $idContrato, bool $requireActive = true): ?object
    {
        $empresaId = session('empresa_id');

        $query = DB::table('usuario')
            ->join('contrato', 'usuario.doc', '=', 'contrato.doc')
            ->where('contrato.id_empresa', $empresaId)
            ->where('usuario.doc', $doc)
            ->where('contrato.id_contrato', $idContrato)
            ->select(
                'usuario.doc',
                DB::raw("TRIM(CONCAT(usuario.primer_nombre,' ',IFNULL(usuario.otros_nombres,''),' ',usuario.primer_apellido,' ',IFNULL(usuario.segundo_apellido,''))) as nombre"),
                'usuario.telefono',
                'contrato.salario_base',
                'contrato.id_contrato'
            );

        if ($requireActive) {
            $query->where(function ($q) {
                $q->where('contrato.estado_laboral', \App\Models\Contrato::ESTADO_LABORAL_ACTIVO)
                    ->orWhere('contrato.estado_nomina', \App\Models\Contrato::ESTADO_NOMINA_PENDIENTE);
            });
        }

        return $query->first();
    }

    private function buildStep2SessionData(float $salarioBase, array $validated): array
    {
        $horasMes = (float) config('nomina.horas_mes', 240);
        $valorHoraNormal = $horasMes > 0 ? ($salarioBase / $horasMes) : 0;
        $cantidades = [];
        $valores = [];

        foreach (self::STEP2_RATES as $key => $multiplier) {
            $cantidad = (int) round($this->parseNumber($validated[$key] ?? 0));
            $cantidades[$key] = $cantidad;
            $valores["valor_{$key}"] = $cantidad * ($valorHoraNormal * $multiplier);
        }

        $totalHorasExtra = 0;
        foreach (self::STEP2_EXTRA_KEYS as $key) {
            $totalHorasExtra += $valores["valor_{$key}"];
        }

        $totalRecargos = 0;
        foreach (self::STEP2_RECARGO_KEYS as $key) {
            $totalRecargos += $valores["valor_{$key}"];
        }

        $valorHorasExtrasRecargos = $totalHorasExtra + $totalRecargos;

        return array_merge($cantidades, $valores, [
            'valor_hora_normal' => $valorHoraNormal,
            'total_horas_extra' => $totalHorasExtra,
            'total_recargos' => $totalRecargos,
            'valor_horas_extras_recargos' => $valorHorasExtrasRecargos,
            'total_devengos_parcial' => $salarioBase + $valorHorasExtrasRecargos,
        ]);
    }

    private function buildEditingSessionPayload(object $registro): array
    {
        $salarioBase = $this->parseNumber($registro->salario_base ?? 0);
        $horasMes = (float) config('nomina.horas_mes', 240);
        $valorHoraNormal = ($salarioBase > 0 && $horasMes > 0) ? ($salarioBase / $horasMes) : 0;
        $valorHorasExtrasRecargos = $this->parseNumber($registro->valor_horas_extras_recargos ?? 0);
        $horasExtraDiurnas = ($valorHoraNormal > 0)
            ? (int) round($valorHorasExtrasRecargos / ($valorHoraNormal * self::STEP2_RATES['horas_extra_diurnas']))
            : 0;

        $totalDevengosFinal =
            $salarioBase +
            $valorHorasExtrasRecargos +
            $this->parseNumber($registro->bonificaciones ?? 0) +
            $this->parseNumber($registro->comisiones ?? 0) +
            $this->parseNumber($registro->otros_devengos ?? 0);

        return [
            'nomina.editing_id' => (int) $registro->id_salario,
            'nomina.step' => 1,
            'nomina.step1' => [
                'empleado_busqueda' => trim(($registro->doc ?? '') . ' - ' . ($registro->nombre ?? '')),
                'nombre' => $registro->nombre,
                'telefono' => $registro->telefono,
                'salario_base' => $salarioBase,
                'fecha_pago' => $registro->fecha_pago,
                'doc' => $registro->doc,
                'id_contrato' => $registro->id_contrato,
            ],
            'nomina.step2' => [
                'horas_extra_diurnas' => $horasExtraDiurnas,
                'horas_extra_nocturnas' => 0,
                'horas_extra_dominicales_diurnas' => 0,
                'horas_extra_dominicales_nocturnas' => 0,
                'recargo_nocturno' => 0,
                'recargo_dominical_diurno' => 0,
                'recargo_dominical_nocturno' => 0,
                'recargo_festivo_diurno' => 0,
                'recargo_festivo_nocturno' => 0,
                'valor_horas_extra_diurnas' => $valorHorasExtrasRecargos,
                'valor_horas_extra_nocturnas' => 0,
                'valor_horas_extra_dominicales_diurnas' => 0,
                'valor_horas_extra_dominicales_nocturnas' => 0,
                'valor_recargo_nocturno' => 0,
                'valor_recargo_dominical_diurno' => 0,
                'valor_recargo_dominical_nocturno' => 0,
                'valor_recargo_festivo_diurno' => 0,
                'valor_recargo_festivo_nocturno' => 0,
                'valor_hora_normal' => $valorHoraNormal,
                'total_horas_extra' => $valorHorasExtrasRecargos,
                'total_recargos' => 0,
                'valor_horas_extras_recargos' => $valorHorasExtrasRecargos,
                'total_devengos_parcial' => $salarioBase + $valorHorasExtrasRecargos,
            ],
            'nomina.step2_ingresos' => [
                'bonificaciones' => $this->parseNumber($registro->bonificaciones ?? 0),
                'comisiones' => $this->parseNumber($registro->comisiones ?? 0),
                'otros_devengos' => $this->parseNumber($registro->otros_devengos ?? 0),
                'total_devengos_final' => $totalDevengosFinal,
            ],
            'nomina.step3' => [
                'retencion_fuente' => $this->parseNumber($registro->retencion_fuente ?? 0),
                'embargo_fiscal' => $this->parseNumber($registro->embargo_fiscal ?? 0),
                'pension_voluntaria' => $this->parseNumber($registro->pension_voluntaria ?? 0),
            ],
        ];
    }

    private function buildSalarioPayload(
        array $s1,
        array $s2,
        array $s2Ingresos,
        int $periodoId,
        string $estado,
        array $contributions,
        string $fechaPago,
        float $retencionFuente,
        float $embargoFiscal,
        float $pensionVoluntaria
    ): array {
        return [
            'id_contrato' => $s1['id_contrato'],
            'id_periodo' => $periodoId,
            'estado' => $estado,
            'auxilio_transporte' => 162000,
            'valor_horas_extras_recargos' => $this->parseNumber($s2['valor_horas_extras_recargos'] ?? 0),
            'bonificaciones' => $this->parseNumber($s2Ingresos['bonificaciones'] ?? 0),
            'comisiones' => $this->parseNumber($s2Ingresos['comisiones'] ?? 0),
            'otros_devengos' => $this->parseNumber($s2Ingresos['otros_devengos'] ?? 0),
            'arl' => $contributions['arl'],
            'eps' => $contributions['eps'],
            'afp' => $contributions['afp'],
            'seguridad_social' => $contributions['seguridad_social'],
            'aporte_fp' => $contributions['aporte_fp'],
            'retencion_fuente' => $retencionFuente,
            'embargo_fiscal' => $embargoFiscal,
            'pension_voluntaria' => $pensionVoluntaria,
            'fecha_pago' => $fechaPago,
            'updated_at' => now(),
        ];
    }
}
