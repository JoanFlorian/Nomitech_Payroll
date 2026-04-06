<aside class="w-64 bg-blue-900 border-r border-blue-800 h-screen flex flex-col shadow-lg overflow-y-auto">

    <!-- HEADER -->
    <div class="flex items-center gap-3 p-6 border-b border-blue-800">
        <div class="bg-white rounded-lg flex items-center justify-center overflow-hidden w-12 h-12">
            <img src="{{ asset('images/logo nomitech.jpeg') }}" alt="Nomitech" class="object-contain w-full h-full" />
        </div>
        <div>
            <h1 class="text-lg font-bold text-white">Nomitech</h1>
            <p class="text-sm text-blue-200">{{ Auth::user()->rol->nombre ?? 'Usuario' }}</p>
        </div>
    </div>

    <!-- MENU -->
    <nav class="flex-1 p-4 flex flex-col gap-1">

        <a href="{{ route('superadmin.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
           {{ request()->routeIs('superadmin.index') ? 'bg-blue-800 text-white font-semibold' : '' }}">
            <i class="bi bi-house-fill text-lg"></i>
            Inicio
        </a>

        <a href="{{ route('superadmin.empresas.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
           {{ request()->is('superadmin/empresas') ? 'bg-blue-800 text-white font-semibold' : '' }}">
            <i class="bi bi-building text-lg"></i>
            Empresas
        </a>

        <a href="{{ route('superadmin.facturacion') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
            {{ request()->routeIs('superadmin.facturacion') ? 'bg-blue-800 text-white font-semibold' : '' }}">
            <i class="bi bi-receipt text-lg"></i>
            Facturación
        </a>



        <a href="{{ route('superadmin.planes.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
            {{ request()->routeIs('superadmin.planes.*') ? 'bg-blue-800 text-white font-semibold' : '' }}">
            <i class="bi bi-layers text-lg"></i>
            Planes
        </a>

        @if((int) (Auth::user()->id_rol ?? 0) === 4)
            <a href="{{ route('superadmin.actualizaciones.principal') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
                {{ request()->routeIs('superadmin.actualizaciones.principal') ? 'bg-blue-800 text-white font-semibold' : '' }}">
                <i class="bi bi-arrow-repeat text-lg"></i>
                Actualizaciones
            </a>
        @endif

        @if((int) (Auth::user()->id_rol ?? 0) === 1)
            <a href="{{ route('admin.catalogos.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg text-blue-100 hover:bg-blue-800 hover:text-white transition
                {{ request()->routeIs('admin.catalogos.*') ? 'bg-blue-800 text-white font-semibold' : '' }}">
                <i class="bi bi-collection text-lg"></i>
                Catalogos de Empresa
            </a>
        @endif

    </nav>

    <!-- FOOTER -->
    <div class="p-6 border-t border-blue-800">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex items-center gap-3 px-4 py-2 rounded-lg text-red-300 hover:bg-red-900/30 hover:text-red-200 transition w-full text-left">
                <i class="bi bi-box-arrow-right text-lg"></i>
                Cerrar sesión
            </button>
        </form>
    </div>

</aside>
