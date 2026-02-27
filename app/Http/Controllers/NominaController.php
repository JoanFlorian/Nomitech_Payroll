<?php

namespace App\Http\Controllers;

use App\Models\Salario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NominaController extends Controller
{
    private const VALIDATION_MESSAGES = [
        '*.numeric' => 'Este campo debe ser numérico.',
        '*.min' => 'Este campo no puede ser negativo.',
    ];

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
        $seguridadSocial = $eps + $afp + $arl;
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

    private function resolvePeriodoId($fechaPago): int
    {
        $timestamp = strtotime((string) $fechaPago) ?: time();
        $fechaInicio = date('Y-m-01', $timestamp);
        $fechaFin = date('Y-m-t', $timestamp);

        $periodoId = DB::table('periodo_liquidacion')
            ->whereDate('fecha_inicio', $fechaInicio)
            ->whereDate('fecha_fin', $fechaFin)
            ->value('id_periodo');

        if ($periodoId) {
            return (int) $periodoId;
        }

        return (int) DB::table('periodo_liquidacion')->insertGetId([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_periodo');
    }

    private function resolveEstadoId(): int
    {
        $estadoId = DB::table('estado')->orderBy('id_estado')->value('id_estado');

        if ($estadoId) {
            return (int) $estadoId;
        }

        return (int) DB::table('estado')->insertGetId([
            'nombre' => 'Activo',
            'descripcion' => 'Estado creado automáticamente para nómina',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_estado');
    }

    private function parseNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
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

    /* ==========================
       INDEX
    ========================== */
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('documento', ''));

        $salarios = Salario::with('contrato.usuario')
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $term = mb_strtolower($busqueda);
                $q->whereHas('contrato.usuario', function ($u) use ($busqueda, $term) {
                    $u->where('doc', 'like', "%{$busqueda}%")
                        ->orWhereRaw(
                            "LOWER(CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)) LIKE ?",
                            ["%{$term}%"]
                        );
                });
            })
            ->orderByDesc('fecha_pago')
            ->paginate(4)
            ->withQueryString();

        return view('nomina.index', compact('salarios'));
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

        return view('nomina.step1', compact('step1', 'isEditing'));
    }

    public function postStep1(Request $request)
    {
        $data = $request->all();
        $data['salario_base'] = $this->parseNumber($request->input('salario_base'));
        session(['nomina.step1' => $data]);
        return redirect()->route('nomina.step2');
    }

    public function edit(int $idSalario)
    {
        $registro = DB::table('salario as s')
            ->join('contrato as c', 'c.id_contrato', '=', 's.id_contrato')
            ->join('usuario as u', 'u.doc', '=', 'c.doc')
            ->where('s.id_salario', $idSalario)
            ->select(
                's.id_salario',
                's.id_contrato',
                's.fecha_pago',
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

        return view('nomina.step2', compact('salarioBase', 'step2'));
    }

    public function postStep2(Request $request)
    {
        $request->merge(
            collect(array_keys(self::STEP2_RATES))
                ->mapWithKeys(fn (string $key) => [$key => $this->parseNumber($request->input($key))])
                ->all()
        );

        $validated = $request->validate(
            array_fill_keys(array_keys(self::STEP2_RATES), 'nullable|numeric|min:0'),
            self::VALIDATION_MESSAGES
        );

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
        $step2Ingresos = session('nomina.step2_ingresos', []);

        return view('nomina.step2_ingresos', compact('salarioBase', 's2', 'step2Ingresos'));
    }

    public function postStep2Ingresos(Request $request)
    {
        $request->merge([
            'bonificaciones' => $this->parseNumber($request->input('bonificaciones')),
            'comisiones' => $this->parseNumber($request->input('comisiones')),
            'otros_devengos' => $this->parseNumber($request->input('otros_devengos')),
        ]);

        $validated = $request->validate([
            'bonificaciones' => 'nullable|numeric|min:0',
            'comisiones' => 'nullable|numeric|min:0',
            'otros_devengos' => 'nullable|numeric|min:0',
        ], self::VALIDATION_MESSAGES);

        $s1 = session('nomina.step1');
        $s2 = session('nomina.step2');
        if (!$s1 || !$s2) {
            return redirect()->route('nomina.step2')->with('error', 'Completa primero las horas y recargos.');
        }

        $bonificaciones = $this->parseNumber($validated['bonificaciones'] ?? 0);
        $comisiones = $this->parseNumber($validated['comisiones'] ?? 0);
        $otrosDevengos = $this->parseNumber($validated['otros_devengos'] ?? 0);

        $totalDevengosFinal =
            $this->parseNumber($s1['salario_base'] ?? 0) +
            $this->parseNumber($s2['total_horas_extra'] ?? 0) +
            $this->parseNumber($s2['total_recargos'] ?? 0) +
            $bonificaciones +
            $comisiones +
            $otrosDevengos;

        session(['nomina.step2_ingresos' => [
            'bonificaciones' => $bonificaciones,
            'comisiones' => $comisiones,
            'otros_devengos' => $otrosDevengos,
            'total_devengos_final' => $totalDevengosFinal,
        ]]);

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

        return view('nomina.step3', compact('salarioBase', 'totalDevengos', 'rules', 'step3', 'isEditing'));
    }

    /* ==========================
       BUSCAR EMPLEADO
    ========================== */
    public function buscarEmpleado($doc)
    {
        return DB::table('usuario')
            ->join('contrato', 'usuario.doc', '=', 'contrato.doc')
            ->where('usuario.doc', $doc)
            ->where('contrato.activo', 1)
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

        $nombreExpr = "TRIM(CONCAT(
            usuario.primer_nombre,' ',
            IFNULL(usuario.otros_nombres,''),' ',
            usuario.primer_apellido,' ',
            IFNULL(usuario.segundo_apellido,'')
        ))";

        $query = DB::table('usuario')
            ->join('contrato', 'usuario.doc', '=', 'contrato.doc')
            ->where('contrato.activo', 1)
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
            $periodoId = $this->resolvePeriodoId($fechaPago);
            $estadoId = $this->resolveEstadoId();

            $retencionFuente = $this->parseNumber($validated['retencion_fuente'] ?? 0);
            $embargoFiscal = $this->parseNumber($validated['embargo_fiscal'] ?? 0);
            $pensionVoluntaria = $this->parseNumber($validated['pension_voluntaria'] ?? 0);

            $payload = $this->buildSalarioPayload(
                $s1,
                $s2,
                $s2Ingresos,
                $periodoId,
                $estadoId,
                $contributions,
                $fechaPago,
                $retencionFuente,
                $embargoFiscal,
                $pensionVoluntaria
            );

            if ($isEditing) {
                DB::table('salario')
                    ->where('id_salario', (int) session('nomina.editing_id'))
                    ->update($payload);
            } else {
                DB::table('salario')->insert(array_merge($payload, [
                    'created_at' => now(),
                ]));
            }

            session()->forget('nomina');

            return redirect()->route('nomina.index')
                ->with('success', $isEditing ? 'Nómina actualizada correctamente' : 'Nómina guardada correctamente');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al guardar nómina: " . $e->getMessage());
            return back()->with('error', 'Hubo un error al procesar la nómina. Por favor, intente nuevamente.');
        }
    }

    private function buildStep2SessionData(float $salarioBase, array $validated): array
    {
        $valorHoraNormal = $salarioBase / 240;
        $cantidades = [];
        $valores = [];

        foreach (self::STEP2_RATES as $key => $multiplier) {
            $cantidad = $this->parseNumber($validated[$key] ?? 0);
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
        $valorHoraNormal = $salarioBase > 0 ? ($salarioBase / 240) : 0;
        $valorHorasExtrasRecargos = $this->parseNumber($registro->valor_horas_extras_recargos ?? 0);
        $horasExtraDiurnas = ($valorHoraNormal > 0)
            ? round(($valorHorasExtrasRecargos / ($valorHoraNormal * self::STEP2_RATES['horas_extra_diurnas'])), 2)
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
        int $estadoId,
        array $contributions,
        string $fechaPago,
        float $retencionFuente,
        float $embargoFiscal,
        float $pensionVoluntaria
    ): array {
        return [
                'id_contrato' => $s1['id_contrato'],
                'id_periodo' => $periodoId,
                'id_estado' => $estadoId,
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
