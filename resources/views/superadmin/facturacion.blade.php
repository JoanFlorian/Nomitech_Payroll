@extends('layouts.superadmin')

@section('content')

{{-- HEADER --}}
<div class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Detalles de compras y transacciones</h2>
        <p class="text-gray-500">
            Gestion integral del historial de pagos y vigencia de suscripciones corporativas.   
        </p>
    </div>

    <div class="flex items-center gap-4">
        <form method="GET" action="{{ route('superadmin.index') }}" class="flex">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Buscar transacciones o empresas..."
                class="px-4 py-2 border rounded-lg w-72 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </form>
        <span class="text-sm text-green-600 font-semibold flex items-center gap-1">
            ● ONLINE
        </span>
    </div>
</div>

{{-- MÉTRICAS --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm">
        <p class="text-sm text-gray-500">Total Transacciones</p>
        <h3 class="text-2xl font-bold mt-2">${{ $totalTransacciones }}</h3>
        <p class="text-sm text-green-600 mt-1">⬆ Ingresos del sistema</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm">
        <p class="text-sm text-gray-500">Suscripciones Activas</p>
        <h3 class="text-2xl font-bold mt-2">{{ $suscripcionesActivas }}</h3>
        <p class="text-sm text-green-600 mt-1">✔ Licencias vigentes</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm">
        <p class="text-sm text-gray-500">Pendientes de Pago</p>
        <h3 class="text-2xl font-bold mt-2">{{ $pendientesPago }}</h3>
        <p class="text-sm text-red-600 mt-1">⚠ Acción requerida</p>
    </div>
</div>

{{-- LISTADO --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold">Listado de Transacciones</h3>

        <form method="GET" action="{{ route('superadmin.index') }}" class="flex gap-2">
            <select name="estado" class="border rounded-lg px-3 py-2 text-sm bg-white" onchange="this.form.submit()">
                <option value="Todos" {{ $estadoSeleccionado === 'Todos' ? 'selected' : '' }}>Estado: Todos</option>
                <option value="succeeded" {{ $estadoSeleccionado === 'succeeded' ? 'selected' : '' }}>Estado: Pagado</option>
                <option value="pending" {{ $estadoSeleccionado === 'pending' ? 'selected' : '' }}>Estado: Pendiente</option>
                <option value="failed" {{ $estadoSeleccionado === 'failed' ? 'selected' : '' }}>Estado: Fallido</option>
            </select>

            <select name="metodo" class="border rounded-lg px-3 py-2 text-sm bg-white" onchange="this.form.submit()">
                <option value="Todos" {{ $metodoSeleccionado === 'Todos' ? 'selected' : '' }}>Proveedor: Todos</option>
                @foreach ($metodosDisponibles as $metodo)
                    <option value="{{ $metodo }}" {{ $metodoSeleccionado === $metodo ? 'selected' : '' }}>
                        Proveedor: {{ $metodo }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 border-b">
                <tr>
                    <th class="text-left py-3">Empresa</th>
                    <th class="text-center">Fecha</th>
                    <th class="text-center">Vigencia</th>
                    <th class="text-center">Método</th>
                    <th class="text-center">Estado</th>
                    <th></th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($transacciones as $pago)
                    @php
                        $licencia = $pago->licencia;
                        $empresa = $pago->empresa ?? $licencia->empresa;
                        
                        // Calcular vigencia
                        $fechaFin = \Carbon\Carbon::parse($licencia->fecha_fin);
                        $hoy = \Carbon\Carbon::now();
                        $diasRestantes = $fechaFin->diffInDays($hoy, false);
                        
                        // Determinar color de vigencia
                        if ($diasRestantes < 0) {
                            $vigenciaColor = 'text-red-600';
                        } elseif ($diasRestantes <= 30) {
                            $vigenciaColor = 'text-yellow-600';
                        } else {
                            $vigenciaColor = 'text-green-600';
                        }
                        
                        // Color del estado del pago
                        $estadoColor = match($pago->estado_pago) {
                            'succeeded' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'failed' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700'
                        };
                        
                        // Traducción del estado
                        $estadoTexto = match($pago->estado_pago) {
                            'succeeded' => 'Pagado',
                            'pending' => 'Pendiente',
                            'failed' => 'Fallido',
                            default => $pago->estado_pago
                        };
                        
                        $fechaPago = $pago->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : $pago->created_at->format('d/m/Y');
                    @endphp
                    <tr>
                        <td class="py-4 font-medium">{{ $empresa->razon_social ?? 'Sin especificar' }}</td>
                        <td class="text-center">{{ $fechaPago }}</td>
                        <td class="text-center {{ $vigenciaColor }}">
                            {{ abs($diasRestantes) }} días
                        </td>
                        <td class="text-center">{{ $pago->proveedor_pago ?? 'No especificado' }}</td>
                        <td class="text-center">
                            <span class="{{ $estadoColor }} px-3 py-1 rounded-full text-xs">
                                {{ $estadoTexto }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button onclick="verFactura({{ $pago->id }})" class="text-gray-400 hover:text-gray-600 cursor-pointer text-xl">⋮</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">
                            No hay transacciones para mostrar
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MODAL FACTURA --}}
    @include('superadmin.factura')

    {{-- PAGINACIÓN --}}
    <div class="flex justify-between items-center mt-6 text-sm">
        <p class="text-gray-500">
            Mostrando {{ ($transacciones->currentPage() - 1) * $transacciones->perPage() + 1 }} a {{ min($transacciones->currentPage() * $transacciones->perPage(), $transacciones->total()) }} de {{ $transacciones->total() }} transacciones
        </p>

        <div class="flex gap-2">
            {{-- Botón Anterior --}}
            @if ($transacciones->onFirstPage())
                <button class="px-3 py-1 border rounded text-gray-400 cursor-not-allowed" disabled>Anterior</button>
            @else
                <a href="{{ $transacciones->previousPageUrl() }}" class="px-3 py-1 border rounded hover:bg-gray-100">Anterior</a>
            @endif

            {{-- Números de página --}}
            @for ($i = 1; $i <= $transacciones->lastPage(); $i++)
                @if ($i == $transacciones->currentPage())
                    <button class="px-3 py-1 bg-blue-600 text-white rounded">{{ $i }}</button>
                @else
                    <a href="{{ $transacciones->url($i) }}" class="px-3 py-1 border rounded hover:bg-gray-100">{{ $i }}</a>
                @endif

                {{-- Mostrar solo 5 páginas --}}
                @if ($i >= $transacciones->currentPage() + 2)
                    @break
                @endif
            @endfor

            {{-- Botón Siguiente --}}
            @if ($transacciones->hasMorePages())
                <a href="{{ $transacciones->nextPageUrl() }}" class="px-3 py-1 border rounded hover:bg-gray-100">Siguiente</a>
            @else
                <button class="px-3 py-1 border rounded text-gray-400 cursor-not-allowed" disabled>Siguiente</button>
            @endif
        </div>
    </div>
</div>

@endsection
