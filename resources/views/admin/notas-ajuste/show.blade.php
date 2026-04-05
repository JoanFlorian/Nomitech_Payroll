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

    {{-- ── Correcciones guardadas (nota_ajuste_detalles) ── --}}
    @php
        $etiquetasCampos = [
            'auxilio_transporte'        => 'Auxilio de transporte',
            'valor_horas_extras_recargos' => 'Horas extras y recargos',
            'bonificaciones'            => 'Bonificaciones',
            'comisiones'                => 'Comisiones',
            'otros_devengos'            => 'Otros devengos',
            'eps'                       => 'Salud (EPS)',
            'afp'                       => 'Pensión (AFP)',
            'aporte_fp'                 => 'Fondo de pensiones voluntario',
            'retencion_fuente'          => 'Retención en la fuente',
            'embargo_fiscal'            => 'Embargo fiscal',
            'pension_voluntaria'        => 'Pensión voluntaria',
        ];
        $detallesGuardados = $nota->detalles ?? collect();
    @endphp

    @if($detallesGuardados->isNotEmpty())
    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="flex items-center gap-2 text-lg font-bold text-indigo-900">
                <span class="material-icons text-base">playlist_add_check</span>
                Correcciones guardadas
            </h3>
            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                {{ $detallesGuardados->count() }} concepto(s)
            </span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-indigo-200 bg-white">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-indigo-100 bg-indigo-50 text-xs font-semibold uppercase tracking-wide text-indigo-700">
                        <th class="px-4 py-3 text-left">Concepto</th>
                        <th class="px-4 py-3 text-right">Valor original</th>
                        <th class="px-4 py-3 text-right">Valor corregido</th>
                        <th class="px-4 py-3 text-right">Diferencia</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($detallesGuardados as $det)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $etiquetasCampos[$det->campo] ?? $det->campo }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            ${{ number_format($det->valor_original, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">
                            ${{ number_format($det->valor_corregido, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $det->diferencia > 0 ? 'text-green-700' : ($det->diferencia < 0 ? 'text-red-700' : 'text-gray-500') }}">
                            {{ $det->diferencia > 0 ? '+' : '' }}${{ number_format($det->diferencia, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($det->aprobado_por)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">
                                    <span class="material-icons text-xs">check_circle</span>
                                    Aplicado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">
                                    <span class="material-icons text-xs">pending</span>
                                    Pendiente
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-xs italic text-indigo-600">
            * Las correcciones guardadas se usarán automáticamente al aplicar el ajuste.
            Puedes sobrescribirlas guardando nuevos valores desde el formulario.
        </p>
    </div>
    @endif

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
            <form id="js-ajuste-form" action="{{ route('admin.notas-ajuste.apply-payment-adjustment', $nota) }}" method="POST" class="mt-5 space-y-5">
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

                {{-- ── Previsualización de impacto económico ── --}}
                <div id="js-impacto-preview" class="hidden rounded-2xl border border-blue-200 bg-blue-50 p-5">
                    <h4 class="mb-3 flex items-center gap-2 text-sm font-bold text-blue-900">
                        <span class="material-icons text-base">preview</span>
                        Impacto de la Nota (Previsualización)
                    </h4>

                    <div id="js-impacto-loading" class="hidden py-2 text-center text-sm text-blue-700">
                        Calculando impacto…
                    </div>

                    <div id="js-impacto-sin-impacto" class="hidden rounded-xl bg-blue-100 px-4 py-3 text-sm text-blue-700">
                        Sin impacto económico
                    </div>

                    <div id="js-impacto-detalle" class="hidden space-y-3">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-blue-200 bg-white p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">💰 Total ajuste</p>
                                <p id="js-impacto-total" class="mt-1 text-base font-bold text-gray-900">$0</p>
                                <p id="js-impacto-tipo" class="mt-1 text-xs text-gray-500"></p>
                            </div>
                            <div class="rounded-xl border border-blue-200 bg-white p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">📊 Base Seg. Social estimada</p>
                                <p id="js-impacto-base-ss" class="mt-1 text-base font-bold text-gray-900">$0</p>
                            </div>
                            <div class="rounded-xl border border-blue-200 bg-white p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Salud empleado (4%)</p>
                                <p id="js-impacto-salud" class="mt-1 text-base font-bold text-blue-700">$0</p>
                            </div>
                            <div class="rounded-xl border border-blue-200 bg-white p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pensión empleado (4%)</p>
                                <p id="js-impacto-pension" class="mt-1 text-base font-bold text-blue-700">$0</p>
                            </div>
                        </div>
                        <p class="text-xs italic text-blue-600">* Estimación informativa. No modifica la nómina real.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
                    <button
                        type="button"
                        data-draft-submit
                        data-draft-action="{{ route('admin.notas-ajuste.guardar-detalles', $nota) }}"
                        class="inline-flex items-center rounded-xl border border-indigo-400 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-800 transition hover:bg-indigo-100"
                        title="Guarda las correcciones sin aplicarlas al desprendible todavía"
                    >
                        <span class="material-icons mr-2 text-base">save</span>
                        Guardar correcciones (borrador)
                    </button>

                    <button type="submit" class="inline-flex items-center rounded-xl bg-[#1565C0] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0D47A1]">
                        <span class="material-icons mr-2 text-base">check_circle</span>
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

<script>
    (function () {
        'use strict';

        const previewUrl    = @json(route('admin.notas-ajuste.preview-impacto', $nota));
        const csrfToken     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const form          = document.getElementById('js-ajuste-form');
        const previewSection = document.getElementById('js-impacto-preview');
        const loadingEl     = document.getElementById('js-impacto-loading');
        const sinImpactoEl  = document.getElementById('js-impacto-sin-impacto');
        const detalleEl     = document.getElementById('js-impacto-detalle');

        if (!form || !previewSection) return;

        const fmtCOP = new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

        function formatAbs(value) {
            return '$' + fmtCOP.format(Math.abs(value));
        }

        function formatSigned(value) {
            return (value >= 0 ? '+$' : '-$') + fmtCOP.format(Math.abs(value));
        }

        function showLoading() {
            previewSection.classList.remove('hidden');
            loadingEl.classList.remove('hidden');
            sinImpactoEl.classList.add('hidden');
            detalleEl.classList.add('hidden');
        }

        function showSinImpacto(msg) {
            loadingEl.classList.add('hidden');
            sinImpactoEl.textContent = msg ?? 'Sin impacto económico';
            sinImpactoEl.classList.remove('hidden');
            detalleEl.classList.add('hidden');
        }

        function showDetalle(data) {
            loadingEl.classList.add('hidden');
            sinImpactoEl.classList.add('hidden');
            detalleEl.classList.remove('hidden');

            const totalEl = document.getElementById('js-impacto-total');
            const tipoEl  = document.getElementById('js-impacto-tipo');

            totalEl.textContent = formatSigned(data.total_diferencia);
            totalEl.className   = data.tipo === 'pago'
                ? 'mt-1 text-base font-bold text-green-700'
                : 'mt-1 text-base font-bold text-red-700';

            tipoEl.textContent = data.tipo === 'pago'
                ? 'Se pagará al empleado'
                : 'Se descontará al empleado';
            tipoEl.className = data.tipo === 'pago'
                ? 'mt-1 text-xs text-green-600'
                : 'mt-1 text-xs text-red-600';

            document.getElementById('js-impacto-base-ss').textContent  = formatAbs(data.base_seguridad_social);
            document.getElementById('js-impacto-salud').textContent    = formatAbs(data.salud);
            document.getElementById('js-impacto-pension').textContent  = formatAbs(data.pension);
        }

        let debounceTimer = null;

        function schedulePreview() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchPreview, 500);
        }

        async function fetchPreview() {
            const hasChecked = form.querySelector('input[name="campos_a_ajustar[]"]:checked') !== null;

            if (!hasChecked) {
                previewSection.classList.add('hidden');
                return;
            }

            showLoading();

            try {
                const formData = new FormData(form);
                // Remove the PATCH method override so we POST to the preview endpoint
                formData.delete('_method');

                const response = await fetch(previewUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (response.status === 401 || response.status === 419) {
                    showSinImpacto('Sesión expirada. Recarga la página.');
                    return;
                }

                if (!response.ok) {
                    showSinImpacto('No se pudo calcular el impacto.');
                    return;
                }

                const data = await response.json();

                if (data.sin_impacto) {
                    showSinImpacto(data.mensaje ?? 'Sin impacto económico');
                } else {
                    showDetalle(data);
                }
            } catch (_) {
                showSinImpacto('Error al calcular el impacto.');
            }
        }

        // Trigger preview on any form input change
        form.addEventListener('change', schedulePreview);
        form.addEventListener('input', schedulePreview);

        // Trigger immediately if checkboxes were pre-checked (e.g. after validation failure)
        if (form.querySelector('input[name="campos_a_ajustar[]"]:checked')) {
            fetchPreview();
        }

        @if($detallesGuardados->isNotEmpty())
        // Detalles pre-guardados → mostrar preview automáticamente al cargar
        fetchPreview();
        @endif
    })();
</script>

<script>
    // Manejo del botón "Guardar correcciones (borrador)":
    // El formulario usa method=POST + _method=PATCH para la ruta de PATCH.
    // El botón de borrador apunta a una ruta POST pura → eliminamos _method temporalmente.
    (function () {
        const draftBtn = document.querySelector('[data-draft-submit]');
        if (!draftBtn) return;
        const ajusteForm = document.getElementById('js-ajuste-form');
        if (!ajusteForm) return;

        draftBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const methodInput = ajusteForm.querySelector('input[name="_method"]');
            const originalValue = methodInput ? methodInput.value : null;
            if (methodInput) methodInput.value = '';

            const url = draftBtn.dataset.draftAction;
            const savedAction = ajusteForm.action;
            ajusteForm.action = url;
            ajusteForm.method = 'POST';

            ajusteForm.submit();

            // Restore in case browser keeps page
            ajusteForm.action = savedAction;
            if (methodInput && originalValue !== null) {
                methodInput.value = originalValue;
            }
        });
    })();
</script>
@endpush
@endsection