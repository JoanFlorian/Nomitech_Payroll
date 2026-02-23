<?php

namespace App\Http\Controllers;

use App\Http\Requests\Step1Request;
use App\Http\Requests\Step2Request;
use App\Http\Requests\Step3Request;
use App\Http\Requests\UpdateEmployeePartialRequest;
use App\Models\Usuario;
use App\Models\Contrato;
use App\Models\Banco;
use App\Models\Empresa;
use App\Models\Cuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class RegistroUsuarios extends Controller
{
    /**
     * PASO 1 - Validar y guardar en sesión datos personales
     */
    public function storeStep1(Step1Request $request)
    {
        // FormRequest already performs validation and returns JSON errors when
        // called via AJAX.
        $data = $request->validated();

        // get doc from the FormRequest merge; this avoids manual transformation
        $data['doc'] = $request->input('doc');

        $data['id_ciudad'] = $data['ciudad'];
        unset($data['ciudad'], $data['departamento']); // departamento no se almacena

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
                    'doc'             => $allData['doc'],
                    'id_tipo_doc'     => $allData['id_tipo_doc'],
                    'contrasena'      => Hash::make((string) $allData['doc']),
                    'primer_nombre'   => $allData['primer_nombre'],
                    'otros_nombres'   => $allData['otros_nombres'] ?? null,
                    'primer_apellido' => $allData['primer_apellido'],
                    'segundo_apellido'=> $allData['segundo_apellido'] ?? null,
                    'id_ciudad'       => $allData['id_ciudad'],
                    'direccion'       => $allData['direccion'] ?? null,
                    'telefono'        => $allData['telefono'] ?? '0000000000',
                    'correo'          => $allData['correo'] ?? ((string) $allData['doc']).'@nomitech.local',
                    'id_rol'          => 3,
                    'activo'          => true,
                ];

                Usuario::firstOrCreate([
                    'doc' => $allData['doc'],
                ], $usuarioData);

                // contrato (uso updateOrCreate para no generar múltiples registros si
                // el formulario se envía más de una vez)
                $companyId = optional(Auth::user())->empresa_id;
                if (empty($companyId)) {
                    $companyId = Empresa::query()->value('id_empresa');
                }

                if (empty($companyId)) {
                    throw new \RuntimeException('No hay empresas configuradas para registrar el contrato.');
                }

                $contratoData = [
                    'id_empresa'          => $companyId,
                    'id_tipo_contrato'    => $allData['id_tipo_contrato'],
                    'id_tipo_trabajador'  => $allData['id_tipo_trabajador'],
                    'id_sub_tipo_trabajador'=> $allData['id_sub_tipo_trabajador'],
                    'id_forma_pago'       => $allData['id_forma_pago'],
                    'id_metodo_pago'      => $allData['id_metodo_pago'],
                    'id_arl'              => $allData['id_arl'],
                    'id_eps'              => $allData['id_eps'],
                    'id_afp'              => $allData['id_afp'],
                    'alto_riesgo'         => (int) ($allData['alto_riesgo'] ?? 0),
                    'nivel_riesgo'        => $allData['nivel_riesgo'] ?? null,
                    'fecha_inicio'        => $allData['fecha_inicio'],
                    'fecha_fin'           => $allData['fecha_fin'] ?? null,
                    'salario_base'        => $allData['salario'] ?? 0,
                    'activo'              => true,
                    'doc'                 => $allData['doc'],
                ];

                $contrato = Contrato::updateOrCreate(
                    ['doc' => $allData['doc']],
                    $contratoData
                );

                $defaultBankId = Banco::query()->value('id_banco');
                if (empty($defaultBankId)) {
                    throw new \RuntimeException('No hay bancos configurados para crear la cuenta del empleado.');
                }

                Cuenta::updateOrCreate(
                    [
                        'id_contrato' => $contrato->id_contrato,
                        'activo' => true,
                    ],
                    [
                        'id_tipo_cuenta' => $allData['tipo_cuenta'],
                        'id_banco' => $defaultBankId,
                        'numero_cuenta' => $allData['numero_cuenta'],
                        'activo' => true,
                    ]
                );
            });

            session()->forget(['employee.step1', 'employee.step2']);

            return response()->json([
                'success' => true,
                'message' => 'Empleado registrado correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error creando empleado: '.$e->getMessage(),
                [
                    'exception' => $e,
                    'code' => $e->getCode(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            $jsonError = [
                'success' => false,
                'errors' => ['general' => ['Error al registrar empleado']],
            ];

            return response()->json($jsonError, 500);
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
        $usuario = Usuario::with('contratos')->findOrFail($doc);
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
        $usuario = Usuario::findOrFail($doc);
        $contrato = $usuario->contratos->first();

        $data = $request->validated();

        // Usar transacción para actualizar
        DB::transaction(function () use ($usuario, &$contrato, $data) {
            // Actualizar solo los campos del Usuario que se enviaron
            $usuarioData = [];
            if (isset($data['id_tipo_doc'])) $usuarioData['id_tipo_doc'] = $data['id_tipo_doc'];
            if (isset($data['primer_nombre'])) $usuarioData['primer_nombre'] = $data['primer_nombre'];
            if (isset($data['otros_nombres'])) $usuarioData['otros_nombres'] = $data['otros_nombres'];
            if (isset($data['primer_apellido'])) $usuarioData['primer_apellido'] = $data['primer_apellido'];
            if (isset($data['segundo_apellido'])) $usuarioData['segundo_apellido'] = $data['segundo_apellido'];
            if (isset($data['id_ciudad'])) $usuarioData['id_ciudad'] = $data['id_ciudad'];
            if (isset($data['direccion'])) $usuarioData['direccion'] = $data['direccion'];

            if (!empty($usuarioData)) {
                $usuario->update($usuarioData);
            }

            // Actualizar solo los campos del Contrato que se enviaron
            $contratoData = [];
            if (isset($data['id_tipo_contrato'])) $contratoData['id_tipo_contrato'] = $data['id_tipo_contrato'];
            if (isset($data['id_tipo_trabajador'])) $contratoData['id_tipo_trabajador'] = $data['id_tipo_trabajador'];
            if (isset($data['id_sub_tipo_trabajador'])) $contratoData['id_sub_tipo_trabajador'] = $data['id_sub_tipo_trabajador'];
            if (isset($data['id_forma_pago'])) $contratoData['id_forma_pago'] = $data['id_forma_pago'];
            if (isset($data['id_metodo_pago'])) $contratoData['id_metodo_pago'] = $data['id_metodo_pago'];
            if (isset($data['id_arl'])) $contratoData['id_arl'] = $data['id_arl'];
            if (isset($data['id_eps'])) $contratoData['id_eps'] = $data['id_eps'];
            if (isset($data['id_afp'])) $contratoData['id_afp'] = $data['id_afp'];
            if (isset($data['alto_riesgo'])) $contratoData['alto_riesgo'] = (int)$data['alto_riesgo'];
            if (isset($data['nivel_riesgo'])) $contratoData['nivel_riesgo'] = $data['nivel_riesgo'];
            if (isset($data['fecha_inicio'])) $contratoData['fecha_inicio'] = $data['fecha_inicio'];
            if (isset($data['fecha_fin'])) $contratoData['fecha_fin'] = $data['fecha_fin'];
            if (isset($data['salario'])) $contratoData['salario_base'] = $data['salario'];
            if (isset($data['activo'])) $contratoData['activo'] = (int)$data['activo'];

            if ($contrato && !empty($contratoData)) {
                $contrato->update($contratoData);
            } elseif (!$contrato && !empty($contratoData)) {
                // Create new contrato if none exists for this usuario
                $companyId = optional(Auth::user())->empresa_id;
                if (empty($companyId)) {
                    $companyId = Empresa::query()->value('id_empresa');
                }

                if (empty($companyId)) {
                    throw new \RuntimeException('No hay empresas configuradas para actualizar el contrato.');
                }

                $contratoData = array_merge(
                    ['doc' => $usuario->doc, 'id_empresa' => $companyId],
                    $contratoData
                );
                $contrato = Contrato::create($contratoData);
            }

            if ($contrato && (isset($data['tipo_cuenta']) || isset($data['numero_cuenta']))) {
                $activeCuenta = Cuenta::where('id_contrato', $contrato->id_contrato)
                    ->where('activo', true)
                    ->first();

                $bankId = $activeCuenta->id_banco ?? Banco::query()->value('id_banco');
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
        });

        return response()->json([
            'success' => true,
            'message' => 'Empleado actualizado correctamente'
        ], 200);
    }
}
