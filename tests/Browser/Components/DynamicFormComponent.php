<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class DynamicFormComponent extends BaseComponent
{
    protected $isEditMode;

    public function __construct($isEditMode = false)
    {
        $this->isEditMode = $isEditMode;
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return $this->isEditMode ? '@modal-edicion' : '@modal-agregar';
    }

    /**
     * Assert that the browser page contains the component.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertVisible($this->selector());
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@btn-submit' => $this->isEditMode ? '@btn-guardar-edicion' : '@btn-guardar-agregar',
        ];
    }

    /**
     * Espera asíncrona dedicada a que el modal termine su transición
     */
    public function waitForModal(Browser $browser)
    {
        $browser->waitFor($this->selector());
        $browser->pause(500); // Pequeña pausa para animaciones CSS opacity
    }

    /**
     * Llena de forma dinámica los campos del modal en función al array de data.
     * El formato de datos debe ser key -> valor, donde la key mapea el parámetro "name" del input, select o textarea.
     *
     * @param Browser $browser
     * @param array $data
     */
    public function fillFields(Browser $browser, array $data)
    {
        foreach ($data as $name => $value) {
            $selector = "[name=\"$name\"]";
            $browser->waitFor($selector);

            // Verificamos si es select usando JS de forma simple
            $isSelect = $browser->script("return document.querySelector('[name=\"$name\"]').tagName === 'SELECT'");

            if ($isSelect) {
                $browser->select($name, (string) $value);
            } else {
                $browser->type($name, (string) $value);
            }
        }
    }

    /**
     * Limpia dinámicamente los campos especificados en el array 
     * (Útil para tests que requieran borrar un valor existente para forzar validación 'required')
     * 
     * @param Browser $browser
     * @param array $fields
     */
    public function clearFields(Browser $browser, array $fields)
    {
        foreach ($fields as $name) {
            $selector = "input[name=\"{$name}\"], textarea[name=\"{$name}\"]";
            $browser->waitFor($selector)
                ->clear($selector);
        }
    }

    /**
     * Hace click en el botón de Guardar/Submit del modal específico.
     */
    public function submitForm(Browser $browser)
    {
        $browser->click('@btn-submit');
    }
}
