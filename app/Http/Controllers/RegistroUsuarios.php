<?php

namespace App\Http\Controllers;

use App\Http\Requests\Step1Request;
use App\Http\Requests\Step2Request;
use App\Models\Contrato;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class RegistroUsuarios extends Controller
{
    /*PASO 1 */
    public function storeStep1(Step1Request $validate)
    {
        session(['employee.step1' => $validate->all()]);
        return response()->json([
            'ok' => true,
            'message' => 'Datos validados correctamente'
        ], 200);
    }

    /* PASO 2 */
    public function storeStep2(Step2Request $request)
    {
        // Normalizar checkbox
        $request['alto_riesgo'] = $request->boolean('alto_riesgo');

        session(['employee.step2' => $request->all()]);
        return response()->json([
            'ok' => true,
            'message' => 'Datos validados correctamente'
        ], 200);
    }

    /*PASO 3  */
    public function storeFinal(Request $request)
    {


        try {
            DB::beginTransaction();
            $dataStep3 = $request->validate([
                'id_forma_pago' => 'required|string',
                'id_metodo_pago' => 'required|string',
                'tipo_cuenta' => 'required|string',
                'numero_cuenta' => 'required|string|max:18',
                'id_eps' => 'required|string',
                'id_afp' => 'required',
            ]);
            $employeeData = array_merge(
                session('employee.step1', []),
                // session('employee.step2', []),
                // $dataStep3
            );


            $step2 = array_merge(
                session('employee.step2', []),
                $dataStep3
            );
            // dd($step2,$employeeData);
            Usuario::create($employeeData + ['id_ciudad' => $employeeData['ciudad'], 'doc' => $employeeData['numero_documento']]);

            Contrato::create($step2 + ['salario_base' => $step2['salario'], 'doc' => $employeeData['numero_documento']]);

            session()->forget('employee');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Error en registro final: " . $th->getMessage(), [
                'exception' => $th,
                'session_data' => session('employee')
            ]);

            return back()->with('error', 'Hubo un error al procesar el registro: ' . $th->getMessage())->withInput();
        }
        return redirect()->route('empleados.index');
    }
}
