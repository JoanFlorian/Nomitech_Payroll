<?php

namespace App\Http\Requests;

use App\Models\FormaPago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class Step3Request extends FormRequest
{


    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $formaPagoId = (int) $this->input('id_forma_pago');
        $isCashFormaPago = $this->isCashFormaPago($formaPagoId);

        return [
            'id_forma_pago' => 'required|integer|exists:forma_pago,id_forma_pago',
            'tipo_cuenta' => ($isCashFormaPago ? 'nullable' : 'required') . '|integer|exists:tipo_cuenta,id_tipo_cuenta',
            'numero_cuenta' => ($isCashFormaPago ? 'nullable' : 'required') . '|string|max:20|regex:/^[0-9]{6,20}$/',
            'id_eps' => 'required|integer|exists:eps,id_eps',
            'id_afp' => 'required|integer|exists:afp,id_afp',
            'fondo_cesantias' => 'nullable|string|max:100',

            // Saldos iniciales de prestaciones (opcionales)
            'prima_inicial' => 'nullable|numeric|min:0',
            'cesantias_inicial' => 'nullable|numeric|min:0',
            'intereses_inicial' => 'nullable|numeric|min:0',
            'vacaciones_inicial' => 'nullable|numeric|min:0',
        ];
    }

    private function isCashFormaPago(int $formaPagoId): bool
    {
        if ($formaPagoId <= 0) {
            return false;
        }

        $formaPagoName = FormaPago::query()
            ->where('id_forma_pago', $formaPagoId)
            ->value('nombre');

        if (!$formaPagoName) {
            return false;
        }

        $normalized = Str::of($formaPagoName)
            ->ascii()
            ->lower()
            ->toString();

        return Str::contains($normalized, ['efectivo', 'contado']);
    }

    public function messages(): array
    {
        return [
            'id_forma_pago.required' => 'Debe seleccionar la forma de pago.',
            'id_forma_pago.integer' => 'La forma de pago no es válida.',
            'id_forma_pago.exists' => 'La forma de pago seleccionada no existe.',

            'tipo_cuenta.required' => 'Debe seleccionar el tipo de cuenta.',
            'tipo_cuenta.integer' => 'El tipo de cuenta no es válido.',
            'tipo_cuenta.exists' => 'El tipo de cuenta seleccionada no existe.',

            'numero_cuenta.required' => 'Debe ingresar el número de cuenta.',
            'numero_cuenta.string' => 'El número de cuenta debe ser texto.',
            'numero_cuenta.max' => 'El número de cuenta no puede superar 20 caracteres.',
            'numero_cuenta.regex' => 'El número de cuenta debe tener entre 6 y 20 dígitos numéricos.',

            'id_eps.required' => 'Debe seleccionar la EPS.',
            'id_eps.integer' => 'La EPS no es válida.',
            'id_eps.exists' => 'La EPS seleccionada no existe.',

            'id_afp.required' => 'Debe seleccionar la AFP.',
            'id_afp.integer' => 'La AFP no es válida.',
            'id_afp.exists' => 'La AFP seleccionada no existe.',

            'fondo_cesantias.string' => 'El fondo de cesantías debe ser texto.',
            'fondo_cesantias.max' => 'El fondo de cesantías no puede superar 100 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
