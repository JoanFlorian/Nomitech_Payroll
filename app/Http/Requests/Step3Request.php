<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Step3Request extends FormRequest
{


    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_forma_pago'   => 'required|integer|exists:forma_pago,id_forma_pago',
            'id_metodo_pago'  => 'required|integer|exists:metodo_pago,id_metodo_pago',
            'tipo_cuenta'     => 'required|integer|exists:tipo_cuenta,id_tipo_cuenta',
            'numero_cuenta'   => 'required|string|max:20|regex:/^[0-9]{6,20}$/',
            'id_eps'          => 'required|integer|exists:eps,id_eps',
            'id_afp'          => 'required|integer|exists:afp,id_afp',
        ];
    }

    public function messages(): array
    {
        return [
            'id_forma_pago.required'   => 'Debe seleccionar la forma de pago.',
            'id_forma_pago.integer'    => 'La forma de pago no es válida.',
            'id_forma_pago.exists'     => 'La forma de pago seleccionada no existe.',

            'id_metodo_pago.required'  => 'Debe seleccionar el método de pago.',
            'id_metodo_pago.integer'   => 'El método de pago no es válido.',
            'id_metodo_pago.exists'    => 'El método de pago seleccionado no existe.',

            'tipo_cuenta.required'     => 'Debe seleccionar el tipo de cuenta.',
            'tipo_cuenta.integer'      => 'El tipo de cuenta no es válido.',
            'tipo_cuenta.exists'       => 'El tipo de cuenta seleccionada no existe.',

            'numero_cuenta.required'   => 'Debe ingresar el número de cuenta.',
            'numero_cuenta.string'     => 'El número de cuenta debe ser texto.',
            'numero_cuenta.max'        => 'El número de cuenta no puede superar 20 caracteres.',
            'numero_cuenta.regex'      => 'El número de cuenta debe tener entre 6 y 20 dígitos numéricos.',

            'id_eps.required'          => 'Debe seleccionar la EPS.',
            'id_eps.integer'           => 'La EPS no es válida.',
            'id_eps.exists'            => 'La EPS seleccionada no existe.',

            'id_afp.required'          => 'Debe seleccionar la AFP.',
            'id_afp.integer'           => 'La AFP no es válida.',
            'id_afp.exists'            => 'La AFP seleccionada no existe.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
