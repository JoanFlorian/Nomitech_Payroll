@extends('layouts.app')

@section('title', 'Provisiones')
@section('page-title', 'Provisiones')

@section('content')
    <div x-data="{ 
        modalOpen: false,
        startDate: '',
        endDate: '',
        periodType: '',
        showTable: false,
        loading: false,
        calculateProvisions() {
            if (!this.periodType) return;
            this.loading = true;
            this.showTable = false;
            setTimeout(() => {
                this.showTable = true;
                this.loading = false;
            }, 1500);
        },
        resetForm() {
            this.modalOpen = false;
            this.startDate = '';
            this.endDate = '';
            this.periodType = '';
            this.showTable = false;
            this.loading = false;
        }
    }">
        <div class="relative z-10 flex flex-col h-full">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Listado de Provisiones</h2>
                    <p class="text-sm text-gray-500 italic">Gestión de provisiones y prestaciones sociales por periodos.</p>
                </div>
                <button @click="modalOpen = true"
                    class="w-full md:w-auto bg-[#10B981] text-white font-bold py-2.5 px-6 rounded-lg shadow-md hover:bg-[#0C9467] transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                    <i class="bi bi-plus-lg mr-2"></i>
                    Nuevo Periodo
                </button>
            </div>

            <div class="space-y-6">
                {{-- Card 1 --}}
                <div
                    class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center hover:shadow-md transition-shadow">
                    <div class="flex-grow">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-1.5 bg-blue-50 text-[#1565C0] rounded-md">
                                <i class="bi bi-calendar-event"></i>
                            </span>
                            <p class="text-lg font-bold text-gray-800">01/08/2025 – 31/08/2025</p>
                        </div>
                        <div class="text-gray-600 ml-9">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total
                                acumulado:</span>
                            <p class="text-xl font-black text-[#1565C0]">$4,520,000</p>
                        </div>
                    </div>
                    <div class="mt-6 sm:mt-0 sm:ml-6 flex-shrink-0 w-full sm:w-auto">
                        <button
                            class="w-full sm:w-auto bg-[#10B981] text-white font-semibold py-2 px-6 rounded-lg hover:bg-[#0C9467] transition-colors duration-300 flex items-center justify-center gap-2">
                            <i class="bi bi-check2-circle"></i>
                            Liquidar provisión
                        </button>
                    </div>
                </div>

                {{-- Card 2 --}}
                <div
                    class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center hover:shadow-md transition-shadow">
                    <div class="flex-grow">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-1.5 bg-blue-50 text-[#1565C0] rounded-md">
                                <i class="bi bi-calendar-event"></i>
                            </span>
                            <p class="text-lg font-bold text-gray-800">01/07/2025 – 31/07/2025</p>
                        </div>
                        <div class="text-gray-600 ml-9">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total
                                acumulado:</span>
                            <p class="text-xl font-black text-[#1565C0]">$4,480,000</p>
                        </div>
                    </div>
                    <div class="mt-6 sm:mt-0 sm:ml-6 flex-shrink-0 w-full sm:w-auto">
                        <button
                            class="w-full sm:w-auto bg-[#10B981] text-white font-semibold py-2 px-6 rounded-lg hover:bg-[#0C9467] transition-colors duration-300 flex items-center justify-center gap-2">
                            <i class="bi bi-check2-circle"></i>
                            Liquidar provisión
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL NUEVO PERIODO --}}
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50 overflow-y-auto" x-show="modalOpen"
            x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            <div @click.away="resetForm()"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl min-h-[300px] flex flex-col relative overflow-hidden my-auto">
                {{-- Figuras decorativas internas (opcional, replicando estilo original pero con clases locales) --}}
                <div class="absolute inset-0 pointer-events-none opacity-5">
                    <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-[#1565C0]"></div>
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 rounded-full bg-[#10B981]"></div>
                </div>

                <div class="relative z-10 flex flex-col h-full">
                    <!-- Header -->
                    <div class="p-6 md:p-8 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-800">Nuevo periodo de provisiones</h2>
                        <button @click="resetForm()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 md:p-8 space-y-6 overflow-y-auto max-h-[60vh]">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha inicio</label>
                                <input type="date" x-model="startDate"
                                    class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] h-11">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha fin</label>
                                <input type="date" x-model="endDate"
                                    class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] h-11">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de periodo</label>
                                <select @change="calculateProvisions()" x-model="periodType"
                                    class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] h-11">
                                    <option value="" disabled>Seleccionar</option>
                                    <option value="mensual">Mensual</option>
                                    <option value="trimestral">Trimestral</option>
                                    <option value="semestral">Semestral</option>
                                    <option value="anual">Anual</option>
                                </select>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        {{-- Loading Spinner --}}
                        <div class="flex flex-col items-center justify-center py-12" x-show="loading" x-transition>
                            <div class="animate-spin rounded-full h-10 w-10 border-4 border-gray-200 border-t-[#1565C0]">
                            </div>
                            <p class="mt-4 text-sm font-medium text-gray-500 tracking-wide uppercase">Calculando
                                proyecciones...</p>
                        </div>

                        {{-- Data Table --}}
                        <div x-show="showTable" x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0">
                            <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="bi bi-table text-blue-500"></i>
                                Proyección de Provisiones por Empleado
                            </h3>
                            <div class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50/30">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">
                                                Nombre y apellidos</th>
                                            <th
                                                class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">
                                                Cesantías</th>
                                            <th
                                                class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">
                                                Intereses</th>
                                            <th
                                                class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">
                                                Prima</th>
                                            <th
                                                class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">
                                                Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-50">
                                        <tr class="hover:bg-blue-50/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">Ana
                                                María García Rojas</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">
                                                $416,667</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">$50,000
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">
                                                $416,667</td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-black text-[#1565C0] text-right">
                                                $883,334</td>
                                        </tr>
                                        <tr class="hover:bg-blue-50/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                                Carlos Alberto Pérez Gómez</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">
                                                $300,000</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">$36,000
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">
                                                $300,000</td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-black text-[#1565C0] text-right">
                                                $636,000</td>
                                        </tr>
                                        <tr class="hover:bg-blue-50/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                                Luisa Fernanda Herrera Díaz</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">
                                                $250,000</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">$30,000
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">
                                                $250,000</td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-black text-[#1565C0] text-right">
                                                $530,000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="p-6 md:p-8 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row justify-end items-center gap-3">
                        <button @click="resetForm()"
                            class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-all">
                            Cancelar
                        </button>
                        <button :disabled="!startDate || !endDate || !periodType || loading"
                            class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-white bg-[#10B981] rounded-lg shadow-md hover:bg-[#0C9467] transition-all disabled:bg-gray-200 disabled:cursor-not-allowed">
                            Guardar periodo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilos personalizados para el input date y select para que se vean más limpios */
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }
    </style>
@endsection