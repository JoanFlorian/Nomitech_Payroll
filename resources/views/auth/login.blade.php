<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Nomitech</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_nomitech.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('images/logo_nomitech.svg') }}">
</head>

<body>
    <div class="relative flex items-center justify-center min-h-screen bg-[#F7F9FC] p-4 sm:p-6 md:p-8 font-['Inter']">
        <!-- Floating Home Link -->
        <a href="{{ route('index') }}" class="absolute top-4 left-4 sm:top-8 sm:left-8 flex items-center gap-2 text-gray-500 hover:text-[#1565C0] transition group duration-300">
            <div class="p-1 px-2.5 bg-white shadow-sm rounded-full border border-gray-100 group-hover:bg-[#1565C0]/5">
                <i class="material-icons text-xl sm:text-2xl mt-1">arrow_back</i>
            </div>
            <span class="text-sm font-medium hidden sm:block">Regresar al inicio</span>
        </a>

        <div class="w-full max-w-4xl mx-auto shadow-2xl rounded-2xl overflow-hidden mt-8 md:mt-0">
            <div class="grid grid-cols-1 md:grid-cols-2">

                <x-auth.left-panel />

                <x-auth.right-panel>
                    <x-auth.login-form />
                </x-auth.right-panel>

            </div>
        </div>
    </div>
</body>

</html>