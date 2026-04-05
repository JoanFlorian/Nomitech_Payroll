@component('mail::message')
# Hola, {{ $nombre }}

Bienvenido a Nomitech. Para completar tu registro y proceder con la activación de tu cuenta, por favor usa el siguiente código de verificación:

@component('mail::panel')
<h1 style="text-align: center; letter-spacing: 5px; margin: 0;">{{ $code }}</h1>
@endcomponent

Este código expirará en 60 minutos.

Si no has solicitado este registro, puedes ignorar este correo.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
