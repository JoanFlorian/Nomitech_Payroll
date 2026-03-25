<aside
    id="sidebar"
    class="sidebar bg-[#1565C0] border-r border-[#0D47A1] h-screen sticky top-0 self-start flex-shrink-0 flex flex-col shadow-lg transition-all duration-300 ease-in-out fixed lg:sticky z-40 group"
    onmouseenter="expandSidebar()"
    onmouseleave="collapseSidebar()">

    <!-- HEADER -->
    <div class="sidebar-header flex items-center gap-3 p-4 border-b border-[#0D47A1] overflow-hidden">
        <div class="sidebar-brand-compact sidebar-icon-container rounded-xl group-hover:rounded-none transition-all duration-300 overflow-hidden bg-transparent flex-shrink-0 w-12 h-12">
            <img
                src="{{ asset('images/logo_nomitech_blanco.svg') }}"
                alt="Nomitech"
                class="object-contain w-full h-full"
            >
        </div>
        <div class="sidebar-brand-full flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 flex-shrink-0 rounded-xl group-hover:rounded-none transition-all duration-300 overflow-hidden bg-transparent">
                <img
                    src="{{ asset('images/logo_nomitech_blanco.svg') }}"
                    alt="Nomitech"
                    class="object-contain w-full h-full"
                >
            </div>
            @php
                $authUser = Auth::user();
                $nombresUsuario = trim((string) (($authUser->primer_nombre ?? '') . ' ' . ($authUser->otros_nombres ?? '')));
                $apellidosUsuario = trim((string) (($authUser->primer_apellido ?? '') . ' ' . ($authUser->segundo_apellido ?? '')));
                $nombreCompletoUsuario = trim((string) ($nombresUsuario . ' ' . $apellidosUsuario));
                if (strtolower($authUser->rol?->nombre ?? '') === 'empleado') {
                    $empresaNombre = optional(
                        $authUser->contratos()
                            ->where('activo', 1)
                            ->latest('id_contrato')
                            ->with('empresa')
                            ->first()
                    )->empresa?->razon_social
                        ?? optional(
                            $authUser->contratos()
                                ->latest('id_contrato')
                                ->with('empresa')
                                ->first()
                        )->empresa?->razon_social
                        ?? 'Empresa no asignada';
                } else {
                    $empresaNombre = optional($authUser->empresa()->first())->razon_social ?? 'Empresa no asignada';
                }
            @endphp
            <div class="min-w-0">
                @if($nombresUsuario !== '' && $apellidosUsuario !== '')
                    <p class="text-sm font-bold text-white leading-tight truncate" title="{{ $nombreCompletoUsuario }}">{{ $nombresUsuario }}</p>
                    <p class="text-xs text-blue-100 leading-tight truncate" title="{{ $nombreCompletoUsuario }}">{{ $apellidosUsuario }}</p>
                @else
                    <p class="text-sm font-bold text-white leading-tight truncate" title="{{ $nombreCompletoUsuario !== '' ? $nombreCompletoUsuario : ($authUser->nombre ?? 'Usuario') }}">
                        {{ $nombreCompletoUsuario !== '' ? $nombreCompletoUsuario : ($authUser->nombre ?? 'Usuario') }}
                    </p>
                @endif
                <p class="text-xs text-blue-200 truncate">{{ Auth::user()->rol->nombre ?? 'Sin rol' }}</p>
                <p class="text-xs text-blue-100 truncate">{{ $empresaNombre }}</p>
            </div>
        </div>
    </div>

    <!-- MENU -->
    <nav class="flex-1 p-4 flex flex-col gap-1 overflow-y-auto overflow-x-hidden">

        @if(strtolower(Auth::user()->rol?->nombre ?? '') === 'empleado')
            {{-- ── Portal del Trabajador ── --}}
            <p class="sidebar-section-title px-4 pt-2 pb-1 text-xs font-semibold uppercase tracking-widest text-blue-300 whitespace-nowrap">Mi Portal</p>

            <a href="{{ route('trabajador.dashboard') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->routeIs('trabajador.dashboard') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-house text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Dashboard</span>
            </a>

            <a href="{{ route('trabajador.desprendibles') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->routeIs('trabajador.desprendibles') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-file-earmark-text text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Mis Desprendibles</span>
            </a>

            <a href="{{ route('trabajador.notas') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->routeIs('trabajador.notas') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-journal-check text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Notas de Ajuste</span>
            </a>

            <a href="{{ route('trabajador.perfil') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->routeIs('trabajador.perfil') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-person-circle text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Mi Perfil</span>
            </a>
        @else
            {{-- ── Módulos de Administrador ── --}}
            @can('view_reports')
                <a href="{{ route('reportes.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                   {{ request()->is('reportes*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-bar-chart text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Reportes</span>
                </a>
            @endcan

            @can('view_employees')
                <a href="/empleados" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                   {{ request()->is('empleados*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-people text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Empleados</span>
                </a>
            @endcan

            @can('view_payroll')
                <a href="{{ route('nomina.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                   {{ request()->is('nomina*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-receipt text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Nómina</span>
                </a>
            @endcan

            @can('view_electronic_payroll')
                <a href="{{ route('nomina-electronica.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                   {{ request()->is('nomina-electronica*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-send-check text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Nómina Electrónica</span>
                </a>
            @endcan

            @can('view_novedades')
                <a href="{{ route('novedades.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                   {{ request()->is('novedades*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-journal-text text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Novedades</span>
                </a>
            @endcan

            @can('view_provisions')
                <a href="{{ route('provisiones.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                   {{ request()->routeIs('provisiones.index') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-box-seam text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Provisiones</span>
                </a>
            @endcan

            @can('view_periods')
                <a href="{{ route('periodos.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                   {{ request()->is('periodos*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-calendar3 text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Periodos de Liquidación</span>
                </a>
            @endcan

            @can('view_pila')
                <a href="{{ url('/pila') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                   {{ request()->is('pila*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-file-earmark-text text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Plantilla PILA</span>
                </a>
            @endcan

            @can('manage_catalogos')
                <a href="{{ route('admin.notas-ajuste.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                    {{ request()->routeIs('admin.notas-ajuste.*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-bell text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Notas de Ajuste</span>
                </a>
                @if(in_array(strtolower(Auth::user()->rol?->nombre ?? ''), ['admin', 'representante legal']))
                <a href="{{ route('admin.catalogos.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                    {{ request()->routeIs('admin.catalogos.*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-collection text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Catálogos de Empresa</span>
                </a>
                @endif
                <a href="{{ route('admin.roles.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                    {{ request()->routeIs('admin.roles.*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-shield-check text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Roles y Permisos</span>
                </a>
            @endcan
        @endif

    </nav>

    <!-- FOOTER -->
    <div class="sidebar-footer mt-auto p-6 border-t border-[#0D47A1] overflow-hidden">
        <a href="{{ route('logout') }}"
            class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-red-300 hover:bg-red-900/30 hover:text-red-200 transition">
            <i class="menu-icon bi bi-box-arrow-right text-lg flex-shrink-0"></i>
            <span class="menu-text whitespace-nowrap">Cerrar sesión</span>
        </a>
    </div>

</aside>

<style>
    /* ==================== SIDEBAR SCROLLBAR ==================== */
    .sidebar nav {
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.2) transparent;
    }
    .sidebar nav::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar nav::-webkit-scrollbar-track {
        background: transparent;
        margin: 8px 0;
    }
    .sidebar nav::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.18);
        border-radius: 9999px;
    }
    .sidebar nav:hover::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.35);
    }
    .sidebar nav::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.5);
    }

    /* ==================== SIDEBAR COLAPSABLE ==================== */
    
    /* RESPONSIVE: M\u00f3viles - sidebar oculto por defecto */
    @media (max-width: 1023px) {
        .sidebar {
            width: 250px !important;
            left: -250px;
            top: 0;
            bottom: 0;
        }
        
        .sidebar.mobile-open {
            left: 0;
        }
        
        /* En m\u00f3viles siempre mostrar textos */
        .sidebar .menu-text,
        .sidebar .sidebar-header-text,
        .sidebar .sidebar-section-title {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .sidebar .menu-item,
        .sidebar .sidebar-footer a {
            justify-content: flex-start !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        
        .sidebar .menu-icon {
            font-size: 1.125rem !important;
        }
        
        .sidebar .sidebar-header {
            justify-content: flex-start !important;
        }
        
        .sidebar .sidebar-section-title {
            font-size: 0.75rem !important;
            padding: 0.5rem 1rem 0.25rem 1rem !important;
            height: auto !important;
        }
        
        /* Desactivar hover en m\u00f3viles */
        .sidebar {
            pointer-events: auto;
        }
    }
    
    /* DESKTOP: comportamiento normal con hover */
    @media (min-width: 1024px) {
        /* Estado por defecto: Colapsado */
        .sidebar {
            width: 70px;
        }

        /* Estado expandido al hacer hover */
        .sidebar.sidebar-expanded {
            width: 250px;
        }

        /* Ocultar textos en modo colapsado */
        .sidebar .menu-text,
        .sidebar .sidebar-header-text,
        .sidebar .sidebar-section-title {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease-in-out, visibility 0.2s ease-in-out;
        }

        /* Mostrar textos en modo expandido */
        .sidebar.sidebar-expanded .menu-text,
        .sidebar.sidebar-expanded .sidebar-header-text,
        .sidebar.sidebar-expanded .sidebar-section-title {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease-in-out 0.1s, visibility 0.3s ease-in-out 0.1s;
        }

        /* Iconos más grandes y centrados en modo colapsado */
        .sidebar .menu-icon {
            font-size: 1.5rem;
            transition: font-size 0.3s ease-in-out;
        }

        .sidebar.sidebar-expanded .menu-icon {
            font-size: 1.125rem;
        }

        /* Centrar items en modo colapsado */
        .sidebar .menu-item {
            justify-content: center;
            transition: justify-content 0.3s ease-in-out;
        }

        .sidebar.sidebar-expanded .menu-item {
            justify-content: flex-start;
        }

        /* Ajustes para header en modo colapsado */
        .sidebar .sidebar-header {
            justify-content: center;
            transition: justify-content 0.3s ease-in-out;
        }

        .sidebar.sidebar-expanded .sidebar-header {
            justify-content: flex-start;
        }

        /* Ajustar padding en modo colapsado */
        .sidebar .menu-item,
        .sidebar .sidebar-footer a {
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar.sidebar-expanded .menu-item,
        .sidebar.sidebar-expanded .sidebar-footer a {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        /* Ocultar sección de título en modo colapsado */
        .sidebar .sidebar-section-title {
            font-size: 0;
            padding: 0;
            margin: 0;
            height: 0;
        }

        .sidebar.sidebar-expanded .sidebar-section-title {
            font-size: 0.75rem;
            padding: 0.5rem 1rem 0.25rem 1rem;
            height: auto;
        }
    }

    /* Reglas generales para todos los tamaños */
    .sidebar * {
        transition-property: all;
        transition-timing-function: ease-in-out;
    }

    .sidebar .menu-text {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-brand-full {
        display: none;
    }

    .sidebar-brand-compact {
        display: flex;
    }

    .sidebar-icon-container {
        width: 54px;
        height: 54px;
        padding: 0.25rem;
    }

    @media (min-width: 1024px) {
        /* Colapsado: mostrar solo icono compacto */
        .sidebar:not(.sidebar-expanded) .sidebar-brand-full {
            display: none;
        }

        .sidebar:not(.sidebar-expanded) .sidebar-brand-compact {
            display: flex;
        }

        /* Expandido: mostrar logo pequeño + info usuario */
        .sidebar.sidebar-expanded .sidebar-brand-full {
            display: flex;
        }

        .sidebar.sidebar-expanded .sidebar-brand-compact {
            display: none;
        }

        .sidebar:not(.sidebar-expanded) .sidebar-icon-container {
            width: 54px;
            height: 54px;
            padding: 0.25rem;
        }

        .sidebar:not(.sidebar-expanded) .sidebar-header {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    }
</style>

<script>
    // ==================== FUNCIONES DE COLAPSO DEL SIDEBAR ====================
    
    function _isModalOpen() {
        return [...document.querySelectorAll('.fixed.inset-0')].some(el => {
            // 1. Saltar elementos decorativos marcados como aria-hidden
            if (el.getAttribute('aria-hidden') === 'true') return false;
            // 2. Saltar elementos no renderizados (display:none vía clase o inline, o dentro de parent hidden)
            if (el.offsetWidth === 0 && el.offsetHeight === 0) return false;
            // 3. Solo contar si tiene fondo de overlay (backdrop semitransparente real)
            const bg = window.getComputedStyle(el).backgroundColor;
            return bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent';
        });
    }

    function expandSidebar() {
        if (_isModalOpen()) return;
        // Solo expandir en desktop
        if (window.innerWidth >= 1024) {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.add('sidebar-expanded');
            }
        }
    }

    function collapseSidebar() {
        // Solo colapsar en desktop
        if (window.innerWidth >= 1024) {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.remove('sidebar-expanded');
            }
        }
    }

    // Inicializar el sidebar en modo colapsado al cargar la página (solo desktop)
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth >= 1024) {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.remove('sidebar-expanded');
            }
        }
    });
</script>