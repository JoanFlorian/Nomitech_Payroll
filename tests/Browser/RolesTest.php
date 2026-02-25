<?php

namespace Tests\Browser;

use Tests\Browser\BaseDuskTestCase;
use Tests\Browser\Traits\AuthenticatesSuperAdmin;
use Laravel\Dusk\Browser;
use App\Models\Rol;

class RolesTest extends BaseDuskTestCase
{
    use AuthenticatesSuperAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::updateOrCreate(
            ['nombre' => 'Administrador'],
            ['nombre' => 'Administrador', 'descripcion' => 'Rol admin']
        );

        Rol::updateOrCreate(
            ['nombre' => 'Rol Para Editar'],
            ['nombre' => 'Rol Para Editar', 'descripcion' => 'Desc']
        );
    }

    /**
     * @dataProvider validationCreateProvider
     * @group validation
     */
    public function test_validaciones_al_crear_rol($dataName, $nombre, $descripcion, $expectedError)
    {
        $this->browse(function (Browser $browser) use ($dataName, $nombre, $descripcion, $expectedError) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-add-roles"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('nombre', $nombre)
                ->type('descripcion', $descripcion)
                ->click('#btnGuardarAgregar');

            // Capturar screenshot
            $this->takeValidationScreenshot($browser, static::class, "Crear_Rol_" . $dataName);

            $browser->waitForText('Error de Validación', 30)
                ->assertSee($expectedError)
                ->pause(1000);
        });
    }

    /**
     * @group smoke
     */
    public function test_creacion_exitosa_rol()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-add-roles"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('nombre', 'Arquitecto de Software')
                ->type('descripcion', 'Rol con permisos administrativos')
                ->click('#btnGuardarAgregar')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('rol', [
            'nombre' => 'Arquitecto de Software',
            'descripcion' => 'Rol con permisos administrativos'
        ]);
    }

    /**
     * @dataProvider validationEditProvider
     * @group validation
     */
    public function test_validaciones_al_editar_rol($dataName, $nombre, $descripcion, $expectedError)
    {
        $this->browse(function (Browser $browser) use ($dataName, $nombre, $descripcion, $expectedError) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-edit-roles"]')
                ->waitFor('#modalContenido table', 15)
                ->type('#buscar-items', 'Rol Para Editar')
                ->pause(1000)
                ->click('#modalVer tbody tr:first-child button')
                ->waitFor('#modalEdicion.flex', 10)
                ->pause(500)
                ->clear('#modalEdicion input[name="nombre"]')
                ->type('#modalEdicion input[name="nombre"]', $nombre)
                ->clear('#modalEdicion textarea[name="descripcion"]')
                ->type('#modalEdicion textarea[name="descripcion"]', $descripcion)
                ->click('button[dusk="btn-guardar-edicion"]');

            // Capturar screenshot
            $this->takeValidationScreenshot($browser, static::class, "Editar_Rol_" . $dataName);

            $browser->waitForText('Error de Validación', 30)
                ->assertSee($expectedError)
                ->pause(1000);
        });
    }

    /**
     * @group smoke
     */
    public function test_edicion_exitosa_rol_vaciando_descripcion()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones')
                ->waitForText('Módulo de Actualizaciones')
                ->click('button[dusk="button-edit-roles"]')
                ->waitFor('#modalContenido table', 15)
                ->type('#buscar-items', 'Rol Para Editar')
                ->pause(1000)
                ->click('#modalVer tbody tr:first-child button')
                ->waitFor('#modalEdicion.flex', 10)
                ->pause(500)
                ->clear('#modalEdicion input[name="nombre"]')
                ->type('#modalEdicion input[name="nombre"]', 'Rol Editado Exito')
                ->clear('#modalEdicion textarea[name="descripcion"]')
                ->click('button[dusk="btn-guardar-edicion"]')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('rol', [
            'nombre' => 'Rol Editado Exito',
            'descripcion' => null
        ]);
    }

    public static function validationCreateProvider()
    {
        return [
            'TC-CARGO-NOM-01 Vacio' => ['CARGO-NOM-01', '', 'Descr', 'El nombre del rol es obligatorio.'],
            'TC-CARGO-NOM-02 Num' => ['CARGO-NOM-02', '123', 'Descr', 'El nombre solo debe contener letras y espacios.'],
            'TC-CARGO-NOM-03 Especiales' => ['CARGO-NOM-03', '###', 'Descr', 'El nombre solo debe contener letras y espacios.'],
            'TC-CARGO-NOM-04 Min' => ['CARGO-NOM-04', 'A', 'Descr', 'El nombre del rol debe tener al menos 2 caracteres.'],
            'TC-CARGO-NOM-05 Max' => ['CARGO-NOM-05', str_repeat('A', 101), 'Descr', 'El nombre del rol no puede tener más de 100 caracteres.'],
            'TC-CARGO-NOM-07 Duplicado' => ['CARGO-NOM-07', 'Administrador', 'Descr', 'El nombre del rol ya se encuentra registrado.'],
            'TC-CARGO-NOM-08 SQLi' => ['CARGO-NOM-08', 'Admin OR 1=1', 'Descr', 'El nombre solo debe contener letras y espacios.'],
            'TC-CARGO-NOM-09 XSS' => ['CARGO-NOM-09', '<script>alert(1)</script>', 'Descr', 'El nombre solo debe contener letras y espacios.'],

            'TC-CARGO-DES-02 Simbolos' => ['CARGO-DES-02', 'Rol Valido', '@@@@', 'La descripción solo puede tener letras, espacios y números.'],
            'TC-CARGO-DES-03 Min' => ['CARGO-DES-03', 'Rol Valido', 'ABC', 'La descripción debe tener al menos 5 letras o números.'],
            'TC-CARGO-DES-04 Max' => ['CARGO-DES-04', 'Rol Valido', str_repeat('A', 256), 'La descripción no puede exceder 255 caracteres.'],
            'TC-CARGO-DES-06 SQLi' => ['CARGO-DES-06', 'Rol Valido', 'desc OR 1=1', 'La descripción solo puede tener letras, espacios y números.'],
            'TC-CARGO-DES-07 XSS' => ['CARGO-DES-07', 'Rol Valido', '<script>alert(1)</script>', 'La descripción solo puede tener letras, espacios y números.'],
        ];
    }

    public static function validationEditProvider()
    {
        return [
            'TC-CARGO-NOM-01 Vacio' => ['CARGO-NOM-01_E', '', 'Descr', 'El nombre del rol es obligatorio.'],
            'TC-CARGO-NOM-02 Num' => ['CARGO-NOM-02_E', '123', 'Descr', 'El nombre solo debe contener letras y espacios.'],
            'TC-CARGO-NOM-03 Especiales' => ['CARGO-NOM-03_E', '###', 'Descr', 'El nombre solo debe contener letras y espacios.'],
            'TC-CARGO-NOM-04 Min' => ['CARGO-NOM-04_E', 'A', 'Descr', 'El nombre del rol debe tener al menos 2 caracteres.'],
            'TC-CARGO-NOM-05 Max' => ['CARGO-NOM-05_E', str_repeat('A', 101), 'Descr', 'El nombre del rol no puede tener más de 100 caracteres.'],
            'TC-CARGO-NOM-07 Duplicado' => ['CARGO-NOM-07_E', 'Administrador', 'Descr', 'El nombre del rol ya se encuentra registrado.'],
            'TC-CARGO-NOM-08 SQLi' => ['CARGO-NOM-08_E', 'Admin OR 1=1', 'Descr', 'El nombre solo debe contener letras y espacios.'],
            'TC-CARGO-NOM-09 XSS' => ['CARGO-NOM-09_E', '<script>alert(1)</script>', 'Descr', 'El nombre solo debe contener letras y espacios.'],

            'TC-CARGO-DES-02 Simbolos' => ['CARGO-DES-02_E', 'Rol Valido', '@@@@', 'La descripción solo puede tener letras, espacios y números.'],
            'TC-CARGO-DES-03 Min' => ['CARGO-DES-03_E', 'Rol Valido', 'ABC', 'La descripción debe tener al menos 5 letras o números.'],
            'TC-CARGO-DES-04 Max' => ['CARGO-DES-04_E', 'Rol Valido', str_repeat('A', 256), 'La descripción no puede exceder 255 caracteres.'],
            'TC-CARGO-DES-06 SQLi' => ['CARGO-DES-06_E', 'Rol Valido', 'desc OR 1=1', 'La descripción solo puede tener letras, espacios y números.'],
            'TC-CARGO-DES-07 XSS' => ['CARGO-DES-07_E', 'Rol Valido', '<script>alert(1)</script>', 'La descripción solo puede tener letras, espacios y números.'],
        ];
    }
}
