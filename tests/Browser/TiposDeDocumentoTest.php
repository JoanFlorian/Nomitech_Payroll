<?php

namespace Tests\Browser;

use Tests\Browser\BaseDuskTestCase;
use Tests\Browser\Traits\AuthenticatesSuperAdmin;
use Laravel\Dusk\Browser;
use App\Models\TipoDoc;

class TiposDeDocumentoTest extends BaseDuskTestCase
{
    use AuthenticatesSuperAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        TipoDoc::updateOrCreate(
            ['id_tipo_doc' => 1],
            ['nombre' => 'Cedula de Ciudadania']
        );

        TipoDoc::updateOrCreate(
            ['id_tipo_doc' => 2],
            ['nombre' => 'Cedula de Extranjeria']
        );

        TipoDoc::updateOrCreate(
            ['nombre' => 'Documento Para Editar'],
            ['nombre' => 'Documento Para Editar']
        );
    }

    /**
     * @dataProvider validationCreateProvider
     * @group validation
     */
    public function test_validaciones_al_crear_tipo_documento($dataName, $nombre, $expectedError)
    {
        $this->browse(function (Browser $browser) use ($dataName, $nombre, $expectedError) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-add-tipos de documento"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('nombre', $nombre)
                ->click('#btnGuardarAgregar');

            // Capturar screenshot
            $this->takeValidationScreenshot($browser, static::class, "Crear_TipoDoc_" . $dataName);

            $browser->waitForText('Error de Validación', 30)
                ->assertSee($expectedError)
                ->pause(1000);
        });
    }

    /**
     * @group smoke
     */
    public function test_creacion_exitosa_tipo_documento()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-add-tipos de documento"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('nombre', 'Pasaporte')
                ->click('#btnGuardarAgregar')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('tipo_doc', [
            'nombre' => 'Pasaporte'
        ]);
    }

    /**
     * @dataProvider validationEditProvider
     * @group validation
     */
    public function test_validaciones_al_editar_tipo_documento($dataName, $nombre, $expectedError)
    {
        $this->browse(function (Browser $browser) use ($dataName, $nombre, $expectedError) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-edit-tipos de documento"]')
                ->waitFor('#modalContenido table', 15)
                ->type('#buscar-items', 'Documento Para Editar')
                ->pause(1000)
                ->click('#modalVer tbody tr:first-child button')
                ->waitFor('#modalEdicion.flex', 10)
                ->pause(500)
                ->clear('#modalEdicion input[name="nombre"]')
                ->type('#modalEdicion input[name="nombre"]', $nombre)
                ->click('button[dusk="btn-guardar-edicion"]');

            // Capturar screenshot
            $this->takeValidationScreenshot($browser, static::class, "Editar_TipoDoc_" . $dataName);

            $browser->waitForText('Error de Validación', 30)
                ->assertSee($expectedError)
                ->pause(1000);
        });
    }

    /**
     * @group smoke
     */
    public function test_edicion_exitosa_tipo_documento()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-edit-tipos de documento"]')
                ->waitFor('#modalContenido table', 15)
                ->type('#buscar-items', 'Documento Para Editar')
                ->pause(1000)
                ->click('#modalVer tbody tr:first-child button')
                ->waitFor('#modalEdicion.flex', 10)
                ->pause(500)
                ->clear('#modalEdicion input[name="nombre"]')
                ->type('#modalEdicion input[name="nombre"]', 'Pasaporte Editado')
                ->click('button[dusk="btn-guardar-edicion"]')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('tipo_doc', [
            'nombre' => 'Pasaporte Editado'
        ]);
    }

    public static function validationCreateProvider()
    {
        return [
            'TC-TDOC-01 Vacio' => ['TDOC-01', '', 'El nombre es obligatorio.'],
            'TC-TDOC-02 Num' => ['TDOC-02', '123', 'El nombre solo debe contener letras y espacios.'],
            'TC-TDOC-03 Especiales' => ['TDOC-03', '$$$', 'El nombre solo debe contener letras y espacios.'],
            'TC-TDOC-04 Min' => ['TDOC-04', 'A', 'El nombre debe tener al menos 2 caracteres.'],
            'TC-TDOC-05 Max' => ['TDOC-05', str_repeat('A', 51), 'El nombre no puede tener más de 50 caracteres.'],
            'TC-TDOC-07 Duplicado' => ['TDOC-07', 'Cedula de Ciudadania', 'El nombre ya se encuentra registrado.'],
            'TC-TDOC-08 SQLi' => ['TDOC-08', 'CC OR 1=1', 'El nombre solo debe contener letras y espacios.'],
            'TC-TDOC-09 XSS' => ['TDOC-09', '<script>alert(1)</script>', 'El nombre solo debe contener letras y espacios.'],
        ];
    }

    public static function validationEditProvider()
    {
        return [
            'TC-TDOC-01 Vacio' => ['TDOC-01_E', '', 'El nombre es obligatorio.'],
            'TC-TDOC-02 Num' => ['TDOC-02_E', '123', 'El nombre solo debe contener letras y espacios.'],
            'TC-TDOC-03 Especiales' => ['TDOC-03_E', '$$$', 'El nombre solo debe contener letras y espacios.'],
            'TC-TDOC-04 Min' => ['TDOC-04_E', 'A', 'El nombre debe tener al menos 2 caracteres.'],
            'TC-TDOC-05 Max' => ['TDOC-05_E', str_repeat('A', 51), 'El nombre no puede tener más de 50 caracteres.'],
            'TC-TDOC-07 Duplicado' => ['TDOC-07_E', 'Cedula de Extranjeria', 'El nombre ya se encuentra registrado.'],
            'TC-TDOC-08 SQLi' => ['TDOC-08_E', 'CC OR 1=1', 'El nombre solo debe contener letras y espacios.'],
            'TC-TDOC-09 XSS' => ['TDOC-09_E', '<script>alert(1)</script>', 'El nombre solo debe contener letras y espacios.'],
        ];
    }
}
