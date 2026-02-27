@extends('layouts.app')

@section('title', 'Devengos')
@section('page-title', 'Devengos')

@section('content')
@php($s2 = $step2 ?? [])

<div class="relative">
    <div class="max-w-6xl mx-auto px-4 md:px-6 lg:px-8 py-6 md:py-8" aria-hidden="true">
        @include('nomina.partials.index_content', ['salarios' => $salarios ?? collect()])
    </div>

    <div class="fixed inset-0 bg-black/50 z-40" aria-hidden="true"></div>

    <div class="fixed inset-0 z-50 p-3 md:p-6 flex items-start md:items-center justify-center overflow-y-auto">
        <div class="relative w-full max-w-6xl max-h-[calc(100vh-1.5rem)] md:max-h-[calc(100vh-3rem)] overflow-y-auto bg-white rounded-3xl shadow-2xl border border-gray-200 p-4 md:p-6 modal-enter">
            <a href="{{ route('nomina.step1') }}" class="absolute top-4 right-4 h-10 w-10 rounded-full bg-white/90 border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-white flex items-center justify-center shadow-sm transition" aria-label="Cerrar"><span class="text-lg">&times;</span></a>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4">
                    <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-blue-500 text-white p-6 md:p-8 shadow-lg">
                        <div class="text-xs uppercase tracking-widest text-blue-100">Nómina</div>
                        <h2 class="text-2xl md:text-3xl font-bold mt-2">Paso 2: Devengos</h2>
                        <p class="text-blue-100 mt-3 text-sm">Horas extras y recargos.</p>
                        <div class="mt-6">
                            <div class="flex items-center justify-between text-xs font-semibold text-blue-100 mb-2"><span>Progreso</span><span>Paso 2 de 3</span></div>
                            <div class="w-full bg-blue-400/40 rounded-full h-2"><div class="bg-white h-2 rounded-full" style="width:66.66%"></div></div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white/90 rounded-2xl shadow-md border border-gray-100 p-6 md:p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Horas extras y recargos</h3>

                        <form method="POST" action="{{ route('nomina.step2.post') }}" id="formStep2Horas">
                            @csrf
                            <input type="hidden" id="salario_base_mensual" value="{{ $salarioBase ?? 0 }}">

                            <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                                <h4 class="text-sm font-semibold text-blue-900 mb-3">Resumen</h4>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                                    <div class="rounded-lg bg-white border border-blue-100 p-3"><div class="text-xs text-gray-500">Salario base</div><div id="resumen_salario_base" class="font-semibold text-gray-800">$0</div></div>
                                    <div class="rounded-lg bg-white border border-blue-100 p-3"><div class="text-xs text-gray-500">Total horas extra</div><div id="resumen_horas_extra" class="font-semibold text-gray-800">$0</div></div>
                                    <div class="rounded-lg bg-white border border-blue-100 p-3"><div class="text-xs text-gray-500">Total recargos</div><div id="resumen_recargos" class="font-semibold text-gray-800">$0</div></div>
                                    <div class="rounded-lg bg-white border border-blue-100 p-3"><div class="text-xs text-gray-500">Devengos parcial</div><div id="resumen_total_devengos" class="font-semibold text-blue-700">$0</div></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                @foreach ([
                                    'horas_extra_diurnas' => 'Horas extra diurnas',
                                    'horas_extra_nocturnas' => 'Horas extra nocturnas',
                                    'horas_extra_dominicales_diurnas' => 'Horas extra dominicales/festivas diurnas',
                                    'horas_extra_dominicales_nocturnas' => 'Horas extra dominicales/festivas nocturnas',
                                    'recargo_nocturno' => 'Recargo nocturno',
                                    'recargo_dominical_diurno' => 'Recargo dominical diurno',
                                    'recargo_dominical_nocturno' => 'Recargo dominical nocturno',
                                    'recargo_festivo_diurno' => 'Recargo festivo diurno',
                                    'recargo_festivo_nocturno' => 'Recargo festivo nocturno',
                                ] as $name => $label)
                                    <div class="{{ in_array($name, ['recargo_festivo_diurno','recargo_festivo_nocturno']) ? 'md:col-span-1' : '' }}">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $label }}</label>
                                        <input name="{{ $name }}" value="{{ old($name, $s2[$name] ?? 0) }}" type="number" min="0" step="0.01" class="devengo-input w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs focus:border-blue-500 focus:outline-none transition bg-white shadow-sm" placeholder="0">
                                        <p class="text-xs text-gray-500 mt-1">Valor calculado: <span id="valor_{{ $name }}" class="font-semibold text-gray-700">$0</span></p>
                                        @error($name)<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>

                            <p id="step2Error" class="hidden text-sm text-red-600 font-medium mt-4"></p>

                            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-6 mt-6 border-t border-gray-200">
                                <a href="{{ route('nomina.step1') }}" class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition text-sm text-center">← Volver</a>
                                <button type="submit" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition text-sm">Siguiente →</button>
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
    const form = document.getElementById('formStep2Horas');
    const inputs = Array.from(document.querySelectorAll('.devengo-input'));
    const baseMensual = Number(document.getElementById('salario_base_mensual').value || 0);
    const errorBox = document.getElementById('step2Error');
    const money = (v) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v || 0);
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
        return Number.isFinite(num) && num >= 0 ? num : NaN;
    };
    const sanitizeInput = (input) => {
        const parsed = toNumber(input.value);
        if (Number.isNaN(parsed)) { input.classList.add('border-red-500'); return false; }
        input.value = parsed.toString(); input.classList.remove('border-red-500'); return true;
    };
    const getValue = (name) => toNumber((form.querySelector(`[name="${name}"]`) || {}).value) || 0;

    const rates = {
        horas_extra_diurnas: 1.25,
        horas_extra_nocturnas: 1.75,
        horas_extra_dominicales_diurnas: 2.0,
        horas_extra_dominicales_nocturnas: 2.5,
        recargo_nocturno: 0.35,
        recargo_dominical_diurno: 0.75,
        recargo_dominical_nocturno: 1.10,
        recargo_festivo_diurno: 0.75,
        recargo_festivo_nocturno: 1.10,
    };

    const calcular = () => {
        const horaNormal = baseMensual / 240;
        const conceptos = Object.keys(rates).reduce((acc, key) => {
            acc[key] = getValue(key) * (horaNormal * rates[key]);
            const el = document.getElementById(`valor_${key}`);
            if (el) el.textContent = money(acc[key]);
            return acc;
        }, {});

        const totalHorasExtra = conceptos.horas_extra_diurnas + conceptos.horas_extra_nocturnas + conceptos.horas_extra_dominicales_diurnas + conceptos.horas_extra_dominicales_nocturnas;
        const totalRecargos = conceptos.recargo_nocturno + conceptos.recargo_dominical_diurno + conceptos.recargo_dominical_nocturno + conceptos.recargo_festivo_diurno + conceptos.recargo_festivo_nocturno;
        const totalParcial = baseMensual + totalHorasExtra + totalRecargos;

        document.getElementById('resumen_salario_base').textContent = money(baseMensual);
        document.getElementById('resumen_horas_extra').textContent = money(totalHorasExtra);
        document.getElementById('resumen_recargos').textContent = money(totalRecargos);
        document.getElementById('resumen_total_devengos').textContent = money(totalParcial);
    };

    inputs.forEach((input) => {
        input.addEventListener('input', () => { if (input.value.includes('-')) input.value = input.value.replace('-', ''); calcular(); });
        input.addEventListener('blur', () => { sanitizeInput(input); errorBox.classList.add('hidden'); calcular(); });
    });

    form.addEventListener('submit', (e) => {
        let ok = true;
        inputs.forEach((input) => { if (!sanitizeInput(input)) ok = false; });
        if (!ok) { e.preventDefault(); errorBox.textContent = 'Solo números positivos o cero.'; errorBox.classList.remove('hidden'); }
    });

    calcular();
});
</script>
@include('nomina.partials.modal_assets')
@endsection
