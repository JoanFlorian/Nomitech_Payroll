@extends('layouts.superadmin')

@section('content')
    <div class="p-6 bg-gray-50"></div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Directorio de Empresas</h2>
            <p class="text-sm text-gray-500">Gestiona y revisa el estado de las empresas</p>
        </div>

        <form method="GET" class="flex gap-2">
            <div class="relative">
                <input type="text" name="buscar" placeholder="Buscar por nombre o NIT"
                    class="pl-10 pr-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    value="{{ request('buscar') }}">
                <i class="bi bi-search absolute left-3 top-2.5 text-gray-400"></i>
            </div>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-lg transition">
                Buscar
            </button>
        </form>
    </div>

    <!-- Filtros -->
    @php $estadoActual = request('estado', 'todas'); @endphp
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach (['todas' => 'Todas', 'activa' => 'Activas', 'por_vencer' => 'Por vencer', 'vencida' => 'Vencidas'] as $key => $label)
            <a href="?estado={{ $key }}"
                class="px-4 py-1.5 rounded-full text-sm border transition
                       {{ $estadoActual === $key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-blue-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Tarjetas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($empresas as $empresa)

            @php
                $estado = $empresa->licencia->estado ?? 'pendiente_pago';
            @endphp

            <div class="bg-white p-5 rounded-xl shadow-sm border hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex justify-between mb-2 items-center">
                        <h3 class="font-semibold text-gray-900 text-lg">
                            {{ $empresa->razon_social }}
                        </h3>

                        <span class="text-xs px-2 py-1 rounded-full font-medium
                                    {{ $estado == 'activa' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $estado == 'por_vencer' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $estado == 'vencida' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $estado == 'pendiente_pago' ? 'bg-blue-100 text-blue-700' : '' }}
                                ">
                            {{ strtoupper(str_replace('_', ' ', $estado)) }}
                        </span>
                    </div>

                    <div class="space-y-1 text-sm text-gray-600">
                        <p><strong>NIT:</strong> {{ $empresa->nit }}</p>
                        <p><strong>Plan:</strong> {{ optional($empresa->licencia->plan)->nombre ?? 'Demo' }}</p>
                        <p><strong>Vence:</strong> {{ $empresa->licencia->fecha_fin ?? 'No aplica' }}</p>
                    </div>
                </div>

                <a href="{{ route('superadmin.empresas.show', $empresa->id_empresa) }}"
                    class="mt-4 inline-flex items-center gap-2 text-blue-600 text-sm font-medium hover:underline">
                    Ver detalles
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

        @empty
            <div class="col-span-full bg-white border rounded-xl p-10 text-center text-gray-500">
                <i class="bi bi-building text-4xl mb-2"></i>
                <p>No se encontraron empresas.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $empresas->links() }}
    </div>
@endsection