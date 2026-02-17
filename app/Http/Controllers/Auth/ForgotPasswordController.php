<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send a reset code to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['correo' => 'required|email']);

        $user = Usuario::where('correo', $request->correo)->first();

        if (!$user) {
            return back()->withErrors(['correo' => 'No encontramos un usuario con ese correo electrónico.']);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->correo],
            [
                'token' => $code,
                'created_at' => Carbon::now()
            ]
        );

        // Send Email
        try {
            Mail::send([], [], function ($message) use ($user, $code) {
                $message->to($user->correo)
                    ->subject('Código de recuperación de contraseña - Nomitech')
                    ->html("<h2>Hola {$user->primer_nombre},</h2><p>Has solicitado restablecer tu contraseña. Tu código de verificación es:</p><h1 style='letter-spacing: 5px; color: #1565C0;'>{$code}</h1><p>Si no solicitaste este cambio, puedes ignorar este correo.</p>");
            });
        } catch (\Exception $e) {
            // Log the error if mail fails but provide message to user if needed
            \Log::error('Mail failure: ' . $e->getMessage());
            return back()->withErrors(['correo' => 'Hubo un error al enviar el correo. Por favor, intenta de nuevo más tarde.']);
        }

        return redirect()->route('password.verify.form', ['correo' => $request->correo])
            ->with('status', 'Hemos enviado un código de verificación a tu correo electrónico.');
    }
}
