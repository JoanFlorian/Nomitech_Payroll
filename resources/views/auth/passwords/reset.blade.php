<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restablecer Contraseña - Nomitech</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="flex items-center justify-center min-h-screen bg-[#F7F9FC]">
        <div class="w-full max-w-4xl mx-auto shadow-2xl rounded-2xl overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2">

                <x-auth.left-panel />

                <x-auth.right-panel>
                    <x-auth.reset-password-form :token="$token" :correo="$correo" />
                </x-auth.right-panel>

            </div>
        </div>
    </div>
</body>

</html>