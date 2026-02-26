<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;

use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'primer_apellido' => $this->primer_apellido ? str_replace(' ', '', ucwords(strtolower($this->primer_apellido))) : null,
            'segundo_apellido' => $this->segundo_apellido ? str_replace(' ', '', ucwords(strtolower($this->segundo_apellido))) : null,
            'primer_nombre' => $this->primer_nombre ? str_replace(' ', '', ucwords(strtolower($this->primer_nombre))) : null,
            'otros_nombres' => $this->otros_nombres ? ucwords(strtolower($this->otros_nombres)) : null,
            'razon_social' => $this->razon_social ? strtoupper($this->razon_social) : null,
            'email' => $this->email ? strtolower($this->email) : null,
            'direccion_empresa' => $this->direccion_empresa ? trim($this->direccion_empresa) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = auth()->user();
        $empresaId = session('empresa_id');

        return [
            // EMPRESA
            'razon_social' => ['required', 'string', 'min:3', 'max:60'],
            'nit' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                'digits_between:5,15',
                Rule::unique('empresa', 'nit')->ignore($empresaId, 'id_empresa')
            ],
            'nit_dv' => ['required', 'numeric', 'digits:1'],
            'pais' => ['required', 'string', 'size:2', 'in:CO'],
            'id_departamento' => ['required', 'exists:departamento,id_departamento'],
            'id_ciudad' => ['required', 'exists:ciudad,id_ciudad'],
            'direccion_empresa' => [
                'required',
                'string',
                'max:150',
                'regex:/^(?=.*[A-Za-z])(?=.*(calle|carrera|cra\.?|cl\.?|av\.?|avenida|transversal|diagonal|#|no\.?)).+$/i'
            ],

            // USUARIO
            'documento' => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                'digits_between:6,12',
                Rule::unique('usuario', 'doc')->ignore($user?->doc, 'doc')
            ],
            'id_tipo_doc' => ['required', 'exists:tipo_doc,id_tipo_doc'],
            'primer_apellido' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/'],
            'segundo_apellido' => ['nullable', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/'],
            'primer_nombre' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/'],
            'otros_nombres' => ['nullable', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/'],
            'telefono_celular' => ['required', 'string', 'regex:/^3[0-9]{9}$/'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuario', 'correo')->ignore($user?->doc, 'doc')
            ],
            'password' => [
                'required',
                'confirmed',
                \Illuminate\Validation\Rules\Password::defaults()->mixedCase()->numbers()->symbols()
            ],

            // Selected plan (optional)
            'plan_id' => ['nullable', 'integer', 'exists:plan,id'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     * Overridden to explicitly include passwords in withInput flash data.
     */
    protected function failedValidation(Validator $validator)
    {
        // Creamos una respuesta manual que incluya ABSOLUTAMENTE TODO el input
        // Esto evita que el manejador de excepciones de Laravel filtre las contraseñas
        $response = redirect($this->getRedirectUrl())
            ->withInput($this->all())
            ->withErrors($validator, $this->errorBag);

        throw new ValidationException($validator, $response);
    }

    /**
     * Get custom error messages for validator.
     */
    public function messages(): array
    {
        return [
            'required' => 'El :attribute es requerido.',
            'string' => 'El :attribute debe ser una cadena de texto.',
            'min' => 'El :attribute debe tener al menos :min caracteres.',
            'max' => 'El :attribute no debe exceder los :max caracteres.',
            'unique' => 'El :attribute ya se encuentra registrado.',
            'exists' => 'El :attribute seleccionado es inválido.',
            'numeric' => 'El :attribute debe ser un número.',
            'digits_between' => 'El :attribute debe tener entre :min y :max dígitos.',
            'email' => 'El :attribute debe ser una dirección de correo válida.',
            'confirmed' => 'La confirmación de la contraseña no coincide.',
            'regex' => 'El formato del :attribute es inválido.',

            // Custom rules/overrides
            'nit.regex' => 'El NIT debe contener solo números.',
            'nit.unique' => 'Ya existe una cuenta con este NIT. Si no terminaste tu pago, por favor inicia sesión para continuar.',
            'documento.regex' => 'El documento debe contener solo números.',
            'telefono_celular.regex' => 'El teléfono celular debe tener 10 dígitos y comenzar con 3.',
            'email.unique' => 'Este correo ya está registrado. Si no terminaste tu pago, por favor inicia sesión para continuar.',
            'direccion_empresa.regex' => 'La dirección debe incluir texto válido y una referencia vial (ej: Calle, Carrera, Cra, Cl, Av, Transversal, Diagonal, # o No).',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'razon_social' => 'Razón Social',
            'nit' => 'NIT',
            'nit_dv' => 'DV',
            'pais' => 'País',
            'id_departamento' => 'Departamento',
            'id_ciudad' => 'Municipio/Ciudad',
            'direccion_empresa' => 'Dirección de la Empresa',
            'documento' => 'Documento',
            'id_tipo_doc' => 'Tipo de Documento',
            'primer_apellido' => 'Primer Apellido',
            'segundo_apellido' => 'Segundo Apellido',
            'primer_nombre' => 'Primer Nombre',
            'otros_nombres' => 'Otros Nombres',
            'telefono_celular' => 'Teléfono Celular',
            'email' => 'Correo Electrónico',
            'password' => 'Contraseña',
            'plan_id' => 'Plan Seleccionado',
        ];
    }
}