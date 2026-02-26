<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    private function tokenMatchesRecord(string $token, ?object $record): bool
    {
        if (!$record) {
            return false;
        }

        $storedToken = (string) ($record->token ?? '');
        if ($storedToken === '') {
            return false;
        }

        if (hash_equals($storedToken, $token)) {
            return true;
        }

        if (strlen($storedToken) > 20) {
            return Hash::check($token, $storedToken);
        }

        return false;
    }

    /**
     * Mostrar la vista de restablecimiento de contraseña para el token dado.
     */
    public function showResetForm(Request $request, $token = null)
    {
        $correo = strtolower(trim((string) $request->query('correo', '')));
        $token = trim((string) $token);

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{6}$/', $token)) {
            return redirect()->route('password.request')->withErrors(['correo' => 'El token de recuperación no es válido o ha expirado.']);
        }

        // Verificar si la sesión tiene la bandera de verificación o si el código es válido en BD
        if (session('password_reset_verified_email') !== $correo || session('password_reset_verified_code') !== $token) {
            // Segunda verificación directamente contra BD en caso de que la sesión se haya borrado
            $record = DB::table('password_reset_tokens')
                ->where('email', $correo)
                ->first();

            if (!$this->tokenMatchesRecord($token, $record)) {
                return redirect()->route('password.request')->withErrors(['correo' => 'El token de recuperación no es válido o ha expirado.']);
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
        $correo = strtolower(trim((string) $request->input('correo', '')));
        $token = trim((string) $request->input('token', ''));

        $request->validate(
            [
                'token' => 'required|digits:6',
                'correo' => 'required|email|max:255',
                'contrasena' => [
                    'required',
                    'string',
                    'min:8',
                    'max:64',
                    'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/',
                    'confirmed',
                ],
            ],
            [
                'token.required' => 'El token de recuperación es obligatorio.',
                'token.digits' => 'El token de recuperación debe contener exactamente 6 números.',
                'correo.required' => 'El campo correo electrónico es obligatorio.',
                'correo.email' => 'El correo electrónico no es válido.',
                'correo.max' => 'El correo electrónico no puede superar los 255 caracteres.',
                'contrasena.required' => 'El campo contraseña es obligatorio.',
                'contrasena.string' => 'La contraseña debe ser una cadena de texto válida.',
                'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'contrasena.max' => 'La contraseña no puede superar los 64 caracteres.',
                'contrasena.regex' => 'La contraseña debe contener al menos una letra, un número y un símbolo.',
                'contrasena.confirmed' => 'Las contraseñas no coinciden.',
            ]
        );

        $record = DB::table('password_reset_tokens')
            ->where('email', $correo)
            ->first();

        if (!$this->tokenMatchesRecord($token, $record)) {
            return back()->withErrors(['correo' => 'El token de recuperación no es válido o ha expirado.']);
        }

        // Opcional: Verificar la expiración de nuevo
        $expires = config('auth.passwords.users.expire');
        if (Carbon::parse($record->created_at)->addMinutes($expires)->isPast()) {
            return redirect()->route('password.request')->withErrors(['correo' => 'El token de recuperación no es válido o ha expirado.']);
        }

        $user = Usuario::where('correo', $correo)->first();
        if (!$user) {
            return back()->withErrors(['correo' => 'Usuario no encontrado.']);
        }

        // Restablecer contraseña
        $user->contrasena = Hash::make($request->contrasena);
        $user->save();

        // Eliminar token
        DB::table('password_reset_tokens')->where('email', $correo)->delete();

        // Clear session
        session()->forget(['password_reset_verified_email', 'password_reset_verified_code']);

        return redirect()->route('login')->with('status', 'La contraseña se ha restablecido correctamente.');
    }
}
