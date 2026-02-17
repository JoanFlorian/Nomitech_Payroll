<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Licencia;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\Empresa;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class facturacioncontroller extends Controller
{
    public function dashboard(Request $request)
    {
        $selectedYear = $request->query('year', 2026);
        $selectedDate = $request->query('date');

        // Base query for payments - Use 'paid' as found in DB
        $pagoQuery = Pago::where('estado_pago', 'paid');

        if ($selectedDate) {
            $pagoQuery->whereDate(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedDate);
            $licenciaQuery = Licencia::whereDate('created_at', $selectedDate);
        } else {
            $pagoQuery->whereYear(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedYear);
            $licenciaQuery = Licencia::whereYear('created_at', $selectedYear);
        }

        // Metrics
        $totalIncome = (float) $pagoQuery->sum('valor');

        // Dynamic Sales Target: Estimate based on current monthly average
        $currentMonthIncome = Pago::where('estado_pago', 'paid')
            ->whereMonth(DB::raw('COALESCE(fecha_pago, created_at)'), Carbon::now()->month)
            ->whereYear(DB::raw('COALESCE(fecha_pago, created_at)'), Carbon::now()->year)
            ->sum('valor');

        $daysElapsed = Carbon::now()->day;
        $daysInMonth = Carbon::now()->daysInMonth;
        $averageDaily = $daysElapsed > 0 ? ($currentMonthIncome / $daysElapsed) : 0;
        $salesTarget = $averageDaily * $daysInMonth;

        // Ensure a minimum target so gauge doesn't break if no sales
        if ($salesTarget <= 0)
            $salesTarget = 1750000;

        // Most sold license
        $topLicense = Licencia::with('plan')
            ->select('plan_id', DB::raw('count(*) as total'))
            ->when($selectedDate, fn($q) => $q->whereDate('created_at', $selectedDate))
            ->when(!$selectedDate, fn($q) => $q->whereYear('created_at', $selectedYear))
            ->groupBy('plan_id')
            ->orderByDesc('total')
            ->first();

        // Previous year income for trend comparison
        $prevYearIncome = Pago::where('estado_pago', 'paid')
            ->whereYear(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedYear - 1)
            ->sum('valor');

        $trend = 0;
        if ($prevYearIncome > 0) {
            $trend = (($totalIncome - $prevYearIncome) / $prevYearIncome) * 100;
        }

        // Chart Data
        if ($selectedDate) {
            $chartData = Pago::where('estado_pago', 'paid')
                ->whereDate(DB::raw('COALESCE(fecha_pago, created_at)'), $selectedDate)
                ->select(DB::raw('HOUR(COALESCE(fecha_pago, created_at)) as label'), DB::raw('SUM(valor) as total'))
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        } else {
            // Monthly data for the year
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
        $pago = Pago::with(['licencia.empresa', 'licencia.plan', 'empresa'])->findOrFail($pagoId);
        $empresa = $pago->empresa ?? $pago->licencia->empresa;
        $licencia = $pago->licencia;

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
            'subtotal' => number_format((float) $pago->valor, 2, '.', ','),
            'iva' => '0.00',
            'total' => number_format((float) $pago->valor, 2, '.', ','),
            'items' => [
                [
                    'concepto' => $licencia->plan->nombre ?? 'Plan de suscripción',
                    'descripcion' => $licencia->plan->descripcion ?? 'Acceso a plataforma Nomitech',
                    'cantidad' => 1,
                    'precio_unitario' => number_format((float) $pago->valor, 2, '.', ',')
                ]
            ]
        ]);
    }

    public function facturacion(Request $request)
    {
        $estadoFiltro = $request->query('estado');
        $metodoFiltro = $request->query('metodo');
        $search = $request->query('q');

        $totalTransacciones = Pago::where('estado_pago', 'paid')->sum('valor');
        $suscripcionesActivas = Licencia::where('fecha_fin', '>=', Carbon::now())->count();
        $pendientesPago = Pago::where('estado_pago', 'pending')->count();

        $query = Pago::with(['licencia.empresa', 'licencia.plan', 'empresa'])
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
                    });
            });
        }

        $transacciones = $query->paginate(9);
        $metodosDisponibles = Pago::distinct('proveedor_pago')
            ->whereNotNull('proveedor_pago')
            ->pluck('proveedor_pago')
            ->toArray();

        return view('superadmin.facturacion', [
            'totalTransacciones' => number_format((float) $totalTransacciones, 2, '.', ','),
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
            $pago = Pago::with(['licencia.empresa', 'licencia.plan', 'empresa'])->findOrFail($pagoId);
            $empresa = $pago->empresa ?? $pago->licencia->empresa;
            $licencia = $pago->licencia;
            $estadoTexto = $this->obtenerEstadoTexto($pago->estado_pago);

            $html = view('superadmin.pdf', compact('pago', 'empresa', 'licencia', 'estadoTexto'))->render();

            return response($html, 200)
                ->header('Content-Type', 'text/html; charset=utf-8')
                ->header('Content-Disposition', 'inline; filename="factura-' . $pago->id . '.html"');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al generar factura HTML: " . $e->getMessage());
            return back()->with('error', 'No se pudo generar la vista de la factura.');
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
            $selectedYear = $request->query('year', 2026);
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
            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al generar reporte PDF: " . $e->getMessage());
            return back()->with('error', 'Hubo un error al generar el reporte PDF. Por favor, intente nuevamente.');
        }
    }
}
