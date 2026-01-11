<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Step1Request extends FormRequest
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
            'id_tipo_doc'   => 'required',
            'numero_documento' => 'required|numeric',
            'primer_apellido'  => 'required|string',
            'segundo_apellido' => 'nullable|string',
            'primer_nombre'    => 'required|string',
            // 'otros_nombres'    => 'nullable|string',
            // 'pais'             => 'required|string',
            'departamento'     => 'required|string',
            'ciudad'           => 'required|string',
            'direccion'        => 'required|string',
        ];
    }

    // Mensajes personalizados (opcional)
    public function messages(): array
    {
        return [
            'id_tipo_doc.required'  => 'El tipo documento es obligatorio',
            'numero_documento.required'    => 'El numero de documento es obligatorio',
            'primer_apellido.required'  => 'El primer apellido es obligatorio',
            'segundo_apellido.required' => 'El segundo apellido es obligatorio',
            'primer_nombre.required'    => 'El primero nombre es obligatorio',
            // 'otros_nombres.required'    => 'El segundo nombre es opcional',
            // 'pais.required'             => 'El pais es obligatorio',
            'departamento.required'     => 'El departamento es obligatorio',
            'ciudad.required'           => 'La ciudad es obligatoria',
            'direccion.required'        => 'La direccion es obligatoria',
        ];
    }
}
