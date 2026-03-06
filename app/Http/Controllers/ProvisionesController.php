<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProvisionesController extends Controller
{
    /**
     * Muestra la vista principal de Provisiones.
     */
    public function index()
    {
        return view('provisiones.index');
    }
}
