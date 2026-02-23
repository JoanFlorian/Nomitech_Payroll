<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ciudad;
use App\Models\Departamento;
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

    public function index(Request $request)
    {
        $items = collect($this->config())->map(fn($m) => [
            'titulo' => $m['titulo'], 'desc' => $m['desc'], 'icono' => $m['icono'],
        ])->values();

        $perPage = 6;
        $page = (int) $request->get('page', 1);

        $modulos = new LengthAwarePaginator(
            $items->slice(($page - 1) * $perPage, $perPage)->values(),
            $items->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $departamentos = Departamento::orderBy('nombre')->get();
        return view('superadmin.actualizaciones.principal', compact('modulos', 'departamentos'));
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

    public function getDatos($tipo)
    {
        $config = $this->config($tipo);
        if (!$config) return response('Tipo de dato no disponible', 404);

        $items = $config['modelo']::orderBy('nombre')->get();

        foreach ($config['campos'] as &$campo) {
            if ($campo['tipo'] === 'select' && !isset($campo['opciones']) && $campo['clave'] === 'id_ciudad') {
                $campo['opciones'] = Ciudad::orderBy('nombre')->get()->map(fn($c) => [
                    'id' => $c->id_ciudad, 'nombre' => $c->nombre
                ])->toArray();
            }
        }

        $config['tipo'] = $tipo;
        $config['ruta'] = route('superadmin.actualizar', [':id']);
        return view('superadmin.actualizaciones.partials.tabla-generica', compact('items', 'config'));
    }

    public function actualizar(Request $request, $id)
    {
        $tipo = $request->input('tipo');
        $config = $this->config($tipo);
        if (!$config) return redirect()->back()->with('error', 'Tipo de dato inválido');

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
        if (!$config) return redirect()->back()->with('error', 'Tipo de dato inválido');

        [$rules, $msgs] = $this->buildRules($tipo, $config);
        $data = collect($request->validate($rules, $msgs))->except(['tipo', '_token', '_method'])->toArray();

        try {
            $config['modelo']::create($data);
            return redirect()->back()->with('success', ucfirst($tipo) . ' agregado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al agregar: ' . $e->getMessage());
        }
    }

    public function storeCiudad(Request $request)
    {
        $validated = $request->validate([
            'codigo'  => 'required|string|max:50|unique:ciudad,codigo',
            'nombre'  => 'required|string|max:255',
            'cod_dep' => 'required|exists:departamento,codigo',
        ], [
            'codigo.required' => 'El código es obligatorio',
            'codigo.unique'   => 'Este código de ciudad ya existe',
            'nombre.required' => 'El nombre es obligatorio',
            'cod_dep.required' => 'El departamento es obligatorio',
            'cod_dep.exists'  => 'El departamento seleccionado no existe',
        ]);

        try {
            Ciudad::create([
                'codigo' => $validated['codigo'],
                'nombre' => $validated['nombre'],
                'id_departamento' => Departamento::where('codigo', $validated['cod_dep'])->firstOrFail()->id_departamento,
            ]);
            return redirect()->back()->with('success', 'Ciudad agregada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al agregar la ciudad: ' . $e->getMessage());
        }
    }

    private function buildRules(string $tipo, array $config, $id = null): array
    {
        $rules = $msgs = [];

        foreach ($config['campos'] as $c) {
            if ($c['requerido'] ?? false) {
                $rules[$c['clave']] = 'required';
                $msgs[$c['clave'] . '.required'] = "{$c['label']} es obligatorio";
            }
            if ($c['tipo'] === 'email') {
                $rules[$c['clave']] = ($rules[$c['clave']] ?? '') . '|email';
            }
        }

        foreach (config('actualizaciones.reglas_unicas.' . $tipo, []) as $campo => $regla) {
            [$tabla, $col, $pk] = explode(',', $regla);
            $rules[$campo] = 'required|string|max:50|unique:' . $tabla . ',' . $col . ($id ? ",{$id},{$pk}" : '');
            $msgs[$campo . '.unique'] = 'Este valor ya existe';
        }

        return [$rules, $msgs];
    }
}
