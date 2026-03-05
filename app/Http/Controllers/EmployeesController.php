<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
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
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Contrato;

class EmployeesController extends Controller
{
    private function construirConsultaEmpleados(Request $request)
    {
        $query = Empleado::with([
            'contratos' => function ($q) {
                $q->orderByDesc('id_contrato')->with('tipoContrato');
            }
        ])->orderByDesc('created_at')->orderByDesc('doc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('primer_nombre', 'like', "%{$search}%")
                    ->orWhere('primer_apellido', 'like', "%{$search}%")
                    ->orWhere('doc', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->input('estado');
            if ($estado === 'activos') {
                $query->whereHas('contratos', function ($q) {
                    $q->where('estado_laboral', Contrato::ESTADO_LABORAL_ACTIVO);
                });
            } elseif ($estado === 'inactivos') {
                $query->whereHas('contratos', function ($q) {
                    $q->where('estado_laboral', Contrato::ESTADO_LABORAL_TERMINADO);
                });
            } elseif ($estado === 'sin_contrato') {
                $query->doesntHave('contratos');
            }
        }

        return $query;
    }

    /**
     * Mostrar lista de empleados con paginación, búsqueda y filtros
     */
    public function index(Request $request)
    {
        $query = $this->construirConsultaEmpleados($request);

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

        // Obtener conteos para los filtros usando Empleado para aislamiento
        $totalEmpleados = Empleado::with('contratos')->count();
        $activosCount = Empleado::whereHas('contratos', function ($q) {
            $q->where('estado_laboral', Contrato::ESTADO_LABORAL_ACTIVO);
        })->count();
        $inactivosCount = Empleado::whereHas('contratos', function ($q) {
            $q->where('estado_laboral', Contrato::ESTADO_LABORAL_TERMINADO);
        })->count();
        $sinContratoCount = Empleado::doesntHave('contratos')->count();

        // Define the step variable for the view
        $step = $request->input('step', 1);

        return view('empleados.index', compact(
            'empleados',
            'tipodoc',
            'departamento',
            'ciudad',
            'tipotrabajadores',
            'suptrabajadores',
            'contratos',
            'Arl',
            'formapagos',
            'metodopago',
            'tipocuenta',
            'Eps',
            'Afp',
            'totalEmpleados',
            'activosCount',
            'inactivosCount',
            'sinContratoCount',
            'step'
        ));
    }

    /**
     * Exportar empleados activos a Excel/CSV
     */
    public function export(Request $request)
    {
        return $this->exportarEmpleadosExcel($request);
    }

    public function exportarEmpleadosExcel(Request $request)
    {
        $usuarios = $this->construirConsultaEmpleados($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Empleados');

        $encabezados = [
            'Documento',
            'Nombre Completo',
            'Email',
            'Tipo Contrato',
            'Salario Base',
            'Estado',
            'Fecha Inicio',
            'Fecha Fin'
        ];

        $sheet->fromArray($encabezados, null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $fila = 2;
        foreach ($usuarios as $usuario) {
            $contrato = $usuario->contratos->first();
            $nombreCompleto = trim(
                $usuario->primer_nombre . ' ' .
                ($usuario->otros_nombres ? $usuario->otros_nombres . ' ' : '') .
                $usuario->primer_apellido . ' ' .
                ($usuario->segundo_apellido ?? '')
            );

            $sheet->setCellValue('A' . $fila, $usuario->doc);
            $sheet->setCellValue('B' . $fila, $nombreCompleto);
            $sheet->setCellValue('C' . $fila, $usuario->email ?? '');
            $sheet->setCellValue('D' . $fila, $contrato?->tipoContrato?->nombre ?? 'Sin contrato');
            $sheet->setCellValue('E' . $fila, (float) ($contrato?->salario_base ?? 0));
            $sheet->setCellValue('F' . $fila, $contrato ? ($contrato->activo ? 'Activo' : 'Inactivo') : 'Sin contrato');
            $sheet->setCellValue('G' . $fila, $contrato?->fecha_inicio ?? '');
            $sheet->setCellValue('H' . $fila, $contrato?->fecha_fin ?? '');
            $fila++;
        }

        foreach (range('A', 'H') as $columna) {
            $sheet->getColumnDimension($columna)->setAutoSize(true);
        }

        $sheet->getStyle('E2:E' . max(2, $fila - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        $writer = new Xlsx($spreadsheet);
        $filename = 'empleados-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportarEmpleadosPdf(Request $request)
    {
        $usuarios = $this->construirConsultaEmpleados($request)->get();

        $estado = $request->query('estado');
        $estadoTexto = match ($estado) {
            'activos' => 'Solo activos',
            'inactivos' => 'Solo inactivos',
            'sin_contrato' => 'Sin contrato',
            default => 'General (todos)',
        };

        $pdf = Pdf::loadView('empleados.reporte-pdf', [
            'usuarios' => $usuarios,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'filtroEstado' => $estadoTexto,
            'busqueda' => $request->query('search', 'Sin filtro'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reporte-empleados-' . now()->format('Ymd_His') . '.pdf');
    }
}
