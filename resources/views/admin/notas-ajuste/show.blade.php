@extends('layouts.app')

@section('title', 'Detalle Nota de Ajuste')
@section('page-title', 'Detalle Nota de Ajuste')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-800 shadow-sm">
            {{ session('warning') }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $nota->usuario?->nombre_completo ?? 'Trabajador no disponible' }}</h2>
            <p class="mt-1 text-sm text-gray-500">Nota enviada {{ optional($nota->created_at)->diffForHumans() }}</p>
        </div>
        <a href="{{ route('admin.notas-ajuste.index') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <span class="material-icons mr-2 text-base">arrow_back</span>
            Volver al listado
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre completo</p>
            <p class="mt-2 text-lg font-semibold text-gray-900">{{ $nota->usuario?->nombre_completo ?? 'N/A' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Documento</p>
            <p class="mt-2 text-lg font-semibold text-gray-900">{{ $nota->usuario?->doc ?? 'N/A' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha de envío</p>
            <p class="mt-2 text-lg font-semibold text-gray-900">{{ optional($nota->created_at)->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-900">Contenido completo de la nota</h3>
            @php
                $estadoClass = match($nota->estado) {
                    \App\Models\NotaAjuste::ESTADO_RESUELTO => 'bg-green-100 text-green-700',
                    \App\Models\NotaAjuste::ESTADO_OMITIDO => 'bg-slate-200 text-slate-700',
                    default => 'bg-amber-100 text-amber-700',
                };
            @endphp
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estadoClass }}">{{ ucfirst($nota->estado) }}</span>
        </div>

        <div class="rounded-2xl bg-gray-50 p-5 text-sm leading-7 text-gray-700 whitespace-pre-line">{{ $nota->mensaje }}</div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900">Desprendible relacionado</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Periodo</p>
                <p class="mt-2 text-sm font-semibold text-gray-900">{{ $nota->salario?->periodo?->nombre ?? 'N/A' }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha pago</p>
                <p class="mt-2 text-sm font-semibold text-gray-900">{{ optional($nota->salario?->created_at)->format('d/m/Y') ?? 'N/A' }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Devengado</p>
                <p class="mt-2 text-sm font-semibold text-green-700">${{ number_format((float) ($nota->salario?->total_devengos ?? 0), 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Deducciones</p>
                <p class="mt-2 text-sm font-semibold text-orange-700">${{ number_format((float) ($nota->salario?->total_deducciones ?? 0), 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Neto</p>
                <p class="mt-2 text-sm font-semibold text-blue-700">${{ number_format((float) ($nota->salario?->salario_neto ?? 0), 0, ',', '.') }}</p>
            </div>
        </div>

        @if($nota->salario)
            <div class="mt-4 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ ($nota->salario?->periodo?->estado ?? '') === 'cerrado' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                Periodo: {{ strtoupper((string) ($nota->salario?->periodo?->estado ?? 'desconocido')) }}
            </div>
        @endif
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900">Ajuste controlado del pago</h3>
        <p class="mt-2 text-sm text-gray-600">
            Corrige solo los conceptos aplicados en el desprendible asociado a esta nota.
            Esta acción no reabre periodos ni modifica la configuración global de nómina.
        </p>

        @php
            $camposAjustables = [
                'auxilio_transporte' => 'Auxilio de transporte',
                'bonificaciones' => 'Bonificaciones',
                'comisiones' => 'Comisiones',
                'otros_devengos' => 'Otros devengos',
            ];
            $oldCamposSeleccionados = old('campos_a_ajustar', []);

            $salarioBaseParaHora = (float) ($nota->salario->salario_base ?? ($nota->salario->contrato->salario_base ?? 0));
            $horasRecargosTotalActual = (float) ($nota->salario->valor_horas_extras_recargos ?? ($nota->salario->horas_extra ?? 0));

            $detalleHorasRecargos = \App\Models\HoraRecargoExtra::query()
                ->with('tipoHoraRecargo')
                ->where('id_salario', $nota->salario->id_salario)
                ->get()
                ->filter(fn($item) => $item->tipoHoraRecargo !== null)
                ->values();

            $tiposRecargo = $detalleHorasRecargos->isNotEmpty()
                ? $detalleHorasRecargos->map(function ($item) {
                    return [
                        'id' => (int) $item->id_tipo_hora_recargo,
                        'nombre' => (string) $item->tipoHoraRecargo->nombre,
                        'rate' => (float) $item->tipoHoraRecargo->valor,
                        'cantidad' => (float) $item->cantidad,
                    ];
                })->values()
                : \App\Models\TipoHoraRecargo::query()
                    ->orderBy('nombre')
                    ->get()
                    ->map(function ($tipo) {
                        return [
                            'id' => (int) $tipo->id_tipo_hora_recargo,
                            'nombre' => (string) $tipo->nombre,
                            'rate' => (float) $tipo->valor,
                            'cantidad' => 0.0,
                        ];
                    })->values();
        @endphp

        @if(!$nota->salario)
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Esta nota no tiene un desprendible válido asociado para aplicar ajustes.
            </div>
        @else
            <form action="{{ route('admin.notas-ajuste.apply-payment-adjustment', $nota) }}" method="POST" class="mt-5 space-y-5">
                @csrf
                @method('PATCH')

                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                    Cada concepto tiene su propio campo editable independiente. Los valores mostrados son los del desprendible asociado.
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @php
                        $checkedHorasRecargos = in_array('valor_horas_extras_recargos', $oldCamposSeleccionados, true);
                    @endphp

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:col-span-2 xl:col-span-3">
                        <label class="inline-flex items-start gap-2 text-sm font-medium text-gray-800">
                            <input type="checkbox" name="campos_a_ajustar[]" value="valor_horas_extras_recargos" class="mt-1 rounded border-gray-300 text-[#1565C0] focus:ring-[#1565C0]" {{ $checkedHorasRecargos ? 'checked' : '' }}>
                            <span>Horas extras y recargos</span>
                        </label>

                        <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($tiposRecargo as $tipo)
                                @php
                                    $tipoId = (int) $tipo['id'];
                                    $cantidadActual = (float) $tipo['cantidad'];
                                    $cantidadOld = old('detalle_recargos_visual.' . $tipoId, $cantidadActual);
                                @endphp
                                <div class="rounded-lg border border-gray-200 bg-white px-3 py-3">
                                    <p class="text-sm font-semibold text-gray-800">{{ $tipo['nombre'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500">Cantidad actual: <span class="font-semibold text-gray-700">{{ number_format($cantidadActual, 0, ',', '.') }}</span></p>
                                    <input
                                        type="number"
                                        min="0"
                                        step="1"
                                        max="744"
                                        name="detalle_recargos_visual[{{ $tipoId }}]"
                                        value="{{ $cantidadOld }}"
                                        data-rate="{{ $tipo['rate'] }}"
                                        data-nombre="{{ mb_strtolower($tipo['nombre'], 'UTF-8') }}"
                                        class="js-detalle-recargo mt-2 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 shadow-sm transition focus:border-[#1565C0] focus:outline-none focus:ring-4 focus:ring-blue-100"
                                        placeholder="Ingresa cantidad"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Valor calculado: <span class="js-valor-recargo font-semibold text-gray-700">$0</span></p>
                                </div>
                            @endforeach
                        </div>

                        <p class="mt-3 text-xs text-gray-500">Total actual aplicado: <span class="font-semibold text-gray-700">${{ number_format($horasRecargosTotalActual, 2, ',', '.') }}</span></p>
                        <p class="js-horas-recargos-preview mt-1 text-xs font-semibold text-[#1565C0]">Total a guardar (horas + recargos): $0</p>
                        <input type="hidden" class="js-salario-base-hora" value="{{ $salarioBaseParaHora }}">
                        <input type="hidden" name="ajuste[valor_horas_extras_recargos]" class="js-horas-recargos-total" value="{{ old('ajuste.valor_horas_extras_recargos', number_format($horasRecargosTotalActual, 2, ',', '.')) }}">
                    </div>

                    @foreach($camposAjustables as $campo => $label)
                        @php
                            $isChecked = in_array($campo, $oldCamposSeleccionados, true);
                            $currentValue = (float) ($nota->salario->{$campo} ?? 0);
                        @endphp
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <label class="inline-flex items-start gap-2 text-sm font-medium text-gray-800">
                                <input type="checkbox" name="campos_a_ajustar[]" value="{{ $campo }}" class="mt-1 rounded border-gray-300 text-[#1565C0] focus:ring-[#1565C0]" {{ $isChecked ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>

                            <p class="mt-2 text-xs text-gray-500">Valor aplicado: <span class="font-semibold text-gray-700">${{ number_format($currentValue, 2, ',', '.') }}</span></p>

                            <input
                                type="text"
                                inputmode="decimal"
                                name="ajuste[{{ $campo }}]"
                                value="{{ old('ajuste.' . $campo, number_format($currentValue, 2, ',', '.')) }}"
                                class="mt-2 w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 shadow-sm transition focus:border-[#1565C0] focus:outline-none focus:ring-4 focus:ring-blue-100"
                                placeholder="Ej: 1.250.000,50"
                            >
                        </div>
                    @endforeach
                </div>

                @if($errors->ajustePago->has('campos_a_ajustar') || $errors->ajustePago->has('campos_a_ajustar.*') || $errors->ajustePago->has('ajuste.*'))
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        @if($errors->ajustePago->has('campos_a_ajustar'))
                            <p>{{ $errors->ajustePago->first('campos_a_ajustar') }}</p>
                        @endif
                        @if($errors->ajustePago->has('campos_a_ajustar.*'))
                            <p>{{ $errors->ajustePago->first('campos_a_ajustar.*') }}</p>
                        @endif
                        @if($errors->ajustePago->has('ajuste.*'))
                            <p>{{ $errors->ajustePago->first('ajuste.*') }}</p>
                        @endif
                    </div>
                @endif

                <div>
                    <label for="motivo_ajuste" class="text-sm font-semibold text-gray-700">Motivo del ajuste aplicado</label>
                    <textarea
                        id="motivo_ajuste"
                        name="motivo_ajuste"
                        rows="3"
                        required
                        minlength="8"
                        maxlength="500"
                        class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm text-gray-800 shadow-sm transition focus:border-[#1565C0] focus:outline-none focus:ring-4 focus:ring-blue-100"
                        placeholder="Describe qué se corrigió y por qué..."
                    >{{ old('motivo_ajuste') }}</textarea>
                    @if($errors->ajustePago->has('motivo_ajuste'))
                        <p class="mt-2 text-sm text-red-600">{{ $errors->ajustePago->first('motivo_ajuste') }}</p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-4">
                    <button type="submit" class="inline-flex items-center rounded-xl bg-[#1565C0] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0D47A1]">
                        Aplicar ajuste al desprendible
                    </button>
                </div>
            </form>
        @endif
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900">Respuesta del administrador</h3>

        <form action="{{ route('admin.notas-ajuste.update', $nota) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <textarea
                    id="respuesta_admin"
                    name="respuesta_admin"
                    rows="6"
                    required
                    minlength="5"
                    maxlength="2000"
                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm text-gray-800 shadow-sm transition focus:border-[#1565C0] focus:outline-none focus:ring-4 focus:ring-blue-100 @error('respuesta_admin') border-red-500 @enderror"
                    placeholder="Escribe la respuesta para el trabajador..."
                >{{ old('respuesta_admin', $nota->respuesta_admin) }}</textarea>
                @error('respuesta_admin')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="submit" name="accion" value="resuelto" class="inline-flex items-center rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
                    Responder y marcar como RESUELTO
                </button>
                <button type="submit" name="accion" value="omitido" class="inline-flex items-center rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                    Responder y marcar como OMITIDO
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        function parseLocalizedNumber(raw) {
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
        }

        function normalizeRateFactor(rawRate) {
            const rate = parseLocalizedNumber(rawRate);

            if (!Number.isFinite(rate) || rate <= 0) return 0;

            if (rate > 10) {
                return rate / 100;
            }

            return rate;
        }

        function formatMoney(value) {
            return new Intl.NumberFormat('es-CO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(value);
        }

        const detalleInputs = Array.from(document.querySelectorAll('.js-detalle-recargo'));
        const salarioBaseNode = document.querySelector('.js-salario-base-hora');
        const totalHidden = document.querySelector('.js-horas-recargos-total');
        const preview = document.querySelector('.js-horas-recargos-preview');

        const salarioBaseMensual = parseLocalizedNumber(salarioBaseNode ? salarioBaseNode.value : 0);
        const valorHora = salarioBaseMensual > 0 ? (salarioBaseMensual / 240) : 0;

        if (!detalleInputs.length || !totalHidden || !preview) {
            return;
        }

        function sanitizeCantidad(value) {
            const parsed = parseInt(String(value || 0), 10);
            if (!Number.isFinite(parsed) || parsed < 0) return 0;
            return Math.min(parsed, 744);
        }

        function syncTotal() {
            let total = 0;

            detalleInputs.forEach(function (input) {
                const horas = sanitizeCantidad(input.value);
                input.value = String(horas);

                const rateFactor = normalizeRateFactor(input.dataset.rate || 0);
                const nombre = String(input.dataset.nombre || '');

                const factorAplicado = nombre.includes('extra') && rateFactor < 1
                    ? (1 + rateFactor)
                    : rateFactor;

                const valor = Number(horas) * (Number(valorHora) * Number(factorAplicado));
                total += valor;

                const span = input.parentElement.querySelector('.js-valor-recargo');
                if (span) {
                    span.textContent = '$' + formatMoney(valor);
                }
            });

            totalHidden.value = total.toFixed(2);
            preview.textContent = 'Total a guardar (horas + recargos): $' + formatMoney(total);
        }

        detalleInputs.forEach(function (input) {
            input.addEventListener('input', syncTotal);
            input.addEventListener('blur', syncTotal);
        });

        syncTotal();
    })();
</script>
@endpush
@endsection