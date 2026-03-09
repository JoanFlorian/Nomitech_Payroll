@extends('layouts.app')

@section('title', 'Devengos')
@section('page-title', 'Devengos')

@section('content')
@php($s2 = $step2 ?? [])

<div class="relative">
    <div class="max-w-6xl mx-auto px-4 md:px-6 lg:px-8 py-6 md:py-8" aria-hidden="true">
        @include('nomina.partials.index_content', ['salarios' => $salarios ?? collect(), 'periodoActivo' => $periodoActivo ?? null])
    </div>

    <div class="fixed inset-0 bg-gray-900/85 z-40" aria-hidden="true"></div>
    <div class="fixed inset-0 z-40 pointer-events-none overflow-hidden" aria-hidden="true">
        @include('nomina.partials.modal_figures')
    </div>

    <div class="fixed inset-0 z-50 p-4 md:p-6 flex items-center justify-center overflow-y-auto">
        <div class="relative w-full max-w-6xl bg-white rounded-3xl shadow-2xl border border-gray-200 p-4 md:p-5 modal-enter">
            <a href="{{ route('nomina.index') }}"
               class="absolute top-4 right-4 h-10 w-10 rounded-full bg-white/90 border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-white flex items-center justify-center shadow-sm transition"
               aria-label="Cerrar">
                <span class="text-lg">&times;</span>
            </a>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-blue-500 text-white p-6 md:p-8 shadow-lg">
                        <div class="text-xs uppercase tracking-widest text-blue-100">Nueva Nomina</div>
                        <h2 class="text-2xl md:text-3xl font-bold mt-2">Paso 2: Devengos</h2>
                        <p class="text-blue-100 mt-3 text-sm">Registra horas extra y recargos para calcular el valor de este periodo.</p>

                        <div class="mt-6">
                            <div class="flex items-center justify-between text-xs font-semibold text-blue-100 mb-2">
                                <span>Progreso</span>
                                <span>2 de 3</span>
                            </div>
                            <div class="w-full bg-blue-400/40 rounded-full h-2">
                                <div class="bg-white h-2 rounded-full transition-all duration-300" style="width: 66.66%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-blue-100 bg-white/80 p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-800">Resumen de calculos</div>
                        <div class="mt-3 grid grid-cols-1 gap-2 text-xs">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                <div class="text-slate-500">Salario base proporcional</div>
                                <div id="resumen_salario_base" class="font-semibold text-slate-800">$0</div>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                <div class="text-slate-500">Total horas extra</div>
                                <div id="resumen_horas_extra" class="font-semibold text-slate-800">$0</div>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                <div class="text-slate-500">Total recargos</div>
                                <div id="resumen_recargos" class="font-semibold text-slate-800">$0</div>
                            </div>
                            <div class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2">
                                <div class="text-blue-700">Devengos parcial</div>
                                <div id="resumen_total_devengos" class="font-semibold text-blue-800">$0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white/90 backdrop-blur rounded-2xl shadow-md border border-gray-100 p-6 md:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl md:text-3xl font-bold text-gray-800">Horas extra y recargos</h3>
                            <span class="hidden md:inline-flex items-center gap-2 text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full">
                                Calculado en tiempo real
                            </span>
                        </div>

                        <form method="POST" action="{{ route('nomina.step2.post') }}" id="formStep2Horas">
                            @csrf

                            <input type="hidden" id="salario_base_mensual" value="{{ (float) $salarioBase }}">
                            <input type="hidden" id="horas_extra" name="horas_extra" value="{{ old('horas_extra', $s2['horas_extra'] ?? 0) }}">
                            <input type="hidden" id="recargos_total" name="recargos" value="{{ old('recargos', $s2['recargos'] ?? 0) }}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                @foreach($tiposRecargo as $recargo)
                                    @php($valorInicial = old('detalle_recargos.' . $recargo->id_tipo_hora_recargo, $s2['detalle_recargos'][$recargo->id_tipo_hora_recargo] ?? 0))
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            {{ $recargo->nombre }}
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            max="744"
                                            step="1"
                                            name="detalle_recargos[{{ $recargo->id_tipo_hora_recargo }}]"
                                            value="{{ $valorInicial }}"
                                            data-rate="{{ (float) $recargo->valor }}"
                                            data-nombre="{{ mb_strtolower($recargo->nombre, 'UTF-8') }}"
                                            class="devengo-input w-full border-2 border-gray-300 px-4 py-3 rounded-xl text-sm focus:border-blue-500 focus:outline-none transition bg-white shadow-sm"
                                        >

                                        <p class="text-xs text-gray-500 mt-1">
                                            Valor calculado:
                                            <span class="valor-recargo font-semibold">$0</span>
                                        </p>
                                    </div>
                                @endforeach
                            </div>

                            @if($errors->any())
                                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-6 mt-6 border-t border-gray-200">
                                <a href="{{ route('nomina.step1') }}"
                                   class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition text-sm text-center">
                                    ← Anterior
                                </a>
                                <button type="submit"
                                        class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition shadow-md hover:shadow-lg text-sm">
                                    Siguiente →
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputs = Array.from(document.querySelectorAll('.devengo-input'));
        const parseLocalizedNumber = (raw) => {
            if (raw === '' || raw === null || raw === undefined) return 0;

            let normalized = String(raw).replace(/[^\d,.-]/g, '').trim();
            if (!normalized) return 0;

            const hasComma = normalized.includes(',');
            const hasDot = normalized.includes('.');

            if (hasComma && hasDot) {
                if (normalized.lastIndexOf(',') > normalized.lastIndexOf('.')) {
                    normalized = normalized.replace(/\./g, '').replace(',', '.');
                } else {
                    normalized = normalized.replace(/,/g, '');
                }
            } else if (hasDot && !hasComma) {
                const parts = normalized.split('.');
                if (parts.length >= 2 && parts[parts.length - 1].length === 3) {
                    normalized = normalized.replace(/\./g, '');
                }
            } else if (hasComma && !hasDot) {
                const parts = normalized.split(',');
                if (parts.length >= 2 && parts[parts.length - 1].length === 3) {
                    normalized = normalized.replace(/,/g, '');
                } else {
                    normalized = normalized.replace(',', '.');
                }
            }

            const parsed = Number(normalized);
            return Number.isFinite(parsed) ? parsed : 0;
        };

        const normalizeRateFactor = (rawRate) => {
            const rate = parseLocalizedNumber(rawRate);

            if (!Number.isFinite(rate) || rate <= 0) return 0;

            // Convierte porcentaje a factor decimal: 25->0.25, 75->0.75, 125->1.25.
            if (rate > 10) {
                return rate / 100;
            }

            // Si ya viene como factor (0.35, 0.75, 1.25, etc.), se usa directo.
            return rate;
        };

        const baseMensual = parseLocalizedNumber(document.getElementById('salario_base_mensual')?.value || 0);
        // Normativa: valor_hora = salario_base / 240.
        const horasMes = 240;
        const inputHorasExtra = document.getElementById('horas_extra');
        const inputRecargosTotal = document.getElementById('recargos_total');
        const initialHorasExtra = parseLocalizedNumber(inputHorasExtra?.value || 0);
        const initialRecargos = parseLocalizedNumber(inputRecargosTotal?.value || 0);
        let userEditedDetalle = false;

        const resumenSalarioBase = document.getElementById('resumen_salario_base');
        const resumenHorasExtra = document.getElementById('resumen_horas_extra');
        const resumenRecargos = document.getElementById('resumen_recargos');
        const resumenDevengos = document.getElementById('resumen_total_devengos');

        const asPesos = (value) => Math.round(Number(value || 0));
        const money = (value) => new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            maximumFractionDigits: 0,
        }).format(asPesos(value));

        const limpiarNumero = (value) => {
            const parsed = Number(value || 0);
            if (!Number.isFinite(parsed) || parsed < 0) {
                return 0;
            }
            return Math.min(parsed, 744);
        };

        function calcular() {
            const valorHora = (baseMensual > 0 && horasMes > 0) ? (baseMensual / horasMes) : 0;
            let totalHorasExtra = 0;
            let totalRecargos = 0;
            let totalDetalleHoras = 0;

            inputs.forEach((input) => {
                const horas = limpiarNumero(input.value);
                input.value = String(horas);
                totalDetalleHoras += horas;

                const rateFactor = normalizeRateFactor(input.dataset.rate || 0);
                const nombre = String(input.dataset.nombre || '');

                // En horas extra, si llega porcentaje puro (0.25), se suma el 100% base -> 1.25.
                const factorAplicado = nombre.includes('extra') && rateFactor < 1
                    ? (1 + rateFactor)
                    : rateFactor;

                const valor = Number(horas) * (Number(valorHora) * Number(factorAplicado));

                const span = input.parentElement.querySelector('.valor-recargo');
                if (span) {
                    span.textContent = money(valor);
                }

                if (nombre.includes('extra')) {
                    totalHorasExtra += valor;
                } else {
                    totalRecargos += valor;
                }
            });

            if (!userEditedDetalle && totalDetalleHoras === 0 && (initialHorasExtra > 0 || initialRecargos > 0)) {
                totalHorasExtra = initialHorasExtra;
                totalRecargos = initialRecargos;
            }

            const totalDevengos = baseMensual + totalHorasExtra + totalRecargos;

            if (inputHorasExtra) {
                inputHorasExtra.value = totalHorasExtra.toFixed(2);
            }
            if (inputRecargosTotal) {
                inputRecargosTotal.value = totalRecargos.toFixed(2);
            }

            if (resumenSalarioBase) resumenSalarioBase.textContent = money(baseMensual);
            if (resumenHorasExtra) resumenHorasExtra.textContent = money(totalHorasExtra);
            if (resumenRecargos) resumenRecargos.textContent = money(totalRecargos);
            if (resumenDevengos) resumenDevengos.textContent = money(totalDevengos);
        }

        inputs.forEach((input) => {
            input.addEventListener('input', () => {
                userEditedDetalle = true;
                calcular();
            });
            input.addEventListener('blur', () => {
                userEditedDetalle = true;
                calcular();
            });
        });

        calcular();
    });
</script>

@include('nomina.partials.modal_assets')
@endsection