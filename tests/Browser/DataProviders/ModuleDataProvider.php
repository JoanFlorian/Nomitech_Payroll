<?php

namespace Tests\Browser\DataProviders;

use Tests\Browser\Support\FormDescriptor;

class ModuleDataProvider
{
    /**
     * Data provider genérico para escenarios de inserción EXITOSOS.
     * Retorna un arreglo iterativo con el Payload esperado.
     */
    public static function successfulCreationData()
    {
        return [
            'Ciudades' => [
                'ciudades',
                ['codigo' => 'BOG-001', 'nombre' => 'Bogotá D.C', 'id_departamento' => '1'] // Asegurate que el Depto ID 1 exista
            ],
            'Tipos de Documento' => [
                'tipos_de_documento',
                ['nombre' => 'Cédula de Extranjería (Dusk)']
            ],
            'Cargos' => [
                'cargos',
                ['nombre' => 'QA Automation Lead', 'descripcion' => 'Responsable de E2E tests']
            ],
            'Métodos de Pago' => [
                'metodos_de_pago',
                ['nombre' => 'Transferencia Bancolombia']
            ],
            'Formas de Pago' => [
                'formas_de_pago',
                ['nombre' => 'Quincenal']
            ],
        ];
    }

    /**
     * Data provider para flujos de ERROR DE VALIDACIÓN.
     * Retorna: Módulo, Payload (datos), Falla esperada por la aserción (Ej. texto del toast o input class)
     */
    public static function validationErrorData()
    {
        $payloads = FormDescriptor::getSecurityPayloads();

        return [
            // Required validations
            'Ciudades - Required Missing' => [
                'ciudades',
                ['codigo' => '', 'nombre' => '', 'id_departamento' => ''],
                'obligatorio' // Parte del mensaje de error esperado a visualizar en la pantalla
            ],
            'Cargos - Required Missing' => [
                'cargos',
                ['nombre' => '', 'descripcion' => 'Solo descripción sin nombre'],
                'obligatorio'
            ],
            // XSS / SQL Injections / Long Strings should be handled gracefully by Laravel 
            // but we ensure saving is either safe or fails correctly.
            'Tipos Doc - XSS Attack' => [
                'tipos_de_documento',
                ['nombre' => $payloads['xss_payload']],
                null // Al testear un "success" de Laravel con XSS, Laravel sanitiza o escapa la vista (Blade hace {{}}), validamos que no se rompe la web.
            ]
        ];
    }
}
