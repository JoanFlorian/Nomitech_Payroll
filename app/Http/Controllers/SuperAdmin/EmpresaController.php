<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Ciudad;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $query = Empresa::with('licencia.plan');

        // Búsqueda por razón social o NIT
        if ($request->buscar) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('razon_social', 'like', '%' . $buscar . '%')
                  ->orWhere('nit', 'like', '%' . $buscar . '%');
            });
        }

        // Filtro por estado (acceso calculado, no es columna real en BD)
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

            $paginated = new LengthAwarePaginator(
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

        // Para la lista desplegable de ciudades en el modal
        $ciudades = Ciudad::orderBy('nombre')->get();

        return view('superadmin.empresas-show', compact('empresa', 'ciudades'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'direccion' => [
                'required',
                'string',
                'max:150',
                'regex:/[a-zA-Z]/',
                'regex:/^(?!.*\d{8,}).*$/',
                'regex:/(calle|carrera|cra\.?|cl\.?|av\.?|avenida|#|no\.?)/i',
            ],
           'id_ciudad' => ['required', 'exists:ciudad,id_ciudad'],
            // Correo estricto
            'correo' => ['required', 'email:rfc,dns', 'max:100'],

            // Teléfono solo números (7–10 dígitos)
            'telefono' => ['required', 'digits_between:7,10'],

            'doc_representante' => ['required', 'digits_between:7,10', 'exists:usuario,doc'],


            'primer_nombre' => ['required', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', 'max:70'],
            'segundo_nombre'  => ['nullable', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', 'max:100'],
            'primer_apellido' => ['required', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', 'max:100'],
            'segundo_apellido'=> ['nullable', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/u', 'max:100'],
        ], [
            'direccion.required' => 'La dirección es obligatoria.',
            'direccion.regex' => 'La dirección debe tener un formato válido (ej: Calle, Carrera, Av, #, No.).',
            'direccion.max' => 'La dirección no puede superar los 150 caracteres.',

            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe tener un formato válido (ej: usuario@dominio.com).',
            'correo.max' => 'El correo no puede superar los 100 caracteres.',

            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.digits_between' => 'El teléfono debe contener solo números y tener entre 7 y 10 dígitos.',

            'doc_representante.required' => 'El documento del representante es obligatorio.',
            'doc_representante.digits_between' => 'El documento debe contener solo números y tener máximo 10 dígitos.',
            'doc_representante.exists' => 'No existe un usuario con el documento del representante proporcionado.',
            'id_ciudad.required' => 'Debes seleccionar una ciudad.',
            'id_ciudad.exists'   => 'La ciudad seleccionada no es válida.',
            'primer_nombre.required' => 'El primer nombre del representante es obligatorio.',
            'primer_nombre.regex' => 'El primer nombre solo debe contener letras.',
            'segundo_nombre.regex'  => 'El segundo nombre solo debe contener letras.',
            'primer_apellido.regex' => 'El primer apellido solo debe contener letras.',
            'segundo_apellido.regex'=> 'El segundo apellido solo debe contener letras.',


            'primer_apellido.required' => 'El primer apellido del representante es obligatorio.',
        ]);

        $empresa->update([
            'direccion' => $data['direccion'],
            'correo' => $data['correo'],
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