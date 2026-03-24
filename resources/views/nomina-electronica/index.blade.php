@extends('layouts.app')

@section('title', 'Nómina Electrónica')
{{-- No usamos @section('page-title') para tener control total del layout interno como pide el usuario --}}

@section('content')
    <div x-data="{ 
        reportModalOpen: false, 
        detailModalOpen: false, 
        loading: false, 
        employees: [], 
        selectedPeriod: null,
        fetchDetails(periodId) {
            this.loading = true;
            this.detailModalOpen = true;
            this.selectedPeriod = periodId;
            fetch(`/nomina-electronica/${periodId}/detalles`)
                .then(res => res.json())
                .then(data => {
                    this.employees = data;
                    this.loading = false;
                })
                .catch(err => {
                    console.error(err);
                    this.loading = false;
                });
        }
    }" class="relative -m-6 md:-m-8">
        {{-- Replicamos el main del snippet dentro del content del layout --}}
        <div class="bg-white p-6 md:p-8 lg:p-12 relative overflow-x-hidden">

            {{-- Figuras Decorativas Exactas --}}
            <div
                class="absolute opacity-5 z-0 pointer-events-none rounded-full bg-[#1565C0] w-[200px] h-[200px] -top-[50px] -left-[100px]">
            </div>
            <div
                class="absolute opacity-5 z-0 pointer-events-none rounded-full bg-[#10B981] w-[150px] h-[150px] -bottom-[75px] -right-[75px]">
            </div>

            {{-- Triángulos usando bordes (Tailwind arbitrary values) --}}
            <div
                class="absolute opacity-5 z-0 pointer-events-none top-[10%] right-[5%] rotate-45 w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#1565C0]">
            </div>
            <div
                class="absolute opacity-5 z-0 pointer-events-none bottom-[20%] left-[5%] -rotate-[120deg] w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#1565C0]">
            </div>
            <div
                class="absolute opacity-5 z-0 pointer-events-none top-[60%] left-[40%] rotate-[10deg] w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#10B981]">
            </div>
            <div
                class="absolute opacity-5 z-0 pointer-events-none top-[15%] left-[50%] -rotate-[90deg] scale-50 w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#10B981]">
            </div>

            {{-- Diamantes --}}
            <div class="absolute opacity-5 z-0 pointer-events-none bg-[#1565C0] w-20 h-20 rotate-45 top-[25%] right-[20%]">
            </div>
            <div
                class="absolute opacity-5 z-0 pointer-events-none bg-[#10B981] w-20 h-20 rotate-[65deg] scale-75 bottom-[5%] left-[30%]">
            </div>

            {{-- Flechas --}}
            <div
                class="absolute opacity-5 z-0 pointer-events-none border-t-[50px] border-t-transparent border-b-[50px] border-b-transparent border-l-[50px] border-l-[#1565C0] top-[80%] right-[15%] rotate-[220deg]">
            </div>
            <div
                class="absolute opacity-5 z-0 pointer-events-none border-t-[40px] border-t-transparent border-b-[40px] border-b-transparent border-l-[40px] border-l-[#10B981] top-[5%] left-[30%] rotate-[150deg]">
            </div>

            <div class="relative z-10 h-full flex flex-col">
                <div class="flex justify-between items-start flex-wrap mb-8">
                    <div>
                        <h2 class="text-4xl font-bold text-gray-800">Nómina Electrónica</h2>
                        <p class="mt-4 text-gray-500 max-w-2xl">
                            Realiza el seguimiento y gestiona los envíos de nómina electrónica por período.
                        </p>
                    </div>
                </div>

                {{-- FILTERS SECTION --}}
                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 mb-8">
                    <form action="{{ route('nomina-electronica.index') }}" method="GET" class="flex flex-col md:flex-row items-end gap-4" data-loader data-loader-text="Filtrando nómina electrónica...">
                        <div class="flex-1 min-w-[150px]">
                            <label for="year" class="block text-sm font-bold text-gray-700 mb-2">Año</label>
                            <select name="year" id="year" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-[#1565C0] transition-colors">
                                <option value="">Todos los años</option>
                                @foreach($anos as $ano)
                                    <option value="{{ $ano }}" {{ request('year') == $ano ? 'selected' : '' }}>{{ $ano }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[180px]">
                            <label for="month" class="block text-sm font-bold text-gray-700 mb-2">Mes</label>
                            <select name="month" id="month" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-[#1565C0] transition-colors">
                                <option value="">Todos los meses</option>
                                @php
                                    $meses = [
                                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                    ];
                                @endphp
                                @foreach($meses as $num => $nombre)
                                    <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-[#1565C0] text-white px-6 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition-colors flex items-center gap-2">
                                <span class="material-icons text-xl">filter_list</span>
                                Filtrar
                            </button>
                            @if(request()->has('year') || request()->has('month'))
                                <a href="{{ route('nomina-electronica.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-xl font-bold hover:bg-gray-300 transition-colors flex items-center gap-2">
                                    <span class="material-icons text-xl">clear</span>
                                    Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="space-y-6">
                    <div class="flex justify-between items-center mb-4 flex-wrap gap-4">
                        <h3 class="text-2xl font-semibold text-gray-700">Historial de periodos de liquidación</h3>
                        @can('transmit_electronic_payroll')
                        <button @click="reportModalOpen = true"
                            class="bg-[#10B981] text-white font-bold py-2 px-6 rounded-lg shadow-md hover:bg-[#0C9467] transition-all duration-300 h-11 flex items-center">
                            Reportar a la DIAN
                        </button>
                        @endcan
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($periodos as $periodo)
                            {{-- Card --}}
                            <div
                                class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                                <div class="flex flex-col h-full">
                                    <div class="flex-grow">
                                        <p class="text-gray-500 text-sm">Rango del periodo</p>
                                        <p class="text-lg font-bold text-gray-800 mb-1">
                                            {{ $periodo->fecha_inicio->format('d/m/Y') }} -
                                            {{ $periodo->fecha_fin->format('d/m/Y') }}
                                        </p>
                                        <p class="text-xs font-medium text-[#1565C0] uppercase tracking-wider mb-4">
                                            {{ $periodo->fecha_inicio->translatedFormat('F Y') }}
                                        </p>

                                        <div class="flex flex-col space-y-2">
                                            <div>
                                                <p class="text-gray-500 text-xs">Estado liquidación</p>
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                    {{ $periodo->estado === 'cerrado' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ ucfirst($periodo->estado) }}
                                                </span>
                                            </div>

                                            <div>
                                                <p class="text-gray-500 text-xs">Estado nómina electrónica</p>
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#10B981]/10 text-[#10B981]">
                                                    <span class="w-2 h-2 mr-2 rounded-full bg-[#10B981]"></span> Aceptado
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-6 space-y-2 w-full">
                                        @can('export_bank_files')
                                            @if($periodo->estado === 'cerrado')
                                                <div class="grid grid-cols-2 gap-2">
                                                    <button onclick="abrirModalExportar({{ $periodo->id_periodo }}, 'bank')"
                                                        title="Exportar archivo CSV solo con transferencias bancarias"
                                                        class="w-full flex items-center justify-center gap-1 text-center bg-emerald-50 text-emerald-700 font-semibold py-2 px-2 rounded-lg hover:bg-emerald-100 transition-colors border border-emerald-200 text-sm">
                                                        <span class="material-icons text-sm">account_balance</span> Bancos
                                                    </button>
                                                    <button onclick="abrirModalExportar({{ $periodo->id_periodo }}, 'general')"
                                                        title="Exportar archivo CSV general incluyendo pagos en efectivo"
                                                        class="w-full flex items-center justify-center gap-1 text-center bg-blue-50 text-blue-700 font-semibold py-2 px-2 rounded-lg hover:bg-blue-100 transition-colors border border-blue-200 text-sm">
                                                        <span class="material-icons text-sm">list_alt</span> General
                                                    </button>
                                                </div>
                                            @endif
                                        @endcan
                                        <button @click="fetchDetails({{ $periodo->id_periodo }})"
                                            class="w-full text-center bg-gray-100 text-[#1565C0] font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors">
                                            Ver detalle
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-12 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                <p class="text-gray-500 font-medium">No se encontraron periodos de liquidación registrados.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- MODAL: REPORTAR A LA DIAN --}}
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak
                x-show="reportModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div @click.away="reportModalOpen = false"
                    class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden"
                    x-data="{ 
                         search: '', 
                         periods: [ 
                            { id: 1, range: '01/08/2025 – 15/08/2025', employees: 24, reported: false }, 
                            { id: 2, range: '16/08/2025 – 31/08/2025', employees: 25, reported: false }, 
                            { id: 3, range: '01/09/2025 – 15/09/2025', employees: 25, reported: false } 
                         ], 
                         get filteredPeriods() { 
                            if (this.search === '') return this.periods.filter(p => !p.reported)
                            return this.periods.filter(p => !p.reported && (p.range.toLowerCase().includes(this.search.toLowerCase()) || p.id.toString().includes(this.search))) 
                         } 
                     }" x-show="reportModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

                    {{-- Formas internas del modal --}}
                    <div class="absolute inset-0 pointer-events-none overflow-hidden">
                        <div
                            class="absolute opacity-10 rounded-full bg-[#1565C0] w-[150px] h-[150px] -top-[75px] -right-[75px]">
                        </div>
                        <div
                            class="absolute opacity-10 rounded-full bg-[#10B981] w-[120px] h-[120px] -bottom-[60px] -left-[60px]">
                        </div>
                        <div
                            class="absolute opacity-10 top-[20%] left-[5%] -rotate-45 scale-75 w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#1565C0]">
                        </div>
                        <div
                            class="absolute opacity-10 bottom-[15%] right-[10%] rotate-[130deg] scale-[0.6] w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#1565C0]">
                        </div>
                        <div
                            class="absolute opacity-10 top-[70%] right-[40%] rotate-[20deg] scale-[0.8] w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#10B981]">
                        </div>
                        <div
                            class="absolute opacity-10 top-[5%] left-[50%] -rotate-[110deg] scale-[0.4] w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#10B981]">
                        </div>
                        <div
                            class="absolute opacity-10 bg-[#1565C0] w-20 h-20 rotate-[10deg] scale-[0.8] top-[35%] left-[20%]">
                        </div>
                        <div
                            class="absolute opacity-10 bg-[#10B981] w-20 h-20 -rotate-[20deg] scale-[0.6] bottom-[25%] right-[25%]">
                        </div>
                        <div
                            class="absolute opacity-10 border-t-[40px] border-t-transparent border-b-[40px] border-b-transparent border-l-[40px] border-l-[#1565C0] bottom-[10%] left-[15%] rotate-180">
                        </div>
                        <div
                            class="absolute opacity-10 border-t-[30px] border-t-transparent border-b-[30px] border-b-transparent border-l-[30px] border-l-[#10B981] top-[15%] right-[30%] rotate-45">
                        </div>
                    </div>

                    <div class="relative z-10 flex flex-col min-h-0 h-full">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-2xl font-bold text-[#1565C0]">Seleccionar periodo de liquidación</h3>
                        </div>
                        <div class="p-6 flex-grow overflow-y-auto">
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar periodo</label>
                                <input
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#1565C0] focus:ring-[#1565C0]"
                                    placeholder="Buscar por rango de fechas o número de periodo" type="text"
                                    x-model="search" />
                            </div>
                            <div class="space-y-4" x-show="filteredPeriods.length > 0">
                                <template x-for="period in filteredPeriods" :key="period.id">
                                    <label
                                        class="flex items-start md:items-center p-4 border rounded-lg hover:border-[#1565C0] cursor-pointer transition-colors duration-200 has-[:checked]:bg-blue-50 has-[:checked]:border-[#1565C0]">
                                        <input class="h-5 w-5 rounded text-[#1565C0] focus:ring-[#1565C0] mr-4 mt-1 md:mt-0"
                                            type="checkbox" />
                                        <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 items-center">
                                            <div class="flex items-center">
                                                <span class="material-icons mr-2 text-gray-500">date_range</span>
                                                <span class="font-medium text-gray-800" x-text="period.range"></span>
                                            </div>
                                            <div class="flex items-center text-gray-600">
                                                <span class="material-icons mr-2">groups</span>
                                                <span x-text="`${period.employees} empleados incluidos`"></span>
                                            </div>
                                            <div class="col-span-1 md:col-span-2 flex items-center mt-2 md:mt-0">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-600">
                                                    No reportado electrónicamente
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                            <div class="text-center py-12" x-show="filteredPeriods.length === 0" x-cloak>
                                <p class="text-gray-500">No hay periodos de liquidación pendientes por reportar.</p>
                            </div>
                        </div>
                        <div class="flex justify-end items-center p-4 bg-gray-50 border-t border-gray-200 space-x-3">
                            <button @click="reportModalOpen = false"
                                class="bg-gray-200 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-300 transition-colors">
                                Cancelar
                            </button>
                            @can('transmit_electronic_payroll')
                            <button
                                class="bg-[#10B981] text-white font-bold py-2 px-6 rounded-lg shadow-md hover:bg-[#0C9467] transition-colors">
                                Reportar a la DIAN
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- MODAL: DETALLE --}}
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak
                x-show="detailModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div @click.away="detailModalOpen = false"
                    class="relative bg-white rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden"
                    x-show="detailModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

                    {{-- Formas internas del modal --}}
                    <div class="absolute inset-0 pointer-events-none overflow-hidden">
                        <div
                            class="absolute opacity-10 rounded-full bg-[#1565C0] w-[150px] h-[150px] -top-[75px] -right-[75px]">
                        </div>
                        <div
                            class="absolute opacity-10 rounded-full bg-[#10B981] w-[120px] h-[120px] -bottom-[60px] -left-[60px]">
                        </div>
                        <div
                            class="absolute opacity-10 top-[20%] left-[5%] -rotate-45 scale-75 w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#1565C0]">
                        </div>
                        <div
                            class="absolute opacity-10 bottom-[15%] right-[10%] rotate-[130deg] scale-[0.6] w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#1565C0]">
                        </div>
                        <div
                            class="absolute opacity-10 top-[70%] right-[40%] rotate-[20deg] scale-[0.8] w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#10B981]">
                        </div>
                        <div
                            class="absolute opacity-10 top-[5%] left-[50%] -rotate-[110deg] scale-[0.4] w-0 h-0 border-l-[50px] border-l-transparent border-r-[50px] border-r-transparent border-b-[100px] border-b-[#10B981]">
                        </div>
                        <div
                            class="absolute opacity-10 bg-[#1565C0] w-20 h-20 rotate-[10deg] scale-[0.8] top-[35%] left-[20%]">
                        </div>
                        <div
                            class="absolute opacity-10 bg-[#10B981] w-20 h-20 -rotate-[20deg] scale-[0.6] bottom-[25%] right-[25%]">
                        </div>
                        <div
                            class="absolute opacity-10 border-t-[40px] border-t-transparent border-b-[40px] border-b-transparent border-l-[40px] border-l-[#1565C0] bottom-[10%] left-[15%] rotate-180">
                        </div>
                        <div
                            class="absolute opacity-10 border-t-[30px] border-t-transparent border-b-[30px] border-b-transparent border-l-[30px] border-l-[#10B981] top-[15%] right-[30%] rotate-45">
                        </div>
                    </div>

                    <div class="relative z-10 flex flex-col min-h-0 h-full">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-2xl font-bold text-gray-800">Nóminas Electrónicas Reportadas</h3>
                            <p class="text-gray-500 mt-1">Consulta el estado de las nóminas electrónicas enviadas a la DIAN
                                para este periodo.</p>
                        </div>
                        <div class="p-6 flex-grow overflow-y-auto">
                            {{-- Skeleton / Loading Spinner --}}
                            <div x-show="loading" class="flex flex-col items-center justify-center py-12">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#1565C0] mb-4"></div>
                                <p class="text-gray-500 font-medium">Cargando registros...</p>
                            </div>

                            <div x-show="!loading && employees.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <template x-for="employee in employees" :key="employee.id_salario">
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 flex flex-col space-y-3">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-bold text-gray-800" x-text="'👤 ' + employee.nombre_empleado"></p>
                                                <p class="text-sm text-gray-600" x-text="'📄 CC ' + employee.documento"></p>
                                            </div>
                                            <a :href="'/nomina-electronica/pdf/' + employee.id_salario"
                                                class="flex items-center text-sm text-[#1565C0] font-semibold hover:text-blue-700 transition-colors">
                                                <span class="material-icons mr-1 text-base">download</span> Descargar PDF
                                            </a>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Estado de la nómina:</p>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-sm font-semibold"
                                                :class="employee.estado_nomina_electronica === 'Rechazado' ? 'bg-[#e53935]/10 text-[#e53935]' : 'bg-[#10B981]/10 text-[#10B981]'"
                                                x-text="employee.estado_nomina_electronica"></span>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Fecha de reporte:</p>
                                            <p class="text-sm text-gray-700 font-medium" x-text="employee.fecha_reporte"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div x-show="!loading && employees.length === 0" class="text-center py-12">
                                <p class="text-gray-500 font-medium">No se encontraron registros de nómina para este periodo.</p>
                            </div>
                        </div>
                        <div class="flex justify-end items-center p-4 bg-gray-50 border-t border-gray-200">
                            <button @click="detailModalOpen = false"
                                class="bg-gray-200 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-300 transition-colors">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- MODAL INTERNAMENTE REUTILIZA EL DE PERIODOS --}}
            <script>
                // Sobrescribir la base URL para que el modal use las rutas del controller NominaElectronica
                window.exportBaseUrl = '/nomina-electronica';
            </script>
            @include('periodos.partials.modal_exportar')
        </div>
    </div>
@endsection