<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    /**
     * Mostrar la vista de restablecimiento de contraseña para el token dado.
     */
    public function showResetForm(Request $request, $token = null)
    {
        $correo = $request->correo;

        // Verificar si la sesión tiene la bandera de verificación o si el código es válido en BD
        if (session('password_reset_verified_email') !== $correo || session('password_reset_verified_code') !== $token) {
            // Segunda verificación directamente contra BD en caso de que la sesión se haya borrado
            $record = DB::table('password_reset_tokens')
                ->where('email', $correo)
                ->where('token', $token)
                ->first();

            if (!$record) {
                return redirect()->route('password.request')->withErrors(['correo' => 'Sesión expirada o inválida.']);
            }
        }

        return view('auth.passwords.reset')->with(
            ['token' => $token, 'correo' => $correo]
        );
    }

    /**
     * Restablecer la contraseña del usuario dado.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'correo' => 'required|email',
            'contrasena' => 'required|confirmed|min:8',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->correo)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return back()->withErrors(['correo' => 'Token de recuperación inválido.']);
        }

        // Opcional: Verificar la expiración de nuevo
        $expires = config('auth.passwords.users.expire');
        if (Carbon::parse($record->created_at)->addMinutes($expires)->isPast()) {
            return redirect()->route('password.request')->withErrors(['correo' => 'El código ha expirado.']);
        }

        $user = Usuario::where('correo', $request->correo)->first();
        if (!$user) {
            return back()->withErrors(['correo' => 'Usuario no encontrado.']);
        }

        // Restablecer contraseña
        $user->contrasena = Hash::make($request->contrasena);
        $user->save();

        // Eliminar token
        DB::table('password_reset_tokens')->where('email', $request->correo)->delete();

        // Clear session
        session()->forget(['password_reset_verified_email', 'password_reset_verified_code']);

        return redirect()->route('login')->with('status', 'Tu contraseña ha sido restablecida con éxito.');
    }
}
