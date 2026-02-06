<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') - Nomitech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>

<body class="bg-[#1565C0]">


    <div class="flex min-h-screen">


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

        </main>
    </div>

</body>

</html>