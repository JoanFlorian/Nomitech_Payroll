@extends('layouts.app')

@section('title', 'Devengos')
@section('page-title', 'Devengos')

@section('content')
@php($s3 = $step2Ingresos ?? [])
@php($s2 = $s2 ?? [])

<div class="relative">

    <div class="max-w-6xl mx-auto px-4 md:px-6 lg:px-8 py-6 md:py-8" aria-hidden="true">
        @include('nomina.partials.index_content', ['salarios' => $salarios ?? collect()])
    </div>

    <div class="fixed inset-0 bg-black/50 z-40"></div>

    <div class="fixed inset-0 z-50 p-3 md:p-6 flex items-start md:items-center justify-center overflow-y-auto">

        <div class="relative w-full max-w-6xl bg-white rounded-3xl shadow-2xl border border-gray-200 p-4 md:p-6 modal-enter">

            <a href="{{ route('nomina.step2') }}"
               class="absolute top-4 right-4 h-10 w-10 rounded-full bg-white border border-gray-200 text-gray-600 hover:text-gray-900 flex items-center justify-center shadow-sm transition">
               <span class="text-lg">&times;</span>
            </a>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <div class="lg:col-span-4">

                    <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-blue-500 text-white p-6 md:p-8 shadow-lg">

                        <div class="text-xs uppercase tracking-widest text-blue-100">Nómina</div>

                        <h2 class="text-2xl md:text-3xl font-bold mt-2">
                            Paso 2: Devengos
                        </h2>

                        <p class="text-blue-100 mt-3 text-sm">
                            Otros ingresos.
                        </p>

                        <div class="mt-6">

                            <div class="flex justify-between text-xs font-semibold text-blue-100 mb-2">
                                <span>Progreso</span>
                                <span>Paso 2 de 3</span>
                            </div>

                            <div class="w-full bg-blue-400/40 rounded-full h-2">
                                <div class="bg-white h-2 rounded-full" style="width:66%"></div>
                            </div>

                        </div>

                    </div>

                </div>


                <div class="lg:col-span-8">

                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 md:p-8">

                        <h3 class="text-2xl font-bold text-gray-800 mb-6">
                            Otros ingresos
                        </h3>

                        <form method="POST" action="{{ route('nomina.step2.ingresos.post') }}" id="formStep2Ingresos">
                            @csrf

                            <input type="hidden" id="salario_base_mensual" value="{{ $salarioBase ?? 0 }}">
                            <input type="hidden" id="total_horas_extra" value="{{ $s2['total_horas_extra'] ?? 0 }}">
                            <input type="hidden" id="total_recargos" value="{{ $s2['total_recargos'] ?? 0 }}">
                            <input type="hidden" id="total_devengos_parcial" value="{{ $s2['total_devengos_parcial'] ?? 0 }}">

                            <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50 p-4">

                                <h4 class="text-sm font-semibold text-blue-900 mb-3">
                                    Resumen
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">

                                    <div class="rounded-lg bg-white border border-blue-100 p-3">
                                        <div class="text-xs text-gray-500">Horas + recargos</div>
                                        <div id="resumen_horas_recargos" class="font-semibold text-gray-800">$0</div>
                                    </div>

                                    <div class="rounded-lg bg-white border border-blue-100 p-3">
                                        <div class="text-xs text-gray-500">Devengos parcial</div>
                                        <div id="resumen_parcial" class="font-semibold">$0</div>
                                    </div>

                                    <div class="rounded-lg bg-white border border-blue-100 p-3">
                                        <div class="text-xs text-gray-500">Otros ingresos</div>
                                        <div id="resumen_otros" class="font-semibold">$0</div>
                                    </div>

                                    <div class="rounded-lg bg-white border border-blue-100 p-3">
                                        <div class="text-xs text-gray-500">Devengos final</div>
                                        <div id="resumen_final" class="font-semibold text-blue-700">$0</div>
                                    </div>

                                </div>

                            </div>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Bonificaciones
                                    </label>

                                    <input
                                        name="bonificaciones"
                                        value="{{ old('bonificaciones', $s3['bonificaciones'] ?? 0) }}"
                                        type="text"
                                        inputmode="decimal"
                                        maxlength="15"
                                        class="devengo-input w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs"
                                    >

                                    <p class="text-[11px] text-gray-500 mt-1">Valor entre 0 y 999.999.999.</p>

                                    @error('bonificaciones')
                                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Comisiones
                                    </label>

                                    <input
                                        name="comisiones"
                                        value="{{ old('comisiones', $s3['comisiones'] ?? 0) }}"
                                        type="text"
                                        inputmode="decimal"
                                        maxlength="15"
                                        class="devengo-input w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs"
                                    >

                                    <p class="text-[11px] text-gray-500 mt-1">Valor entre 0 y 999.999.999.</p>

                                    @error('comisiones')
                                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">

                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Otros devengos
                                    </label>

                                    <input
                                        name="otros_devengos"
                                        value="{{ old('otros_devengos', $s3['otros_devengos'] ?? 0) }}"
                                        type="text"
                                        inputmode="decimal"
                                        maxlength="15"
                                        class="devengo-input w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs"
                                    >

                                    <p class="text-[11px] text-gray-500 mt-1">Valor entre 0 y 999.999.999.</p>

                                    @error('otros_devengos')
                                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                                    @enderror

                                </div>

                            </div>


                            <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-3 pt-6 mt-6 border-t border-gray-200">

                                <a href="{{ route('nomina.step2') }}"
                                   class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium text-sm text-center">
                                   ← Volver
                                </a>

                                <button type="submit"
                                        class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm">
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

    const MAX_OTROS_INGRESOS = 999999999;

    const inputs = document.querySelectorAll('.devengo-input');

    const money = v => new Intl.NumberFormat('es-CO',{
        style:'currency',
        currency:'COP',
        maximumFractionDigits:0
    }).format(v || 0);

    const numberFormatter = new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    const formatInputNumber = (v) => numberFormatter.format(Number(v || 0));

    const normalizeNumberString = (raw) => {
        if (raw === '' || raw === null || raw === undefined) return '';

        let normalized = String(raw).replace(/[^\d,.-]/g, '').trim();
        if (!normalized) return '';

        const hasComma = normalized.includes(',');
        const hasDot = normalized.includes('.');

        if (hasComma && hasDot) {
            if (normalized.lastIndexOf(',') > normalized.lastIndexOf('.')) {
                normalized = normalized.replace(/\./g, '').replace(',', '.');
            } else {
                normalized = normalized.replace(/,/g, '');
            }
        } else if (hasDot && !hasComma) {
            const dotCount = (normalized.match(/\./g) || []).length;
            if (dotCount > 1) {
                normalized = normalized.replace(/\./g, '');
            } else {
                const parts = normalized.split('.');
                if (parts.length === 2 && parts[1].length === 3 && parts[0].length >= 1) {
                    normalized = normalized.replace('.', '');
                }
            }
        } else if (hasComma && !hasDot) {
            const commaCount = (normalized.match(/,/g) || []).length;
            if (commaCount > 1) {
                normalized = normalized.replace(/,/g, '');
            } else {
                const parts = normalized.split(',');
                if (parts.length === 2 && parts[1].length === 3 && parts[0].length >= 1) {
                    normalized = normalized.replace(',', '');
                } else {
                    normalized = normalized.replace(',', '.');
                }
            }
        }

        return normalized;
    };

    const toNumber = (raw) => {
        if (raw === '' || raw === null || raw === undefined) return 0;
        const num = Number(normalizeNumberString(raw));
        return Number.isFinite(num) && num >= 0 ? num : 0;
    };

    const salarioBase = toNumber(document.getElementById('salario_base_mensual')?.value || 0);
    const totalHorasExtra = toNumber(document.getElementById('total_horas_extra')?.value || 0);
    const totalRecargos = toNumber(document.getElementById('total_recargos')?.value || 0);
    const parcialFromStep2 = toNumber(document.getElementById('total_devengos_parcial')?.value || 0);

    const get = name => toNumber(document.querySelector(`[name="${name}"]`)?.value || 0);

    const calcular = () => {
        const totalHorasRecargos = totalHorasExtra + totalRecargos;
        const parcial = parcialFromStep2 > 0 ? parcialFromStep2 : (salarioBase + totalHorasRecargos);

        const otros =
            get('bonificaciones') +
            get('comisiones') +
            get('otros_devengos');

        const final = parcial + otros;

        document.getElementById('resumen_horas_recargos').textContent = money(totalHorasRecargos);
        document.getElementById('resumen_parcial').textContent = money(parcial);
        document.getElementById('resumen_otros').textContent = money(otros);
        document.getElementById('resumen_final').textContent = money(final);

    };

    inputs.forEach(input=>{
        input.addEventListener('input', ()=>{
            if (input.value.includes('-')) input.value = input.value.replace('-', '');
            calcular();
        });

        input.addEventListener('blur', ()=>{
            input.value = formatInputNumber(Math.min(get(input.name), MAX_OTROS_INGRESOS));
            calcular();
        });
    });

    inputs.forEach((input) => {
        input.value = formatInputNumber(Math.min(get(input.name), MAX_OTROS_INGRESOS));
    });

    const form = document.getElementById('formStep2Ingresos');
    if (form) {
        form.addEventListener('submit', (event) => {
            let hasErrors = false;

            inputs.forEach((input) => {
                const value = Math.min(get(input.name), MAX_OTROS_INGRESOS);
                input.value = formatInputNumber(value);

                if (!Number.isFinite(value) || value < 0 || value > MAX_OTROS_INGRESOS) {
                    hasErrors = true;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            if (hasErrors) {
                event.preventDefault();
            }
        });
    }

    calcular();

});

</script>

@include('nomina.partials.modal_assets')

@endsection