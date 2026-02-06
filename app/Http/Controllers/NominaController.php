<?php

namespace App\Http\Controllers;

use App\Models\Salario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NominaController extends Controller
{
    /* ==========================
       INDEX
    ========================== */
    public function index(Request $request)
    {
        $salarios = Salario::with('contrato.usuario')
            ->when($request->documento, function ($q) use ($request) {
                $q->whereHas('contrato.usuario', function ($u) use ($request) {
                    $u->where('doc', $request->documento);
                });
            })
            ->orderByDesc('fecha_pago')
            ->get();

        return view('nomina.index', compact('salarios'));
    }

    /* ==========================
       STEP 1
    ========================== */
    public function step1()
    {
        session(['nomina.step' => 1]);
        return view('nomina.step1');
    }

    public function postStep1(Request $request)
    {
        session(['nomina.step1' => $request->all()]);
        return redirect()->route('nomina.step2');
    }

    /* ==========================
       STEP 2
    ========================== */
    public function step2()
    {
        session(['nomina.step' => 2]);
        return view('nomina.step2');
    }

    public function postStep2(Request $request)
    {
        session(['nomina.step2' => $request->all()]);
        return redirect()->route('nomina.step3');
    }

    /* ==========================
       STEP 3
    ========================== */
    public function step3()
    {
        session(['nomina.step' => 3]);
        return view('nomina.step3');
    }

    /* ==========================
       BUSCAR EMPLEADO
    ========================== */
    public function buscarEmpleado($doc)
    {
        return DB::table('usuario')
            ->join('contrato', 'usuario.doc', '=', 'contrato.doc')
            ->where('usuario.doc', $doc)
            ->where('contrato.activo', 1)
            ->select(
                'usuario.doc',
                DB::raw("CONCAT(
                    usuario.primer_nombre,' ',
                    IFNULL(usuario.otros_nombres,''),' ',
                    usuario.primer_apellido,' ',
                    IFNULL(usuario.segundo_apellido,'')
                ) as nombre"),
                'usuario.telefono',
                'contrato.salario_base',
                'contrato.id_contrato'
            )
            ->first();
    }

    /* ==========================
       GUARDAR NÓMINA
    ========================== */
    public function store(Request $request)
    {
        $s1 = session('nomina.step1');
        $s2 = session('nomina.step2');

        DB::table('salario')->insert([
            'id_contrato' => $s1['id_contrato'],
            'salario_base' => $s1['salario_base'],
            'auxilio_transporte' => 162000,
            'horas_extra' => $s2['horas_extra'],
            'bonificaciones' => $s2['bonificaciones'],
            'comisiones' => $s2['comisiones'],
            'otros_devengos' => $s2['otros_devengos'],
            'eps' => $request->eps,
            'afp' => $request->afp,
            'fecha_pago' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session()->forget('nomina');

        return redirect()->route('nomina.index')
            ->with('success', 'Nómina guardada correctamente');
    }
}
