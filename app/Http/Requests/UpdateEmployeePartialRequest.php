<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeePartialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validación flexible para edición parcial
     * Solo valida los campos que se envían
     */
    public function rules(): array
    {
        $doc = $this->route('doc') ?? $this->input('doc');

        $rules = [];

        // Validar solo los campos que se envían en la solicitud
        if ($this->has('id_tipo_doc')) {
            $rules['id_tipo_doc'] = 'integer|max:4';
        }

        if ($this->has('numero_documento')) {
            $rules['numero_documento'] = 'digits_between:5,15|unique:usuario,doc,' . $doc . ',doc';
        }

        if ($this->has('primer_apellido')) {
            $rules['primer_apellido'] = 'string|min:2|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/';
        }

        if ($this->has('segundo_apellido')) {
            $rules['segundo_apellido'] = 'nullable|string|min:2|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/';
        }

        if ($this->has('primer_nombre')) {
            $rules['primer_nombre'] = 'string|min:2|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/';
        }

        if ($this->has('otros_nombres')) {
            $rules['otros_nombres'] = 'nullable|string|min:2|max:50|regex:/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/';
        }

        if ($this->has('id_ciudad')) {
            $rules['id_ciudad'] = 'integer';
        }

        if ($this->has('direccion')) {
            $rules['direccion'] = 'string|min:5|max:100';
        }

        // Datos laborales
        if ($this->has('fecha_inicio')) {
            $rules['fecha_inicio'] = 'date';
        }

        if ($this->has('fecha_fin')) {
            $rules['fecha_fin'] = 'nullable|date|after_or_equal:' . ($this->input('fecha_inicio') ?? 'now');
        }

        if ($this->has('id_tipo_trabajador')) {
            $rules['id_tipo_trabajador'] = 'integer';
        }

        if ($this->has('id_sub_tipo_trabajador')) {
            $rules['id_sub_tipo_trabajador'] = 'integer';
        }

        if ($this->has('id_tipo_contrato')) {
            $rules['id_tipo_contrato'] = 'integer';
        }

        if ($this->has('salario')) {
            $rules['salario'] = 'numeric|min:0|max:999999999';
        }

        if ($this->has('codigo_interno')) {
            $rules['codigo_interno'] = 'string|min:3|max:20|regex:/^[0-9]+$/';
        }

        if ($this->has('id_arl')) {
            $rules['id_arl'] = 'integer';
        }

        if ($this->has('nivel_riesgo')) {
            $rules['nivel_riesgo'] = 'nullable|string|max:50';
        }

        if ($this->has('alto_riesgo')) {
            $rules['alto_riesgo'] = 'nullable|in:0,1,on';
        }

        if ($this->has('horas_diarias')) {
            $rules['horas_diarias'] = 'integer|min:1|max:12';
        }

        // Datos financieros
        if ($this->has('id_forma_pago')) {
            $rules['id_forma_pago'] = 'integer';
        }

        if ($this->has('id_metodo_pago')) {
            $rules['id_metodo_pago'] = 'integer';
        }

        if ($this->has('tipo_cuenta')) {
            $rules['tipo_cuenta'] = 'integer';
        }

        if ($this->has('numero_cuenta')) {
            $rules['numero_cuenta'] = 'min:6|max:18|regex:/^[0-9]+$/';
        }

        if ($this->has('id_eps')) {
            $rules['id_eps'] = 'integer';
        }

        if ($this->has('id_afp')) {
            $rules['id_afp'] = 'integer';
        }

        // Estado
        if ($this->has('activo')) {
            $rules['activo'] = 'nullable|in:0,1,on';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'id_tipo_doc.integer' => 'El tipo de documento no es válido.',
            'numero_documento.digits_between' => 'El número de documento debe tener entre 5 y 15 dígitos.',
            'numero_documento.unique' => 'Este número de documento ya está registrado en el sistema.',
            'primer_apellido.regex' => 'El primer apellido solo puede contener letras y espacios.',
            'segundo_apellido.regex' => 'El segundo apellido solo puede contener letras y espacios.',
            'primer_nombre.regex' => 'El primer nombre solo puede contener letras y espacios.',
            'otros_nombres.regex' => 'Los otros nombres solo pueden contener letras y espacios.',
            'id_ciudad.integer' => 'Debe seleccionar una ciudad válida.',
            'direccion.min' => 'La dirección debe tener al menos 5 caracteres.',
            'fecha_inicio.date' => 'Debe ingresar una fecha válida.',
            'fecha_fin.date' => 'Debe ingresar una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha fin no puede ser menor que la fecha de inicio.',
            'salario.numeric' => 'El salario debe ser un valor numérico.',
            'salario.min' => 'El salario no puede ser negativo.',
            'codigo_interno.regex' => 'El código interno solo puede contener números.',
            'numero_cuenta.regex' => 'El número de cuenta solo puede contener dígitos.',
        ];
    }

    /**
     * Prepare the data for validation.
     * Mapea alto_riesgo y activo correctamente.
     */
    protected function prepareForValidation(): void
    {
        // Mapear alto_riesgo a 0 o 1 solo si está presente
        if ($this->has('alto_riesgo')) {
            if (is_null($this->input('alto_riesgo')) || $this->input('alto_riesgo') === '') {
                $this->merge(['alto_riesgo' => 0]);
            } else {
                $this->merge(['alto_riesgo' => $this->boolean('alto_riesgo') ? 1 : 0]);
            }
        }

        // Mapear activo a 0 o 1 solo si está presente
        if ($this->has('activo')) {
            if (is_null($this->input('activo')) || $this->input('activo') === '') {
                $this->merge(['activo' => 0]);
            } else {
                $this->merge(['activo' => $this->boolean('activo') ? 1 : 0]);
            }
        }

        // Mapear numero_documento a doc si es necesario
        if ($this->has('numero_documento') && !$this->has('doc')) {
            $this->merge(['doc' => $this->input('numero_documento')]);
        }
    }
}
