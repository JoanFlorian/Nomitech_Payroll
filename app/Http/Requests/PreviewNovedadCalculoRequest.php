<?php

namespace App\Http\Requests;

class PreviewNovedadCalculoRequest extends StoreNovedadEmpleadoRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['fecha_inicio'], $rules['fecha_fin']);
        $rules['salario_base'] = 'bail|nullable|numeric|min:1';

        return $rules;
    }
}
