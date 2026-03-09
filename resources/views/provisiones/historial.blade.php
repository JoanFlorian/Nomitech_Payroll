@extends('layouts.app')

@section('title', 'Historial de Prestaciones')
@section('page-title', 'Historial de Prestaciones')

@section('content')
    <div>
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-lg"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $empleadoNombre }}</h2>
                <p class="text-sm text-gray-500 italic">Documento: {{ $doc }} · Historial completo de movimientos del
                    ledger.</p>
            </div>
            <a href="{{ route('provisiones.index') }}"
                class="w-full md:w-auto bg-white text-gray-700 font-bold py-2.5 px-6 rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-all duration-300 flex items-center justify-center gap-2">
                <i class="bi bi-arrow-left"></i>
                Volver a Provisiones
            </a>
        </div>

        {{-- Movements Table --}}
        @if($movements->isEmpty())
            <div class="text-center py-16">
                <i class="bi bi-journal-x text-5xl text-gray-300 mb-4 block"></i>
                <p class="text-gray-400 text-lg font-medium">No hay movimientos registrados para este empleado.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Fecha</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Origen
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Prestación
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Valor
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Referencia
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @foreach($movements as $movement)
                            @php
                                $isNegative = $movement->amount < 0;
                                $badgeColors = match ($movement->movement_type) {
                                    'accrual' => 'bg-blue-50 text-blue-700',
                                    'payment' => 'bg-red-50 text-red-700',
                                    'adjustment' => 'bg-amber-50 text-amber-700',
                                    'initial' => 'bg-emerald-50 text-emerald-700',
                                    default => 'bg-gray-50 text-gray-700',
                                };
                                $sourceBadge = match ($movement->source) {
                                    'payroll' => 'bg-blue-50 text-blue-600',
                                    'liquidation' => 'bg-red-50 text-red-600',
                                    'migration' => 'bg-purple-50 text-purple-600',
                                    'manual' => 'bg-amber-50 text-amber-600',
                                    default => 'bg-gray-50 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $movement->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeColors }}">
                                        {{ \App\Models\BenefitLedger::movementTypeLabel($movement->movement_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $sourceBadge }}">
                                        {{ ucfirst($movement->source) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                    {{ \App\Models\BenefitLedger::benefitTypeLabel($movement->benefit_type) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $isNegative ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ $isNegative ? '-' : '+' }}${{ number_format(abs($movement->amount), 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $movement->reference }}">
                                    {{ $movement->reference ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
@endsection