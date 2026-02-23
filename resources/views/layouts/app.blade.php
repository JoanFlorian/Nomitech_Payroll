<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Nomitech</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    {{-- Flatpickr Calendar --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- jQuery (shared by varios módulos) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Configure CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <style>
    [x-cloak] { display: none !important; }

    .input-field {
        width: 100%;
        height: 3rem;
        padding-left: 2.75rem;
        border-radius: 0.5rem;
        border: 1px solid #BDBDBD;
        transition: all 300ms;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .input-field:focus {
        outline: none;
        border: 2px solid #1565C0;
        box-shadow: 0 0 0 4px rgba(21, 101, 192, 0.2);
        padding-left: calc(2.75rem - 1px);
    }

    .input-field:hover {
        border-color: #1565C0;
    }

    .input-icon {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 0.875rem;
        color: #9E9E9E;
        pointer-events: none;
        transition: color 300ms;
        z-index: 10;
    }

    .input-group {
        position: relative;
    }

    .input-group:focus-within .input-icon,
    .input-group:hover .input-icon {
        color: #1565C0;
    }
    
    /* Shape decorations - absolute positioning so they don't affect layout */
    .shape {
        position: absolute;
        pointer-events: none;
    }
    
    .shape-circle {
        border-radius: 50%;
    }
    
    .shape-triangle,
    .shape-arrow {
        width: 0;
        height: 0;
    }
    </style>

</head>
<body class="bg-[#1565C0]">
    

<div class="flex min-h-screen">


    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main content --}}
    <main class="flex-1 min-h-screen bg-white relative overflow-hidden rounded-l-3xl shadow-2xl">
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
