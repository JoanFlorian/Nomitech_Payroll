<?php

namespace App\Http\Requests\Actualizaciones;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormaPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nombre' => 'bail|required|string|regex:/^[A-Za-zÁ-Úá-úñÑ\s]+$/|min:2|max:50|unique:forma_pago,nombre,' . $id . ',id_forma_pago',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres.',
            'nombre.regex' => 'El nombre solo debe contener letras y espacios.',
            'nombre.unique' => 'El nombre ya se encuentra registrado.',
        ];
    }
}
