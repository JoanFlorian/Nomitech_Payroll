<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;

class CatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $catalogo = (string) $this->route('catalogo');
        return array_key_exists($catalogo, config('catalogos', []));
    }

    public function rules(): array
    {
        $catalogo = (string) $this->route('catalogo');
        $config = config('catalogos.' . $catalogo);

        if (!$config) {
            return [];
        }

        $primaryKey = $config['primary_key'];
        $columns = collect(Schema::getColumnListing($config['table']))
            ->reject(fn(string $column) => in_array($column, ['created_at', 'updated_at', 'empresa_nit', 'origen', $primaryKey], true))
            ->values();

        $rules = [];

        foreach ($columns as $column) {
            if ($column === 'nombre') {
                $rules[$column] = [
                    'required',
                    'string',
                    'min:2',
                    'max:120',
                    'regex:/^[A-Za-z0-9ÁÉÍÓÚáéíóúÑñÜü\\s\\-\\.,&()]+$/',
                ];
                continue;
            }

            if (in_array($column, ['estado', 'seguridad_social'], true)) {
                $rules[$column] = ['nullable', 'boolean'];
                continue;
            }

            if ($column === 'telefono') {
                $rules[$column] = ['nullable', 'string', 'max:30'];
                continue;
            }

            if ($column === 'direccion') {
                $rules[$column] = ['nullable', 'string', 'max:255'];
                continue;
            }

            if ($column === 'descripcion') {
                $rules[$column] = ['nullable', 'string', 'max:255'];
                continue;
            }

            $rules[$column] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 120 caracteres.',
            'nombre.regex' => 'El nombre contiene caracteres no permitidos.',
            'estado.boolean' => 'El estado enviado no es valido.',
            'seguridad_social.boolean' => 'El valor de seguridad social no es valido.',
        ];
    }
}
