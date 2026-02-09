<aside class="w-64 bg-white border-r h-screen flex flex-col shadow-lg">

    <!-- HEADER -->
    <div class="flex items-center gap-3 p-6 border-b">
        <div class="bg-blue-600 text-white p-3 rounded-lg flex items-center justify-center">
            <i class="bi bi-grid-fill text-xl"></i>
        </div>
        <div>
            <h1 class="text-lg font-bold text-gray-800">Nomitech</h1>
            <p class="text-sm text-gray-500">Super Administrador</p>
        </div>
    </div>

    <!-- MENU -->
    <nav class="flex-1 p-4 flex flex-col gap-1">

        <!-- Inicio -->
        <a href="{{ url('/superadmin') }}"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600
        {{ request()->is('superadmin') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
            <i class="bi bi-house-fill text-lg"></i>
            Inicio
        </a>

        <!-- Empresas -->
        <a href="#"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="bi bi-building text-lg"></i>
            Empresas
        </a>

        <!-- Facturación -->
        <a href="#"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="bi bi-receipt text-lg"></i>
            Facturación
        </a>

        <!-- Configuración -->
        <a href="#"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="bi bi-gear text-lg"></i>
            Configuración
        </a>

        <!-- Crear planes -->
        <a href="#"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600">
            <i class="bi bi-plus-circle text-lg"></i>
            Crear planes
        </a>

    </nav>

    <!-- FOOTER / LOGOUT OPCIONAL -->
    <div class="p-6 border-t">
        <a href="#"
        class="flex items-center gap-3 px-4 py-2 rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-600">
            <i class="bi bi-box-arrow-right text-lg"></i>
            Cerrar sesión
        </a>
    </div>

</aside>