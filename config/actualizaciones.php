<?php

use App\Models\{Ciudad, TipoDoc, Banco, TipoContrato, Eps, Arl, Empresa, Departamento, Estado, FormaPago, MetodoPago, Pais, Rol, TipoHoraRecargo, PayrollParameter};

return [

    // Reglas de validación única: campo => 'tabla,columna,primaryKey'
    'reglas_unicas' => [
        'ciudades'      => ['codigo' => 'ciudad,codigo,id_ciudad'],
        'empresas'      => ['nit' => 'empresa,nit,id_empresa'],
        'departamentos' => ['codigo' => 'departamento,codigo,id_departamento'],
        'paises'        => ['codigo_alfa2' => 'pais,codigo_alfa2,id_pais'],
    ],

    // Configuración de cada módulo
    'modulos' => [
        'ciudades' => [
            'modelo' => Ciudad::class, 'campoId' => 'id_ciudad', 'tabla' => 'ciudad',
            'titulo' => 'Ciudades', 'desc' => 'Gestiona el listado de ciudades y regiones del sistema.', 'icono' => 'bi-geo-alt',
            'campos' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-pin-map', 'requerido' => true],
                ['clave' => 'id_departamento', 'label' => 'Departamento', 'tipo' => 'select', 'icono' => 'bi-map', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'simple'],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
                ['clave' => 'id_departamento', 'label' => 'Departamento', 'tipo' => 'relacion', 'relacion' => 'departamento', 'mostrar' => 'nombre'],
            ],
            'colores' => ['icono' => 'from-orange-400 to-orange-600', 'boton' => 'from-orange-500 to-orange-600'],
        ],
        'tipos_de_documento' => [
            'modelo' => TipoDoc::class, 'campoId' => 'id_tipo_doc', 'tabla' => 'tipo_doc',
            'titulo' => 'Tipos de Documento', 'desc' => 'Configura tipos de identificación como CC, NIT, CE.', 'icono' => 'bi-person-badge',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-person-badge', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-green-400 to-green-600', 'boton' => 'from-green-500 to-green-600'],
        ],
        'bancos' => [
            'modelo' => Banco::class, 'campoId' => 'id_banco', 'tabla' => 'banco',
            'titulo' => 'Bancos', 'desc' => 'Gestiona entidades bancarias para nómina.', 'icono' => 'bi-bank',
            'campos' => [
                ['clave' => 'id_banco', 'label' => 'Código', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-bank', 'requerido' => true],
                ['clave' => 'codigo_ach', 'label' => 'Código ACH', 'tipo' => 'text', 'icono' => 'bi-qr-code-scan'],
                ['clave' => 'telefono', 'label' => 'Teléfono', 'tipo' => 'text', 'icono' => 'bi-telephone'],
                ['clave' => 'direccion', 'label' => 'Dirección', 'tipo' => 'text', 'icono' => 'bi-geo-alt'],
            ],
            'columnas' => [
                ['clave' => 'id_banco', 'label' => 'ID', 'tipo' => 'simple'],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
                ['clave' => 'codigo_ach', 'label' => 'ACH', 'tipo' => 'simple'],
                ['clave' => 'telefono', 'label' => 'Teléfono', 'tipo' => 'simple'],
                ['clave' => 'direccion', 'label' => 'Dirección', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-purple-400 to-purple-600', 'boton' => 'from-purple-500 to-purple-600'],
        ],
        'tipos_de_contrato' => [
            'modelo' => TipoContrato::class, 'campoId' => 'id_tipo_contrato', 'tabla' => 'tipo_contrato',
            'titulo' => 'Tipos de Contrato', 'desc' => 'Configura tipos de contrato laboral.', 'icono' => 'bi-file-earmark-text',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-file-earmark', 'requerido' => true],
                ['clave' => 'seguridad_social', 'label' => '¿Incluye Seguridad Social?', 'tipo' => 'select', 'icono' => 'bi-check-circle', 'opciones' => [
                    ['id' => 0, 'nombre' => 'No'],
                    ['id' => 1, 'nombre' => 'Sí'],
                ]],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-cyan-400 to-cyan-600', 'boton' => 'from-cyan-500 to-cyan-600'],
        ],
        'eps' => [
            'modelo' => Eps::class, 'campoId' => 'id_eps', 'tabla' => 'eps',
            'titulo' => 'EPS', 'desc' => 'Gestión de entidades de salud.', 'icono' => 'bi-heart-pulse',
            'campos' => [
                ['clave' => 'id_eps', 'label' => 'Código', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-heart-pulse', 'requerido' => true],
                ['clave' => 'telefono', 'label' => 'Teléfono', 'tipo' => 'text', 'icono' => 'bi-telephone'],
                ['clave' => 'direccion', 'label' => 'Dirección', 'tipo' => 'text', 'icono' => 'bi-geo-alt'],
            ],
            'columnas' => [
                ['clave' => 'id_eps', 'label' => 'ID', 'tipo' => 'simple'],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
                ['clave' => 'telefono', 'label' => 'Teléfono', 'tipo' => 'simple'],
                ['clave' => 'direccion', 'label' => 'Dirección', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-red-400 to-red-600', 'boton' => 'from-red-500 to-red-600'],
        ],
        'arl' => [
            'modelo' => Arl::class, 'campoId' => 'id_arl', 'tabla' => 'arl',
            'titulo' => 'ARL', 'desc' => 'Gestión de riesgos laborales.', 'icono' => 'bi-shield-check',
            'campos' => [
                ['clave' => 'id_arl', 'label' => 'Código', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-shield-check', 'requerido' => true],
                ['clave' => 'telefono', 'label' => 'Teléfono', 'tipo' => 'text', 'icono' => 'bi-telephone'],
                ['clave' => 'direccion', 'label' => 'Dirección', 'tipo' => 'text', 'icono' => 'bi-geo-alt'],
            ],
            'columnas' => [
                ['clave' => 'id_arl', 'label' => 'ID', 'tipo' => 'simple'],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
                ['clave' => 'telefono', 'label' => 'Teléfono', 'tipo' => 'simple'],
                ['clave' => 'direccion', 'label' => 'Dirección', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-yellow-400 to-yellow-600', 'boton' => 'from-yellow-500 to-yellow-600'],
        ],
        'empresas' => [
            'modelo' => Empresa::class, 'campoId' => 'id_empresa', 'tabla' => 'empresa',
            'titulo' => 'Empresas', 'desc' => 'Gestiona entidades empresariales del sistema.', 'icono' => 'bi-building',
            'campos' => [
                ['clave' => 'nit', 'label' => 'NIT', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'razon_social', 'label' => 'Razón Social', 'tipo' => 'text', 'icono' => 'bi-card-text', 'requerido' => true],
                ['clave' => 'id_ciudad', 'label' => 'Ciudad', 'tipo' => 'select', 'icono' => 'bi-geo-alt', 'requerido' => true],
                ['clave' => 'doc_representante', 'label' => 'Doc. Representante', 'tipo' => 'text', 'icono' => 'bi-person-badge'],
                ['clave' => 'direccion', 'label' => 'Dirección', 'tipo' => 'text', 'icono' => 'bi-map-pin'],
                ['clave' => 'correo', 'label' => 'Correo', 'tipo' => 'email', 'icono' => 'bi-envelope'],
                ['clave' => 'telefono', 'label' => 'Teléfono', 'tipo' => 'text', 'icono' => 'bi-telephone'],
            ],
            'columnas' => [
                ['clave' => 'nit', 'label' => 'NIT', 'tipo' => 'simple'],
                ['clave' => 'razon_social', 'label' => 'Razón Social', 'tipo' => 'simple'],
                ['clave' => 'id_ciudad', 'label' => 'Ciudad', 'tipo' => 'relacion', 'relacion' => 'ciudad', 'mostrar' => 'nombre'],
            ],
            'colores' => ['icono' => 'from-blue-400 to-blue-600', 'boton' => 'from-blue-500 to-blue-600'],
        ],
        'departamentos' => [
            'modelo' => Departamento::class, 'campoId' => 'id_departamento', 'tabla' => 'departamento',
            'titulo' => 'Departamentos', 'desc' => 'Gestiona los departamentos y regiones administrativas.', 'icono' => 'bi-map',
            'campos' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-map', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'simple'],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-teal-400 to-teal-600', 'boton' => 'from-teal-500 to-teal-600'],
        ],
        'estados' => [
            'modelo' => Estado::class, 'campoId' => 'id_estado', 'tabla' => 'estado',
            'titulo' => 'Estados', 'desc' => 'Define estados o estatus para procesos.', 'icono' => 'bi-info-circle',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-info-circle', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-lime-400 to-lime-600', 'boton' => 'from-lime-500 to-lime-600'],
        ],
        'formas_de_pago' => [
            'modelo' => FormaPago::class, 'campoId' => 'id_forma_pago', 'tabla' => 'forma_pago',
            'titulo' => 'Formas de Pago', 'desc' => 'Configura formas de pago disponibles.', 'icono' => 'bi-credit-card',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-credit-card', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-fuchsia-400 to-fuchsia-600', 'boton' => 'from-fuchsia-500 to-fuchsia-600'],
        ],
        'metodos_de_pago' => [
            'modelo' => MetodoPago::class, 'campoId' => 'id_metodo_pago', 'tabla' => 'metodo_pago',
            'titulo' => 'Métodos de Pago', 'desc' => 'Gestiona métodos de pago para nómina.', 'icono' => 'bi-wallet2',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-wallet2', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-rose-400 to-rose-600', 'boton' => 'from-rose-500 to-rose-600'],
        ],
        'paises' => [
            'modelo' => Pais::class, 'campoId' => 'id_pais', 'tabla' => 'pais',
            'titulo' => 'Países', 'desc' => 'Gestiona el listado de países.', 'icono' => 'bi-globe',
            'campos' => [
                ['clave' => 'codigo_alfa2', 'label' => 'Código', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-globe', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'codigo_alfa2', 'label' => 'Código', 'tipo' => 'simple'],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-sky-400 to-sky-600', 'boton' => 'from-sky-500 to-sky-600'],
        ],
        'roles' => [
            'modelo' => Rol::class, 'campoId' => 'id_rol', 'tabla' => 'rol',
            'titulo' => 'Roles', 'desc' => 'Define roles y permisos de usuario.', 'icono' => 'bi-shield-lock',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-shield-lock', 'requerido' => true],
                ['clave' => 'descripcion', 'label' => 'Descripción', 'tipo' => 'textarea', 'icono' => 'bi-file-text'],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-violet-400 to-violet-600', 'boton' => 'from-violet-500 to-violet-600'],
        ],
        'tipos_hora_recargo' => [
            'modelo' => TipoHoraRecargo::class, 'campoId' => 'id_tipo_hora_recargo', 'tabla' => 'tipo_hora_recargo',
            'titulo' => 'Tipos Hora Recargo', 'desc' => 'Configura tipos de horas con recargo.', 'icono' => 'bi-clock',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-clock', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-amber-400 to-amber-600', 'boton' => 'from-amber-500 to-amber-600'],
        ],
        'payroll_parameters' => [
            'modelo' => PayrollParameter::class, 'campoId' => 'id', 'tabla' => 'payroll_parameters',
            'titulo' => 'Parámetros de Nómina', 'desc' => 'Gestiona parámetros dinámicos de nómina.', 'icono' => 'bi-sliders',
            'campos' => [
                ['clave' => 'smmlv', 'label' => 'SMMLV', 'tipo' => 'number', 'icono' => 'bi-cash-coin', 'requerido' => true],
                ['clave' => 'auxilio_transporte', 'label' => 'Auxilio Transporte', 'tipo' => 'number', 'icono' => 'bi-truck', 'requerido' => true],
                ['clave' => 'auxilio_transporte_tope', 'label' => 'Auxilio Transporte Tope', 'tipo' => 'number', 'icono' => 'bi-arrow-up', 'requerido' => true],
                ['clave' => 'eps_employee', 'label' => 'EPS Empleado (%)', 'tipo' => 'number', 'icono' => 'bi-percent', 'requerido' => true],
                ['clave' => 'pension_employee', 'label' => 'Pensión Empleado (%)', 'tipo' => 'number', 'icono' => 'bi-percent', 'requerido' => true],
                ['clave' => 'fondo_solidaridad', 'label' => 'Fondo Solidaridad (%)', 'tipo' => 'number', 'icono' => 'bi-percent', 'requerido' => true],
                ['clave' => 'eps_employer', 'label' => 'EPS Empleador (%)', 'tipo' => 'number', 'icono' => 'bi-percent', 'requerido' => true],
                ['clave' => 'pension_employer', 'label' => 'Pensión Empleador (%)', 'tipo' => 'number', 'icono' => 'bi-percent', 'requerido' => true],
                ['clave' => 'arl_riesgo_1', 'label' => 'ARL Riesgo 1 (%)', 'tipo' => 'number', 'icono' => 'bi-percent', 'requerido' => true],
                ['clave' => 'caja_compensacion', 'label' => 'Caja Compensación (%)', 'tipo' => 'number', 'icono' => 'bi-percent', 'requerido' => true],
                ['clave' => 'fondo_solidaridad_threshold', 'label' => 'Umbral Fondo Solidaridad', 'tipo' => 'number', 'icono' => 'bi-door-closed', 'requerido' => true],
                ['clave' => 'horas_mes', 'label' => 'Horas por Mes', 'tipo' => 'number', 'icono' => 'bi-clock', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'smmlv', 'label' => 'SMMLV', 'tipo' => 'simple'],
                ['clave' => 'auxilio_transporte', 'label' => 'Auxilio Transporte', 'tipo' => 'simple'],
                ['clave' => 'eps_employee', 'label' => 'EPS Empleado', 'tipo' => 'simple'],
                ['clave' => 'pension_employee', 'label' => 'Pensión Empleado', 'tipo' => 'simple'],
            ],
            'colores' => ['icono' => 'from-indigo-400 to-indigo-600', 'boton' => 'from-indigo-500 to-indigo-600'],
        ],
    ],
];
