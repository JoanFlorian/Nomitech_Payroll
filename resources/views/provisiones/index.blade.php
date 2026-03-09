@extends('layouts.app')

@section('title', 'Provisiones')
@section('page-title', 'Provisiones')

@section('content')
    <div x-data="{
            liquidarModal: false,
            masivoModal: false,
            selectedEmployee: '',
            selectedBenefit: '',
            selectedAmount: 0,
            masivoBenefit: '',
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

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Provisiones de Prestaciones Sociales</h2>
                <p class="text-sm text-gray-500 italic">Balances actuales y gestión de prestaciones por empleado.</p>
            </div>
            <button @click="masivoModal = true"
                class="w-full md:w-auto bg-[#1565C0] text-white font-bold py-2.5 px-6 rounded-lg shadow-md hover:bg-[#0D47A1] transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2">
                <i class="bi bi-people"></i>
                Liquidación Masiva
            </button>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 p-5 rounded-xl border border-blue-100 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <span class="p-1.5 bg-blue-100 text-[#1565C0] rounded-md"><i class="bi bi-gift"></i></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-400">Prima</span>
                </div>
                <p class="text-xl font-black text-[#1565C0]">${{ number_format($totals['prima'], 0, ',', '.') }}</p>
            </div>
            <div
                class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-5 rounded-xl border border-emerald-100 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <span class="p-1.5 bg-emerald-100 text-emerald-600 rounded-md"><i class="bi bi-bank"></i></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Cesantías</span>
                </div>
                <p class="text-xl font-black text-emerald-600">${{ number_format($totals['cesantias'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 p-5 rounded-xl border border-amber-100 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <span class="p-1.5 bg-amber-100 text-amber-600 rounded-md"><i class="bi bi-percent"></i></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-400">Intereses</span>
                </div>
                <p class="text-xl font-black text-amber-600">${{ number_format($totals['intereses'], 0, ',', '.') }}</p>
            </div>
            <div
                class="bg-gradient-to-br from-purple-50 to-purple-100/50 p-5 rounded-xl border border-purple-100 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <span class="p-1.5 bg-purple-100 text-purple-600 rounded-md"><i class="bi bi-sun"></i></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-purple-400">Vacaciones</span>
                </div>
                <p class="text-xl font-black text-purple-600">${{ number_format($totals['vacaciones'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 p-5 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <span class="p-1.5 bg-gray-200 text-gray-600 rounded-md"><i class="bi bi-calculator"></i></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total</span>
                </div>
                <p class="text-xl font-black text-gray-800">${{ number_format($totals['total'], 0, ',', '.') }}</p>
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
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Empleado
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Prima
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Cesantías
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Intereses
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">
                                Vacaciones</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @foreach($balances as $balance)
                            @php
                                $nombre = $balance->usuario
                                    ? trim($balance->usuario->nombres . ' ' . $balance->usuario->apellidos)
                                    : $balance->employee_id;
                                $totalEmpleado = $balance->prima_balance + $balance->cesantias_balance + $balance->intereses_balance + $balance->vacaciones_balance;
                            @endphp
                            <tr class="hover:bg-blue-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-800">{{ $nombre }}</div>
                                    <div class="text-xs text-gray-400">{{ $balance->employee_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right font-medium">
                                    ${{ number_format($balance->prima_balance, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right font-medium">
                                    ${{ number_format($balance->cesantias_balance, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right font-medium">
                                    ${{ number_format($balance->intereses_balance, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right font-medium">
                                    ${{ number_format($balance->vacaciones_balance, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('provisiones.historial', $balance->employee_id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-[#1565C0] bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                                            title="Ver historial">
                                            <i class="bi bi-clock-history"></i> Historial
                                        </a>
                                        @if($totalEmpleado > 0)
                                            <button
                                                @click="liquidarModal = true; selectedEmployee = '{{ $balance->employee_id }}'; selectedAmount = 0; selectedBenefit = ''"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-[#10B981] rounded-lg hover:bg-[#0C9467] transition-colors"
                                                title="Liquidar prestación">
                                                <i class="bi bi-cash-stack"></i> Liquidar
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- MODAL: Liquidación Individual --}}
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50" x-show="liquidarModal" x-cloak
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div @click.away="liquidarModal = false"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative overflow-hidden">
                <div class="absolute inset-0 pointer-events-none opacity-5">
                    <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-[#10B981]"></div>
                </div>
                <div class="relative z-10">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800">Liquidar Prestación</h2>
                        <button @click="liquidarModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('provisiones.liquidar.individual') }}" class="p-6 space-y-5">
                        @csrf
                        <input type="hidden" name="employee_id" :value="selectedEmployee">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Prestación</label>
                            <select name="benefit_type" x-model="selectedBenefit" required
                                class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-[#10B981] focus:border-[#10B981] h-11">
                                <option value="" disabled>Seleccionar...</option>
                                <option value="prima">Prima</option>
                                <option value="cesantias">Cesantías</option>
                                <option value="intereses_cesantias">Intereses de Cesantías</option>
                                <option value="vacaciones">Vacaciones</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Monto a Liquidar</label>
                            <input type="number" name="amount" x-model="selectedAmount" step="0.01" min="0.01" required
                                class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-[#10B981] focus:border-[#10B981] h-11"
                                placeholder="Ej: 500000">
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="liquidarModal = false"
                                class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-all">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 text-sm font-bold text-white bg-[#10B981] rounded-lg shadow-md hover:bg-[#0C9467] transition-all">
                                <i class="bi bi-check2-circle mr-1"></i> Confirmar Liquidación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL: Liquidación Masiva --}}
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
                        <button @click="masivoModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
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
                                    <p class="text-sm text-amber-600 mt-1">Esta acción liquidará la prestación seleccionada
                                        para <strong>todos los empleados</strong> que tengan saldo positivo. Los balances se
                                        pondrán en cero.</p>
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
                                <option value="vacaciones">Vacaciones</option>
                            </select>
                        </div>

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
    </div>
@endsection