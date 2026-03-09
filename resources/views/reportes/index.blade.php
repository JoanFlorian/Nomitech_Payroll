@extends('layouts.app')

@section('title', 'Reportes')
@section('page-title', 'Dashboard de Reportes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
@endpush

@section('content')
@php
    $costoTotalNomina = (float) ($resumen->costo_total_nomina ?? 0);
    $empleadosActivos = (int) ($resumen->empleados_activos ?? 0);
    $salarioNetoPromedio = (float) ($resumen->salario_neto_promedio ?? 0);
    $aportesSeguridadSocial = (float) ($resumen->aportes_seguridad_social ?? 0);
    $totalDeducciones = (float) ($resumen->total_deducciones ?? 0);
    $maxEvolucion = max(1, (float) $evolucion->max('total'));
    $evolucionAnual = $evolucionAnual ?? collect();
    $maxEvolucionAnual = max(1, (float) $evolucionAnual->max('total'));
@endphp



    <form id="form-filtros-reportes" method="GET" action="{{ route('reportes.index') }}" class="filters-panel glass-card rounded-2xl p-4 md:p-5 rise-in flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full lg:max-w-3xl">
            <div class="filter-field">
                <label for="periodo" class="filter-label">Periodo</label>
                <div class="select-shell">
                    <select id="periodo" name="periodo" onchange="this.form.submit()" class="report-select">
                        <option value="all" {{ $periodoSeleccionado === 'all' ? 'selected' : '' }}>Todos los periodos</option>
                        @foreach ($periodosDisponibles as $periodo)
                            <option value="{{ $periodo }}" {{ $periodoSeleccionado === $periodo ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $periodo)->locale('es')->translatedFormat('F Y') }}
                            </option>
                        @endforeach
                    </select>
                    <i class="bi bi-chevron-down select-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="filter-field">
                <label for="tipo_contrato" class="filter-label">Tipo de contrato</label>
                <div class="select-shell">
                    <select id="tipo_contrato" name="tipo_contrato" onchange="this.form.submit()" class="report-select">
                        <option value="all" {{ $tipoContratoSeleccionado === 'all' ? 'selected' : '' }}>Todos los tipos</option>
                        @foreach ($tiposContrato as $tipo)
                            <option value="{{ $tipo->id_tipo_contrato }}" {{ (string) $tipoContratoSeleccionado === (string) $tipo->id_tipo_contrato ? 'selected' : '' }}>
                                {{ $tipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <i class="bi bi-chevron-down select-icon" aria-hidden="true"></i>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" formaction="{{ route('reportes.export.pdf') }}" class="inline-flex items-center justify-center gap-2 bg-[#f59e0b] hover:bg-[#d97706] text-white font-semibold px-5 py-3 rounded-xl shadow-lg shadow-amber-900/10">
                <i class="bi bi-file-earmark-pdf"></i>
                PDF
            </button>
            <button type="submit" formaction="{{ route('reportes.export.excel') }}" class="inline-flex items-center justify-center gap-2 bg-[#1f415d] hover:bg-[#173147] text-white font-semibold px-5 py-3 rounded-xl shadow-lg shadow-slate-900/10">
                <i class="bi bi-file-earmark-excel"></i>
                Excel
            </button>
        </div>
    </form>

    <section class="space-y-4 rise-in">
        <h2 class="text-2xl font-bold text-[#12263a]">Resumen de N&oacute;mina</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="metric-card glass-card rounded-2xl p-4">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-11 h-11 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center"><i class="bi bi-currency-dollar"></i></span>
                    <p class="text-[#3a5a77] text-sm">Costo total de n&oacute;mina</p>
                </div>
                <p class="text-2xl font-bold text-[#12263a]">${{ number_format($costoTotalNomina, 0, ',', '.') }}</p>
            </div>

            <div class="metric-card glass-card rounded-2xl p-4">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-11 h-11 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center"><i class="bi bi-people"></i></span>
                    <p class="text-[#3a5a77] text-sm">Empleados activos</p>
                </div>
                <p class="text-2xl font-bold text-[#12263a]">{{ $empleadosActivos }}</p>
            </div>

            <div class="metric-card glass-card rounded-2xl p-4">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-11 h-11 rounded-full bg-cyan-100 text-cyan-700 flex items-center justify-center"><i class="bi bi-wallet2"></i></span>
                    <p class="text-[#3a5a77] text-sm">Salario neto promedio</p>
                </div>
                <p class="text-2xl font-bold text-[#12263a]">${{ number_format($salarioNetoPromedio, 0, ',', '.') }}</p>
            </div>

            <div class="metric-card glass-card rounded-2xl p-4">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="bi bi-shield-check"></i></span>
                    <p class="text-[#3a5a77] text-sm">Aportes seguridad social</p>
                </div>
                <p class="text-2xl font-bold text-[#12263a]">${{ number_format($aportesSeguridadSocial, 0, ',', '.') }}</p>
            </div>

            <div class="metric-card glass-card rounded-2xl p-4">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-11 h-11 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center"><i class="bi bi-arrow-down"></i></span>
                    <p class="text-[#3a5a77] text-sm">Total deducciones</p>
                </div>
                <p class="text-2xl font-bold text-[#12263a]">${{ number_format($totalDeducciones, 0, ',', '.') }}</p>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-3 gap-4 rise-in">
        <article class="xl:col-span-2 glass-card rounded-2xl p-5 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-2xl font-bold text-[#12263a]">Evoluci&oacute;n de N&oacute;mina</h3>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span id="label-anual" class="font-medium text-gray-600">Anual</span>
                    <button type="button" id="toggle-evolucion" role="switch" aria-checked="true" aria-label="Cambiar entre vista anual y mensual"
                        class="inline-flex items-center w-14 h-8 p-1 rounded-full bg-teal-700 transition">
                        <span id="toggle-knob" class="w-6 h-6 bg-white rounded-full ml-auto transition"></span>
                    </button>
                    <span id="label-mensual" class="font-semibold text-[#0f766e]">Mensual</span>
                </div>
            </div>

            <div class="border border-teal-100 rounded-2xl bg-gradient-to-b from-[#f8fcff] to-[#eef7f8] p-4">
                <div id="chart-mensual" class="h-72 w-full border border-teal-100 rounded-xl bg-white/80 p-4">
                    @if ($evolucion->isEmpty())
                        <div class="h-full flex items-center justify-center text-gray-400">Sin datos para la selecci&oacute;n actual</div>
                    @else
                        <div class="h-full flex items-end gap-3 overflow-x-auto pb-2">
                            @foreach ($evolucion as $item)
                                @php
                                    $porcentaje = min(100, max(8, ($item['total'] / $maxEvolucion) * 100));
                                @endphp
                                <div class="bar-card min-w-[84px] h-full flex flex-col justify-end">
                                    <div class="text-center text-[11px] font-semibold text-[#35546f] mb-2">
                                        ${{ number_format($item['total'], 0, ',', '.') }}
                                    </div>
                                    <div class="h-full min-h-[180px] flex items-end bg-[#e8f4f6] rounded-lg overflow-hidden">
                                        <div class="w-full bg-gradient-to-t from-[#0f766e] to-[#14b8a6] rounded-t-lg transition-all" style="height: {{ $porcentaje }}%"></div>
                                    </div>
                                    <div class="text-center text-[11px] font-semibold uppercase text-[#46617a] mt-2">{{ $item['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div id="chart-anual" class="hidden h-72 w-full border border-teal-100 rounded-xl bg-white/80 p-4">
                    @if ($evolucionAnual->isEmpty())
                        <div class="h-full flex items-center justify-center text-gray-400">Sin datos anuales para la selección actual</div>
                    @else
                        <div class="h-full flex items-end gap-3 overflow-x-auto pb-2">
                            @foreach ($evolucionAnual as $item)
                                @php
                                    $porcentaje = min(100, max(8, ($item['total'] / $maxEvolucionAnual) * 100));
                                @endphp
                                <div class="bar-card min-w-[96px] h-full flex flex-col justify-end">
                                    <div class="text-center text-[11px] font-semibold text-[#35546f] mb-2">
                                        ${{ number_format($item['total'], 0, ',', '.') }}
                                    </div>
                                    <div class="h-full min-h-[180px] flex items-end bg-[#e8f4f6] rounded-lg overflow-hidden">
                                        <div class="w-full bg-gradient-to-t from-[#1f415d] to-[#2f658f] rounded-t-lg transition-all" style="height: {{ $porcentaje }}%"></div>
                                    </div>
                                    <div class="text-center text-[11px] font-semibold uppercase text-[#46617a] mt-2">{{ $item['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <p id="texto-evolucion" class="text-center text-sm text-[#4d6a84] mt-3">Total de n&oacute;mina liquidada por periodo (mensual).</p>
            </div>
        </article>

        <article class="glass-card rounded-2xl p-5 md:p-6">
            <h3 class="text-2xl font-bold text-[#12263a] mb-4">Desglose de N&oacute;mina</h3>
            <div class="space-y-3">
                <div class="metric-card flex items-center justify-between bg-[#f3faf9] border border-teal-100 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center"><i class="bi bi-wallet2"></i></span>
                        <span class="text-[#2b4b67] font-medium">Salarios</span>
                    </div>
                    <span class="font-bold text-[#12263a]">${{ number_format($desglose['salarios'], 0, ',', '.') }}</span>
                </div>

                <div class="metric-card flex items-center justify-between bg-[#fff8ec] border border-amber-100 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center"><i class="bi bi-gift"></i></span>
                        <span class="text-[#2b4b67] font-medium">Bonificaciones</span>
                    </div>
                    <span class="font-bold text-[#12263a]">${{ number_format($desglose['bonificaciones'], 0, ',', '.') }}</span>
                </div>

                <div class="metric-card flex items-center justify-between bg-[#f0f7ff] border border-sky-100 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center"><i class="bi bi-cash"></i></span>
                        <span class="text-[#2b4b67] font-medium">Prestaciones Sociales</span>
                    </div>
                    <span class="font-bold text-[#12263a]">${{ number_format($desglose['prestaciones_sociales'], 0, ',', '.') }}</span>
                </div>

                <div class="metric-card flex items-center justify-between bg-[#f7f8fb] border border-slate-200 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center"><i class="bi bi-file-earmark-text"></i></span>
                        <span class="text-[#2b4b67] font-medium">Provisiones</span>
                    </div>
                    <span class="font-bold text-[#12263a]">${{ number_format($desglose['provisiones'], 0, ',', '.') }}</span>
                </div>
            </div>
        </article>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('toggle-evolucion');
    const knob = document.getElementById('toggle-knob');
    const mensualChart = document.getElementById('chart-mensual');
    const anualChart = document.getElementById('chart-anual');
    const labelAnual = document.getElementById('label-anual');
    const labelMensual = document.getElementById('label-mensual');
    const texto = document.getElementById('texto-evolucion');

    if (!toggle || !knob || !mensualChart || !anualChart) return;

    let isMensual = true;

    const render = () => {
        mensualChart.classList.toggle('hidden', !isMensual);
        anualChart.classList.toggle('hidden', isMensual);
        toggle.setAttribute('aria-checked', isMensual ? 'true' : 'false');
        toggle.classList.toggle('bg-teal-700', isMensual);
        toggle.classList.toggle('bg-slate-700', !isMensual);
        knob.classList.toggle('ml-auto', isMensual);
        knob.classList.toggle('mr-auto', !isMensual);
        labelMensual.classList.toggle('font-semibold', isMensual);
        labelMensual.classList.toggle('text-teal-700', isMensual);
        labelMensual.classList.toggle('text-gray-600', !isMensual);
        labelAnual.classList.toggle('font-semibold', !isMensual);
        labelAnual.classList.toggle('text-slate-700', !isMensual);
        labelAnual.classList.toggle('text-gray-600', isMensual);
        if (texto) {
            texto.textContent = isMensual
                ? 'Total de nomina liquidada por periodo (mensual).'
                : 'Total de nomina liquidada acumulada por ano.';
        }
    };

    toggle.addEventListener('click', () => {
        isMensual = !isMensual;
        render();
    });

    render();
});
</script>
@endsection
