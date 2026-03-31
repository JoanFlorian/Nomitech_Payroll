<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredencialesEmpleadoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $nombreCompleto;
    public string $correo;
    public string $contrasenaTemp;
    public string $urlLogin;

    public function __construct(Usuario $usuario)
    {
        $this->nombreCompleto = trim(trim($usuario->primer_nombre . ' ' . ($usuario->otros_nombres ?? '')) . ' ' . $usuario->primer_apellido . ' ' . ($usuario->segundo_apellido ?? ''));
        $this->correo         = $usuario->correo;
        $this->contrasenaTemp = (string) $usuario->doc; // El documento ES la contraseña temporal
        $this->urlLogin       = url('/login');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido a Nomitech - Tus credenciales de acceso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.credenciales-empleado',
        );
    }
}
