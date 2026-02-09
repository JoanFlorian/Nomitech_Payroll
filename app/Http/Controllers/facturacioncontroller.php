<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Licencia;
use Carbon\Carbon;
use Illuminate\Http\Request;

class facturacioncontroller extends Controller
{
    /**
     * Mostrar dashboard de superadmin con listado de transacciones
     */
    public function index(Request $request)
    {
        // Obtener filtros de la solicitud
        $estadoFiltro = $request->query('estado');
        $metodoFiltro = $request->query('metodo');
        $search = $request->query('q');

        // Métricas del dashboard
        $totalTransacciones = Pago::where('estado_pago', 'succeeded')
            ->sum('valor');

        $suscripcionesActivas = Licencia::where('fecha_fin', '>=', Carbon::now())
            ->count();

        $pendientesPago = Pago::where('estado_pago', 'pending')->count();

        // Construir query de transacciones
        $query = Pago::with(['licencia' => function ($q) {
            $q->with(['empresa', 'plan']);
        }, 'empresa'])
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
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

        $transacciones = $query->paginate(15);

        // Obtener opciones para los filtros
        $metodosDisponibles = Pago::distinct('proveedor_pago')
            ->whereNotNull('proveedor_pago')
            ->pluck('proveedor_pago')
            ->toArray();

        $estadosDisponibles = ['succeeded', 'pending', 'failed'];

        return view('superadmin.index', [
            'totalTransacciones' => number_format($totalTransacciones, 2, '.', ','),
            'suscripcionesActivas' => $suscripcionesActivas,
            'pendientesPago' => $pendientesPago,
            'transacciones' => $transacciones,
            'metodosDisponibles' => $metodosDisponibles,
            'estadosDisponibles' => $estadosDisponibles,
            'estadoSeleccionado' => $estadoFiltro ?? 'Todos',
            'metodoSeleccionado' => $metodoFiltro ?? 'Todos'
        ]);
    }

    /**
     * Obtener datos de factura en formato JSON
     */
    public function getFactura($pagoId)
    {
        $pago = Pago::with(['licencia.empresa', 'licencia.plan', 'empresa'])->findOrFail($pagoId);

        $empresa = $pago->empresa ?? $pago->licencia->empresa;
        $licencia = $pago->licencia;

        $estadoTexto = $this->obtenerEstadoTexto($pago->estado_pago);

        return response()->json([
            'numero_factura' => $pago->referencia ?? 'FAC-' . $pago->id,
            'fecha' => $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : $pago->created_at->format('d/m/Y'),
            'estado' => $estadoTexto,
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
            'subtotal' => number_format($pago->valor, 2, '.', ','),
            'iva' => '0.00',
            'total' => number_format($pago->valor, 2, '.', ','),
            'items' => [
                [
                    'concepto' => $licencia->plan->nombre ?? 'Plan de suscripción',
                    'descripcion' => $licencia->plan->descripcion ?? 'Acceso a plataforma Nomitech',
                    'cantidad' => 1,
                    'precio_unitario' => number_format($pago->valor, 2, '.', ',')
                ]
            ]
        ]);
    }

    /**
     * Descargar factura en PDF (o HTML si DomPDF no está disponible)
     */
    public function descargarFacturaPdf($pagoId)
    {
        $pago = Pago::with(['licencia.empresa', 'licencia.plan', 'empresa'])->findOrFail($pagoId);

        $empresa = $pago->empresa ?? $pago->licencia->empresa;
        $licencia = $pago->licencia;
        $estadoTexto = $this->obtenerEstadoTexto($pago->estado_pago);

        // Renderizar vista Blade
        $html = view('superadmin.pdf', compact('pago', 'empresa', 'licencia', 'estadoTexto'))->render();

        // Fallback: devolver HTML para imprimir desde el navegador
        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="factura-' . $pago->id . '.html"');
    }

    /**
     * Obtener texto descriptivo del estado de pago
     */
    private function obtenerEstadoTexto($estado)
    {
        return match($estado) {
            'succeeded' => 'Pagado',
            'pending' => 'Pendiente',
            'failed' => 'Fallido',
            default => ucfirst($estado)
        };
    }
}
