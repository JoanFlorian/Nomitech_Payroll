<?php

namespace App\Http\Requests\Actualizaciones;

use Illuminate\Foundation\Http\FormRequest;

class StoreCiudadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => 'bail|required|string|regex:/^[0-9]+$/|min:2|max:11|unique:ciudad,codigo',
            'nombre' => 'bail|required|string|regex:/^[A-Za-zÁ-Úá-úñÑ\s]+$/|min:2|max:100|unique:ciudad,nombre',
            'cod_dep' => 'bail|required|exists:departamento,codigo',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.regex' => 'El código debe contener solo números positivos.',
            'codigo.min' => 'El código debe tener al menos 2 dígitos.',
            'codigo.max' => 'El código no puede tener más de 11 dígitos.',
            'codigo.unique' => 'El código ya se encuentra registrado.',

            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.regex' => 'El nombre solo debe contener letras y espacios.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique' => 'El nombre ya se encuentra registrado.',

            'cod_dep.required' => 'El departamento es obligatorio.',
            'cod_dep.exists' => 'El departamento seleccionado no es válido.',
        ];
    }
}
