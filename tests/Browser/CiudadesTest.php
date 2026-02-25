<?php

namespace Tests\Browser;

use Tests\Browser\BaseDuskTestCase;
use Tests\Browser\Traits\AuthenticatesSuperAdmin;
use Laravel\Dusk\Browser;
use App\Models\Departamento;
use App\Models\Ciudad;

/**
 * @group smoke
 * @group validation
 */
class CiudadesTest extends BaseDuskTestCase
{
    use AuthenticatesSuperAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $pais = \App\Models\Pais::updateOrCreate(
            ['id_pais' => 1],
            ['nombre' => 'Colombia', 'codigo_alfa2' => 'CO', 'codigo_alfa3' => 'COL', 'codigo_numerico' => '170']
        );

        Departamento::updateOrCreate(
            ['id_departamento' => 1],
            ['codigo' => 'D-01', 'nombre' => 'Departamento Test Dusk', 'codigo_iso' => 'TD-01', 'id_pais' => $pais->id_pais]
        );

        // Ciudades para validaciones de duplicado y para edición
        Ciudad::updateOrCreate(
            ['codigo' => '11001'],
            ['nombre' => 'Bogota', 'id_departamento' => 1]
        );

        Ciudad::updateOrCreate(
            ['codigo' => '99999'],
            ['nombre' => 'Ciudad Para Editar', 'id_departamento' => 1]
        );
        // Ciudad para validación de duplicado de nombre en edición
        Ciudad::updateOrCreate(
            ['codigo' => '12345'],
            ['nombre' => 'Pereira', 'id_departamento' => 1]
        );
    }

    /**
     * @dataProvider validationCreateProvider
     * @group validation
     */
    public function test_validaciones_al_crear_ciudad($dataName, $codigo, $nombre, $expectedError)
    {
        $this->browse(function (Browser $browser) use ($dataName, $codigo, $nombre, $expectedError) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-add-ciudades"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('codigo', $codigo)
                ->type('nombre', $nombre)
                ->select('cod_dep', 'D-01')
                ->click('#btnGuardarAgregar');

            // Capturar screenshot
            $this->takeValidationScreenshot($browser, static::class, "Crear_Ciudad_" . $dataName);

            $browser->waitForText('Error de Validación', 30)
                ->assertSee($expectedError)
                ->pause(1000);
        });
    }

    /**
     * @dataProvider validationEditProvider
     * @group validation
     */
    public function test_validaciones_al_editar_ciudad($dataName, $codigo, $nombre, $expectedError)
    {
        $this->browse(function (Browser $browser) use ($dataName, $codigo, $nombre, $expectedError) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-edit-ciudades"]')
                ->waitFor('#modalContenido table', 15)
                ->type('#buscar-items', '99999')
                ->pause(1000)
                ->click('#modalVer tbody tr:first-child button')
                ->waitFor('#modalEdicion.flex', 10)
                ->pause(500)
                ->clear('#modalEdicion input[name="codigo"]')
                ->type('#modalEdicion input[name="codigo"]', $codigo)
                ->clear('#modalEdicion input[name="nombre"]')
                ->type('#modalEdicion input[name="nombre"]', $nombre)
                ->click('button[dusk="btn-guardar-edicion"]');

            // Capturar screenshot
            $this->takeValidationScreenshot($browser, static::class, "Editar_Ciudad_" . $dataName);

            $browser->waitForText('Error de Validación', 30)
                ->assertSee($expectedError)
                ->pause(1000);
        });
    }

    /**
     * @group smoke
     */
    public function test_creacion_exitosa_ciudad_tc_ciu_cod_08_ceros()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-add-ciudades"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('codigo', '00000')
                ->type('nombre', 'San Jose')
                ->select('cod_dep', 'D-01')
                ->click('#btnGuardarAgregar')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('ciudad', [
            'codigo' => '00000',
            'nombre' => 'San Jose'
        ]);
    }

    /**
     * @group smoke
     */
    public function test_creacion_exitosa_ciudad_tc_ciu_cod_06_valido()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-add-ciudades"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('codigo', '33003')
                ->type('nombre', 'Ciudad Nueva Valida')
                ->select('cod_dep', 'D-01')
                ->click('#btnGuardarAgregar')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('ciudad', [
            'codigo' => '33003',
            'nombre' => 'Ciudad Nueva Valida'
        ]);
    }

    /**
     * @group smoke
     */
    public function test_edicion_exitosa_ciudad()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-edit-ciudades"]')
                ->waitFor('#modalContenido table', 15)
                ->type('#buscar-items', '99999')
                ->pause(1000)
                ->click('#modalVer tbody tr:first-child button')
                ->waitFor('#modalEdicion.flex', 10)
                ->pause(500)
                ->clear('#modalEdicion input[name="codigo"]')
                ->type('#modalEdicion input[name="codigo"]', '88888')
                ->clear('#modalEdicion input[name="nombre"]')
                ->type('#modalEdicion input[name="nombre"]', 'Ciudad Editada Exito')
                ->click('button[dusk="btn-guardar-edicion"]')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('ciudad', [
            'codigo' => '88888',
            'nombre' => 'Ciudad Editada Exito'
        ]);
    }

    public static function validationCreateProvider()
    {
        return [
            'TC-CIU-COD-01 Vacio' => ['CIU-COD-01', '', 'Ciudad Test', 'El código es obligatorio.'],
            'TC-CIU-COD-02 Texto' => ['CIU-COD-02', 'ABC', 'Ciudad Test', 'El código debe contener solo números positivos.'],
            'TC-CIU-COD-03 Alfanumerico' => ['CIU-COD-03', '12A34', 'Ciudad Test', 'El código debe contener solo números positivos.'],
            'TC-CIU-COD-04 Min' => ['CIU-COD-04', '1', 'Ciudad Test', 'El código debe tener al menos 2 dígitos.'],
            'TC-CIU-COD-05 Max' => ['CIU-COD-05', '123456789012', 'Ciudad Test', 'El código no puede tener más de 11 dígitos.'],
            'TC-CIU-COD-07 Negativo' => ['CIU-COD-07', '-100', 'Ciudad Test', 'El código debe contener solo números positivos.'],
            'TC-CIU-COD-09 SQLi' => ['CIU-COD-09', '11001 OR 1=1', 'Ciudad Test', 'El código debe contener solo números positivos.'],
            'TC-CIU-COD-10 XSS' => ['CIU-COD-10', '<script>alert(1)</script>', 'Ciudad Test', 'El código debe contener solo números positivos.'],
            'TC-CIU-COD-11 Duplicado' => ['CIU-COD-11', '11001', 'Ciudad Test', 'El código ya se encuentra registrado.'],
            'TC-CIU-NOM-01 Vacio' => ['CIU-NOM-01', '54321', '', 'El nombre es obligatorio.'],
            'TC-CIU-NOM-02 Num' => ['CIU-NOM-02', '54321', '12345', 'El nombre solo debe contener letras y espacios.'],
            'TC-CIU-NOM-03 Especiales' => ['CIU-NOM-03', '54321', '@@@@', 'El nombre solo debe contener letras y espacios.'],
            'TC-CIU-NOM-04 Min' => ['CIU-NOM-04', '54321', 'A', 'El nombre debe tener al menos 2 caracteres.'],
            'TC-CIU-NOM-05 Max' => ['CIU-NOM-05', '54321', str_repeat('A', 101), 'El nombre no puede tener más de 100 caracteres.'],
            'TC-CIU-NOM-08 SQLi' => ['CIU-NOM-08', '54321', "Bogota' OR 1=1", 'El nombre solo debe contener letras y espacios.'],
            'TC-CIU-NOM-09 XSS' => ['CIU-NOM-09', '54321', '<script>alert(1)</script>', 'El nombre solo debe contener letras y espacios.'],
            'TC-CIU-NOM-10 Duplicado' => ['CIU-NOM-10', '54321', 'Bogota', 'El nombre ya se encuentra registrado.'],
        ];
    }

    public static function validationEditProvider()
    {
        return [
            'TC-CIU-COD-01 Vacio' => ['CIU-COD-01_E', '', 'Ciudad Test', 'El código es obligatorio.'],
            'TC-CIU-COD-02 Texto' => ['CIU-COD-02_E', 'ABC', 'Ciudad Test', 'El código debe contener solo números positivos.'],
            'TC-CIU-COD-03 Alfanumerico' => ['CIU-COD-03_E', '12A34', 'Ciudad Test', 'El código debe contener solo números positivos.'],
            'TC-CIU-COD-04 Min' => ['CIU-COD-04_E', '1', 'Ciudad Test', 'El código debe tener al menos 2 dígitos.'],
            'TC-CIU-COD-05 Max' => ['CIU-COD-05_E', '123456789012', 'Ciudad Test', 'El código no puede tener más de 11 dígitos.'],
            'TC-CIU-COD-07 Negativo' => ['CIU-COD-07_E', '-100', 'Ciudad Test', 'El código debe contener solo números positivos.'],
            'TC-CIU-COD-09 SQLi' => ['CIU-COD-09_E', '11001 OR 1=1', 'Ciudad Test', 'El código debe contener solo números positivos.'],
            'TC-CIU-COD-10 XSS' => ['CIU-COD-10_E', '<script>alert(1)</script>', 'Ciudad Test', 'El código debe contener solo números positivos.'],
            'TC-CIU-COD-11 Duplicado' => ['CIU-COD-11_E', '11001', 'Ciudad Test', 'El código ya se encuentra registrado.'],
            'TC-CIU-NOM-01 Vacio' => ['CIU-NOM-01_E', '88888', '', 'El nombre es obligatorio.'],
            'TC-CIU-NOM-02 Num' => ['CIU-NOM-02_E', '88888', '12345', 'El nombre solo debe contener letras y espacios.'],
            'TC-CIU-NOM-03 Especiales' => ['CIU-NOM-03_E', '88888', '@@@@', 'El nombre solo debe contener letras y espacios.'],
            'TC-CIU-NOM-04 Min' => ['CIU-NOM-04_E', '88888', 'A', 'El nombre debe tener al menos 2 caracteres.'],
            'TC-CIU-NOM-05 Max' => ['CIU-NOM-05_E', '88888', str_repeat('A', 101), 'El nombre no puede tener más de 100 caracteres.'],
            'TC-CIU-NOM-08 SQLi' => ['CIU-NOM-08_E', '88888', "Bogota' OR 1=1", 'El nombre solo debe contener letras y espacios.'],
            'TC-CIU-NOM-09 XSS' => ['CIU-NOM-09_E', '88888', '<script>alert(1)</script>', 'El nombre solo debe contener letras y espacios.'],
            'TC-CIU-NOM-10 Duplicado' => ['CIU-NOM-10_E', '88888', 'Pereira', 'El nombre ya se encuentra registrado.'],
        ];
    }
}
