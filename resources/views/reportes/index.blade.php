@extends('layouts.app')

@section('title', 'Reportes')
@section('page-title', 'Dashboard de Reportes')

@section('content')
@php
    $costoTotalNomina = (float) ($resumen->costo_total_nomina ?? 0);
    $empleadosActivos = (int) ($resumen->empleados_activos ?? 0);
    $salarioNetoPromedio = (float) ($resumen->salario_neto_promedio ?? 0);
    $aportesSeguridadSocial = (float) ($resumen->aportes_seguridad_social ?? 0);
    $totalDeducciones = (float) ($resumen->total_deducciones ?? 0);
    $maxEvolucion = max(1, (float) $evolucion->max('total'));
@endphp

<div class="space-y-6">
    <form method="GET" action="{{ route('reportes.index') }}" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full lg:max-w-3xl">
            <select name="periodo" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="all" {{ $periodoSeleccionado === 'all' ? 'selected' : '' }}>Periodo (todos)</option>
                @foreach ($periodosDisponibles as $periodo)
                    <option value="{{ $periodo }}" {{ $periodoSeleccionado === $periodo ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $periodo)->locale('es')->translatedFormat('F Y') }}
                    </option>
                @endforeach
            </select>

            <select name="tipo_contrato" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="all" {{ $tipoContratoSeleccionado === 'all' ? 'selected' : '' }}>Tipo de contrato (todos)</option>
                @foreach ($tiposContrato as $tipo)
                    <option value="{{ $tipo->id_tipo_contrato }}" {{ (string) $tipoContratoSeleccionado === (string) $tipo->id_tipo_contrato ? 'selected' : '' }}>
                        {{ $tipo->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" formaction="{{ route('reportes.index') }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-3 rounded-xl shadow">
                <i class="bi bi-funnel"></i>
                Aplicar
            </button>
            <button type="submit" formaction="{{ route('reportes.export.pdf') }}" class="inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-3 rounded-xl shadow">
                <i class="bi bi-file-earmark-pdf"></i>
                PDF
            </button>
            <button type="submit" formaction="{{ route('reportes.export.excel') }}" class="inline-flex items-center justify-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-5 py-3 rounded-xl shadow">
                <i class="bi bi-file-earmark-excel"></i>
                Excel
            </button>
        </div>
    </form>

    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-800">Resumen de Nómina</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-11 h-11 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center"><i class="bi bi-currency-dollar"></i></span>
                    <p class="text-gray-600 text-sm">Costo total de nómina</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($costoTotalNomina, 0, ',', '.') }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-11 h-11 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center"><i class="bi bi-people"></i></span>
                    <p class="text-gray-600 text-sm">Empleados activos</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $empleadosActivos }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-11 h-11 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center"><i class="bi bi-wallet2"></i></span>
                    <p class="text-gray-600 text-sm">Salario neto promedio</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($salarioNetoPromedio, 0, ',', '.') }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-11 h-11 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center"><i class="bi bi-shield-check"></i></span>
                    <p class="text-gray-600 text-sm">Aportes seguridad social</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($aportesSeguridadSocial, 0, ',', '.') }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-11 h-11 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center"><i class="bi bi-arrow-down"></i></span>
                    <p class="text-gray-600 text-sm">Total deducciones</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($totalDeducciones, 0, ',', '.') }}</p>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <article class="xl:col-span-2 bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Evolución de Nómina</h3>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span>Anual</span>
                    <span class="inline-flex items-center w-14 h-8 p-1 rounded-full bg-emerald-500">
                        <span class="w-6 h-6 bg-white rounded-full ml-auto"></span>
                    </span>
                    <span class="font-semibold text-emerald-600">Mensual</span>
                </div>
            </div>

            <div class="border border-gray-100 rounded-xl bg-gray-50 p-4">
                <div class="h-72 w-full border border-gray-200 rounded-lg bg-gray-100 p-4 overflow-y-auto">
                    @if ($evolucion->isEmpty())
                        <div class="h-full flex items-center justify-center text-gray-400">Sin datos para la selección actual</div>
                    @else
                        <div class="space-y-3">
                            @foreach ($evolucion as $item)
                                @php
                                    $porcentaje = min(100, max(2, ($item['total'] / $maxEvolucion) * 100));
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                        <span class="font-semibold uppercase">{{ $item['label'] }}</span>
                                        <span>${{ number_format($item['total'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="h-3 rounded-full bg-gray-200 overflow-hidden">
                                        <div class="h-3 rounded-full bg-blue-500" style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <p class="text-center text-sm text-gray-500 mt-3">Costo total de nómina representado en millones de pesos (M).</p>
            </div>
        </article>

        <article class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Desglose de Nómina</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center"><i class="bi bi-wallet2"></i></span>
                        <span class="text-gray-700 font-medium">Salarios</span>
                    </div>
                    <span class="font-bold text-gray-900">${{ number_format($desglose['salarios'], 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="bi bi-gift"></i></span>
                        <span class="text-gray-700 font-medium">Bonificaciones</span>
                    </div>
                    <span class="font-bold text-gray-900">${{ number_format($desglose['bonificaciones'], 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center"><i class="bi bi-cash"></i></span>
                        <span class="text-gray-700 font-medium">Prestaciones Sociales</span>
                    </div>
                    <span class="font-bold text-gray-900">${{ number_format($desglose['prestaciones_sociales'], 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-orange-100 text-orange-700 flex items-center justify-center"><i class="bi bi-file-earmark-text"></i></span>
                        <span class="text-gray-700 font-medium">Provisiones</span>
                    </div>
                    <span class="font-bold text-gray-900">${{ number_format($desglose['provisiones'], 0, ',', '.') }}</span>
                </div>
            </div>
        </article>
    </section>
</div>
@endsection
