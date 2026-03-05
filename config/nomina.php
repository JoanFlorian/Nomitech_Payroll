<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Salario mínimo y auxilio
    |--------------------------------------------------------------------------
    */

    'smmlv' => 1750905,

    'auxilio_transporte' => 249095,

    'auxilio_transporte_tope' => 2, // aplica hasta 2 SMMLV


    /*
    |--------------------------------------------------------------------------
    | Seguridad social - aportes del empleado
    |--------------------------------------------------------------------------
    */

    'employee_rates' => [

        'eps' => 0.04,

        'pension' => 0.04,

        'fondo_solidaridad' => 0.01,

    ],


    

    'employer_rates' => [

        'eps' => 0.085,

        'pension' => 0.12,

        'arl_riesgo_1' => 0.00522,

        'caja_compensacion' => 0.04,

        'icbf' => 0.03,

        'sena' => 0.02,

    ],


    

    'fondo_solidaridad_threshold' => 4, // aplica desde 4 SMMLV


    

    'recargos' => [

        'extra_diurna' => 1.25,

        'extra_nocturna' => 1.75,

        'recargo_nocturno' => 1.35,

        'dominical_festivo' => 1.75,

        'extra_dominical_diurna' => 2.0,

        'extra_dominical_nocturna' => 2.5,

    ],


    

    'horas_mes' => 240,



   

    'contract_types' => [

        'termino_indefinido' => [
            'eps' => true,
            'pension' => true,
            'arl' => true,
        ],

        'termino_fijo' => [
            'eps' => true,
            'pension' => true,
            'arl' => true,
        ],

        'obra_o_labor' => [
            'eps' => true,
            'pension' => true,
            'arl' => true,
        ],

        'aprendizaje' => [
            'eps' => true,
            'pension' => false,
            'arl' => true,
        ],

        'practicas' => [
            'eps' => false,
            'pension' => false,
            'arl' => false,
        ],

        'prestacion_de_servicios' => [
            'eps' => false,
            'pension' => false,
            'arl' => false,
        ],

    ],

];
