<aside
    id="sidebar"
    class="sidebar bg-[#1565C0] border-r border-[#0D47A1] h-screen sticky top-0 self-start flex-shrink-0 flex flex-col shadow-lg transition-all duration-300 ease-in-out"
    onmouseenter="expandSidebar()"
    onmouseleave="collapseSidebar()">

    <!-- HEADER -->
    <div class="sidebar-header flex items-center gap-3 p-6 border-b border-[#0D47A1] overflow-hidden">
        <div class="sidebar-icon-container bg-[#1976D2] text-white p-3 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="bi bi-shield-lock text-xl"></i>
        </div>
        <div class="sidebar-header-text">
            <h1 class="text-lg font-bold text-white whitespace-nowrap">Nomitech</h1>
            <p class="text-sm text-blue-200 whitespace-nowrap">{{ Auth::user()->rol->nombre ?? 'Usuario' }}</p>
        </div>
    </div>

    <!-- MENU -->
    <nav class="flex-1 p-4 flex flex-col gap-1 overflow-y-auto overflow-x-hidden">

        @if(strtolower(Auth::user()->rol?->nombre ?? '') === 'trabajador')
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
            <a href="{{ route('reportes.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('reportes*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-bar-chart text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Reportes</span>
            </a>

            <a href="/empleados" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('empleados*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-people text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Empleados</span>
            </a>

            <a href="{{ route('nomina.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('nomina*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-receipt text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Nómina</span>
            </a>

            <a href="{{ route('nomina-electronica.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('nomina-electronica*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-send-check text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Nómina Electrónica</span>
            </a>

            <a href="{{ route('novedades.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('novedades*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-journal-text text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Novedades</span>
            </a>

            <a href="{{ route('provisiones.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->routeIs('provisiones.index') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-box-seam text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Provisiones</span>
            </a>

            <a href="{{ route('periodos.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('periodos*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-calendar3 text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Periodos de Liquidación</span>
            </a>

            <a href="{{ url('/pila') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('pila*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="menu-icon bi bi-file-earmark-text text-lg flex-shrink-0"></i>
                <span class="menu-text whitespace-nowrap">Plantilla PILA</span>
            </a>

            @if(in_array((int) (Auth::user()->id_rol ?? 0), [1, 4], true))
                <a href="{{ route('admin.catalogos.index') }}" class="menu-item flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                    {{ request()->routeIs('admin.catalogos.*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="menu-icon bi bi-collection text-lg flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap">Catalogos de Empresa</span>
                </a>
            @endif
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
    /* ==================== SIDEBAR COLAPSABLE ==================== */
    
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

    /* Suavizar transiciones de ancho */
    .sidebar * {
        transition-property: all;
        transition-timing-function: ease-in-out;
    }

    /* Evitar líneas cortadas en textos */
    .sidebar .menu-text,
    .sidebar .sidebar-header-text h1,
    .sidebar .sidebar-header-text p {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Ajustar el icono del header */
    .sidebar-icon-container {
        min-width: 46px;
        min-height: 46px;
    }
</style>

<script>
    // ==================== FUNCIONES DE COLAPSO DEL SIDEBAR ====================
    
    function expandSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.add('sidebar-expanded');
        }
    }

    function collapseSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.remove('sidebar-expanded');
        }
    }

    // Inicializar el sidebar en modo colapsado al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.remove('sidebar-expanded');
        }
    });
</script>