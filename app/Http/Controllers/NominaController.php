<?php

namespace App\Http\Controllers;

use App\Models\Salario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NominaController extends Controller
{
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

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $normalized = str_replace(',', '.', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }

    /* ==========================
       INDEX
    ========================== */
    public function index(Request $request)
    {
        $salarios = Salario::with('contrato.usuario')
            ->when($request->documento, function ($q) use ($request) {
                $q->whereHas('contrato.usuario', function ($u) use ($request) {
                    $u->where('doc', $request->documento);
                });
            })
            ->orderByDesc('fecha_pago')
            ->get();

        return view('nomina.index', compact('salarios'));
    }

    /* ==========================
       STEP 1
    ========================== */
    public function step1()
    {
        session(['nomina.step' => 1]);
        $step1 = session('nomina.step1', []);
        return view('nomina.step1', compact('step1'));
    }

    public function postStep1(Request $request)
    {
        $data = $request->all();
        $data['salario_base'] = $this->parseNumber($request->input('salario_base'));
        session(['nomina.step1' => $data]);
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
            return redirect()->route('nomina.step1')->with('error', 'Completa el paso 1 para continuar.');
        }

        $salarioBase = $this->parseNumber($s1['salario_base'] ?? 0);
        $step2 = session('nomina.step2', []);

        return view('nomina.step2', compact('salarioBase', 'step2'));
    }

    public function postStep2(Request $request)
    {
        $validated = $request->validate([
            'horas_extra_diurnas' => 'nullable|numeric|min:0',
            'horas_extra_nocturnas' => 'nullable|numeric|min:0',
            'horas_extra_dominicales_diurnas' => 'nullable|numeric|min:0',
            'horas_extra_dominicales_nocturnas' => 'nullable|numeric|min:0',
            'recargo_nocturno' => 'nullable|numeric|min:0',
            'recargo_dominical_diurno' => 'nullable|numeric|min:0',
            'recargo_dominical_nocturno' => 'nullable|numeric|min:0',
            'recargo_festivo_diurno' => 'nullable|numeric|min:0',
            'recargo_festivo_nocturno' => 'nullable|numeric|min:0',
        ], [
            '*.numeric' => 'Este campo debe ser numérico.',
            '*.min' => 'Este campo no puede ser negativo.',
        ]);

        $s1 = session('nomina.step1');
        if (!$s1) {
            return redirect()->route('nomina.step1')->with('error', 'Completa el paso 1 para continuar.');
        }

        $salarioBase = $this->parseNumber($s1['salario_base'] ?? 0);
        $valorHoraNormal = $salarioBase / 240;

        $hExtraDiurna = $this->parseNumber($validated['horas_extra_diurnas'] ?? 0);
        $hExtraNocturna = $this->parseNumber($validated['horas_extra_nocturnas'] ?? 0);
        $hExtraDomDiurna = $this->parseNumber($validated['horas_extra_dominicales_diurnas'] ?? 0);
        $hExtraDomNocturna = $this->parseNumber($validated['horas_extra_dominicales_nocturnas'] ?? 0);
        $rNocturno = $this->parseNumber($validated['recargo_nocturno'] ?? 0);
        $rDomDiurno = $this->parseNumber($validated['recargo_dominical_diurno'] ?? 0);
        $rDomNocturno = $this->parseNumber($validated['recargo_dominical_nocturno'] ?? 0);
        $rFestivoDiurno = $this->parseNumber($validated['recargo_festivo_diurno'] ?? 0);
        $rFestivoNocturno = $this->parseNumber($validated['recargo_festivo_nocturno'] ?? 0);

        $valorHorasExtraDiurna = $hExtraDiurna * ($valorHoraNormal * 1.25);
        $valorHorasExtraNocturna = $hExtraNocturna * ($valorHoraNormal * 1.75);
        $valorHorasExtraDomDiurna = $hExtraDomDiurna * ($valorHoraNormal * 2.0);
        $valorHorasExtraDomNocturna = $hExtraDomNocturna * ($valorHoraNormal * 2.5);

        $valorRecargoNocturno = $rNocturno * ($valorHoraNormal * 0.35);
        $valorRecargoDomDiurno = $rDomDiurno * ($valorHoraNormal * 0.75);
        $valorRecargoDomNocturno = $rDomNocturno * ($valorHoraNormal * 1.10);
        $valorRecargoFestivoDiurno = $rFestivoDiurno * ($valorHoraNormal * 0.75);
        $valorRecargoFestivoNocturno = $rFestivoNocturno * ($valorHoraNormal * 1.10);

        $totalHorasExtra =
            $valorHorasExtraDiurna +
            $valorHorasExtraNocturna +
            $valorHorasExtraDomDiurna +
            $valorHorasExtraDomNocturna;

        $totalRecargos =
            $valorRecargoNocturno +
            $valorRecargoDomDiurno +
            $valorRecargoDomNocturno +
            $valorRecargoFestivoDiurno +
            $valorRecargoFestivoNocturno;

        $totalDevengosParcial = $salarioBase + $totalHorasExtra + $totalRecargos;
        $valorHorasExtrasRecargos = $totalHorasExtra + $totalRecargos;

        session(['nomina.step2' => [
            'horas_extra_diurnas' => $hExtraDiurna,
            'horas_extra_nocturnas' => $hExtraNocturna,
            'horas_extra_dominicales_diurnas' => $hExtraDomDiurna,
            'horas_extra_dominicales_nocturnas' => $hExtraDomNocturna,
            'recargo_nocturno' => $rNocturno,
            'recargo_dominical_diurno' => $rDomDiurno,
            'recargo_dominical_nocturno' => $rDomNocturno,
            'recargo_festivo_diurno' => $rFestivoDiurno,
            'recargo_festivo_nocturno' => $rFestivoNocturno,
            'valor_horas_extra_diurnas' => $valorHorasExtraDiurna,
            'valor_horas_extra_nocturnas' => $valorHorasExtraNocturna,
            'valor_horas_extra_dominicales_diurnas' => $valorHorasExtraDomDiurna,
            'valor_horas_extra_dominicales_nocturnas' => $valorHorasExtraDomNocturna,
            'valor_recargo_nocturno' => $valorRecargoNocturno,
            'valor_recargo_dominical_diurno' => $valorRecargoDomDiurno,
            'valor_recargo_dominical_nocturno' => $valorRecargoDomNocturno,
            'valor_recargo_festivo_diurno' => $valorRecargoFestivoDiurno,
            'valor_recargo_festivo_nocturno' => $valorRecargoFestivoNocturno,
            'valor_hora_normal' => $valorHoraNormal,
            'total_horas_extra' => $totalHorasExtra,
            'total_recargos' => $totalRecargos,
            'valor_horas_extras_recargos' => $valorHorasExtrasRecargos,
            'total_devengos_parcial' => $totalDevengosParcial,
        ]]);

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
        $validated = $request->validate([
            'bonificaciones' => 'nullable|numeric|min:0',
            'comisiones' => 'nullable|numeric|min:0',
            'otros_devengos' => 'nullable|numeric|min:0',
        ], [
            '*.numeric' => 'Este campo debe ser numérico.',
            '*.min' => 'Este campo no puede ser negativo.',
        ]);

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

        return view('nomina.step3', compact('salarioBase', 'totalDevengos', 'rules'));
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

            $validated = $request->validate([
                'retencion_fuente' => 'nullable|numeric|min:0',
                'embargo_fiscal' => 'nullable|numeric|min:0',
                'pension_voluntaria' => 'nullable|numeric|min:0',
            ], [
                '*.numeric' => 'Este campo debe ser numérico.',
                '*.min' => 'Este campo no puede ser negativo.',
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

            DB::table('salario')->insert([
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
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            session()->forget('nomina');

            return redirect()->route('nomina.index')
                ->with('success', 'Nómina guardada correctamente');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al guardar nómina: " . $e->getMessage());
            return back()->with('error', 'Hubo un error al procesar la nómina. Por favor, intente nuevamente.');
        }
    }
}
