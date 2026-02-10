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
            'correo' => ['required', 'string', 'email', 'max:256'],
            'contrasena' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'correo.required' => 'El correo es requerido.',
            'contrasena.required' => 'La contraseña es requerida.',
        ];
    }
}
