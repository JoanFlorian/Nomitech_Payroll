<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePerfilRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'primer_nombre'    => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'otros_nombres'    => 'nullable|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'primer_apellido'  => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'segundo_apellido' => 'nullable|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'id_tipo_doc'      => 'required|integer|exists:tipo_documento,id_tipo_doc',
            'telefono'         => 'required|numeric|digits_between:7,15',
            'correo'           => 'required|email|max:255',
            'direccion'        => 'required|string|max:255',
            'id_ciudad'        => 'required|integer|exists:ciudad,id_ciudad',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'primer_nombre.required'    => 'El primer nombre es obligatorio',
            'primer_nombre.regex'       => 'El primer nombre solo debe contener letras',
            'primer_apellido.required'  => 'El primer apellido es obligatorio',
            'primer_apellido.regex'     => 'El primer apellido solo debe contener letras',
            'otros_nombres.regex'       => 'Los otros nombres solo deben contener letras',
            'segundo_apellido.regex'    => 'El segundo apellido solo debe contener letras',
            'id_tipo_doc.required'      => 'El tipo de documento es obligatorio',
            'id_tipo_doc.exists'        => 'El tipo de documento seleccionado no es válido',
            'telefono.required'         => 'El teléfono es obligatorio',
            'telefono.numeric'          => 'El teléfono debe ser numérico',
            'telefono.digits_between'   => 'El teléfono debe tener al menos 7 dígitos',
            'correo.required'           => 'El correo electrónico es obligatorio',
            'correo.email' => 'El correo electrónico debe ser válido',
            'direccion.required' => 'La dirección es obligatoria',
            'id_ciudad.required' => 'La ciudad es obligatoria',
            'id_ciudad.exists' => 'La ciudad seleccionada no es válida',
        ];
    }
}
