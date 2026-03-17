<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo nomitech.jpeg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo nomitech.jpeg') }}">
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

    /* Scroll moderno global */
    * {
        scrollbar-width: thin;
        scrollbar-color: #1565C0 #E7EEF7;
    }

    *::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    *::-webkit-scrollbar-track {
        background: linear-gradient(180deg, #f4f8fc 0%, #e7eef7 100%);
        border-radius: 999px;
    }

    *::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #1565C0 0%, #0D47A1 100%);
        border-radius: 999px;
        border: 2px solid #f4f8fc;
    }

    *::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #1E73D8 0%, #1565C0 100%);
    }

    .modern-scroll {
        scroll-behavior: smooth;
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
    <main class="flex-1 min-h-screen bg-white relative overflow-hidden rounded-l-3xl shadow-2xl w-full lg:w-auto">
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
        
        <div class="h-full w-full flex flex-col p-4 sm:p-6 lg:p-8 z-10 relative">
            @php($hideLayoutHeader = trim($__env->yieldContent('hide-layout-header')) !== '')

            @if (! $hideLayoutHeader)
                <div class="mt-12 lg:mt-0 mb-4 sm:mb-6 lg:mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">@yield('page-title')</h1>

                    <div class="flex items-center justify-end gap-3">
                        @if(Auth::check() && (int) (Auth::user()->id_rol ?? 0) !== 3)
                            <div x-data="{ open: false }" class="relative" x-cloak>
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="relative inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 shadow-sm transition hover:border-[#1565C0] hover:text-[#1565C0]"
                                    aria-label="Notificaciones de notas de ajuste"
                                >
                                    <i class="bi bi-bell text-lg"></i>
                                    @if(($adminUnreadAdjustmentNotesCount ?? 0) > 0)
                                        <span class="absolute -right-2 -top-2 min-w-[1.5rem] rounded-full bg-red-500 px-2 py-0.5 text-center text-xs font-bold text-white">
                                            {{ $adminUnreadAdjustmentNotesCount > 99 ? '99+' : $adminUnreadAdjustmentNotesCount }}
                                        </span>
                                    @endif
                                </button>

                                <div
                                    x-show="open"
                                    @click.away="open = false"
                                    x-transition
                                    class="absolute right-0 z-50 mt-3 w-[22rem] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl"
                                >
                                    <div class="border-b border-gray-100 px-4 py-3">
                                        <p class="text-sm font-semibold text-gray-900">Notas de ajuste</p>
                                        <p class="text-xs text-gray-500">{{ $adminUnreadAdjustmentNotesCount ?? 0 }} pendientes por revisar</p>
                                    </div>

                                    <div class="max-h-96 overflow-y-auto">
                                        @forelse(($adminUnreadAdjustmentNotes ?? collect()) as $notificationNote)
                                            <a
                                                href="{{ route('admin.notas-ajuste.show', $notificationNote) }}"
                                                class="block border-b border-gray-100 px-4 py-3 transition hover:bg-blue-50"
                                            >
                                                <p class="text-sm font-semibold text-gray-800">
                                                    {{ $notificationNote->usuario?->nombre_completo ?? 'Trabajador no disponible' }}
                                                </p>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Nota enviada {{ optional($notificationNote->created_at)->diffForHumans() }}
                                                </p>
                                                <p class="mt-1 text-xs text-blue-700">
                                                    {{ $notificationNote->salario?->periodo?->nombre ? 'Periodo: ' . $notificationNote->salario->periodo->nombre : 'Sin periodo asociado' }}
                                                </p>
                                            </a>
                                        @empty
                                            <div class="px-4 py-6 text-center text-sm text-gray-500">
                                                No hay notas de ajuste nuevas.
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="bg-gray-50 px-4 py-3 text-right">
                                        <a href="{{ route('admin.notas-ajuste.index') }}" class="text-sm font-medium text-[#1565C0] hover:text-[#0D47A1]">
                                            Ver todas las notas
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @auth
                            <div class="hidden rounded-xl border border-gray-200 bg-white px-4 py-3 text-right shadow-sm sm:block">
                                <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->nombre_completo ?? ((Auth::user()->primer_nombre ?? 'Usuario') . ' ' . (Auth::user()->primer_apellido ?? '')) }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::user()->rol->nombre ?? 'Sin rol' }}</p>
                            </div>
                        @endauth
                    </div>
                </div>
            @endif

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

</body>
</html>
