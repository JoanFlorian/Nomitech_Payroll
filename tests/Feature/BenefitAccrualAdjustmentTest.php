<?php

namespace Tests\Feature;

use App\Models\Contrato;
use App\Models\Empresa;
use App\Models\Novedad;
use App\Models\PeriodoLiquidacion;
use App\Models\Salario;
use App\Models\TipoContrato;
use App\Models\Usuario;
use App\Services\Benefits\BenefitAccrualService;
use App\Services\NominaCalculatorService;
use App\Models\BenefitBalance;
use App\Models\BenefitLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenefitAccrualAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected NominaCalculatorService $calculator;
    protected BenefitAccrualService $accrualService;
    protected Empresa $empresa;
    protected Usuario $usuario;
    protected Contrato $contrato;
    protected PeriodoLiquidacion $periodo;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable FK checks to avoid seeding everything
        Schema::disableForeignKeyConstraints();

        // Seeding required base data for integrity
        DB::table('pais')->insert(['id_pais' => 1, 'nombre' => 'Colombia', 'codigo_alfa2' => 'CO']);
        DB::table('departamento')->insert(['id_departamento' => 1, 'nombre' => 'Dep', 'codigo' => '01', 'codigo_iso' => 'CO-DEP', 'id_pais' => 1]);
        DB::table('ciudad')->insert(['id_ciudad' => 1, 'nombre' => 'City', 'codigo' => '001', 'id_departamento' => 1]);
        DB::table('rol')->insert(['id_rol' => 1, 'nombre' => 'Admin']);
        DB::table('tipo_contrato')->insert(['id_tipo_contrato' => 1, 'nombre' => 'Indefinido']);
        DB::table('tipo_doc')->insert(['id_tipo_doc' => 1, 'nombre' => 'CC']);
        
        DB::table('tipo_trabajador')->insert(['id_tipo_trabajador' => 1, 'nombre' => 'Dependiente']);
        DB::table('sub_tipo_trabajador')->insert(['id_sub_tipo_trabajador' => 1, 'nombre' => 'No aplica']);
        DB::table('forma_pago')->insert(['id_forma_pago' => 1, 'nombre' => 'Mensual']);
        DB::table('metodo_pago')->insert(['id_metodo_pago' => 1, 'nombre' => 'Transferencia']);
        
        DB::table('eps')->insert(['id_eps' => 860066942, 'nombre' => 'Sura', 'codigo_pila' => 'EPS010']);
        DB::table('afp')->insert(['id_afp' => 800227940, 'nombre' => 'Proteccion', 'codigo_pila' => '230301']);
        DB::table('arl')->insert(['id_arl' => 860011153, 'nombre' => 'Sura ARL', 'codigo_pila' => '14-23']);
        DB::table('cajas_compensacion')->insert(['id_caja' => 890900841, 'nombre' => 'Comfama', 'codigo_pila' => 'CCF31']);
        DB::table('niveles_riesgo')->insert(['id' => 1, 'nombre' => 'Nivel 1', 'porcentaje' => 0.00522]);

        // Ensure parameters exist (Global table)
        DB::table('payroll_parameters')->insert([
            'id' => 1,
            'smmlv' => 1300000,
            'auxilio_transporte' => 162000,
            'auxilio_transporte_tope' => 2600000,
            'horas_mes' => 240,
            'eps_employee' => 0.04,
            'pension_employee' => 0.04,
            'eps_employer' => 0.085,
            'pension_employer' => 0.12,
            'arl_riesgo_1' => 0.00522,
            'caja_compensacion' => 0.04,
            'fondo_solidaridad' => 0.01,
            'fondo_solidaridad_threshold' => 5200000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Mock a TipoNovedad
        DB::table('tipo_novedad')->insert([
            ['id_tipo_novedad' => 1, 'nombre' => 'Incapacidad'],
            ['id_tipo_novedad' => 2, 'nombre' => 'Suspension'],
            ['id_tipo_novedad' => 3, 'nombre' => 'Vacaciones'],
        ]);

        // Resolve services AFTER seeding
        $this->calculator = app(NominaCalculatorService::class);
        $this->accrualService = app(BenefitAccrualService::class);

        // Setup base models
        $this->usuario = Usuario::create([
            'doc' => '123456',
            'id_tipo_doc' => 1,
            'primer_nombre' => 'Test',
            'primer_apellido' => 'User',
            'correo' => 'test@user.com',
            'contrasena' => bcrypt('password'),
            'id_ciudad' => 1,
            'direccion' => 'Calle 123',
            'telefono' => '3001234567',
            'id_rol' => 1
        ]);

        $this->empresa = Empresa::create([
            'nit' => '900123456',
            'razon_social' => 'Test Company',
            'doc_representante' => $this->usuario->doc,
            'id_ciudad' => 1,
            'direccion' => 'Calle Empresarial',
            'correo' => 'empresa@test.com',
            'telefono' => '5551234'
        ]);
        
        $this->contrato = Contrato::create([
            'doc' => $this->usuario->doc,
            'id_empresa' => $this->empresa->id_empresa,
            'id_tipo_contrato' => 1, 
            'id_tipo_trabajador' => 1,
            'id_sub_tipo_trabajador' => 1,
            'id_forma_pago' => 1,
            'id_metodo_pago' => 1,
            'id_arl' => 860011153,
            'id_eps' => 860066942,
            'id_afp' => 800227940,
            'id_caja' => 890900841,
            'salario_base' => 3000000,
            'fecha_inicio' => '2026-01-01',
            'estado' => Contrato::ESTADO_ACTIVO,
            'alto_riesgo' => false,
            'nivel_riesgo' => 1,
            'nivel_riesgo_id' => 1
        ]);

        $this->periodo = PeriodoLiquidacion::create([
            'id_empresa' => $this->empresa->id_empresa,
            'nombre' => 'Enero 2026',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-01-30',
            'estado' => 'abierto'
        ]);
        
        Schema::enableForeignKeyConstraints();
    }

    public function test_ige_does_not_reduce_benefit_days(): void
    {
        // 1. Create Salario stub
        $salario = Salario::create([
            'id_contrato' => $this->contrato->id_contrato,
            'id_periodo' => $this->periodo->id_periodo,
            'estado' => Salario::ESTADO_PENDIENTE
        ]);

        // 2. Create IGE Novelty (10 days)
        Novedad::create([
            'id_tipo_novedad' => 1,
            'id_salario' => $salario->id_salario,
            'id_periodo' => $this->periodo->id_periodo,
            'empleado_id' => $this->usuario->doc,
            'tipo_novedad_codigo' => 'IGE',
            'tipo_novedad_nombre' => 'Incapacidad Enfermedad General',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-01-10',
            'dias' => 10,
            'pago' => 0,
            'afecta_nomina' => true
        ]);

        // 3. Liquidate Payroll
        $this->calculator->guardarNominaEmpleado($this->contrato->id_contrato, $this->periodo->id_periodo);
        
        $salario->refresh();

        // Payroll days should be 20 (30 - 10)
        $this->assertEquals(20, $salario->dias_a_trabajar);
        // Benefit days should be 30 (Respects IGE)
        $this->assertEquals(30, $salario->dias_trabajados_prestacional);

        // 4. Run Accrual
        $this->accrualService->generateAccrualsForPeriod($this->periodo);

        // Check Prima Ledger Entry
        $ledger = BenefitLedger::where('employee_id', $this->usuario->doc)
            ->where('benefit_type', BenefitLedger::TYPE_PRIMA)
            ->first();

        // Record exists
        $this->assertNotNull($ledger);
        
        // Formula: 3,000,000 * 30 / 360 = 250,000
        $this->assertEquals(250000, round((float)$ledger->amount, 0));
    }

    public function test_maternity_leave_sets_base_salary_zero_and_pays_in_maternity_novedad(): void
    {
        $salario = Salario::create([
            'id_contrato' => $this->contrato->id_contrato,
            'id_periodo' => $this->periodo->id_periodo,
            'estado' => Salario::ESTADO_PENDIENTE
        ]);

        Novedad::create([
            'id_tipo_novedad' => 3,
            'id_salario' => $salario->id_salario,
            'id_periodo' => $this->periodo->id_periodo,
            'empleado_id' => $this->usuario->doc,
            'tipo_novedad_codigo' => 'LMAT',
            'tipo_novedad_nombre' => 'Licencia de Maternidad',
            'fecha_inicio' => '2026-01-10',
            'fecha_fin' => '2026-01-25',
            'dias' => 16,
            'pago' => 0,
            'afecta_nomina' => true
        ]);

        $this->calculator->guardarNominaEmpleado($this->contrato->id_contrato, $this->periodo->id_periodo);
        $salario->refresh();

        $this->assertEquals(0, $salario->dias_a_trabajar);
        $this->assertEquals(30, $salario->dias_trabajados_prestacional);

        // Salario base no debe pagarse y se paga solo la novedad de maternidad.
        $this->assertEquals(1600000, (int) $salario->total_devengado);
    }

    public function test_maternity_leave_rolls_over_to_next_period_and_deducts_days(): void
    {
        $periodo2 = PeriodoLiquidacion::create([
            'id_empresa' => $this->empresa->id_empresa,
            'nombre' => 'Febrero 2026',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-02-28',
            'estado' => 'abierto'
        ]);

        $salario1 = Salario::create([
            'id_contrato' => $this->contrato->id_contrato,
            'id_periodo' => $this->periodo->id_periodo,
            'estado' => Salario::ESTADO_PENDIENTE
        ]);

        Novedad::create([
            'id_tipo_novedad' => 3,
            'id_salario' => $salario1->id_salario,
            'id_periodo' => $this->periodo->id_periodo,
            'empleado_id' => $this->usuario->doc,
            'tipo_novedad_codigo' => 'LMAT',
            'tipo_novedad_nombre' => 'Licencia de Maternidad',
            'fecha_inicio' => '2026-01-10',
            'fecha_fin' => '2026-05-15',
            'dias' => 126,
            'pago' => 0,
            'afecta_nomina' => true
        ]);

        $this->calculator->guardarNominaEmpleado($this->contrato->id_contrato, $this->periodo->id_periodo);
        $salario1->refresh();

        $this->assertEquals(0, $salario1->dias_a_trabajar);
        $this->assertEquals(30, $salario1->dias_trabajados_prestacional);
        $this->assertEquals(2100000, (int) $salario1->total_devengado);

        $salario2 = Salario::create([
            'id_contrato' => $this->contrato->id_contrato,
            'id_periodo' => $periodo2->id_periodo,
            'estado' => Salario::ESTADO_PENDIENTE
        ]);

        $this->calculator->guardarNominaEmpleado($this->contrato->id_contrato, $periodo2->id_periodo);
        $salario2->refresh();

        $this->assertEquals(0, $salario2->dias_a_trabajar);
        $this->assertEquals(30, $salario2->dias_trabajados_prestacional);
        $this->assertEquals(2800000, (int) $salario2->total_devengado);
    }

    public function test_sln_does_reduce_benefit_days(): void
    {
        // 1. Create Salario stub
        $salario = Salario::create([
            'id_contrato' => $this->contrato->id_contrato,
            'id_periodo' => $this->periodo->id_periodo,
            'estado' => Salario::ESTADO_PENDIENTE
        ]);

        // 2. Create SLN Novelty (5 days)
        Novedad::create([
            'id_tipo_novedad' => 2,
            'id_salario' => $salario->id_salario,
            'id_periodo' => $this->periodo->id_periodo,
            'empleado_id' => $this->usuario->doc,
            'tipo_novedad_codigo' => 'SLN',
            'tipo_novedad_nombre' => 'Licencia No Remunerada',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-01-05',
            'dias' => 5,
            'pago' => 0,
            'afecta_nomina' => true
        ]);

        // 3. Liquidate Payroll
        $this->calculator->guardarNominaEmpleado($this->contrato->id_contrato, $this->periodo->id_periodo);
        
        $salario->refresh();

        // Payroll days should be 25
        $this->assertEquals(25, $salario->dias_a_trabajar);
        // Benefit days should also be 25
        $this->assertEquals(25, $salario->dias_trabajados_prestacional);

        // 4. Run Accrual
        $this->accrualService->generateAccrualsForPeriod($this->periodo);

        $ledger = BenefitLedger::where('employee_id', $this->usuario->doc)
            ->where('benefit_type', BenefitLedger::TYPE_PRIMA)
            ->first();

        // Formula: 3,000,000 * 25 / 360 = 208,333.33
        $this->assertEquals(208333, round((float)$ledger->amount, 0));
    }
}
