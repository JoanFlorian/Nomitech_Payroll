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

        // Superadmin bypasses company/license validation
        if ((int) $usuario->id_rol === 4) {
            Auth::login($usuario);
            
        } else {
            // Non-superadmin users must have a company assigned
            $empresa = $usuario->empresa()->first();

            if (!$empresa) {
                return back()->withErrors(['correo' => 'No hay empresa asignada'])->withInput();
            }

            // Load license
            $licencia = $empresa->licencia;

            if (!$licencia) {
                return back()->withErrors(['correo' => 'Licencia no asignada'])->withInput();
            }

            // Check license status
            if ($licencia->fecha_fin && $licencia->fecha_fin->isPast()) {
                return back()->withErrors(['correo' => 'Licencia vencida'])->withInput();
            }

            // Check if license is not yet active
            if ($licencia->fecha_inicio && $licencia->fecha_inicio->isFuture()) {
                return back()->withErrors(['correo' => 'Licencia aún no activa'])->withInput();
            }

            // All good - log in user and set session company
            Auth::login($usuario);
            session(['empresa_id' => $empresa->id_empresa]);
        }

        // Redirect by role
        switch ((int) $usuario->id_rol) {
            case 1: // Administrador
                return redirect()->route('empleados.index');
            case 2: // Auxiliar RRHH
            case 4: // Superadmin
                return redirect()->route('superadmin.empresas.index');
            case 3: // Empleado
            default:
                return redirect('/trabajador');
        }
    }
}
