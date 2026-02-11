<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        // Validate credentials
        $correo = $request->input('correo');
        $password = $request->input('contrasena');

        $usuario = Usuario::where('correo', $correo)->first();

        if (!$usuario || !Hash::check($password, $usuario->contrasena)) {
            return back()->withErrors(['correo' => 'Credenciales inválidas'])->withInput();
        }

        // Superadmin (role 4) - no license check needed
        if ((int) $usuario->id_rol === 4) {
            Auth::login($usuario);
            return redirect()->route('superadmin.empresas.index');
        }

        // Administrador (role 1)
        if ((int) $usuario->id_rol === 1) {
            $empresa = $usuario->empresa()->first();

            if (!$empresa) {
                return back()->withErrors(['correo' => 'No hay empresa asignada'])->withInput();
            }

            // Check if company has active license
            $licencia = $empresa->licencia;
            
            if (!$licencia || !$licencia->fecha_fin || $licencia->fecha_fin->isPast()) {
                // License is missing or expired - redirect to expired license view
                Auth::login($usuario);
                session(['empresa_id' => $empresa->id_empresa]);
                return redirect()->route('licencia.expired');
            }

            // License is active, proceed normally
            Auth::login($usuario);
            session(['empresa_id' => $empresa->id_empresa]);
            return redirect()->route('empleados.index');
        }

        // Empleado (role 3) or Auxiliar RRHH (role 2)
        if ((int) $usuario->id_rol === 2 || (int) $usuario->id_rol === 3) {
            // Get company from contrato (employee contract)
            $contrato = $usuario->contratos()->first();

            if (!$contrato) {
                return back()->withErrors(['correo' => 'No tiene contratos asignados'])->withInput();
            }

            $empresa = \App\Models\Empresa::find($contrato->id_empresa);

            if (!$empresa) {
                return back()->withErrors(['correo' => 'Empresa del contrato no encontrada'])->withInput();
            }

            // Check if company has active license
            $licencia = $empresa->licencia;

            if (!$licencia || !$licencia->fecha_fin || $licencia->fecha_fin->isPast()) {
                // License is not active - redirect to landing page with modal
                Auth::login($usuario);
                session(['empresa_id' => $empresa->id_empresa]);
                session(['license_expired' => true]); // Flag to show modal
                return redirect('/'); // Redirect to landing page
            }

            // License is active, proceed normally
            Auth::login($usuario);
            session(['empresa_id' => $empresa->id_empresa]);
            
            // Redirect based on sub-role
            if ((int) $usuario->id_rol === 2) {
                return redirect()->route('empleados.index'); // Auxiliar RRHH
            } else {
                return redirect('/trabajador'); // Empleado
            }
        }

        // Default fallback
        Auth::login($usuario);
        return redirect('/');
    }
}
