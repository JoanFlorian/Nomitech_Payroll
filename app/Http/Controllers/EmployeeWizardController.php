<?php

namespace App\Http\Controllers;
use App\Models\Usuario;
use App\Models\Contrato;
use App\Models\Salario;
use App\Models\NominaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EmployeeWizardController extends Controller
{
    
    public function step1(Request $request)
    {
        $data = $request->validate([
            'doc' => 'required',
            'primer_nombre' => 'required',
            'primer_apellido' => 'required',
            'correo' => 'required|email',
            'telefono' => 'required',
            'direccion' => 'required',
        ]);

        session(['employee.step1' => $data]);

        return redirect()->route('empleados.step2');
    }

    // PASO 2
    public function step2(Request $request)
    {
        $data = $request->validate([
            'salario_base' => 'required|numeric',
            'auxilio_transporte' => 'numeric',
            'comisiones' => 'numeric',
            'bonificaciones' => 'numeric',
            'otros_devengos' => 'numeric'
        ]);

        session(['employee.step2' => $data]);

        return redirect()->route('empleados.step3');
    }

    // PASO 3 – Guardar todo
    public function step3(Request $request, NominaService $service)
    {
        $step1 = session('employee.step1');
        $step2 = session('employee.step2');

        $data = $request->validate([
            'eps' => 'required|numeric',
            'afp' => 'required|numeric',
            'otras_deducciones' => 'numeric'
        ]);

        DB::transaction(function () use ($step1, $step2, $data, $service) {

            $usuario = Usuario::create($step1);

            $contrato = Contrato::create([
                'doc' => $usuario->doc,
                'id_empresa' => 1,
                'salario_base' => $step2['salario_base'],
                'activo' => 1
            ]);

            $salario = Salario::create(array_merge(
                ['id_contrato' => $contrato->id_contrato],
                $step2,
                $data,
                ['fecha_pago' => now()]
            ));

            $resultado = $service->liquidar($salario);

         
        });

        session()->forget('employee');

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado y nómina creados correctamente');
    }
}
