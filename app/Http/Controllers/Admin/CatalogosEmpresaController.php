<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogoRequest;
use App\Services\CatalogoEmpresaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogosEmpresaController extends Controller
{
    public function __construct(private readonly CatalogoEmpresaService $service)
    {
    }

    public function index()
    {
        $catalogos = $this->service->getCatalogos();

        return view('admin.catalogos.index', compact('catalogos'));
    }

    public function show(string $catalogo)
    {
        $catalogoConfig = $this->service->getCatalogoConfig($catalogo);
        $displayColumns = $this->service->getDisplayColumns($catalogo);
        $formFields = $this->service->getFormFields($catalogo);

        return view('admin.catalogos.show', [
            'catalogo' => $catalogo,
            'catalogoConfig' => $catalogoConfig,
            'displayColumns' => $displayColumns,
            'formFields' => $formFields,
            'catalogos' => $this->service->getCatalogos(),
        ]);
    }

    public function data(Request $request, string $catalogo): JsonResponse
    {
        $this->service->getCatalogoConfig($catalogo);

        $search = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min($perPage, 50));

        $items = $this->service->paginate($catalogo, $search, $perPage);

        return response()->json([
            'success' => true,
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function store(CatalogoRequest $request, string $catalogo): JsonResponse
    {
        $id = $this->service->create($catalogo, $request->validated());

        return response()->json([
            'success' => true,
            'id' => $id,
            'message' => 'Registro creado correctamente.',
        ]);
    }

    public function update(CatalogoRequest $request, string $catalogo, int $id): JsonResponse
    {
        $this->service->update($catalogo, $id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
        ]);
    }

    public function toggleEstado(Request $request, string $catalogo, int $id): JsonResponse
    {
        $this->service->getCatalogoConfig($catalogo);

        $validated = $request->validate([
            'estado' => ['required', 'boolean'],
        ]);

        $this->service->toggleEstado($catalogo, $id, (bool) $validated['estado']);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
        ]);
    }
}
