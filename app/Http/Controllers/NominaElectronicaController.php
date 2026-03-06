<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NominaElectronicaController extends Controller
{
    /**
     * Muestra la vista principal de Nómina Electrónica.
     */
    public function index()
    {
        return view('nomina-electronica.index');
    }
}
