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

        // Determine company for the user
        $empresa = $usuario->empresas()->first();
        if (!$empresa) {
            $empresa = $usuario->empresasAsignadas()->first();
        }

        if (!$empresa) {
            // No company assigned - redirect to selection/notice
            return redirect()->route('empresa.select');
        }

        // Load license
        $licencia = $empresa->licencia;

        if (!$licencia) {
            return redirect()->route('licencia.required');
        }

        if ($licencia->fecha_fin && $licencia->fecha_fin->isPast()) {
                return redirect()->route('licencia.expired');
            }
            return redirect()->route('licencia.pending');
    

        // All good - log in user and set session company
        Auth::login($usuario);
        session(['empresa_id' => $empresa->id_empresa]);

        // Redirect by role
        switch ((int) $usuario->id_rol) {
            case 1: // Administrador
            case 2: // Auxiliar RRHH
                return redirect()->route('empleados.index');
            case 4: // Super admin
                return redirect('/superadmin');
            case 3: // Empleado
            default:
                return redirect('/trabajador');
        }
    }
}
