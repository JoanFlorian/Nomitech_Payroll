<?php

namespace Tests\Browser;

use Tests\Browser\BaseDuskTestCase;
use Tests\Browser\Traits\AuthenticatesSuperAdmin;
use Laravel\Dusk\Browser;
use App\Models\MetodoPago;

class MetodosDePagoTest extends BaseDuskTestCase
{
    use AuthenticatesSuperAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // TC-MPAGO-07 Duplicado
        MetodoPago::updateOrCreate(
            ['nombre' => 'Efectivo'],
            ['nombre' => 'Efectivo']
        );

        MetodoPago::updateOrCreate(
            ['nombre' => 'Metodo Para Editar'],
            ['nombre' => 'Metodo Para Editar']
        );
    }

    /**
     * @dataProvider validationCreateProvider
     * @group validation
     */
    public function test_validaciones_al_crear_metodo_pago($dataName, $nombre, $expectedError)
    {
        $this->browse(function (Browser $browser) use ($dataName, $nombre, $expectedError) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones?page=2')
                ->waitForText('Módulo de Actualizaciones')
                ->waitFor('button[dusk="button-add-métodos de pago"]', 10)
                ->click('button[dusk="button-add-métodos de pago"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('nombre', $nombre)
                ->click('#btnGuardarAgregar');

            // Capturar screenshot
            $this->takeValidationScreenshot($browser, static::class, "Crear_MetodoPago_" . $dataName);

            $browser->waitForText('Error de Validación', 30)
                ->assertSee($expectedError)
                ->pause(1000);
        });
    }

    /**
     * @group smoke
     */
    public function test_creacion_exitosa_metodo_pago()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones?page=2')
                ->waitForText('Módulo de Actualizaciones')
                ->waitFor('button[dusk="button-add-métodos de pago"]', 10)
                ->click('button[dusk="button-add-métodos de pago"]')
                ->waitFor('#modalAgregar.flex', 10)
                ->type('nombre', 'Tarjeta de credito')
                ->click('#btnGuardarAgregar')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('metodo_pago', [
            'nombre' => 'Tarjeta de credito'
        ]);
    }

    /**
     * @dataProvider validationEditProvider
     * @group validation
     */
    public function test_validaciones_al_editar_metodo_pago($dataName, $nombre, $expectedError)
    {
        $this->browse(function (Browser $browser) use ($dataName, $nombre, $expectedError) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones?page=2')
                ->waitForText('Módulo de Actualizaciones')
                ->waitFor('button[dusk="button-edit-métodos de pago"]', 10)
                ->click('button[dusk="button-edit-métodos de pago"]')
                ->waitFor('#modalContenido table', 15)
                ->type('#buscar-items', 'Metodo Para Editar')
                ->pause(1000)
                ->click('#modalVer tbody tr:first-child button')
                ->waitFor('#modalEdicion.flex', 10)
                ->pause(500)
                ->clear('#modalEdicion input[name="nombre"]')
                ->type('#modalEdicion input[name="nombre"]', $nombre)
                ->click('button[dusk="btn-guardar-edicion"]');

            // Capturar screenshot
            $this->takeValidationScreenshot($browser, static::class, "Editar_MetodoPago_" . $dataName);

            $browser->waitForText('Error de Validación', 30)
                ->assertSee($expectedError)
                ->pause(1000);
        });
    }

    /**
     * @group smoke
     */
    public function test_edicion_exitosa_metodo_pago()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $browser->visit('/superadmin/actualizaciones?page=2')
                ->waitForText('Módulo de Actualizaciones')
                ->waitFor('button[dusk="button-edit-métodos de pago"]', 10)
                ->click('button[dusk="button-edit-métodos de pago"]')
                ->waitFor('#modalContenido table', 15)
                ->type('#buscar-items', 'Metodo Para Editar')
                ->pause(1000)
                ->click('#modalVer tbody tr:first-child button')
                ->waitFor('#modalEdicion.flex', 10)
                ->pause(500)
                ->clear('#modalEdicion input[name="nombre"]')
                ->type('#modalEdicion input[name="nombre"]', 'Transferencia ACH')
                ->click('button[dusk="btn-guardar-edicion"]')
                ->waitFor('#alertExito', 30)
                ->pause(10000);
        });

        $this->assertDatabaseHas('metodo_pago', [
            'nombre' => 'Transferencia ACH'
        ]);
    }

    public static function validationCreateProvider()
    {
        return [
            'TC-MPAGO-01 Vacio' => ['MPAGO-01', '', 'El nombre es obligatorio.'],
            'TC-MPAGO-02 Num' => ['MPAGO-02', '123', 'El nombre solo debe contener letras y espacios.'],
            'TC-MPAGO-03 Especiales' => ['MPAGO-03', '***', 'El nombre solo debe contener letras y espacios.'],
            'TC-MPAGO-04 Min' => ['MPAGO-04', 'A', 'El nombre debe tener al menos 2 caracteres.'],
            'TC-MPAGO-05 Max' => ['MPAGO-05', str_repeat('A', 51), 'El nombre no puede tener más de 50 caracteres.'],
            'TC-MPAGO-07 Duplicado' => ['MPAGO-07', 'Efectivo', 'El nombre ya se encuentra registrado.'],
            'TC-MPAGO-08 SQLi' => ['MPAGO-08', 'Efectivo OR 1=1', 'El nombre solo debe contener letras y espacios.'],
            'TC-MPAGO-09 XSS' => ['MPAGO-09', '<script>alert(1)</script>', 'El nombre solo debe contener letras y espacios.'],
        ];
    }

    public static function validationEditProvider()
    {
        return [
            'TC-MPAGO-01 Vacio' => ['MPAGO-01_E', '', 'El nombre es obligatorio.'],
            'TC-MPAGO-02 Num' => ['MPAGO-02_E', '123', 'El nombre solo debe contener letras y espacios.'],
            'TC-MPAGO-03 Especiales' => ['MPAGO-03_E', '***', 'El nombre solo debe contener letras y espacios.'],
            'TC-MPAGO-04 Min' => ['MPAGO-04_E', 'A', 'El nombre debe tener al menos 2 caracteres.'],
            'TC-MPAGO-05 Max' => ['MPAGO-05_E', str_repeat('A', 51), 'El nombre no puede tener más de 50 caracteres.'],
            'TC-MPAGO-07 Duplicado' => ['MPAGO-07_E', 'Efectivo', 'El nombre ya se encuentra registrado.'],
            'TC-MPAGO-08 SQLi' => ['MPAGO-08_E', 'Efectivo OR 1=1', 'El nombre solo debe contener letras y espacios.'],
            'TC-MPAGO-09 XSS' => ['MPAGO-09_E', '<script>alert(1)</script>', 'El nombre solo debe contener letras y espacios.'],
        ];
    }
}
