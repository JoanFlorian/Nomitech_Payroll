<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $planes = Plan::orderBy('orden')->get();
        return view('superadmin.planes.index', compact('planes'));
    }

    public function create()
    {
        return view('superadmin.planes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'duracion' => 'required|integer|min:1',
            'num_empl' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:500',
            'destacado' => 'nullable|boolean',
            'features' => 'nullable|array|max:4',
            'features.*' => 'nullable|string|max:255',
        ]);

        // Convert destacados to boolean if present, otherwise false
        $data['destacado'] = $request->has('destacado');

        // Filter out empty features
        if (isset($data['features'])) {
            $data['features'] = array_values(array_filter($data['features']));
        }

        Plan::create($data);

        return redirect()
            ->route('superadmin.planes.index')
            ->with('success', 'Plan creado correctamente.');
    }

    public function edit(Plan $plan)
    {
        return view('superadmin.planes.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'duracion' => 'required|integer|min:1',
            'num_empl' => 'required|integer|min:1',
            'descripcion' => 'nullable|string|max:500',
            'destacado' => 'nullable|boolean',
            'features' => 'nullable|array|max:4',
            'features.*' => 'nullable|string|max:255',
        ]);

        $data['destacado'] = $request->has('destacado');

        if (isset($data['features'])) {
            $data['features'] = array_values(array_filter($data['features']));
        } else {
            $data['features'] = [];
        }

        $plan->update($data);

        return redirect()
            ->route('superadmin.planes.index')
            ->with('success', 'Plan actualizado correctamente.');
    }
}

