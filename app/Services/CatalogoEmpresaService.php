<?php

namespace App\Services;

use App\Models\Empresa;
use App\Repositories\CatalogoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CatalogoEmpresaService
{
    private array $technicalColumns = ['created_at', 'updated_at', 'empresa_nit', 'origen'];

    public function __construct(private readonly CatalogoRepository $repository)
    {
    }

    public function getCatalogos(): array
    {
        return config('catalogos', []);
    }

    public function getCatalogoConfig(string $catalogo): array
    {
        $config = $this->getCatalogos()[$catalogo] ?? null;

        if (!$config) {
            abort(404, 'Catalogo no permitido.');
        }

        return $config;
    }

    public function getEmpresaNit(): string
    {
        $empresaId = session('empresa_id');
        if (!$empresaId) {
            throw ValidationException::withMessages([
                'empresa' => 'No hay una empresa seleccionada en la sesion.',
            ]);
        }

        $nit = Empresa::query()->where('id_empresa', $empresaId)->value('nit');

        if (!$nit) {
            throw ValidationException::withMessages([
                'empresa' => 'No se pudo resolver el NIT de la empresa actual.',
            ]);
        }

        return (string) $nit;
    }

    public function paginate(string $catalogo, string $search, int $perPage): LengthAwarePaginator
    {
        $catalogoConfig = $this->getCatalogoConfig($catalogo);
        $empresaNit = $this->getEmpresaNit();

        return $this->repository->paginate($catalogoConfig, $empresaNit, $search, $perPage);
    }

    public function getDisplayColumns(string $catalogo): array
    {
        $catalogoConfig = $this->getCatalogoConfig($catalogo);
        $table = $catalogoConfig['table'];
        $primaryKey = $catalogoConfig['primary_key'];

        $allColumns = collect(Schema::getColumnListing($table))
            ->reject(fn(string $column) => in_array($column, [...$this->technicalColumns, $primaryKey], true))
            ->values();

        $preferred = collect([
            'nombre',
            'telefono',
            'direccion',
            'descripcion',
            'seguridad_social',
            'origen',
            'estado',
        ])->filter(fn(string $col) => $allColumns->contains($col));

        $ordered = $preferred
            ->merge($allColumns->reject(fn(string $col) => $preferred->contains($col)))
            ->values();

        return $ordered->map(fn(string $key) => [
            'key' => $key,
            'label' => mb_strtoupper(str_replace('_', ' ', $key), 'UTF-8'),
        ])->toArray();
    }

    public function getFormFields(string $catalogo): array
    {
        $catalogoConfig = $this->getCatalogoConfig($catalogo);
        $table = $catalogoConfig['table'];
        $primaryKey = $catalogoConfig['primary_key'];

        $fields = collect(Schema::getColumnListing($table))
            ->reject(fn(string $column) => in_array($column, [...$this->technicalColumns, $primaryKey], true))
            ->values();

        $preferred = collect(['nombre', 'descripcion', 'telefono', 'direccion', 'seguridad_social', 'estado'])
            ->filter(fn(string $column) => $fields->contains($column));

        $fields = $preferred
            ->merge($fields->reject(fn(string $column) => $preferred->contains($column)))
            ->values();

        return $fields->map(function (string $key) {
            $type = match ($key) {
                'estado', 'seguridad_social' => 'switch',
                'telefono' => 'tel',
                default => 'text',
            };

            return [
                'key' => $key,
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'type' => $type,
                'required' => $key === 'nombre',
                'default' => in_array($key, ['estado', 'seguridad_social'], true),
            ];
        })->toArray();
    }

    public function getEditableColumns(string $catalogo): array
    {
        $catalogoConfig = $this->getCatalogoConfig($catalogo);
        $table = $catalogoConfig['table'];
        $primaryKey = $catalogoConfig['primary_key'];

        return collect(Schema::getColumnListing($table))
            ->reject(fn(string $column) => in_array($column, [...$this->technicalColumns, $primaryKey], true))
            ->values()
            ->toArray();
    }

    public function preparePayload(string $catalogo, array $data): array
    {
        $columns = $this->getEditableColumns($catalogo);
        $payload = [];

        foreach ($columns as $column) {
            if (in_array($column, ['estado', 'seguridad_social'], true)) {
                $payload[$column] = $this->normalizeBoolean($data[$column] ?? false);
                continue;
            }

            if (!array_key_exists($column, $data)) {
                $payload[$column] = null;
                continue;
            }

            $payload[$column] = is_string($data[$column])
                ? trim($data[$column])
                : $data[$column];
        }

        return $payload;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [1, '1', true, 'true', 'on'], true);
    }

    public function create(string $catalogo, array $data): int
    {
        $catalogoConfig = $this->getCatalogoConfig($catalogo);
        $empresaNit = $this->getEmpresaNit();

        if ($this->repository->existsByNameForTenant($catalogoConfig, $data['nombre'], $empresaNit)) {
            throw ValidationException::withMessages([
                'nombre' => 'Ya existe un registro con ese nombre en tu empresa.',
            ]);
        }

        $payload = $this->preparePayload($catalogo, $data);
        $payload['empresa_nit'] = $empresaNit;
        $payload['origen'] = 'empresa';
        $payload['created_at'] = now();
        $payload['updated_at'] = now();

        return $this->repository->create($catalogoConfig, $payload);
    }

    public function update(string $catalogo, int $id, array $data): void
    {
        $catalogoConfig = $this->getCatalogoConfig($catalogo);
        $empresaNit = $this->getEmpresaNit();

        $item = $this->repository->findVisibleById($catalogoConfig, $id, $empresaNit);

        if (!$item) {
            throw ValidationException::withMessages([
                'registro' => 'Registro no encontrado para tu empresa.',
            ]);
        }

        if ($item->origen !== 'empresa' || $item->empresa_nit !== $empresaNit) {
            throw ValidationException::withMessages([
                'registro' => 'Los registros del sistema no son editables.',
            ]);
        }

        if ($this->repository->existsByNameForTenant($catalogoConfig, $data['nombre'], $empresaNit, $id)) {
            throw ValidationException::withMessages([
                'nombre' => 'Ya existe un registro con ese nombre en tu empresa.',
            ]);
        }

        $payload = $this->preparePayload($catalogo, $data);
        $payload['updated_at'] = now();

        $this->repository->updateById($catalogoConfig, $id, $payload);
    }

    public function toggleEstado(string $catalogo, int $id, bool $estado): void
    {
        $catalogoConfig = $this->getCatalogoConfig($catalogo);
        $empresaNit = $this->getEmpresaNit();

        $item = $this->repository->findVisibleById($catalogoConfig, $id, $empresaNit);

        if (!$item) {
            throw ValidationException::withMessages([
                'registro' => 'Registro no encontrado para tu empresa.',
            ]);
        }

        if ($item->origen !== 'empresa' || $item->empresa_nit !== $empresaNit) {
            throw ValidationException::withMessages([
                'registro' => 'Los registros del sistema no son editables.',
            ]);
        }

        $this->repository->updateById($catalogoConfig, $id, [
            'estado' => $estado,
            'updated_at' => now(),
        ]);
    }
}
