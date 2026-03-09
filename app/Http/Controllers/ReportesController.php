<?php

namespace App\Http\Controllers;

use App\Models\TipoContrato;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportesController extends Controller
{
    public function index(Request $request)
    {
        return view('reportes.index', $this->buildReportData($request));
    }

    public function exportarPdf(Request $request)
    {
        $data = $this->buildReportData($request);
        $data['fechaGeneracion'] = now()->format('d/m/Y H:i');
        $data['tipoContratoNombre'] = $this->resolveTipoContratoNombre(
            $data['tiposContrato'],
            $data['tipoContratoSeleccionado']
        );

        $pdf = Pdf::loadView('reportes.reporte-pdf', $data)->setPaper('a4', 'landscape');

        return $pdf->download('reporte-nomina-' . now()->format('Ymd_His') . '.pdf');
    }

    public function exportarExcel(Request $request)
    {
        $data = $this->buildReportData($request);

        $resumen = $data['resumen'];
        $desglose = $data['desglose'];
        $evolucion = $data['evolucion'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Nómina');

        $sheet->setCellValue('A1', 'Reporte de Nómina');
        $sheet->setCellValue('A2', 'Generado: ' . now()->format('d/m/Y H:i'));
        $sheet->setCellValue('A3', 'Periodo: ' . ($data['periodoSeleccionado'] === 'all'
            ? 'Todos'
            : Carbon::createFromFormat('Y-m', $data['periodoSeleccionado'])->locale('es')->translatedFormat('F Y')));
        $sheet->setCellValue('A4', 'Tipo de contrato: ' . $this->resolveTipoContratoNombre(
            $data['tiposContrato'],
            $data['tipoContratoSeleccionado']
        ));

        $sheet->setCellValue('A6', 'Resumen de Nómina');
        $sheet->setCellValue('A7', 'Costo total de nómina');
        $sheet->setCellValue('A8', 'Empleados activos');
        $sheet->setCellValue('A9', 'Salario neto promedio');
        $sheet->setCellValue('A10', 'Aportes seguridad social');
        $sheet->setCellValue('A11', 'Total deducciones');

        $sheet->setCellValue('B7', (float) ($resumen->costo_total_nomina ?? 0));
        $sheet->setCellValue('B8', (int) ($resumen->empleados_activos ?? 0));
        $sheet->setCellValue('B9', (float) ($resumen->salario_neto_promedio ?? 0));
        $sheet->setCellValue('B10', (float) ($resumen->aportes_seguridad_social ?? 0));
        $sheet->setCellValue('B11', (float) ($resumen->total_deducciones ?? 0));

        $sheet->setCellValue('D6', 'Desglose de Nómina');
        $sheet->setCellValue('D7', 'Salarios');
        $sheet->setCellValue('D8', 'Bonificaciones');
        $sheet->setCellValue('D9', 'Prestaciones Sociales');
        $sheet->setCellValue('D10', 'Provisiones');

        $sheet->setCellValue('E7', (float) ($desglose['salarios'] ?? 0));
        $sheet->setCellValue('E8', (float) ($desglose['bonificaciones'] ?? 0));
        $sheet->setCellValue('E9', (float) ($desglose['prestaciones_sociales'] ?? 0));
        $sheet->setCellValue('E10', (float) ($desglose['provisiones'] ?? 0));

        $sheet->setCellValue('A13', 'Evolución de Nómina');
        $sheet->setCellValue('A14', 'Periodo');
        $sheet->setCellValue('B14', 'Total');

        $fila = 15;
        foreach ($evolucion as $item) {
            $sheet->setCellValue('A' . $fila, strtoupper((string) $item['label']));
            $sheet->setCellValue('B' . $fila, (float) $item['total']);
            $fila++;
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6:E6')->getFont()->setBold(true);
        $sheet->getStyle('A14:B14')->getFont()->setBold(true);

        $sheet->getStyle('B7:B11')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('E7:E10')->getNumberFormat()->setFormatCode('#,##0.00');
        if ($fila > 15) {
            $sheet->getStyle('B15:B' . ($fila - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        }

        foreach (['A', 'B', 'D', 'E'] as $columna) {
            $sheet->getColumnDimension($columna)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'reporte-nomina-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildReportData(Request $request): array
    {
        $empresaId = session('empresa_id');
        $periodoSeleccionado = (string) $request->query('periodo', 'all');
        $tipoContratoSeleccionado = (string) $request->query('tipo_contrato', 'all');

        $periodosDisponibles = DB::table('salario as s')
            ->join('contrato as c', 'c.id_contrato', '=', 's.id_contrato')
            ->when($empresaId, fn($q) => $q->where('c.id_empresa', $empresaId))
            ->whereNotNull('s.fecha_pago')
            ->selectRaw("DATE_FORMAT(s.fecha_pago, '%Y-%m') as periodo")
            ->distinct()
            ->orderByDesc('periodo')
            ->pluck('periodo');

        $tiposContrato = TipoContrato::query()
            ->orderBy('nombre')
            ->get(['id_tipo_contrato', 'nombre']);

        $devengosExpr = 'COALESCE(s.total_devengado, (
            c.salario_base
            + s.auxilio_transporte
            + COALESCE(s.valor_horas_extras_recargos, s.horas_extra, 0)
            + s.bonificaciones
            + s.comisiones
            + s.otros_devengos
        ))';

        $deduccionesExpr = '(
            s.eps + s.afp + s.aporte_fp
            + s.retencion_fuente + s.embargo_fiscal + s.pension_voluntaria
        )';

        $baseQuery = DB::table('salario as s')
            ->join('contrato as c', 'c.id_contrato', '=', 's.id_contrato')
            ->leftJoin('tipo_contrato as tc', 'tc.id_tipo_contrato', '=', 'c.id_tipo_contrato')
            ->when($empresaId, fn($q) => $q->where('c.id_empresa', $empresaId))
            ->when(
                $periodoSeleccionado !== 'all',
                fn($q) => $q->whereRaw("DATE_FORMAT(s.fecha_pago, '%Y-%m') = ?", [$periodoSeleccionado])
            )
            ->when(
                $tipoContratoSeleccionado !== 'all',
                fn($q) => $q->where('c.id_tipo_contrato', (int) $tipoContratoSeleccionado)
            );

        // Debe contar todos los contratos activos vinculados a la empresa,
        // incluso si no tienen liquidaciones en salario para el periodo.
        $empleadosActivos = DB::table('contrato as c')
            ->when($empresaId, fn($q) => $q->where('c.id_empresa', $empresaId))
            ->when(
                $tipoContratoSeleccionado !== 'all',
                fn($q) => $q->where('c.id_tipo_contrato', (int) $tipoContratoSeleccionado)
            )
            ->where('c.activo', 1)
            ->distinct('c.doc')
            ->count('c.doc');

        $resumen = (clone $baseQuery)
            ->selectRaw("COALESCE(SUM({$devengosExpr}), 0) as costo_total_nomina")
            ->selectRaw("COALESCE(AVG({$devengosExpr} - {$deduccionesExpr}), 0) as salario_neto_promedio")
            ->selectRaw('COALESCE(SUM(s.seguridad_social + s.aporte_fp), 0) as aportes_seguridad_social')
            ->selectRaw("COALESCE(SUM({$deduccionesExpr}), 0) as total_deducciones")
            ->first();

        $resumen->empleados_activos = (int) $empleadosActivos;

        $desgloseNomina = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(c.salario_base), 0) as salarios')
            ->selectRaw('COALESCE(SUM(s.bonificaciones + s.comisiones + s.otros_devengos), 0) as bonificaciones')
            ->selectRaw('COALESCE(SUM(s.auxilio_transporte), 0) as prestaciones_sociales')
            ->first();

        $provisiones = DB::table('provision as p')
            ->join('contrato as c', 'c.id_contrato', '=', 'p.id_contrato')
            ->join('periodo_liquidacion as pl', 'pl.id_periodo', '=', 'p.id_periodo')
            ->when($empresaId, fn($q) => $q->where('c.id_empresa', $empresaId))
            ->when(
                $periodoSeleccionado !== 'all',
                fn($q) => $q->whereRaw("DATE_FORMAT(pl.fecha_inicio, '%Y-%m') = ?", [$periodoSeleccionado])
            )
            ->when(
                $tipoContratoSeleccionado !== 'all',
                fn($q) => $q->where('c.id_tipo_contrato', (int) $tipoContratoSeleccionado)
            )
            ->selectRaw('COALESCE(SUM(p.cesantias + p.intereses_cesantias + p.prima), 0) as total')
            ->value('total');

        $evolucion = (clone $baseQuery)
            ->whereNotNull('s.fecha_pago')
            ->selectRaw("DATE_FORMAT(s.fecha_pago, '%Y-%m') as periodo")
            ->selectRaw("COALESCE(SUM({$devengosExpr}), 0) as total")
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get()
            ->map(function ($item) {
                return [
                    'periodo' => $item->periodo,
                    'label' => Carbon::createFromFormat('Y-m', $item->periodo)->locale('es')->translatedFormat('M y'),
                    'total' => (float) $item->total,
                ];
            });

        $evolucionAnual = (clone $baseQuery)
            ->whereNotNull('s.fecha_pago')
            ->selectRaw("DATE_FORMAT(s.fecha_pago, '%Y') as anio")
            ->selectRaw("COALESCE(SUM({$devengosExpr}), 0) as total")
            ->groupBy('anio')
            ->orderBy('anio')
            ->get()
            ->map(function ($item) {
                return [
                    'anio' => $item->anio,
                    'label' => (string) $item->anio,
                    'total' => (float) $item->total,
                ];
            });

        return [
            'periodosDisponibles' => $periodosDisponibles,
            'tiposContrato' => $tiposContrato,
            'periodoSeleccionado' => $periodoSeleccionado,
            'tipoContratoSeleccionado' => $tipoContratoSeleccionado,
            'resumen' => $resumen,
            'desglose' => [
                'salarios' => (float) ($desgloseNomina->salarios ?? 0),
                'bonificaciones' => (float) ($desgloseNomina->bonificaciones ?? 0),
                'prestaciones_sociales' => (float) ($desgloseNomina->prestaciones_sociales ?? 0),
                'provisiones' => (float) ($provisiones ?? 0),
            ],
            'evolucion' => $evolucion,
            'evolucionAnual' => $evolucionAnual,
        ];
    }

    private function resolveTipoContratoNombre($tiposContrato, string $tipoContratoSeleccionado): string
    {
        if ($tipoContratoSeleccionado === 'all') {
            return 'Todos';
        }

        $tipo = $tiposContrato->firstWhere('id_tipo_contrato', (int) $tipoContratoSeleccionado);

        return $tipo->nombre ?? 'No definido';
    }
}
