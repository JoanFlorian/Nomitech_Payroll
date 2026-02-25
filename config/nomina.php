<?php

return [
    'smmlv' => 1423500,
    'aporte_fp_smmlv_threshold' => 4,
    'rates' => [
        'eps' => 0.04,
        'afp' => 0.04,
        'arl' => 0.00522,
        'aporte_fp' => 0.01,
    ],
    'contract_types' => [
        'termino_indefinido' => [
            'aplica_seguridad_social' => true,
        ],
        'termino_fijo' => [
            'aplica_seguridad_social' => true,
        ],
        'obra_o_labor' => [
            'aplica_seguridad_social' => true,
        ],
        'aprendizaje' => [
            'aplica_seguridad_social' => false,
        ],
        'practicas' => [
            'aplica_seguridad_social' => false,
        ],
        'prestacion_de_servicios' => [
            'aplica_seguridad_social' => false,
        ],
    ],
];
