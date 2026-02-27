<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Ciudad;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
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

    public function validarCorreo(Request $request, Empresa $empresa)
    {
        $correo = trim((string) $request->input('correo', ''));

        if ($correo === '') {
            return response()->json([
                'available' => false,
                'message' => 'El correo es obligatorio.',
            ]);
        }

        if (mb_strlen($correo, 'UTF-8') > 100) {
            return response()->json([
                'available' => false,
                'message' => 'El correo debe tener máximo 100 caracteres.',
            ]);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'available' => false,
                'message' => 'El correo no tiene un formato válido.',
            ]);
        }

        $correoNormalizado = mb_strtoupper($correo, 'UTF-8');

        $existeEnOtraEmpresa = Empresa::query()
            ->whereRaw('UPPER(correo) = ?', [$correoNormalizado])
            ->where('id_empresa', '!=', $empresa->id_empresa)
            ->exists();

        return response()->json([
            'available' => !$existeEnOtraEmpresa,
            'message' => $existeEnOtraEmpresa
                ? 'Este correo ya está registrado en otra empresa.'
                : '',
        ]);
    }

   public function update(Request $request, Empresa $empresa)
{
    $validated = $request->validate([

        
        'direccion' => [
            'required',
            'string',
            'max:150',
            'regex:/^(?=.*[A-Za-z])(?=.*(calle|carrera|cra\.?|cl\.?|av\.?|avenida|transversal|diagonal|#|no\.?)).+$/i'
        ],

        
        'id_ciudad' => [
            'required',
            'exists:ciudad,id_ciudad'
        ],

        
        'correo' => [
            'required',
            'email:rfc',
            'max:100',
            Rule::unique((new Empresa())->getTable(), 'correo')
                ->ignore($empresa->id_empresa, 'id_empresa')
        ],

        // TELÉFONO (máximo 11 dígitos numéricos)
        'telefono' => [
            'required',
            'regex:/^[0-9]{1,11}$/'
        ],

        // DOCUMENTO REPRESENTANTE
        'doc_representante' => [
            'required',
            'digits_between:7,12',
            'exists:' . (new Usuario())->getTable() . ',doc'
        ],

        // NOMBRES
        'primer_nombre' => [
            'required',
            'string',
            'max:60',
            'regex:/^(?=.*\pL)[\pL\s]+$/u'
        ],

        'segundo_nombre' => [
            'nullable',
            'string',
            'max:60',
            'regex:/^(?=.*\pL)[\pL\s]+$/u'
        ],

        'primer_apellido' => [
            'required',
            'string',
            'max:60',
            'regex:/^(?=.*\pL)[\pL\s]+$/u'
        ],

        'segundo_apellido' => [
            'nullable',
            'string',
            'max:60',
            'regex:/^(?=.*\pL)[\pL\s]+$/u'
        ],

    ], [

        'direccion.required' => 'La dirección es obligatoria.',
        'direccion.regex' => 'La dirección debe tener formato válido (Calle, Carrera, Av, #, etc).',

        'id_ciudad.required' => 'Debes seleccionar una ciudad.',
        'id_ciudad.exists' => 'La ciudad seleccionada no es válida.',

        'correo.required' => 'El correo es obligatorio.',
        'correo.email' => 'El correo no tiene un formato válido.',
        'correo.unique' => 'Este correo ya está registrado en otra empresa.',

        'telefono.required' => 'El teléfono es obligatorio.',
        'telefono.regex' => 'El teléfono debe contener solo números y máximo 11 dígitos.',

        'doc_representante.required' => 'El documento del representante es obligatorio.',
        'doc_representante.exists' => 'No existe un usuario con ese documento.',

        'primer_nombre.required' => 'El primer nombre es obligatorio.',
        'primer_nombre.regex' => 'El nombre solo puede contener letras.',

        'primer_apellido.required' => 'El primer apellido es obligatorio.',
        'primer_apellido.regex' => 'El apellido solo puede contener letras.',
    ]);

    // Normalización en MAYÚSCULAS
    $normalizarTexto = function (?string $valor): ?string {
        if ($valor === null) {
            return null;
        }

        $valor = preg_replace('/\s+/', ' ', trim($valor));

        if ($valor === '') {
            return null;
        }

        return mb_strtoupper($valor, 'UTF-8');
    };

    $validated['primer_nombre'] = $normalizarTexto($validated['primer_nombre']);
    $validated['segundo_nombre'] = $normalizarTexto($validated['segundo_nombre'] ?? null);
    $validated['primer_apellido'] = $normalizarTexto($validated['primer_apellido']);
    $validated['segundo_apellido'] = $normalizarTexto($validated['segundo_apellido'] ?? null);
    $validated['correo'] = mb_strtoupper(trim($validated['correo']), 'UTF-8');
    $validated['direccion'] = mb_strtoupper(trim($validated['direccion']), 'UTF-8');

    DB::transaction(function () use ($empresa, $validated) {
        // ACTUALIZAR EMPRESA
        $empresa->update([
            'direccion' => $validated['direccion'],
            'correo' => $validated['correo'],
            'telefono' => $validated['telefono'],
            'doc_representante' => $validated['doc_representante'],
            'id_ciudad' => $validated['id_ciudad'],
        ]);

        // 🔐 SEGURIDAD: usar relación en vez de buscar manualmente
        $usuario = $empresa->representante;

        if ($usuario) {
            $usuario->update([
                'primer_nombre' => $validated['primer_nombre'],
                'otros_nombres' => $validated['segundo_nombre'],
                'primer_apellido' => $validated['primer_apellido'],
                'segundo_apellido' => $validated['segundo_apellido'],
            ]);
        }
    });

    return redirect()
        ->route('superadmin.empresas.show', $empresa->id_empresa)
        ->with('success', 'Datos actualizados correctamente.');
}
}