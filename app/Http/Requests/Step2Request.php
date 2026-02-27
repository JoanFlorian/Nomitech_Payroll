<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class Step2Request extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // FECHAS
            'fecha_inicio' => 'bail|required|date',
            'fecha_fin' => 'bail|nullable|date|after_or_equal:fecha_inicio',

            // HORAS
            'horas_diarias' => 'bail|required|integer|min:1|max:12',

            // SELECTS
            'id_tipo_trabajador'      => 'bail|required|integer|exists:tipo_trabajador,id_tipo_trabajador',
            'id_sub_tipo_trabajador'  => 'bail|required|integer|exists:sub_tipo_trabajador,id_sub_tipo_trabajador',
            'id_tipo_contrato'        => 'bail|required|integer|exists:tipo_contrato,id_tipo_contrato',
            'id_arl'                  => 'bail|required|integer|exists:arl,id_arl',

            // SALARIO
            'salario' => 'bail|required|numeric|min:0.01|max:999999999',

            // NIVEL RIESGO
            'nivel_riesgo' => 'bail|required|in:Nivel I,Nivel II,Nivel III,Nivel IV,Nivel V',

            // CODIGO INTERNO
            'codigo_interno' => 'bail|required|string|min:3|max:20|regex:/^[0-9]+$/',

            // BOOLEAN CHECK - alto_riesgo (checkbox)
            'alto_riesgo' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | FECHA INICIO
            |--------------------------------------------------------------------------
            */
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date'     => 'Debe ingresar una fecha válida.',

            /*
            |--------------------------------------------------------------------------
            | FECHA FIN
            |--------------------------------------------------------------------------
            */
            'fecha_fin.date' => 'Debe ingresar una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha fin no puede ser menor que la fecha de inicio.',

            /*
            |--------------------------------------------------------------------------
            | HORAS DIARIAS
            |--------------------------------------------------------------------------
            */
            'horas_diarias.required' => 'Las horas diarias son obligatorias.',
            'horas_diarias.integer'  => 'Las horas diarias deben ser un número entero.',
            'horas_diarias.min'      => 'Debe trabajar mínimo 1 hora diaria.',
            'horas_diarias.max'      => 'No puede superar 12 horas diarias.',

            /*
            |--------------------------------------------------------------------------
            | TIPO TRABAJADOR
            |--------------------------------------------------------------------------
            */
            'id_tipo_trabajador.required' => 'Debe seleccionar el tipo de trabajador.',
            'id_tipo_trabajador.integer'  => 'Debe seleccionar un tipo de trabajador válido.',
            'id_tipo_trabajador.exists'   => 'El tipo de trabajador seleccionado no existe.',

            /*
            |--------------------------------------------------------------------------
            | SUB TIPO TRABAJADOR
            |--------------------------------------------------------------------------
            */
            'id_sub_tipo_trabajador.required' => 'Debe seleccionar el sub tipo de trabajador.',
            'id_sub_tipo_trabajador.integer'  => 'Debe seleccionar un sub tipo válido.',
            'id_sub_tipo_trabajador.exists'   => 'El sub tipo de trabajador seleccionado no existe.',

            /*
            |--------------------------------------------------------------------------
            | TIPO CONTRATO
            |--------------------------------------------------------------------------
            */
            'id_tipo_contrato.required' => 'Debe seleccionar el tipo de contrato.',
            'id_tipo_contrato.integer'  => 'Debe seleccionar un tipo de contrato válido.',
            'id_tipo_contrato.exists'   => 'El tipo de contrato seleccionado no existe.',

            /*
            |--------------------------------------------------------------------------
            | ARL
            |--------------------------------------------------------------------------
            */
            'id_arl.required' => 'Debe seleccionar la ARL.',
            'id_arl.integer'  => 'Debe seleccionar una ARL válida.',
            'id_arl.exists'   => 'La ARL seleccionada no existe.',

            /*
            |--------------------------------------------------------------------------
            | SALARIO
            |--------------------------------------------------------------------------
            */
            'salario.required' => 'El salario es obligatorio.',
            'salario.numeric'  => 'El salario debe ser un valor numérico.',
            'salario.min'      => 'El salario debe ser mayor que cero.',
            'salario.max'      => 'El salario es demasiado alto.',

            /*
            |--------------------------------------------------------------------------
            | NIVEL DE RIESGO
            |--------------------------------------------------------------------------
            */
            'nivel_riesgo.required' => 'Debe seleccionar el nivel de riesgo.',
            'nivel_riesgo.in'       => 'El nivel de riesgo seleccionado no es válido.',

            /*
            |--------------------------------------------------------------------------
            | CÓDIGO INTERNO
            |--------------------------------------------------------------------------
            */
            'codigo_interno.required' => 'El código interno es obligatorio.',
            'codigo_interno.min'      => 'El código interno debe tener mínimo 3 caracteres.',
            'codigo_interno.max'      => 'El código interno no puede superar 20 caracteres.',
            'codigo_interno.regex'    => 'El código interno solo puede contener números.',
        ];
    }

    /**
     * Prepare the data for validation. Ensure alto_riesgo is always 0 or 1
     * regardless of whether the checkbox was checked.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'alto_riesgo' => $this->has('alto_riesgo') ? 1 : 0,
        ]);
    }

    /**
     * Always respond with JSON errors so AJAX front‑end can parse them.
     */
    protected function failedValidation($validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}

