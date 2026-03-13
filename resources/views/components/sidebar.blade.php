<aside
    class="w-64 bg-[#1565C0] border-r border-[#0D47A1] h-screen sticky top-0 self-start flex-shrink-0 flex flex-col shadow-lg">

    <!-- HEADER -->
    <div class="flex items-center gap-3 p-6 border-b border-[#0D47A1]">
        <div class="bg-[#1976D2] text-white p-3 rounded-lg flex items-center justify-center">
            <i class="bi bi-shield-lock text-xl"></i>
        </div>
        <div>
            <h1 class="text-lg font-bold text-white">Nomitech</h1>
            <p class="text-sm text-blue-200">{{ Auth::user()->rol->nombre ?? 'Usuario' }}</p>
        </div>
    </div>

    <!-- MENU -->
    <nav class="flex-1 p-4 flex flex-col gap-1 overflow-y-auto">

        @if(strtolower(Auth::user()->rol?->nombre ?? '') === 'trabajador')
            {{-- ── Portal del Trabajador ── --}}
            <p class="px-4 pt-2 pb-1 text-xs font-semibold uppercase tracking-widest text-blue-300">Mi Portal</p>

            <a href="{{ route('trabajador.dashboard') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->routeIs('trabajador.dashboard') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-house text-lg"></i>
                Dashboard
            </a>

            <a href="{{ route('trabajador.desprendibles') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->routeIs('trabajador.desprendibles') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-file-earmark-text text-lg"></i>
                Mis Desprendibles
            </a>

            <a href="{{ route('trabajador.notas') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->routeIs('trabajador.notas') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-journal-check text-lg"></i>
                Notas de Ajuste
            </a>

            <a href="{{ route('trabajador.perfil') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->routeIs('trabajador.perfil') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-person-circle text-lg"></i>
                Mi Perfil
            </a>
        @else
            {{-- ── Módulos de Administrador ── --}}
            <a href="{{ route('reportes.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('reportes*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-bar-chart text-lg"></i>
                Reportes
            </a>

            <a href="/empleados" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('empleados*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-people text-lg"></i>
                Empleados
            </a>

            <a href="{{ route('nomina.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('nomina*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-receipt text-lg"></i>
                Nómina
            </a>

            <a href="{{ route('nomina-electronica.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('nomina-electronica*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-send-check text-lg"></i>
                Nómina Electrónica
            </a>

            <a href="{{ route('novedades.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('novedades*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-journal-text text-lg"></i>
                Novedades
            </a>

            <a href="{{ route('provisiones.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->routeIs('provisiones.index') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-box-seam text-lg"></i>
                Provisiones
            </a>

            <a href="{{ route('periodos.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
               {{ request()->is('periodos*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                <i class="bi bi-calendar3 text-lg"></i>
                Periodos de Liquidación
            </a>

            @if(in_array((int) (Auth::user()->id_rol ?? 0), [1, 4], true))
                <a href="{{ route('admin.catalogos.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-[#0D47A1] hover:text-white transition
                    {{ request()->routeIs('admin.catalogos.*') ? 'bg-[#0D47A1] text-white font-semibold' : '' }}">
                    <i class="bi bi-collection text-lg"></i>
                    Catalogos de Empresa
                </a>
            @endif
        @endif

    </nav>

    <!-- FOOTER -->
    <div class="mt-auto p-6 border-t border-[#0D47A1]">
        <a href="{{ route('logout') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg text-red-300 hover:bg-red-900/30 hover:text-red-200 transition">
            <i class="bi bi-box-arrow-right text-lg"></i>
            Cerrar sesión
        </a>
    </div>

</aside>