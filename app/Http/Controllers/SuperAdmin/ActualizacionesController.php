<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Http\Requests\Actualizaciones\StoreCiudadRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActualizacionesController extends Controller
{
    private function config(?string $tipo = null): array
    {
        $all = config('actualizaciones.modulos');
        return $tipo ? ($all[$tipo] ?? []) : $all;
    }

    private function opcionesSelectPorClave(string $clave): ?array
    {
        return match ($clave) {
            'id_ciudad' => Ciudad::orderBy('nombre')->get()->map(fn($c) => [
                'id' => $c->id_ciudad,
                'nombre' => $c->nombre,
            ])->toArray(),
            'id_departamento' => Departamento::orderBy('nombre')->get()->map(fn($d) => [
                'id' => $d->id_departamento,
                'nombre' => mb_strtoupper($d->nombre, 'UTF-8'),
            ])->toArray(),
            default => null,
        };
    }

    public function index(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Actualizaciones index hit', [
            'session_id' => $request->session()->getId(),
            'auth' => auth()->check() ? auth()->user()->doc : 'guest',
            'success' => session('success'),
            'error' => session('error'),
            'all_session' => $request->session()->all()
        ]);

        $items = collect($this->config())->map(fn($m) => [
            'titulo' => $m['titulo'],
            'desc' => $m['desc'],
            'icono' => $m['icono'],
        ])->values();

        $perPage = 6;
        $page = (int) $request->get('page', 1);
        $pagedItems = $items->slice(($page - 1) * $perPage, $perPage)->values();

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
        $ciudades = Ciudad::orderBy('nombre')->get();
        return view('superadmin.actualizaciones.principal', compact('modulos', 'departamentos', 'ciudades'));
    }

    public function exportarExcel($tipo)
    {
        $config = $this->config($tipo);
        if (!$config) {
            return redirect()->back()->with('error', 'Tipo de dato no disponible para exportar');
        }

        $items = $config['modelo']::orderBy('nombre')->get();
        $columnas = $config['columnas'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(ucfirst($tipo));

        // Encabezados
        $col = 'A';
        foreach ($columnas as $columna) {
            $sheet->setCellValue($col . '1', $columna['label']);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Datos
        $row = 2;
        foreach ($items as $item) {
            $col = 'A';
            foreach ($columnas as $c) {
                $valor = (($c['tipo'] ?? 'simple') === 'relacion')
                    ? ($item->{$c['relacion']}->{$c['mostrar']} ?? '')
                    : ($item->{$c['clave']} ?? '');
                $sheet->setCellValue($col . $row, $valor);
                $col++;
            }
            $row++;
        }

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $tipo . '_' . date('Y-m-d') . '.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function getDatos(Request $request, $tipo)
    {
        $config = $this->config($tipo);
        if (!$config)
            return response('Tipo de dato no disponible', 404);

        $q = trim((string) $request->get('q', ''));
        $perPage = (int) $request->get('per_page', 50);
        $perPage = max(10, min(100, $perPage));

        $query = $config['modelo']::query();

        $relaciones = collect($config['columnas'])
            ->filter(fn($col) => ($col['tipo'] ?? 'simple') === 'relacion' && !empty($col['relacion']))
            ->pluck('relacion')
            ->unique()
            ->values()
            ->toArray();

        if (!empty($relaciones)) {
            $query->with($relaciones);
        }

        if ($q !== '') {
            $query->where(function ($subQuery) use ($config, $q) {
                foreach ($config['columnas'] as $columna) {
                    if (($columna['tipo'] ?? 'simple') === 'relacion' && !empty($columna['relacion']) && !empty($columna['mostrar'])) {
                        $subQuery->orWhereHas($columna['relacion'], function ($relationQuery) use ($columna, $q) {
                            $relationQuery->where($columna['mostrar'], 'like', '%' . $q . '%');
                        });
                    } elseif (!empty($columna['clave'])) {
                        $subQuery->orWhere($columna['clave'], 'like', '%' . $q . '%');
                    }
                }
            });
        }

        $columnasDisponibles = collect($config['columnas'])->pluck('clave');
        $orden = $columnasDisponibles->contains('nombre') ? 'nombre' : $config['campoId'];

        $items = $query->orderBy($orden)
            ->paginate($perPage)
            ->appends($request->query());

        foreach ($config['campos'] as &$campo) {
            if (($campo['tipo'] ?? null) !== 'select' || isset($campo['opciones'])) {
                continue;
            }

            $campo['opciones'] = $this->opcionesSelectPorClave($campo['clave']) ?? [];
        }

        $config['tipo'] = $tipo;
        $config['ruta'] = route('superadmin.actualizar', [':id']);
        $config['q'] = $q;
        return view('superadmin.actualizaciones.partials.tabla-generica', compact('items', 'config'));
    }

    public function actualizar(Request $request, $id)
    {
        $tipo = $request->input('tipo');
        $config = $this->config($tipo);
        if (!$config)
            return redirect()->back()->with('error', 'Tipo de dato inválido');

        $request->merge($this->normalizarEntrada($request->all()));

        $item = $config['modelo']::findOrFail($id);
        [$rules, $msgs] = $this->buildRules($tipo, $config, $id);
        $data = collect($request->validate($rules, $msgs))->except(['tipo', 'id', '_token', '_method'])->toArray();

        try {
            $item->update($data);
            return redirect()->back()->with('success', 'Actualizado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function store(Request $request, $tipo)
    {
        $config = $this->config($tipo);
        if (!$config)
            return redirect()->back()->with('error', 'Tipo de dato inválido');

        $request->merge($this->normalizarEntrada($request->all()));

        [$rules, $msgs] = $this->buildRules($tipo, $config);
        $data = collect($request->validate($rules, $msgs))->except(['tipo', '_token', '_method'])->toArray();

        try {
            $config['modelo']::create($data);
            return redirect()->back()->with('success', ucfirst($tipo) . ' agregado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al agregar: ' . $e->getMessage());
        }
    }

    public function storeCiudad(StoreCiudadRequest $request)
    {
        $validated = $request->validated();
        $validated = $this->normalizarEntrada($validated);

        try {
            Ciudad::create([
                'codigo' => $validated['codigo'],
                'nombre' => $validated['nombre'],
                'id_departamento' => $validated['id_departamento'],
            ]);
            return redirect()->back()->with('success', 'Ciudad agregada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al agregar la ciudad: ' . $e->getMessage());
        }
    }

    private function buildRules(string $tipo, array $config, $id = null): array
    {
        $rules = $msgs = [];

        $appendRule = function (string $campo, string $regla) use (&$rules) {
            $rules[$campo] = isset($rules[$campo]) && $rules[$campo] !== ''
                ? $rules[$campo] . '|' . $regla
                : $regla;
        };

        $existsRules = [
            'id_departamento' => 'exists:departamento,id_departamento',
            'id_ciudad' => 'exists:ciudad,id_ciudad',
        ];

        $reglasPorClave = [
            'codigo' => [
                'reglas' => ['regex:/^[0-9]{8}$/'],
                'mensajes' => ['regex' => 'El código debe tener exactamente 8 dígitos numéricos'],
            ],
            'nombre' => [
                'reglas' => ['min:2', 'max:100', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+$/'],
                'mensajes' => ['regex' => 'El nombre solo puede contener letras y espacios'],
            ],
            'descripcion' => [
                'reglas' => ['min:5', 'max:255', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s\.,;:()\-]+$/'],
                'mensajes' => ['regex' => 'La descripción solo puede contener letras, números y puntuación básica'],
            ],
            'codigo_alfa2' => [
                'reglas' => ['size:2', 'regex:/^[A-Z]{2}$/'],
                'mensajes' => ['regex' => 'El código debe contener exactamente 2 letras mayúsculas'],
            ],
            'id_eps' => [
                'reglas' => ['regex:/^[0-9]{6,15}$/', 'unique:eps,id_eps' . ($id ? ",{$id},id_eps" : '')],
                'mensajes' => [
                    'regex' => 'El código de EPS solo puede contener números (6 a 15 dígitos)',
                    'unique' => 'Este código de EPS ya existe',
                ],
            ],
            'id_banco' => [
                'reglas' => ['regex:/^[0-9]{1,11}$/', 'unique:banco,id_banco' . ($id ? ",{$id},id_banco" : '')],
                'mensajes' => [
                    'regex' => 'El código del banco solo puede contener números (máximo 11 dígitos)',
                    'unique' => 'Este código de banco ya existe',
                ],
            ],
            'id_arl' => [
                'reglas' => ['regex:/^[0-9]{6,15}$/', 'unique:arl,id_arl' . ($id ? ",{$id},id_arl" : '')],
                'mensajes' => [
                    'regex' => 'El código de ARL solo puede contener números (6 a 15 dígitos)',
                    'unique' => 'Este código de ARL ya existe',
                ],
            ],
            'nit' => [
                'reglas' => ['regex:/^[0-9]{6,15}$/'],
            ],
            'doc_representante' => [
                'reglas' => ['regex:/^[0-9]{6,15}$/'],
            ],
            'telefono' => [
                'reglas' => ['regex:/^[0-9]{1,11}$/'],
                'mensajes' => ['regex' => 'El teléfono solo puede contener números y máximo 11 dígitos'],
            ],
            'razon_social' => [
                'reglas' => ['min:2', 'max:120', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s\.\,&\-]+$/'],
                'mensajes' => ['regex' => 'La razón social contiene caracteres no permitidos'],
            ],
            'direccion' => [
                'reglas' => ['min:5', 'max:120', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9\s#\-\.,]+$/'],
                'mensajes' => ['regex' => 'La dirección contiene caracteres no permitidos'],
            ],
            'seguridad_social' => [
                'reglas' => ['in:0,1'],
                'mensajes' => ['in' => 'El valor de seguridad social no es válido'],
            ],
        ];

        foreach ($config['campos'] as $c) {
            $clave = $c['clave'];
            $tipoCampo = $c['tipo'] ?? 'text';
            $esRequerido = $c['requerido'] ?? false;

            $appendRule($clave, 'bail');
            $appendRule($clave, $esRequerido ? 'required' : 'nullable');

            if ($tipoCampo === 'email') {
                $appendRule($clave, 'string');
                $appendRule($clave, 'email:rfc');
                $appendRule($clave, 'max:120');
                $msgs[$clave . '.email'] = 'El correo electrónico no tiene un formato válido';
            } elseif ($tipoCampo === 'select') {
                $appendRule($clave, 'integer');
            } else {
                $appendRule($clave, 'string');
            }

            if ($c['requerido'] ?? false) {
                $msgs[$clave . '.required'] = "{$c['label']} es obligatorio";
            }

            if (isset($existsRules[$clave])) {
                $appendRule($clave, $existsRules[$clave]);
                $msgs[$clave . '.exists'] = "{$c['label']} seleccionado no es válido";
            }

            if (isset($reglasPorClave[$clave])) {
                foreach ($reglasPorClave[$clave]['reglas'] as $reglaExtra) {
                    $appendRule($clave, $reglaExtra);
                }

                foreach (($reglasPorClave[$clave]['mensajes'] ?? []) as $reglaMsg => $texto) {
                    $msgs[$clave . '.' . $reglaMsg] = $texto;
                }
            }

            if (in_array($clave, ['nit', 'doc_representante'], true)) {
                $msgs[$clave . '.regex'] = "{$c['label']} solo puede contener números (6 a 15 dígitos)";
            }
        }

        foreach (config('actualizaciones.reglas_unicas.' . $tipo, []) as $campo => $regla) {
            [$tabla, $col, $pk] = explode(',', $regla);
            $reglaUnique = 'unique:' . $tabla . ',' . $col . ($id ? ",{$id},{$pk}" : '');
            $reglaBase = $rules[$campo] ?? 'required|string|max:50';
            $rules[$campo] = str_contains($reglaBase, 'unique:') ? $reglaBase : $reglaBase . '|' . $reglaUnique;
            $msgs[$campo . '.unique'] = 'Este valor ya existe';
        }

        return [$rules, $msgs];
    }

    private function normalizarEntrada(array $entrada): array
    {
        $camposTexto = ['nombre', 'razon_social', 'direccion', 'descripcion'];

        foreach ($entrada as $clave => $valor) {
            if (!is_string($valor)) {
                continue;
            }

            $valorLimpio = trim(strip_tags($valor));
            $valorLimpio = preg_replace('/\s+/', ' ', $valorLimpio);

            if (in_array($clave, $camposTexto, true)) {
                $valorLimpio = preg_replace('/\s{2,}/', ' ', $valorLimpio);
            }

            $valorLimpio = mb_strtoupper($valorLimpio, 'UTF-8');

            $entrada[$clave] = $valorLimpio;
        }

        return $entrada;
    }
}
