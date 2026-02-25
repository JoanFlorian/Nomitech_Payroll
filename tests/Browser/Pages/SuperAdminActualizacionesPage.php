<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class SuperAdminActualizacionesPage extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/superadmin/actualizaciones';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url())
            ->assertSee('Módulo de Actualizaciones');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@alert-success' => '#alertExito',
            '@alert-error' => '#alertError',
        ];
    }

    /**
     * Espera a que la grilla principal de módulos cargue.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function waitForGridLoaded(Browser $browser)
    {
        // Esperamos que al menos esté visible el botón de algún bloque
        $browser->waitForText('Agregar');
    }

    /**
     * Hace click en el botón Agregar para un módulo específico.
     *
     * @param  Browser  $browser
     * @param  string   $modulo  (Ej. 'ciudades', 'cargos')
     * @return void
     */
    public function clickAgregarModulo(Browser $browser, $modulo)
    {
        $duskSelector = "@button-add-{$modulo}";
        $browser->waitFor($duskSelector)
            ->click($duskSelector);
    }

    /**
     * Hace click en el botón Editar para un módulo específico.
     *
     * @param  Browser  $browser
     * @param  string   $modulo  (Ej. 'ciudades', 'cargos')
     * @return void
     */
    public function clickEditarModulo(Browser $browser, $modulo)
    {
        $duskSelector = "@button-edit-{$modulo}";
        $browser->waitFor($duskSelector)
            ->click($duskSelector);
    }
}
