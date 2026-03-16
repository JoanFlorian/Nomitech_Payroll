@extends('layouts.app')

@section('title', 'Provisiones')
@section('page-title', 'Provisiones')

@section('content')
    <div x-data="{
                                            gestionarModal: false,
                                            masivoModal: false,
                                            consignacionModal: false,
                                            selectedEmployee: '',
                                            selectedBenefit: '',
                                            selectedAmount: '',
                                            paymentMode: 'direct',
                                            cesantiasMode: 'retiro_empresa',
                                            retiroReason: 'housing',
                                            masivoBenefit: '',
                                            masivoPaymentMode: 'direct',
                                            resultadoModal: {{ session('mass_liquidation_results') ? 'true' : 'false' }},
                                        }">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-lg"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                <i class="bi bi-exclamation-circle-fill text-lg"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl flex items-center gap-3">
                <i class="bi bi-info-circle-fill text-lg"></i>
                <span class="font-medium">{{ session('info') }}</span>
            </div>
        @endif

        {{-- Legal Date Warnings --}}
        @if(isset($warnings) && $warnings->isNotEmpty())
            @foreach($warnings as $warning)
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl flex items-center gap-3">
                    <i class="bi bi-calendar-event text-lg"></i>
                    <span class="text-sm font-medium">{{ $warning }}</span>
                </div>
            @endforeach
        @endif

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Provisiones de Prestaciones Sociales</h2>
                <p class="text-sm text-gray-500 italic">
                    Balances actuales y gestión de prestaciones por empleado.
                    @if(isset($activePeriod) && $activePeriod)
                        <span
                            class="inline-flex items-center gap-1 ml-2 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                            <i class="bi bi-play-circle"></i> Periodo activo:
                            {{ \Carbon\Carbon::parse($activePeriod->fecha_inicio)->format('d/m') }} -
                            {{ \Carbon\Carbon::parse($activePeriod->fecha_fin)->format('d/m/Y') }}
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1 ml-2 px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-semibold rounded-full">
                            <i class="bi bi-pause-circle"></i> Sin periodo activo
                        </span>
                    @endif
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto mt-4 md:mt-0">
                <button type="button" @click.prevent="consignacionModal = true"
                    class="bg-emerald-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-md hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2">
                    <i class="bi bi-file-earmark-arrow-down"></i>
                    Consignación Anual
                </button>
                <button type="button" @click.prevent="masivoModal = true"
                    class="bg-[#1565C0] text-white font-bold py-2.5 px-6 rounded-lg shadow-md hover:bg-[#0D47A1] transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2">
                    <i class="bi bi-people"></i>
                    Liquidación Masiva
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-8">
            {{-- Prima Card --}}
            <div
                class="relative overflow-hidden bg-blue-50/40 p-4 rounded-xl border border-blue-200 shadow-sm group hover:shadow-md transition-all duration-300">
                {{-- Shapes --}}
                <div
                    class="absolute -top-6 -right-6 w-16 h-16 bg-blue-100 rounded-full opacity-80 group-hover:scale-125 transition-transform duration-700">
                </div>
                <div
                    class="absolute top-[10%] left-[20%] w-0 h-0 border-l-[15px] border-l-transparent border-r-[15px] border-r-transparent border-b-[25px] border-b-blue-200/60 rotate-12 opacity-70">
                </div>
                <div class="absolute bottom-[10%] right-[30%] w-8 h-8 bg-blue-100/60 rotate-45 rounded-sm opacity-70"></div>
                <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-blue-100 rounded-full opacity-60"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="p-1.5 bg-white text-[#1565C0] rounded-lg shadow-sm"><i
                                class="bi bi-gift text-sm"></i></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-[#1565C0]">Prima</span>
                    </div>
                    <p class="text-lg font-black text-[#1565C0]">${{ number_format($totals['prima'], 2, ',', '.') }}</p>
                </div>
            </div>

            {{-- Cesantías Card --}}
            <div
                class="relative overflow-hidden bg-emerald-50/40 p-4 rounded-xl border border-emerald-200 shadow-sm group hover:shadow-md transition-all duration-300">
                {{-- Shapes --}}
                <div
                    class="absolute -top-6 -right-6 w-16 h-16 bg-emerald-100 rounded-full opacity-80 group-hover:scale-125 transition-transform duration-700">
                </div>
                <div
                    class="absolute top-[40%] right-[10%] w-0 h-0 border-t-[10px] border-t-transparent border-b-[10px] border-b-transparent border-l-[15px] border-l-emerald-200/60 rotate-[200deg] opacity-70">
                </div>
                <div class="absolute bottom-[15%] left-[25%] w-6 h-6 bg-emerald-100/60 rotate-12 rounded-sm opacity-70">
                </div>
                <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-emerald-100 rounded-full opacity-60"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="p-1.5 bg-white text-emerald-600 rounded-lg shadow-sm"><i
                                class="bi bi-bank text-sm"></i></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600">Cesantías</span>
                    </div>
                    <p class="text-lg font-black text-emerald-600">${{ number_format($totals['cesantias'], 2, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Intereses Card --}}
            <div
                class="relative overflow-hidden bg-sky-50/40 p-4 rounded-xl border border-sky-200 shadow-sm group hover:shadow-md transition-all duration-300">
                {{-- Shapes --}}
                <div
                    class="absolute -top-6 -right-6 w-16 h-16 bg-sky-100 rounded-full opacity-80 group-hover:scale-125 transition-transform duration-700">
                </div>
                <div class="absolute top-[15%] left-[40%] w-4 h-4 bg-sky-200/60 rotate-45 opacity-70"></div>
                <div
                    class="absolute bottom-[20%] right-[10%] w-0 h-0 border-l-[12px] border-l-transparent border-r-[12px] border-r-transparent border-b-[20px] border-b-sky-200/60 -rotate-[15deg] opacity-70">
                </div>
                <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-sky-100 rounded-full opacity-60"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="p-1.5 bg-white text-sky-600 rounded-lg shadow-sm"><i
                                class="bi bi-percent text-sm"></i></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-sky-600">Intereses</span>
                    </div>
                    <p class="text-lg font-black text-sky-600">${{ number_format($totals['intereses'], 2, ',', '.') }}</p>
                </div>
            </div>

            {{-- Vacaciones Card --}}
            <div
                class="relative overflow-hidden bg-indigo-50/40 p-4 rounded-xl border border-indigo-200 shadow-sm group hover:shadow-md transition-all duration-300">
                {{-- Shapes --}}
                <div
                    class="absolute -top-6 -right-6 w-16 h-16 bg-indigo-100 rounded-full opacity-80 group-hover:scale-125 transition-transform duration-700">
                </div>
                <div
                    class="absolute top-[50%] left-[10%] w-0 h-0 border-t-[8px] border-t-transparent border-b-[8px] border-b-transparent border-l-[12px] border-l-indigo-200/60 rotate-[45deg] opacity-70">
                </div>
                <div class="absolute top-[10%] left-[30%] w-5 h-5 bg-indigo-200/60 rotate-[30deg] rounded-full opacity-70">
                </div>
                <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-indigo-100 rounded-full opacity-60"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="p-1.5 bg-white text-indigo-600 rounded-lg shadow-sm"><i
                                class="bi bi-sun text-sm"></i></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-600">Vacaciones</span>
                    </div>
                    <p class="text-lg font-black text-indigo-600">
                        {{ number_format($totals['vacaciones'], 2, ',', '.') }} días</p>
                </div>
            </div>

            {{-- Total Card --}}
            <div
                class="relative overflow-hidden bg-[#1565C0] p-4 rounded-xl border border-blue-800 shadow-md group hover:shadow-lg transition-all duration-300">
                {{-- Shapes --}}
                <div
                    class="absolute -top-6 -right-6 w-20 h-20 bg-white/20 rounded-full group-hover:scale-125 transition-transform duration-700">
                </div>
                <div
                    class="absolute top-[20%] left-[15%] w-0 h-0 border-l-[15px] border-l-transparent border-r-[15px] border-r-transparent border-b-[25px] border-b-white/20 rotate-[220deg] opacity-90">
                </div>
                <div class="absolute bottom-[25%] right-[20%] w-6 h-6 bg-white/10 rotate-[65deg] opacity-90"></div>
                <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-white/10 rounded-full opacity-40"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="p-1.5 bg-white/20 text-white rounded-lg"><i
                                class="bi bi-calculator text-sm"></i></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-blue-100">Total Global</span>
                    </div>
                    <p class="text-lg font-black text-white">${{ number_format($totals['total_money'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Balances Table --}}
        @if($balances->isEmpty())
            <div class="text-center py-16">
                <i class="bi bi-inbox text-5xl text-gray-300 mb-4 block"></i>
                <p class="text-gray-400 text-lg font-medium">No hay provisiones registradas aún.</p>
                <p class="text-gray-400 text-sm mt-1">Las provisiones se generan automáticamente al cerrar un periodo de nómina.
                </p>
            </div>
        @else
            <div x-data="{ 
                                search: '',
                                get filteredBalances() {
                                    if (!this.search) return true;
                                    return true; {{-- Logic handled in x-show for simplicity with Blade loops --}}
                                }
                            }">
                {{-- Table Header with Search --}}
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="bi bi-people text-[#1565C0]"></i>
                        Saldos por Empleado
                    </h3>

                    <div class="relative w-full md:w-72">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="bi bi-search text-sm"></i>
                        </span>
                        <input type="text" x-model.debounce.300ms="search" placeholder="Buscar por nombre o documento..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 sm:text-sm transition-all shadow-sm">
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm bg-white">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">
                                    Empleado
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Prima
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">
                                    Cesantías
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">
                                    Intereses
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">
                                    Vacaciones</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            @foreach($balances as $balance)
                                @php
                                    $nombreLower = str_replace("'", "\\'", strtolower($balance->usuario ? $balance->usuario->nombre_completo : 'Empleado no encontrado'));
                                    $docLower = str_replace("'", "\\'", strtolower($balance->employee_id));
                                @endphp
                                <tr x-show="!search || '{{ $nombreLower }}'.includes(search.toLowerCase()) || '{{ $docLower }}'.includes(search.toLowerCase())"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    class="hover:bg-gray-50/80 transition-all duration-200 group">
                                    @php
                                        $nombre = $balance->usuario
                                            ? $balance->usuario->nombre_completo
                                            : 'Empleado no encontrado';

                                        // Generate initials
                                        $initials = '';
                                        if ($balance->usuario) {
                                            $initials = mb_substr($balance->usuario->primer_nombre, 0, 1) . mb_substr($balance->usuario->primer_apellido, 0, 1);
                                        } else {
                                            $initials = '??';
                                        }

                                        // Deterministic color based on name
                                        $colors = ['bg-[#1564C0]', 'bg-[#0CB983]', 'bg-sky-500', 'bg-indigo-500', 'bg-blue-400', 'bg-emerald-400'];
                                        $colorIndex = abs(crc32($balance->employee_id)) % count($colors);
                                        $avatarColor = $colors[$colorIndex];

                                        $totalMonetario = $balance->prima_balance + $balance->cesantias_balance + $balance->intereses_balance;
                                        $tieneSaldo = ($totalMonetario > 0 || $balance->vacaciones_balance > 0);
                                    @endphp
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            {{-- Avatar --}}
                                            <div
                                                class="w-10 h-10 rounded-full {{ $avatarColor }} flex items-center justify-center text-white text-xs font-bold shadow-sm group-hover:scale-105 transition-transform">
                                                {{ strtoupper($initials) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-900 leading-tight">{{ $nombre }}</div>
                                                <div class="text-[11px] text-gray-400 font-medium tracking-wide">ID:
                                                    {{ $balance->employee_id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right font-medium">
                                        ${{ number_format($balance->prima_balance, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right font-medium">
                                        ${{ number_format($balance->cesantias_balance, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right font-medium">
                                        ${{ number_format($balance->intereses_balance, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right font-medium">
                                        {{ number_format($balance->vacaciones_balance, 2, ',', '.') }} días
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- Historial --}}
                                            <a href="{{ route('provisiones.historial', $balance->employee_id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-[#1565C0] bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                                                title="Ver historial">
                                                <i class="bi bi-clock-history"></i> Historial
                                            </a>

                                            @if($tieneSaldo)
                                                {{-- SINGLE unified button --}}
                                                <button type="button"
                                                    @click.prevent="gestionarModal = true; selectedEmployee = '{{ trim((string) $balance->employee_id) }}'; selectedAmount = ''; selectedBenefit = ''; paymentMode = 'direct'; cesantiasMode = 'pago_directo'; retiroReason = 'housing'"
                                                    class="inline-flex items-center gap-1 px-4 py-1.5 text-xs font-semibold text-white bg-gradient-to-r from-[#1565C0] to-[#1976D2] rounded-lg shadow-sm hover:from-[#0D47A1] hover:to-[#1565C0] transition-all"
                                                    title="Gestionar prestación">
                                                    <i class="bi bi-cash-stack"></i> Gestionar
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- No results found --}}
                    <div x-show="search && ![...$el.previousElementSibling.querySelectorAll('tbody tr')].some(tr => tr.style.display !== 'none')"
                        class="py-12 text-center" style="display: none;">
                        <div
                            class="inline-flex items-center justify-center w-12 h-12 bg-gray-50 rounded-full text-gray-400 mb-3">
                            <i class="bi bi-search text-xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No se encontraron empleados que coincidan con "<span
                                x-text="search" class="font-bold"></span>"</p>
                        <button @click="search = ''" class="mt-2 text-sm text-[#1565C0] font-semibold hover:underline">Limpiar
                            búsqueda</button>
                    </div>
                </div>

                {{-- Pagination Links --}}
                <div class="mt-6">
                    {{ $balances->links() }}
                </div>
        @endif

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- MODAL: Gestionar Prestación (Unified — ALL flows) --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" x-show="gestionarModal" x-cloak
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div @click.away="gestionarModal = false"
                    class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative overflow-hidden max-h-[90vh] overflow-y-auto">
                    <div class="absolute inset-0 pointer-events-none opacity-5">
                        <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-[#1565C0]"></div>
                    </div>
                    <div class="relative z-10">
                        <div
                            class="sticky top-0 bg-white p-6 border-b border-gray-100 flex justify-between items-center z-20">
                            <h2 class="text-xl font-bold text-gray-800">
                                <i class="bi bi-cash-stack mr-2 text-[#1565C0]"></i>Gestionar Prestación
                            </h2>
                            <button type="button" @click="gestionarModal = false"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="bi bi-x-lg text-xl"></i>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('provisiones.pagar-prestacion') }}" class="p-6 space-y-5">
                            @csrf
                            <input type="hidden" name="employee_id" :value="selectedEmployee">

                            {{-- 1. Benefit Type --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Prestación</label>
                                <select name="benefit_type" x-model="selectedBenefit" required
                                    @change="cesantiasMode = 'retiro_empresa'; retiroReason = 'housing'"
                                    class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] h-11">
                                    <option value="" disabled>Seleccionar prestación...</option>
                                    <option value="prima">Prima de Servicios</option>
                                    <option value="cesantias">Cesantías</option>
                                    <option value="intereses_cesantias">Intereses de Cesantías</option>
                                </select>
                            </div>

                            {{-- 2. Cesantías Sub-Mode (only when cesantías selected) --}}
                            <template x-if="selectedBenefit === 'cesantias'">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">Tipo de Operación</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        {{-- Retiro Empresa --}}
                                        <label class="cursor-pointer rounded-xl p-3 transition-all border-2 text-center"
                                            :class="cesantiasMode === 'retiro_empresa' ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:border-gray-300'">
                                            <input type="radio" name="cesantias_mode" value="retiro_empresa"
                                                x-model="cesantiasMode" class="sr-only">
                                            <i class="bi bi-building text-xl block mb-1"
                                                :class="cesantiasMode === 'retiro_empresa' ? 'text-amber-500' : 'text-gray-400'"></i>
                                            <span class="text-xs font-bold block"
                                                :class="cesantiasMode === 'retiro_empresa' ? 'text-amber-600' : 'text-gray-600'">Retiro
                                                Empresa</span>
                                        </label>
                                        {{-- Autorización Fondo --}}
                                        <label class="cursor-pointer rounded-xl p-3 transition-all border-2 text-center"
                                            :class="cesantiasMode === 'autorizacion_fondo' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'">
                                            <input type="radio" name="cesantias_mode" value="autorizacion_fondo"
                                                x-model="cesantiasMode" class="sr-only">
                                            <i class="bi bi-bank text-xl block mb-1"
                                                :class="cesantiasMode === 'autorizacion_fondo' ? 'text-emerald-500' : 'text-gray-400'"></i>
                                            <span class="text-xs font-bold block"
                                                :class="cesantiasMode === 'autorizacion_fondo' ? 'text-emerald-600' : 'text-gray-600'">Autorizar
                                                Fondo</span>
                                        </label>
                                    </div>

                                    {{-- Contextual info per cesantías mode --}}
                                    <div class="mt-3">
                                        <template x-if="cesantiasMode === 'retiro_empresa'">
                                            <div
                                                class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700 flex items-start gap-2">
                                                <i class="bi bi-building mt-0.5"></i>
                                                <span>Cesantías <strong>NO consignadas</strong> al fondo. La empresa paga
                                                    directamente y el saldo <strong>se reduce</strong>.</span>
                                            </div>
                                        </template>
                                        <template x-if="cesantiasMode === 'autorizacion_fondo'">
                                            <div
                                                class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 flex items-start gap-2">
                                                <i class="bi bi-bank mt-0.5"></i>
                                                <span>Cesantías <strong>YA consignadas</strong>. Se genera un certificado de
                                                    autorización. El saldo <strong>NO se modifica</strong>.</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- 3. Reason (only for retiro_empresa / autorizacion_fondo) --}}
                            <template
                                x-if="selectedBenefit === 'cesantias' && (cesantiasMode === 'retiro_empresa' || cesantiasMode === 'autorizacion_fondo')">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Motivo del Retiro</label>
                                    <select name="reason" x-model="retiroReason" required
                                        class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] h-11">
                                        <option value="housing">Vivienda (Compra, mejora o liberación)</option>
                                        <option value="education">Educación (Matrícula superior, Icetex)</option>
                                    </select>
                                </div>
                            </template>

                            {{-- 4. Amount --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <span
                                        x-text="selectedBenefit === 'vacaciones' ? 'Días a Compensar' : (selectedBenefit === 'cesantias' && cesantiasMode === 'autorizacion_fondo' ? 'Monto Autorizado' : 'Monto a Pagar')"></span>
                                </label>
                                <input type="number" name="amount" x-model="selectedAmount" step="0.01" min="0.01" required
                                    class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] h-11"
                                    placeholder="Ej: 500000">
                            </div>

                            {{-- 5. Payment Mode (hidden for autorizacion_fondo since it's always direct) --}}
                            <template x-if="!(selectedBenefit === 'cesantias' && cesantiasMode === 'autorizacion_fondo')">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">Modo de Pago</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        {{-- Direct --}}
                                        <label class="cursor-pointer rounded-xl p-4 transition-all hover:shadow-md border-2"
                                            :class="paymentMode === 'direct' ? 'border-[#1565C0] bg-blue-50/50' : 'border-gray-200'">
                                            <input type="radio" name="payment_mode" value="direct" x-model="paymentMode"
                                                class="sr-only">
                                            <div class="flex flex-col items-center text-center gap-2">
                                                <span class="p-2 rounded-full"
                                                    :class="paymentMode === 'direct' ? 'bg-[#1565C0] text-white' : 'bg-gray-100 text-gray-500'">
                                                    <i class="bi bi-lightning-charge text-lg"></i>
                                                </span>
                                                <span class="text-sm font-bold"
                                                    :class="paymentMode === 'direct' ? 'text-[#1565C0]' : 'text-gray-600'">Pago
                                                    Inmediato</span>
                                                <span class="text-xs text-gray-400">Saldo se reduce ahora</span>
                                            </div>
                                        </label>
                                        {{-- Payroll --}}
                                        <label class="cursor-pointer rounded-xl p-4 transition-all hover:shadow-md border-2"
                                            :class="paymentMode === 'payroll' ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200'"
                                            @if(!isset($activePeriod) || !$activePeriod)
                                            x-bind:class="'opacity-50 pointer-events-none'" @endif>
                                            <input type="radio" name="payment_mode" value="payroll" x-model="paymentMode"
                                                class="sr-only" @if(!isset($activePeriod) || !$activePeriod) disabled
                                                @endif>
                                            <div class="flex flex-col items-center text-center gap-2">
                                                <span class="p-2 rounded-full"
                                                    :class="paymentMode === 'payroll' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500'">
                                                    <i class="bi bi-calendar-check text-lg"></i>
                                                </span>
                                                <span class="text-sm font-bold"
                                                    :class="paymentMode === 'payroll' ? 'text-emerald-600' : 'text-gray-600'">Integrar
                                                    a Nómina</span>
                                                <span class="text-xs text-gray-400">
                                                    @if(isset($activePeriod) && $activePeriod)
                                                        {{ \Carbon\Carbon::parse($activePeriod->fecha_inicio)->format('d/m') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($activePeriod->fecha_fin)->format('d/m') }}
                                                    @else
                                                        Sin periodo activo
                                                    @endif
                                                </span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </template>

                            {{-- For autorizacion_fondo, force direct payment_mode --}}
                            <template x-if="selectedBenefit === 'cesantias' && cesantiasMode === 'autorizacion_fondo'">
                                <input type="hidden" name="payment_mode" value="direct">
                            </template>

                            {{-- Contextual info about payment mode --}}
                            <template
                                x-if="paymentMode === 'direct' && !(selectedBenefit === 'cesantias' && cesantiasMode === 'autorizacion_fondo')">
                                <div
                                    class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-700 flex items-start gap-2">
                                    <i class="bi bi-info-circle mt-0.5"></i>
                                    <span>Pago inmediato. El saldo se reduce ahora y <strong>no aparece en la
                                            nómina</strong>.</span>
                                </div>
                            </template>
                            <template x-if="paymentMode === 'payroll'">
                                <div
                                    class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-sm text-emerald-700 flex items-start gap-2">
                                    <i class="bi bi-calendar-check mt-0.5"></i>
                                    <span>Devengo en nómina. El saldo <strong>se reduce al cerrar el periodo</strong> y
                                        aparece
                                        en el comprobante.</span>
                                </div>
                            </template>

                            {{-- Submit --}}
                            <div class="flex justify-end gap-3 pt-2">
                                <button type="button" @click="gestionarModal = false"
                                    class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-all">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="px-5 py-2.5 text-sm font-bold text-white rounded-lg shadow-md transition-all"
                                    :class="{
                                                                'bg-amber-600 hover:bg-amber-700': selectedBenefit === 'cesantias' && cesantiasMode === 'retiro_empresa',
                                                                'bg-emerald-600 hover:bg-emerald-700': selectedBenefit === 'cesantias' && cesantiasMode === 'autorizacion_fondo',
                                                                'bg-emerald-600 hover:bg-emerald-700': paymentMode === 'payroll' && !(selectedBenefit === 'cesantias' && (cesantiasMode === 'retiro_empresa' || cesantiasMode === 'autorizacion_fondo')),
                                                                'bg-[#1565C0] hover:bg-[#0D47A1]': paymentMode === 'direct' && !(selectedBenefit === 'cesantias' && (cesantiasMode === 'retiro_empresa' || cesantiasMode === 'autorizacion_fondo'))
                                                            }">
                                    <i class="bi mr-1" :class="{
                                                                    'bi-building': selectedBenefit === 'cesantias' && cesantiasMode === 'retiro_empresa',
                                                                    'bi-file-earmark-arrow-down': selectedBenefit === 'cesantias' && cesantiasMode === 'autorizacion_fondo',
                                                                    'bi-calendar-check': paymentMode === 'payroll' && selectedBenefit !== 'cesantias',
                                                                    'bi-check2-circle': paymentMode === 'direct' && selectedBenefit !== 'cesantias'
                                                                }"></i>
                                    <span x-text="
                                                                selectedBenefit === 'cesantias' && cesantiasMode === 'retiro_empresa' ? 'Registrar Retiro' :
                                                                selectedBenefit === 'cesantias' && cesantiasMode === 'autorizacion_fondo' ? 'Autorizar y Generar Certificado' :
                                                                paymentMode === 'payroll' ? 'Programar en Nómina' : 'Confirmar Pago'
                                                            "></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- MODAL: Liquidación Masiva --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" x-show="masivoModal" x-cloak
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div @click.away="masivoModal = false"
                    class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none opacity-5">
                        <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-[#1565C0]"></div>
                    </div>
                    <div class="relative z-10">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="text-xl font-bold text-gray-800">Liquidación Masiva</h2>
                            <button type="button" @click="masivoModal = false"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="bi bi-x-lg text-xl"></i>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('provisiones.liquidar.masivo') }}" class="p-6 space-y-5">
                            @csrf
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <i class="bi bi-exclamation-triangle-fill text-amber-500 text-lg mt-0.5"></i>
                                    <div>
                                        <p class="text-sm font-semibold text-amber-700">Atención</p>
                                        <p class="text-sm text-amber-600 mt-1">Liquida la prestación para <strong>todos los
                                                empleados</strong> con saldo positivo.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Prestación</label>
                                <select name="benefit_type" x-model="masivoBenefit" required
                                    class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] h-11">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option value="prima">Prima</option>
                                    <option value="cesantias">Cesantías</option>
                                    <option value="intereses_cesantias">Intereses de Cesantías</option>
                                </select>
                            </div>

                            {{-- Mass Payment Mode --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Modo de Pago</label>
                                <div class="grid grid-cols-2 gap-3">
                                    {{-- Direct --}}
                                    <label class="cursor-pointer rounded-xl p-4 transition-all hover:shadow-md border-2"
                                        :class="masivoPaymentMode === 'direct' ? 'border-[#1565C0] bg-blue-50/50' : 'border-gray-200'">
                                        <input type="radio" name="payment_mode" value="direct" x-model="masivoPaymentMode"
                                            class="sr-only">
                                        <div class="flex flex-col items-center text-center gap-2">
                                            <span class="p-2 rounded-full"
                                                :class="masivoPaymentMode === 'direct' ? 'bg-[#1565C0] text-white' : 'bg-gray-100 text-gray-500'">
                                                <i class="bi bi-lightning-charge text-lg"></i>
                                            </span>
                                            <span class="text-sm font-bold"
                                                :class="masivoPaymentMode === 'direct' ? 'text-[#1565C0]' : 'text-gray-600'">Pago
                                                Inmediato</span>
                                        </div>
                                    </label>
                                    {{-- Payroll --}}
                                    <label class="cursor-pointer rounded-xl p-4 transition-all hover:shadow-md border-2"
                                        :class="masivoPaymentMode === 'payroll' ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200'"
                                        @if(!isset($activePeriod) || !$activePeriod)
                                        x-bind:class="'opacity-50 pointer-events-none'" @endif>
                                        <input type="radio" name="payment_mode" value="payroll" x-model="masivoPaymentMode"
                                            class="sr-only" @if(!isset($activePeriod) || !$activePeriod) disabled @endif>
                                        <div class="flex flex-col items-center text-center gap-2">
                                            <span class="p-2 rounded-full"
                                                :class="masivoPaymentMode === 'payroll' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500'">
                                                <i class="bi bi-calendar-check text-lg"></i>
                                            </span>
                                            <span class="text-sm font-bold"
                                                :class="masivoPaymentMode === 'payroll' ? 'text-emerald-600' : 'text-gray-600'">Integrar
                                                a Nómina</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Contextual info about mass payment mode --}}
                            <template x-if="masivoPaymentMode === 'direct'">
                                <div
                                    class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-700 flex items-start gap-2">
                                    <i class="bi bi-info-circle mt-0.5"></i>
                                    <span>Los saldos se reducirán <strong>de inmediato</strong> para todos los
                                        empleados.</span>
                                </div>
                            </template>
                            <template x-if="masivoPaymentMode === 'payroll'">
                                <div
                                    class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-sm text-emerald-700 flex items-start gap-2">
                                    <i class="bi bi-calendar-check mt-0.5"></i>
                                    <span>La liquidación aparecerá en el periodo de nómina actual y el saldo se reducirá
                                        <strong>al cerrarlo</strong>.</span>
                                </div>
                            </template>

                            <div class="flex justify-end gap-3 pt-2">
                                <button type="button" @click="masivoModal = false"
                                    class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-all">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="px-5 py-2.5 text-sm font-bold text-white bg-[#1565C0] rounded-lg shadow-md hover:bg-[#0D47A1] transition-all">
                                    <i class="bi bi-people mr-1"></i> Confirmar Liquidación Masiva
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- MODAL: Consignación Anual --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" x-show="consignacionModal"
                x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div @click.away="consignacionModal = false"
                    class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none opacity-5">
                        <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-indigo-500"></div>
                    </div>
                    <div class="relative z-10">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="text-xl font-bold text-gray-800">Consignación Anual de Cesantías</h2>
                            <button type="button" @click="consignacionModal = false"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="bi bi-x-lg text-xl"></i>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('provisiones.cesantias.consignacion-anual') }}"
                            class="p-6 space-y-5">
                            @csrf
                            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <i class="bi bi-info-circle-fill text-indigo-500 text-lg mt-0.5"></i>
                                    <div>
                                        <p class="text-sm font-semibold text-indigo-700">Un archivo TXT por fondo</p>
                                        <p class="text-sm text-indigo-600 mt-1">Múltiples fondos se descargan como ZIP.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Año de Causación</label>
                                <input type="number" name="year" value="{{ date('Y') - 1 }}" max="{{ date('Y') }}" required
                                    class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 h-11">
                                <p class="text-xs text-gray-400 mt-1">Normalmente el año anterior (pago antes del 14 de
                                    Febrero).</p>
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <button type="button" @click="consignacionModal = false"
                                    class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-all">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 transition-all">
                                    <i class="bi bi-download mr-1"></i> Generar y Descargar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- MODAL: Resultados de Liquidación Masiva --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if(session('mass_liquidation_results'))
            @php $res = session('mass_liquidation_results'); @endphp
            <div class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-[60]" x-show="resultadoModal" x-cloak
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

                <div @click.away="resultadoModal = false"
                    class="bg-white rounded-3xl shadow-2xl w-full max-w-md relative overflow-hidden border border-gray-100">

                    {{-- Decorative background shapes --}}
                    <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-blue-500/10 rounded-full"></div>
                    <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-emerald-500/10 rounded-full"></div>
                    <div class="absolute top-12 left-8 w-3 h-3 bg-sky-400/20 rotate-12 rounded-sm"></div>
                    <div class="absolute top-1/2 right-4 w-5 h-5 border-2 border-indigo-400/20 rotate-45 rounded-md"></div>
                    <div class="absolute bottom-1/4 left-12 w-2 h-2 bg-amber-400/20 rounded-full"></div>
                    <div class="absolute top-24 right-1/4 w-1.5 h-1.5 bg-blue-400/20 rounded-full"></div>
                    <div class="absolute bottom-8 right-12 w-4 h-1 bg-emerald-400/20 -rotate-12 rounded-full"></div>
                    <div class="absolute top-1/3 left-4 w-6 h-6 border border-sky-400/10 rounded-full"></div>
                    <div class="absolute top-4 right-1/3 w-2 h-2 bg-indigo-400/15 rounded-sm rotate-45"></div>

                    <div class="relative z-10">
                        {{-- Header --}}
                        <div class="p-6 text-center">
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-2xl {{ $res['processed_count'] > 0 ? 'bg-green-50 text-green-500' : 'bg-amber-50 text-amber-500' }} mb-3 shadow-inner">
                                @if($res['processed_count'] > 0)
                                    <i class="bi bi-patch-check-fill text-3xl"></i>
                                @else
                                    <i class="bi bi-exclamation-octagon-fill text-3xl"></i>
                                @endif
                            </div>

                            <h2 class="text-xl font-black text-gray-800 leading-tight">
                                Resultados
                            </h2>
                            <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                                {{ $res['benefit_label'] }}
                            </p>
                        </div>

                        <div class="px-6 pb-6 space-y-4">
                            {{-- Main Summary --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 rounded-2xl bg-gray-50/50 border border-gray-100 text-center">
                                    <span class="block text-xl font-black text-gray-800">{{ $res['processed_count'] }}</span>
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Procesados</span>
                                </div>
                                <div
                                    class="p-3 rounded-2xl {{ count($res['skipped_employees']) > 0 ? 'bg-amber-50/50 border-amber-100' : 'bg-gray-50/50 border-gray-100' }} border text-center">
                                    <span
                                        class="block text-xl font-black {{ count($res['skipped_employees']) > 0 ? 'text-amber-600' : 'text-gray-800' }}">
                                        {{ count($res['skipped_employees']) }}
                                    </span>
                                    <span
                                        class="text-[9px] font-bold uppercase tracking-wider {{ count($res['skipped_employees']) > 0 ? 'text-amber-500' : 'text-gray-400' }}">Omitidos</span>
                                </div>
                            </div>

                            {{-- Specific Message for 0 processed --}}
                            @if($res['processed_count'] == 0)
                                <div class="p-3 bg-red-50 border border-red-100 rounded-xl relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-8 h-8 bg-red-100/50 rounded-bl-full"></div>
                                    <p class="text-[11px] font-bold text-red-600 text-center leading-normal">
                                        No se pudo integrar a la nómina de ningún empleado porque ninguno está asociado al periodo
                                        activo.
                                    </p>
                                </div>
                            @else
                                <div class="p-3 bg-green-50 border border-green-100 rounded-xl">
                                    <p class="text-[11px] font-bold text-green-600 text-center leading-normal">
                                        Liquidación integrada exitosamente.
                                    </p>
                                </div>
                            @endif

                            {{-- Skipped Employees List --}}
                            @if(!empty($res['skipped_employees']))
                                <div class="space-y-2">
                                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Detalle de
                                        Omisiones</h4>
                                    <div class="max-h-40 overflow-y-auto pr-1 custom-scrollbar">
                                        <div class="space-y-1.5">
                                            @foreach($res['skipped_employees'] as $skipped)
                                                <div
                                                    class="flex items-center justify-between p-2.5 bg-white/60 border border-gray-100 rounded-xl">
                                                    <div class="flex items-center gap-2.5">
                                                        <div
                                                            class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-[9px] font-black uppercase">
                                                            {{ mb_substr($skipped['name'], 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <p class="text-[11px] font-bold text-gray-700 leading-none">
                                                                {{ $skipped['name'] }}
                                                            </p>
                                                            <p class="text-[9px] text-gray-400 mt-1 uppercase tracking-tighter">ID:
                                                                {{ $skipped['doc'] }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @if($skipped['reason'] === 'no_salary')
                                                        <span
                                                            class="px-1.5 py-0.5 bg-gray-50 text-gray-400 text-[8px] font-bold uppercase rounded-md border border-gray-100 italic">No
                                                            asociado</span>
                                                    @else
                                                        <span
                                                            class="px-1.5 py-0.5 bg-amber-50 text-amber-500 text-[8px] font-bold uppercase rounded-md border border-amber-100">Saldo
                                                            0</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Close Button --}}
                            <div class="pt-1">
                                <button type="button" @click="resultadoModal = false"
                                    class="w-full py-3 bg-gray-900 hover:bg-black text-white text-xs font-black rounded-xl shadow-lg shadow-gray-200 transition-all transform hover:-translate-y-0.5 active:scale-95">
                                    CERRAR RESULTADOS
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection