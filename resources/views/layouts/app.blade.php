<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo_nomitech.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('images/logo_nomitech.svg') }}">
    <title>@yield('title') - Nomitech</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                        manrope: ['Manrope', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    {{-- Flatpickr Calendar --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    body {
        font-family: 'Manrope', sans-serif;
    }
    </style>

    @stack('styles')

</head>
<body class="bg-[#1565C0]">
    

<div class="flex min-h-screen relative">

    {{-- Overlay oscuro para móviles cuando el sidebar está abierto --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 lg:hidden hidden" onclick="toggleMobileSidebar()"></div>

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main content --}}
    <main class="flex-1 min-h-screen bg-white relative overflow-x-hidden rounded-l-3xl shadow-2xl w-full lg:w-auto">
        <x-shapes /> {{-- las figuras decorativas --}}
        
        {{-- Botón hamburguesa para móviles --}}
        <button 
            id="mobile-menu-btn"
            onclick="toggleMobileSidebar()" 
            class="lg:hidden fixed top-4 left-4 z-20 bg-[#1565C0] text-white p-3 rounded-lg shadow-lg hover:bg-[#0D47A1] transition"
            aria-label="Abrir menú"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        
        <div class="min-h-full w-full flex flex-col p-4 sm:p-6 lg:p-8 z-10 relative">
            {{-- Header con título + campana --}}
            <div class="flex items-center justify-between mb-4 sm:mb-6 lg:mb-8 mt-12 lg:mt-0">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">@yield('page-title')</h1>

                @if(isset($adminUnreadAdjustmentNotesCount) && (int)(Auth::user()->id_rol ?? 0) !== 3)
                <div x-data="{ open: false }" class="relative" @click.away="open = false">
                    <button @click="open = !open" class="relative p-2 rounded-full hover:bg-gray-100 transition focus:outline-none">
                        <i class="bi bi-bell text-xl {{ $adminUnreadAdjustmentNotesCount > 0 ? 'text-[#1565C0]' : 'text-gray-400' }}"></i>
                        @if($adminUnreadAdjustmentNotesCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow animate-pulse">{{ $adminUnreadAdjustmentNotesCount }}</span>
                        @endif
                    </button>

                    <div x-show="open" x-cloak x-transition
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                        <div class="px-4 py-3 bg-gradient-to-r from-[#1565C0] to-[#0D47A1] text-white font-semibold text-sm flex items-center gap-2">
                            <i class="bi bi-bell-fill"></i> Notas de Ajuste Pendientes
                        </div>
                        <ul class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
                            @foreach($adminUnreadAdjustmentNotes as $noti)
                            <li>
                                <a href="{{ route('admin.notas-ajuste.show', $noti) }}" class="block px-4 py-3 hover:bg-blue-50 transition">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $noti->usuario->primer_nombre ?? 'Empleado' }} {{ $noti->usuario->primer_apellido ?? '' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ Illuminate\Support\Str::limit($noti->mensaje, 60) }}</p>
                                    <p class="text-[10px] text-gray-400 mt-1">{{ optional($noti->created_at)->diffForHumans() }}</p>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('admin.notas-ajuste.index') }}" class="block text-center text-sm font-semibold text-[#1565C0] py-3 hover:bg-gray-50 border-t border-gray-100 transition">
                            Ver todas las notas
                        </a>
                    </div>
                </div>
                @endif
            </div>
            <div class="flex-grow bg-white p-4 sm:p-6 rounded-xl shadow-lg border border-gray-100">
                @yield('content') {{-- Aquí va el contenido de cada módulo --}}
            </div>
        </div>

    </main>
</div>

<script>
function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('hidden');
        
        // Evitar scroll del body cuando el sidebar está abierto
        if (sidebar.classList.contains('mobile-open')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    }
}

// Cerrar sidebar al cambiar de tamaño a desktop
window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar && overlay) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }
});
</script>

@stack('modals')

@stack('scripts')

@if(session('needs_first_period'))
<div id="modalFirstPeriod" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-white/20">
        <div class="p-8 text-center">
            <div class="mb-6 inline-flex items-center justify-center w-20 h-20 bg-blue-50 text-blue-600 rounded-2xl">
                <i class="bi bi-calendar-check text-4xl"></i>
            </div>
            
            <h2 class="text-3xl font-extrabold text-gray-900 mb-4">¡Bienvenido a Nomitech!</h2>
            <p class="text-gray-600 mb-8 leading-relaxed">
                Para comenzar a gestionar su nómina, por favor seleccione el periodo de liquidación con el que desea iniciar.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <button onclick="selectFirstPeriod('current')" 
                    class="group relative p-6 bg-white border-2 border-gray-100 rounded-2xl text-left hover:border-blue-500 hover:bg-blue-50 transition-all duration-300 shadow-sm hover:shadow-md">
                    <span class="block text-xs font-bold uppercase text-gray-400 group-hover:text-blue-600 mb-1">Opción 1</span>
                    <span class="block text-lg font-bold text-gray-800">Mes Actual</span>
                    <span class="block text-sm text-gray-500 mt-1">{{ now()->translatedFormat('F Y') }}</span>
                    <i class="bi bi-check-circle-fill absolute top-4 right-4 text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </button>

                <button onclick="selectFirstPeriod('next')" 
                    class="group relative p-6 bg-white border-2 border-gray-100 rounded-2xl text-left hover:border-blue-500 hover:bg-blue-50 transition-all duration-300 shadow-sm hover:shadow-md">
                    <span class="block text-xs font-bold uppercase text-gray-400 group-hover:text-blue-600 mb-1">Opción 2</span>
                    <span class="block text-lg font-bold text-gray-800">Mes Siguiente</span>
                    <span class="block text-sm text-gray-500 mt-1">{{ now()->addMonth()->translatedFormat('F Y') }}</span>
                    <i class="bi bi-check-circle-fill absolute top-4 right-4 text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </button>
            </div>

            <p class="text-xs text-gray-400">
                <i class="bi bi-info-circle mr-1"></i> Esta elección solo se realiza una vez para configurar su ciclo inicial.
            </p>
        </div>
        
        <div id="firstPeriodLoader" class="hidden absolute inset-0 bg-white/80 flex flex-col items-center justify-center z-50">
            <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-4 text-blue-600 font-bold">Configurando periodos...</p>
        </div>
    </div>
</div>

<script>
function selectFirstPeriod(choice) {
    const loader = document.getElementById('firstPeriodLoader');
    loader.classList.remove('hidden');

    fetch("{{ route('periodos.store-first') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ choice: choice })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            loader.classList.add('hidden');
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(err => {
        loader.classList.add('hidden');
        Swal.fire('Error', 'No se pudo configurar el periodo.', 'error');
    });
}
</script>
@endif

<x-loader />

</body>
</html>
