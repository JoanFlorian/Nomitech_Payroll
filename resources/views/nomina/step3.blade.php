@extends('layouts.app')

@section('title', 'Deducciones')
@section('page-title', 'Deducciones')

@section('content')
<div class="relative">
    <div class="max-w-6xl mx-auto px-4 md:px-6 lg:px-8 py-6 md:py-8" aria-hidden="true">
        @include('nomina.partials.index_content', ['salarios' => $salarios ?? collect()])
    </div>

    <div class="fixed inset-0 bg-gray-900/85 z-40" aria-hidden="true"></div>
    <div class="fixed inset-0 z-40 pointer-events-none overflow-hidden" aria-hidden="true">
        @include('nomina.partials.modal_figures')
    </div>

    <div class="fixed inset-0 z-50 p-4 md:p-6 flex items-center justify-center overflow-y-auto">
        <div class="relative w-full max-w-6xl bg-white rounded-3xl shadow-2xl border border-gray-200 p-4 md:p-5 modal-enter">
            <a href="{{ route('nomina.step2.ingresos') }}" class="absolute top-4 right-4 h-10 w-10 rounded-full bg-white/90 border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-white flex items-center justify-center shadow-sm transition" aria-label="Cerrar"><span class="text-lg">&times;</span></a>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4">
                    <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-blue-500 text-white p-6 md:p-8 shadow-lg">
                        <div class="text-xs uppercase tracking-widest text-blue-100">Nómina</div>
                        <h2 class="text-2xl md:text-3xl font-bold mt-2">Paso 3: Deducciones</h2>
                        <p class="text-blue-100 mt-3 text-sm">Deducciones automáticas y manuales.</p>
                        <div class="mt-6">
                            <div class="flex items-center justify-between text-xs font-semibold text-blue-100 mb-2"><span>Progreso</span><span>Paso 3 de 3</span></div>
                            <div class="w-full bg-blue-400/40 rounded-full h-2"><div class="bg-white h-2 rounded-full" style="width:100%"></div></div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                        <h4 class="text-sm font-semibold text-blue-900 mb-3">Resumen final</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg bg-white border border-blue-100 p-3"><div class="text-xs text-gray-500">Total devengos</div><div id="resumen_devengos" class="font-semibold text-gray-800">$0</div></div>
                            <div class="rounded-lg bg-white border border-blue-100 p-3"><div class="text-xs text-gray-500">Total deducciones</div><div id="resumen_deducciones" class="font-semibold text-gray-800">$0</div></div>
                            <div class="rounded-lg bg-white border border-blue-100 p-3 md:col-span-2"><div class="text-xs text-gray-500">Salario neto a pagar</div><div id="resumen_neto" class="font-semibold text-blue-700">$0</div></div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white/90 backdrop-blur rounded-2xl shadow-md border border-gray-100 p-6 md:p-8">
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Deducciones del salario</h3>

                        <form method="POST" action="{{ route('nomina.store') }}" id="formDeducciones">
                            @csrf
                            <input type="hidden" id="salario_base" value="{{ $salarioBase ?? 0 }}">
                            <input type="hidden" id="total_devengos" value="{{ $totalDevengos ?? 0 }}">
                            <input type="hidden" id="confirm_edit" name="confirm_edit" value="">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">EPS (salud) <span id="eps_rate_label" class="text-blue-700">(0%)</span></label>
                                    <input id="eps" name="eps" type="text" inputmode="decimal" readonly class="w-full border-2 border-gray-200 px-3 py-2 rounded-lg text-xs bg-gray-50 text-gray-700" value="0">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">AFP (pensión) <span id="afp_rate_label" class="text-blue-700">(0%)</span></label>
                                    <input id="afp" name="afp" type="text" inputmode="decimal" readonly class="w-full border-2 border-gray-200 px-3 py-2 rounded-lg text-xs bg-gray-50 text-gray-700" value="0">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Total seguridad social <span id="seguridad_rate_label" class="text-blue-700">(0%)</span></label>
                                    <input id="seguridad_social" name="seguridad_social" type="text" inputmode="decimal" readonly class="w-full border-2 border-gray-200 px-3 py-2 rounded-lg text-xs bg-gray-50 text-gray-700" value="0">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Retención en la fuente</label>
                                    <input id="retencion_fuente" name="retencion_fuente" type="text" inputmode="decimal" value="{{ old('retencion_fuente', $step3['retencion_fuente'] ?? 0) }}" class="manual-input w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs focus:border-blue-500 focus:outline-none transition bg-white shadow-sm" placeholder="0">
                                    @error('retencion_fuente')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Embargo fiscal</label>
                                    <input id="embargo_fiscal" name="embargo_fiscal" type="text" inputmode="decimal" value="{{ old('embargo_fiscal', $step3['embargo_fiscal'] ?? 0) }}" class="manual-input w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs focus:border-blue-500 focus:outline-none transition bg-white shadow-sm" placeholder="0">
                                    @error('embargo_fiscal')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pensión voluntaria</label>
                                    <input id="pension_voluntaria" name="pension_voluntaria" type="text" inputmode="decimal" value="{{ old('pension_voluntaria', $step3['pension_voluntaria'] ?? 0) }}" class="manual-input w-full border-2 border-gray-300 px-3 py-2 rounded-lg text-xs focus:border-blue-500 focus:outline-none transition bg-white shadow-sm" placeholder="0">
                                    @error('pension_voluntaria')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <p id="deduccionesError" class="hidden text-sm text-red-600 font-medium mt-4"></p>

                            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-6 mt-6 border-t border-gray-200">
                                <a href="{{ route('nomina.step2.ingresos') }}" class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition text-sm text-center">← Volver</a>
                                <button type="submit" class="px-8 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition shadow-md hover:shadow-lg text-sm">Guardar Nómina</button>
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
    const form = document.getElementById('formDeducciones');
    const manualInputs = Array.from(document.querySelectorAll('.manual-input'));
    const salarioBase = Number(document.getElementById('salario_base').value || 0);
    const totalDevengos = Number(document.getElementById('total_devengos').value || 0);
    const errorBox = document.getElementById('deduccionesError');

    const EPS_RATE = 0.04;
    const AFP_RATE = 0.04;
    const percentLabel = (rate) => `${(rate * 100).toFixed(0)}%`;

    const epsRateLabel = document.getElementById('eps_rate_label');
    const afpRateLabel = document.getElementById('afp_rate_label');
    const seguridadRateLabel = document.getElementById('seguridad_rate_label');

    if (epsRateLabel) epsRateLabel.textContent = `(${percentLabel(EPS_RATE)})`;
    if (afpRateLabel) afpRateLabel.textContent = `(${percentLabel(AFP_RATE)})`;
    if (seguridadRateLabel) seguridadRateLabel.textContent = `(${percentLabel(EPS_RATE + AFP_RATE)})`;

    const money = (v) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v || 0);
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
        return Number.isFinite(num) && num >= 0 ? num : NaN;
    };
    const sanitize = (input) => {
        const parsed = toNumber(input.value);
        if (Number.isNaN(parsed)) { input.classList.add('border-red-500'); return false; }
        input.value = formatInputNumber(parsed);
        input.classList.remove('border-red-500');
        return true;
    };

    const calc = () => {
        const eps = totalDevengos * EPS_RATE;
        const afp = totalDevengos * AFP_RATE;
        const seguridadSocial = eps + afp;

        const retencion = toNumber(document.getElementById('retencion_fuente').value) || 0;
        const embargo = toNumber(document.getElementById('embargo_fiscal').value) || 0;
        const pensionVol = toNumber(document.getElementById('pension_voluntaria').value) || 0;

        // Caja de compensacion: solo informativa (aporte empleador), no se deduce al empleado.
        const totalDeducciones = seguridadSocial + retencion + embargo + pensionVol;
        const neto = totalDevengos - totalDeducciones;

        document.getElementById('eps').value = formatInputNumber(eps);
        document.getElementById('afp').value = formatInputNumber(afp);
        document.getElementById('seguridad_social').value = formatInputNumber(seguridadSocial);

        document.getElementById('resumen_devengos').textContent = money(totalDevengos);
        document.getElementById('resumen_deducciones').textContent = money(totalDeducciones);
        document.getElementById('resumen_neto').textContent = money(neto);
    };

    manualInputs.forEach((input) => {
        input.addEventListener('input', () => { if (input.value.includes('-')) input.value = input.value.replace('-', ''); calc(); });
        input.addEventListener('blur', () => { sanitize(input); errorBox.classList.add('hidden'); calc(); });
    });

    form.addEventListener('submit', async (e) => {
        let valid = true;
        manualInputs.forEach((input) => { if (!sanitize(input)) valid = false; });
        if (!valid) {
            e.preventDefault();
            errorBox.textContent = 'Solo se permiten valores numéricos no negativos.';
            errorBox.classList.remove('hidden');
            return;
        }

        const isEditing = @json((bool)($isEditing ?? false));
        if (isEditing) {
            e.preventDefault();

            if (typeof Swal === 'undefined') {
                const confirmation = window.prompt('Escribe editar para confirmar la modificación', '');
                if ((confirmation || '').trim().toLowerCase() !== 'editar') {
                    errorBox.textContent = 'No se aplicaron cambios. Debes escribir "editar" para confirmar.';
                    errorBox.classList.remove('hidden');
                    return;
                }

                document.getElementById('confirm_edit').value = 'editar';
                form.submit();
                return;
            }

            const result = await Swal.fire({
                title: 'Confirmar edición',
                text: 'Para guardar cambios escribe "editar".',
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'Escribe editar',
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                inputValidator: (value) => {
                    if ((value || '').trim().toLowerCase() !== 'editar') {
                        return 'Debes escribir exactamente "editar"';
                    }
                    return null;
                }
            });

            if (!result.isConfirmed) {
                e.preventDefault();
                return;
            }

            document.getElementById('confirm_edit').value = 'editar';
            form.submit();
            return;
        }

        calc();
    });

    manualInputs.forEach((input) => {
        const parsed = toNumber(input.value);
        if (!Number.isNaN(parsed)) {
            input.value = formatInputNumber(parsed);
        }
    });

    calc();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('nomina.partials.modal_assets')
@endsection
