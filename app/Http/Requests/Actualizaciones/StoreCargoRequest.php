<?php

namespace App\Http\Requests\Actualizaciones;

use Illuminate\Foundation\Http\FormRequest;

class StoreCargoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'bail|required|string|regex:/^[A-Za-zÁ-Úá-úñÑ\s]+$/|min:2|max:100|unique:rol,nombre',
            'descripcion' => 'bail|nullable|string|regex:/^[a-zA-Z0-9\s]+$/|min:2|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.regex' => 'El nombre solo debe contener letras y espacios.',
            'nombre.unique' => 'El nombre ya se encuentra registrado.',

            'descripcion.min' => 'La descripción debe tener al menos 2 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 255 caracteres.',
            'descripcion.regex' => 'La descripción contiene caracteres no permitidos.',
        ];
    }
}
