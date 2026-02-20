<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;

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

    public function store(StorePlanRequest $request)
    {
        $data = $request->validated();

        // Capitalizar el nombre
        $data['nombre'] = ucwords(strtolower($data['nombre']));

        // Convertir destacado a booleano si está presente, en caso contrario falso
        $data['destacado'] = $request->has('destacado');

        // Filtrar características vacías
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

    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $data = $request->validated();

        // Capitalizar el nombre
        $data['nombre'] = ucwords(strtolower($data['nombre']));

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
