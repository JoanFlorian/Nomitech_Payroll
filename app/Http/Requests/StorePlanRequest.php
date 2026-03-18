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
     * Preparar los datos antes de la validación.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('features')) {
            $this->merge([
                'features' => array_values(array_filter($this->features ?? [], function ($value) {
                    return !is_null($value) && trim($value) !== '';
                }))
            ]);
        }
    }

    /**
     * Reglas de validación para crear un plan.
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|min:3|max:60|unique:plan,nombre',
            'valor' => ['required', 'numeric', 'gt:0', 'max:99999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
            'duracion' => 'required|integer|min:1|max:12',
            'num_empl' => 'required|integer|min:1|max:10000',
            'max_admins' => 'required|integer|min:1|max:20',
            'max_auxiliares' => 'required|integer|min:0|max:20',
            'descripcion' => 'nullable|string|max:500',
            'destacado' => 'nullable|boolean',
            'features' => 'required|array|min:2|max:4',
            'features.*' => 'required|string|min:3|max:25|regex:/^(?=.*[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ])[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.,\-&\/\(\)]+$/u',
            'stripe_price_id' => 'nullable|string|starts_with:price_',
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
            'duracion.min' => 'La duración del plan debe ser al menos :min mes.',
            'duracion.max' => 'La duración del plan no puede exceder :max meses.',

            // num_empl
            'num_empl.required' => 'El número de empleados es obligatorio.',
            'num_empl.integer' => 'El número de empleados debe ser un número entero.',
            'num_empl.min' => 'El número de empleados debe ser al menos :min.',
            'num_empl.max' => 'El número de empleados no puede exceder :max.',

            // descripcion
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'descripcion.max' => 'La descripción no puede exceder :max caracteres.',

            // features
            'features.required' => 'Debes ingresar al menos 2 características para el plan.',
            'features.array' => 'Las características deben ser una lista.',
            'features.min' => 'El plan debe tener al menos :min características.',
            'features.max' => 'Se permite un máximo de :max características.',
            'features.*.required' => 'La característica no puede estar vacía.',
            'features.*.string' => 'Cada característica debe ser una cadena de texto.',
            'features.*.min' => 'Cada característica debe tener al menos :min caracteres.',
            'features.*.max' => 'Cada característica no puede exceder :max caracteres.',
            'features.*.regex' => 'Cada característica debe contener al menos una letra y solo permite letras, números, espacios y los símbolos (/,.&-).',
            'max_admins.required' => 'La cantidad de administradores es obligatoria.',
            'max_admins.integer' => 'La cantidad de administradores debe ser un número entero.',
            'max_admins.min' => 'Debe haber al menos 1 administrador.',
            'max_admins.max' => 'No se permiten más de 20 administradores por plan.',
            'max_auxiliares.required' => 'La cantidad de auxiliares es obligatoria.',
            'max_auxiliares.integer' => 'La cantidad de auxiliares debe ser un número entero.',
            'max_auxiliares.min' => 'La cantidad de auxiliares no puede ser negativa.',
            'max_auxiliares.max' => 'No se permiten más de 20 auxiliares por plan.',
            'stripe_price_id.starts_with' => 'El Stripe Price Id debe comenzar con "price_".',
        ];
    }
}
