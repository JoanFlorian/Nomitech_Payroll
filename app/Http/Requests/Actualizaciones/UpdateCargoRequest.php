<?php

namespace App\Http\Requests\Actualizaciones;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCargoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nombre' => 'bail|required|string|regex:/^[A-Za-zÁ-Úá-úñÑ\s]+$/|min:2|max:100|unique:rol,nombre,' . $id . ',id_rol',
            'descripcion' => 'bail|nullable|string|regex:/^[A-Za-zÁ-Úá-úñÑ0-9\s]+$/|min:5|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.min' => 'El nombre del rol debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre del rol no puede tener más de 100 caracteres.',
            'nombre.regex' => 'El nombre solo debe contener letras y espacios.',
            'nombre.unique' => 'El nombre del rol ya se encuentra registrado.',

            'descripcion.min' => 'La descripción debe tener al menos 5 letras o números.',
            'descripcion.max' => 'La descripción no puede exceder 255 caracteres.',
            'descripcion.regex' => 'La descripción solo puede tener letras, espacios y números.',
        ];
    }
}
