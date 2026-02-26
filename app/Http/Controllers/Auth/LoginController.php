<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_LOCKOUT_SECONDS = 300;

    private function loginThrottleKey(Request $request): string
    {
        $correo = strtolower(trim((string) $request->input('correo', '')));
        return 'login:' . $correo . '|' . $request->ip();
    }

    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $throttleKey = $this->loginThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = (int) ceil($seconds / 60);
            $minuteText = $minutes === 1 ? 'minuto' : 'minutos';

            return back()->withErrors([
                'correo' => "Has superado el número de intentos permitidos. Intenta nuevamente en {$minutes} {$minuteText}.",
            ])->withInput($request->only('correo'));
        }

        // Validate credentials
        $correo = strtolower(trim((string) $request->input('correo', '')));
        $password = (string) $request->input('contrasena', '');

        $usuario = Usuario::where('correo', $correo)->first();

        if (!$usuario || !Hash::check($password, $usuario->contrasena)) {
            RateLimiter::hit($throttleKey, self::LOGIN_LOCKOUT_SECONDS);

            if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
                return back()->withErrors([
                    'correo' => 'Has superado el número de intentos permitidos. Tu acceso ha sido bloqueado por 5 minutos.',
                ])->withInput($request->only('correo'));
            }

            $attempts = RateLimiter::attempts($throttleKey);
            $remainingAttempts = max(self::MAX_LOGIN_ATTEMPTS - $attempts, 0);
            $attemptText = $remainingAttempts === 1 ? 'intento' : 'intentos';

            return back()->withErrors([
                'correo' => "Las credenciales ingresadas son incorrectas. Te quedan {$remainingAttempts} {$attemptText}.",
            ])->withInput($request->only('correo'));
        }

        RateLimiter::clear($throttleKey);

        // Superadmin (role 4) - no license check needed
        if ((int) $usuario->id_rol === 4) {
            Auth::login($usuario);
            return redirect()->route('superadmin.empresas.index');
        }

        // Representante Legal (rol 1)
        if ((int) $usuario->id_rol === 1) {
            $empresa = $usuario->empresa()->first();

            if (!$empresa) {
                return back()->withErrors(['correo' => 'No hay empresa asignada'])->withInput();
            }

            // Verificar si la empresa tiene licencia activa
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

        // Empleado (rol 3) o Administrador (rol 2)
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

            // Verificar si la empresa tiene licencia activa
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
                return redirect()->route('empleados.index'); // Administrador
            } else {
                return redirect('/trabajador'); // Empleado
            }
        }

        // Retorno predeterminado
        Auth::login($usuario);
        return redirect('/');
    }
}
