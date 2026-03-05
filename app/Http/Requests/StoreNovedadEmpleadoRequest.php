<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNovedadEmpleadoRequest extends FormRequest
{
    private const TIPOS_NOVEDAD = [
        'incapacidad_enfermedad_general',
        'incapacidad_laboral_arl',
        'licencia_maternidad',
        'licencia_paternidad',
        'licencia_remunerada',
        'licencia_no_remunerada',
        'permiso_remunerado',
        'permiso_no_remunerado',
        'suspension_contrato',
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
            'dias' => 'bail|nullable|numeric|min:0|max:30|required_if:unidad_cantidad,dias|prohibited_unless:unidad_cantidad,dias',
            'horas' => 'bail|nullable|numeric|min:0|max:240|required_if:unidad_cantidad,horas|prohibited_unless:unidad_cantidad,horas',
            'fecha_inicio' => 'bail|required|date',
            'fecha_fin' => 'bail|required|date|after_or_equal:fecha_inicio',
            'observaciones' => 'bail|nullable|string|max:500',
            'pago_manual' => 'bail|nullable|numeric|min:-999999999.99|max:999999999.99',
            'es_remunerado' => 'bail|nullable|boolean',
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
            'dias.max' => 'Los días no pueden superar 30 por novedad.',
            'horas.max' => 'Las horas no pueden superar 240 por novedad.',
            'dias.required_if' => 'Debe ingresar la cantidad en días.',
            'horas.required_if' => 'Debe ingresar la cantidad en horas.',
            'dias.prohibited_unless' => 'Si selecciona horas, no debe diligenciar días.',
            'horas.prohibited_unless' => 'Si selecciona días, no debe diligenciar horas.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $tipoNovedad = $this->normalizeTipoNovedad($this->input('tipo_novedad'));

        $dias = $this->normalizeNumeric($this->input('dias', $this->input('cantidad_dias')));
        $horas = $this->normalizeNumeric($this->input('horas', $this->input('cantidad_horas')));

        $this->merge([
            'empleado_id' => $this->input('empleado_id', $this->input('doc_empleado')),
            'tipo_novedad' => $tipoNovedad,
            'dias' => $dias,
            'horas' => $horas,
            'unidad_cantidad' => $this->input('unidad_cantidad', ($horas > 0 ? 'horas' : 'dias')),
            'es_remunerado' => $this->boolean('es_remunerado', $this->boolean('licencia_remunerada', false)),
            'pago_manual' => $this->normalizeNumeric($this->input('pago_manual', $this->input('pago'))),
        ]);
    }

    private function normalizeTipoNovedad($value): string
    {
        $raw = strtolower(trim((string) ($value ?? '')));
        $ascii = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $raw);
        $slug = preg_replace('/[^a-z0-9]+/', '_', $ascii);
        $slug = trim((string) $slug, '_');

        return match ($slug) {
            'incapacidad', 'incapacidad_enfermedad', 'incapacidad_enfermedad_general' => 'incapacidad_enfermedad_general',
            'incapacidad_laboral', 'incapacidad_laboral_arl' => 'incapacidad_laboral_arl',
            'licencia_maternidad' => 'licencia_maternidad',
            'licencia_paternidad' => 'licencia_paternidad',
            'licencia_remunerada' => 'licencia_remunerada',
            'licencia', 'licencia_no_remunerada' => 'licencia_no_remunerada',
            'permiso_remunerado' => 'permiso_remunerado',
            'permiso', 'permiso_no_remunerado' => 'permiso_no_remunerado',
            'suspension', 'suspension_contrato' => 'suspension_contrato',
            default => $slug,
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
