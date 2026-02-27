<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    private const MAX_TOKEN_REQUESTS = 5;
    private const TOKEN_REQUEST_LOCKOUT_SECONDS = 300; // Change from 600 to 300 (5 minutes)
    private const TOKEN_REQUEST_COOLDOWN_SECONDS = 60;

    private function tokenRequestThrottleKey(string $correo): string
    {
        return 'password-reset-request:' . strtolower(trim($correo));
    }

    private function tokenCooldownThrottleKey(string $correo): string
    {
        return 'password-reset-cooldown:' . strtolower(trim($correo));
    }

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
        $request->validate(
            ['correo' => 'required|string|email|max:255'],
            [
                'correo.required' => 'El campo correo electrónico es obligatorio.',
                'correo.string' => 'El correo electrónico debe ser texto válido.',
                'correo.email' => 'El correo electrónico no es válido.',
                'correo.max' => 'El correo electrónico no puede superar los 255 caracteres.',
            ]
        );

        $correo = strtolower(trim((string) $request->input('correo', '')));

        $throttleKey = $this->tokenRequestThrottleKey($correo);
        $cooldownKey = $this->tokenCooldownThrottleKey($correo);
        $blockKey = 'password-reset-blocked:' . $correo;

        if (Cache::has($blockKey)) {
            $unblockTime = Cache::get($blockKey);
            $seconds = max(0, $unblockTime - now()->timestamp);

            if ($seconds > 0) {
                $minutes = (int) ceil($seconds / 60);
                $minuteText = $minutes === 1 ? 'minuto' : 'minutos';

                Log::warning("Rate limit reached for {$correo} at ForgotPasswordController. Blocked.");

                return back()->withErrors([
                    'correo' => "Has agotado tus 5 intentos. Por seguridad, tu capacidad de enviar códigos se ha bloqueado por {$minutes} {$minuteText}.",
                ])->withInput($request->only('correo'))->with('cooldown_seconds', $seconds);
            } else {
                Cache::forget($blockKey);
            }
        }

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($cooldownKey);

            return back()->withErrors([
                'correo' => "Debes esperar {$seconds} segundos antes de solicitar un nuevo código.",
            ])->withInput($request->only('correo'))->with('cooldown_seconds', $seconds);
        }

        $user = Usuario::where('correo', $correo)->first();

        if (!$user) {
            return back()->withErrors(['correo' => 'No encontramos un usuario con ese correo electrónico.']);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $correo],
            [
                'token' => $code,
                'created_at' => Carbon::now()
            ]
        );

        RateLimiter::hit($cooldownKey, self::TOKEN_REQUEST_COOLDOWN_SECONDS);
        RateLimiter::hit($throttleKey, 3600); // Mantenemos el historial de intentos por 1 hora
        $currentAttempts = RateLimiter::attempts($throttleKey);

        Log::info("Code sent to {$correo}. Current attempts: {$currentAttempts}/" . self::MAX_TOKEN_REQUESTS);

        // Send Email
        try {
            Mail::send([], [], function ($message) use ($user, $code) {
                $message->to($user->correo)
                    ->subject('Código de recuperación de contraseña - Nomitech')
                    ->html("<h2>Hola {$user->primer_nombre},</h2><p>Has solicitado restablecer tu contraseña. Tu código de verificación es:</p><h1 style='letter-spacing: 5px; color: #1565C0;'>{$code}</h1><p>Si no solicitaste este cambio, puedes ignorar este correo.</p>");
            });
        } catch (\Exception $e) {
            // Log the error if mail fails but provide message to user if needed
            Log::error('Mail failure: ' . $e->getMessage());
            return back()->withErrors(['correo' => 'Hubo un error al enviar el correo. Por favor, intenta de nuevo más tarde.']);
        }

        $statusMessage = 'Hemos enviado un código de verificación a tu correo electrónico.';

        // Add warning message starting from 2nd attempt
        if ($currentAttempts >= self::MAX_TOKEN_REQUESTS) {
            Cache::put($blockKey, now()->addMinutes(5)->timestamp, now()->addMinutes(5));
            RateLimiter::clear($throttleKey);

            $statusMessage .= " Has alcanzado el límite de 5 intentos. No podrás solicitar más códigos por 5 minutos.";
        } elseif ($currentAttempts >= 2) {
            $remaining = max(self::MAX_TOKEN_REQUESTS - $currentAttempts, 0);
            $statusMessage .= " Tienes {$remaining} intentos restantes antes de que el acceso se bloquee por 5 minutos.";
        }

        return redirect()->route('password.verify.form', ['correo' => $correo])
            ->with('status', $statusMessage);
    }
}
