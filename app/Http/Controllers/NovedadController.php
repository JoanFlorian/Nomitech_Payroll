<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNovedadEmpleadoRequest;
use App\Http\Requests\UpdateNovedadEmpleadoRequest;
use App\Models\Novedad;
use App\Models\Salario;
use App\Models\TipoNovedad;
use App\Models\Usuario;
use App\Services\CalculoNovedadService;

class NovedadController extends Controller
{
    public function __construct(private readonly CalculoNovedadService $calculoNovedadService)
    {
    }

    public function index()
    {
        $empleados = Usuario::query()
            ->select('doc', 'primer_nombre', 'otros_nombres', 'primer_apellido', 'segundo_apellido')
            ->whereHas('contratos.salarios')
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->limit(800)
            ->get();

        $docs = $empleados->pluck('doc')->filter()->values();

        $salarios = Salario::query()
            ->join('contrato', 'contrato.id_contrato', '=', 'salario.id_contrato')
            ->whereIn('contrato.doc', $docs)
            ->select('contrato.doc', 'contrato.salario_base as contrato_salario_base', 'salario.id_salario')
            ->orderByDesc('salario.id_salario')
            ->get()
            ->groupBy('doc')
            ->map(fn ($rows) => (float) ($rows->first()->contrato_salario_base ?? 0));

        $novedades = Novedad::query()
            ->with(['tipoNovedad', 'salario.contrato.usuario'])
            ->orderByDesc('id_novedad')
            ->get();

        $empleadosBusqueda = $empleados
            ->map(function (Usuario $empleado) use ($salarios) {
                $nombres = trim(implode(' ', array_filter([
                    $empleado->primer_nombre,
                    $empleado->otros_nombres,
                ])));

                $apellidos = trim(implode(' ', array_filter([
                    $empleado->primer_apellido,
                    $empleado->segundo_apellido,
                ])));

                return [
                    'doc' => $empleado->doc,
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'nombre_completo' => trim($nombres . ' ' . $apellidos),
                    'salario_base' => (float) ($salarios[$empleado->doc] ?? 0),
                ];
            })
            ->values();

        return view('novedades.index', [
            'novedades' => $novedades,
            'empleadosBusqueda' => $empleadosBusqueda,
        ]);
    }

    public function store(StoreNovedadEmpleadoRequest $request)
    {
        $data = $request->validated();
        $salario = $this->calculoNovedadService->obtenerSalarioEmpleado((string) $data['empleado_id']);

        if (!$salario) {
            return back()
                ->withErrors(['empleado_id' => 'El empleado seleccionado no tiene una nómina registrada para asociar la novedad.'])
                ->withInput()
                ->with('open_novedad_modal', true);
        }

        $tipoNombre = ucfirst((string) $data['tipo_novedad']);
        $tipoNovedad = TipoNovedad::firstOrCreate(['nombre' => $tipoNombre]);

        $salarioBase = (float) ($salario->contrato_salario_base ?? optional($salario->contrato)->salario_base ?? 0);
        $valorCalculado = $this->calculoNovedadService->calcularValor($data, $salarioBase);
        $dias = (float) ($data['dias'] ?? 0);
        $horas = (float) ($data['horas'] ?? 0);

        Novedad::create([
            'id_tipo_novedad' => $tipoNovedad->id_tipo_novedad,
            'id_salario' => $salario->id_salario,
            'empleado_id' => $data['empleado_id'],
            'tipo_novedad_nombre' => $tipoNombre,
            'fecha' => $data['fecha_inicio'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'unidad_cantidad' => $data['unidad_cantidad'],
            'dias' => $dias,
            'horas' => $horas,
            'cantidad' => $data['unidad_cantidad'] === 'horas' ? $horas : $dias,
            'es_remunerado' => (bool) ($data['es_remunerado'] ?? false),
            'salario_base' => $salarioBase,
            'valor_calculado' => $valorCalculado,
            'valor_novedad' => $valorCalculado,
            'pago' => $valorCalculado,
            'pago_manual' => $data['pago_manual'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        return redirect()->route('novedades.index')->with('success', 'La novedad se registró correctamente.');
    }

    public function update(UpdateNovedadEmpleadoRequest $request, int $id_novedad)
    {
        $novedad = Novedad::query()->findOrFail($id_novedad);
        $data = $request->validated();

        $salario = $this->calculoNovedadService->obtenerSalarioEmpleado((string) $data['empleado_id']);
        if (!$salario) {
            return back()
                ->withErrors(['empleado_id' => 'El empleado seleccionado no tiene una nómina registrada para asociar la novedad.'])
                ->withInput();
        }

        $tipoNombre = ucfirst((string) $data['tipo_novedad']);
        $tipoNovedad = TipoNovedad::firstOrCreate(['nombre' => $tipoNombre]);

        $salarioBase = (float) ($salario->contrato_salario_base ?? optional($salario->contrato)->salario_base ?? 0);
        $valorCalculado = $this->calculoNovedadService->calcularValor($data, $salarioBase);
        $dias = (float) ($data['dias'] ?? 0);
        $horas = (float) ($data['horas'] ?? 0);

        $novedad->update([
            'id_tipo_novedad' => $tipoNovedad->id_tipo_novedad,
            'id_salario' => $salario->id_salario,
            'empleado_id' => $data['empleado_id'],
            'tipo_novedad_nombre' => $tipoNombre,
            'fecha' => $data['fecha_inicio'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'unidad_cantidad' => $data['unidad_cantidad'],
            'dias' => $dias,
            'horas' => $horas,
            'cantidad' => $data['unidad_cantidad'] === 'horas' ? $horas : $dias,
            'es_remunerado' => (bool) ($data['es_remunerado'] ?? false),
            'salario_base' => $salarioBase,
            'valor_calculado' => $valorCalculado,
            'valor_novedad' => $valorCalculado,
            'pago' => $valorCalculado,
            'pago_manual' => $data['pago_manual'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        return redirect()->route('novedades.index')->with('success', 'La novedad se actualizó correctamente.');
    }

    public function destroy(int $id_novedad)
    {
        Novedad::query()->findOrFail($id_novedad)->delete();

        return redirect()->route('novedades.index')->with('success', 'La novedad se eliminó correctamente.');
    }
}
