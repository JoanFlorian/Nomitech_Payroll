<aside class="w-64 bg-blue-900 border-r border-blue-800 h-screen flex flex-col shadow-lg">

    <!-- HEADER -->
    <div class="flex items-center gap-3 p-6 border-b border-blue-800">
        <div class="bg-blue-600 text-white p-3 rounded-lg flex items-center justify-center">
            <i class="bi bi-grid-fill text-xl"></i>
        </div>
        <div>
            <h1 class="text-lg font-bold text-white">Nomitech</h1>
            <p class="text-sm text-blue-200">Super Administrador</p>
        </div>
    </div>

    <!-- MENU -->
    <nav class="flex-1 p-4 flex flex-col gap-1">

        <a href="{{ url('/superadmin') }}"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
        {{ request()->is('superadmin') ? 'bg-blue-800 text-white font-semibold' : '' }}">
            <i class="bi bi-house-fill text-lg"></i>
            Inicio
        </a>

        <a href="{{ route('superadmin.empresas') }}"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
        {{ request()->is('superadmin/empresas') ? 'bg-blue-800 text-white font-semibold' : '' }}">
            <i class="bi bi-building text-lg"></i>
            Empresas
        </a>

        <a href="{{ route('superadmin.facturacion') }}"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
        {{ request()->is('superadmin/facturacion') ? 'bg-blue-800 text-white font-semibold' : '' }}">
            <i class="bi bi-receipt text-lg"></i>
            Facturación
        </a>

        <a href="{{ route('superadmin.configuracion') }}"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
        {{ request()->is('superadmin/configuracion') ? 'bg-blue-800 text-white font-semibold' : '' }}">
            <i class="bi bi-gear text-lg"></i>
            Configuración
        </a>

        <a href="{{ route('superadmin.crear-planes') }}"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
        {{ request()->is('superadmin/crear-planes') ? 'bg-blue-800 text-white font-semibold' : '' }}">
            <i class="bi bi-plus-circle text-lg"></i>
            Crear planes
        </a>
    </nav>

    <!-- FOOTER -->
    <div class="p-6 border-t border-blue-800">
        <a href="{{ route('logout') }}"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-red-300 hover:bg-red-900/30 hover:text-red-200 transition">
            <i class="bi bi-box-arrow-right text-lg"></i>
            Cerrar sesión
        </a>
    </div>

</aside>