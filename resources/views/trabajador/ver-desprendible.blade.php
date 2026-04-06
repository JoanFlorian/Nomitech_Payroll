@extends('layouts.app')

@section('title', 'Detalle del Desprendible')

@section('content')
@php
    $contrato      = $desprendible->contrato;
    $usuario       = $contrato->usuario;
    $periodo       = $desprendible->periodo;
    $diasTrabajados = (int) ($desprendible->dias_trabajados ?? $desprendible->dias_a_trabajar ?? 30);
    $diasPeriodo   = 30;
    $diasNoTrabajados = max(0, $diasPeriodo - $diasTrabajados);
    $salarioBase   = (float) ($desprendible->salario_base ?? $contrato->salario_base ?? 0);
    $ibc           = $salarioBase
                     + (float) ($desprendible->valor_horas_extras_recargos ?? $desprendible->horas_extra ?? 0)
                     + (float) ($desprendible->bonificaciones ?? 0)
                     + (float) ($desprendible->comisiones ?? 0)
                     + (float) ($desprendible->otros_devengos ?? 0);

    // Todas las novedades vinculadas a este salario (ya filtradas por id_salario en la relación)
    $novedadesPeriodo = $desprendible->novedades;

    $horasExtrasTotalValor = (float) ($desprendible->valor_horas_extras_recargos ?? $desprendible->horas_extra ?? 0);
@endphp

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-4 md:p-8">
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- ── Header ── --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Desprendible de Nómina</h1>
                <p class="mt-1 text-gray-500">Periodo: <span class="font-semibold text-gray-700">{{ $periodo->nombre ?? 'N/A' }}</span></p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('trabajador.desprendibles') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <span class="material-icons mr-2 text-base">arrow_back</span>
                    Volver
                </a>
                <a href="{{ route('trabajador.desprendible.pdf', $desprendible->id_salario) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors">
                    <span class="material-icons mr-2 text-base">download</span>
                    Descargar PDF
                </a>
            </div>
        </div>

        {{-- ── 1. Información del empleado ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2">
                <span class="material-icons text-blue-600">person</span>
                Información del Empleado
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre completo</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $usuario->nombre_completo ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Documento</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $usuario->numero_documento ?? $usuario->doc ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Periodo</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $periodo->nombre ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha de pago</p>
                    <p class="mt-1 font-semibold text-gray-800">
                        {{ \Carbon\Carbon::parse($desprendible->fecha_pago ?? $desprendible->created_at)->format('d/m/Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tipo de contrato</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $contrato->tipoContrato->nombre ?? 'N/A' }}</p>
                </div>
                @if($contrato->tipoTrabajador ?? null)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cargo / Tipo trabajador</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $contrato->tipoTrabajador->nombre ?? 'N/A' }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Salario base del periodo</p>
                    <p class="mt-1 font-semibold text-gray-800">${{ number_format($salarioBase, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Días trabajados</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $diasTrabajados }} de {{ $diasPeriodo }}</p>
                </div>
                @if($diasNoTrabajados > 0)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Días no trabajados</p>
                    <p class="mt-1 font-semibold text-orange-600">{{ $diasNoTrabajados }}</p>
                </div>
                @endif
                @if($contrato->metodoPago ?? null)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Método de pago</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $contrato->metodoPago->nombre ?? 'N/A' }}</p>
                </div>
                @endif
                @if($contrato->formaPago ?? null)
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Forma de pago</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $contrato->formaPago->nombre ?? 'N/A' }}</p>
                </div>
                @endif
                @if(!empty($contrato->tipo_cuenta) && !empty($contrato->numero_cuenta))
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cuenta / Banco</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ ucfirst(strtolower($contrato->tipo_cuenta)) }} — {{ $contrato->numero_cuenta }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ── 2. Novedades del periodo ── --}}
        @if($novedadesPeriodo->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2">
                <span class="material-icons text-amber-500">event_note</span>
                Novedades del Periodo
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-amber-200 text-xs font-semibold uppercase tracking-wide text-amber-700">
                            <th class="px-4 py-3 text-left">Tipo de novedad</th>
                            <th class="px-4 py-3 text-center">Fecha inicio</th>
                            <th class="px-4 py-3 text-center">Fecha fin</th>
                            <th class="px-4 py-3 text-center">Días</th>
                            <th class="px-4 py-3 text-left">Observación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($novedadesPeriodo as $nov)
                        <tr class="hover:bg-amber-50">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $nov->tipo_novedad_nombre ?? $nov->tipoNovedad?->nombre ?? 'Novedad' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                {{ optional($nov->fecha_inicio ?? $nov->fecha)->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                {{ optional($nov->fecha_fin)->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-800">
                                {{ $nov->dias > 0 ? $nov->dias : '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">
                                {{ $nov->observaciones ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ── 3. Devengos ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2">
                <span class="material-icons text-green-600">trending_up</span>
                Devengos
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-600">
                            <th class="px-4 py-3 text-left">Concepto</th>
                            <th class="px-4 py-3 text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            // Salario base
                            $salarioBaseProporcional = (float) (( ($desprendible->salario_base ?? $contrato->salario_base ?? 0) / 30) * $diasTrabajados);
                        @endphp

                        {{-- Salario base proporcional --}}
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">
                                Salario base
                                @if($diasTrabajados < $diasPeriodo)
                                    <span class="ml-2 text-xs text-gray-400">({{ $diasTrabajados }} días)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-green-600 font-semibold">
                                ${{ number_format($salarioBaseProporcional, 0, ',', '.') }}
                            </td>
                        </tr>

                        {{-- Auxilio de transporte --}}
                        @if((float)($desprendible->auxilio_transporte ?? 0) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">Auxilio de transporte</td>
                            <td class="px-4 py-3 text-right text-green-600 font-semibold">
                                ${{ number_format((float)$desprendible->auxilio_transporte, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        {{-- Horas extras y recargos --}}
                        @if($horasExtrasTotalValor > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">
                                Horas extras y recargos
                                @if($horasExtras->isNotEmpty())
                                <div class="mt-2 ml-2 space-y-1">
                                    @foreach($horasExtras as $hx)
                                    @php
                                        $nombreHX = $hx->tipoHoraRecargo->nombre ?? 'Hora';
                                        $cantidadHX = (float) $hx->cantidad;
                                        $pagoHX = (float) $hx->pago;
                                        $valorUnitarioHX = $cantidadHX > 0 ? round($pagoHX / $cantidadHX, 2) : 0;
                                    @endphp
                                    <div class="grid grid-cols-4 gap-2 text-xs text-gray-500 border-l-2 border-green-200 pl-2">
                                        <span class="col-span-2 font-medium text-gray-600">{{ $nombreHX }}</span>
                                        <span class="text-center">{{ number_format($cantidadHX, 1, ',', '.') }} h</span>
                                        <span class="text-right font-semibold text-green-700">${{ number_format($pagoHX, 0, ',', '.') }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-green-600 font-semibold align-top">
                                ${{ number_format($horasExtrasTotalValor, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        {{-- Bonificaciones --}}
                        @if((float)($desprendible->bonificaciones ?? 0) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">Bonificaciones</td>
                            <td class="px-4 py-3 text-right text-green-600 font-semibold">
                                ${{ number_format((float)$desprendible->bonificaciones, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        {{-- Comisiones --}}
                        @if((float)($desprendible->comisiones ?? 0) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">Comisiones</td>
                            <td class="px-4 py-3 text-right text-green-600 font-semibold">
                                ${{ number_format((float)$desprendible->comisiones, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        {{-- Otros devengos --}}
                        @if((float)($desprendible->otros_devengos ?? 0) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">Otros devengos</td>
                            <td class="px-4 py-3 text-right text-green-600 font-semibold">
                                ${{ number_format((float)$desprendible->otros_devengos, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        {{-- Novedades devengadas --}}
                        @foreach($desprendible->novedades as $nov)
                            @if((float)($nov->pago ?? 0) > 0)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $nov->tipo_novedad_nombre ?? $nov->tipoNovedad?->nombre ?? 'Novedad' }}
                                </td>
                                <td class="px-4 py-3 text-right text-green-600 font-semibold">
                                    ${{ number_format((float)$nov->pago, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                        @endforeach

                        <tr class="bg-green-50">
                            <td class="px-4 py-4 font-bold text-gray-800">TOTAL DEVENGOS</td>
                            <td class="px-4 py-4 text-right font-bold text-green-700 text-base">
                                ${{ number_format((float)$desprendible->total_devengos, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── 4. Deducciones ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2">
                <span class="material-icons text-orange-600">trending_down</span>
                Deducciones
            </h2>

            <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                <span class="font-semibold">Base de cotización (IBC):</span>
                ${{ number_format($ibc, 0, ',', '.') }}
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-600">
                            <th class="px-4 py-3 text-left">Concepto</th>
                            <th class="px-4 py-3 text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">

                        @if((float)($desprendible->eps ?? 0) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">Salud (EPS — 4%)</td>
                            <td class="px-4 py-3 text-right text-orange-600 font-semibold">
                                ${{ number_format((float)$desprendible->eps, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        @if((float)($desprendible->afp ?? 0) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">Pensión AFP (4%)</td>
                            <td class="px-4 py-3 text-right text-orange-600 font-semibold">
                                ${{ number_format((float)$desprendible->afp, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        @if((float)($desprendible->aporte_fp ?? 0) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">Fondo de solidaridad pensional</td>
                            <td class="px-4 py-3 text-right text-orange-600 font-semibold">
                                ${{ number_format((float)$desprendible->aporte_fp, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        @if((float)($desprendible->retencion_fuente ?? 0) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">Retención en la fuente</td>
                            <td class="px-4 py-3 text-right text-orange-600 font-semibold">
                                ${{ number_format((float)$desprendible->retencion_fuente, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        @if((float)($desprendible->embargo_fiscal ?? 0) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">Embargo fiscal</td>
                            <td class="px-4 py-3 text-right text-orange-600 font-semibold">
                                ${{ number_format((float)$desprendible->embargo_fiscal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        @if((float)($desprendible->pension_voluntaria ?? 0) > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">Pensión voluntaria</td>
                            <td class="px-4 py-3 text-right text-orange-600 font-semibold">
                                ${{ number_format((float)$desprendible->pension_voluntaria, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif

                        {{-- Novedades como deducciones --}}
                        @foreach($desprendible->novedades as $nov)
                            @if((float)($nov->pago ?? 0) < 0)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $nov->tipo_novedad_nombre ?? $nov->tipoNovedad?->nombre ?? 'Novedad' }}
                                </td>
                                <td class="px-4 py-3 text-right text-orange-600 font-semibold">
                                    ${{ number_format(abs((float)$nov->pago), 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                        @endforeach

                        <tr class="bg-orange-50">
                            <td class="px-4 py-4 font-bold text-gray-800">TOTAL DEDUCCIONES</td>
                            <td class="px-4 py-4 text-right font-bold text-orange-700 text-base">
                                ${{ number_format((float)$desprendible->total_deducciones, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── 5. Seguridad social (solo visual) ── --}}
        @if((float)($desprendible->eps ?? 0) > 0 || (float)($desprendible->afp ?? 0) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2">
                <span class="material-icons text-indigo-600">health_and_safety</span>
                Aportes Seguridad Social (empleado)
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">Base IBC</p>
                    <p class="mt-2 text-lg font-bold text-indigo-800">${{ number_format($ibc, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-500">Salud (4%)</p>
                    <p class="mt-2 text-lg font-bold text-blue-800">${{ number_format((float)($desprendible->eps ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-violet-100 bg-violet-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-500">Pensión (4%)</p>
                    <p class="mt-2 text-lg font-bold text-violet-800">${{ number_format((float)($desprendible->afp ?? 0), 0, ',', '.') }}</p>
                </div>
            </div>
            <p class="mt-3 text-xs italic text-gray-400">* Solo valores empleado. No incluye aportes del empleador.</p>
        </div>
        @endif

        {{-- ── 6. Resumen final ── --}}
        <div class="rounded-2xl bg-gradient-to-br from-[#1565C0] to-[#0D47A1] shadow-lg p-6 text-white">
            <h2 class="text-base font-bold uppercase tracking-widest text-blue-200 mb-5">Resumen</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-blue-200 text-xs font-semibold uppercase tracking-wide mb-2">Total devengado</p>
                    <p class="text-3xl font-bold">${{ number_format((float)$desprendible->total_devengos, 0, ',', '.') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-blue-200 text-xs font-semibold uppercase tracking-wide mb-2">Total deducciones</p>
                    <p class="text-3xl font-bold">${{ number_format((float)$desprendible->total_deducciones, 0, ',', '.') }}</p>
                </div>
                <div class="text-center bg-white/20 rounded-xl p-4">
                    <p class="text-blue-100 text-xs font-semibold uppercase tracking-wide mb-2">Neto a pagar</p>
                    <p class="text-4xl font-bold">${{ number_format((float)$desprendible->salario_neto, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
