<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function create()
    {
        return view('superadmin.planes-create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'valor'       => 'required|numeric|min:0',
            'duracion'    => 'required|integer|min:1',
            'num_empl'    => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:500',
        ]);

        Plan::create($data);

        return redirect()
            ->route('superadmin.crear-planes')
            ->with('success', 'Plan creado correctamente.');
    }
}
