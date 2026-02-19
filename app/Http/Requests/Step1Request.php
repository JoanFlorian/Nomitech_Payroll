<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'id_tipo_doc' => 'bail|required|integer|max:4',

            // Número documento
            'numero_documento' => 'bail|required|digits_between:5,15',

            // PRIMER APELLIDO
            'primer_apellido' => 'bail|required|string|min:2|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/',

            // SEGUNDO APELLIDO (opcional)
            'segundo_apellido' => 'bail|nullable|string|min:2|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/',

            // PRIMER NOMBRE
            'primer_nombre' => 'bail|required|string|min:2|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/',

            // OTROS NOMBRES (opcional)
            'otros_nombres' => 'bail|nullable|string|min:2|max:50|regex:/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/',

            // SELECTS
            'departamento' => 'bail|required|integer',
            'ciudad'       => 'bail|required|integer',

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
            'id_tipo_doc.max'      => 'El tipo de documento no es válido.',

            /*
            |--------------------------------------------------------------------------
            | NUMERO DOCUMENTO
            |--------------------------------------------------------------------------
            */
            'numero_documento.required'       => 'El número de documento es obligatorio.',
            'numero_documento.digits_between' => 'El número de documento debe tener entre 5 y 15 dígitos.',

            /*
            |--------------------------------------------------------------------------
            | PRIMER APELLIDO
            |--------------------------------------------------------------------------
            */
            'primer_apellido.required' => 'El primer apellido es obligatorio.',
            'primer_apellido.min'      => 'El primer apellido debe tener mínimo 4 caracteres.',
            'primer_apellido.max'      => 'El primer apellido no puede superar 30 caracteres.',
            'primer_apellido.regex'    => 'El primer apellido solo puede contener letras y espacios.',

            /*
            |--------------------------------------------------------------------------
            | SEGUNDO APELLIDO
            |--------------------------------------------------------------------------
            */
            'segundo_apellido.min'   => 'El segundo apellido debe tener mínimo 4 caracteres.',
            'segundo_apellido.max'   => 'El segundo apellido no puede superar 30 caracteres.',
            'segundo_apellido.regex' => 'El segundo apellido solo puede contener letras y espacios.',

            /*
            |--------------------------------------------------------------------------
            | PRIMER NOMBRE
            |--------------------------------------------------------------------------
            */
            'primer_nombre.required' => 'El primer nombre es obligatorio.',
            'primer_nombre.min'      => 'El primer nombre debe tener mínimo 2 caracteres.',
            'primer_nombre.max'      => 'El primer nombre no puede superar 30 caracteres.',
            'primer_nombre.regex'    => 'El primer nombre solo puede contener letras y espacios.',

            /*
            |--------------------------------------------------------------------------
            | OTROS NOMBRES
            |--------------------------------------------------------------------------
            */
            'otros_nombres.min'   => 'Los otros nombres deben tener mínimo 5 caracteres.',
            'otros_nombres.max'   => 'Los otros nombres no pueden superar 50 caracteres.',
            'otros_nombres.regex' => 'Los otros nombres solo pueden contener letras y espacios.',

            /*
            |--------------------------------------------------------------------------
            | DEPARTAMENTO
            |--------------------------------------------------------------------------
            */
            'departamento.required' => 'El departamento es obligatorio.',
            'departamento.integer'  => 'Debe seleccionar un departamento válido.',

            /*
            |--------------------------------------------------------------------------
            | CIUDAD
            |--------------------------------------------------------------------------
            */
            'ciudad.required' => 'La ciudad es obligatoria.',
            'ciudad.integer'  => 'Debe seleccionar una ciudad válida.',

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
}
