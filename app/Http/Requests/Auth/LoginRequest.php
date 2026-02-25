<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correo' => ['required', 'string', 'email', 'max:255'],
            'contrasena' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.string' => 'El correo electrónico debe ser texto válido.',
            'correo.email' => 'El correo electrónico no es válido.',
            'correo.max' => 'El correo electrónico no puede superar los 255 caracteres.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.string' => 'La contraseña debe ser texto válido.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
