@extends('layouts.app')

@section('title', 'Enviar Nota de Ajuste')
@section('page-title', 'Enviar Nota de Ajuste')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-gray-100 bg-gradient-to-r from-blue-50 to-cyan-50 p-6 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900">Desprendible seleccionado</h2>
        <p class="mt-2 text-sm text-gray-600">Tu nota quedará asociada a este desprendible específico para que el administrador pueda revisarlo en detalle.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Periodo</p>
            <p class="mt-2 text-sm font-semibold text-gray-900">{{ $desprendible->periodo->nombre ?? 'N/A' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha de pago</p>
            <p class="mt-2 text-sm font-semibold text-gray-900">{{ optional($desprendible->created_at)->format('d/m/Y') }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Devengado</p>
            <p class="mt-2 text-sm font-semibold text-green-700">${{ number_format((float) ($desprendible->total_devengos ?? 0), 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Deducciones</p>
            <p class="mt-2 text-sm font-semibold text-orange-700">${{ number_format((float) ($desprendible->total_deducciones ?? 0), 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Neto</p>
            <p class="mt-2 text-sm font-semibold text-blue-700">${{ number_format((float) ($desprendible->salario_neto ?? 0), 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <form id="nota-ajuste-form" action="{{ route('trabajador.notas.store') }}" method="POST" class="space-y-4" novalidate>
            @csrf
            <input type="hidden" name="id_salario" value="{{ $desprendible->id_salario }}">

            <div>
                <label for="mensaje" class="mb-2 block text-sm font-semibold text-gray-700">Mensaje de la nota</label>
                <textarea
                    id="mensaje"
                    name="mensaje"
                    rows="8"
                    minlength="10"
                    maxlength="2000"
                    required
                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm text-gray-800 shadow-sm transition focus:border-[#1565C0] focus:outline-none focus:ring-4 focus:ring-blue-100 @error('mensaje') border-red-500 @enderror"
                    placeholder="Describe claramente el ajuste solicitado para este desprendible..."
                >{{ old('mensaje') }}</textarea>
                <div class="mt-2 flex items-center justify-between gap-4">
                    <p id="mensaje-error" class="text-sm text-red-600">@error('mensaje') {{ $message }} @enderror</p>
                    <p class="text-xs text-gray-500"><span id="mensaje-count">{{ mb_strlen(old('mensaje', '')) }}</span>/2000</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('trabajador.desprendibles') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center rounded-xl bg-[#1565C0] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0D47A1]">
                    <span class="material-icons mr-2 text-base">send</span>
                    Enviar nota
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('nota-ajuste-form');
    const textarea = document.getElementById('mensaje');
    const errorEl = document.getElementById('mensaje-error');
    const countEl = document.getElementById('mensaje-count');

    function validateMessage() {
        const rawValue = textarea.value || '';
        const trimmedValue = rawValue.trim();
        countEl.textContent = rawValue.length;

        if (trimmedValue.length === 0) {
            errorEl.textContent = 'Debes escribir una nota de ajuste.';
            return false;
        }

        if (trimmedValue.length < 10) {
            errorEl.textContent = 'La nota de ajuste debe tener al menos 10 caracteres.';
            return false;
        }

        errorEl.textContent = '';
        return true;
    }

    textarea.addEventListener('input', validateMessage);
    textarea.addEventListener('blur', validateMessage);

    form.addEventListener('submit', function (event) {
        if (!validateMessage()) {
            event.preventDefault();
            textarea.focus();
        }
    });

    validateMessage();
});
</script>
@endpush
