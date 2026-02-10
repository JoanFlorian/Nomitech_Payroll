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
        $empresas = Empresa::with('licencia.plan')->get();

        // Filtro por estado
        if ($request->estado && $request->estado != 'todas') {
            $empresas = $empresas->filter(function ($empresa) use ($request) {
                $estado = optional($empresa->licencia)->estado_calculado ?? 'prueba';
                return $estado == $request->estado;
            });
        }

        // Buscador
        if ($request->buscar) {
            $empresas = $empresas->filter(function ($empresa) use ($request) {
                return str_contains(strtolower($empresa->razon_social), strtolower($request->buscar))
                    || str_contains(strtolower($empresa->nit), strtolower($request->buscar));
            });
        }

        return view('superadmin.empresas', compact('empresas'));
    }

    public function show(Empresa $empresa)
    {
        $empresa->load(['licencia.plan', 'representante', 'ciudad']);

        // 👇 AGREGAR ESTO (para el select de ciudades en el modal)
        $ciudades = Ciudad::orderBy('nombre')->get();

        return view('superadmin.empresas-show', compact('empresa', 'ciudades'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'direccion'         => 'required|string|max:150',
            'correo'            => 'nullable|email|max:256',
            'telefono'          => 'required|string|max:20',
            'doc_representante' => 'required|string',
            'id_ciudad'         => 'required|integer',

            'primer_nombre'     => 'required|string|max:100',
            'segundo_nombre'    => 'nullable|string|max:100',
            'primer_apellido'   => 'required|string|max:100',
            'segundo_apellido'  => 'nullable|string|max:100',
        ]);

        $empresa->update([
            'direccion'         => $data['direccion'],
            'correo'            => $data['correo'] ?? null,
            'telefono'          => $data['telefono'],
            'doc_representante' => $data['doc_representante'],
            'id_ciudad'         => $data['id_ciudad'],
        ]);

        $usuario = Usuario::where('doc', $data['doc_representante'])->first();

        if ($usuario) {
            $usuario->update([
                'primer_nombre'    => $data['primer_nombre'],
                'otros_nombres'    => $data['segundo_nombre'] ?? null,
                'primer_apellido'  => $data['primer_apellido'],
                'segundo_apellido' => $data['segundo_apellido'] ?? null,
            ]);
        }

        return redirect()
            ->route('superadmin.empresas.show', $empresa->id_empresa)
            ->with('success', 'Datos actualizados correctamente.');
    }
}
