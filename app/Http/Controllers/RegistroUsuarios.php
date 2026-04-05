<?php

namespace App\Http\Controllers;

use App\Http\Requests\Step1Request;
use App\Http\Requests\Step2Request;
use App\Http\Requests\Step3Request;
use App\Http\Requests\UpdateEmployeePartialRequest;
use App\Mail\CredencialesEmpleadoMail;
use App\Models\Empleado;
use App\Models\Usuario;
use App\Models\Contrato;
use App\Models\Banco;
use App\Models\Empresa;
use App\Models\Cuenta;
use App\Models\TipoContrato;
use App\Models\Rol;
use App\Models\NivelRiesgo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class RegistroUsuarios extends Controller
{
    /** @var array<int, string>|null */
    private static ?array $contratoColumnsCache = null;

    private \App\Services\PlanService $planService;
    private \App\Services\Benefits\BenefitPaymentService $benefitService;
    private \App\Services\ContractTerminationService $terminationService;

    public function __construct(
        \App\Services\PlanService $planService,
        \App\Services\Benefits\BenefitPaymentService $benefitService,
        \App\Services\ContractTerminationService $terminationService
    ) {
        $this->planService = $planService;
        $this->benefitService = $benefitService;
        $this->terminationService = $terminationService;
    }

    private function resolveCompanyId(): ?int
    {
        $sessionCompanyId = (int) session('empresa_id');
        if ($sessionCompanyId > 0) {
            return $sessionCompanyId;
        }

        $authUser = Auth::user();
        if ($authUser && method_exists($authUser, 'empresa')) {
            $relatedCompanyId = (int) $authUser->empresa()->value('empresa.id_empresa');
            if ($relatedCompanyId > 0) {
                return $relatedCompanyId;
            }
        }

        $fallbackCompanyId = (int) Empresa::query()->value('id_empresa');
        return $fallbackCompanyId > 0 ? $fallbackCompanyId : null;
    }

    private function isIndefiniteContract(?int $idTipoContrato): bool
    {
        if (!$idTipoContrato) {
            return false;
        }

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

    private function filterContratoData(array $data): array
    {
        if (self::$contratoColumnsCache === null) {
            self::$contratoColumnsCache = Schema::getColumnListing('contrato');
        }

        return array_filter(
            $data,
            fn($key) => in_array($key, self::$contratoColumnsCache, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * PASO 1 - Validar y guardar en sesión datos personales
     */
    public function storeStep1(Step1Request $request)
    {
        $companyId = $this->resolveCompanyId();
        if ($companyId) {
            $empresa = Empresa::find($companyId);
            if ($empresa) {
                // Check if this is a NEW employee or an inactive one being reactivated
                $docInput = $request->input('doc');
                $isCurrentlyActive = Contrato::where('doc', $docInput)
                    ->where('id_empresa', $companyId)
                    ->whereIn('estado', [
                        Contrato::ESTADO_ACTIVO,
                        Contrato::ESTADO_POR_VENCER,
                        Contrato::ESTADO_PROGRAMADO
                    ])
                    ->exists();

                if (!$isCurrentlyActive) {
                    $check = $this->planService->checkEmployeeLimit($empresa);
                    if (!$check['can']) {
                        return response()->json([
                            'errors' => ['general' => [$check['reason']]]
                        ], 403);
                    }
                }
            }
        }

        // FormRequest already performs validation and returns JSON errors when
        $data = $request->validated();

        // get doc from the FormRequest merge; this avoids manual transformation
        $data['doc'] = $request->input('doc');
        $data['correo'] = $data['email'];

        $data['id_ciudad'] = $data['ciudad'];
        unset($data['email'], $data['ciudad'], $data['departamento']); // departamento no se almacena

        // reset cualquier paso posterior para evitar datos corruptos
        session()->forget('employee.step2');

        session(['employee.step1' => $data]);

        return response()->json([
            'success' => true,
            'message' => 'Datos personales validados correctamente'
        ], 200);
    }

    /**
     * PASO 2 - Validar y guardar en sesión datos laborales
     */
    public function storeStep2(Step2Request $request)
    {
        // make sure step1 still exists in session
        if (!session()->has('employee.step1')) {
            return response()->json([
                'errors' => ['general' => ['Debe completar primero la información personal (Paso 1).']]
            ], 422);
        }

        $data = $request->validated();

        session(['employee.step2' => $data]);

        return response()->json([
            'success' => true,
            'message' => 'Datos laborales validados correctamente'
        ], 200);
    }

    /**
     * PASO 3 - Validar paso 3, fusionar todos los datos y crear Usuario + Contrato
     */
    public function storeFinal(Step3Request $request)
    {
        // datos del paso 3 ya validados por Step3Request
        $dataStep3 = $request->validated();

        // recuperar pasos previos
        $step1 = session('employee.step1');
        $step2 = session('employee.step2');

        if (empty($step1) || empty($step2)) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión expirada',
                'errors' => ['general' => ['Sesión expirada. Por favor, completa los pasos nuevamente.']]
            ], 400);
        }

        // fusionamos únicamente, no sobreescribimos campos del paso 3
        $allData = array_merge($step1, $step2, $dataStep3);

        try {
            DB::transaction(function () use ($allData) {
                // evitar duplicados creando o recuperando
                $usuarioData = [
                    'doc' => $allData['doc'],
                    'id_tipo_doc' => $allData['id_tipo_doc'],
                    'contrasena' => Hash::make((string) $allData['doc']),
                    'primer_nombre' => $allData['primer_nombre'],
                    'otros_nombres' => $allData['otros_nombres'] ?? null,
                    'primer_apellido' => $allData['primer_apellido'],
                    'segundo_apellido' => $allData['segundo_apellido'] ?? null,
                    'id_ciudad' => $allData['id_ciudad'],
                    'direccion' => $allData['direccion'] ?? null,
                    'telefono' => $allData['telefono'] ?? '0000000000',
                    'correo' => $allData['correo'] ?? ((string) $allData['doc']) . '@nomitech.local',
                    'fondo_cesantias' => $allData['fondo_cesantias'] ?? null,
                    'id_rol' => $allData['id_rol'] ?? 3,
                    'activo' => true,
                    'must_change_password' => true, // obliga al trabajador a cambiar su contraseña en el primer ingreso
                ];

                $usuario = Usuario::updateOrCreate([
                    'doc' => $allData['doc'],
                ], $usuarioData);

                // Link User to Empresa (Required for security scopes)
                $companyId = $this->resolveCompanyId();
                if ($companyId) {
                    $usuario->empresa()->syncWithoutDetaching([$companyId]);
                }

                // contrato (uso updateOrCreate para no generar múltiples registros si
                // el formulario se envía más de una vez)
                $companyId = $this->resolveCompanyId();

                if (empty($companyId)) {
                    throw new \RuntimeException('No hay empresas configuradas para registrar el contrato.');
                }

                $idTipoContrato = (int) $allData['id_tipo_contrato'];
                $fechaFin = $allData['fecha_fin'] ?? null;
                if ($this->isIndefiniteContract($idTipoContrato)) {
                    $fechaFin = null;
                }

                $contratoData = [
                    'id_empresa' => $companyId,
                    'id_tipo_contrato' => $idTipoContrato,
                    'id_tipo_trabajador' => $allData['id_tipo_trabajador'],
                    'id_sub_tipo_trabajador' => $allData['id_sub_tipo_trabajador'],
                    'id_forma_pago' => $allData['id_forma_pago'],
                    'id_metodo_pago' => $allData['id_metodo_pago'] ?? null,
                    'id_arl' => $allData['id_arl'],
                    'id_eps' => $allData['id_eps'],
                    'id_afp' => $allData['id_afp'] ?? null,
                    'id_caja' => $allData['id_caja'] ?? null,
                    'alto_riesgo' => (int) ($allData['alto_riesgo'] ?? 0),
                    'nivel_riesgo_id' => $allData['nivel_riesgo_id'] ?? null,
                    'fecha_inicio' => $allData['fecha_inicio'],
                    'fecha_fin' => $fechaFin,
                    'salario_base' => $allData['salario'] ?? 0,
                    'horas_diarias' => $allData['horas_diarias'] ?? 0,
                    'codigo_interno' => $allData['codigo_interno'] ?? null,
                    'activo' => true,
                    'estado' => Contrato::ESTADO_ACTIVO,
                    'doc' => $allData['doc'],
                ];

                $contratoData = $this->filterContratoData($contratoData);

                $contrato = Contrato::updateOrCreate(
                    ['doc' => $allData['doc']],
                    $contratoData
                );

                // Solo crear cuenta bancaria si no es forma de pago efectivo
                $tipoCuenta = $allData['tipo_cuenta'] ?? null;
                $numeroCuenta = $allData['numero_cuenta'] ?? null;
                $bankId = (int) ($allData['id_banco'] ?? 0);

                if (!empty($tipoCuenta) && !empty($numeroCuenta)) {
                    if ($bankId <= 0) {
                        $bankId = (int) Banco::query()->value('id_banco');
                    }

                    if ($bankId <= 0) {
                        throw new \RuntimeException('No hay bancos configurados para crear la cuenta del empleado.');
                    }

                    Cuenta::updateOrCreate(
                        [
                            'id_contrato' => $contrato->id_contrato,
                            'activo' => true,
                        ],
                        [
                            'id_tipo_cuenta' => $tipoCuenta,
                            'id_banco' => $bankId,
                            'numero_cuenta' => $numeroCuenta,
                            'activo' => true,
                        ]
                    );
                }

                // Create initial benefit balances if provided
                $initialBalances = [
                    'prima_inicial' => (float) ($allData['prima_inicial'] ?? 0),
                    'cesantias_inicial' => (float) ($allData['cesantias_inicial'] ?? 0),
                    'intereses_inicial' => (float) ($allData['intereses_inicial'] ?? 0),
                    'vacaciones_inicial' => (float) ($allData['vacaciones_inicial'] ?? 0),
                ];

                $hasInitialBalances = array_sum($initialBalances) > 0;
                if ($hasInitialBalances) {
                    $benefitService = app(\App\Services\Benefits\BenefitPaymentService::class);
                    $benefitService->createInitialBalances(
                        $allData['doc'],
                        $companyId,
                        $initialBalances
                    );
                }

                // ── LABOR CONTINUITY CHECK ──
                $this->terminationService->handleLaborContinuity($contrato);
            });

            session()->forget(['employee.step1', 'employee.step2']);

            // Enviar correo con credenciales al trabajador (no bloquea el flujo si falla)
            try {
                $usuarioCreado = Usuario::find($allData['doc']);
                if ($usuarioCreado && filter_var($usuarioCreado->correo, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($usuarioCreado->correo)->send(new CredencialesEmpleadoMail($usuarioCreado));
                }
            } catch (\Exception $mailException) {
                Log::warning('No se pudo enviar correo de credenciales al empleado: ' . $mailException->getMessage(), [
                    'doc' => $allData['doc'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Empleado registrado correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error(
                'Error creando empleado: ' . $e->getMessage(),
                [
                    'exception' => $e,
                    'code' => $e->getCode(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'success' => false,
                'errors' => ['general' => ['Error al registrar empleado']],
            ], 500);
        }
    }

    /**
     * Procesar la renovación de un contrato.
     */
    public function renewContract(UpdateEmployeePartialRequest $request, $doc)
    {
        try {
            DB::beginTransaction();

            $companyId = session('empresa_id');
            $usuario = Empleado::findOrFail($doc);
            $empresa = Empresa::find($companyId);
            
            if ($empresa) {
                // For renewals, we check if the employee IS ALREADY counted as active.
                // If they are not (e.g., they were TERMINATED), then renewing them increases the count.
                $isCurrentlyActive = Contrato::where('doc', $doc)
                    ->where('id_empresa', $companyId)
                    ->whereIn('estado', [
                        Contrato::ESTADO_ACTIVO,
                        Contrato::ESTADO_POR_VENCER,
                        Contrato::ESTADO_PROGRAMADO
                    ])
                    ->exists();

                if (!$isCurrentlyActive) {
                    $check = $this->planService->checkEmployeeLimit($empresa);
                    if (!$check['can']) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Límite alcanzado',
                            'errors' => ['general' => [$check['reason']]]
                        ], 403);
                    }
                }
            }

            $contratoAnterior = Contrato::where('doc', $doc)
                ->where('id_empresa', $companyId)
                ->orderByDesc('id_contrato')
                ->firstOrFail();

            // Validación adicional de renovación
            if ($request->has('fecha_inicio')) {
                if ($request->fecha_inicio <= $contratoAnterior->fecha_inicio) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Datos inválidos',
                        'errors' => ['fecha_inicio' => ['La nueva fecha de inicio debe ser posterior a la fecha de inicio del contrato anterior.']]
                    ], 422);
                }
            }

            $data = $request->validated();

            // 1. Actualizar datos del Usuario (si cambiaron durante la renovación)
            $usuarioData = [];
            foreach (['id_tipo_doc', 'id_rol', 'primer_nombre', 'otros_nombres', 'primer_apellido', 'segundo_apellido', 'id_ciudad', 'direccion', 'fondo_cesantias'] as $field) {
                if (isset($data[$field])) $usuarioData[$field] = $data[$field];
            }
            if (!empty($usuarioData)) {
                $usuario->update($usuarioData);
            }

            // 2. Crear el nuevo contrato (replicando el anterior)
            $nuevoContrato = $contratoAnterior->replicate([
                'id_contrato',
                'created_at',
                'updated_at',
                'fecha_vencimiento_contrato', 
                'salario_final_pagado_at',
                'prestaciones_liquidadas_at',
                'cesantias_transferidas_at',
                'vacaciones_liquidadas_at'
            ]);

            // Actualizar con los nuevos datos laborales
            if (isset($data['id_tipo_contrato'])) $nuevoContrato->id_tipo_contrato = $data['id_tipo_contrato'];
            if (isset($data['id_tipo_trabajador'])) $nuevoContrato->id_tipo_trabajador = $data['id_tipo_trabajador'];
            if (isset($data['id_sub_tipo_trabajador'])) $nuevoContrato->id_sub_tipo_trabajador = $data['id_sub_tipo_trabajador'];
            if (isset($data['id_forma_pago'])) $nuevoContrato->id_forma_pago = $data['id_forma_pago'];
            if (isset($data['id_metodo_pago'])) $nuevoContrato->id_metodo_pago = $data['id_metodo_pago'];
            if (isset($data['id_arl'])) $nuevoContrato->id_arl = $data['id_arl'];
            if (isset($data['id_eps'])) $nuevoContrato->id_eps = $data['id_eps'];
            if (isset($data['id_afp'])) $nuevoContrato->id_afp = $data['id_afp'];
            if (isset($data['alto_riesgo'])) $nuevoContrato->alto_riesgo = (int) $data['alto_riesgo'];
            if (isset($data['nivel_riesgo_id'])) $nuevoContrato->nivel_riesgo_id = $data['nivel_riesgo_id'];
            if (isset($data['fecha_inicio'])) $nuevoContrato->fecha_inicio = $data['fecha_inicio'];
            if (isset($data['fecha_fin'])) $nuevoContrato->fecha_fin = $data['fecha_fin'];
            if (isset($data['salario'])) $nuevoContrato->salario_base = $data['salario'];
            if (isset($data['horas_diarias'])) $nuevoContrato->horas_diarias = $data['horas_diarias'];
            
            // Si el contrato es indefinido, forzar fecha_fin null
            if ($this->isIndefiniteContract($nuevoContrato->id_tipo_contrato)) {
                $nuevoContrato->fecha_fin = null;
            }

            // Finalizar el contrato anterior para que no siga saliendo en la nómina masiva
            if (!$contratoAnterior->fecha_fin && isset($data['fecha_inicio'])) {
                $contratoAnterior->fecha_fin = \Carbon\Carbon::parse($data['fecha_inicio'])->subDay()->toDateString();
            }
            $contratoAnterior->activo = false;
            $contratoAnterior->estado = Contrato::ESTADO_TERMINADO;
            $contratoAnterior->save();

            $nuevoContrato->activo = true;
            $nuevoContrato->estado = Contrato::ESTADO_ACTIVO; 
            $nuevoContrato->save();

            // 3. Crear/Actualizar cuenta bancaria para el NUEVO contrato
            $tipoCuenta = $data['tipo_cuenta'] ?? null;
            $numeroCuenta = $data['numero_cuenta'] ?? null;

            if (!empty($tipoCuenta) && !empty($numeroCuenta)) {
                $bancoId = (int) ($data['id_banco'] ?? 0);
                if ($bancoId <= 0) {
                    $bancoId = (int) Banco::query()->value('id_banco');
                }
                Cuenta::updateOrCreate(
                    ['id_contrato' => $nuevoContrato->id_contrato],
                    [
                        'id_tipo_cuenta' => $tipoCuenta,
                        'id_banco' => $bancoId,
                        'numero_cuenta' => $numeroCuenta,
                        'activo' => true,
                    ]
                );
            }

            // 4. Sincronizar estados usando el servicio de ciclo de vida
            $lifecycleService = app(\App\Services\ContractLifecycleService::class);
            $lifecycleService->procesarCreacionContrato($nuevoContrato);

            // 5. Crear saldos iniciales de beneficios si se proporcionan (Migración)
            $initialBalances = [
                'prima_inicial'    => (float) ($data['prima_inicial'] ?? 0),
                'cesantias_inicial' => (float) ($data['cesantias_inicial'] ?? 0),
                'intereses_inicial' => (float) ($data['intereses_inicial'] ?? 0),
                'vacaciones_inicial' => (float) ($data['vacaciones_inicial'] ?? 0),
            ];

            if (array_sum($initialBalances) > 0) {
                $this->benefitService->createInitialBalances(
                    $doc,
                    $companyId,
                    $initialBalances
                );
            }

            // ── LABOR CONTINUITY CHECK ──
            $this->terminationService->handleLaborContinuity($nuevoContrato);

            // Invalidar hash PILA si se cambió alguna entidad de seguridad social en la renovación
            $entidadesSSocial = ['id_eps', 'id_afp', 'id_arl', 'id_caja'];
            $entidadesCambiadas = false;
            
            foreach ($entidadesSSocial as $entidad) {
                if (isset($data[$entidad]) && $data[$entidad] !== $contratoAnterior->{$entidad}) {
                    $entidadesCambiadas = true;
                    break;
                }
            }
            
            if ($entidadesCambiadas && Schema::hasTable('planilla_pila') && Schema::hasColumn('planilla_pila', 'datos_hash')) {
                DB::table('planilla_pila')
                    ->where('id_empresa', $companyId)
                    ->where('estado', 'generada')
                    ->update(['datos_hash' => null]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrato renovado exitosamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error renovando contrato: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar la renovación.'
            ], 500);
        }
    }

    /**
     * Limpiar sesión del wizard de registro de empleado.
     */
    public function clearWizardSession()
    {
        session()->forget(['employee.step1', 'employee.step2']);

        return response()->json([
            'success' => true,
            'message' => 'Sesión del registro limpiada correctamente',
        ], 200);
    }

    /**
     * Obtener datos de un empleado para editar (AJAX)
     */
    public function editEmployee($doc)
    {
        $usuario = Empleado::with(['contratos' => function($q) {
            $q->orderByDesc('id_contrato');
        }, 'ciudad'])->findOrFail($doc);
        
        $contrato = $usuario->contratos->first();
        $cuenta = null;

        if ($contrato) {
            $cuenta = Cuenta::where('id_contrato', $contrato->id_contrato)
                ->where('activo', true)
                ->first();
        }

        return response()->json([
            'usuario' => $usuario,
            'contrato' => $contrato, // null if not found
            'cuenta' => $cuenta,
        ]);
    }

    /**
     * Actualizar datos del empleado (edición parcial)
     * Solo actualiza los campos que se envían en la solicitud
     */
    public function updateEmployee(UpdateEmployeePartialRequest $request, $doc)
    {
        $usuario = Empleado::findOrFail($doc);
        $contrato = $usuario->contratos->first();

        $data = $request->validated();

        // Usar transacción para actualizar
        DB::transaction(function () use ($usuario, $contrato, $data) {
            // Actualizar solo los campos del Usuario que se enviaron
            $usuarioData = [];
            if (isset($data['id_tipo_doc']))
                $usuarioData['id_tipo_doc'] = $data['id_tipo_doc'];
            if (isset($data['primer_nombre']))
                $usuarioData['primer_nombre'] = $data['primer_nombre'];
            if (isset($data['otros_nombres']))
                $usuarioData['otros_nombres'] = $data['otros_nombres'];
            if (isset($data['primer_apellido']))
                $usuarioData['primer_apellido'] = $data['primer_apellido'];
            if (isset($data['segundo_apellido']))
                $usuarioData['segundo_apellido'] = $data['segundo_apellido'];
            if (isset($data['id_ciudad']))
                $usuarioData['id_ciudad'] = $data['id_ciudad'];
            if (isset($data['direccion']))
                $usuarioData['direccion'] = $data['direccion'];
            if (isset($data['email']))
                $usuarioData['correo'] = $data['email'];
            if (isset($data['telefono']))
                $usuarioData['telefono'] = $data['telefono'];
            if (array_key_exists('fondo_cesantias', $data))
                $usuarioData['fondo_cesantias'] = $data['fondo_cesantias'];
            if (isset($data['id_rol'])) {
                $usuarioData['id_rol'] = $data['id_rol'];
            }

            // Link User to Empresa (Ensure scope works)
            $id_empresa = session('empresa_id');
            if ($id_empresa) {
                $usuario->empresa()->syncWithoutDetaching([$id_empresa]);
            }
            
            if (!empty($usuarioData)) {
                $usuario->update($usuarioData);
            }

            // Actualizar solo los campos del Contrato que se enviaron
            $contratoData = [];
            if (isset($data['id_tipo_contrato']))
                $contratoData['id_tipo_contrato'] = $data['id_tipo_contrato'];
            if (isset($data['id_tipo_trabajador']))
                $contratoData['id_tipo_trabajador'] = $data['id_tipo_trabajador'];
            if (isset($data['id_sub_tipo_trabajador']))
                $contratoData['id_sub_tipo_trabajador'] = $data['id_sub_tipo_trabajador'];
            if (isset($data['id_forma_pago']))
                $contratoData['id_forma_pago'] = $data['id_forma_pago'];
            if (isset($data['id_metodo_pago']))
                $contratoData['id_metodo_pago'] = $data['id_metodo_pago'];
            if (isset($data['id_arl']))
                $contratoData['id_arl'] = $data['id_arl'];
            if (isset($data['id_eps']))
                $contratoData['id_eps'] = $data['id_eps'];
            if (isset($data['id_afp']))
                $contratoData['id_afp'] = $data['id_afp'];
            if (isset($data['id_caja']))
                $contratoData['id_caja'] = $data['id_caja'];
            if (isset($data['alto_riesgo']))
                $contratoData['alto_riesgo'] = (int) $data['alto_riesgo'];
            if (isset($data['nivel_riesgo_id']))
                $contratoData['nivel_riesgo_id'] = $data['nivel_riesgo_id'];
            if (isset($data['fecha_inicio']))
                $contratoData['fecha_inicio'] = $data['fecha_inicio'];
            if (isset($data['fecha_fin']))
                $contratoData['fecha_fin'] = $data['fecha_fin'];
            if (isset($data['salario']))
                $contratoData['salario_base'] = $data['salario'];
            if (isset($data['activo']))
                $contratoData['activo'] = (int) $data['activo'];

            $resolvedTipoContrato = (int) ($data['id_tipo_contrato'] ?? ($contrato?->id_tipo_contrato ?? 0));
            if ($this->isIndefiniteContract($resolvedTipoContrato)) {
                $contratoData['fecha_fin'] = null;
            }

            if ($contrato && !empty($contratoData)) {
                // Verificar si se cambió alguna entidad de seguridad social
                $entidadesCambiadas = false;
                $entidadesSSocial = ['id_eps', 'id_afp', 'id_arl', 'id_caja'];
                
                foreach ($entidadesSSocial as $entidad) {
                    if (isset($contratoData[$entidad]) && $contratoData[$entidad] !== $contrato->{$entidad}) {
                        $entidadesCambiadas = true;
                        break;
                    }
                }
                
                $contrato->update($contratoData);
                
                // Si cambió alguna entidad de seguridad social, invalidar hash PILA para permitir regeneración
                if ($entidadesCambiadas && Schema::hasTable('planilla_pila') && Schema::hasColumn('planilla_pila', 'datos_hash')) {
                    DB::table('planilla_pila')
                        ->where('id_empresa', $contrato->id_empresa)
                        ->where('estado', 'generada')
                        ->update(['datos_hash' => null]);
                }
            } elseif (!$contrato && !empty($contratoData)) {
                // Create new contrato if none exists for this usuario
                $companyId = $this->resolveCompanyId();

                if (empty($companyId)) {
                    throw new \RuntimeException('No hay empresas configuradas para actualizar el contrato.');
                }

                $contratoData = array_merge(
                    ['doc' => $usuario->doc, 'id_empresa' => $companyId],
                    $contratoData
                );
                $contratoData = $this->filterContratoData($contratoData);
                $contrato = Contrato::create($contratoData);
            }

            if ($contrato && (isset($data['tipo_cuenta']) || isset($data['numero_cuenta']))) {
                $activeCuenta = Cuenta::where('id_contrato', $contrato->id_contrato)
                    ->where('activo', true)
                    ->first();

                $bankId = (int) ($data['id_banco'] ?? ($activeCuenta->id_banco ?? Banco::query()->value('id_banco')));
                if (empty($bankId)) {
                    throw new \RuntimeException('No hay bancos configurados para actualizar la cuenta.');
                }

                $tipoCuenta = $data['tipo_cuenta'] ?? $activeCuenta->id_tipo_cuenta ?? null;
                $numeroCuenta = $data['numero_cuenta'] ?? $activeCuenta->numero_cuenta ?? null;

                if (!empty($tipoCuenta) && !empty($numeroCuenta)) {
                    Cuenta::updateOrCreate(
                        [
                            'id_contrato' => $contrato->id_contrato,
                            'activo' => true,
                        ],
                        [
                            'id_tipo_cuenta' => $tipoCuenta,
                            'id_banco' => $bankId,
                            'numero_cuenta' => $numeroCuenta,
                            'activo' => true,
                        ]
                    );
                }
            }

            // Actualizar saldos iniciales de beneficios si se proporcionan (Migración)
            $initialBalances = [
                'prima_inicial'    => (float) ($data['prima_inicial'] ?? 0),
                'cesantias_inicial' => (float) ($data['cesantias_inicial'] ?? 0),
                'intereses_inicial' => (float) ($data['intereses_inicial'] ?? 0),
                'vacaciones_inicial' => (float) ($data['vacaciones_inicial'] ?? 0),
            ];

            if (array_sum($initialBalances) > 0) {
                $companyId = session('empresa_id');
                $this->benefitService->createInitialBalances(
                    $usuario->doc,
                    (int) $companyId,
                    $initialBalances
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Empleado actualizado correctamente'
        ], 200);
    }
}
