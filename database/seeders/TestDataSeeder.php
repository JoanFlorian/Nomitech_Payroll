<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\Rol;
use App\Models\Plan;
use App\Models\Licencia;
use App\Models\Pago;
use App\Models\Contrato;
use App\Models\Ciudad;
use App\Models\TipoDoc;
use App\Services\Payroll\PeriodoAutomationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting test data seeding...');

        // // 1. Ensure master data exists (COMMENTED OUT TO SPEED UP REMOTE SEEDING)
        // $seeders = [
        //     RolSeeder::class, PaisSeeder::class, DepartamentoSeeder::class,
        //     CiudadSeeder::class, TipoDocSeeder::class, PlanSeeder::class,
        //     TipoContratoSeeder::class, TipoTrabajadorSeeder::class,
        //     SubTipoTrabajadorSeeder::class, ARLSeeder::class, EPSSeeder::class,
        //     AFPSeeder::class, CajaCompensacionSeeder::class, FormaPagoSeeder::class,
        //     MetodoPagoSeeder::class, BancoSeeder::class, TipoCuentaSeeder::class,
        //     NivelRiesgoSeeder::class,
        // ];

        // foreach ($seeders as $seeder) {
        //     try {
        //         $this->call($seeder);
        //     } catch (\Exception $e) {
        //         $this->command->warn("Seeder $seeder failed or already run: " . $e->getMessage());
        //     }
        // }

        // 2. Get Dynamic IDs
        $this->command->info('2. Getting dynamic IDs...');
        $idTipoDoc = TipoDoc::where('nombre', 'LIKE', 'Cedula%')->first()?->id_tipo_doc ?? 1;
        $idRolRep = Rol::where('nombre', 'Representante Legal')->first()?->id_rol ?? 1;
        $idRolEmp = Rol::where('nombre', 'Empleado')->first()?->id_rol ?? 3;
        $idRolAux = Rol::where('nombre', 'Auxiliar de Nómina')->first()?->id_rol ?? 5;
        $idCiudadBCS = Ciudad::where('nombre', 'BOGOTÁ, D.C.')->first()?->id_ciudad ?? 11001;

        // 3. Create Legal Representative
        $this->command->info('3. Creating Legal Representative...');
        $repDoc = '1000000001';
        $repData = [
            'doc' => $repDoc,
            'id_tipo_doc' => $idTipoDoc,
            // 'numero_documento' => $repDoc, // Column missing
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Representante',
            'correo' => 'rep@test.com',
            'telefono' => '3001234567',
            'direccion' => 'Calle Falsa 123',
            'id_ciudad' => $idCiudadBCS,
            'id_rol' => $idRolRep,
            'activo' => true,
            'contrasena' => Hash::make('password'),
            // 'is_owner' => true, // Column missing in DB
            'updated_at' => now(),
            'created_at' => now(),
        ];
        
        if (DB::table('usuario')->where('doc', $repDoc)->exists()) {
            DB::table('usuario')->where('doc', $repDoc)->update($repData);
        } else {
            DB::table('usuario')->insert($repData);
        }
        
        $representante = Usuario::where('doc', $repDoc)->first();

        // 4. Select Plan
        $this->command->info('4. Selecting Plan...');
        $plan = Plan::where('nombre', 'Pyme 30')->first() ?: Plan::first();

        // 5. Create Empresa
        $this->command->info('5. Creating Empresa...');
        $empNit = '900123456-1';
        $empData = [
            'nit' => $empNit,
            'razon_social' => 'Empresa de Prueba S.A.S',
            'doc_representante' => $representante->doc,
            'id_ciudad' => $idCiudadBCS,
            'direccion' => 'Avenida Siempre Viva 742',
            'correo' => 'contacto@empresatest.com',
            'telefono' => '6012345678',
            'nit_dv' => 1,
            'updated_at' => now(),
            'created_at' => now(),
        ];
        
        if (DB::table('empresa')->where('nit', $empNit)->exists()) {
            DB::table('empresa')->where('nit', $empNit)->update($empData);
        } else {
            DB::table('empresa')->insert($empData);
        }
        
        $empresa = Empresa::where('nit', $empNit)->first();

        // 5.1 Cleanup existing data for this company to allow re-testing
        $this->command->info('5.1 Cleaning up existing payroll data for test company...');
        
        $periodIds = DB::table('periodo_liquidacion')
            ->where('id_empresa', $empresa->id_empresa)
            ->pluck('id_periodo');

        if ($periodIds->isNotEmpty()) {
            // Delete in order to respect potential foreign keys
            DB::table('benefit_ledger')->whereIn('period_id', $periodIds)->orWhereIn('payroll_period_id', $periodIds)->delete();
            // Also clean any benefit_ledger entries by tenant (covers entries without period_id)
            DB::table('benefit_ledger')->where('tenant_id', $empresa->id_empresa)->delete();
            DB::table('benefit_balance')->where('tenant_id', $empresa->id_empresa)->delete();
            DB::table('provision')->whereIn('id_periodo', $periodIds)->delete();
            DB::table('pila_archivos')->whereIn('periodo_id', $periodIds)->delete();
            
            // Clean up other PILA tables
            $planillaIds = DB::table('planilla_pila')->whereIn('id_periodo', $periodIds)->pluck('id');
            if ($planillaIds->isNotEmpty()) {
                DB::table('pila_detalle_empleado')->whereIn('planilla_id', $planillaIds)->delete();
                DB::table('planilla_pila')->whereIn('id', $planillaIds)->delete();
            }
            
            $salarioIds = DB::table('salario')->whereIn('id_periodo', $periodIds)->pluck('id_salario');
            if ($salarioIds->isNotEmpty()) {
                DB::table('novedad')->whereIn('id_salario', $salarioIds)->delete();
                DB::table('salario')->whereIn('id_salario', $salarioIds)->delete();
            }
            
            DB::table('periodo_liquidacion')->where('id_empresa', $empresa->id_empresa)->delete();
        }

        // 5.2 Cleanup existing employees and contracts for this company to avoid duplicate errors
        $this->command->info('5.2 Cleaning up existing employees and contracts...');
        
        $employeeDocs = [];
        for ($i = 1; $i <= 29; $i++) {
            $employeeDocs[] = (string)(3000000000 + $i);
        }

        // Clean up contracts and employee relationships for this company
        DB::table('contrato')->where('id_empresa', $empresa->id_empresa)->delete();
        DB::table('usuario_empresa')->where('id_empresa', $empresa->id_empresa)->whereIn('doc', $employeeDocs)->delete();
        
        // Cleanup the users themselves if they are only test users
        DB::table('usuario')->whereIn('doc', $employeeDocs)->delete();

        if (DB::table('usuario_empresa')->where(['doc' => $representante->doc, 'id_empresa' => $empresa->id_empresa])->doesntExist()) {
            DB::table('usuario_empresa')->insert(['doc' => $representante->doc, 'id_empresa' => $empresa->id_empresa]);
        }

        // 6. Create Licencia and Pago
        $this->command->info('6. Creating Licencia and Pago...');
        $licenciaData = [
            'empresa_id' => $empresa->id_empresa,
            'plan_id' => $plan->id,
            // 'estado' => 'activa', // Column missing in DB
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonths(3),
            'updated_at' => now(),
            'created_at' => now(),
        ];
        
        if (DB::table('licencia')->where(['empresa_id' => $empresa->id_empresa, 'plan_id' => $plan->id])->exists()) {
            DB::table('licencia')->where(['empresa_id' => $empresa->id_empresa, 'plan_id' => $plan->id])->update($licenciaData);
        } else {
            DB::table('licencia')->insert($licenciaData);
        }
        
        $licencia = Licencia::where('empresa_id', $empresa->id_empresa)->where('plan_id', $plan->id)->first();

        $pagoData = [
            'licencia_id' => $licencia->id,
            'empresa_id' => $empresa->id_empresa,
            'plan_id' => $plan->id,
            // 'proveedor_pago' => 'Manual', // Column missing in DB
            'valor' => $plan->valor,
            'moneda' => 'COP',
            'estado_pago' => 'aprobado',
            'fecha_pago' => now(),
            'updated_at' => now(),
            'created_at' => now(),
        ];
        
        if (DB::table('pago')->where('licencia_id', $licencia->id)->exists()) {
            DB::table('pago')->where('licencia_id', $licencia->id)->update($pagoData);
        } else {
            $pagoData['referencia'] = 'TEST-SEED-' . Str::uuid()->toString();
            DB::table('pago')->insert($pagoData);
        }

        // 7. Create Auxiliaries
        $this->command->info('7. Creating Auxiliaries...');
        $maxAux = $plan->max_auxiliares ?: 0;
        $auxNames = [
            ['nombre' => 'Margarita', 'apellido' => 'Rosa'],
            ['nombre' => 'Pedro', 'apellido' => 'Infante'],
        ];
        for ($i = 1; $i <= $maxAux; $i++) {
            $docAux = "200000000$i";
            $auxData = [
                'doc' => $docAux,
                'id_tipo_doc' => $idTipoDoc,
                'primer_nombre' => $auxNames[$i-1]['nombre'],
                'primer_apellido' => $auxNames[$i-1]['apellido'],
                'correo' => "aux$i@test.com",
                'id_rol' => $idRolAux,
                'id_ciudad' => $idCiudadBCS,
                'activo' => true,
                'direccion' => 'Calle Auxiliar',
                'telefono' => '3000000000',
                'contrasena' => Hash::make('password'),
                'updated_at' => now(),
                'created_at' => now(),
            ];
            
            if (DB::table('usuario')->where('doc', $docAux)->exists()) {
                DB::table('usuario')->where('doc', $docAux)->update($auxData);
            } else {
                DB::table('usuario')->insert($auxData);
            }
            
            $aux = Usuario::where('doc', $docAux)->first();

            if (DB::table('usuario_empresa')->where(['doc' => $aux->doc, 'id_empresa' => $empresa->id_empresa])->doesntExist()) {
                DB::table('usuario_empresa')->insert(['doc' => $aux->doc, 'id_empresa' => $empresa->id_empresa]);
            }
        }

        // 8. Create Employees and Contracts (BATCHED)
        $this->command->info('8. Creating Employees and Contracts (BATCHED)...');
        
        $firstNames = ['Juan', 'Maria', 'Carlos', 'Sandra', 'Luis', 'Diana', 'Jose', 'Paula', 'Andres', 'Natalia', 'Diego', 'Laura', 'Fernando', 'Valentina', 'Javier', 'Sofia', 'Ricardo', 'Isabella', 'Gustavo', 'Camila', 'Jorge', 'Angela', 'Mauricio', 'Daniela', 'Roberto', 'Claudia', 'Sergio', 'Monica', 'Gabriel', 'Vanessa'];
        $lastNames = ['Rodriguez', 'Martinez', 'Garcia', 'Gomez', 'Lopez', 'Gonzalez', 'Hernandez', 'Diaz', 'Perez', 'Sanchez', 'Romero', 'Torres', 'Alvarez', 'Ruiz', 'Ramirez', 'Flores', 'Acosta', 'Morales', 'Vargas', 'Castillo', 'Jimenez', 'Mendoza', 'Reyes', 'Salazar', 'Castro', 'Ortiz', 'Silva', 'Rojas', 'Duarte', 'Castro'];
        $salaries = [2000000, 3000000, 4000000];

        $numEmpl = 29;
        $usersBatch = [];
        $contractsBatch = [];
        $userEmpBatch = [];

        $idNivelRiesgo = DB::table('niveles_riesgo')->where('nombre', 'Nivel I')->value('id') ?? 1;
        $idArl = DB::table('arl')->value('id_arl') ?? 800088702;
        $idEps = DB::table('eps')->value('id_eps') ?? 9001562642;
        $idAfp = DB::table('afp')->value('id_afp') ?? 1;
        $idCaja = DB::table('cajas_compensacion')->value('id_caja') ?? 860066942;
        $password = Hash::make('password');

        for ($i = 0; $i < $numEmpl; $i++) {
            $realIndex = $i + 1;
            $docEmpl = "30000000" . str_pad($realIndex, 2, '0', STR_PAD_LEFT);
            $firstName = $firstNames[$i % count($firstNames)];
            $lastName = $lastNames[$i % count($lastNames)];
            $salary = $salaries[$i % count($salaries)];

            $usersBatch[] = [
                'doc' => $docEmpl,
                'id_tipo_doc' => $idTipoDoc,
                'primer_nombre' => $firstName,
                'primer_apellido' => $lastName,
                'correo' => strtolower($firstName . "." . $lastName . "." . $realIndex . "@test.com"),
                'id_rol' => $idRolEmp,
                'id_ciudad' => $idCiudadBCS,
                'activo' => true,
                'direccion' => 'Calle Empleado ' . $realIndex,
                'telefono' => '300' . str_pad($realIndex, 7, '0', STR_PAD_LEFT),
                'contrasena' => $password,
                'updated_at' => now(),
                'created_at' => now(),
            ];

            $userEmpBatch[] = [
                'doc' => $docEmpl,
                'id_empresa' => $empresa->id_empresa
            ];

            // Create Contract
            $idTipoContrato = ($i % 6) + 1; 
            $fechaFin = null;
            if ($idTipoContrato > 1) {
                // Even index → ends April 15 (termination in April period)
                // Odd index  → ends June 30 (no termination in April period)
                $fechaFin = ($i % 2 === 0) ? '2026-04-15' : '2026-06-30';
            }

            $contractsBatch[] = [
                'doc' => $docEmpl,
                'id_empresa' => $empresa->id_empresa,
                'id_tipo_contrato' => $idTipoContrato,
                'id_tipo_trabajador' => 1,
                'id_sub_tipo_trabajador' => 1,
                'id_forma_pago' => 1,
                'id_metodo_pago' => 1,
                'id_arl' => $idArl,
                'id_eps' => $idEps,
                'id_afp' => $idAfp,
                'id_caja' => $idCaja,
                'nivel_riesgo_id' => $idNivelRiesgo,
                'fecha_inicio' => now()->subMonths(1)->format('Y-m-d'),
                'fecha_fin' => $fechaFin,
                'salario_base' => $salary,
                'salario' => $salary,
                'activo' => true,
                'horas_diarias' => 8,
                'estado' => Contrato::ESTADO_ACTIVO,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        // Ejecutar los batches
        DB::table('usuario')->upsert($usersBatch, ['doc'], ['correo', 'id_rol', 'activo', 'updated_at']);
        DB::table('usuario_empresa')->insert($userEmpBatch);
        DB::table('contrato')->upsert($contractsBatch, ['doc', 'id_empresa'], ['id_tipo_contrato', 'salario_base', 'salario', 'estado', 'updated_at']);


        // 9. Create Liquidation Period
        $this->command->info('9. Creating Liquidation Period...');
        $automationService = app(PeriodoAutomationService::class);
        
        // Forzamos que sea en Marzo para las pruebas de auto-cierre
        $fechaPruebas = \Carbon\Carbon::create(2026, 3, 1);
        $automationService->handleLicenseActivation($empresa, $fechaPruebas);

        $this->command->info('Test data seeded successfully!');
    }
}
