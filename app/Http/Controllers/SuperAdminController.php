<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function empresas(Request $request)
    {
        $empresas = Empresa::with(['licencia.plan'])->get();

        $empresas = $empresas->map(function ($empresa) {
            $licencia = $empresa->licencia;

            if (!$licencia || !$licencia->fecha_fin) {
                $empresa->estado = 'prueba';
            } else {
                $hoy = Carbon::now();
                $fin = Carbon::parse($licencia->fecha_fin);

                if ($fin->isPast()) {
                    $empresa->estado = 'vencida';
                } elseif ($fin->diffInDays($hoy) <= 10) {
                    $empresa->estado = 'por_vencer';
                } else {
                    $empresa->estado = 'activa';
                }
            }

            return $empresa;
        });

        // Filtro por estado
        if ($request->estado && $request->estado != 'todas') {
            $empresas = $empresas->filter(fn($e) => $e->estado == $request->estado);
        }

        //  Buscador
        if ($request->buscar) {
            $empresas = $empresas->filter(function ($e) use ($request) {
                return str_contains(strtolower($e->razon_social), strtolower($request->buscar))
                    || str_contains(strtolower($e->nit), strtolower($request->buscar));
            });
        }

        return view('superadmin.empresas', compact('empresas'));
    }
}
