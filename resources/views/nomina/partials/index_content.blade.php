@php
    $salarios = $salarios ?? collect();
    $periodoActivo = $periodoActivo ?? $periodo ?? null;
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap');

    .nomina-shell {
        --nomina-bg: #f4f7fb;
        --nomina-ink: #14213d;
        --nomina-brand: #0a4af4;
        --nomina-brand-soft: #d9e6ff;
        --nomina-success: #078a5b;
        --nomina-danger: #c62828;
        font-family: 'Manrope', sans-serif;
        background:
            radial-gradient(circle at 10% 12%, rgba(10, 74, 244, 0.12), transparent 28%),
            radial-gradient(circle at 88% 4%, rgba(19, 195, 174, 0.14), transparent 24%),
            linear-gradient(165deg, #f8fbff 0%, var(--nomina-bg) 100%);
    }

    .nomina-shell h2,
    .nomina-shell h3,
    .nomina-shell h4 {
        font-family: 'Sora', sans-serif;
    }

    .nomina-reveal {
        animation: nominaSlideUp 520ms ease both;
    }

    .nomina-reveal-delay {
        animation: nominaSlideUp 650ms ease both;
    }

    .nomina-table tbody tr {
        transition: background-color 180ms ease, transform 180ms ease;
    }

    .nomina-table tbody tr.nomina-row-selected {
        background: linear-gradient(90deg, rgba(10, 74, 244, 0.12), rgba(10, 74, 244, 0.03));
        box-shadow: inset 4px 0 0 #0a4af4;
    }

    .nomina-table tbody tr.nomina-row-selected td {
        color: #0f172a;
    }

    .nomina-table tbody tr:hover {
        transform: translateY(-1px);
    }

    @keyframes nominaSlideUp {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="nomina-shell rounded-[30px] border border-slate-200/80 p-4 md:p-7 shadow-xl shadow-slate-200/50">
    @if($periodoActivo)
        <section class="nomina-reveal-delay mb-6 rounded-3xl border border-blue-100 bg-gradient-to-r from-blue-50 via-white to-cyan-50 p-4 md:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200">
                        <i class="bi bi-calendar-week text-lg"></i>
                    </div>

                    <div>
                        <h3 class="text-xs font-extrabold uppercase tracking-[0.2em] text-blue-800">Periodo de Liquidacion Activo</h3>
                        <p class="mt-1 text-lg font-extrabold text-slate-900">
                            {{ $periodoActivo->fecha_inicio->format('d/m/Y') }} - {{ $periodoActivo->fecha_fin->format('d/m/Y') }}
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-blue-200 px-3 py-1 text-[11px] font-bold uppercase text-blue-800">
                                {{ $periodoActivo->tipo_frecuencia }}
                            </span>
                            <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase {{ $periodoActivo->estado === 'abierto' ? 'bg-emerald-200 text-emerald-800' : 'bg-amber-200 text-amber-800' }}">
                                {{ $periodoActivo->estado }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('periodos.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-4 py-2 text-sm font-bold text-blue-700 transition hover:border-blue-400 hover:text-blue-900">
                        <i class="bi bi-arrow-repeat"></i>
                        Cambiar Periodo
                    </a>

                    {{-- DEBUG: canClose={{ auth()->user()->can('close_period') ? 'true' : 'false' }} status={{ $periodoActivo->estado }} canBeClosed={{ $periodoActivo->canBeClosed() ? 'true' : 'false' }} --}}
                    @if(($periodoActivo->estado === 'abierto' || $periodoActivo->estado === 'pendiente') && $periodoActivo->canBeClosed())
                        @can('close_period')
                        <div class="flex items-center gap-3">
                            {{-- Cierre Automático Temporalmente Visible (Originalmente: Solo después del día 20) --}}
                            <div class="flex flex-col items-start mr-2">
                                <label class="text-[9px] uppercase font-bold text-slate-500 leading-none mb-1">Auto-Cierre</label>
                                <input type="date"
                                    value="{{ $periodoActivo->fecha_cierre_automatico ? $periodoActivo->fecha_cierre_automatico->format('Y-m-d') : '' }}"
                                    onchange="updateAutoClose({{ $periodoActivo->id_periodo }}, this.value)"
                                    class="text-[10px] border-slate-200 rounded-lg p-1.5 w-28 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                    title="Programar cierre automático">
                            </div>

                            <button type="button"
                                onclick="abrirModalCierre({{ $periodoActivo->id_periodo }}, '{{ $periodoActivo->fecha_inicio->format('d/m/Y') }}', '{{ $periodoActivo->fecha_fin->format('d/m/Y') }}')"
                                class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-md shadow-red-200 transition hover:bg-red-700">
                                <i class="bi bi-lock-fill"></i>
                                Cerrar Periodo
                            </button>
                        </div>
                        @endcan
                    @endif
                </div>
            </div>
        </section>
    @else
        <section class="mb-6 rounded-3xl border border-amber-200 bg-amber-50 p-4 md:p-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-200 text-amber-800">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-amber-900">No hay un periodo seleccionado.</p>
                        <p class="text-xs text-amber-700">Selecciona un periodo abierto para poder liquidar nomina.</p>
                    </div>
                </div>

                <a href="{{ route('periodos.index') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-amber-700">
                    Seleccionar Periodo
                </a>
            </div>
        </section>
    @endif

    <form method="GET" action="{{ route('nomina.index') }}" class="mb-6 rounded-3xl border border-slate-200 bg-white p-4 md:p-5 shadow-sm" data-loader data-loader-text="Exportando reporte...">
        <button type="submit" class="sr-only" tabindex="-1" aria-hidden="true">Buscar</button>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-8">
                <label class="mb-2 block text-[11px] font-extrabold uppercase tracking-[0.18em] text-slate-500">
                    Buscar Empleado
                </label>

                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input
                        type="text"
                        id="buscar-empleado"
                        name="documento"
                        list="empleados-sugeridos"
                        value="{{ request('documento') }}"
                        placeholder="Documento o nombres"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 transition hover:border-blue-400 focus:border-blue-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-100"
                    >
                    <datalist id="empleados-sugeridos"></datalist>
                </div>
            </div>

            <div class="lg:col-span-4 flex flex-wrap gap-2 lg:justify-end">
                @can('export_payroll')
                <button
                    type="submit"
                    formaction="{{ route('nomina.export.pdf') }}"
                    class="inline-flex min-w-[110px] items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-red-700"
                >
                    <i class="bi bi-filetype-pdf"></i>
                    PDF
                </button>

                <button
                    type="submit"
                    formaction="{{ route('nomina.export.excel') }}"
                    class="inline-flex min-w-[110px] items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                >
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    Excel
                </button>
                @endcan
            </div>
        </div>
    </form>

    <div class="mb-6 flex flex-wrap items-center gap-3">
        @can('calculate_payroll')
        <button type="button"
            id="btn-editar-empleado"
            title="Editar empleado seleccionado"
            disabled
            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-gray-300 px-4 text-sm font-bold text-white shadow-sm transition-all duration-200 cursor-not-allowed opacity-70">
            <i class="bi bi-pencil-square"></i>
            Editar
        </button>
        @endcan

        @can('calculate_payroll')
        <a href="{{ route('nomina.step1', ['fresh' => 1]) }}"
            title="Agregar empleado"
            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
            <i class="bi bi-plus-lg"></i>
            Agregar empleado
        </a>

        @if($periodoActivo && $periodoActivo->estado !== \App\Models\PeriodoLiquidacion::ESTADO_CERRADO)
            <form id="realizar-nomina-form" method="POST" action="{{ route('nomina.realizar') }}" data-loader data-loader-text="Calculando nómina masiva...">
                @csrf
                <button type="submit"
                    title="Nómina masiva"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">
                    <i class="bi bi-calculator-fill"></i>
                    Nómina Masiva
                </button>
            </form>
        @endif
        @endcan
    </div>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 md:px-6">
            <h4 class="text-sm font-extrabold uppercase tracking-[0.14em] text-slate-600">Resultados de Liquidacion</h4>
        </div>

        <div class="overflow-x-auto">
            <table class="nomina-table min-w-[860px] w-full text-sm">
                <thead class="bg-slate-900 text-slate-100">
                    <tr>
                        <th class="sticky top-0 z-10 bg-slate-900 px-4 py-3 text-center font-semibold tracking-wide w-14">Sel.</th>
                        <th class="sticky top-0 z-10 bg-slate-900 px-4 py-3 text-left font-semibold tracking-wide">Documento</th>
                        <th class="sticky top-0 z-10 bg-slate-900 px-4 py-3 text-left font-semibold tracking-wide">Empleado</th>
                        <th class="sticky top-0 z-10 bg-slate-900 px-4 py-3 text-center font-semibold tracking-wide">Estado</th>
                        <th class="sticky top-0 z-10 bg-slate-900 px-4 py-3 text-center font-semibold tracking-wide">Días</th>
                        <th class="sticky top-0 z-10 bg-slate-900 px-4 py-3 text-right font-semibold tracking-wide">Salario inicial</th>
                        <th class="sticky top-0 z-10 bg-slate-900 px-4 py-3 text-right font-semibold tracking-wide">Devengos</th>
                        <th class="sticky top-0 z-10 bg-slate-900 px-4 py-3 text-right font-semibold tracking-wide">Deducciones</th>
                        <th class="sticky top-0 z-10 bg-slate-900 px-4 py-3 text-right font-semibold tracking-wide">Novedades</th>
                        <th class="sticky top-0 z-10 bg-slate-900 px-4 py-3 text-right font-semibold tracking-wide">Salario final</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($salarios as $salario)
                        <tr data-doc="{{ $salario->contrato->usuario->doc }}"
                            data-salario-id="{{ $salario->id_salario }}"
                            class="nomina-row cursor-pointer hover:bg-blue-50/55">

                            <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                                <input type="radio"
                                    name="selected_nomina"
                                    class="nomina-select-radio h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500"
                                    value="{{ $salario->id_salario }}"
                                    aria-label="Seleccionar empleado {{ $salario->contrato->usuario->nombre_completo }}">
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700">
                                {{ $salario->contrato->usuario->doc }}
                            </td>

                            <td class="px-4 py-3 font-semibold text-slate-900">
                                {{ \Illuminate\Support\Str::title(mb_strtolower((string) ($salario->contrato->usuario->nombre_completo ?? ''))) }}
                            </td>
                            
                            <td class="px-4 py-3 text-center">
                                @php
                                    $estadoActual = $salario->estado ?? 'pendiente';
                                    $badgeStyle = 'bg-slate-100 text-slate-800 border-slate-200';
                                    $badgeIcon = 'bi-clock-history';

                                    if ($estadoActual === 'pagado') {
                                        $badgeStyle = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                                        $badgeIcon = 'bi-check-circle-fill';
                                    } elseif ($estadoActual === 'liquidado') {
                                        $badgeStyle = 'bg-blue-100 text-blue-800 border-blue-200';
                                        $badgeIcon = 'bi-calculator-fill';
                                    }
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase {{ $badgeStyle }}">
                                    <i class="bi {{ $badgeIcon }}"></i>
                                    {{ $estadoActual }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center font-bold text-slate-700">
                                {{ (int) ($salario->dias_trabajados ?? $salario->dias_a_trabajar) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right text-slate-700">
                                ${{ number_format($salario->contrato->salario_base ?? 0, 0, ',', '.') }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-emerald-700">
                                ${{ number_format((float) ($salario->total_devengado - $salario->total_novedades_devengado), 0, ',', '.') }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-red-600">
                                -${{ number_format((float) $salario->getRawOriginal('total_deducciones'), 0, ',', '.') }}
                            </td>

                            @php
                                $totalNovedadesVal = (float) ($salario->total_novedades ?? 0);
                                $resumenNov = $salario->resumen_novedades ?? [];
                            @endphp
                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold {{ $totalNovedadesVal > 0 ? 'text-emerald-700' : ($totalNovedadesVal < 0 ? 'text-red-600' : 'text-slate-400') }}">
                                <div class="inline-flex items-center justify-end gap-1 novedad-cell" data-novedad-breakdown="{{ json_encode($resumenNov) }}">
                                    <span>
                                        @if($totalNovedadesVal > 0)
                                            +${{ number_format($totalNovedadesVal, 0, ',', '.') }}
                                        @elseif($totalNovedadesVal < 0)
                                            -${{ number_format(abs($totalNovedadesVal), 0, ',', '.') }}
                                        @else
                                            $0
                                        @endif
                                    </span>
                                    @if(!empty($resumenNov) && ($resumenNov['horas_extra'] ?? 0) + ($resumenNov['recargos'] ?? 0) + ($resumenNov['bonificaciones'] ?? 0) + ($resumenNov['otros_devengos'] ?? 0) + ($resumenNov['deducciones'] ?? 0) > 0)
                                        <i class="bi bi-info-circle text-blue-600 cursor-help text-sm"></i>
                                    @endif
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right font-extrabold text-slate-900">
                                ${{ number_format((float) $salario->neto_pagar, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-14 text-center">
                                <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <i class="bi bi-table"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-500">No hay registros de nomina</p>
                                <p class="mt-1 text-xs text-slate-400">Cuando registres una liquidacion, aparecera aqui.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if(method_exists($salarios, 'links'))
        <div class="mt-5 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm md:flex-row md:items-center md:justify-between md:px-5">
            <p class="text-sm font-medium text-slate-500">
                Mostrando {{ $salarios->firstItem() ?? 0 }} a {{ $salarios->lastItem() ?? 0 }} de {{ $salarios->total() }} empleados
            </p>
            <div class="[&>nav]:inline-flex [&>nav>div]:items-center [&_a]:rounded-lg [&_a]:transition [&_a]:duration-150 [&_a:hover]:bg-blue-50 [&_span]:rounded-lg">
                {{ $salarios->onEachSide(1)->links() }}
            </div>
        </div>
    @endif
</div>

<div id="novedad-tooltip" class="novedad-tooltip-popup" style="display: none;">
    <div class="novedad-tooltip-header">
        <span class="font-semibold">Detalle de Novedades</span>
    </div>
    <div class="novedad-tooltip-content" id="novedad-tooltip-content">
        <!-- Content will be dynamically inserted -->
    </div>
</div>

<style>
    .novedad-tooltip-popup {
        position: fixed;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        padding: 0;
        z-index: 1000;
        min-width: 280px;
        max-width: 350px;
        font-size: 13px;
    }

    .novedad-tooltip-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        font-size: 14px;
    }

    .novedad-tooltip-content {
        padding: 12px 16px;
        max-height: 300px;
        overflow-y: auto;
    }

    .novedad-tooltip-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .novedad-tooltip-item:last-child {
        border-bottom: none;
    }

    .novedad-tooltip-label {
        font-weight: 500;
        color: #475569;
        font-size: 12px;
    }

    .novedad-tooltip-value {
        font-weight: 700;
        font-size: 13px;
    }

    .novedad-tooltip-value.devengado {
        color: #059669;
    }

    .novedad-tooltip-value.deduccion {
        color: #dc2626;
    }

    .novedad-cell {
        cursor: pointer;
        transition: all 0.2s;
    }

    .novedad-cell:hover {
        transform: scale(1.05);
    }

    .novedad-cell i {
        vertical-align: middle;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const novedadCells = document.querySelectorAll('.novedad-cell');
    const tooltip = document.getElementById('novedad-tooltip');
    const tooltipContent = document.getElementById('novedad-tooltip-content');
    let currentTimeout = null;

    function formatMoney(value) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    function showTooltip(cell, event) {
        try {
            const breakdownData = JSON.parse(cell.getAttribute('data-novedad-breakdown'));
            
            if (!breakdownData || Object.keys(breakdownData).length === 0) {
                return;
            }

            const horasExtra = parseFloat(breakdownData.horas_extra || 0);
            const recargos = parseFloat(breakdownData.recargos || 0);
            const bonificaciones = parseFloat(breakdownData.bonificaciones || 0);
            const otrosDevengos = parseFloat(breakdownData.otros_devengos || 0);
            const deducciones = parseFloat(breakdownData.deducciones || 0);

            // Check if there's any data to display
            if (horasExtra === 0 && recargos === 0 && bonificaciones === 0 && otrosDevengos === 0 && deducciones === 0) {
                return;
            }

            let html = '<div class="space-y-1">';

            if (horasExtra > 0 || recargos > 0 || bonificaciones > 0 || otrosDevengos > 0) {
                html += '<div class="font-bold text-emerald-700 text-xs uppercase tracking-wide mb-2">Devengos</div>';
                
                if (horasExtra > 0) {
                    html += `<div class="novedad-tooltip-item">
                        <span class="novedad-tooltip-label">Horas Extra</span>
                        <span class="novedad-tooltip-value devengado">${formatMoney(horasExtra)}</span>
                    </div>`;
                }
                
                if (recargos > 0) {
                    html += `<div class="novedad-tooltip-item">
                        <span class="novedad-tooltip-label">Recargos</span>
                        <span class="novedad-tooltip-value devengado">${formatMoney(recargos)}</span>
                    </div>`;
                }
                
                if (bonificaciones > 0) {
                    html += `<div class="novedad-tooltip-item">
                        <span class="novedad-tooltip-label">Bonificaciones</span>
                        <span class="novedad-tooltip-value devengado">${formatMoney(bonificaciones)}</span>
                    </div>`;
                }
                
                if (otrosDevengos > 0) {
                    html += `<div class="novedad-tooltip-item">
                        <span class="novedad-tooltip-label">Otros Devengos</span>
                        <span class="novedad-tooltip-value devengado">${formatMoney(otrosDevengos)}</span>
                    </div>`;
                }
            }

            if (deducciones > 0) {
                html += '<div class="font-bold text-red-600 text-xs uppercase tracking-wide mb-2 mt-3">Deducciones</div>';
                html += `<div class="novedad-tooltip-item">
                    <span class="novedad-tooltip-label">Total Deducciones</span>
                    <span class="novedad-tooltip-value deduccion">-${formatMoney(deducciones)}</span>
                </div>`;
            }

            html += '</div>';

            tooltipContent.innerHTML = html;

            // Position the tooltip
            const rect = cell.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            
            let top = rect.top + window.scrollY - tooltip.offsetHeight - 10;
            let left = rect.left + window.scrollX + (rect.width / 2) - (tooltip.offsetWidth / 2);

            // Adjust if tooltip goes off-screen
            if (top < window.scrollY) {
                top = rect.bottom + window.scrollY + 10;
            }

            if (left < 10) {
                left = 10;
            } else if (left + tooltip.offsetWidth > window.innerWidth - 10) {
                left = window.innerWidth - tooltip.offsetWidth - 10;
            }

            tooltip.style.top = top + 'px';
            tooltip.style.left = left + 'px';
            tooltip.style.display = 'block';
        } catch (error) {
            console.error('Error showing tooltip:', error);
        }
    }

    function hideTooltip() {
        if (currentTimeout) {
            clearTimeout(currentTimeout);
        }
        currentTimeout = setTimeout(() => {
            tooltip.style.display = 'none';
        }, 150);
    }

    novedadCells.forEach(cell => {
        cell.addEventListener('mouseenter', (e) => {
            if (currentTimeout) {
                clearTimeout(currentTimeout);
            }
            showTooltip(cell, e);
        });

        cell.addEventListener('mouseleave', () => {
            hideTooltip();
        });

        cell.addEventListener('click', (e) => {
            e.stopPropagation();
            if (tooltip.style.display === 'none') {
                showTooltip(cell, e);
            } else {
                tooltip.style.display = 'none';
            }
        });
    });

    // Hide tooltip when clicking outside
    document.addEventListener('click', (e) => {
        if (!tooltip.contains(e.target) && !Array.from(novedadCells).some(cell => cell.contains(e.target))) {
            tooltip.style.display = 'none';
        }
    });

    // Hide tooltip on scroll
    window.addEventListener('scroll', () => {
        if (tooltip.style.display === 'block') {
            tooltip.style.display = 'none';
        }
    });
});
</script>