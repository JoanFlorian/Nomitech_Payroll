@extends('layouts.superadmin')

@section('content')
    <div class="flex flex-col gap-8" x-data="{ 
        selectedYear: '{{ $selectedYear }}',
        selectedDate: '{{ $selectedDate }}',
        updateYear() {
            window.location.href = '{{ route('superadmin.index') }}?year=' + this.selectedYear;
        },
        updateDate(date) {
            window.location.href = '{{ route('superadmin.index') }}?date=' + date;
        }
    }">

        <!-- NAVBAR SUPERIOR -->
        <div class="flex justify-between items-center bg-transparent">
            <div class="relative w-1/3">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="bi bi-search text-gray-400"></i>
                </span>
                <input type="text"
                    class="block w-full pl-10 pr-3 py-2 border border-transparent rounded-xl bg-gray-200 focus:bg-white focus:ring-0 sm:text-sm"
                    placeholder="Buscar reportes, transacciones...">
            </div>

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-500 uppercase">Año Fiscal:</span>
                    <select x-model="selectedYear" @change="updateYear()"
                        class="text-sm font-bold text-blue-600 bg-transparent border-none focus:ring-0 cursor-pointer">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-800">Admin Nomitech</p>
                        <p class="text-xs text-gray-500 uppercase font-semibold">Super Usuario</p>
                    </div>
                    <div
                        class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center border border-gray-300 overflow-hidden">
                        <i class="bi bi-person-fill text-2xl text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- RENDIMIENTO DE VENTAS -->
        <div class="flex flex-col gap-1">
            <h2 class="text-2xl font-bold text-gray-800">Rendimiento de Ventas
                {{ $selectedDate ? 'del ' . \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') : 'Anuales' }}</h2>
            <p class="text-sm text-gray-500">Análisis detallado de ingresos acumulados y metas comerciales del período
                actual.</p>
        </div>

        <!-- TARJETAS DE KPI -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.dashboard-card title="Ingresos Totales {{ $selectedDate ? 'del Día' : 'Anuales' }}"
                value="${{ $totalIncome }}"
                subtitle="{{ $selectedDate ? 'VALOR RECAUDADO HOY' : 'RESPECTO AL AÑO PASADO' }}" icon="bi-cash-stack"
                :trend="$selectedDate ? null : ($trend . '%')" :trendUp="$trendUp" />

            <x-ui.dashboard-card title="Meta de Ventas (${{ $salesTarget }})"
                value="{{ number_format(($currentIncomeValue / $salesTargetValue) * 100, 1) }}%"
                subtitle="OBJETIVO: ${{ $salesTarget }}" icon="bi-send-fill">
                <div class="w-full bg-gray-100 rounded-full h-2.5 mt-2">
                    <div class="bg-green-500 h-2.5 rounded-full"
                        style="width: {{ min(100, ($currentIncomeValue / $salesTargetValue) * 100) }}%"></div>
                </div>
            </x-ui.dashboard-card>

            <x-ui.dashboard-card title="Licencias más vendidas" value="{{ $topLicenseName }}"
                subtitle="{{ $topLicenseCount }} UNIDADES ESTE {{ $selectedDate ? 'DÍA' : 'AÑO' }}" icon="bi-shield-check">
                <div class="flex -space-x-2 mt-2">
                    <div
                        class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center border-2 border-white text-[10px] font-bold text-blue-600">
                        {{ $topLicenseCount }}</div>
                    <div class="w-6 h-6 rounded-full bg-gray-300 border-2 border-white"></div>
                    <div class="w-6 h-6 rounded-full bg-gray-400 border-2 border-white"></div>
                </div>
            </x-ui.dashboard-card>
        </div>

        <!-- FILTROS Y BOTONES -->
        <div class="flex justify-between items-center bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4">
                @if($selectedDate)
                    <a href="{{ route('superadmin.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        <i class="bi bi-x-circle mr-1"></i> Quitar Filtro de Fecha
                    </a>
                @endif
            </div>
            <div class="flex justify-end gap-3">
            <div class="relative flex items-center bg-white border border-gray-200 rounded-xl px-4 py-2 hover:bg-gray-50 transition cursor-pointer group">
                <i class="bi bi-calendar-event text-gray-500 mr-2 group-hover:text-blue-600 transition"></i>
                <span class="text-sm font-semibold text-gray-700 mr-2">
                    {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') : 'Filtrar Fecha' }}
                </span>
                <input type="date" 
                       min="2026-01-01"
                       @change="updateDate($event.target.value)"
                       value="{{ $selectedDate }}"
                       class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10">
            </div>
            <a href="{{ route('superadmin.reporte.descargar', ['year' => $selectedYear, 'date' => $selectedDate]) }}" 
               class="flex items-center gap-2 px-4 py-2 bg-blue-600 rounded-xl text-sm font-semibold text-white hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                <i class="bi bi-download"></i>
                Descargar Reporte {{ $selectedDate ? 'Diario' : 'Anual' }}
            </a>
        </div>
        </div>

        <!-- GRÁFICO DE INGRESOS -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-6">
            <div class="flex justify-between items-center">
                <div class="flex flex-col">
                    <h3 class="text-xl font-bold text-gray-800">Ingresos Acumulados</h3>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">VISUALIZACIÓN DE CRECIMIENTO
                        {{ $selectedDate ? 'POR HORAS' : 'MENSUAL' }}</p>
                </div>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-500">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        {{ $selectedDate ? 'Ingresos' : 'Ingresos ' . $selectedYear }}
                    </div>
                </div>
            </div>

            <!-- BARS -->
            <div class="flex items-end justify-between h-64 mt-4 gap-2">
                @if(count($chartData) > 0)
                    @php
                        $maxTotal = $chartData->max('total') ?: 1;
                        $labels = $selectedDate ? range(0, 23) : range(1, 12);
                        $months = ['', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
                    @endphp
                    @foreach($labels as $label)
                        @php
                            $data = $chartData->firstWhere('label', $label);
                            $total = $data ? (float) $data->total : 0;
                            $height = ($total / $maxTotal) * 100;
                            $labelText = $selectedDate ? $label . 'h' : $months[$label];
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-3 group h-full">
                            <div class="w-full flex items-end h-full">
                                <div class="w-full bg-blue-100 rounded-t-lg transition-all group-hover:bg-blue-600 relative"
                                    style="height: {{ max(5, $height) }}%">
                                    <div
                                        class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-30">
                                        ${{ number_format($total, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400">{{ $labelText }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 gap-2">
                        <i class="bi bi-bar-chart text-4xl opacity-20"></i>
                        <p class="text-sm font-semibold">No hay datos para esta selección</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- ACCESOS RÁPIDOS INFERIORES -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <a href="{{ route('superadmin.empresas.index') }}"
                class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 flex items-center gap-4 hover:bg-blue-50 transition cursor-pointer">
                <div class="bg-blue-100 p-3 rounded-xl text-blue-600">
                    <i class="bi bi-building text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-800 text-sm">Empresas</p>
                    <p class="text-xs text-gray-500">Ver directorio</p>
                </div>
            </a>

            <a href="{{ route('superadmin.facturacion') }}"
                class="bg-green-50/50 p-4 rounded-2xl border border-green-100 flex items-center gap-4 hover:bg-green-50 transition cursor-pointer">
                <div class="bg-green-100 p-3 rounded-xl text-green-600">
                    <i class="bi bi-receipt text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-800 text-sm">Transacciones</p>
                    <p class="text-xs text-gray-500">Ver Historial</p>
                </div>
            </a>

            <div
                class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 flex items-center gap-4 hover:bg-blue-50 transition cursor-pointer">
                <div class="bg-blue-100 p-3 rounded-xl text-blue-600">
                    <i class="bi bi-shield-lock text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-800 text-sm">Licencias</p>
                    <p class="text-xs text-gray-500">Gestión activa</p>
                </div>
            </div>

            <div
                class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex items-center gap-4 hover:bg-gray-100 transition cursor-pointer">
                <div class="bg-gray-100 p-3 rounded-xl text-gray-600">
                    <i class="bi bi-headset text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-800 text-sm">Soporte</p>
                    <p class="text-xs text-gray-500">Tickets anuales</p>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="text-center py-4">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">© {{ date('Y') }} NOMITECH S.A.S. -
                DASHBOARD DE ANALÍTICAS AVANZADAS</p>
        </div>

    </div>
@endsection