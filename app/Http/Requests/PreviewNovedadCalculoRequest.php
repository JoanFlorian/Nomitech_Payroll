<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreviewNovedadCalculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empleado_id' => 'bail|required|string',
            'tipo_novedad' => 'bail|required|string',
            'unidad_cantidad' => 'bail|required|in:dias,horas',
            'dias' => 'bail|nullable|numeric|min:0|max:30|required_if:unidad_cantidad,dias|prohibited_unless:unidad_cantidad,dias',
            'horas' => 'bail|nullable|numeric|min:0|max:240|required_if:unidad_cantidad,horas|prohibited_unless:unidad_cantidad,horas',
            'pago_manual' => 'bail|nullable|numeric|min:-999999999.99|max:999999999.99',
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
