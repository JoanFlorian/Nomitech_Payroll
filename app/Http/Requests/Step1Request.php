<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class Step1Request extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Tipo documento
            'id_tipo_doc' => 'bail|required|integer|exists:tipo_doc,id_tipo_doc',

            // Número documento (se mapea a 'doc')
            'doc' => 'bail|required|digits_between:5,15|unique:usuario,doc',

            // PRIMER APELLIDO
            'primer_apellido' => 'bail|required|string|min:3|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/',

            // SEGUNDO APELLIDO (opcional)
            'segundo_apellido' => 'bail|nullable|string|min:3|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/',

            // PRIMER NOMBRE
            'primer_nombre' => 'bail|required|string|min:3|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/',

            // OTROS NOMBRES (opcional)
            'otros_nombres' => 'bail|nullable|string|min:3|max:50|regex:/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/',

            // CONTACTO
            'email' => 'bail|required|email|max:255|unique:usuario,correo',
            'telefono' => 'bail|required|digits_between:7,15|regex:/^[0-9]+$/',

            // SELECTS - Mapear a id_ciudad
            'departamento' => 'bail|required|integer|exists:departamento,id_departamento',
            'ciudad'       => 'bail|required|integer|exists:ciudad,id_ciudad',

            // DIRECCIÓN
            'direccion' => 'bail|required|string|min:5|max:100',
        ];
    }

    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | TIPO DOCUMENTO
            |--------------------------------------------------------------------------
            */
            'id_tipo_doc.required' => 'El tipo de documento es obligatorio.',
            'id_tipo_doc.integer'  => 'El tipo de documento no es válido.',
            'id_tipo_doc.exists'   => 'El tipo de documento seleccionado no es válido.',

            /*
            |--------------------------------------------------------------------------
            | NUMERO DOCUMENTO
            |--------------------------------------------------------------------------
            */
            'doc.required'       => 'El número de documento es obligatorio.',
            'doc.digits_between' => 'El número de documento debe tener entre 5 y 15 dígitos.',
            'doc.unique'         => 'Este número de documento ya está registrado en el sistema.',

            /*
            |--------------------------------------------------------------------------
            | PRIMER APELLIDO
            |--------------------------------------------------------------------------
            */
            'primer_apellido.required' => 'El primer apellido es obligatorio.',
            'primer_apellido.min'      => 'El primer apellido debe tener mínimo 3 caracteres.',
            'primer_apellido.max'      => 'El primer apellido no puede superar 30 caracteres.',
            'primer_apellido.regex'    => 'El primer apellido solo puede contener letras y espacios.',

            /*
            |--------------------------------------------------------------------------
            | SEGUNDO APELLIDO
            |--------------------------------------------------------------------------
            */
            'segundo_apellido.min'   => 'El segundo apellido debe tener mínimo 3 caracteres.',
            'segundo_apellido.max'   => 'El segundo apellido no puede superar 30 caracteres.',
            'segundo_apellido.regex' => 'El segundo apellido solo puede contener letras y espacios.',

            /*
            |--------------------------------------------------------------------------
            | PRIMER NOMBRE
            |--------------------------------------------------------------------------
            */
            'primer_nombre.required' => 'El primer nombre es obligatorio.',
            'primer_nombre.min'      => 'El primer nombre debe tener mínimo 3 caracteres.',
            'primer_nombre.max'      => 'El primer nombre no puede superar 30 caracteres.',
            'primer_nombre.regex'    => 'El primer nombre solo puede contener letras y espacios.',

            /*
            |--------------------------------------------------------------------------
            | OTROS NOMBRES
            |--------------------------------------------------------------------------
            */
            'otros_nombres.min'   => 'Los otros nombres deben tener mínimo 3 caracteres.',
            'otros_nombres.max'   => 'Los otros nombres no pueden superar 50 caracteres.',
            'otros_nombres.regex' => 'Los otros nombres solo pueden contener letras y espacios.',

            /*
            |--------------------------------------------------------------------------
            | EMAIL Y TELÉFONO
            |--------------------------------------------------------------------------
            */
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'Debe ingresar un correo electrónico válido.',
            'email.max'      => 'El correo no puede superar 255 caracteres.',
            'email.unique'   => 'El correo electrónico ya está registrado en el sistema.',
            'telefono.required'       => 'El teléfono es obligatorio.',
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 15 dígitos.',
            'telefono.regex'          => 'El teléfono solo puede contener números.',

            /*
            |--------------------------------------------------------------------------
            | DEPARTAMENTO
            |--------------------------------------------------------------------------
            */
            'departamento.required' => 'El departamento es obligatorio.',
            'departamento.integer'  => 'Debe seleccionar un departamento válido.',
            'departamento.exists'   => 'El departamento seleccionado no es válido.',

            /*
            |--------------------------------------------------------------------------
            | CIUDAD
            |--------------------------------------------------------------------------
            */
            'ciudad.required' => 'La ciudad es obligatoria.',
            'ciudad.integer'  => 'Debe seleccionar una ciudad válida.',
            'ciudad.exists'   => 'La ciudad seleccionada no es válida.',

            /*
            |--------------------------------------------------------------------------
            | DIRECCIÓN
            |--------------------------------------------------------------------------
            */
            'direccion.required' => 'La dirección es obligatoria.',
            'direccion.min'      => 'La dirección debe tener mínimo 5 caracteres.',
            'direccion.max'      => 'La dirección no puede superar 100 caracteres.',
        ];
    }
    /**
     * Always respond with JSON errors for AJAX clients.
     * Prevents a redirect when validation fails.
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
