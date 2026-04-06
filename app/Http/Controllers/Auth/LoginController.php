<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

            if ($usuario && !Hash::check($password, $usuario->contrasena)) {
                return back()->withErrors([
                    'contrasena' => "La contraseña es incorrecta. Te quedan {$remainingAttempts} {$attemptText}.",
                ])->withInput($request->only('correo'));
            }

            return back()->withErrors([
                'correo' => "Las credenciales ingresadas son incorrectas. Te quedan {$remainingAttempts} {$attemptText}.",
            ])->withInput($request->only('correo'));
        }

        RateLimiter::clear($throttleKey);

        // Superadmin (role 4) - no license check needed
        if ((int) $usuario->id_rol === 4) {
            Auth::login($usuario);

            if ((bool) $usuario->must_change_password) {
                return redirect()->route('cambiar-password');
            }

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

            if (!$licencia || !$licencia->fecha_fin) {
                // License is missing or pending payment - redirect to pending view
                Auth::login($usuario);
                session(['empresa_id' => $empresa->id_empresa]);

                if ((bool) $usuario->must_change_password) {
                    return redirect()->route('cambiar-password');
                }

                return redirect()->route('licencia.pending');
            }

            if (Carbon::parse($licencia->fecha_fin)->isPast()) {
                // License is expired - redirect to expired license view
                Auth::login($usuario);
                session(['empresa_id' => $empresa->id_empresa]);

                if ((bool) $usuario->must_change_password) {
                    return redirect()->route('cambiar-password');
                }

                return redirect()->route('licencia.expired');
            }

            // License is active, proceed normally
            Auth::login($usuario);
            session(['empresa_id' => $empresa->id_empresa]);

            if ((bool) $usuario->must_change_password) {
                return redirect()->route('cambiar-password');
            }

            return redirect()->route('empleados.index');
        }

        // Empleado (rol 3) o Perfiles Administrativos (Administrador, Auxiliar, Auditor)
        if (!in_array((int) $usuario->id_rol, [1, 4])) {
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

            if (!$licencia || !$licencia->fecha_fin || Carbon::parse($licencia->fecha_fin)->isPast()) {
                // License is not active - redirect to landing page with modal
                Auth::login($usuario);
                session(['empresa_id' => $empresa->id_empresa]);
                session(['license_expired' => true]); // Flag to show modal

                if ((bool) $usuario->must_change_password) {
                    return redirect()->route('cambiar-password');
                }

                return redirect()->route('index'); // Redirect to landing page
            }

            // License is active, proceed normally
            Auth::login($usuario);
            session(['empresa_id' => $empresa->id_empresa]);

            if ((bool) $usuario->must_change_password) {
                return redirect()->route('cambiar-password');
            }

            // Redirect based on sub-role
            if ((int) $usuario->id_rol === 3) {
                return redirect()->route('trabajador.dashboard'); // Empleado
            } else {
                $safeRoute = $usuario->getFirstAccessibleRoute();
                return redirect()->route($safeRoute);
            }
        }

        // Retorno predeterminado
        Auth::login($usuario);

        if ((bool) $usuario->must_change_password) {
            return redirect()->route('cambiar-password');
        }

        return redirect()->route('index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
