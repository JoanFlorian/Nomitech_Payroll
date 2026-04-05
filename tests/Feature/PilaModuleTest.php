<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\PeriodoLiquidacion;
use App\Models\Usuario;
use App\Models\Contrato;
use App\Models\Salario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PilaModuleTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private PeriodoLiquidacion $periodo;
    private Usuario $usuario;
    private Contrato $contrato;
    private Salario $salario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();
    }

    private function setupTestData(): void
    {
        // Crear empresa
        $this->empresa = Empresa::factory()->create([
            'nit' => '123456789',
            'razon_social' => 'Empresa Test',
        ]);

        // Crear período abierto
        $this->periodo = PeriodoLiquidacion::factory()->create([
            'id_empresa' => $this->empresa->id_empresa,
            'estado' => PeriodoLiquidacion::ESTADO_ABIERTO,
            'fecha_inicio' => now()->startOfMonth(),
            'fecha_fin' => now()->endOfMonth(),
        ]);

        // Crear usuario/empleado
        $this->usuario = Usuario::factory()->create([
            'doc' => '1234567890',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
        ]);

        // Crear contrato
        $this->contrato = Contrato::factory()->create([
            'id_empresa' => $this->empresa->id_empresa,
            'doc' => $this->usuario->doc,
            'id_tipo_contrato' => 1,
            'salario_base' => 2000000,
            'activo' => true,
        ]);

        // Crear salario
        $this->salario = Salario::factory()->create([
            'id_periodo' => $this->periodo->id_periodo,
            'id_contrato' => $this->contrato->id_contrato,
            'dias_a_trabajar' => 30,
        ]);

        session(['empresa_id' => $this->empresa->id_empresa]);
    }

    /** @test */
    public function test_pila_index_muestra_periodos_abiertos()
    {
        $response = $this->get(route('pila.index'));

        $response->assertStatus(200);
        $response->assertViewHas('periodos');
        
        $periodos = $response->original->getData()['periodos'];
        $this->assertNotEmpty($periodos);
        $this->assertTrue($periodos->contains('id_periodo', $this->periodo->id_periodo));
    }

    /** @test */
    public function test_no_muestra_periodos_cerrados_sin_cambios()
    {
        $periodoCerrado = PeriodoLiquidacion::factory()->create([
            'id_empresa' => $this->empresa->id_empresa,
            'estado' => PeriodoLiquidacion::ESTADO_CERRADO,
            'fecha_inicio' => now()->subMonth()->startOfMonth(),
            'fecha_fin' => now()->subMonth()->endOfMonth(),
        ]);

        $response = $this->get(route('pila.index'));
        $periodos = $response->original->getData()['periodos'];

        // Período cerrado no debe aparecer porque no tiene cambios
        $this->assertFalse($periodos->contains('id_periodo', $periodoCerrado->id_periodo));
    }

    /** @test */
    public function test_generar_pila_exitosamente()
    {
        $response = $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        $response->assertRedirect();
        
        // Verificar que se creó registro en planilla_pila
        $this->assertDatabaseHas('planilla_pila', [
            'id_empresa' => $this->empresa->id_empresa,
            'id_periodo' => $this->periodo->id_periodo,
            'estado' => 'generada',
        ]);

        // Verificar que se guardó el hash
        $planilla = DB::table('planilla_pila')
            ->where('id_empresa', $this->empresa->id_empresa)
            ->where('id_periodo', $this->periodo->id_periodo)
            ->first();

        $this->assertNotNull($planilla->datos_hash);
    }

    /** @test */
    public function test_bloquea_regeneracion_sin_cambios()
    {
        // Generar PILA primera vez
        $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        // Intentar regenerar sin cambios
        $response = $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        $response->assertRedirect()->withErrors();
        $response->assertSessionHasErrors('pila');
    }

    /** @test */
    public function test_permite_regeneracion_con_cambios_salario()
    {
        // Generar PILA primera vez
        $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        // Obtener hash anterior
        $planillaAntes = DB::table('planilla_pila')
            ->where('id_empresa', $this->empresa->id_empresa)
            ->where('id_periodo', $this->periodo->id_periodo)
            ->first();
        $hashAntes = $planillaAntes->datos_hash;

        // Cambiar salario
        $this->salario->update(['dias_a_trabajar' => 25]);

        // Intentar regenerar
        $response = $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        $response->assertRedirect();

        // Obtener hash después
        $planillaDespues = DB::table('planilla_pila')
            ->where('id_empresa', $this->empresa->id_empresa)
            ->where('id_periodo', $this->periodo->id_periodo)
            ->first();

        // Hash debe cambiar
        $this->assertNotEquals($hashAntes, $planillaDespues->datos_hash);
    }

    /** @test */
    public function test_invalida_hash_con_cambio_entidad_seguridad_social()
    {
        // Generar PILA primera vez
        $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        // Obtener hash
        $planillaAntes = DB::table('planilla_pila')
            ->where('id_empresa', $this->empresa->id_empresa)
            ->where('id_periodo', $this->periodo->id_periodo)
            ->first();

        $this->assertNotNull($planillaAntes->datos_hash);

        // Cambiar EPS del contrato
        $this->contrato->update(['id_eps' => 2]);

        // Verificar que el hash fue invalidado
        $planillaDespues = DB::table('planilla_pila')
            ->where('id_empresa', $this->empresa->id_empresa)
            ->first();

        $this->assertNull($planillaDespues->datos_hash);
    }

    /** @test */
    public function test_permite_regeneracion_despues_cambio_entidad()
    {
        // Generar PILA primera vez
        $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        // Cambiar EPS (invalida hash)
        $this->contrato->update(['id_eps' => 2]);

        // Intentar regenerar debe permitir
        $response = $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        $response->assertRedirect();

        // Verificar que se generó con nuevo hash
        $planilla = DB::table('planilla_pila')
            ->where('id_empresa', $this->empresa->id_empresa)
            ->where('id_periodo', $this->periodo->id_periodo)
            ->first();

        $this->assertNotNull($planilla->datos_hash);
    }

    /** @test */
    public function test_genera_pila_en_periodo_cerrado()
    {
        // Cerrar período
        $this->periodo->update(['estado' => PeriodoLiquidacion::ESTADO_CERRADO]);

        // Intentar generar debe funcionar
        $response = $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        $response->assertRedirect();

        // Verificar que se creó la PILA
        $this->assertDatabaseHas('planilla_pila', [
            'id_empresa' => $this->empresa->id_empresa,
            'id_periodo' => $this->periodo->id_periodo,
        ]);
    }

    /** @test */
    public function test_historial_muestra_estado_disponible()
    {
        // Generar PILA
        $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        $response = $this->get(route('pila.index', [
            'id_periodo' => $this->periodo->id_periodo,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('historialPila');

        $historial = $response->original->getData()['historialPila'];
        $this->assertNotEmpty($historial);

        // Primer registro debe tener tiene_cambios = false
        $primerRegistro = $historial->first();
        $this->assertFalse($primerRegistro->tiene_cambios);
    }

    /** @test */
    public function test_historial_muestra_estado_pendiente_si_hay_cambios()
    {
        // Generar PILA
        $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        // Cambiar salario
        $this->salario->update(['dias_a_trabajar' => 25]);

        $response = $this->get(route('pila.index', [
            'id_periodo' => $this->periodo->id_periodo,
        ]));

        $historial = $response->original->getData()['historialPila'];
        
        // Registro debe tener tiene_cambios = true
        $primerRegistro = $historial->first();
        $this->assertTrue($primerRegistro->tiene_cambios);
    }

    /** @test */
    public function test_descarga_pila_exitosamente()
    {
        // Generar PILA
        $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        // Obtener el archivo
        $response = $this->post(route('pila.descargar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** @test */
    public function test_historial_limpio_solo_una_version()
    {
        // Generar PILA primera vez
        $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        $historialPrimera = DB::table('pila_archivos')
            ->where('empresa_id', $this->empresa->id_empresa)
            ->where('periodo_id', $this->periodo->id_periodo)
            ->count();

        $this->assertEquals(1, $historialPrimera);

        // Cambiar salario y regenerar
        $this->salario->update(['dias_a_trabajar' => 25]);
        $this->post(route('pila.generar'), [
            'id_periodo' => $this->periodo->id_periodo,
        ]);

        $historialSegunda = DB::table('pila_archivos')
            ->where('empresa_id', $this->empresa->id_empresa)
            ->where('periodo_id', $this->periodo->id_periodo)
            ->count();

        // Debe seguir habiendo solo 1 archivo (el anterior se limpió)
        $this->assertEquals(1, $historialSegunda);
    }
}
