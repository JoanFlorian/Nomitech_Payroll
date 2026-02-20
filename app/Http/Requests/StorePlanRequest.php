<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para crear un plan.
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|min:3|max:60|unique:plan,nombre',
            'valor' => ['required', 'numeric', 'gt:0', 'max:99999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
            'duracion' => 'required|integer|in:1,3,6,12',
            'num_empl' => 'required|integer|min:1|max:10000',
            'descripcion' => 'nullable|string|max:500',
            'destacado' => 'nullable|boolean',
            'features' => 'nullable|array|max:4',
            'features.*' => 'nullable|string|max:255',
        ];
    }

    /**
     * Mensajes de error en español.
     */
    public function messages(): array
    {
        return [
            // nombre
            'nombre.required' => 'El nombre del plan es obligatorio.',
            'nombre.string' => 'El nombre del plan debe ser una cadena de texto.',
            'nombre.min' => 'El nombre del plan debe tener al menos :min caracteres.',
            'nombre.max' => 'El nombre del plan no puede exceder :max caracteres.',
            'nombre.unique' => 'Ya existe un plan con ese nombre.',

            // valor
            'valor.required' => 'El valor del plan es obligatorio.',
            'valor.numeric' => 'El valor del plan debe ser un número válido.',
            'valor.gt' => 'El valor del plan debe ser mayor a cero.',
            'valor.max' => 'El valor del plan no puede exceder 99,999,999.99.',
            'valor.regex' => 'El valor del plan debe tener máximo 2 decimales.',

            // duracion
            'duracion.required' => 'La duración del plan es obligatoria.',
            'duracion.integer' => 'La duración del plan debe ser un número entero.',
            'duracion.in' => 'La duración del plan debe ser 1, 3, 6 o 12 meses.',

            // num_empl
            'num_empl.required' => 'El número de empleados es obligatorio.',
            'num_empl.integer' => 'El número de empleados debe ser un número entero.',
            'num_empl.min' => 'El número de empleados debe ser al menos :min.',
            'num_empl.max' => 'El número de empleados no puede exceder :max.',

            // descripcion
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'descripcion.max' => 'La descripción no puede exceder :max caracteres.',

            // features
            'features.array' => 'Las características deben ser una lista.',
            'features.max' => 'Se permite un máximo de :max características.',
            'features.*.string' => 'Cada característica debe ser texto.',
            'features.*.max' => 'Cada característica no puede exceder :max caracteres.',
        ];
    }
}
