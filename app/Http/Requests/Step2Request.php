<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Step2Request extends FormRequest
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
            'fecha_inicio'      => 'required|date',
            'fecha_fin'         => 'nullable|date|after_or_equal:fecha_inicio',
            'horas_diarias'     => 'required|integer|min:1|max:24',
            'id_tipo_trabajador'   => 'required|string',
            'id_sub_tipo_trabajador'=> 'required|string',
            'id_tipo_contrato'     => 'required|string',
            'salario'           => 'required|numeric|min:0',
            'codigo_interno'    => 'required|string',
            'id_arl'               => 'required|string',
            // 'alto_riesgo'       => 'nullable|boolean',
            // 'nivel_riesgo'      => 'required|string',
        ];
    }


    public function messages(): array
    {
        return [
            'fecha_inicio.required'  => 'Fecha requerida',
            'fecha_fin.required'    => 'Fecha requerida',
            'horas_diarias.required'  => 'Campo requerido',
            'tipo_trabajador.required' => 'Campo obligatorio',
            'id_sub_tipo_trabajador.required'    => 'Campo obligatorio',
            'id_tipo_contrato.required'    => 'Campo obligatorio',
            'salario.required'             => 'Campo obligatorio',
            'codigo_interno.required'     => 'Campo obligatorio',
            'arl.required'           => 'Campo obligatorio',
            // 'alto_riesgo.required'        => 'Campo obligatorio',
            // 'nivel_riesgo.required'        => 'La direccion es obligatoria',
        ];
    }
}
