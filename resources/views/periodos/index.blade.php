@extends('layouts.app')

@section('title', 'Gestión de Periodos')
@section('page-title', 'PERIODOS DE NÓMINA')

@section('content')

@php($periodos = $periodos ?? collect())

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-8">

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-3">
            <span class="material-icons text-green-500">check_circle</span>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3">
            <span class="material-icons text-red-500">error</span>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">Periodos de Nómina</h2>
            <p class="text-sm text-gray-500 mt-1">Gestione los periodos de liquidación y exportaciones bancarias.</p>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="mb-8 border-b border-gray-100 pb-6">
        <form action="{{ route('periodos.index') }}" method="GET" class="space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                {{-- Filtros por Estado (Tabs) --}}
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Filtrar por Estado</label>
                    <div class="inline-flex p-1 bg-gray-100 rounded-xl">
                        @php($currentEstado = $estado ?? 'todos')
                        @foreach(['todos' => 'Todos', 'abierto' => 'Abiertos', 'pendiente' => 'Pendientes', 'cerrado' => 'Cerrados'] as $val => $label)
                            <a href="{{ route('periodos.index', array_merge(request()->query(), ['estado' => $val, 'page' => 1])) }}"
                                class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $currentEstado == $val ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Filtros por Fecha --}}
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Mes</label>
                        <select name="mes" onchange="this.form.submit()" 
                            class="bg-gray-50 border-gray-200 rounded-xl text-[11px] font-bold focus:ring-blue-500 focus:border-blue-500 py-2 min-w-[130px]">
                            <option value="">Cualquier Mes</option>
                            @foreach(['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $i => $m)
                                <option value="{{ $i + 1 }}" {{ ($mes == ($i + 1)) ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Año</label>
                        <select name="anio" onchange="this.form.submit()"
                            class="bg-gray-50 border-gray-200 rounded-xl text-[11px] font-bold focus:ring-blue-500 focus:border-blue-500 py-2 min-w-[90px]">
                            <option value="">Año</option>
                            @for($y = date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ $anio == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    @if(($estado && $estado !== 'todos') || $mes || $anio)
                        <a href="{{ route('periodos.index') }}" 
                            class="flex items-center justify-center h-[38px] w-[38px] bg-white border border-gray-200 text-gray-400 rounded-xl hover:bg-red-50 hover:text-red-500 hover:border-red-100 transition-all shadow-sm group"
                            title="Limpiar Filtros">
                            <span class="material-icons text-sm group-hover:rotate-90 transition-transform">filter_alt_off</span>
                        </a>
                    @endif
                </div>
            </div>
            
            {{-- Preservar estado actual cuando se cambia el mes/año via submit --}}
            <input type="hidden" name="estado" value="{{ $estado ?? 'todos' }}">
        </form>
    </div>

    {{-- TABLA DE PERIODOS --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-sm border-collapse">

            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold tracking-wide text-xs">Frecuencia</th>
                    <th class="px-4 py-3 text-left font-semibold tracking-wide text-xs">Fecha Inicio</th>
                    <th class="px-4 py-3 text-left font-semibold tracking-wide text-xs">Fecha Fin</th>
                    <th class="px-4 py-3 text-center font-semibold tracking-wide text-xs">Estado</th>
                    <th class="px-4 py-3 text-center font-semibold tracking-wide text-xs">Comprobantes</th>
                    <th class="px-4 py-3 text-right font-semibold tracking-wide text-xs">Acciones</th>
                </tr>
            </thead>

            <tbody class="bg-white">
                @forelse($periodos as $periodo)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">

                    <td class="px-4 py-3 text-xs">
                        @php($color = match ($periodo->tipo_frecuencia) {
                            'mensual' => 'bg-blue-100 text-blue-700',
                            'quincenal' => 'bg-purple-100 text-purple-700',
                            'decenal' => 'bg-indigo-100 text-indigo-700',
                            default => 'bg-gray-100 text-gray-700'
                        })
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $color }}">
                            {{ $periodo->tipo_frecuencia }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-gray-700 font-medium text-xs">
                        {{ $periodo->fecha_inicio->format('d/m/Y') }}
                    </td>

                    <td class="px-4 py-3 text-gray-700 font-medium text-xs">
                        {{ $periodo->fecha_fin->format('d/m/Y') }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        @php($statusColor = match ($periodo->estado) {
                            'abierto' => 'bg-green-100 text-green-700',
                            'cerrado' => 'bg-gray-100 text-gray-600',
                            'pendiente' => 'bg-yellow-100 text-yellow-700',
                            default => 'bg-gray-100 text-gray-700'
                        })
                        <span class="px-3 py-1 rounded-lg text-[11px] font-bold uppercase {{ $statusColor }}">
                            {{ $periodo->estado }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-center text-gray-500 font-medium text-xs">
                        {{ $periodo->salarios()->count() }} registros
                    </td>

                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($periodo->estado === \App\Models\PeriodoLiquidacion::ESTADO_ABIERTO || $periodo->estado === \App\Models\PeriodoLiquidacion::ESTADO_PENDIENTE)
                                {{-- Boton Liquidar --}}
                                @can('calculate_payroll')
                                <a href="{{ route('periodos.select', $periodo->id_periodo) }}"
                                    class="flex items-center gap-1.5 bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                    <span>Liquidar</span>
                                </a>
                                @endcan

                                {{-- DEBUG: canClose={{ auth()->user()->can('close_period') ? 'true' : 'false' }} status={{ $periodo->estado }} canBeClosed={{ $periodo->canBeClosed() ? 'true' : 'false' }} --}}
                                @if($periodo->canBeClosed())
                                    @can('close_period')
                                    <button type="button"
                                        onclick="abrirModalCierre({{ $periodo->id_periodo }}, '{{ $periodo->fecha_inicio->format('d/m/Y') }}', '{{ $periodo->fecha_fin->format('d/m/Y') }}')"
                                        class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                        Cerrar
                                    </button>
                                    @endcan
                                @endif
                            @endif

                            @if($periodo->estado === \App\Models\PeriodoLiquidacion::ESTADO_CERRADO)
                                {{-- Boton Exportar --}}
                                @can('export_bank_files')
                                <button type="button" onclick="abrirModalExportar({{ $periodo->id_periodo }})"
                                    class="flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    <span>Exportar Pagos</span>
                                </button>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-16">
                        <div class="flex flex-col items-center">
                            <span class="text-4xl mb-4">📅</span>
                            <h3 class="text-lg font-semibold text-gray-900">No hay periodos activos</h3>
                            <p class="text-gray-500 mt-1">Los periodos se generan automáticamente al cerrar el anterior.
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($periodos, 'links'))
        <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <p class="text-sm text-gray-500">
                Mostrando {{ $periodos->firstItem() ?? 0 }} a {{ $periodos->lastItem() ?? 0 }} de {{ $periodos->total() }}
                periodos
            </p>
            <div>
                {{ $periodos->links() }}
            </div>
        </div>
    @endif

</div>

@can('create_period')
{{-- BOTÓN FLOTANTE --}}
<button onclick="document.getElementById('modalNuevoPeriodo').classList.remove('hidden')"
    class="fixed bottom-8 right-8 w-16 h-16 bg-blue-600 text-white rounded-full shadow-2xl hover:bg-blue-700 transition-all flex items-center justify-center group z-40 transform hover:scale-110">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 transition-transform group-hover:rotate-90" fill="none"
        viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
    </svg>
</button>
@endcan

{{-- MODAL NUEVO PERIODO --}}
<div id="modalNuevoPeriodo" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Fondo oscuro con figuritas decorativas -->
        <div class="fixed inset-0 bg-gray-900/85 transition-opacity" aria-hidden="true"
            onclick="document.getElementById('modalNuevoPeriodo').classList.add('hidden')">
            <!-- Figuritas decorativas Marca: Azul #1565C0, Verde #2AA58C -->
            <div class="absolute top-[5%] left-[10%] w-32 h-32 rounded-full bg-[#1565C0]/20 blur-[2px] rotate-12"></div>
            <div class="absolute top-[15%] right-[15%] w-48 h-48 rounded-3xl bg-[#2AA58C]/15 blur-[1px] -rotate-12">
            </div>
            <div class="absolute bottom-[10%] left-[20%] w-40 h-40 rounded-xl bg-[#2AA58C]/20 rotate-45"></div>
            <div class="absolute bottom-[20%] right-[10%] w-56 h-56 rounded-full bg-[#1565C0]/15 blur-[3px]"></div>
            <div class="absolute top-[40%] left-[-5%] w-24 h-24 rounded-full bg-[#2AA58C]/25"></div>
            <div class="absolute top-[55%] right-[-5%] w-36 h-36 rounded-2xl bg-[#1565C0]/20 -rotate-6"></div>
            <div class="absolute top-[70%] left-[45%] w-16 h-16 rounded-full bg-[#2AA58C]/20"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div
            class="relative inline-block overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:max-w-lg sm:w-full border border-gray-100 sm:-translate-y-12">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-900" id="modal-title">Nuevo Periodo de Liquidación</h3>
                <button onclick="document.getElementById('modalNuevoPeriodo').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('periodos.store') }}" method="POST">
                @csrf
                <div class="px-6 py-6 space-y-5">
                    {{-- Empresa (Hidden since multi-company is not supported yet) --}}
                    <input type="hidden" name="id_empresa" value="{{ session('empresa_id') }}">

                    {{-- Frecuencia --}}
                    <div>
                        <label
                            class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Frecuencia</label>
                        <select name="tipo_frecuencia" id="tipo_frecuencia" required
                            class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-2.5">
                            <option value="mensual">Mensual (1-30/31)</option>
                            <option value="quincenal">Quincenal (1-15 / 16-Fin)</option>
                            <option value="decenal">Decenal (1-10 / 11-20 / 21-Fin)</option>
                            <option value="semanal">Semanal (7 días)</option>
                            <option value="catorcenal">Catorcenal (14 días)</option>
                            <option value="otro">Personalizado (Otro)</option>
                        </select>
                    </div>

                    {{-- Fechas --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Fecha
                                Inicio</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" required
                                min="{{ $minDate ?? '' }}" max="{{ $maxDate ?? '' }}"
                                class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-2"
                                value="{{ old('fecha_inicio', (isset($minDate) && date('Y-m-d') < $minDate) ? $minDate : date('Y-m-d')) }}">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Fecha
                                Fin</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" required
                                min="{{ $minDate ?? '' }}" max="{{ $maxDate ?? '' }}"
                                class="w-full border-gray-200 bg-gray-50 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-2"
                                readonly>
                            <div class="flex flex-col gap-1 mt-1">
                                <p id="hint_fecha" class="text-[10px] text-blue-600 font-medium italic"></p>
                                @if($minDate && $maxDate)
                                    <p class="text-[9px] text-gray-400 leading-tight">
                                        Rango permitido: {{ \Carbon\Carbon::parse($minDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($maxDate)->format('d/m/Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex flex-col sm:flex-row-reverse gap-3 rounded-b-2xl">
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
                        Crear Periodo
                    </button>
                    <button type="button" onclick="document.getElementById('modalNuevoPeriodo').classList.add('hidden')"
                        class="w-full sm:w-auto px-6 py-2.5 bg-white text-gray-700 text-sm font-bold rounded-xl border border-gray-300 hover:bg-gray-50 transition-all">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('periodos.partials.modal_cerrar')
@include('periodos.partials.modal_exportar')

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const freqSelect = document.getElementById('tipo_frecuencia');
        const startInput = document.getElementById('fecha_inicio');
        const endInput = document.getElementById('fecha_fin');
        const hintText = document.getElementById('hint_fecha');

        const isRestricted = @json($isRestrictedByLicense);

        function updateEndDate() {
            const freq = freqSelect.value;
            const startVal = startInput.value;

            if (!startVal) return;

            if (freq === 'otro') {
                endInput.readOnly = false;
                endInput.classList.remove('bg-gray-50', 'border-gray-200');
                endInput.classList.add('bg-white', 'border-gray-300');
                hintText.textContent = "Defina el rango libremente.";
                return;
            }

            // Standard frequencies
            endInput.readOnly = true;
            endInput.classList.add('bg-gray-50', 'border-gray-200');
            endInput.classList.remove('bg-white', 'border-gray-300');

            // Parse start date accurately
            const [year, month, day] = startVal.split('-').map(Number);
            let startDate = new Date(year, month - 1, day);
            let endDate = new Date(startDate);
            
            // Initial calculation based on frequency
            let label = "";
            if (freq === 'mensual') {
                endDate = new Date(year, month, 0); // Last day of start month
                label = "Fin de mes.";
            } else if (freq === 'quincenal') {
                endDate.setDate(startDate.getDate() + 14);
                label = "+14 días.";
            } else if (freq === 'decenal') {
                endDate.setDate(startDate.getDate() + 9);
                label = "+9 días.";
            } else if (freq === 'semanal') {
                endDate.setDate(startDate.getDate() + 6);
                label = "+6 días.";
            } else if (freq === 'catorcenal') {
                endDate.setDate(startDate.getDate() + 13);
                label = "+13 días.";
            }

            // APLICA RESTRICCIÓN DE MES ACTUAL (Licencia pre-25)
            if (isRestricted) {
                const lastDayOfMonth = new Date(year, month, 0);
                if (endDate > lastDayOfMonth) {
                    endDate = lastDayOfMonth;
                    
                    // RECALCULAR FECHA INICIO PARA MANTENER DURACIÓN DENTRO DEL MES
                    let newStartDate = new Date(endDate);
                    if (freq === 'quincenal') newStartDate.setDate(endDate.getDate() - 14);
                    else if (freq === 'decenal') newStartDate.setDate(endDate.getDate() - 9);
                    else if (freq === 'semanal') newStartDate.setDate(endDate.getDate() - 6);
                    else if (freq === 'catorcenal') newStartDate.setDate(endDate.getDate() - 13);
                    else if (freq === 'mensual') newStartDate.setDate(1);

                    // Actualizar input de inicio
                    const yS = newStartDate.getFullYear();
                    const mS = String(newStartDate.getMonth() + 1).padStart(2, '0');
                    const dS = String(newStartDate.getDate()).padStart(2, '0');
                    startInput.value = `${yS}-${mS}-${dS}`;

                    label += " (Periodo ajustado al mes actual por su licencia)";
                    hintText.classList.replace('text-blue-600', 'text-red-500');
                } else {
                    hintText.classList.replace('text-red-500', 'text-blue-600');
                }
            } else {
                hintText.classList.replace('text-red-500', 'text-blue-600');
            }

            hintText.textContent = `Calculado: ${label}`;

            const y = endDate.getFullYear();
            const m = String(endDate.getMonth() + 1).padStart(2, '0');
            const d = String(endDate.getDate()).padStart(2, '0');
            endInput.value = `${y}-${m}-${d}`;
        }

        freqSelect.addEventListener('change', updateEndDate);
        startInput.addEventListener('change', updateEndDate);

        // Init
        updateEndDate();
    });

    });
</script>

@endsection