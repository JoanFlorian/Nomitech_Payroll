<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Licencia;
use App\Http\Controllers\Concerns\HandlesExportResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\Empresa;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class facturacioncontroller extends Controller
{
    use HandlesExportResponses;

    private function construirConsultaFacturacion(Request $request)
    {
        $estadoFiltro = $request->query('estado');
        $metodoFiltro = $request->query('metodo');
        $search = $request->query('q');

        $query = Pago::with(['licencia.empresa', 'licencia.plan', 'plan', 'empresa'])
            ->orderBy('created_at', 'desc');

        if ($estadoFiltro && $estadoFiltro !== 'Todos') {
            $query->where('estado_pago', strtolower($estadoFiltro));
        }

        if ($metodoFiltro && $metodoFiltro !== 'Todos') {
            $query->where('proveedor_pago', strtolower($metodoFiltro));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                    ->orWhere('proveedor_pago', 'like', "%{$search}%")
                    ->orWhereHas('licencia.empresa', function ($q2) use ($search) {
                        $q2->where('razon_social', 'like', "%{$search}%");
                    })
                    ->orWhereHas('empresa', function ($q2) use ($search) {
                        $q2->where('razon_social', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    public function dashboard(Request $request)
    {
        $selectedYear = $request->query('year', date('Y'));
        $selectedDate = $request->query('date');

        // Consulta base para pagos - Usar 'paid' tal como se encuentra en BD
        $pagoQuery = Pago::where('estado_pago', 'paid');

        if ($selectedDate) {
            $pagoQuery->whereDate(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedDate);
            $licenciaQuery = Licencia::whereDate('created_at', $selectedDate);
        } else {
            $pagoQuery->whereYear(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedYear);
            $licenciaQuery = Licencia::whereYear('created_at', $selectedYear);
        }

        // Métricas
        $totalIncome = (float) $pagoQuery->sum('valor');

        // Objetivo de ventas dinámico: Estimar basado en el promedio mensual actual
        $currentMonthIncome = Pago::where('estado_pago', 'paid')
            ->whereMonth(DB::raw('COALESCE(fecha_pago, created_at)'), Carbon::now()->month)
            ->whereYear(DB::raw('COALESCE(fecha_pago, created_at)'), Carbon::now()->year)
            ->sum('valor');

        $daysElapsed = Carbon::now()->day;
        $daysInMonth = Carbon::now()->daysInMonth;
        $averageDaily = $daysElapsed > 0 ? ($currentMonthIncome / $daysElapsed) : 0;
        $salesTarget = $averageDaily * $daysInMonth;

        // Asegurar un objetivo mínimo para que el indicador no se rompa si no hay ventas
        if ($salesTarget <= 0)
            $salesTarget = 1750000;

        // Licencia más vendida
        $topLicense = Licencia::with('plan')
            ->select('plan_id', DB::raw('count(*) as total'))
            ->when($selectedDate, fn($q) => $q->whereDate('created_at', $selectedDate))
            ->when(!$selectedDate, fn($q) => $q->whereYear('created_at', $selectedYear))
            ->groupBy('plan_id')
            ->orderByDesc('total')
            ->first();

        // Ingresos del año anterior para comparación de tendencias
        $prevYearIncome = Pago::where('estado_pago', 'paid')
            ->whereYear(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedYear - 1)
            ->sum('valor');

        $trend = 0;
        if ($prevYearIncome > 0) {
            $trend = (($totalIncome - $prevYearIncome) / $prevYearIncome) * 100;
        }

        // Datos del gráfico
        if ($selectedDate) {
            $chartData = Pago::where('estado_pago', 'paid')
                ->whereDate(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedDate)
                ->select(DB::raw('HOUR(COALESCE(fecha_pago, created_at)) as label'), DB::raw('SUM(valor) as total'))
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        } else {
            // Datos mensuales del año
            $chartData = Pago::where('estado_pago', 'paid')
                ->whereYear(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedYear)
                ->select(DB::raw('MONTH(COALESCE(fecha_pago, created_at)) as label'), DB::raw('SUM(valor) as total'))
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        }

        return view('superadmin.index', [
            'totalIncome' => number_format((float) $totalIncome, 0, ',', '.'),
            'salesTarget' => number_format((float) $salesTarget, 0, ',', '.'),
            'salesTargetValue' => $salesTarget,
            'currentIncomeValue' => (float) $totalIncome,
            'topLicenseName' => $topLicense->plan->nombre ?? 'N/A',
            'topLicenseCount' => $topLicense->total ?? 0,
            'trend' => number_format((float) $trend, 1, '.', ','),
            'trendUp' => $trend >= 0,
            'selectedYear' => $selectedYear,
            'selectedDate' => $selectedDate,
            'chartData' => $chartData,
            'availableYears' => range(2026, date('Y') + 1)
        ]);
    }


    public function getFactura($pagoId)
    {
        // Cargar plan directo desde pago (trazabilidad) y desde licencia (fallback datos históricos)
        $pago = Pago::with(['licencia.empresa', 'licencia.plan', 'plan', 'empresa'])->findOrFail($pagoId);
        $empresa = $pago->empresa ?? $pago->licencia->empresa;
        $licencia = $pago->licencia;

        // Usar plan directo del pago si existe (nuevo comportamiento),
        // si no, caer al plan de la licencia (registros históricos anteriores al fix)
        $plan = $pago->plan ?? ($licencia ? $licencia->plan : null);

        // Los valores se retornan como float puro para que parseFloat() en JavaScript funcione.
        // El formateo a moneda colombiana lo realiza toLocaleString('es-CO') en el frontend.
        $valorFloat = (float) $pago->valor;

        return response()->json([
            'numero_factura' => 'FAC-' . ($pago->fecha_pago ? $pago->fecha_pago->format('Y') : $pago->created_at->format('Y')) . '-' .
                str_pad($pago->id, 6, '0', STR_PAD_LEFT),
            'fecha' => $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : $pago->created_at->format('d/m/Y'),
            'estado' => $this->obtenerEstadoTexto($pago->estado_pago),
            'empresa_emisora' => [
                'nombre' => 'Nomitech',
                'razon_social' => 'Nomitech SAS',
                'nit' => '9012345678',
                'direccion' => 'Cra 10 #45-67, Bogotá',
                'email' => 'facturacion@nomitech.com'
            ],
            'cliente' => [
                'razon_social' => $empresa->razon_social ?? 'Sin especificar',
                'nit' => $empresa->nit ?? 'No especificado',
                'direccion' => $empresa->direccion ?? 'No especificado'
            ],
            'metodo_pago' => ucfirst($pago->proveedor_pago ?? 'No especificado'),
            'subtotal' => $valorFloat,
            'iva' => 0,
            'total' => $valorFloat,
            'items' => [
                [
                    'concepto' => $plan->nombre ?? 'Plan de suscripción',
                    'descripcion' => $plan->descripcion ?? 'Acceso a plataforma Nomitech',
                    'cantidad' => 1,
                    'precio_unitario' => $valorFloat
                ]
            ]
        ]);
    }

    public function facturacion(Request $request)
    {
        $estadoFiltro = $request->query('estado');
        $metodoFiltro = $request->query('metodo');

        $totalTransacciones = Pago::where('estado_pago', 'paid')->sum('valor');
        $suscripcionesActivas = Licencia::where('fecha_fin', '>=', Carbon::now())->count();
        $pendientesPago = Pago::where('estado_pago', 'pending')->count();

        $query = $this->construirConsultaFacturacion($request);
        $transacciones = $query->paginate(9);
        $metodosDisponibles = Pago::distinct('proveedor_pago')
            ->whereNotNull('proveedor_pago')
            ->pluck('proveedor_pago')
            ->toArray();

        return view('superadmin.facturacion', [
            'totalTransacciones' => number_format((float) $totalTransacciones, 0, ',', '.'),
            'suscripcionesActivas' => $suscripcionesActivas,
            'pendientesPago' => $pendientesPago,
            'transacciones' => $transacciones,
            'metodosDisponibles' => $metodosDisponibles,
            'estadoSeleccionado' => $estadoFiltro ?? 'Todos',
            'metodoSeleccionado' => $metodoFiltro ?? 'Todos'
        ]);
    }

    public function descargarFacturaPdf($pagoId)
    {
        try {
            // Cargar plan directo del pago y de la licencia (fallback datos históricos)
            $pago = Pago::with(['licencia.empresa', 'licencia.plan', 'plan', 'empresa'])->findOrFail($pagoId);
            $empresa = $pago->empresa ?? $pago->licencia->empresa;
            $licencia = $pago->licencia;
            $estadoTexto = $this->obtenerEstadoTexto($pago->estado_pago);
            // Plan desde el pago (nuevo) o desde la licencia (histórico)
            $planParaPdf = $pago->plan ?? ($licencia ? $licencia->plan : null);

            $pdf = Pdf::loadView('superadmin.pdf', compact('pago', 'empresa', 'licencia', 'estadoTexto', 'planParaPdf'));

            return $this->downloadPdfResponse($pdf, 'factura-' . $pago->id . '.pdf');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al generar factura PDF: " . $e->getMessage());
            return back()->with('error', 'No se pudo generar el PDF de la factura.');
        }
    }

    private function obtenerEstadoTexto($estado)
    {
        return match ($estado) {
            'paid' => 'Pagado',
            'pending' => 'Pendiente',
            'failed' => 'Fallido',
            default => ucfirst($estado)
        };
    }
    public function descargarReporte(Request $request)
    {
        try {
            $selectedYear = $request->query('year', date('Y'));
            $selectedDate = $request->query('date');

            // Reuse dashboard logic for data fetching
            $pagoQuery = Pago::where('estado_pago', 'paid');

            if ($selectedDate) {
                $pagoQuery->whereDate(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedDate);
                $titulo = "Reporte Diario - " . Carbon::parse($selectedDate)->format('d/m/Y');
            } else {
                $pagoQuery->whereYear(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedYear);
                $titulo = "Reporte Anual - " . $selectedYear;
            }

            $totalIncome = (float) $pagoQuery->sum('valor');
            $pagos = $pagoQuery->with('licencia.plan')->orderBy('created_at', 'desc')->get();

            $topLicense = Licencia::with('plan')
                ->select('plan_id', DB::raw('count(*) as total'))
                ->when($selectedDate, fn($q) => $q->whereDate('created_at', $selectedDate))
                ->when(!$selectedDate, fn($q) => $q->whereYear('created_at', $selectedYear))
                ->groupBy('plan_id')
                ->orderByDesc('total')
                ->first();

            $data = [
                'titulo' => $titulo,
                'selectedDate' => $selectedDate,
                'selectedYear' => $selectedYear,
                'totalIncome' => number_format($totalIncome, 0, ',', '.'),
                'pagos' => $pagos,
                'topLicenseName' => $topLicense->plan->nombre ?? 'N/A',
                'topLicenseCount' => $topLicense->total ?? 0,
                'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i')
            ];

            $pdf = Pdf::loadView('superadmin.reporte-pdf', $data);

            $filename = $selectedDate ? "reporte-{$selectedDate}.pdf" : "reporte-{$selectedYear}.pdf";
            return $this->downloadPdfResponse($pdf, $filename);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al generar reporte PDF: " . $e->getMessage());
            return back()->with('error', 'Hubo un error al generar el reporte PDF. Por favor, intente nuevamente.');
        }
    }

    public function exportarFacturacionPdf(Request $request)
    {
        try {
            $pagos = $this->construirConsultaFacturacion($request)->get();

            if ($pagos->isEmpty()) {
                return redirect()->route('superadmin.facturacion', $request->only(['q', 'estado', 'metodo']))
                    ->with('warning', 'No hay datos para exportar con los filtros seleccionados.');
            }

            $estadoSeleccionado = $request->query('estado', 'Todos');
            $estadoTexto = match ($estadoSeleccionado) {
                'paid' => 'Solo pagados',
                'pending' => 'Pendientes',
                'failed' => 'Fallidos',
                default => 'Todos',
            };

            $busquedaTexto = trim((string) $request->query('q', ''));
            if ($busquedaTexto === '') {
                $busquedaTexto = $estadoSeleccionado === 'paid' ? 'Solo pagados' : 'Sin filtro';
            }

            $data = [
                'pagos' => $pagos,
                'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i'),
                'estadoSeleccionado' => $estadoTexto,
                'metodoSeleccionado' => $request->query('metodo', 'Todos'),
                'busqueda' => $busquedaTexto
            ];

            $pdf = Pdf::loadView('superadmin.facturacion-reporte-pdf', $data)->setPaper('a4', 'landscape');
            $nombreArchivo = $this->buildFacturacionExportFileName($request, 'pdf');

            return $this->downloadPdfResponse($pdf, $nombreArchivo);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al generar reporte de facturación en PDF: " . $e->getMessage());
            return back()->with('error', 'No fue posible generar el reporte PDF de facturación.');
        }
    }

    public function exportarFacturacionExcel(Request $request)
    {
        try {
            $pagos = $this->construirConsultaFacturacion($request)->get();

            if ($pagos->isEmpty()) {
                return redirect()->route('superadmin.facturacion', $request->only(['q', 'estado', 'metodo']))
                    ->with('warning', 'No hay datos para exportar con los filtros seleccionados.');
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Facturación Empresas');

            $encabezados = [
                'Empresa',
                'NIT',
                'Referencia',
                'Fecha',
                'Plan',
                'Método',
                'Estado',
                'Vigencia',
                'Valor'
            ];

            $sheet->fromArray($encabezados, null, 'A1');

            $fila = 2;
            foreach ($pagos as $pago) {
                $licencia = $pago->licencia;
                $empresa = $pago->empresa ?? ($licencia ? $licencia->empresa : null);
                $plan = $pago->plan ?? ($licencia ? $licencia->plan : null);

                $fechaFin = $licencia?->fecha_fin ? Carbon::parse($licencia->fecha_fin) : null;
                $diasRestantes = $fechaFin ? Carbon::now()->diffInDays($fechaFin, false) : null;
                $vigencia = is_null($diasRestantes) ? 'Sin vigencia' : ($diasRestantes < 0 ? 'Vencida' : abs((int) floor($diasRestantes)) . ' días');

                $estadoTexto = match ($pago->estado_pago) {
                    'paid' => 'Pagado',
                    'pending' => 'Pendiente',
                    'failed' => 'Fallido',
                    default => $pago->estado_pago,
                };

                $fechaPago = $pago->fecha_pago
                    ? Carbon::parse($pago->fecha_pago)->format('d/m/Y')
                    : $pago->created_at->format('d/m/Y');

                $sheet->setCellValue('A' . $fila, $empresa->razon_social ?? 'Sin especificar');
                $sheet->setCellValue('B' . $fila, $empresa->nit ?? 'No especificado');
                $sheet->setCellValue('C' . $fila, $pago->referencia ?? 'FAC-' . $pago->id);
                $sheet->setCellValue('D' . $fila, $fechaPago);
                $sheet->setCellValue('E' . $fila, $plan->nombre ?? 'Plan de suscripción');
                $sheet->setCellValue('F' . $fila, $pago->proveedor_pago ?? '—');
                $sheet->setCellValue('G' . $fila, $estadoTexto);
                $sheet->setCellValue('H' . $fila, $vigencia);
                $sheet->setCellValue('I' . $fila, (float) $pago->valor);
                $fila++;
            }

            foreach (range('A', 'I') as $columna) {
                $sheet->getColumnDimension($columna)->setAutoSize(true);
            }

            $sheet->getStyle('A1:I1')->getFont()->setBold(true);
            $sheet->getStyle('I2:I' . max(2, $fila - 1))->getNumberFormat()->setFormatCode('#,##0.00');

            $nombreArchivo = $this->buildFacturacionExportFileName($request, 'xlsx');

            return $this->streamSpreadsheetDownload($spreadsheet, $nombreArchivo);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al generar reporte de facturación en Excel: " . $e->getMessage());
            return back()->with('error', 'No fue posible generar el reporte Excel de facturación.');
        }
    }

    private function buildFacturacionExportFileName(Request $request, string $extension): string
    {
        $scope = match ((string) $request->query('estado', 'Todos')) {
            'paid' => 'transacciones_pagadas',
            'pending' => 'transacciones_pendientes',
            'failed' => 'transacciones_fallidas',
            default => 'transacciones_general',
        };

        $metodo = trim((string) $request->query('metodo', ''));
        if ($metodo !== '' && strcasecmp($metodo, 'Todos') !== 0) {
            $scope .= '_' . Str::slug($metodo, '_');
        }

        if (trim((string) $request->query('q', '')) !== '') {
            $scope .= '_filtrado';
        }

        return $scope . '_' . now()->format('Ymd_His') . '.' . $extension;
    }
}
