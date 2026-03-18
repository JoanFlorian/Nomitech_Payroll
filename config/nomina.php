<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Salario Mínimo Mensual Legal Vigente (SMMLV)
    |--------------------------------------------------------------------------
    |
    | Vigencia: año 2026 - Decreto 2020 de 2025
    | Valor: $1.749.205 COP
    |
    | Este valor se usa en:
    |  - App\Http\Requests\Step2Request (validación al registrar empleado)
    |  - App\Http\Requests\UpdateEmployeePartialRequest (validación al editar)
    |  - resources/views/empleados/index.blade.php (validación JS al registrar)
    |  - resources/views/empleados/partials/modal_edit.blade.php (validación JS al editar)
    |
    | Para actualizar al iniciar un nuevo año fiscal, cambie únicamente
    | el valor de 'salario_minimo'.
    |
    */
    'salario_minimo' => 1_749_205,

    /*
    | Alias de compatibilidad — algunos helpers leen 'nomina.smmlv'.
    | Mantenga este valor igual a 'salario_minimo'.
    */
    'smmlv' => 1_749_205,

    /*
    |--------------------------------------------------------------------------
    | Auxilio de Transporte
    |--------------------------------------------------------------------------
    |
    | Vigencia: año 2026
    | Valor: $247.090 COP
    |
    | Aplica para empleados con salario <= 2 SMMLV.
    |
    */
    'auxilio_transporte' => 247_090,

];
