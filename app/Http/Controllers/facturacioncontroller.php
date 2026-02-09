<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Licencia;
use App\Models\Plan;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Http\Request;

class facturacioncontroller extends Controller
{
    public function index(Request $request)
    {
        // Filtros
        $estadoFiltro = $request->query('estado');
        $metodoFiltro = $request->query('metodo');
        $search = $request->query('q');

        // TARJETA 1: Total de transacciones ingresos con estado_pago = succeeded
        $totalTransacciones = Pago::where('estado_pago', 'succeeded')
            ->sum('valor');

        // TARJETA 2: Suscripciones activas licencias con fecha_fin >= hoy
        $suscripcionesActivas = Licencia::where('fecha_fin', '>=', Carbon::now())
            ->count();

        // TARJETA 3: Pendientes de pago (pagos con estado_pago = 'pending')
        $pendientesPago = Pago::where('estado_pago', 'pending')->count();

        // LISTADO DE TRANSACCIONES CON FILTROS
        $query = Pago::with(['licencia' => function ($q) {
            $q->with(['empresa', 'plan']);
        }, 'empresa'])
            ->orderBy('created_at', 'desc');

        // Filtrar por estado de pago
        if ($estadoFiltro && $estadoFiltro !== 'Todos') {
            $query->where('estado_pago', strtolower($estadoFiltro));
        }

        // Filtrar por proveedor de pago
        if ($metodoFiltro && $metodoFiltro !== 'Todos') {
            $query->where('proveedor_pago', strtolower($metodoFiltro));
        }

        // Filtrar por texto de búsqueda (referencia, proveedor o nombre de empresa)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                ->orWhere('proveedor_pago', 'like', "%{$search}%")
                ->orWhereHas('licencia.empresa', function ($q2) use ($search) {
                    $q2->where('razon_social', 'like', "%{$search}%");
                });
            });
        }

        // Paginar resultados
        $transacciones = $query->paginate(15);

        // Obtener proveedores disponibles
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

    private function getColorEstado($estado)
    {
        return match($estado) {
            'succeeded' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            default => 'gray'
        };
    }
}
