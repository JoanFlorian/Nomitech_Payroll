<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $request->validate([
            'correo' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->correo)
            ->first();

        if (!$record || $record->token !== $request->code) {
            return back()->withErrors(['code' => 'El código ingresado es incorrecto.']);
        }

        // Check expiry (e.g., 60 minutes as per config/auth.php users expiration)
        $expires = config('auth.passwords.users.expire');
        if (Carbon::parse($record->created_at)->addMinutes($expires)->isPast()) {
            return back()->withErrors(['code' => 'El código ha expirado. Por favor, solicita uno nuevo.']);
        }

        // Store verification in session to allow access to reset form
        session(['password_reset_verified_email' => $request->correo, 'password_reset_verified_code' => $request->code]);

        return redirect()->route('password.reset', ['token' => $request->code, 'correo' => $request->correo]);
    }
}
