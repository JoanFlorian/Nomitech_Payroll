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
            'codigo' => 'bail|required|string|regex:/^[0-9]{8}$/|unique:ciudad,codigo',
            'nombre' => 'bail|required|string|regex:/^[A-Za-zÁ-Úá-úñÑ\s]+$/|min:2|max:100|unique:ciudad,nombre',
            'id_departamento' => 'bail|required|exists:departamento,id_departamento',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.regex' => 'El código debe tener exactamente 8 dígitos numéricos.',
            'codigo.unique' => 'El código ya se encuentra registrado.',

            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.regex' => 'El nombre solo debe contener letras y espacios.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique' => 'El nombre ya se encuentra registrado.',

            'id_departamento.required' => 'El departamento es obligatorio.',
            'id_departamento.exists' => 'El departamento seleccionado no es válido.',
        ];
    }
}
