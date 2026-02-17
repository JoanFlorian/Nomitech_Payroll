@extends('layouts.superadmin')

@section('content')

    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Facturación y Transacciones</h1>
                <p class="text-gray-600 text-sm mt-1">Gestión integral de pagos y vigencia de licencias</p>
            </div>
            <span
                class="inline-flex items-center gap-2 bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-semibold">
                <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                EN LÍNEA
            </span>
        </div>

        <form method="GET" action="{{ route('superadmin.facturacion') }}" class="flex gap-4">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar empresa, referencia o proveedor..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('superadmin.facturacion') }}" class="flex gap-3 items-end">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-700 mb-1 uppercase tracking-wide">Estado</label>
                <select name="estado"
                    class="w-full border border-gray-300 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onchange="this.form.submit()">
                    <option value="Todos" {{ $estadoSeleccionado === 'Todos' ? 'selected' : '' }}>Todos</option>
                    <option value="paid" {{ $estadoSeleccionado === 'paid' ? 'selected' : '' }}>✓ Pagado</option>
                    <option value="pending" {{ $estadoSeleccionado === 'pending' ? 'selected' : '' }}>⏳ Pendiente</option>
                    <option value="failed" {{ $estadoSeleccionado === 'failed' ? 'selected' : '' }}>✗ Fallido</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-700 mb-1 uppercase tracking-wide">Proveedor</label>
                <select name="metodo"
                    class="w-full border border-gray-300 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onchange="this.form.submit()">
                    <option value="Todos" {{ $metodoSeleccionado === 'Todos' ? 'selected' : '' }}>Todos</option>
                    @foreach ($metodosDisponibles as $metodo)
                        <option value="{{ $metodo }}" {{ $metodoSeleccionado === $metodo ? 'selected' : '' }}>{{ $metodo }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-lg p-4 shadow-sm">
            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total Ingreso</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">${{ $totalTransacciones }}</h3>
            <p class="text-blue-600 text-xs mt-2">Ingresos procesados</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 border-l-4 border-green-500 rounded-lg p-4 shadow-sm">
            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Licencias Activas</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $suscripcionesActivas }}</h3>
            <p class="text-green-600 text-xs mt-2">Suscripciones vigentes</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 border-l-4 border-red-500 rounded-lg p-4 shadow-sm">
            <p class="text-red-600 text-xs font-semibold uppercase tracking-wide">Por Cobrar</p>
            <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $pendientesPago }}</h3>
            <p class="text-red-600 text-xs mt-2">Pagos pendientes</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
            <h2 class="text-base font-bold text-gray-900">Historial de Transacciones</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-gray-700">Empresa</th>
                        <th class="text-center px-6 py-3 font-semibold text-gray-700">Fecha</th>
                        <th class="text-center px-6 py-3 font-semibold text-gray-700">Vigencia</th>
                        <th class="text-center px-6 py-3 font-semibold text-gray-700">Método</th>
                        <th class="text-center px-6 py-3 font-semibold text-gray-700">Estado</th>
                        <th class="text-center px-6 py-3 font-semibold text-gray-700">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($transacciones as $pago)
                        @php
                            $licencia = $pago->licencia;
                            $empresa = $pago->empresa ?? $licencia->empresa;
                            $fechaFin = \Carbon\Carbon::parse($licencia->fecha_fin);
                            $diasRestantes = $fechaFin->diffInDays(\Carbon\Carbon::now(), false);

                            if ($diasRestantes < 0) {
                                $vigenciaColor = 'text-red-600 font-semibold';
                            } elseif ($diasRestantes <= 30) {
                                $vigenciaColor = 'text-yellow-600 font-semibold';
                            } else {
                                $vigenciaColor = 'text-green-600';
                            }

                            $estadoColor = match ($pago->estado_pago) {
                                'paid' => 'bg-green-100 text-green-700',
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'failed' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700'
                            };

                            $estadoTexto = match ($pago->estado_pago) {
                                'paid' => 'Pagado',
                                'pending' => 'Pendiente',
                                'failed' => 'Fallido',
                                default => $pago->estado_pago
                            };

                            $fechaPago = $pago->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : $pago->created_at->format('d/m/Y');
                        @endphp
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $empresa->razon_social ?? 'Sin especificar' }}
                            </td>
                            <td class="px-6 py-3 text-center text-gray-700">{{ $fechaPago }}</td>
                            <td class="px-6 py-3 text-center"><span
                                    class="{{ $vigenciaColor }}">{{ abs((int) floor($diasRestantes)) }} días</span></td>
                            <td class="px-6 py-3 text-center text-gray-700">{{ $pago->proveedor_pago ?? '—' }}</td>
                            <td class="px-6 py-3 text-center"><span
                                    class="{{ $estadoColor }} px-3 py-0.5 rounded-full text-xs font-semibold">{{ $estadoTexto }}</span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <button onclick="verFactura({{ $pago->id }})"
                                    class="text-blue-500 hover:text-blue-700 font-semibold" title="Ver factura">☰</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">No hay transacciones para mostrar</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-between items-center text-xs">
            <p class="text-gray-600">
                Mostrando {{ ($transacciones->currentPage() - 1) * $transacciones->perPage() + 1 }}
                a {{ min($transacciones->currentPage() * $transacciones->perPage(), $transacciones->total()) }}
                de {{ $transacciones->total() }}
            </p>
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                {{ $transacciones->withQueryString()->links() }}
            </div>

        </div>
    </div>

    @include('superadmin.factura')

@endsection