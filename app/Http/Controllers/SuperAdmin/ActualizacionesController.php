<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Banco;
use App\Models\TipoDoc;
use App\Models\Rol;
use App\Models\TipoContrato;
use App\Models\Eps;
use App\Models\Arl;
use App\Models\Estado;
use App\Models\FormaPago;
use App\Models\MetodoPago;
use App\Models\Pais;
use App\Models\TipoHoraRecargo;
use App\Http\Requests\Actualizaciones\StoreCiudadRequest;
use App\Http\Requests\Actualizaciones\UpdateCiudadRequest;
use App\Http\Requests\Actualizaciones\StoreTipoDocumentoRequest;
use App\Http\Requests\Actualizaciones\UpdateTipoDocumentoRequest;
use App\Http\Requests\Actualizaciones\StoreCargoRequest;
use App\Http\Requests\Actualizaciones\UpdateCargoRequest;
use App\Http\Requests\Actualizaciones\StoreFormaPagoRequest;
use App\Http\Requests\Actualizaciones\UpdateFormaPagoRequest;
use App\Http\Requests\Actualizaciones\StoreMetodoPagoRequest;
use App\Http\Requests\Actualizaciones\UpdateMetodoPagoRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ActualizacionesController extends Controller
{
    // Mapa de configuración para cada tipo de dato
    private $configuraciones = [
        'ciudades' => [
            'modelo' => Ciudad::class,
            'campoId' => 'id_ciudad',
            'tabla' => 'ciudad',
            'campos' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-pin-map', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'simple'],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'orange',
            'colores' => ['icono' => 'from-orange-400 to-orange-600', 'boton' => 'from-orange-500 to-orange-600'],
        ],
        'tipos_de_documento' => [
            'modelo' => TipoDoc::class,
            'campoId' => 'id_tipo_doc',
            'tabla' => 'tipo_doc',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-person-badge', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'green',
            'colores' => ['icono' => 'from-green-400 to-green-600', 'boton' => 'from-green-500 to-green-600'],
        ],
        'bancos' => [
            'modelo' => Banco::class,
            'campoId' => 'id_banco',
            'tabla' => 'banco',
            'campos' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-bank', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'simple'],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'purple',
            'colores' => ['icono' => 'from-purple-400 to-purple-600', 'boton' => 'from-purple-500 to-purple-600'],
        ],
        'roles_nombres' => [
            'modelo' => Rol::class,
            'campoId' => 'id_rol',
            'tabla' => 'rol',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-briefcase', 'requerido' => true],
                ['clave' => 'descripcion', 'label' => 'Descripción', 'tipo' => 'textarea', 'icono' => 'bi-file-text'],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'indigo',
            'colores' => ['icono' => 'from-indigo-400 to-indigo-600', 'boton' => 'from-indigo-500 to-indigo-600'],
        ],
        'tipos_de_contrato' => [
            'modelo' => TipoContrato::class,
            'campoId' => 'id_tipo_contrato',
            'tabla' => 'tipo_contrato',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-file-earmark', 'requerido' => true],
                [
                    'clave' => 'seguridad_social',
                    'label' => '¿Incluye Seguridad Social?',
                    'tipo' => 'select',
                    'icono' => 'bi-check-circle',
                    'opciones' => [
                        ['id' => 0, 'nombre' => 'No'],
                        ['id' => 1, 'nombre' => 'Sí'],
                    ]
                ],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'cyan',
            'colores' => ['icono' => 'from-cyan-400 to-cyan-600', 'boton' => 'from-cyan-500 to-cyan-600'],
        ],
        'eps' => [
            'modelo' => Eps::class,
            'campoId' => 'id_eps',
            'tabla' => 'eps',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-heart-pulse', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'red',
            'colores' => ['icono' => 'from-red-400 to-red-600', 'boton' => 'from-red-500 to-red-600'],
        ],
        'arl' => [
            'modelo' => Arl::class,
            'campoId' => 'id_arl',
            'tabla' => 'arl',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-shield-check', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'yellow',
            'colores' => ['icono' => 'from-yellow-400 to-yellow-600', 'boton' => 'from-yellow-500 to-yellow-600'],
        ],
        'empresas' => [
            'modelo' => Empresa::class,
            'campoId' => 'id_empresa',
            'tabla' => 'empresa',
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
            'colorBoton' => 'blue',
            'colores' => ['icono' => 'from-blue-400 to-blue-600', 'boton' => 'from-blue-500 to-blue-600'],
        ],
        'departamentos' => [
            'modelo' => Departamento::class,
            'campoId' => 'id_departamento',
            'tabla' => 'departamento',
            'campos' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-map', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'simple'],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'teal',
            'colores' => ['icono' => 'from-teal-400 to-teal-600', 'boton' => 'from-teal-500 to-teal-600'],
        ],
        'estados' => [
            'modelo' => Estado::class,
            'campoId' => 'id_estado',
            'tabla' => 'estado',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-info-circle', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'lime',
            'colores' => ['icono' => 'from-lime-400 to-lime-600', 'boton' => 'from-lime-500 to-lime-600'],
        ],
        'formas_de_pago' => [
            'modelo' => FormaPago::class,
            'campoId' => 'id_forma_pago',
            'tabla' => 'forma_pago',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-credit-card', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'fuchsia',
            'colores' => ['icono' => 'from-fuchsia-400 to-fuchsia-600', 'boton' => 'from-fuchsia-500 to-fuchsia-600'],
        ],
        'metodos_de_pago' => [
            'modelo' => MetodoPago::class,
            'campoId' => 'id_metodo_pago',
            'tabla' => 'metodo_pago',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-wallet2', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'rose',
            'colores' => ['icono' => 'from-rose-400 to-rose-600', 'boton' => 'from-rose-500 to-rose-600'],
        ],
        'paises' => [
            'modelo' => Pais::class,
            'campoId' => 'id_pais',
            'tabla' => 'pais',
            'campos' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'text', 'icono' => 'bi-hash', 'requerido' => true],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-globe', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'codigo', 'label' => 'Código', 'tipo' => 'simple'],
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'sky',
            'colores' => ['icono' => 'from-sky-400 to-sky-600', 'boton' => 'from-sky-500 to-sky-600'],
        ],
        'roles' => [
            'modelo' => Rol::class,
            'campoId' => 'id_rol',
            'tabla' => 'rol',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-shield-lock', 'requerido' => true],
                ['clave' => 'descripcion', 'label' => 'Descripción', 'tipo' => 'textarea', 'icono' => 'bi-file-text'],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'violet',
            'colores' => ['icono' => 'from-violet-400 to-violet-600', 'boton' => 'from-violet-500 to-violet-600'],
        ],
        'tipos_hora_recargo' => [
            'modelo' => TipoHoraRecargo::class,
            'campoId' => 'id_tipo_hora_recargo',
            'tabla' => 'tipo_hora_recargo',
            'campos' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text', 'icono' => 'bi-clock', 'requerido' => true],
            ],
            'columnas' => [
                ['clave' => 'nombre', 'label' => 'Nombre', 'tipo' => 'simple'],
            ],
            'colorBoton' => 'amber',
            'colores' => ['icono' => 'from-amber-400 to-amber-600', 'boton' => 'from-amber-500 to-amber-600'],
        ],
    ];

    public function index(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Actualizaciones index hit', [
            'session_id' => $request->session()->getId(),
            'auth' => auth()->check() ? auth()->user()->doc : 'guest',
            'success' => session('success'),
            'error' => session('error'),
            'all_session' => $request->session()->all()
        ]);

        $items = collect([
            ['titulo' => 'Ciudades', 'desc' => 'Gestiona el listado de ciudades y regiones del sistema.', 'icono' => 'bi-geo-alt'],
            ['titulo' => 'Tipos de Documento', 'desc' => 'Configura tipos de identificación como CC, NIT, CE.', 'icono' => 'bi-person-badge'],
            ['titulo' => 'Bancos', 'desc' => 'Gestiona entidades bancarias para nómina.', 'icono' => 'bi-bank'],
            ['titulo' => 'Roles', 'desc' => 'Define roles de trabajo, jerarquías y responsabilidades.', 'icono' => 'bi-briefcase'],
            ['titulo' => 'Tipos de Contrato', 'desc' => 'Configura tipos de contrato laboral.', 'icono' => 'bi-file-earmark-text'],
            ['titulo' => 'EPS', 'desc' => 'Gestión de entidades de salud.', 'icono' => 'bi-heart-pulse'],
            ['titulo' => 'ARL', 'desc' => 'Gestión de riesgos laborales.', 'icono' => 'bi-shield-check'],
            ['titulo' => 'Empresas', 'desc' => 'Gestiona entidades empresariales del sistema.', 'icono' => 'bi-building'],
            ['titulo' => 'Departamentos', 'desc' => 'Gestiona los departamentos y regiones administrativas.', 'icono' => 'bi-map'],
            ['titulo' => 'Estados', 'desc' => 'Define estados o estatus para procesos.', 'icono' => 'bi-info-circle'],
            ['titulo' => 'Formas de Pago', 'desc' => 'Configura formas de pago disponibles.', 'icono' => 'bi-credit-card'],
            ['titulo' => 'Métodos de Pago', 'desc' => 'Gestiona métodos de pago para nómina.', 'icono' => 'bi-wallet2'],
            ['titulo' => 'Países', 'desc' => 'Gestiona el listado de países.', 'icono' => 'bi-globe'],
            ['titulo' => 'Roles de Sistema', 'desc' => 'Define roles y permisos de seguridad del usuario.', 'icono' => 'bi-shield-lock'],
            ['titulo' => 'Tipos Hora Recargo', 'desc' => 'Configura tipos de horas con recargo.', 'icono' => 'bi-clock'],
        ]);

        $perPage = 6;
        $page = (int) $request->get('page', 1);

        $pagedItems = $items
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        $modulos = new LengthAwarePaginator(
            $pagedItems,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $departamentos = Departamento::orderBy('nombre')->get();

        return view('superadmin.actualizaciones.principal', compact('modulos', 'departamentos'));
    }

    public function getDatos($tipo, Request $request)
    {
        if (!isset($this->configuraciones[$tipo])) {
            return response('Tipo de dato no disponible', 404);
        }

        $config = $this->configuraciones[$tipo];
        $modeloClass = $config['modelo'];
        $items = $modeloClass::orderBy('nombre')->get();

        // Obtener opciones para campos select dinámicos
        foreach ($config['campos'] as &$campo) {
            if ($campo['tipo'] === 'select' && !isset($campo['opciones'])) {
                if ($campo['clave'] === 'id_ciudad') {
                    $ciudades = Ciudad::orderBy('nombre')->get();
                    $campo['opciones'] = $ciudades->map(fn($c) => [
                        'id' => $c->id_ciudad,
                        'nombre' => $c->nombre
                    ])->toArray();
                }
            }
        }

        // Pasar configuración a la vista
        $config['tipo'] = $tipo;
        $config['ruta'] = route('superadmin.actualizar', [':id']);

        return view('superadmin.actualizaciones.partials.tabla-generica', compact('items', 'config'));
    }

    /**
     * Mapa de FormRequests por tipo para el método actualizar.
     */
    private $updateRequestMap = [
        'ciudades' => UpdateCiudadRequest::class,
        'tipos_de_documento' => UpdateTipoDocumentoRequest::class,
        'roles_nombres' => UpdateCargoRequest::class,
        'formas_de_pago' => UpdateFormaPagoRequest::class,
        'metodos_de_pago' => UpdateMetodoPagoRequest::class,
    ];

    public function actualizar(Request $request, $id)
    {
        $tipo = $request->input('tipo');

        if (!isset($this->configuraciones[$tipo])) {
            return redirect()->back()->with('error', 'Tipo de dato inválido');
        }

        // Usar FormRequest dedicado si existe para este tipo
        if (isset($this->updateRequestMap[$tipo])) {
            $formRequest = app($this->updateRequestMap[$tipo]);
            $validated = $formRequest->validated();
        } else {
            // Validación genérica para tipos sin FormRequest dedicado
            $config = $this->configuraciones[$tipo];
            $rules = [];
            $messages = [];

            foreach ($config['campos'] as $campo) {
                if ($campo['requerido'] ?? false) {
                    $rules[$campo['clave']] = 'required';
                    $messages[$campo['clave'] . '.required'] = "El/La {$campo['label']} es obligatorio(a).";
                }
                if ($campo['tipo'] === 'email') {
                    $rules[$campo['clave']] = ($rules[$campo['clave']] ?? '') . '|email';
                }
            }

            // Validaciones únicas para tipos sin FormRequest
            if ($tipo === 'empresas') {
                $rules['nit'] = 'required|string|max:50|unique:empresa,nit,' . $id . ',id_empresa';
                $messages['nit.unique'] = 'El NIT ya se encuentra registrado.';
            } elseif ($tipo === 'departamentos') {
                $rules['codigo'] = 'bail|required|regex:/^[0-9]+$/|min:2|max:11|unique:departamento,codigo,' . $id . ',id_departamento';
                $messages['codigo.unique'] = 'El código ya se encuentra registrado.';
                $messages['codigo.regex'] = 'El código debe contener solo números positivos.';
                $messages['codigo.min'] = 'El código debe tener al menos 2 dígitos.';
                $messages['codigo.max'] = 'El código no puede tener más de 11 dígitos.';
            } elseif ($tipo === 'paises') {
                $rules['codigo'] = 'required|string|max:50|unique:pais,codigo,' . $id . ',id_pais';
                $messages['codigo.unique'] = 'El código ya se encuentra registrado.';
            } elseif ($tipo === 'bancos') {
                $rules['codigo'] = 'required|string|max:50|unique:banco,codigo,' . $id . ',id_banco';
                $messages['codigo.unique'] = 'El código ya se encuentra registrado.';
            }

            $validated = $request->validate($rules, $messages);
        }

        $config = $this->configuraciones[$tipo];
        $modeloClass = $config['modelo'];
        $item = $modeloClass::findOrFail($id);

        // Remover campos que no son del modelo
        $validated = collect($validated)
            ->except(['tipo', 'id', '_token', '_method'])
            ->toArray();

        try {
            $item->update($validated);
            return redirect()->back()->with('success', 'Actualizado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Mapa de FormRequests por tipo para el método store.
     */
    private $storeRequestMap = [
        'tipos_de_documento' => StoreTipoDocumentoRequest::class,
        'roles_nombres' => StoreCargoRequest::class,
        'formas_de_pago' => StoreFormaPagoRequest::class,
        'metodos_de_pago' => StoreMetodoPagoRequest::class,
    ];

    public function store(Request $request, $tipo)
    {
        if (!isset($this->configuraciones[$tipo])) {
            return redirect()->back()->with('error', 'Tipo de dato inválido');
        }

        $config = $this->configuraciones[$tipo];
        $modeloClass = $config['modelo'];

        // Usar FormRequest dedicado si existe para este tipo
        if (isset($this->storeRequestMap[$tipo])) {
            $formRequest = app($this->storeRequestMap[$tipo]);
            $validated = $formRequest->validated();
        } else {
            // Validación genérica para tipos sin FormRequest dedicado
            $rules = [];
            $messages = [];

            foreach ($config['campos'] as $campo) {
                if ($campo['requerido'] ?? false) {
                    $rules[$campo['clave']] = 'required';
                    $messages[$campo['clave'] . '.required'] = "El/La {$campo['label']} es obligatorio(a).";
                }
                if ($campo['tipo'] === 'email') {
                    $rules[$campo['clave']] = ($rules[$campo['clave']] ?? '') . '|email';
                }
            }

            // Validaciones únicas para tipos sin FormRequest
            if ($tipo === 'empresas') {
                $rules['nit'] = 'required|string|max:50|unique:empresa,nit';
                $messages['nit.unique'] = 'El NIT ya se encuentra registrado.';
            } elseif ($tipo === 'departamentos') {
                $rules['codigo'] = 'bail|required|regex:/^[0-9]+$/|min:2|max:11|unique:departamento,codigo';
                $messages['codigo.unique'] = 'El código ya se encuentra registrado.';
                $messages['codigo.regex'] = 'El código debe contener solo números positivos.';
                $messages['codigo.min'] = 'El código debe tener al menos 2 dígitos.';
                $messages['codigo.max'] = 'El código no puede tener más de 11 dígitos.';
            } elseif ($tipo === 'paises') {
                $rules['codigo'] = 'required|string|max:50|unique:pais,codigo';
                $messages['codigo.unique'] = 'El código ya se encuentra registrado.';
            } elseif ($tipo === 'bancos') {
                $rules['codigo'] = 'required|string|max:50|unique:banco,codigo';
                $messages['codigo.unique'] = 'El código ya se encuentra registrado.';
            }

            $validated = $request->validate($rules, $messages);
        }

        // Remover campos que no son del modelo
        $validated = collect($validated)
            ->except(['tipo', '_token', '_method'])
            ->toArray();

        try {
            $modeloClass::create($validated);
            return redirect()->back()->with('success', ucfirst($tipo) . ' agregado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al agregar: ' . $e->getMessage());
        }
    }

    public function storeCiudad(StoreCiudadRequest $request)
    {
        $validated = $request->validated();

        try {
            $ciudad = Ciudad::create([
                'codigo' => $validated['codigo'],
                'nombre' => $validated['nombre'],
                'id_departamento' => Departamento::where('codigo', $validated['cod_dep'])->firstOrFail()->id_departamento,
            ]);

            return redirect()->back()->with('success', 'Ciudad agregada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al agregar la ciudad: ' . $e->getMessage());
        }
    }
}
