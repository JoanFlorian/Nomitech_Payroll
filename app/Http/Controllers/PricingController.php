<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\View\View;

class PricingController extends Controller
{
    /**
     * Mostrar la página de inicio con planes de precios
     */
    public function index(): View
    {
        // Obtener planes ordenados por campo 'orden', con planes destacados primero
        $planes = Plan::orderBy('destacado', 'desc')
            ->orderBy('orden', 'asc')
            ->get();

        return view('index', compact('planes'));
    }
}
