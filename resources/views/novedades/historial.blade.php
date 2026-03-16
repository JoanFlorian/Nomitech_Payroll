@extends('layouts.app')

@section('title', 'Historial contrato')
@section('page-title', 'HISTORIAL CONTRATO')

@section('content')
@php
    $historial = $historial ?? collect();
    $periodos = $periodos ?? collect();
    $filtroPeriodo = $filtroPeriodo ?? 'todos';
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-sm flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Historial de cambios de contrato</h2>
            <p class="text-sm text-gray-500 mt-1">
                Aquí se muestran los cambios de EPS, AFP, ARL y salario registrados por el sistema de novedades.
            </p>
            @if(isset($empresaId) && $empresaId)
                <p class="mt-1 text-xs text-gray-400">Empresa en sesión: {{ $empresaId }}</p>
            @endif
        </div>
        <a href="{{ route('novedades.index') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
            <span class="material-icons text-[18px]">arrow_back</span>
            Volver a novedades
        </a>
    </div>

    <!-- Filtro por período -->
    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label for="periodo" class="block text-sm font-semibold text-gray-700 mb-2">Filtrar por período de liquidación:</label>
                <select
                    name="periodo"
                    id="periodo"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm"
                >
                    <option value="todos" @selected($filtroPeriodo === 'todos')>Todos los períodos</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id_periodo }}" @selected($filtroPeriodo == $periodo->id_periodo)>
                            {{ \Carbon\Carbon::parse($periodo->fecha_inicio)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($periodo->fecha_fin)->format('d/m/Y') }} ({{ ucfirst($periodo->estado) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button
                type="submit"
                class="px-6 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors font-semibold text-sm"
            >
                Filtrar
            </button>
            @if($filtroPeriodo !== 'todos')
                <a href="{{ route('novedades.historial') }}" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium">
                    Limpiar
                </a>
            @endif
        </form>
        @if($filtroPeriodo !== 'todos')
            <p class="mt-2 text-xs text-gray-500">Mostrando {{ $historial->count() }} registro(s) para el período seleccionado.</p>
        @else
            <p class="mt-2 text-xs text-gray-500">Mostrando {{ $historial->count() }} registro(s) en total.</p>
        @endif
    </div>

    <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3">Contrato</th>
                        <th class="px-4 py-3">Empleado</th>
                        <th class="px-4 py-3">Dato anterior</th>
                        <th class="px-4 py-3">Dato nuevo</th>
                        <th class="px-4 py-3">Tipo novedad</th>
                        <th class="px-4 py-3">Fecha cambio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($historial as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-800 text-xs">{{ $item->id_contrato }}</td>
                            <td class="px-4 py-2 text-gray-800 text-xs">
                                @if($item->nombre_completo)
                                    {{ \Illuminate\Support\Str::title($item->nombre_completo) }}<br>
                                @endif
                                <span class="text-[11px] text-gray-500">{{ $item->doc ?? '' }}</span>
                            </td>
                            <td class="px-4 py-2 text-gray-800 text-xs">{{ $item->dato_anterior_label ?? $item->dato_anterior }}</td>
                            <td class="px-4 py-2 text-gray-800 text-xs">{{ $item->dato_nuevo_label ?? $item->dato_nuevo }}</td>
                            <td class="px-4 py-2 text-gray-800 text-xs">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                    @if($item->tipo_novedad === 'EPS') bg-blue-50 text-blue-700 
                                    @elseif($item->tipo_novedad === 'AFP') bg-emerald-50 text-emerald-700 
                                    @elseif($item->tipo_novedad === 'ARL') bg-purple-50 text-purple-700 
                                    @else bg-amber-50 text-amber-700 @endif
                                ">
                                    {{ $item->tipo_novedad }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-800 text-xs">
                                {{ \Carbon\Carbon::parse($item->fecha_cambio)->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-xs text-gray-500">No hay registros de historial de contrato.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
