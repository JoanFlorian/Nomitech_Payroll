<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Ciudad;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $query = Empresa::with('licencia.plan');

        // Buscador por razón social o NIT
        if ($request->buscar) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('razon_social', 'like', '%' . $buscar . '%')
                    ->orWhere('nit', 'like', '%' . $buscar . '%');
            });
        }

        // Filtro por estado (computed accessor, no es columna real en BD)
        if ($request->estado && $request->estado != 'todas') {
            $estadoFiltro = $request->estado;

            $empresas = $query->orderBy('razon_social')->get();

            $empresas = $empresas->filter(function ($empresa) use ($estadoFiltro) {
                $estado = optional($empresa->licencia)->estado ?? 'pendiente_pago';
                return $estado === $estadoFiltro;
            });

            // Paginar manualmente la colección filtrada
            $page = $request->input('page', 1);
            $perPage = 8;
            $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $empresas->forPage($page, $perPage)->values(),
                $empresas->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('superadmin.empresas', ['empresas' => $paginated]);
        }

        $empresas = $query
            ->orderBy('razon_social')
            ->paginate(8)
            ->withQueryString();

        return view('superadmin.empresas', compact('empresas'));
    }

    public function show(Empresa $empresa)
    {
        $empresa->load(['licencia.plan', 'representante', 'ciudad']);

        // Para el select de ciudades en el modal
        $ciudades = Ciudad::orderBy('nombre')->get();

        return view('superadmin.empresas-show', compact('empresa', 'ciudades'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'direccion' => 'required|string|max:150',
            'correo' => 'nullable|email|max:256',
            'telefono' => 'required|string|max:20',
            'doc_representante' => 'required|string',
            'id_ciudad' => 'required|integer',

            'primer_nombre' => 'required|string|max:100',
            'segundo_nombre' => 'nullable|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
        ]);

        $empresa->update([
            'direccion' => $data['direccion'],
            'correo' => $data['correo'] ?? null,
            'telefono' => $data['telefono'],
            'doc_representante' => $data['doc_representante'],
            'id_ciudad' => $data['id_ciudad'],
        ]);

        $usuario = Usuario::where('doc', $data['doc_representante'])->first();

        if ($usuario) {
            $usuario->update([
                'primer_nombre' => $data['primer_nombre'],
                'otros_nombres' => $data['segundo_nombre'] ?? null,
                'primer_apellido' => $data['primer_apellido'],
                'segundo_apellido' => $data['segundo_apellido'] ?? null,
            ]);
        }

        return redirect()
            ->route('superadmin.empresas.show', $empresa->id_empresa)
            ->with('success', 'Datos actualizados correctamente.');
    }
}
