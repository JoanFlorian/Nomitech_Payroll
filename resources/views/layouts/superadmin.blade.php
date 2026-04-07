<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nomitech - Superadmin</title>

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_nomitech.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('images/logo_nomitech.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <style>
        body { font-family: 'Manrope', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">

    {{-- Overlay para móvil --}}
    <div id="nomitech-overlay" onclick="toggleNomitechSidebar()"></div>

    <div class="flex min-h-screen">
        <x-nomitech />

        <main class="flex-1 bg-gray-100 overflow-y-auto min-w-0">

            {{-- Botón hamburguesa --}}
            <button
                id="nomitech-hamburger"
                onclick="toggleNomitechSidebar()"
                class="fixed top-4 left-4 z-50 bg-blue-900 text-white p-3 rounded-lg shadow-lg hover:bg-blue-800 transition"
                aria-label="Abrir menú"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="p-4 sm:p-6 lg:p-8 pt-16 lg:pt-8">
                @yield('content')
            </div>
        </main>

    </div>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        function toggleNomitechSidebar() {
            const sidebar = document.getElementById('nomitech-sidebar');
            const overlay = document.getElementById('nomitech-overlay');
            if (!sidebar || !overlay) return;
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                const sidebar = document.getElementById('nomitech-sidebar');
                const overlay = document.getElementById('nomitech-overlay');
                if (sidebar) sidebar.classList.remove('open');
                if (overlay) overlay.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    </script>

</body>

</html>