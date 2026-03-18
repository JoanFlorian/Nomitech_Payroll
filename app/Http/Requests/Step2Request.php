<?php

namespace App\Http\Requests;

use App\Models\TipoContrato;
use App\Models\TipoTrabajador;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class Step2Request extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // FECHAS
            'fecha_inicio' => 'bail|required|date',
            'fecha_fin' => 'bail|nullable|date|after:fecha_inicio',

            // HORAS
            'horas_diarias' => 'bail|required|integer|min:1|max:12',

            // SELECTS
            'id_tipo_trabajador'      => 'bail|required|integer|exists:tipo_trabajador,id_tipo_trabajador',
            'id_sub_tipo_trabajador'  => 'bail|required|integer|exists:sub_tipo_trabajador,id_sub_tipo_trabajador',
            'id_tipo_contrato'        => 'bail|required|integer|exists:tipo_contrato,id_tipo_contrato',
            'id_arl'                  => 'bail|required|integer|exists:arl,id_arl',

            // SALARIO
            'salario' => 'bail|required|numeric|min:0|max:999999999',

            // NIVEL RIESGO
            'nivel_riesgo' => 'bail|required|in:Nivel I,Nivel II,Nivel III,Nivel IV,Nivel V',

            // CODIGO INTERNO
            'codigo_interno' => 'bail|nullable|string|min:3|max:20|regex:/^[0-9]+$/',

            // BOOLEAN CHECK - alto_riesgo (checkbox)
            'alto_riesgo' => 'nullable|boolean',

            // ROL
            'id_rol' => 'nullable|integer|exists:rol,id_rol',
        ];
    }

    public function messages(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | FECHA INICIO
            |--------------------------------------------------------------------------
            */
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date'     => 'Debe ingresar una fecha válida.',

            /*
            |--------------------------------------------------------------------------
            | FECHA FIN
            |--------------------------------------------------------------------------
            */
            'fecha_fin.date' => 'Debe ingresar una fecha válida.',
            'fecha_fin.after' => 'La fecha fin debe ser posterior a la fecha de inicio.',

            /*
            |--------------------------------------------------------------------------
            | HORAS DIARIAS
            |--------------------------------------------------------------------------
            */
            'horas_diarias.required' => 'Las horas diarias son obligatorias.',
            'horas_diarias.integer'  => 'Las horas diarias deben ser un número entero.',
            'horas_diarias.min'      => 'Debe trabajar mínimo 1 hora diaria.',
            'horas_diarias.max'      => 'No puede superar 12 horas diarias.',

            /*
            |--------------------------------------------------------------------------
            | TIPO TRABAJADOR
            |--------------------------------------------------------------------------
            */
            'id_tipo_trabajador.required' => 'Debe seleccionar el tipo de trabajador.',
            'id_tipo_trabajador.integer'  => 'Debe seleccionar un tipo de trabajador válido.',
            'id_tipo_trabajador.exists'   => 'El tipo de trabajador seleccionado no existe.',

            /*
            |--------------------------------------------------------------------------
            | SUB TIPO TRABAJADOR
            |--------------------------------------------------------------------------
            */
            'id_sub_tipo_trabajador.required' => 'Debe seleccionar el sub tipo de trabajador.',
            'id_sub_tipo_trabajador.integer'  => 'Debe seleccionar un sub tipo válido.',
            'id_sub_tipo_trabajador.exists'   => 'El sub tipo de trabajador seleccionado no existe.',

            /*
            |--------------------------------------------------------------------------
            | TIPO CONTRATO
            |--------------------------------------------------------------------------
            */
            'id_tipo_contrato.required' => 'Debe seleccionar el tipo de contrato.',
            'id_tipo_contrato.integer'  => 'Debe seleccionar un tipo de contrato válido.',
            'id_tipo_contrato.exists'   => 'El tipo de contrato seleccionado no existe.',

            /*
            |--------------------------------------------------------------------------
            | ARL
            |--------------------------------------------------------------------------
            */
            'id_arl.required' => 'Debe seleccionar la ARL.',
            'id_arl.integer'  => 'Debe seleccionar una ARL válida.',
            'id_arl.exists'   => 'La ARL seleccionada no existe.',

            /*
            |--------------------------------------------------------------------------
            | SALARIO
            |--------------------------------------------------------------------------
            */
            'salario.required' => 'El salario es obligatorio.',
            'salario.numeric'  => 'El salario debe ser un valor numérico.',
            'salario.min'      => 'El salario no puede ser negativo.',
            'salario.max'      => 'El salario es demasiado alto.',

            /*
            |--------------------------------------------------------------------------
            | NIVEL DE RIESGO
            |--------------------------------------------------------------------------
            */
            'nivel_riesgo.required' => 'Debe seleccionar el nivel de riesgo.',
            'nivel_riesgo.in'       => 'El nivel de riesgo seleccionado no es válido.',

            /*
            |--------------------------------------------------------------------------
            | CÓDIGO INTERNO
            |--------------------------------------------------------------------------
            */
            'codigo_interno.min'      => 'El código interno debe tener mínimo 3 caracteres.',
            'codigo_interno.max'      => 'El código interno no puede superar 20 caracteres.',
            'codigo_interno.regex'    => 'El código interno solo puede contener números.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $id_rol = $this->input('id_rol');
            if ($id_rol) {
                $id_empresa = session('empresa_id');
                $empresa = \App\Models\Empresa::find($id_empresa);
                if ($empresa) {
                    $planService = app(\App\Services\PlanService::class);
                    $role = \App\Models\Rol::find($id_rol);
                    if ($role) {
                        // Check Admin Limit
                        if (in_array($role->nombre, ['Representante Legal', 'Administrador', 'Auditor de Nómina'])) {
                            $check = $planService->checkAdminLimit($empresa);
                            if (!$check['can']) {
                                $validator->errors()->add('id_rol', $check['reason']);
                            }
                        }
                        // Check Auxiliary Limit
                        if ($role->nombre === 'Auxiliar de Nómina') {
                            $check = $planService->checkAuxiliaryLimit($empresa);
                            if (!$check['can']) {
                                $validator->errors()->add('id_rol', $check['reason']);
                            }
                        }
                    }
                }
            }

            $idTipoContrato = (int) $this->input('id_tipo_contrato');
            $salario = (float) $this->input('salario', 0);
            $fechaFin = $this->input('fecha_fin');

            if ($idTipoContrato <= 0) {
                return;
            }

            if ($this->isIndefiniteContract($idTipoContrato) && !empty($fechaFin)) {
                $validator->errors()->add(
                    'fecha_fin',
                    'Para contrato indefinido no debe registrar fecha de fin.'
                );
                return;
            }

            $smmlv = (float) config('nomina.salario_minimo', config('nomina.smmlv'));

            if ($smmlv <= 0) {
                return;
            }

            if ($this->isSalaryExemptContract($idTipoContrato)) {
                return;
            }

            if ($idTipoContrato === 4) {
                $etapaAprendiz = $this->resolveAprendizStage();

                if ($etapaAprendiz === 'lectiva') {
                    $minimoAprendiz = $smmlv * 0.75;
                    if ($salario < $minimoAprendiz) {
                        $validator->errors()->add(
                            'salario',
                            'Para etapa lectiva, el salario base no puede ser inferior al 75% del salario mínimo legal vigente.'
                        );
                    }
                    return;
                }

                if ($etapaAprendiz === 'productiva') {
                    if ($salario < $smmlv) {
                        $validator->errors()->add(
                            'salario',
                            'Para etapa productiva, el salario base no puede ser inferior al salario mínimo legal vigente.'
                        );
                    }
                    return;
                }

                $validator->errors()->add(
                    'salario',
                    'Para contrato de aprendizaje debe indicar la etapa del aprendiz (lectiva o productiva).'
                );
                return;
            }

            if ($salario < $smmlv) {
                $validator->errors()->add(
                    'salario',
                    'El salario base no puede ser inferior al salario mínimo legal vigente para este tipo de contrato.'
                );
            }
        });
    }

    private function isIndefiniteContract(int $idTipoContrato): bool
    {
        $nombreTipoContrato = TipoContrato::query()
            ->where('id_tipo_contrato', $idTipoContrato)
            ->value('nombre');

        if (!$nombreTipoContrato) {
            return false;
        }

        $nombreNormalizado = Str::of($nombreTipoContrato)
            ->ascii()
            ->lower()
            ->toString();

        return Str::contains($nombreNormalizado, 'indefinid');
    }

    private function resolveAprendizStage(): ?string
    {
        $etapa = Str::of((string) $this->input('etapa_aprendiz', ''))
            ->ascii()
            ->lower()
            ->trim()
            ->toString();

        if (Str::contains($etapa, 'lectiva')) {
            return 'lectiva';
        }

        if (Str::contains($etapa, 'productiva')) {
            return 'productiva';
        }

        $idTipoTrabajador = $this->input('id_tipo_trabajador');
        if (!$idTipoTrabajador) {
            return null;
        }

        $tipoTrabajadorNombre = TipoTrabajador::query()
            ->where('id_tipo_trabajador', $idTipoTrabajador)
            ->value('nombre');

        if (!$tipoTrabajadorNombre) {
            return null;
        }

        $nombreNormalizado = Str::of($tipoTrabajadorNombre)
            ->ascii()
            ->lower()
            ->toString();

        if (Str::contains($nombreNormalizado, 'lectiva')) {
            return 'lectiva';
        }

        if (Str::contains($nombreNormalizado, 'productiva')) {
            return 'productiva';
        }

        return null;
    }

    private function isSalaryExemptContract(int $idTipoContrato): bool
    {
        $nombreTipoContrato = TipoContrato::query()
            ->where('id_tipo_contrato', $idTipoContrato)
            ->value('nombre');

        if (!$nombreTipoContrato) {
            return false;
        }

        $normalized = Str::of($nombreTipoContrato)
            ->ascii()
            ->lower()
            ->toString();

        if (Str::contains($normalized, 'prestacion') && Str::contains($normalized, 'servicio')) {
            return true;
        }

        if (Str::contains($normalized, 'obra')) {
            return true;
        }

        if (Str::contains($normalized, 'labor')) {
            return true;
        }

        return false;
    }

    /**
     * Prepare the data for validation. Ensure alto_riesgo is always 0 or 1
     * regardless of whether the checkbox was checked.
     */
    protected function prepareForValidation(): void
    {
        $nivelRiesgo = (string) $this->input('nivel_riesgo', '');
        $altoRiesgoFromNivel = $this->resolveHighRiskFromNivel($nivelRiesgo);

        $payload = [
            'alto_riesgo' => $altoRiesgoFromNivel ?? ($this->has('alto_riesgo') ? 1 : 0),
        ];

        if (!$this->has('salario') && $this->has('salario_base')) {
            $payload['salario'] = $this->input('salario_base');
        }

        if (isset($payload['salario']) || $this->has('salario')) {
            $salarioInput = $payload['salario'] ?? $this->input('salario');
            $salarioNormalizado = $this->normalizeLocalizedNumber($salarioInput);
            if ($salarioNormalizado !== null) {
                $payload['salario'] = $salarioNormalizado;
            }
        }

        if ($this->has('codigo_interno')) {
            $codigoInterno = trim((string) $this->input('codigo_interno'));
            $payload['codigo_interno'] = $codigoInterno === '' ? null : $codigoInterno;
        }

        $this->merge($payload);
    }

    private function resolveHighRiskFromNivel(string $nivelRiesgo): ?int
    {
        if ($nivelRiesgo === '') {
            return null;
        }

        $normalized = Str::of($nivelRiesgo)
            ->ascii()
            ->upper()
            ->toString();

        if (Str::contains($normalized, ['III', 'IV', 'V', '3', '4', '5'])) {
            return 1;
        }

        if (Str::contains($normalized, ['II', 'I', '2', '1'])) {
            return 0;
        }

        return null;
    }

    private function normalizeLocalizedNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = preg_replace('/\s+/', '', (string) $value);
        $value = preg_replace('/[^\d,.-]/', '', $value);

        if ($value === '' || $value === '-' || $value === ',' || $value === '.') {
            return null;
        }

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');
        $decimalPos = max($lastComma !== false ? $lastComma : -1, $lastDot !== false ? $lastDot : -1);

        if ($decimalPos >= 0) {
            $integerPart = preg_replace('/[.,]/', '', substr($value, 0, $decimalPos));
            $decimalPart = preg_replace('/[.,]/', '', substr($value, $decimalPos + 1));
            $normalized = ($integerPart === '' ? '0' : $integerPart) . '.' . $decimalPart;
        } else {
            $normalized = preg_replace('/[.,]/', '', $value);
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    /**
     * Always respond with JSON errors so AJAX front‑end can parse them.
     */
    protected function failedValidation($validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}

