<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title','Nomitech')</title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    {{-- Flatpickr Calendar --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    {{-- Alpine --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_nomitech.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('images/logo_nomitech.svg') }}">

    <style>
        body { font-family: 'Manrope', sans-serif; }
        [x-cloak]{ display:none !important; }
    </style>
</head>

<body class="bg-slate-100 min-h-screen">

{{-- Overlay móvil --}}
<div id="nomina-overlay" onclick="toggleNominaSidebar()"></div>

<div class="flex min-h-screen">

    {{-- SIDEBAR --}}
    <aside id="nomina-sidebar" class="w-64 bg-[#1565C0] text-white flex flex-col flex-shrink-0">
        <div class="p-6 text-2xl font-bold">Nomitech</div>

        <nav class="flex-1 px-4 space-y-1">
            <a href="{{ route('empleados.index') }}" class="flex items-center px-4 py-3 bg-white/10 rounded-lg">
                <span class="material-icons mr-3">people</span> Empleados
            </a>
            <a href="{{ route('nomina.index') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg">
                <span class="material-icons mr-3">receipt_long</span> Nómina
            </a>
            <a href="{{ route('novedades.index') }}" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg">
                <span class="material-icons mr-3">event_note</span> Novedades
            </a>
        </nav>
    </aside>

    {{-- CONTENIDO --}}
    <main class="flex-1 bg-white rounded-tl-[40px] shadow-xl overflow-hidden mt-4 relative min-w-0">

        {{-- Botón hamburguesa --}}
        <button
            id="nomina-hamburger"
            onclick="toggleNominaSidebar()"
            class="fixed top-4 left-4 z-50 bg-[#1565C0] text-white p-3 rounded-lg shadow-lg hover:bg-[#0D47A1] transition"
            aria-label="Abrir menú"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        @yield('content')
    </main>

</div>

<script>
    function toggleNominaSidebar() {
        const sidebar = document.getElementById('nomina-sidebar');
        const overlay = document.getElementById('nomina-overlay');
        if (!sidebar || !overlay) return;
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
        document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            const sidebar = document.getElementById('nomina-sidebar');
            const overlay = document.getElementById('nomina-overlay');
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
</script>

</body>
</html>
