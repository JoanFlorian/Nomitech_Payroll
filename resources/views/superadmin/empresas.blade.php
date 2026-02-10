@extends('layouts.superadmin')

@section('content')
<div class="p-6">

    <!-- Header -->
    <div class="flex justify-between mb-6">
        <h2 class="text-xl font-bold">Directorio de Empresas</h2>

        <form method="GET" class="flex gap-2">
            <input type="text" name="buscar" placeholder="Buscar por nombre o NIT"
                   class="border rounded px-3 py-2 text-sm" value="{{ request('buscar') }}">
            <button class="bg-blue-600 text-white px-4 rounded">Buscar</button>
        </form>
    </div>

    <!-- Filtros -->
    <div class="flex gap-3 mb-6">
        <a href="?estado=todas" class="px-3 py-1 border rounded">Todas</a>
        <a href="?estado=activa" class="px-3 py-1 border rounded">Activas</a>
        <a href="?estado=por_vencer" class="px-3 py-1 border rounded">Por vencer</a>
        <a href="?estado=vencida" class="px-3 py-1 border rounded">Vencidas</a>
    </div>

    <!-- Tarjetas -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($empresas as $empresa)

            @php
                $estado = $empresa->licencia->estado ?? 'prueba';
            @endphp

            <div class="bg-white p-5 rounded-xl shadow border">
                <div class="flex justify-between mb-2 items-center">
                    <h3 class="font-semibold text-gray-800">
                        {{ $empresa->razon_social }}
                    </h3>

                    <span class="text-xs px-2 py-1 rounded
                        {{ $estado == 'activa' ? 'bg-green-100 text-green-600' : '' }}
                        {{ $estado == 'por_vencer' ? 'bg-yellow-100 text-yellow-600' : '' }}
                        {{ $estado == 'vencida' ? 'bg-red-100 text-red-600' : '' }}
                        {{ $estado == 'prueba' ? 'bg-blue-100 text-blue-600' : '' }}
                    ">
                        {{ strtoupper(str_replace('_',' ', $estado)) }}
                    </span>
                </div>

                <p class="text-sm text-gray-500">
                    NIT: {{ $empresa->nit }}
                </p>

                <p class="text-sm">
                    Plan: {{ optional($empresa->licencia->plan)->nombre ?? 'Demo' }}
                </p>

                <p class="text-sm">
                    Vence: {{ $empresa->licencia->fecha_fin ?? 'No aplica' }}
                </p>

                <a href="{{ route('superadmin.empresas.show', $empresa->id_empresa) }}"
               class="block mt-3 text-blue-600 text-sm hover:underline">
               Ver detalles
                </a>


            </div>

        @empty
            <p class="col-span-4 text-center text-gray-500">
                No se encontraron empresas.
            </p>
        @endforelse
    </div>

</div>
@endsection
