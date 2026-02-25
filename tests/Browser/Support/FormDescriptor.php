<?php

namespace Tests\Browser\Support;

/**
 * Define el esquema de formularios y escenarios de prueba 
 * para construir los tests de manera dinámica y parametrizada.
 */
class FormDescriptor
{
    /**
     * Retorna la configuración de campos y selectores Dusk para un módulo dado.
     * @param string $module
     * @return array
     */
    public static function getFields(string $module): array
    {
        $descriptors = [
            'ciudades' => [
                'codigo' => ['type' => 'text', 'required' => true],
                'nombre' => ['type' => 'text', 'required' => true],
                'id_departamento' => ['type' => 'select', 'required' => true],
            ],
            'tipos_de_documento' => [
                'nombre' => ['type' => 'text', 'required' => true],
            ],
            'cargos' => [
                'nombre' => ['type' => 'text', 'required' => true],
                'descripcion' => ['type' => 'textarea', 'required' => false],
            ],
            'metodos_de_pago' => [
                'nombre' => ['type' => 'text', 'required' => true],
            ],
            'formas_de_pago' => [
                'nombre' => ['type' => 'text', 'required' => true],
            ],
            // Agrega más según requieras
        ];

        return $descriptors[$module] ?? [];
    }

    /**
     * Retorna los payloads de error esperados (Ej. XSS, SQLi, Formato inválido)
     */
    public static function getSecurityPayloads(): array
    {
        return [
            'sql_injection' => "'; DROP TABLE users; --",
            'xss_payload' => "<script>alert('xss')</script>",
            'html_injection' => "<b>Bold Text</b>",
        ];
    }
}
