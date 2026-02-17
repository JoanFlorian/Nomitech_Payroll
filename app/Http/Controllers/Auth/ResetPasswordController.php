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
     * Display the password reset view for the given token.
     */
    public function showResetForm(Request $request, $token = null)
    {
        $correo = $request->correo;

        // Verify if session has verification flag or if code is valid in DB
        if (session('password_reset_verified_email') !== $correo || session('password_reset_verified_code') !== $token) {
            // Second check against DB directly in case session cleared
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
     * Reset the given user's password.
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

        // Optional: Check expiry again
        $expires = config('auth.passwords.users.expire');
        if (Carbon::parse($record->created_at)->addMinutes($expires)->isPast()) {
            return redirect()->route('password.request')->withErrors(['correo' => 'El código ha expirado.']);
        }

        $user = Usuario::where('correo', $request->correo)->first();
        if (!$user) {
            return back()->withErrors(['correo' => 'Usuario no encontrado.']);
        }

        // Reset Password
        $user->contrasena = Hash::make($request->contrasena);
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->correo)->delete();

        // Clear session
        session()->forget(['password_reset_verified_email', 'password_reset_verified_code']);

        return redirect()->route('login')->with('status', 'Tu contraseña ha sido restablecida con éxito.');
    }
}
