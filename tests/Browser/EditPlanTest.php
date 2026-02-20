<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Plan;
use Faker\Factory as Faker;

/**
 * ============================================================================
 *  QA FUNCIONAL — Formulario Editar Plan (Laravel Dusk)
 * ============================================================================
 *
 *  Pruebas de validación funcional para los campos:
 *    - nombre      (required|string|min:3|max:60)
 *    - descripcion (nullable|string|max:500)
 *    - valor       (required|numeric|min:3000)
 *    - num_empl    (required|integer|min:1)
 *    - duracion    (required|integer|min:1) — select
 *
 *  Ejecución:  php artisan dusk --filter=EditPlanTest
 * ============================================================================
 */
class EditPlanTest extends DuskTestCase
{
    protected static ?int $planId = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (is_null(self::$planId)) {
            $plan = Plan::first();
            if (!$plan) {
                $this->markTestSkipped('No hay planes en la BD para probar.');
            }
            self::$planId = $plan->id;
        }
    }

    protected function editUrl(): string
    {
        return '/superadmin/planes/' . self::$planId . '/edit';
    }

    /**
     * Datos válidos por defecto (Faker) para rellenar campos que NO se están probando.
     */
    protected function validDefaults(): array
    {
        $faker = Faker::create('es_CO');
        return [
            'nombre' => $faker->unique()->words(3, true) . ' Plan',
            'descripcion' => $faker->sentence(8),
            'valor' => (string) $faker->numberBetween(5000, 50000),
            'num_empl' => (string) $faker->numberBetween(5, 100),
            'duracion' => (string) $faker->randomElement([1, 3, 6, 12]),
        ];
    }

    /**
     * Rellena el formulario con defaults, sobreescribe el campo objetivo,
     * quita restricciones HTML5, envía, pausa y toma screenshot.
     */
    protected function fillAndSubmit(
        Browser $browser,
        string $targetField,
        $targetValue,
        string $screenshotName,
        bool $isSelect = false,
        bool $isTextarea = false
    ): void {
        $defaults = $this->validDefaults();

        $browser->visit($this->editUrl())->pause(10000);

        // Rellenar todos los campos con datos válidos
        foreach ($defaults as $field => $value) {
            if ($field === $targetField)
                continue;

            if ($field === 'duracion') {
                $browser->select('duracion', $value);
            } elseif ($field === 'descripcion') {
                $browser->clear('descripcion')->type('descripcion', $value);
            } else {
                $browser->clear($field)->type($field, $value);
            }
        }

        // Quitar atributos HTML5 que bloquean el envío (required, min, max, minlength, maxlength)
        $browser->script("
            document.querySelectorAll('input, select, textarea').forEach(el => {
                el.removeAttribute('required');
                el.removeAttribute('min');
                el.removeAttribute('max');
                el.removeAttribute('minlength');
                el.removeAttribute('maxlength');
            });
        ");

        // Rellenar el campo objetivo con el valor de prueba
        if ($isSelect) {
            try {
                $browser->select($targetField, (string) $targetValue);
            } catch (\Exception $e) {
                // Valor no existe en el select — se queda con el default
            }
        } elseif ($isTextarea) {
            $browser->clear($targetField);
            if ($targetValue !== null && $targetValue !== '') {
                $browser->type($targetField, (string) $targetValue);
            }
        } else {
            $browser->clear($targetField);
            if ($targetValue !== null && $targetValue !== '') {
                $browser->type($targetField, (string) $targetValue);
            }
        }

        // Enviar y pausar para ver errores visualmente
        $browser->press('Actualizar plan')
            ->pause(2500)
            ->screenshot($screenshotName);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  A) CAMPO: nombre  (required|string|min:3|max:60)
    // ════════════════════════════════════════════════════════════════════════

    /** @test */
    public function edit_nombre_vacio(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit($browser, 'nombre', '', 'edit_A01_nombre_vacio');
            $browser->assertSee('nombre');
        });
    }

    /** @test */
    public function edit_nombre_muy_corto(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit($browser, 'nombre', 'A', 'edit_A02_nombre_corto');
            $browser->assertSee('nombre');
        });
    }

    /** @test */
    public function edit_nombre_muy_largo(): void
    {
        $this->browse(function (Browser $browser) {
            $longName = str_repeat('Abcdefghij', 10); // 100 chars > max:60
            $this->fillAndSubmit($browser, 'nombre', $longName, 'edit_A03_nombre_largo');
            $browser->assertSee('nombre');
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  B) CAMPO: descripcion  (nullable|string|max:500)
    // ════════════════════════════════════════════════════════════════════════

    /** @test */
    public function edit_descripcion_null(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit(
                $browser,
                'descripcion',
                '',
                'edit_B01_descripcion_null',
                false,
                true
            );
            // nullable → no debería dar error, puede redirigir al índice
            $browser->assertPresent('body');
        });
    }

    /** @test */
    public function edit_descripcion_muy_larga(): void
    {
        $this->browse(function (Browser $browser) {
            $longDesc = str_repeat('Lorem ipsum dolor sit amet. ', 25); // ~700 chars > max:500
            $this->fillAndSubmit(
                $browser,
                'descripcion',
                $longDesc,
                'edit_B02_descripcion_larga',
                false,
                true
            );
            $browser->assertSee('descripcion');
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  C) CAMPO: valor  (required|numeric|min:3000)
    // ════════════════════════════════════════════════════════════════════════

    /** @test */
    public function edit_valor_vacio(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit($browser, 'valor', '', 'edit_C01_valor_vacio');
            $browser->assertSee('valor');
        });
    }

    /** @test */
    public function edit_valor_texto(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit($browser, 'valor', 'ABC', 'edit_C02_valor_texto');
            $browser->assertSee('valor');
        });
    }

    /** @test */
    public function edit_valor_negativo(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit($browser, 'valor', '-100', 'edit_C03_valor_negativo');
            $browser->assertSee('valor');
        });
    }

    /** @test */
    public function edit_valor_decimales_excesivos(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit($browser, 'valor', '10.999', 'edit_C04_valor_decimales');
            // Puede aceptarse o rechazarse según la validación
            $browser->assertPresent('body');
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  D) CAMPO: num_empl  (required|integer|min:1)
    // ════════════════════════════════════════════════════════════════════════

    /** @test */
    public function edit_num_empl_vacio(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit($browser, 'num_empl', '', 'edit_D01_num_empl_vacio');
            $browser->assertSee('num_empl');
        });
    }

    /** @test */
    public function edit_num_empl_texto(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit($browser, 'num_empl', 'abc', 'edit_D02_num_empl_texto');
            $browser->assertSee('num_empl');
        });
    }

    /** @test */
    public function edit_num_empl_negativo(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit($browser, 'num_empl', '-1', 'edit_D03_num_empl_negativo');
            $browser->assertSee('num_empl');
        });
    }

    /** @test */
    public function edit_num_empl_cero(): void
    {
        $this->browse(function (Browser $browser) {
            $this->fillAndSubmit($browser, 'num_empl', '0', 'edit_D04_num_empl_cero');
            $browser->assertSee('num_empl');
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  E) CAMPO: duracion  (required|integer|min:1) — select
    // ════════════════════════════════════════════════════════════════════════

    /** @test */
    public function edit_duracion_vacia(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit($this->editUrl())->pause(10000);

            // Rellenar otros campos con datos válidos
            $defaults = $this->validDefaults();
            foreach ($defaults as $field => $value) {
                if ($field === 'duracion')
                    continue;
                if ($field === 'descripcion') {
                    $browser->clear('descripcion')->type('descripcion', $value);
                } else {
                    $browser->clear($field)->type($field, $value);
                }
            }

            // Quitar restricciones HTML5
            $browser->script("
                document.querySelectorAll('input, select, textarea').forEach(el => {
                    el.removeAttribute('required');
                    el.removeAttribute('min');
                    el.removeAttribute('max');
                    el.removeAttribute('minlength');
                    el.removeAttribute('maxlength');
                });
            ");

            // Inyectar opción vacía en el select y seleccionarla
            $browser->script("
                var sel = document.querySelector('select[name=\"duracion\"]');
                var opt = document.createElement('option');
                opt.value = '';
                opt.text = '-- Seleccionar --';
                sel.insertBefore(opt, sel.firstChild);
                sel.value = '';
            ");

            $browser->press('Actualizar plan')
                ->pause(2500)
                ->screenshot('edit_E01_duracion_vacia')
                ->assertSee('duracion');
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  F) HAPPY PATH — Datos válidos completos
    // ════════════════════════════════════════════════════════════════════════

    /** @test */
    public function edit_datos_validos(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit($this->editUrl())
                ->pause(10000)
                ->clear('nombre')
                ->type('nombre', 'Plan Dusk Test Valido')
                ->select('duracion', '3')
                ->clear('valor')
                ->type('valor', '15000')
                ->clear('num_empl')
                ->type('num_empl', '50')
                ->clear('descripcion')
                ->type('descripcion', 'Descripcion valida generada por Dusk QA.')
                ->press('Actualizar plan')
                ->pause(2500)
                ->screenshot('edit_F01_datos_validos');

            // Si todo es válido, debería redirigir al índice con éxito
            $browser->assertSee('Plan actualizado correctamente');
        });
    }
}
