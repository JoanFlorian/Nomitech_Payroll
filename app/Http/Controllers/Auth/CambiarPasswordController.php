<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CambiarPasswordController extends Controller
{
    /**
     * Mostrar formulario de cambio de contraseña obligatorio.
     */
    public function show()
    {
        // Si el usuario no necesita cambiar contraseña, redirigir al dashboard correspondiente
        if (!Auth::check() || !(bool) Auth::user()->must_change_password) {
            return redirect()->route($this->getRedirectRoute(Auth::user()));
        }

        return view('auth.cambiar-password');
    }

    /**
     * Procesar el cambio de contraseña.
     */
    public function update(Request $request)
    {
        $request->validate([
            'contrasena' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'contrasena_confirmation' => 'required|string',
        ], [
            'contrasena.required'              => 'La nueva contraseña es obligatoria.',
            'contrasena.confirmed'             => 'Las contraseñas no coinciden.',
            'contrasena_confirmation.required' => 'Debes confirmar la nueva contraseña.',
        ]);

        $usuario = Auth::user();

        // Evitar que se reutilice la misma contraseña (el documento)
        if (Hash::check($request->contrasena, $usuario->contrasena)) {
            return back()->withErrors([
                'contrasena' => 'La nueva contraseña no puede ser igual a la contraseña temporal.',
            ]);
        }

        $usuario->contrasena          = Hash::make($request->contrasena);
        $usuario->must_change_password = false;
        $usuario->save();

        return redirect()->route($this->getRedirectRoute($usuario))
            ->with('success', 'Contraseña actualizada correctamente. ¡Bienvenido!');
    }

    private function getRedirectRoute($usuario)
    {
        if ((int) $usuario->id_rol === 4) {
            return 'superadmin.empresas.index';
        } elseif ((int) $usuario->id_rol === 1) {
            return 'empleados.index';
        } elseif ((int) $usuario->id_rol === 3) {
            return 'trabajador.dashboard';
        }

        return $usuario->getFirstAccessibleRoute() ?? 'index';
    }
}
