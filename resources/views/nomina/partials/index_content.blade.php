@php($salarios = $salarios ?? collect())

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-8">

    {{-- BANNER DE PERIODO ACTIVO --}}
    @if($periodoActivo)
        <div
            class="mb-8 p-4 bg-blue-50 border border-blue-100 rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="bg-blue-600 text-white p-3 rounded-xl shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4v-4m-9 18h10a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-blue-900 uppercase tracking-wider">Periodo de Liquidación Activo</h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-lg font-extrabold text-blue-900">
                            {{ $periodoActivo->fecha_inicio->format('d/m/Y') }} -
                            {{ $periodoActivo->fecha_fin->format('d/m/Y') }}
                        </span>
                        <span class="px-2 py-0.5 bg-blue-200 text-blue-700 text-[10px] font-bold rounded uppercase">
                            {{ $periodoActivo->tipo_frecuencia }}
                        </span>
                        <span
                            class="px-2 py-0.5 {{ $periodoActivo->estado === 'abierto' ? 'bg-green-200 text-green-700' : 'bg-yellow-200 text-yellow-700' }} text-[10px] font-bold rounded uppercase">
                            {{ $periodoActivo->estado }}
                        </span>
                    </div>
                </div>
            </div>

            <a href="{{ route('periodos.index') }}"
                class="text-blue-600 hover:text-blue-800 text-sm font-bold flex items-center gap-1 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Cambiar Periodo
            </a>

            @if($periodoActivo->estado === \App\Models\PeriodoLiquidacion::ESTADO_ABIERTO)
                <button type="button"
                    onclick="abrirModalCierre({{ $periodoActivo->id_periodo }}, '{{ $periodoActivo->fecha_inicio->format('d/m/Y') }}', '{{ $periodoActivo->fecha_fin->format('d/m/Y') }}')"
                    class="bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-red-700 transition-all shadow-md shadow-red-200 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Cerrar Periodo
                </button>
            @endif
        </div>
    @else
        <div class="mb-8 p-4 bg-yellow-50 border border-yellow-100 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <p class="text-sm font-bold text-yellow-900">No hay un periodo seleccionado.</p>
                    <p class="text-xs text-yellow-700">Debe seleccionar un periodo abierto para poder liquidar nómina.</p>
                </div>
            </div>
            <a href="{{ route('periodos.index') }}"
                class="bg-yellow-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-yellow-700 transition-all shadow-md shadow-yellow-200">
                Seleccionar Periodo
            </a>
        </div>
    @endif

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('nomina.index') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">

        <button type="submit"
                class="sr-only"
                tabindex="-1"
                aria-hidden="true">
            Buscar
        </button>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Buscar empleado
            </label>
            <input type="text" name="documento" value="{{ request('documento') }}" placeholder="Documento o nombres"
                class="w-full border border-gray-300 bg-white px-4 py-2.5 rounded-xl hover:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:border-blue-600 focus:outline-none transition">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Periodo de liquidación
            </label>
            <input type="text"
                   name="documento"
                   value="{{ request('documento') }}"
                   placeholder="Documento o nombres"
                   class="w-full border border-gray-300 bg-white px-4 py-2.5 rounded-xl hover:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:border-blue-600 focus:outline-none transition">
        </div>

        <div class="md:col-span-2 flex flex-wrap gap-2 justify-end">
            <button type="submit"
                    formaction="{{ route('nomina.export.pdf') }}"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                <i class="bi bi-file-earmark-pdf"></i>
                PDF
            </button>
            <button type="submit" formaction="{{ route('nomina.export.excel') }}"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                <i class="bi bi-file-earmark-excel"></i>
                Excel
            </button>
        </div>

    </form>

    {{-- TABLA --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-sm border-collapse min-w-[720px]">

            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-4 py-3 text-center font-semibold tracking-wide w-12">Sel.</th>
                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Documento</th>
                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Empleado</th>
                    <th class="px-4 py-3 text-right font-semibold tracking-wide">Salario inicial</th>
                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Devengos</th>
                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Deducciones</th>
                    <th class="px-4 py-3 text-right font-semibold tracking-wide">Salario base</th>
                </tr>
            </thead>

            <tbody class="bg-white">
                @forelse($salarios as $salario)
                                <tr data-doc="{{ $salario->contrato->usuario->doc }}" data-salario-id="{{ $salario->id_salario }}"
                                    class="nomina-row border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer">

                                    <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                                        <input type="radio" name="selected_nomina"
                                            class="nomina-select-radio h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                            value="{{ $salario->id_salario }}"
                                            aria-label="Seleccionar empleado {{ $salario->contrato->usuario->nombre_completo }}">
                                    </td>

                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $salario->contrato->usuario->doc }}
                                    </td>

                        <td class="px-4 py-3 text-gray-900 font-medium">
                            {{ $salario->contrato->usuario->nombre_completo }}
                        </td>

                                    <td class="px-4 py-3 text-right text-gray-700 whitespace-nowrap">
                                        ${{ number_format($salario->contrato->salario_base ?? 0, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3 text-gray-700">
                                        ${{ number_format($salario->total_devengos, 0, ',', '.') }}
                                    </td>

                        <td class="px-4 py-3 text-red-600 font-medium">
                            (${{ number_format(
                                $salario->total_deducciones,
                                0, ',', '.'
                            ) }})
                        </td>

                        <td class="px-4 py-3 text-right font-semibold text-gray-900 whitespace-nowrap">
                            ${{ number_format($salario->salario_neto, 0, ',', '.') }}
                        </td>

                                </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-400">
                            No hay registros de nomina
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    @if(method_exists($salarios, 'links'))
        <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <p class="text-sm text-gray-500">
                Mostrando {{ $salarios->firstItem() ?? 0 }} a {{ $salarios->lastItem() ?? 0 }} de {{ $salarios->total() }}
                empleados
            </p>
            <div>
                {{ $salarios->links() }}
            </div>
        </div>
    @endif

</div>