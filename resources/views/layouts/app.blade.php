<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title') - Nomitech</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    @include('auth.styles') {{-- o tus estilos compartidos --}}
</head>
<body class="bg-[#1565C0]">
<div class="flex h-screen">

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main content --}}
    <main class="flex-1 bg-white relative overflow-hidden rounded-l-3xl shadow-2xl">
        <x-shapes /> {{-- las figuras decorativas --}}
        <div class="h-full w-full flex flex-col p-8 z-10 relative">
            <h1 class="text-4xl font-bold text-gray-800 mb-8">@yield('page-title')</h1>
            <div class="flex-grow bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                @yield('content') {{-- Aquí va el contenido de cada módulo --}}
            </div>
        </div>

        {{-- Botón flotante + icono --}}
        <button class="absolute bottom-8 right-8 bg-[rgb(16,185,129)] text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg hover:bg-emerald-600 transition-colors z-20">
            <span class="material-icons text-4xl">add</span>
        </button>
    </main>
</div>
</body>
</html>
