<?php

namespace App\Http\Requests;

class UpdateNovedadEmpleadoRequest extends StoreNovedadEmpleadoRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'edit_novedad_id' => 'bail|required|integer',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'edit_novedad_id.required' => 'No se recibió la novedad a actualizar.',
            'edit_novedad_id.integer' => 'El identificador de la novedad no es válido.',
        ]);
    }
}
