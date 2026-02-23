<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\TipoDoc;
use App\Models\Departamento;
use App\Models\Ciudad;
use App\Models\TipoTrabajador;
use App\Models\SubTipoTrabajador;
use App\Models\TipoContrato;
use App\Models\Arl;
use App\Models\FormaPago;
use App\Models\MetodoPago;
use App\Models\TipoCuenta;
use App\Models\Eps;
use App\Models\Afp;
use App\Services\EmployeesExport;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    /**
     * Mostrar lista de empleados con paginación, búsqueda y filtros
     */
    public function index(Request $request)
    {
        $query = Usuario::with('contratos');

        // Búsqueda por nombre o documento
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('primer_nombre', 'like', "%{$search}%")
                  ->orWhere('primer_apellido', 'like', "%{$search}%")
                  ->orWhere('doc', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $estado = $request->input('estado');
            if ($estado === 'activos') {
                $query->whereHas('contratos', function ($q) {
                    $q->where('activo', 1);
                });
            } elseif ($estado === 'inactivos') {
                $query->whereHas('contratos', function ($q) {
                    $q->where('activo', 0);
                });
            } elseif ($estado === 'sin_contrato') {
                $query->doesntHave('contratos');
            }
        }

        // Obtener empleados con paginación (4 por página)
        $empleados = $query->paginate(4)->appends($request->query());

        // Obtener datos necesarios para los formularios del wizard
        $tipodoc = TipoDoc::all();
        $departamento = Departamento::all();
        $ciudad = Ciudad::all();
        $tipotrabajadores = TipoTrabajador::all();
        $suptrabajadores = SubTipoTrabajador::all();
        $contratos = TipoContrato::all();
        $Arl = Arl::all();
        $formapagos = FormaPago::all();
        $metodopago = MetodoPago::all();
        $tipocuenta = TipoCuenta::all();
        $Eps = Eps::all();
        $Afp = Afp::all();

        // Obtener conteos para los filtros
        $totalEmpleados = Usuario::with('contratos')->count();
        $activosCount = Usuario::whereHas('contratos', function ($q) { $q->where('activo', 1); })->count();
        $inactivosCount = Usuario::whereHas('contratos', function ($q) { $q->where('activo', 0); })->count();
        $sinContratoCount = Usuario::doesntHave('contratos')->count();

        // Define the step variable for the view
        $step = $request->input('step', 1);

        return view('empleados.index', compact(
            'empleados', 'tipodoc', 'departamento', 'ciudad', 'tipotrabajadores', 'suptrabajadores', 'contratos', 'Arl', 'formapagos', 'metodopago', 'tipocuenta', 'Eps', 'Afp', 'totalEmpleados', 'activosCount', 'inactivosCount', 'sinContratoCount', 'step'
        ));
    }

    /**
     * Exportar empleados activos a Excel/CSV
     */
    public function export()
    {
        return EmployeesExport::export();
    }
}
