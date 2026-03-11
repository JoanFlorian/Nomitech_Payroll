<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CatalogoRepository
{
    public function paginate(array $catalogoConfig, string $empresaNit, string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $table = $catalogoConfig['table'];

        $query = DB::table($table)
            ->where(function ($q) use ($empresaNit) {
                $q->whereNull('empresa_nit')
                    ->orWhere('empresa_nit', $empresaNit);
            });

        if ($search !== '') {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        return $query
            ->orderByDesc('origen')
            ->orderBy('nombre')
            ->paginate($perPage, ['*'], 'page')
            ->through(function ($item) use ($empresaNit) {
                $item->editable = $item->origen === 'empresa' && $item->empresa_nit === $empresaNit;
                return $item;
            });
    }

    public function create(array $catalogoConfig, array $payload): int
    {
        $table = $catalogoConfig['table'];
        $pk = $catalogoConfig['primary_key'];

        if (!empty($catalogoConfig['manual_id'])) {
            $nextId = ((int) DB::table($table)->max($pk)) + 1;
            $payload[$pk] = $nextId;
        }

        DB::table($table)->insert($payload);

        return (int) ($payload[$pk] ?? DB::getPdo()->lastInsertId());
    }

    public function findVisibleById(array $catalogoConfig, int $id, string $empresaNit): ?object
    {
        $table = $catalogoConfig['table'];
        $pk = $catalogoConfig['primary_key'];

        return DB::table($table)
            ->where($pk, $id)
            ->where(function ($q) use ($empresaNit) {
                $q->whereNull('empresa_nit')
                    ->orWhere('empresa_nit', $empresaNit);
            })
            ->first();
    }

    public function updateById(array $catalogoConfig, int $id, array $payload): int
    {
        $table = $catalogoConfig['table'];
        $pk = $catalogoConfig['primary_key'];

        return DB::table($table)
            ->where($pk, $id)
            ->update($payload);
    }

    public function existsByNameForTenant(array $catalogoConfig, string $nombre, string $empresaNit, ?int $ignoreId = null): bool
    {
        $table = $catalogoConfig['table'];
        $pk = $catalogoConfig['primary_key'];

        $query = DB::table($table)
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre, 'UTF-8')])
            ->where('empresa_nit', $empresaNit);

        if ($ignoreId !== null) {
            $query->where($pk, '!=', $ignoreId);
        }

        return $query->exists();
    }
}
