<?php

namespace Tests\Browser;

use Tests\Browser\BaseDuskTestCase;
use Tests\Browser\Traits\AuthenticatesSuperAdmin;
use Laravel\Dusk\Browser;
use App\Models\FormaPago;

class FormasDePagoTest extends BaseDuskTestCase
{
    use AuthenticatesSuperAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // TC-FPAGO-07 Duplicado
        FormaPago::updateOrCreate(
            ['nombre' => 'Crédito'],
            ['nombre' => 'Crédito']
        );

        FormaPago::updateOrCreate(
            ['nombre' => 'Forma Para Editar'],
            ['nombre' => 'Forma Para Editar']
        );
    }

    /**
     * @dataProvider validationCreateProvider
     * @group validation
     */
    public function test_validaciones_al_crear_forma_pago($dataName, $nombre, $expectedError)
    {
        $this->browse(function (Browser $browser) use ($dataName, $nombre, $expectedError) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones?page=2')
                ->waitForText('Módulo de Actualizaciones')
                ->waitFor('button[dusk="button-add-formas de pago"]', 10)
                ->click('button[dusk="button-add-formas de pago"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('nombre', $nombre)
                ->click('#btnGuardarAgregar');

            // Capturar screenshot
            $this->takeValidationScreenshot($browser, static::class, "Crear_FormaPago_" . $dataName);

            $browser->waitForText('Error de Validación', 30)
                ->assertSee($expectedError)
                ->pause(1000);
        });
    }

    /**
     * @group smoke
     */
    public function test_creacion_exitosa_forma_pago()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones?page=2')
                ->waitForText('Módulo de Actualizaciones')
                ->waitFor('button[dusk="button-add-formas de pago"]', 10)
                ->click('button[dusk="button-add-formas de pago"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('nombre', 'Contado')
                ->click('#btnGuardarAgregar')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('forma_pago', [
            'nombre' => 'Contado'
        ]);
    }

    /**
     * @dataProvider validationEditProvider
     * @group validation
     */
    public function test_validaciones_al_editar_forma_pago($dataName, $nombre, $expectedError)
    {
        $this->browse(function (Browser $browser) use ($dataName, $nombre, $expectedError) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones?page=2')
                ->waitForText('Módulo de Actualizaciones')
                ->waitFor('button[dusk="button-edit-formas de pago"]', 10)
                ->click('button[dusk="button-edit-formas de pago"]')
                ->waitFor('#modalContenido table', 15)
                ->type('#buscar-items', 'Forma Para Editar')
                ->pause(1000)
                ->click('#modalVer tbody tr:first-child button')
                ->waitFor('#modalEdicion.flex', 10)
                ->pause(500)
                ->clear('#modalEdicion input[name="nombre"]')
                ->type('#modalEdicion input[name="nombre"]', $nombre)
                ->click('button[dusk="btn-guardar-edicion"]');

            // Capturar screenshot
            $this->takeValidationScreenshot($browser, static::class, "Editar_FormaPago_" . $dataName);

            $browser->waitForText('Error de Validación', 30)
                ->assertSee($expectedError)
                ->pause(1000);
        });
    }

    /**
     * @group smoke
     */
    public function test_edicion_exitosa_forma_pago()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones?page=2')
                ->waitForText('Módulo de Actualizaciones')
                ->waitFor('button[dusk="button-edit-formas de pago"]', 10)
                ->click('button[dusk="button-edit-formas de pago"]')
                ->waitFor('#modalContenido table', 15)
                ->type('#buscar-items', 'Forma Para Editar')
                ->pause(1000)
                ->click('#modalVer tbody tr:first-child button')
                ->waitFor('#modalEdicion.flex', 10)
                ->pause(500)
                ->clear('#modalEdicion input[name="nombre"]')
                ->type('#modalEdicion input[name="nombre"]', 'Cheque Editado')
                ->click('button[dusk="btn-guardar-edicion"]')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('forma_pago', [
            'nombre' => 'Cheque Editado'
        ]);
    }

    public static function validationCreateProvider()
    {
        return [
            'TC-FPAGO-01 Vacio' => ['FPAGO-01', '', 'El nombre es obligatorio.'],
            'TC-FPAGO-02 Num' => ['FPAGO-02', '123', 'El nombre solo debe contener letras y espacios.'],
            'TC-FPAGO-03 Especiales' => ['FPAGO-03', '$$$', 'El nombre solo debe contener letras y espacios.'],
            'TC-FPAGO-04 Min' => ['FPAGO-04', 'A', 'El nombre debe tener al menos 2 caracteres.'],
            'TC-FPAGO-05 Max' => ['FPAGO-05', str_repeat('A', 51), 'El nombre no puede tener más de 50 caracteres.'],
            'TC-FPAGO-07 Duplicado' => ['FPAGO-07', 'Crédito', 'El nombre ya se encuentra registrado.'],
            'TC-FPAGO-08 SQLi' => ['FPAGO-08', 'Credito OR 1=1', 'El nombre solo debe contener letras y espacios.'],
            'TC-FPAGO-09 XSS' => ['FPAGO-09', '<script>alert(1)</script>', 'El nombre solo debe contener letras y espacios.'],
        ];
    }

    public static function validationEditProvider()
    {
        return [
            'TC-FPAGO-01 Vacio' => ['FPAGO-01_E', '', 'El nombre es obligatorio.'],
            'TC-FPAGO-02 Num' => ['FPAGO-02_E', '123', 'El nombre solo debe contener letras y espacios.'],
            'TC-FPAGO-03 Especiales' => ['FPAGO-03_E', '$$$', 'El nombre solo debe contener letras y espacios.'],
            'TC-FPAGO-04 Min' => ['FPAGO-04_E', 'A', 'El nombre debe tener al menos 2 caracteres.'],
            'TC-FPAGO-05 Max' => ['FPAGO-05_E', str_repeat('A', 51), 'El nombre no puede tener más de 50 caracteres.'],
            'TC-FPAGO-07 Duplicado' => ['FPAGO-07_E', 'Crédito', 'El nombre ya se encuentra registrado.'],
            'TC-FPAGO-08 SQLi' => ['FPAGO-08_E', 'Credito OR 1=1', 'El nombre solo debe contener letras y espacios.'],
            'TC-FPAGO-09 XSS' => ['FPAGO-09_E', '<script>alert(1)</script>', 'El nombre solo debe contener letras y espacios.'],
        ];
    }
}
