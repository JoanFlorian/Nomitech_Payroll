<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreviewNovedadCalculoRequest extends FormRequest
{
    private const DIAS_MAXIMO_GENERAL = 30;
    private const DIAS_MAXIMO_MATERNIDAD = 126;
    private const DIAS_MAXIMO_LUTO = 5;

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
            'dias' => 'bail|nullable|numeric|min:0|max:126|required_if:unidad_cantidad,dias|prohibited_unless:unidad_cantidad,dias',
            'horas' => 'bail|nullable|numeric|min:0|max:240|required_if:unidad_cantidad,horas|prohibited_unless:unidad_cantidad,horas',
            'pago_manual' => 'bail|nullable|numeric|min:-999999999.99|max:999999999.99',
            'es_remunerado' => 'bail|nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $tipoNovedad = $this->normalizeTipoNovedad($this->input('tipo_novedad'));
        $dias = $this->normalizeNumeric($this->input('dias', $this->input('cantidad_dias')));
        $horas = $this->normalizeNumeric($this->input('horas', $this->input('cantidad_horas')));

        $unidadCantidad = $this->input('unidad_cantidad', ($horas > 0 ? 'horas' : 'dias'));

        if ($tipoNovedad === 'licencia_maternidad') {
            $unidadCantidad = 'dias';
            $dias = self::DIAS_MAXIMO_MATERNIDAD;
            $horas = null;
        }

        if ($tipoNovedad === 'licencia_luto') {
            $unidadCantidad = 'dias';
            $horas = null;
        }

        if ($tipoNovedad === 'calamidad_domestica') {
            $unidadCantidad = 'dias';
            $horas = null;
        }

        if ($tipoNovedad === 'cita_medica') {
            $unidadCantidad = 'horas';
            $dias = null;
        }

        if ($tipoNovedad === 'ausencia_injustificada') {
            $unidadCantidad = 'dias';
            $horas = null;
        }

        $this->merge([
            'empleado_id' => $this->input('empleado_id', $this->input('doc_empleado')),
            'tipo_novedad' => $tipoNovedad,
            'dias' => $dias,
            'horas' => $horas,
            'unidad_cantidad' => $unidadCantidad,
            'pago_manual' => $this->normalizeNumeric($this->input('pago_manual', $this->input('pago'))),
            'es_remunerado' => $this->boolean('es_remunerado', $this->boolean('licencia_remunerada', false)),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $tipo = $this->input('tipo_novedad');
            $unidad = $this->input('unidad_cantidad');
            $dias = (float) ($this->input('dias') ?? 0);
            $pagoManual = $this->input('pago_manual');

            $tiposAutomaticos = [
                'incapacidad_enfermedad_general',
                'incapacidad_laboral_arl',
                'licencia_maternidad',
                'licencia_paternidad',
                'licencia_luto',
                'licencia_remunerada',
                'licencia_no_remunerada',
                'calamidad_domestica',
                'cita_medica',
                'permiso_remunerado',
                'permiso_no_remunerado',
                'ausencia_injustificada',
                'suspension_contrato',
            ];

            if ($tipo === 'licencia_maternidad') {
                if ($unidad !== 'dias') {
                    $validator->errors()->add('unidad_cantidad', 'La licencia de maternidad solo se registra en días.');
                }

                if ($dias !== (float) self::DIAS_MAXIMO_MATERNIDAD) {
                    $validator->errors()->add('dias', 'Para licencia de maternidad la cantidad debe ser exactamente 126 días.');
                }
            }

            if ($tipo !== 'licencia_maternidad' && $unidad === 'dias' && $dias > self::DIAS_MAXIMO_GENERAL) {
                $validator->errors()->add('dias', 'Los días no pueden superar 30 por novedad.');
            }

            if ($tipo === 'licencia_luto') {
                if ($unidad !== 'dias') {
                    $validator->errors()->add('unidad_cantidad', 'La licencia por luto solo se registra en días.');
                }

                if ($dias < 1 || $dias > self::DIAS_MAXIMO_LUTO) {
                    $validator->errors()->add('dias', 'Para licencia por luto los días deben estar entre 1 y 5.');
                }
            }

            if ($tipo === 'calamidad_domestica' && $unidad !== 'dias') {
                $validator->errors()->add('unidad_cantidad', 'La calamidad doméstica solo se registra en días.');
            }

            if ($tipo === 'cita_medica' && $unidad !== 'horas') {
                $validator->errors()->add('unidad_cantidad', 'La cita médica solo se registra en horas.');
            }

            if ($tipo === 'ausencia_injustificada') {
                if ($unidad !== 'dias') {
                    $validator->errors()->add('unidad_cantidad', 'La ausencia injustificada solo se registra en días.');
                }

                if ($dias < 0) {
                    $validator->errors()->add('dias', 'La ausencia injustificada no permite valores negativos.');
                }
            }

            if (in_array($tipo, $tiposAutomaticos, true) && $pagoManual !== null && $pagoManual !== '') {
                $validator->errors()->add('pago_manual', 'Esta novedad se calcula automáticamente y no admite valor manual.');
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
            'incapacidad', 'incapacidad_enfermedad', 'incapacidad_enfermedad_general' => 'incapacidad_enfermedad_general',
            'incapacidad_laboral', 'incapacidad_laboral_arl' => 'incapacidad_laboral_arl',
            'licencia_de_maternidad', 'licencia_maternidad' => 'licencia_maternidad',
            'licencia_de_paternidad', 'licencia_paternidad' => 'licencia_paternidad',
            'licencia_por_luto', 'licencia_luto' => 'licencia_luto',
            'licencia_remunerada' => 'licencia_remunerada',
            'licencia', 'licencia_no_remunerada' => 'licencia_no_remunerada',
            'calamidad_domestica' => 'calamidad_domestica',
            'cita_medica' => 'cita_medica',
            'permiso_remunerado' => 'permiso_remunerado',
            'permiso', 'permiso_no_remunerado' => 'permiso_no_remunerado',
            'ausencia_injustificada' => 'ausencia_injustificada',
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
