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

        // Filtro por estado
        if ($request->estado && $request->estado != 'todas') {
            $query->whereHas('licencia', function ($q) use ($request) {
                $q->where('estado', $request->estado);
            });
        }

        // Buscador por razón social o NIT
        if ($request->buscar) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('razon_social', 'like', '%' . $buscar . '%')
                    ->orWhere('nit', 'like', '%' . $buscar . '%');
            });
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
