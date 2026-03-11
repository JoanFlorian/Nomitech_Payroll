<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNovedadEmpleadoRequest extends FormRequest
{
    private const DIAS_MAXIMO_GENERAL = 30;
    private const DIAS_MAXIMO_MATERNIDAD = 126;
    private const DIAS_MAXIMO_PATERNIDAD = 14;

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
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = (int) session('empresa_id');

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
            'dias' => 'bail|nullable|integer|min:0|max:126',
            'horas' => 'bail|nullable|integer|min:0|max:240',
            'fecha_inicio' => 'bail|required|date',
            'fecha_fin' => 'bail|required|date|after_or_equal:fecha_inicio',
            'observaciones' => 'bail|nullable|string|max:500',
            'pago_manual' => 'bail|nullable|numeric|min:0|max:999999999.99',
            'valor_manual' => 'bail|nullable|numeric|min:0|max:999999999.99',
            'salario_base' => 'bail|required|numeric|min:1',
            'es_remunerado' => 'bail|nullable|boolean',
            'tipo_licencia' => 'bail|nullable|string|in:luto,calamidad_domestica,permiso_especial,remunerada,no_remunerada',
            'certificado_medico' => 'bail|nullable|boolean',
            'id_eps' => 'bail|nullable|integer|exists:eps,id_eps',
            'id_afp' => 'bail|nullable|integer|exists:afp,id_afp',
            'id_arl' => 'bail|nullable|integer|exists:arl,id_arl',
        ];
    }

    public function messages(): array
    {
        return [
            'empleado_id.required' => 'Debe seleccionar un empleado.',
            'empleado_id.exists' => 'El empleado seleccionado no es válido.',
            'tipo_novedad.required' => 'Debe seleccionar el tipo de novedad.',
            'tipo_novedad.in' => 'El tipo de novedad seleccionado no es válido.',
            'dias.numeric' => 'Los días deben ser numéricos.',
            'horas.numeric' => 'Las horas deben ser numéricas.',
            'dias.min' => 'Los días no pueden ser negativos.',
            'horas.min' => 'Las horas no pueden ser negativas.',
            'dias.max' => 'Los días no pueden superar 126 por novedad.',
            'horas.max' => 'Las horas no pueden superar 240 por novedad.',
            'salario_base.required' => 'El salario base es obligatorio.',
            'salario_base.min' => 'El salario base debe ser mayor a 0.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $tipoNovedad = $this->normalizeTipoNovedad($this->input('tipo_novedad'));

        $dias = $this->normalizeNumeric($this->input('dias', $this->input('cantidad_dias')));
        $horas = $this->normalizeNumeric($this->input('horas', $this->input('cantidad_horas')));

        $unidadCantidad = $this->input('unidad_cantidad', ($horas > 0 ? 'horas' : 'dias'));

        if (in_array($tipoNovedad, ['TDE', 'TAE', 'TDP', 'TAP', 'VSP', 'VST', 'VCT'], true)) {
            $dias = null;
            $horas = null;
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
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $tipo = $this->input('tipo_novedad');
            $unidad = $this->input('unidad_cantidad');
            $dias = (float) ($this->input('dias') ?? 0);
            $horas = (float) ($this->input('horas') ?? 0);
            $pagoManual = $this->input('valor_manual', $this->input('pago_manual'));
            $tipoLicencia = strtolower(trim((string) $this->input('tipo_licencia')));
            $certificadoMedico = filter_var($this->input('certificado_medico', false), FILTER_VALIDATE_BOOLEAN);

            $requiereCantidad = !in_array($tipo, ['TDE', 'TAE', 'TDP', 'TAP', 'VSP', 'VST', 'VCT'], true);

            if ($requiereCantidad && $unidad === 'dias' && $dias <= 0) {
                $validator->errors()->add('dias', 'Debe ingresar la cantidad en días.');
            }

            if ($requiereCantidad && $unidad === 'horas' && $horas <= 0) {
                $validator->errors()->add('horas', 'Debe ingresar la cantidad en horas.');
            }

            if ($requiereCantidad && !in_array($unidad, ['dias', 'horas'], true)) {
                $validator->errors()->add('unidad_cantidad', 'Debe indicar si la novedad se calcula en días u horas.');
            }

            if ($requiereCantidad && $unidad === 'dias' && $dias > self::DIAS_MAXIMO_GENERAL && !in_array($tipo, ['LMAT', 'LPAT'], true)) {
                $validator->errors()->add('dias', 'Los días no pueden superar 30 por periodo.');
            }

            if ($tipo === 'LMAT') {
                if ($unidad !== 'dias') {
                    $validator->errors()->add('unidad_cantidad', 'La licencia de maternidad solo se registra en días.');
                }

                if ($dias > (float) self::DIAS_MAXIMO_MATERNIDAD) {
                    $validator->errors()->add('dias', 'La licencia de maternidad no puede exceder 126 días.');
                }
            }

            if ($tipo === 'LPAT') {
                if ($unidad !== 'dias') {
                    $validator->errors()->add('unidad_cantidad', 'La licencia de paternidad solo se registra en días.');
                }

                if ($dias !== (float) self::DIAS_MAXIMO_PATERNIDAD) {
                    $validator->errors()->add('dias', 'La licencia de paternidad debe ser de 14 días.');
                }
            }

            if ($tipo === 'LIC' && !in_array($tipoLicencia, ['luto', 'calamidad_domestica', 'permiso_especial', 'remunerada', 'no_remunerada'], true)) {
                $validator->errors()->add('tipo_licencia', 'Debe seleccionar un tipo de licencia válido.');
            }

            if (in_array($tipo, ['IGE', 'IRL', 'INC'], true) && !$certificadoMedico) {
                $validator->errors()->add('certificado_medico', 'La incapacidad debe contar con certificado médico.');
            }

            if ($tipo === 'VSP' && ($pagoManual === null || $pagoManual === '' || (float) $pagoManual <= 0)) {
                $validator->errors()->add('valor_manual', 'Debe ingresar el nuevo salario para la variación permanente de salario.');
            }

            if (!in_array($tipo, ['VSP', 'VST'], true) && $pagoManual !== null && $pagoManual !== '' && (float) $pagoManual > 0) {
                $validator->errors()->add('valor_manual', 'Esta novedad se calcula automáticamente y no permite valor manual.');
            }

            if (in_array($tipo, ['TDE', 'TAE'], true) && !$this->filled('id_eps')) {
                $validator->errors()->add('id_eps', 'Debe seleccionar la EPS para el traslado.');
            }

            if (in_array($tipo, ['TDP', 'TAP'], true) && !$this->filled('id_afp')) {
                $validator->errors()->add('id_afp', 'Debe seleccionar la AFP para el traslado.');
            }

            if ($tipo === 'VCT' && !$this->filled('id_arl')) {
                $validator->errors()->add('id_arl', 'Debe seleccionar la ARL para la variación de centro de trabajo.');
            }

        });
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
