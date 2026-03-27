<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Step3Request extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $fields = ['prima_inicial', 'cesantias_inicial', 'intereses_inicial', 'vacaciones_inicial'];
        foreach ($fields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                if (is_string($value)) {
                    // Normalize: remove thousands dots, replace decimal comma with dot
                    $normalized = str_replace('.', '', $value);
                    $normalized = str_replace(',', '.', $normalized);
                    $this->merge([$field => $normalized]);
                }
            }
        }
    }


    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_forma_pago' => 'required|integer|exists:forma_pago,id_forma_pago',
            'id_banco' => 'required|integer|exists:banco,id_banco',
            'tipo_cuenta' => 'nullable|integer|exists:tipo_cuenta,id_tipo_cuenta',
            'numero_cuenta' => 'nullable|string|max:20|regex:/^[0-9]{6,20}$/',
            'id_eps' => 'required|integer|exists:eps,id_eps',
            'id_afp' => 'required|integer|exists:afp,id_afp',
            'id_caja' => 'required|integer|exists:cajas_compensacion,id_caja',
            'fondo_cesantias' => 'nullable|string|max:100',

            // Saldos iniciales de prestaciones (opcionales)
            'prima_inicial' => 'nullable|numeric|min:0|max:999999999',
            'cesantias_inicial' => 'nullable|numeric|min:0|max:9999999999',
            'intereses_inicial' => 'nullable|numeric|min:0',
            'vacaciones_inicial' => 'nullable|numeric|min:0|max:180',
        ];
    }

    public function messages(): array
    {
        return [
            'id_forma_pago.required' => 'Debe seleccionar la forma de pago.',
            'id_forma_pago.integer' => 'La forma de pago no es válida.',
            'id_forma_pago.exists' => 'La forma de pago seleccionada no existe.',

            'id_banco.required' => 'Debe seleccionar el banco.',
            'id_banco.integer' => 'El banco no es válido.',
            'id_banco.exists' => 'El banco seleccionado no existe.',

            'tipo_cuenta.required' => 'Debe seleccionar el tipo de cuenta.',
            'tipo_cuenta.integer' => 'El tipo de cuenta no es válido.',
            'tipo_cuenta.exists' => 'El tipo de cuenta seleccionada no existe.',

            'numero_cuenta.required' => 'Debe ingresar el número de cuenta.',
            'numero_cuenta.string' => 'El número de cuenta debe ser texto.',
            'numero_cuenta.max' => 'El número de cuenta no puede superar 20 caracteres.',
            'numero_cuenta.regex' => 'El número de cuenta debe tener entre 6 y 20 dígitos numéricos.',

            'id_eps.required' => 'Debe seleccionar la EPS.',
            'id_eps.integer' => 'La EPS no es válida.',
            'id_eps.exists' => 'La EPS seleccionada no existe.',

            'id_afp.required' => 'Debe seleccionar la AFP.',
            'id_afp.integer' => 'La AFP no es válida.',
            'id_afp.exists' => 'La AFP seleccionada no existe.',

            'id_caja.required' => 'Debe seleccionar la Caja de Compensación.',
            'id_caja.integer' => 'La Caja de Compensación no es válida.',
            'id_caja.exists' => 'La Caja de Compensación seleccionada no existe.',

            'fondo_cesantias.string' => 'El fondo de cesantías debe ser texto.',
            'fondo_cesantias.max' => 'El fondo de cesantías no puede superar 100 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
