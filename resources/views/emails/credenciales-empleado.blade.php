<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Nomitech</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f5f7fa; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #1565C0, #1976D2); padding: 40px 30px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 26px; font-weight: 700; letter-spacing: -0.5px; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; margin-top: 6px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 18px; font-weight: 600; color: #1565C0; margin-bottom: 16px; }
        .intro { font-size: 15px; color: #555; line-height: 1.7; margin-bottom: 28px; }
        .credentials-box { background: #f0f4ff; border: 2px solid #d0dfff; border-radius: 10px; padding: 24px 28px; margin-bottom: 28px; }
        .credentials-box h3 { font-size: 14px; font-weight: 700; color: #1565C0; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; }
        .credential-row { display: flex; align-items: center; margin-bottom: 12px; }
        .credential-row:last-child { margin-bottom: 0; }
        .credential-label { font-size: 13px; color: #888; width: 140px; flex-shrink: 0; }
        .credential-value { font-size: 15px; font-weight: 600; color: #1a1a1a; background: #fff; border: 1px solid #d0dfff; border-radius: 6px; padding: 6px 14px; flex: 1; letter-spacing: 0.3px; }
        .warning-box { background: #fff8e1; border-left: 4px solid #f59e0b; border-radius: 0 8px 8px 0; padding: 14px 18px; margin-bottom: 28px; }
        .warning-box p { font-size: 13px; color: #92400e; line-height: 1.6; }
        .warning-box strong { color: #78350f; }
        .btn-wrapper { text-align: center; margin-bottom: 28px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #1565C0, #1976D2); color: #ffffff !important; text-decoration: none; padding: 14px 36px; border-radius: 8px; font-size: 15px; font-weight: 600; letter-spacing: 0.3px; }
        .divider { border: none; border-top: 1px solid #eaecef; margin: 24px 0; }
        .footer { background: #f8f9fb; padding: 24px 40px; text-align: center; border-top: 1px solid #eaecef; }
        .footer p { font-size: 12px; color: #999; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <h1>Nomitech</h1>
            <p>Sistema de Gestión de Nómina</p>
        </div>

        <!-- Body -->
        <div class="body">
            <p class="greeting">Hola, {{ $nombreCompleto }}</p>

            <p class="intro">
                Tu cuenta en la plataforma de empleados <strong>Nomitech</strong> ha sido creada exitosamente por tu empleador.
                A continuación encontrarás tus credenciales de acceso:
            </p>

            <!-- Credenciales -->
            <div class="credentials-box">
                <h3>Tus credenciales de acceso</h3>
                <div class="credential-row">
                    <span class="credential-label">Usuario:</span>
                    <span class="credential-value">{{ $correo }}</span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Contraseña temporal:</span>
                    <span class="credential-value">{{ $contrasenaTemp }}</span>
                </div>
            </div>

            <!-- Advertencia -->
            <div class="warning-box">
                <p>
                    <strong>⚠️ Importante:</strong> Al ingresar por primera vez, el sistema te pedirá
                    cambiar tu contraseña. Por tu seguridad, no compartas estas credenciales con nadie.
                </p>
            </div>

            <!-- Botón -->
            <div class="btn-wrapper">
                <a href="{{ $urlLogin }}" class="btn">Ingresar a la plataforma</a>
            </div>

            <hr class="divider">

            <p style="font-size: 13px; color: #777; text-align: center;">
                Si tienes problemas para iniciar sesión, contacta al departamento de recursos humanos.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Este mensaje fue generado automáticamente por Nomitech.<br>
            Por favor no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
