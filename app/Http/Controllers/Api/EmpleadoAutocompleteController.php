<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpleadoAutocompleteController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $empresaId = (int) session('empresa_id');
        $search = trim((string) $request->query('search', ''));
        $limit = max(1, min(20, (int) $request->query('limit', 12)));

        if ($empresaId <= 0) {
            return response()->json(['data' => []]);
        }

        $buildQuery = function (int $empresaFilterId) use ($search, $limit) {
            $query = DB::table('usuario')
                ->join('contrato', 'contrato.doc', '=', 'usuario.doc')
                ->where(function ($q) {
                    $q->where('contrato.activo', true)
                        ->orWhere('contrato.estado_laboral', Contrato::ESTADO_LABORAL_ACTIVO)
                        ->orWhere('contrato.estado_nomina', Contrato::ESTADO_NOMINA_PENDIENTE);
                })
                ->where('contrato.id_empresa', $empresaFilterId)
                ->selectRaw("usuario.doc as id")
                ->selectRaw("usuario.doc as documento")
                ->selectRaw("TRIM(CONCAT_WS(' ', usuario.primer_nombre, usuario.otros_nombres, usuario.primer_apellido, usuario.segundo_apellido)) as nombre")
                ->selectRaw('contrato.salario_base as salario_base');

            if ($search !== '') {
                $term = mb_strtolower($search);
                $query->where(function ($q) use ($search, $term) {
                    $q->where('usuario.doc', 'like', "%{$search}%")
                        ->orWhereRaw(
                            "LOWER(TRIM(CONCAT_WS(' ', usuario.primer_nombre, usuario.otros_nombres, usuario.primer_apellido, usuario.segundo_apellido))) LIKE ?",
                            ["%{$term}%"]
                        );
                });
            }

            return $query
                ->orderBy('usuario.primer_nombre')
                ->orderBy('usuario.primer_apellido')
                ->limit($limit);
        };

        $rows = $buildQuery($empresaId)->get();

        $empleados = $rows
            ->map(fn ($empleado) => [
                'id' => (string) $empleado->id,
                'nombre' => (string) $empleado->nombre,
                'documento' => (string) $empleado->documento,
                'salario_base' => (float) $empleado->salario_base,
            ])
            ->values();

        return response()->json(['data' => $empleados]);
    }
}
