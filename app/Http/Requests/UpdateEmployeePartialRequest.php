<?php

namespace App\Http\Requests;

use App\Models\Contrato;
use App\Models\TipoContrato;
use App\Models\TipoTrabajador;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class UpdateEmployeePartialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validación flexible para edición parcial.
     * Aplica las mismas reglas del flujo de registro,
     * pero únicamente sobre los campos enviados.
     */
    public function rules(): array
    {
        $doc = $this->route('doc') ?? $this->input('doc');

        $rules = [];

        // PASO 1 - Datos personales
        if ($this->has('id_tipo_doc')) {
            $rules['id_tipo_doc'] = 'bail|required|integer|exists:tipo_doc,id_tipo_doc';
        }

        if ($this->has('numero_documento')) {
            $rules['numero_documento'] = 'bail|required|digits_between:5,15|unique:usuario,doc,' . $doc . ',doc';
        }

        if ($this->has('primer_apellido')) {
            $rules['primer_apellido'] = 'bail|required|string|min:3|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/';
        }

        if ($this->has('segundo_apellido')) {
            $rules['segundo_apellido'] = 'bail|nullable|string|min:3|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/';
        }

        if ($this->has('primer_nombre')) {
            $rules['primer_nombre'] = 'bail|required|string|min:3|max:30|regex:/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/';
        }

        if ($this->has('otros_nombres')) {
            $rules['otros_nombres'] = 'bail|nullable|string|min:3|max:50|regex:/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/';
        }

        if ($this->has('id_ciudad')) {
            $rules['id_ciudad'] = 'bail|required|integer|exists:ciudad,id_ciudad';
        }

        if ($this->has('direccion')) {
            $rules['direccion'] = [
                'bail',
                'required',
                'string',
                'max:150',
                'regex:/^(?=.*[A-Za-z])(?=.*(calle|carrera|cra\.?|cl\.?|av\.?|avenida|transversal|diagonal|#|no\.?)).+$/i',
            ];
        }

        // PASO 2 - Datos laborales
        if ($this->has('fecha_inicio')) {
            $rules['fecha_inicio'] = 'bail|required|date';
        }

        if ($this->has('fecha_fin')) {
            $rules['fecha_fin'] = 'bail|nullable|date|after:fecha_inicio';
        }

        if ($this->has('id_tipo_trabajador')) {
            $rules['id_tipo_trabajador'] = 'bail|required|integer|exists:tipo_trabajador,id_tipo_trabajador';
        }

        if ($this->has('id_sub_tipo_trabajador')) {
            $rules['id_sub_tipo_trabajador'] = 'bail|required|integer|exists:sub_tipo_trabajador,id_sub_tipo_trabajador';
        }

        if ($this->has('id_tipo_contrato')) {
            $rules['id_tipo_contrato'] = 'bail|required|integer|exists:tipo_contrato,id_tipo_contrato';
        }

        if ($this->has('salario')) {
            $rules['salario'] = 'bail|required|numeric|min:0|max:999999999';
        }

        if ($this->has('codigo_interno')) {
            $rules['codigo_interno'] = 'bail|nullable|string|min:3|max:20|regex:/^[0-9]+$/';
        }

        if ($this->has('id_arl')) {
            $rules['id_arl'] = 'bail|required|integer|exists:arl,id_arl';
        }

        if ($this->has('nivel_riesgo')) {
            $rules['nivel_riesgo'] = 'bail|required|in:Nivel I,Nivel II,Nivel III,Nivel IV,Nivel V';
        }

        if ($this->has('alto_riesgo')) {
            $rules['alto_riesgo'] = 'nullable|boolean';
        }

        if ($this->has('horas_diarias')) {
            $rules['horas_diarias'] = 'bail|required|integer|min:1|max:12';
        }

        // PASO 3 - Datos financieros
        if ($this->has('id_forma_pago')) {
            $rules['id_forma_pago'] = 'bail|required|integer|exists:forma_pago,id_forma_pago';
        }

        if ($this->has('id_metodo_pago')) {
            $rules['id_metodo_pago'] = 'bail|required|integer|exists:metodo_pago,id_metodo_pago';
        }

        if ($this->has('tipo_cuenta')) {
            $rules['tipo_cuenta'] = 'bail|required|integer|exists:tipo_cuenta,id_tipo_cuenta';
        }

        if ($this->has('numero_cuenta')) {
            $rules['numero_cuenta'] = 'bail|required|string|max:20|regex:/^[0-9]{6,20}$/';
        }

        if ($this->has('id_eps')) {
            $rules['id_eps'] = 'bail|required|integer|exists:eps,id_eps';
        }

        if ($this->has('id_afp')) {
            $rules['id_afp'] = 'bail|required|integer|exists:afp,id_afp';
        }

        if ($this->has('id_caja')) {
            $rules['id_caja'] = 'bail|required|integer|exists:cajas_compensacion,id_caja';
        }

        if ($this->has('fondo_cesantias')) {
            $rules['fondo_cesantias'] = 'bail|nullable|string|max:100';
        }

        // Migration Fields (Optional)
        if ($this->has('prima_inicial')) {
            $rules['prima_inicial'] = 'bail|nullable|numeric|min:0|max:999999999';
        }
        if ($this->has('cesantias_inicial')) {
            $rules['cesantias_inicial'] = 'bail|nullable|numeric|min:0|max:9999999999';
        }
        if ($this->has('intereses_inicial')) {
            $rules['intereses_inicial'] = 'bail|nullable|numeric|min:0';
        }
        if ($this->has('vacaciones_inicial')) {
            $rules['vacaciones_inicial'] = 'bail|nullable|numeric|min:0|max:180';
        }

        if ($this->has('email')) {
            $rules['email'] = 'bail|required|email|max:255|unique:usuario,correo,' . $doc . ',doc';
        }

        if ($this->has('telefono')) {
            $rules['telefono'] = 'bail|required|digits:10|regex:/^[0-9]+$/';
        }

        // Estado contrato
        if ($this->has('activo')) {
            $rules['activo'] = 'nullable|boolean';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            // PASO 1 - Datos personales
            'id_tipo_doc.required' => 'El tipo de documento es obligatorio.',
            'id_tipo_doc.integer' => 'El tipo de documento no es válido.',
            'id_tipo_doc.exists' => 'El tipo de documento seleccionado no es válido.',

            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.digits_between' => 'El número de documento debe tener entre 5 y 15 dígitos.',
            'numero_documento.unique' => 'Este número de documento ya está registrado en el sistema.',

            'primer_apellido.required' => 'El primer apellido es obligatorio.',
            'primer_apellido.min' => 'El primer apellido debe tener mínimo 3 caracteres.',
            'primer_apellido.max' => 'El primer apellido no puede superar 30 caracteres.',
            'primer_apellido.regex' => 'El primer apellido solo puede contener letras y espacios.',

            'segundo_apellido.min' => 'El segundo apellido debe tener mínimo 3 caracteres.',
            'segundo_apellido.max' => 'El segundo apellido no puede superar 30 caracteres.',
            'segundo_apellido.regex' => 'El segundo apellido solo puede contener letras y espacios.',

            'primer_nombre.required' => 'El primer nombre es obligatorio.',
            'primer_nombre.min' => 'El primer nombre debe tener mínimo 3 caracteres.',
            'primer_nombre.max' => 'El primer nombre no puede superar 30 caracteres.',
            'primer_nombre.regex' => 'El primer nombre solo puede contener letras y espacios.',

            'otros_nombres.min' => 'Los otros nombres deben tener mínimo 3 caracteres.',
            'otros_nombres.max' => 'Los otros nombres no pueden superar 50 caracteres.',
            'otros_nombres.regex' => 'Los otros nombres solo pueden contener letras y espacios.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ingresar un correo electrónico válido.',
            'email.max' => 'El correo no puede superar 255 caracteres.',
            'email.unique' => 'El correo electrónico ya está registrado en el sistema.',

            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.digits' => 'El teléfono debe tener exactamente 10 dígitos.',
            'telefono.regex' => 'El teléfono solo puede contener números.',

            'id_ciudad.required' => 'La ciudad es obligatoria.',
            'id_ciudad.integer' => 'Debe seleccionar una ciudad válida.',
            'id_ciudad.exists' => 'La ciudad seleccionada no es válida.',

            'direccion.required' => 'La dirección es obligatoria.',
            'direccion.max' => 'La dirección no puede superar 150 caracteres.',
            'direccion.regex' => 'La dirección debe incluir texto válido y una referencia vial (ej: Calle, Carrera, Cra, Cl, Av, Transversal, Diagonal, # o No).',

            // PASO 2 - Datos laborales
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'Debe ingresar una fecha válida.',

            'fecha_fin.date' => 'Debe ingresar una fecha válida.',
            'fecha_fin.after' => 'La fecha fin debe ser posterior a la fecha de inicio.',

            'horas_diarias.required' => 'Las horas diarias son obligatorias.',
            'horas_diarias.integer' => 'Las horas diarias deben ser un número entero.',
            'horas_diarias.min' => 'Debe trabajar mínimo 1 hora diaria.',
            'horas_diarias.max' => 'No puede superar 12 horas diarias.',

            'id_tipo_trabajador.required' => 'Debe seleccionar el tipo de trabajador.',
            'id_tipo_trabajador.integer' => 'Debe seleccionar un tipo de trabajador válido.',
            'id_tipo_trabajador.exists' => 'El tipo de trabajador seleccionado no existe.',

            'id_sub_tipo_trabajador.required' => 'Debe seleccionar el sub tipo de trabajador.',
            'id_sub_tipo_trabajador.integer' => 'Debe seleccionar un sub tipo válido.',
            'id_sub_tipo_trabajador.exists' => 'El sub tipo de trabajador seleccionado no existe.',

            'id_tipo_contrato.required' => 'Debe seleccionar el tipo de contrato.',
            'id_tipo_contrato.integer' => 'Debe seleccionar un tipo de contrato válido.',
            'id_tipo_contrato.exists' => 'El tipo de contrato seleccionado no existe.',

            'id_arl.required' => 'Debe seleccionar la ARL.',
            'id_arl.integer' => 'Debe seleccionar una ARL válida.',
            'id_arl.exists' => 'La ARL seleccionada no existe.',

            'salario.required' => 'El salario es obligatorio.',
            'salario.numeric' => 'El salario debe ser un valor numérico.',
            'salario.min' => 'El salario no puede ser negativo.',
            'salario.max' => 'El salario es demasiado alto.',

            'nivel_riesgo.required' => 'Debe seleccionar el nivel de riesgo.',
            'nivel_riesgo.in' => 'El nivel de riesgo seleccionado no es válido.',

            'codigo_interno.min' => 'El código interno debe tener mínimo 3 caracteres.',
            'codigo_interno.max' => 'El código interno no puede superar 20 caracteres.',
            'codigo_interno.regex' => 'El código interno solo puede contener números.',

            // PASO 3 - Datos financieros
            'id_forma_pago.required' => 'Debe seleccionar la forma de pago.',
            'id_forma_pago.integer' => 'La forma de pago no es válida.',
            'id_forma_pago.exists' => 'La forma de pago seleccionada no existe.',

            'id_metodo_pago.required' => 'Debe seleccionar el método de pago.',
            'id_metodo_pago.integer' => 'El método de pago no es válido.',
            'id_metodo_pago.exists' => 'El método de pago seleccionado no existe.',

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

            'id_caja.required' => 'Debe seleccionar la Caja de Compensación.',
            'id_caja.integer' => 'La Caja de Compensación no es válida.',
            'id_caja.exists' => 'La Caja de Compensación seleccionada no existe.',

            'fondo_cesantias.string' => 'El fondo de cesantías debe ser texto.',
            'fondo_cesantias.max' => 'El fondo de cesantías no puede superar 100 caracteres.',
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $doc = (string) ($this->route('doc') ?? $this->input('doc') ?? '');

            $contratoActual = null;
            if ($doc !== '') {
                $contratoActual = \App\Models\Contrato::query()->where('doc', $doc)->where('activo', 1)->first();
            }

            $idTipoContratoFecha = 0;
            if ($contratoActual instanceof \App\Models\Contrato) {
                $idTipoContratoFecha = (int) ($this->input('id_tipo_contrato') ?? $contratoActual->id_tipo_contrato);
            } else {
                $idTipoContratoFecha = (int) $this->input('id_tipo_contrato', 0);
            }
            $fechaFin = $this->input('fecha_fin');

            if ($idTipoContratoFecha > 0 && $this->isIndefiniteContract($idTipoContratoFecha) && !empty($fechaFin)) {
                $validator->errors()->add(
                    'fecha_fin',
                    'Para contrato indefinido no debe registrar fecha de fin.'
                );
            }

            $requiresSalaryValidation = $this->hasAny([
                'salario',
                'salario_base',
                'id_tipo_contrato',
                'id_tipo_trabajador',
                'etapa_aprendiz',
            ]);

            if (!$requiresSalaryValidation) {
                return;
            }

            $salario = (float) 0;
            if ($this->has('salario')) {
                $salario = (float) $this->input('salario');
            } elseif ($this->has('salario_base')) {
                $salario = (float) $this->input('salario_base');
            } elseif ($contratoActual instanceof \App\Models\Contrato) {
                $salario = (float) $contratoActual->salario_base;
            }

            $idTipoContrato = 0;
            if ($this->has('id_tipo_contrato')) {
                $idTipoContrato = (int) $this->input('id_tipo_contrato');
            } elseif ($contratoActual instanceof \App\Models\Contrato) {
                $idTipoContrato = (int) $contratoActual->id_tipo_contrato;
            }

            $idTipoTrabajador = 0;
            if ($this->has('id_tipo_trabajador')) {
                $idTipoTrabajador = (int) $this->input('id_tipo_trabajador');
            } elseif ($contratoActual instanceof \App\Models\Contrato) {
                $idTipoTrabajador = (int) $contratoActual->id_tipo_trabajador;
            }

            if ($idTipoContrato <= 0) {
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
                $etapaAprendiz = $this->resolveAprendizStage($idTipoTrabajador);

                if ($etapaAprendiz === 'lectiva') {
                    $minimoLectiva = $smmlv * 0.75;
                    if ($salario < $minimoLectiva) {
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
            $idFormaPago = (int) ($this->input('id_forma_pago') ?? ($contratoActual?->id_forma_pago ?? 0));
            $isCash = false;
            
            if ($idFormaPago > 0) {
                $nombreForma = \App\Models\FormaPago::query()->where('id_forma_pago', $idFormaPago)->value('nombre');
                if ($nombreForma) {
                    $normalized = Str::of($nombreForma)->ascii()->lower()->toString();
                    $isCash = Str::contains($normalized, ['efectivo', 'contado']);
                }
            }

            if (!$isCash) {
                if ($this->has('tipo_cuenta') && empty($this->input('tipo_cuenta'))) {
                    $validator->errors()->add('tipo_cuenta', 'Debe seleccionar el tipo de cuenta.');
                }
                if ($this->has('numero_cuenta')) {
                    $numero = $this->input('numero_cuenta');
                    if (empty($numero)) {
                        $validator->errors()->add('numero_cuenta', 'Debe ingresar el número de cuenta.');
                    } elseif (!preg_match('/^[0-9]{6,20}$/', (string)$numero)) {
                        $validator->errors()->add('numero_cuenta', 'El número de cuenta debe tener entre 6 y 20 dígitos numéricos.');
                    }
                }
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

        $normalized = Str::of($nombreTipoContrato)
            ->ascii()
            ->lower()
            ->toString();

        return Str::contains($normalized, 'indefinid');
    }

    private function resolveAprendizStage(?int $idTipoTrabajador = null): ?string
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

        if (!$idTipoTrabajador) {
            return null;
        }

        if ($idTipoTrabajador === 12) {
            return 'lectiva';
        }

        if ($idTipoTrabajador === 19) {
            return 'productiva';
        }

        $tipoTrabajadorNombre = TipoTrabajador::query()
            ->where('id_tipo_trabajador', $idTipoTrabajador)
            ->value('nombre');

        if (!$tipoTrabajadorNombre) {
            return null;
        }

        $normalized = Str::of($tipoTrabajadorNombre)
            ->ascii()
            ->lower()
            ->toString();

        if (Str::contains($normalized, 'lectiva')) {
            return 'lectiva';
        }

        if (Str::contains($normalized, 'productiva')) {
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
     * Prepare the data for validation.
     * Mapea alto_riesgo y activo correctamente.
     */
    protected function prepareForValidation(): void
    {
        // Derivar alto_riesgo desde nivel_riesgo para mantener consistencia.
        $nivelRiesgo = (string) $this->input('nivel_riesgo', '');
        $altoRiesgoFromNivel = $this->resolveHighRiskFromNivel($nivelRiesgo);

        if ($altoRiesgoFromNivel !== null && $this->has('nivel_riesgo')) {
            $this->merge(['alto_riesgo' => $altoRiesgoFromNivel]);
        } elseif ($this->has('alto_riesgo')) {
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

        // Compatibilidad por si llega salario_base en lugar de salario
        if (!$this->has('salario') && $this->has('salario_base')) {
            $this->merge(['salario' => $this->input('salario_base')]);
        }

        if ($this->has('salario')) {
            $salarioNormalizado = $this->normalizeLocalizedNumber($this->input('salario'));
            if ($salarioNormalizado !== null) {
                $this->merge(['salario' => $salarioNormalizado]);
            }
        }

        // Normalize Migration Fields
        $migrationFields = ['prima_inicial', 'cesantias_inicial', 'intereses_inicial', 'vacaciones_inicial'];
        foreach ($migrationFields as $field) {
            if ($this->has($field)) {
                $val = $this->normalizeLocalizedNumber($this->input($field));
                if ($val !== null) {
                    $this->merge([$field => $val]);
                }
            }
        }
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
     * Siempre responder en JSON para peticiones AJAX.
     */
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
