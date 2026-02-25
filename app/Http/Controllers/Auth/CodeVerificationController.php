<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CodeVerificationController extends Controller
{
    /**
     * Display the code verification form.
     */
    public function showVerifyForm(Request $request)
    {
        $correo = $request->query('correo');

        if (!$correo) {
            return redirect()->route('password.request');
        }

        return view('auth.passwords.verify', compact('correo'));
    }

    /**
     * Verify the 6-digit code.
     */
    public function verify(Request $request)
    {
        $correo = trim((string) $request->input('correo', ''));
        $code = trim((string) $request->input('code', ''));

        $request->validate(
            [
                'correo' => 'required|email|max:255',
                'code' => 'required|digits:6',
            ],
            [
                'correo.required' => 'El campo correo electrónico es obligatorio.',
                'correo.email' => 'El correo electrónico no es válido.',
                'correo.max' => 'El correo electrónico no puede superar los 255 caracteres.',
                'code.required' => 'El código de verificación es obligatorio.',
                'code.digits' => 'El código de verificación debe contener exactamente 6 números.',
            ]
        );

        $record = DB::table('password_reset_tokens')
            ->where('email', $correo)
            ->first();

        $storedToken = (string) ($record->token ?? '');
        $isValidToken = $record && hash_equals($storedToken, $code);

        if (!$isValidToken && $record && strlen($storedToken) > 20) {
            $isValidToken = Hash::check($code, $storedToken);
        }

        if (!$isValidToken) {
            return back()->withErrors(['code' => 'El código ingresado es incorrecto.']);
        }

        // Verificar expiración (por ejemplo, 60 minutos según la expiración en config/auth.php)
        $expires = config('auth.passwords.users.expire');
        if (Carbon::parse($record->created_at)->addMinutes($expires)->isPast()) {
            return back()->withErrors(['code' => 'El código ha expirado. Por favor, solicita uno nuevo.']);
        }

        // Store verification in session to allow access to reset form
        session(['password_reset_verified_email' => $correo, 'password_reset_verified_code' => $code]);

        return redirect()->route('password.reset', ['token' => $code, 'correo' => $correo]);
    }
}
