<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $empresas = Empresa::with('licencia.plan')->get();

        // Filtro por estado
        if ($request->estado && $request->estado != 'todas') {
            $empresas = $empresas->filter(function ($empresa) use ($request) {
                $estado = $empresa->licencia->estado_calculado ?? 'prueba';
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

        return view('superadmin.empresas-show', compact('empresa'));

    }

    public function update(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'razon_social' => 'required|string|max:150',
            'direccion'    => 'required|string|max:150',
            'correo'       => 'nullable|email|max:256',
            'telefono'     => 'required|string|max:20',
            'doc_representante' => 'required|string',
            'id_ciudad'    => 'required|integer',
        ]);

        $empresa->update($data);

        return redirect()
            ->route('superadmin.empresas.show', $empresa->id_empresa)
            ->with('success', 'Datos actualizados correctamente.');
    }
}
