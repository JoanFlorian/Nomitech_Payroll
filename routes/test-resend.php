<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/test-resend', function () {
    try {
        Mail::raw('Este es un correo de prueba desde Nomitech usando Resend API.', function ($message) {
            $message->to('florezk546@gmail.com')
                    ->subject('Prueba de Resend API - Nomitech');
        });
        return 'Email enviado correctamente (revisa tu bandeja de entrada o spam).';
    } catch (\Exception $e) {
        return 'Error al enviar email: ' . $e->getMessage();
    }
});
