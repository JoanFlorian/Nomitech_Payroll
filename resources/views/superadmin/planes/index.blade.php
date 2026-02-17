@extends('layouts.superadmin')

@section('content')
    <div class="p-6 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">

        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-sm">
                        <i class="bi bi-layers-fill text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Planes de Licenciamiento</h2>
                        <p class="text-sm text-gray-500">
                            Gestiona los paquetes y precios disponibles para las empresas.
                        </p>
                    </div>
                </div>

                <a href="{{ route('superadmin.planes.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition shadow-sm hover:shadow-md">
                    <i class="bi bi-plus-lg"></i>
                    Crear nuevo plan
                </a>
            </div>

            @if(session('success'))
                <div
                    class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Precio
                                </th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Duración
                                </th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Límite
                                    Empleados</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($planes as $plan)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ $plan->nombre }}</div>
                                        @if($plan->destacado)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 mt-1">
                                                Destacado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-700 text-nowrap">
                                            ${{ number_format($plan->valor, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600">
                                            {{ $plan->duracion }} {{ Str::plural('mes', $plan->duracion) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600">
                                            {{ $plan->num_empl }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            @if($plan->stripe_price_id)
                                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                                <span class="text-xs text-green-700 font-medium">Conectado a Stripe</span>
                                            @else
                                                <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                                <span class="text-xs text-gray-500 font-medium">Borrador</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('superadmin.planes.edit', $plan) }}"
                                            class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-800 transition">
                                            <i class="bi bi-pencil-square"></i>
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                                                <i class="bi bi-info-circle text-gray-300 text-3xl"></i>
                                            </div>
                                            <p class="text-gray-500 font-medium">No se encontraron planes registrados.</p>
                                            <a href="{{ route('superadmin.planes.create') }}"
                                                class="text-blue-600 hover:underline mt-2 text-sm font-semibold">
                                                Crea el primer plan aquí
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection