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
                ->leftJoin('benefit_balance', function($join) {
                    $join->on('benefit_balance.employee_id', '=', 'usuario.doc')
                         ->on('benefit_balance.tenant_id', '=', 'contrato.id_empresa');
                })
                ->leftJoin('eps', 'eps.id_eps', '=', 'contrato.id_eps')
                ->leftJoin('afp', 'afp.id_afp', '=', 'contrato.id_afp')
                ->where(function ($q) {
                    $q->where('contrato.activo', true)
                        ->orWhereIn('contrato.estado', [
                            Contrato::ESTADO_ACTIVO,
                            Contrato::ESTADO_POR_VENCER,
                            Contrato::ESTADO_PROGRAMADO,
                            Contrato::ESTADO_VENCIDO,
                        ]);
                })
                ->where('contrato.id_empresa', $empresaFilterId)
                ->selectRaw("usuario.doc as id")
                ->selectRaw("usuario.doc as documento")
                ->selectRaw("TRIM(CONCAT_WS(' ', usuario.primer_nombre, usuario.otros_nombres, usuario.primer_apellido, usuario.segundo_apellido)) as nombre")
                ->selectRaw('contrato.salario_base as salario_base')
                ->selectRaw('COALESCE(benefit_balance.vacaciones_balance, 0) as vacaciones_balance')
                ->selectRaw('contrato.id_eps as id_eps')
                ->selectRaw('eps.nombre as eps_nombre')
                ->selectRaw('contrato.id_afp as id_afp')
                ->selectRaw('afp.nombre as afp_nombre');

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
                'vacaciones_balance' => (float) $empleado->vacaciones_balance,
                'id_eps' => $empleado->id_eps ? (int) $empleado->id_eps : null,
                'eps_nombre' => $empleado->eps_nombre ? (string) $empleado->eps_nombre : null,
                'id_afp' => $empleado->id_afp ? (int) $empleado->id_afp : null,
                'afp_nombre' => $empleado->afp_nombre ? (string) $empleado->afp_nombre : null,
            ])
            ->values();

        return response()->json(['data' => $empleados]);
    }
}
