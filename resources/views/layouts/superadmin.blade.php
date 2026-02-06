<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nomitech - Superadmin</title>

    <!-- FUENTE -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- TAILWIND -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>

<body class="font-inter bg-gray-100">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-nomitech />

        {{-- Contenido --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    <!-- ALPINE JS -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
