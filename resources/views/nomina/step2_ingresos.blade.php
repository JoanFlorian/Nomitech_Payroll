@extends('layouts.app')

@section('title', 'Devengos')
@section('page-title', 'Devengos')

@section('content')
@php($s3 = $step2Ingresos ?? [])
@php($s2 = $s2 ?? [])
@php($auxilioDb = (float) ($auxilioTransporteDb ?? 0))
@php($aplicaTope = (bool) ($aplicaPorTope ?? true))
@php($aplicaAuxilio = (int) old('aplica_auxilio_transporte', $s3['aplica_auxilio_transporte'] ?? ($auxilioDb > 0 ? 1 : 0)))
@php($auxilioActual = (float) old('auxilio_transporte', $s3['auxilio_transporte'] ?? ($aplicaAuxilio ? $auxilioDb : 0)))
@php($totalHorasExtraS2 = (float) ($s2['total_horas_extra'] ?? $s2['horas_extra'] ?? 0))
@php($totalRecargosS2 = (float) ($s2['total_recargos'] ?? $s2['recargos'] ?? 0))
@php($totalParcialS2 = (float) ($s2['total_devengos_parcial'] ?? (($salarioBase ?? 0) + $totalHorasExtraS2 + $totalRecargosS2)))

<div class="relative">

    <div class="max-w-6xl mx-auto px-4 md:px-6 lg:px-8 py-6 md:py-8" aria-hidden="true">
        @include('nomina.partials.index_content', ['salarios' => $salarios ?? collect()])
    </div>

    <div class="fixed inset-0 bg-gray-900/85 z-40" aria-hidden="true"></div>
    <div class="fixed inset-0 z-40 pointer-events-none overflow-hidden" aria-hidden="true">
        @include('nomina.partials.modal_figures')
    </div>

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

                    <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
                        <h4 class="text-sm font-semibold text-blue-900 mb-3">
                            Resumen
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
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

                </div>


                <div class="lg:col-span-8">

                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 md:p-8">

                        <h3 class="text-2xl font-bold text-gray-800 mb-6">
                            Otros ingresos
                        </h3>

                        @if(($step2AutoRecalculated ?? false) === true)
                            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                Se recalcularon automaticamente las horas y recargos para corregir valores inconsistentes del paso anterior.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('nomina.step2.ingresos.post') }}" id="formStep2Ingresos">
                            @csrf

                            <input type="hidden" id="salario_base_mensual" value="{{ $salarioBase ?? 0 }}">
                            <input type="hidden" id="total_horas_extra" value="{{ $totalHorasExtraS2 }}">
                            <input type="hidden" id="total_recargos" value="{{ $totalRecargosS2 }}">
                            <input type="hidden" id="total_devengos_parcial" value="{{ $totalParcialS2 }}">
                            <input type="hidden" id="auxilio_base_db" value="{{ $auxilioDb }}">
                            <input type="hidden" id="aplica_por_tope" value="{{ $aplicaTope ? 1 : 0 }}">

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
                                        autocomplete="off"
                                        maxlength="15"
                                        class="devengo-input w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs"
                                    >

                                    <p class="text-[11px] text-gray-500 mt-1">Valor entre 0 y 999.999.999.999.</p>

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
                                        autocomplete="off"
                                        maxlength="15"
                                        class="devengo-input w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs"
                                    >

                                    <p class="text-[11px] text-gray-500 mt-1">Valor entre 0 y 999.999.999.999.</p>

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
                                        autocomplete="off"
                                        maxlength="15"
                                        class="devengo-input w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs"
                                    >

                                    <p class="text-[11px] text-gray-500 mt-1">Valor entre 0 y 999.999.999.999.</p>

                                    @error('otros_devengos')
                                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                                    @enderror

                                </div>

                                <div class="md:col-span-2 rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                                        Auxilio de transporte
                                    </label>

                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <button type="button" id="btn_auxilio_si"
                                            class="px-4 py-2 text-xs font-bold rounded-lg border transition">Si aplica</button>
                                        <button type="button" id="btn_auxilio_no"
                                            class="px-4 py-2 text-xs font-bold rounded-lg border transition">No aplica</button>
                                    </div>

                                    <input type="hidden" name="aplica_auxilio_transporte" id="aplica_auxilio_transporte" value="{{ $aplicaAuxilio ? 1 : 0 }}">

                                    <input
                                        id="auxilio_transporte"
                                        name="auxilio_transporte"
                                        value="{{ $auxilioActual }}"
                                        type="text"
                                        inputmode="decimal"
                                        readonly
                                        class="w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs bg-white"
                                    >

                                    @if(!$aplicaTope)
                                        <p class="text-[11px] text-amber-700 mt-2">No aplica por tope salarial configurado.</p>
                                    @else
                                        <p class="text-[11px] text-gray-500 mt-2">Se calcula automaticamente desde parametros de nomina.</p>
                                    @endif
                                </div>

                                {{-- Informational: Integrated benefit payments --}}
                                @if(isset($benefitPayments) && $benefitPayments->isNotEmpty())
                                    <div class="md:col-span-2 rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                                        <label class="block text-sm font-semibold text-emerald-800 mb-3">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Prestaciones integradas a esta nómina
                                        </label>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            @foreach($benefitPayments as $bp)
                                                <div class="rounded-lg bg-white border border-emerald-200 p-3">
                                                    <div class="text-xs text-gray-500 mb-1">
                                                        {{ \App\Models\BenefitLedger::benefitTypeLabel($bp->benefit_type) }}
                                                    </div>
                                                    <input
                                                        type="text"
                                                        value="${{ number_format(abs($bp->amount), 0, ',', '.') }}"
                                                        readonly
                                                        class="w-full border-2 border-emerald-300 px-3 py-2 rounded-lg text-xs bg-emerald-50 text-emerald-800 font-semibold cursor-not-allowed"
                                                    >
                                                </div>
                                            @endforeach
                                        </div>

                                        <p class="text-[11px] text-emerald-700 mt-2">
                                            Estos valores fueron integrados desde el módulo de Provisiones y se incluirán en el pago de nómina del empleado.
                                        </p>
                                    </div>
                                @endif

                            </div>

                            <p id="otrosIngresosError" class="hidden mt-4 text-sm text-red-600 font-medium"></p>


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

    const MAX_OTROS_INGRESOS = 999999999999;
    const MAX_RESUMEN_VALOR = 999999999999;

    const inputs = document.querySelectorAll('.devengo-input');
    const allowedControlKeys = new Set(['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End']);

    const getInlineErrorNode = (input) => {
        let node = input.parentElement.querySelector('.numeric-inline-error');
        if (!node) {
            node = document.createElement('p');
            node.className = 'numeric-inline-error hidden text-xs text-red-600 mt-1 font-medium';
            input.parentElement.appendChild(node);
        }
        return node;
    };

    const showInlineError = (input, message) => {
        input.classList.add('border-red-500');
        const node = getInlineErrorNode(input);
        node.textContent = message;
        node.classList.remove('hidden');
    };

    const clearInlineError = (input) => {
        input.classList.remove('border-red-500');
        const node = input.parentElement.querySelector('.numeric-inline-error');
        if (node) {
            node.classList.add('hidden');
        }
    };

    const sanitizeCurrencyValue = (raw) => {
        const value = Number(raw || 0);
        if (!Number.isFinite(value) || value < 0) return 0;
        if (value > MAX_RESUMEN_VALOR) return 0;
        return value;
    };

    const asPesos = (value) => Math.round(sanitizeCurrencyValue(value));
    const money = v => new Intl.NumberFormat('es-CO',{
        style:'currency',
        currency:'COP',
        maximumFractionDigits:0
    }).format(asPesos(v));

    const numberFormatter = new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
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
                // Solo tratar como miles cuando el bloque final es exactamente de 3 digitos (ej: 13.906).
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
                // Solo tratar como miles cuando el bloque final es exactamente de 3 digitos (ej: 13,906).
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

    const salarioBase = sanitizeCurrencyValue(toNumber(document.getElementById('salario_base_mensual')?.value || 0));
    const totalHorasExtra = sanitizeCurrencyValue(toNumber(document.getElementById('total_horas_extra')?.value || 0));
    const totalRecargos = sanitizeCurrencyValue(toNumber(document.getElementById('total_recargos')?.value || 0));
    const auxilioBaseDb = toNumber(document.getElementById('auxilio_base_db')?.value || 0);
    const aplicaPorTope = Number(document.getElementById('aplica_por_tope')?.value || 0) === 1;

    const btnAuxilioSi = document.getElementById('btn_auxilio_si');
    const btnAuxilioNo = document.getElementById('btn_auxilio_no');
    const inputAplicaAuxilio = document.getElementById('aplica_auxilio_transporte');
    const inputAuxilio = document.getElementById('auxilio_transporte');

    const get = name => toNumber(document.querySelector(`[name="${name}"]`)?.value || 0);

    const obtenerAuxilioActual = () => {
        if (!inputAplicaAuxilio || !inputAuxilio) return 0;
        if (Number(inputAplicaAuxilio.value || 0) !== 1) return 0;
        return toNumber(inputAuxilio.value || 0);
    };

    const pintarBotonesAuxilio = () => {
        const aplica = Number(inputAplicaAuxilio?.value || 0) === 1;

        if (btnAuxilioSi) {
            btnAuxilioSi.className = aplica
                ? 'px-4 py-2 text-xs font-bold rounded-lg border border-blue-600 bg-blue-600 text-white transition'
                : 'px-4 py-2 text-xs font-bold rounded-lg border border-gray-300 bg-white text-gray-600 transition';
            btnAuxilioSi.disabled = !aplicaPorTope;
        }

        if (btnAuxilioNo) {
            btnAuxilioNo.className = !aplica
                ? 'px-4 py-2 text-xs font-bold rounded-lg border border-slate-700 bg-slate-700 text-white transition'
                : 'px-4 py-2 text-xs font-bold rounded-lg border border-gray-300 bg-white text-gray-600 transition';
        }
    };

    const setEstadoAuxilio = (aplica) => {
        if (!inputAplicaAuxilio || !inputAuxilio) return;

        const valorAplica = aplica && aplicaPorTope;
        inputAplicaAuxilio.value = valorAplica ? '1' : '0';
        inputAuxilio.value = formatInputNumber(valorAplica ? auxilioBaseDb : 0);
        inputAuxilio.readOnly = true;
        inputAuxilio.classList.toggle('opacity-60', !valorAplica);

        pintarBotonesAuxilio();
        calcular();
    };

    const calcular = () => {
        const totalHorasRecargosRaw = totalHorasExtra + totalRecargos;
        const parcialRaw = salarioBase + totalHorasRecargosRaw;

        const otros =
            get('bonificaciones') +
            get('comisiones') +
            get('otros_devengos') +
            obtenerAuxilioActual();

        const totalHorasRecargos = sanitizeCurrencyValue(totalHorasRecargosRaw);
        const parcial = sanitizeCurrencyValue(parcialRaw);
        const final = sanitizeCurrencyValue(parcial + otros);

        document.getElementById('resumen_horas_recargos').textContent = money(totalHorasRecargos);
        document.getElementById('resumen_parcial').textContent = money(parcial);
        document.getElementById('resumen_otros').textContent = money(otros);
        document.getElementById('resumen_final').textContent = money(final);

    };

    inputs.forEach(input=>{
        input.addEventListener('keydown', (event) => {
            if (event.ctrlKey || event.metaKey || event.altKey || allowedControlKeys.has(event.key)) {
                return;
            }

            if (!/^[0-9.,]$/.test(event.key)) {
                event.preventDefault();
                showInlineError(input, 'Solo se permiten números y separadores decimales.');
            }
        });

        input.addEventListener('beforeinput', (event) => {
            if (!event.data) {
                return;
            }

            if (!/^[0-9.,]+$/.test(event.data)) {
                event.preventDefault();
                showInlineError(input, 'Solo se permiten números y separadores decimales.');
            }
        });

        input.addEventListener('paste', (event) => {
            event.preventDefault();
            const text = (event.clipboardData || window.clipboardData).getData('text') || '';
            if (text.trim() !== '' && text !== sanitizeMoneyText(text)) {
                showInlineError(input, 'El pegado contenía letras o símbolos inválidos.');
            } else {
                clearInlineError(input);
            }
            input.value = sanitizeMoneyText(text);
            input.classList.remove('border-red-500');
            calcular();
        });

        input.addEventListener('input', ()=>{
            input.value = sanitizeMoneyText(input.value);
            if (input.value.includes('-')) input.value = input.value.replace('-', '');
            clearInlineError(input);
            calcular();
        });

        input.addEventListener('blur', ()=>{
            clearInlineError(input);
            input.value = formatInputNumber(Math.min(get(input.name), MAX_OTROS_INGRESOS));
            calcular();
        });
    });

    inputs.forEach((input) => {
        input.value = formatInputNumber(Math.min(get(input.name), MAX_OTROS_INGRESOS));
    });

    const form = document.getElementById('formStep2Ingresos');
    const errorBox = document.getElementById('otrosIngresosError');

    const isValidMoneyText = (value) => {
        const raw = String(value ?? '').trim();
        if (raw === '') return true;
        return /^\d[\d.,]*$/.test(raw);
    };

    const sanitizeMoneyText = (value) => String(value ?? '').replace(/[^\d.,]/g, '');

    inputs.forEach((input) => {
        input.value = sanitizeMoneyText(input.value);
    });

    if (form) {
        form.addEventListener('submit', (event) => {
            let hasErrors = false;
            if (errorBox) {
                errorBox.classList.add('hidden');
                errorBox.textContent = '';
            }

            inputs.forEach((input) => {
                input.value = sanitizeMoneyText(input.value);
                if (!isValidMoneyText(input.value)) {
                    hasErrors = true;
                    input.classList.add('border-red-500');
                    return;
                }

                const value = Math.min(get(input.name), MAX_OTROS_INGRESOS);

                if (!Number.isFinite(value) || value < 0 || value > MAX_OTROS_INGRESOS) {
                    hasErrors = true;
                    input.classList.add('border-red-500');
                    input.value = formatInputNumber(value);
                } else {
                    input.classList.remove('border-red-500');
                    // Set to unformatted number right before submit
                    input.value = value;
                }
            });
            
            if (inputAuxilio && typeof obtenerAuxilioActual === 'function') {
                inputAuxilio.value = obtenerAuxilioActual();
            }

            if (hasErrors) {
                event.preventDefault();
                if (errorBox) {
                    errorBox.textContent = 'Ingresa solo números no negativos. Puedes usar punto como separador de miles.';
                    errorBox.classList.remove('hidden');
                }
            }
        });
    }

    if (btnAuxilioSi) {
        btnAuxilioSi.addEventListener('click', () => setEstadoAuxilio(true));
    }

    if (btnAuxilioNo) {
        btnAuxilioNo.addEventListener('click', () => setEstadoAuxilio(false));
    }

    setEstadoAuxilio(Number(inputAplicaAuxilio?.value || 0) === 1);

    calcular();

});

</script>

@include('nomina.partials.modal_assets')

@endsection