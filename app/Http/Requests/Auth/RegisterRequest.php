<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            // NIE032: Razón Social - Alfanumérico, Max 60 caracteres
            'razon_social' => ['nullable', 'string', 'max:60'],

            // NIE210: Primer Apellido - Alfanumérico, Max 60 caracteres
            'primer_apellido' => ['nullable', 'string', 'max:60'],

            // NIE211: Segundo Apellido - Alfanumérico, Max 60 caracteres
            'segundo_apellido' => ['nullable', 'string', 'max:60'],

            // NIE212: Primer Nombre - Alfanumérico, Max 60 caracteres
            'primer_nombre' => ['nullable', 'string', 'max:60'],

            // NIE213: Otros Nombres - Alfanumérico, Max 60 caracteres
            'otros_nombres' => ['nullable', 'string', 'max:60'],

            // NIE033: NIT - Numérico, Max 20 caracteres, sin guiones ni DV
            'nit' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20', 'unique:empresa,nit'],

            // NIE034: Dígito de Verificación - Numérico, 1 dígito exacto
            'dv' => ['required', 'numeric', 'digits:1'],

            // NIE035: País - ISO 3166-1 alpha-2, exactamente 2 caracteres
            'pais' => ['required', 'string', 'size:2', 'in:CO'],

            // NIE036: Departamento - ID interno (FK)
            'id_departamento' => ['required', 'exists:departamento,id_departamento'],

            // NIE037: Municipio/Ciudad - ID interno (FK)
            'id_ciudad' => ['required', 'exists:ciudad,id_ciudad'],

            // NIE038: Dirección - Alfanumérico, Max 255 caracteres
            'direccion_empresa' => ['required', 'string', 'max:255'],

            // Campos adicionales del sistema (no DIAN)
            'doc' => ['required', 'string', 'max:20', 'unique:usuario'],
            'id_tipo_doc' => ['required', 'exists:tipo_doc,id_tipo_doc'],
            'correo' => ['required', 'string', 'email', 'max:256', 'unique:usuario'],
            'contrasena' => ['required', 'confirmed', 'min:8'],
            'telefono' => ['required', 'string', 'max:20'],
        ];
    }

    /**
     * Get custom error messages for validator.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'nit.regex' => 'El NIT debe contener solo números, sin guiones ni espacios.',
            'nit.max' => 'El NIT no puede exceder los 20 caracteres.',
            'dv.digits' => 'El Dígito de Verificación debe tener exactamente 1 dígito.',
            'pais.size' => 'El código del país debe tener exactamente 2 caracteres.',
        ];
    }
}