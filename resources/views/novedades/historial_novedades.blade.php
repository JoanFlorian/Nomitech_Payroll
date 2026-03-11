@extends('layouts.app')

@section('title', 'Historial de Novedades')
@section('page-title', 'HISTORIAL DE NOVEDADES')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="relative overflow-hidden rounded-xl bg-white p-6 border border-gray-100 shadow-sm">
        <div class="pointer-events-none absolute -top-10 -right-10 h-44 w-44 rounded-full bg-blue-100/50"></div>
        
        <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
            <div class="max-w-3xl">
                <p class="text-gray-500 leading-relaxed">
                    Consulta el historial completo de todas las novedades registradas. Filtra por período para ver los cambios realizados.
                </p>
            </div>
            
            <a
                href="{{ route('novedades.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-full border border-emerald-500 px-5 py-2. 5 text-xs font-semibold text-emerald-600 bg-white shadow-sm hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
            >
                <span class="material-icons text-[18px]">close</span>
                Cerrar
            </a>
        </div>
    </div>

    @if (isset($empresaId))
        <p class="mt-2 text-xs text-gray-400">
            Empresa en sesión: {{ $empresaId }} | Registros totales: {{ ($novedades ?? collect())->count() }}
        </p>
    @endif

    <!-- Filtro por período -->
    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label for="periodo" class="block text-sm font-semibold text-gray-700 mb-2">Filtrar por período:</label>
                <select
                    name="periodo"
                    id="periodo"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                >
                    <option value="todos" @selected($filtroPeriodo === 'todos')>Todos los períodos</option>
                    <option value="sin_periodo" @selected($filtroPeriodo === 'sin_periodo')>Sin período asignado</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id_periodo }}" @selected($filtroPeriodo == $periodo->id_periodo)>
                            {{ \Carbon\Carbon::parse($periodo->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodo->fecha_fin)->format('d/m/Y') }} ({{ ucfirst($periodo->estado) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button
                type="submit"
                class="px-6 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors font-semibold"
            >
                Filtrar
            </button>
        </form>
    </div>

    @if ($novedades->count() === 0)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-6 py-8 text-center">
            <p class="text-amber-700 font-semibold">No hay novedades para mostrar</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Empleado</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Fecha Inicio</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Fecha Fin</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wide">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Observaciones</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Periodo</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wide">Registrado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($novedades as $novedad)
                        @php
                            $periodo = $novedad->periodoLiquidacion;
                            $salario = $novedad->salario;
                            $usuario = $salario?->contrato?->usuario;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ \Illuminate\Support\Str::title(trim(collect([
                                            $usuario->primer_nombre ?? null,
                                            $usuario->otros_nombres ?? null,
                                            $usuario->primer_apellido ?? null,
                                            $usuario->segundo_apellido ?? null,
                                        ])->filter()->implode(' '))) }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $usuario->doc ?? '—' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                    {{ \Illuminate\Support\Str::before($novedad->tipo_novedad_nombre, ' - ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $novedad->fecha_inicio ? \Carbon\Carbon::parse($novedad->fecha_inicio)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $novedad->fecha_fin ? \Carbon\Carbon::parse($novedad->fecha_fin)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                                {{ number_format($novedad->valor_novedad, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                {{ $novedad->observaciones ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if ($periodo)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">
                                        {{ \Carbon\Carbon::parse($periodo->fecha_inicio)->format('d/m') }} - {{ \Carbon\Carbon::parse($periodo->fecha_fin)->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Sin período</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $novedad->created_at ? \Carbon\Carbon::parse($novedad->created_at)->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
