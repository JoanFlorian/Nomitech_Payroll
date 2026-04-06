<?php

namespace App\Http\Requests;

use App\Models\Novedad;
use App\Models\PeriodoLiquidacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreNovedadEmpleadoRequest extends FormRequest
{
    private const DIAS_MAXIMO_GENERAL = 30;
    private const DIAS_MAXIMO_MATERNIDAD = 126;
    private const DIAS_MAXIMO_PATERNIDAD = 14;

    private const TIPOS_EXCLUSIVOS = ['SLN', 'IGE', 'IRL', 'LMAT', 'LPAT', 'VAC', 'INC', 'LIC'];

    private const TIPOS_NOVEDAD = [
        'TDE',
        'TAE',
        'TDP',
        'TAP',
        'VSP',
        'VST',
        'SLN',
        'IGE',
        'IRL',
        'LMAT',
        'LPAT',
        'VAC',
        'VCT',
        'INC',
        'LIC',
        'RET',
    ];

    private const TIPOS_REQUIEREN_SOPORTE_MEDICO = ['INC', 'IGE', 'IRL'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = (int) session('empresa_id');
        $today = now(config('app.timezone'))->toDateString();

        return [
            'empleado_id' => [
                'bail',
                'required',
                Rule::exists('contrato', 'doc')->where(function ($query) use ($empresaId) {
                    if ($empresaId > 0) {
                        $query->where('id_empresa', $empresaId);
                    }
                }),
            ],
            'tipo_novedad' => 'bail|required|string|in:' . implode(',', self::TIPOS_NOVEDAD),
            'unidad_cantidad' => 'bail|required|in:dias,horas',
            'cantidad_dias' => 'bail|nullable|numeric|min:0.01|max:126',
            'cantidad_horas' => 'bail|nullable|numeric|min:0.01|max:240',
            'dias' => 'bail|nullable|numeric|min:0.01|max:126',
            'horas' => 'bail|nullable|numeric|min:0.01|max:240',
            'fecha_inicio' => 'bail|required|date',
            'fecha_fin' => 'bail|nullable|date|after_or_equal:fecha_inicio',
            'observaciones' => 'bail|nullable|string|max:500',
            'pago_manual' => 'bail|nullable|numeric|min:0|max:999999999.99',
            'valor_manual' => 'bail|nullable|numeric|min:0|max:999999999.99',
            'salario_base' => 'bail|required|numeric|min:1',
            'es_remunerado' => 'bail|nullable|boolean',
            'licencia_remunerada' => 'bail|nullable|boolean',
            'tipo_licencia' => 'bail|nullable|string|in:luto,calamidad_domestica,permiso_especial,remunerada,no_remunerada',
            'certificado_medico' => 'bail|nullable|boolean',
            'soporte_medico_archivo' => 'bail|nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_eps' => 'bail|nullable|integer|exists:eps,id_eps',
            'id_afp' => 'bail|nullable|integer|exists:afp,id_afp',
            'id_arl' => 'bail|nullable|integer|exists:arl,id_arl',
        ];
    }

    public function messages(): array
    {
        return [
            'empleado_id.required' => 'Debe seleccionar un empleado.',
            'empleado_id.exists' => 'El empleado seleccionado no es válido o no pertenece a su empresa.',
            'tipo_novedad.required' => 'Debe seleccionar el tipo de novedad.',
            'tipo_novedad.in' => 'El tipo de novedad seleccionado no es válido.',
            'unidad_cantidad.required' => 'Debe seleccionar si la cantidad es en días u horas.',
            'unidad_cantidad.in' => 'La unidad de cantidad debe ser días u horas.',
            'dias.numeric' => 'Los días deben ser numéricos.',
            'horas.numeric' => 'Las horas deben ser numéricas.',
            'cantidad_dias.numeric' => 'Los días deben ser numéricos.',
            'cantidad_horas.numeric' => 'Las horas deben ser numéricas.',
            'dias.min' => 'Los días deben ser mayor a 0.',
            'horas.min' => 'Las horas deben ser mayor a 0.',
            'cantidad_dias.min' => 'Los días deben ser mayor a 0.',
            'cantidad_horas.min' => 'Las horas deben ser mayor a 0.',
            'dias.max' => 'Los días no pueden superar 126 por novedad.',
            'horas.max' => 'Las horas no pueden superar 240 por novedad.',
            'cantidad_dias.max' => 'Los días no pueden superar 126 por novedad.',
            'cantidad_horas.max' => 'Las horas no pueden superar 240 por novedad.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a la fecha actual',
            'fecha_fin.required' => 'La fecha fin es obligatoria.',
            'fecha_fin.date' => 'La fecha fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser igual o posterior a la fecha de inicio.',
            'salario_base.required' => 'El salario base es obligatorio.',
            'salario_base.min' => 'El salario base debe ser mayor a 0.',
            'salario_base.numeric' => 'El salario base debe ser numérico.',
            'observaciones.max' => 'Las observaciones no pueden superar los 500 caracteres.',
            'pago_manual.numeric' => 'El pago manual debe ser numérico.',
            'pago_manual.min' => 'El pago manual no puede ser negativo.',
            'valor_manual.numeric' => 'El valor manual debe ser numérico.',
            'valor_manual.min' => 'El valor manual no puede ser negativo.',
            'tipo_licencia.in' => 'El tipo de licencia seleccionado no es válido.',
            'soporte_medico_archivo.file' => 'Debe adjuntar un archivo válido como soporte médico.',
            'soporte_medico_archivo.mimes' => 'El soporte médico debe estar en formato PDF, JPG o PNG.',
            'soporte_medico_archivo.max' => 'El soporte médico no puede superar los 5 MB.',
            'id_eps.exists' => 'La EPS seleccionada no es válida.',
            'id_afp.exists' => 'La AFP seleccionada no es válida.',
            'id_arl.exists' => 'La ARL seleccionada no es válida.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $tipoNovedad = $this->normalizeTipoNovedad($this->input('tipo_novedad'));

        $dias = $this->normalizeNumeric($this->input('dias', $this->input('cantidad_dias')));
        $horas = $this->normalizeNumeric($this->input('horas', $this->input('cantidad_horas')));

        $unidadCantidad = $this->input('unidad_cantidad', ($horas > 0 ? 'horas' : 'dias'));

        if (in_array($tipoNovedad, ['TDE', 'TAE', 'TDP', 'TAP', 'VSP', 'VST', 'VCT', 'RET'], true)) {
            $dias = in_array($tipoNovedad, ['TDE', 'TAE', 'TDP', 'TAP', 'RET'], true) ? 1 : null;
            $horas = null;
            
            // Para traslados y retiro, sincronizar fecha_fin con fecha_inicio
            if (in_array($tipoNovedad, ['TDE', 'TAE', 'TDP', 'TAP', 'RET'], true)) {
                $this->merge(['fecha_fin' => $this->input('fecha_inicio')]);
            }
        }

        if ($tipoNovedad === 'LMAT') {
            $unidadCantidad = 'dias';
            $horas = null;
        }

        if (in_array($tipoNovedad, ['LPAT', 'VAC', 'LIC'], true)) {
            $unidadCantidad = 'dias';
            if ($horas !== null && $horas > 0) {
                $unidadCantidad = 'horas';
                $dias = null;
            } else {
                $horas = null;
            }
        }

        $this->merge([
            'empleado_id' => $this->input('empleado_id', $this->input('doc_empleado')),
            'tipo_novedad' => $tipoNovedad,
            'dias' => $dias,
            'horas' => $horas,
            'unidad_cantidad' => $unidadCantidad,
            'es_remunerado' => $this->boolean('es_remunerado', $this->boolean('licencia_remunerada', false)),
            'pago_manual' => $this->normalizeNumeric($this->input('pago_manual', $this->input('pago'))),
            'valor_manual' => $this->normalizeNumeric($this->input('valor_manual', $this->input('pago_manual', $this->input('pago')))),
            'tipo_licencia' => strtolower(trim((string) $this->input('tipo_licencia', ''))),
            'certificado_medico' => $this->boolean('certificado_medico') || $this->hasFile('soporte_medico_archivo'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $tipo = $this->input('tipo_novedad');
            $unidad = $this->input('unidad_cantidad');
            $dias = (float) ($this->input('dias') ?? 0);
            $horas = (float) ($this->input('horas') ?? 0);
            $fechaInicio = $this->input('fecha_inicio');
            $fechaFin = $this->input('fecha_fin');
            $empleadoDoc = $this->input('empleado_id');
            $pagoManual = $this->input('valor_manual', $this->input('pago_manual'));
            $tipoLicencia = strtolower(trim((string) $this->input('tipo_licencia')));
            $certificadoMedico = filter_var($this->input('certificado_medico', false), FILTER_VALIDATE_BOOLEAN);
            $requiereSoporteMedico = in_array($tipo, self::TIPOS_REQUIEREN_SOPORTE_MEDICO, true);
            $tieneArchivoSoporte = $this->hasFile('soporte_medico_archivo');
            $tieneSoportePersistido = $this->hasExistingMedicalSupport();
            $esPreview = $this->routeIs('novedades.calculo.preview');

            $requiereCantidad = !in_array($tipo, ['TDE', 'TAE', 'TDP', 'TAP', 'VSP', 'VST', 'VCT', 'RET'], true);

            // Validación: cantidad requerida según tipo
            if ($requiereCantidad) {
                if (!in_array($unidad, ['dias', 'horas'], true)) {
                    $validator->errors()->add('unidad_cantidad', 'Debe indicar si la novedad se calcula en días u horas.');
                }
                
                if ($unidad === 'dias' && $dias <= 0) {
                    $validator->errors()->add('dias', 'Debe ingresar la cantidad en días (mayor a 0).');
                    $validator->errors()->add('cantidad_dias', 'Debe ingresar la cantidad en días (mayor a 0).');
                }

                if ($unidad === 'horas' && $horas <= 0) {
                    $validator->errors()->add('horas', 'Debe ingresar la cantidad en horas (mayor a 0).');
                    $validator->errors()->add('cantidad_horas', 'Debe ingresar la cantidad en horas (mayor a 0).');
                }
            }

            // Validación: máximo días según tipo.
            // IGE/IRL/INC pueden abarcar más de un periodo (hasta 126),
            // pero el pago del empleador se limita por regla de negocio en el cálculo.
            if (
                $requiereCantidad
                && $unidad === 'dias'
                && $dias > self::DIAS_MAXIMO_GENERAL
                && !in_array($tipo, ['LMAT', 'LPAT', 'IGE', 'IRL', 'INC'], true)
            ) {
                $validator->errors()->add('dias', 'Los días no pueden superar 30 por periodo.');
                $validator->errors()->add('cantidad_dias', 'Los días no pueden superar 30 por periodo.');
            }

            // Validación: licencia de maternidad
            if ($tipo === 'LMAT') {
                if ($unidad !== 'dias') {
                    $validator->errors()->add('unidad_cantidad', 'La licencia de maternidad solo se registra en días.');
                }

                if ($dias > (float) self::DIAS_MAXIMO_MATERNIDAD) {
                    $validator->errors()->add('dias', 'La licencia de maternidad no puede exceder 126 días.');
                    $validator->errors()->add('cantidad_dias', 'La licencia de maternidad no puede exceder 126 días.');
                }
                
                if ($dias <= 0) {
                    $validator->errors()->add('dias', 'Debe ingresar la cantidad de días para la licencia de maternidad.');
                    $validator->errors()->add('cantidad_dias', 'Debe ingresar la cantidad de días para la licencia de maternidad.');
                }
            }

            // Validación: licencia de paternidad
            if ($tipo === 'LPAT') {
                if ($unidad !== 'dias') {
                    $validator->errors()->add('unidad_cantidad', 'La licencia de paternidad solo se registra en días.');
                }

                if ($dias !== (float) self::DIAS_MAXIMO_PATERNIDAD) {
                    $validator->errors()->add('dias', 'La licencia de paternidad debe ser de 14 días.');
                    $validator->errors()->add('cantidad_dias', 'La licencia de paternidad debe ser de 14 días.');
                }
            }

            // Validación: tipo de licencia requerido para LIC
            if ($tipo === 'LIC' && !in_array($tipoLicencia, ['luto', 'calamidad_domestica', 'permiso_especial', 'remunerada', 'no_remunerada'], true)) {
                $validator->errors()->add('tipo_licencia', 'Debe seleccionar un tipo de licencia válido.');
            }

            // Validación: soporte médico obligatorio para incapacidades
            if ($requiereSoporteMedico && !$certificadoMedico && !$tieneArchivoSoporte && !$tieneSoportePersistido) {
                $validator->errors()->add('certificado_medico', 'La incapacidad debe contar con certificado médico verificado.');
            }

            if ($requiereSoporteMedico && !$esPreview && !$tieneArchivoSoporte && !$tieneSoportePersistido) {
                $validator->errors()->add('soporte_medico_archivo', 'Debe adjuntar el certificado médico en PDF, JPG o PNG para registrar esta incapacidad.');
            }

            // Validación: valor manual para VSP
            if ($tipo === 'VSP' && ($pagoManual === null || $pagoManual === '' || (float) $pagoManual <= 0)) {
                $validator->errors()->add('valor_manual', 'Debe ingresar el nuevo salario para la variación permanente de salario.');
                $validator->errors()->add('pago_manual', 'Debe ingresar el nuevo salario para la variación permanente de salario.');
            }

            // Validación: no permitir valor manual en novedades automáticas
            if (!in_array($tipo, ['VSP', 'VST'], true) && $pagoManual !== null && $pagoManual !== '' && (float) $pagoManual > 0) {
                $validator->errors()->add('valor_manual', 'Esta novedad se calcula automáticamente y no permite valor manual.');
                $validator->errors()->add('pago_manual', 'Esta novedad se calcula automáticamente y no permite valor manual.');
            }

            // Validación: EPS requerida para traslados
            if (in_array($tipo, ['TDE', 'TAE'], true) && !$this->filled('id_eps')) {
                $validator->errors()->add('id_eps', 'Debe seleccionar la EPS para el traslado.');
            }

            // Validación: AFP requerida para traslados
            if (in_array($tipo, ['TDP', 'TAP'], true) && !$this->filled('id_afp')) {
                $validator->errors()->add('id_afp', 'Debe seleccionar la AFP para el traslado.');
            }

            // Validación: ARL requerida para variación de centro de trabajo
            if ($tipo === 'VCT' && !$this->filled('id_arl')) {
                $validator->errors()->add('id_arl', 'Debe seleccionar la ARL para la variación de centro de trabajo.');
            }

            // Validación: Múltiples traslados en el mismo mes calendario
            if (in_array($tipo, ['TDE', 'TAE', 'TDP', 'TAP'], true) && $this->input('empleado_id') && $this->input('fecha_inicio')) {
                $mes = date('m', strtotime($this->input('fecha_inicio')));
                $anio = date('Y', strtotime($this->input('fecha_inicio')));
                
                $esEps = in_array($tipo, ['TDE', 'TAE'], true);
                $tiposAValidar = $esEps ? ['TDE', 'TAE'] : ['TDP', 'TAP'];
                
                $existeTraslado = \Illuminate\Support\Facades\DB::table('novedad')
                    ->where('empleado_id', $this->input('empleado_id'))
                    ->whereIn('tipo_novedad_codigo', $tiposAValidar)
                    ->whereMonth('fecha_inicio', $mes)
                    ->whereYear('fecha_inicio', $anio)
                    ->where(function($q) {
                        $q->where('estado', '!=', 'cerrada')->orWhereNull('estado');
                    })
                    ->when($this->input('edit_novedad_id'), function ($q, $id) {
                        $q->where('id_novedad', '!=', $id);
                    })
                    ->exists();

                if ($existeTraslado) {
                    $grupo = $esEps ? 'EPS' : 'AFP';
                    $validator->errors()->add('tipo_novedad', 'El empleado ya tiene un traslado de ' . $grupo . ' registrado en el mes ' . $mes . '-' . $anio . '.');
                }
            }

            // Validación: fechas coherentes
            if ($fechaInicio && $fechaFin && strtotime($fechaFin) < strtotime($fechaInicio)) {
                $validator->errors()->add('fecha_fin', 'La fecha fin debe ser igual o posterior a la fecha de inicio.');
            }

            // Validación: fechas coherentes con la cantidad de días (Candado de Coherencia)
            if ($unidad === 'dias' && $dias > 0 && $fechaInicio && $fechaFin) {
                $fInicio = \Carbon\Carbon::parse($fechaInicio);
                $fFin = \Carbon\Carbon::parse($fechaFin);
                $diasCalculados = $fInicio->diffInDays($fFin) + 1;

                if (abs($diasCalculados - $dias) > 0.01) {
                    $validator->errors()->add(
                        'fecha_fin',
                        "Discrepancia detectada: El rango de fechas seleccionado equivale a {$diasCalculados} días, pero se ingresaron {$dias} días. Por favor ajuste las fechas o la cantidad."
                    );
                }
            }

            // Validación: la novedad no puede ser anterior a la fecha de ingreso ni posterior a la de retiro (si aplica)
            if ($fechaInicio && $empleadoDoc) {
                $contrato = DB::table('contrato')
                    ->where('doc', $empleadoDoc)
                    ->orderByDesc('id_contrato')
                    ->first(['fecha_inicio', 'fecha_fin']);

                if ($contrato) {
                    if ($contrato->fecha_inicio) {
                        $fechaIngreso = \Carbon\Carbon::parse($contrato->fecha_inicio);
                        $fInicio    = \Carbon\Carbon::parse($fechaInicio);
                        if ($fInicio->lt($fechaIngreso)) {
                            $validator->errors()->add(
                                'fecha_inicio',
                                'La fecha de inicio de la novedad no puede ser anterior a la fecha de ingreso del empleado (' . $fechaIngreso->format('d/m/Y') . ').'
                            );
                        }
                    }

                    if ($contrato->fecha_fin && $fechaFin) {
                        $fechaRetiro = \Carbon\Carbon::parse($contrato->fecha_fin);
                        $fFin = \Carbon\Carbon::parse($fechaFin);
                        if ($fFin->gt($fechaRetiro)) {
                            $validator->errors()->add(
                                'fecha_fin',
                                'La novedad no puede terminar después de la fecha de terminación del contrato (' . $fechaRetiro->format('d/m/Y') . ').'
                            );
                        }
                    }
                }
            }

            // Validación: solo permitir novedades en periodos Abiertos o Pendientes
            // Excepción: variaciones de estructura (VSP/VCT), traslados y novedades de licencia/vacaciones/ausencia
            // pueden registrarse aunque no haya periodo activo (se asociarán al último periodo o quedan sin periodo)
            $tiposSinPeriodo = ['VSP', 'TDE', 'TAE', 'TDP', 'TAP', 'VCT', 'LIC', 'LMAT', 'LPAT', 'SLN', 'VAC', 'IGE', 'IRL', 'INC'];
            $periodoActivo = PeriodoLiquidacion::getActivePeriod();
            if (!in_array($tipo, $tiposSinPeriodo, true) && (!$periodoActivo || !in_array($periodoActivo->estado, [PeriodoLiquidacion::ESTADO_ABIERTO, PeriodoLiquidacion::ESTADO_PENDIENTE]))) {
                $validator->errors()->add(
                    'tipo_novedad',
                    'No se pueden registrar novedades porque el periodo de liquidación actual no existe o ya está cerrado/liquidado.'
                );
            }

            // Nueva validación: Al menos uno de los extremos (inicio o fin) debe estar en el periodo activo
            // para novedades que afectan la nómina (según requerimiento de flexibilidad).
            $tiposFlexibles = ['VAC', 'SLN', 'LIC', 'IGE', 'IRL', 'LMAT', 'LPAT', 'INC'];
            if (in_array($tipo, $tiposFlexibles, true) && $fechaInicio && $fechaFin) {
                if ($periodoActivo) {
                    $pI = \Carbon\Carbon::parse($periodoActivo->fecha_inicio);
                    $pF = \Carbon\Carbon::parse($periodoActivo->fecha_fin);
                    $fI = \Carbon\Carbon::parse($fechaInicio);
                    $fF = \Carbon\Carbon::parse($fechaFin);

                    $inicioEnPeriodo = $fI->between($pI, $pF);
                    $finEnPeriodo    = $fF->between($pI, $pF);

                    if (!$inicioEnPeriodo && !$finEnPeriodo) {
                        $validator->errors()->add(
                            'fecha_inicio',
                            'Al menos la fecha de inicio o la fecha de fin deben estar dentro del periodo de liquidación activo (' 
                            . $pI->format('d/m/Y') . ' a ' . $pF->format('d/m/Y') . ') para registrar esta novedad.'
                        );
                    }
                }
            }
            
            // Nueva validación para traslados y retiro: La fecha debe estar dentro del periodo activo
            if (in_array($tipo, ['TDE', 'TAE', 'TDP', 'TAP', 'RET'], true) && ($fechaInicio || $fechaFin)) {
                if ($periodoActivo) {
                    $fecha = $fechaInicio ?: $fechaFin;
                    $f = \Carbon\Carbon::parse($fecha);
                    if ($f->lt($periodoActivo->fecha_inicio) || $f->gt($periodoActivo->fecha_fin)) {
                        $validator->errors()->add(
                            'fecha_inicio',
                            'La fecha de ' . ($tipo === 'RET' ? 'retiro' : 'traslado') . ' debe estar dentro del periodo de liquidación actual ('
                            . \Carbon\Carbon::parse($periodoActivo->fecha_inicio)->format('d/m/Y') . ' a '
                            . \Carbon\Carbon::parse($periodoActivo->fecha_fin)->format('d/m/Y') . ').'
                        );
                    }
                }
            }

            // Validación: Coexistencia con otras novedades exclusivas (Traslapes)
            if (in_array($tipo, self::TIPOS_EXCLUSIVOS, true) && $fechaInicio && $fechaFin && $empleadoDoc) {
                $excludeId = $this->input('edit_novedad_id');
                
                $conflicto = DB::table('novedad')
                    ->where('empleado_id', $empleadoDoc)
                    ->whereIn('tipo_novedad_codigo', self::TIPOS_EXCLUSIVOS)
                    ->where(function ($q) {
                        $q->where('estado', '!=', 'cerrada')->orWhereNull('estado');
                    })
                    ->whereDate('fecha_inicio', '<=', $fechaFin)
                    ->whereDate('fecha_fin', '>=', $fechaInicio)
                    ->when($excludeId, function ($q, $id) {
                        $q->where('id_novedad', '!=', $id);
                    })
                    ->first();

                if ($conflicto) {
                    $fInicioC = \Carbon\Carbon::parse($conflicto->fecha_inicio)->format('d/m/Y');
                    $fFinC = \Carbon\Carbon::parse($conflicto->fecha_fin)->format('d/m/Y');
                    $validator->errors()->add(
                        'fecha_inicio',
                        "Conflicto de fechas: Ya existe una novedad de tipo {$conflicto->tipo_novedad_codigo} registrada del {$fInicioC} al {$fFinC}. No se admiten traslapes."
                    );
                }
            }

            // Validación: Saldo de Vacaciones
            if ($tipo === 'VAC' && $unidad === 'dias' && $dias > 0 && $empleadoDoc) {
                $balance = DB::table('benefit_balance')
                    ->where('employee_id', $empleadoDoc)
                    ->value('vacaciones_balance') ?? 0;
                
                // Si es edición, debemos sumar los días originales de la novedad al balance actual para comparar correctamente
                $originalDays = 0;
                $excludeId = $this->input('edit_novedad_id');
                if ($excludeId) {
                    $originalDays = DB::table('novedad')->where('id_novedad', $excludeId)->value('cantidad_dias') ?? 0;
                }

                if ($dias > ($balance + $originalDays)) {
                    $disponible = $balance + $originalDays;
                    $validator->errors()->add(
                        'dias',
                        "Saldo insuficiente: El empleado solo tiene {$disponible} días de vacaciones disponibles."
                    );
                }
            }

            // Validación: Conflictos de Traslado (No coexistir con SLN)
            if (in_array($tipo, ['TDE', 'TAE', 'TDP', 'TAP'], true) && $fechaInicio && $fechaFin && $empleadoDoc) {
                $excludeId = $this->input('edit_novedad_id');
                $conflictoSLN = DB::table('novedad')
                    ->where('empleado_id', $empleadoDoc)
                    ->where('tipo_novedad_codigo', 'SLN')
                    ->where(function ($q) {
                        $q->where('estado', '!=', 'cerrada')->orWhereNull('estado');
                    })
                    ->whereDate('fecha_inicio', '<=', $fechaFin)
                    ->whereDate('fecha_fin', '>=', $fechaInicio)
                    ->when($excludeId, function ($q, $id) {
                        $q->where('id_novedad', '!=', $id);
                    })
                    ->exists();

                if ($conflictoSLN) {
                    $validator->errors()->add(
                        'tipo_novedad',
                        "No se puede registrar este traslado porque coincide con un periodo de Suspensión (SLN) activo para el empleado."
                    );
                }
            }

        });
    }

    private function hasExistingMedicalSupport(): bool
    {
        if ($this->routeIs('novedades.calculo.preview')) {
            return false;
        }

        $novedadId = $this->route('id_novedad') ?? $this->input('edit_novedad_id');

        if (!$novedadId || !is_numeric($novedadId)) {
            return false;
        }

        return Novedad::query()
            ->whereKey((int) $novedadId)
            ->whereNotNull('soporte_medico_path')
            ->exists();
    }

    private function normalizeTipoNovedad($value): string
    {
        $raw = strtolower(trim((string) ($value ?? '')));
        $ascii = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $raw);
        $slug = preg_replace('/[^a-z0-9]+/', '_', $ascii);
        $slug = trim((string) $slug, '_');

        return match ($slug) {
            'tde' => 'TDE',
            'tae' => 'TAE',
            'tdp' => 'TDP',
            'tap' => 'TAP',
            'vsp' => 'VSP',
            'vst' => 'VST',
            'sln', 'suspension', 'suspension_contrato', 'ausencia_injustificada' => 'SLN',
            'ige', 'incapacidad', 'incapacidad_enfermedad', 'incapacidad_enfermedad_general' => 'IGE',
            'irl', 'incapacidad_laboral', 'incapacidad_laboral_arl' => 'IRL',
            'lmat', 'licencia_maternidad', 'licencia_de_maternidad' => 'LMAT',
            'lpat', 'licencia_paternidad', 'licencia_de_paternidad' => 'LPAT',
            'vac', 'vacaciones' => 'VAC',
            'vct' => 'VCT',
            'inc' => 'INC',
            'ret', 'retiro', 'terminacion', 'terminacion_contrato' => 'RET',
            'lic', 'licencia_por_luto', 'licencia_luto', 'licencia_remunerada', 'licencia_no_remunerada', 'licencia', 'calamidad_domestica', 'permiso_remunerado', 'permiso_no_remunerado', 'permiso', 'cita_medica' => 'LIC',
            default => strtoupper($slug),
        };
    }

    private function normalizeNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d,.-]/', '', (string) $value);
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return is_numeric($normalized) ? (float) $normalized : null;
    }
}
