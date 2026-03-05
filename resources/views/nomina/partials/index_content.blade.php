@php($salarios = $salarios ?? collect())

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-8">

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('nomina.index') }}"
            class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">

        <button type="submit"
                class="sr-only"
                tabindex="-1"
                aria-hidden="true">
            Buscar
        </button>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Periodo de liquidacion
            </label>
            <input type="text"
                   id="periodo-liquidacion"
                   name="periodo"
                     value="{{ request('periodo') }}"
                   placeholder="Seleccionar fecha"
                   class="w-full border border-gray-300 bg-white px-4 py-2.5 rounded-xl hover:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:border-blue-600 focus:outline-none cursor-pointer transition">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Buscar empleado
            </label>
            <input type="text"
                   id="buscar-empleado"
                   name="documento"
                   list="empleados-sugeridos"
                   value="{{ request('documento') }}"
                   placeholder="Documento o nombres"
                   class="w-full border border-gray-300 bg-white px-4 py-2.5 rounded-xl hover:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:border-blue-600 focus:outline-none transition">
            <datalist id="empleados-sugeridos"></datalist>
        </div>

        <div class="md:col-span-2 flex flex-wrap gap-2 justify-end">
            <button type="submit"
                    formaction="{{ route('nomina.export.pdf') }}"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                <i class="bi bi-file-earmark-pdf"></i>
                PDF
            </button>
            <button type="submit"
                    formaction="{{ route('nomina.export.excel') }}"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                <i class="bi bi-file-earmark-excel"></i>
                Excel
            </button>
        </div>

    </form>

    {{-- BOTON NUEVA NOMINA --}}
    <div class="flex justify-end gap-3 mb-6">
        <button type="button"
                id="btn-editar-empleado"
                title="Editar empleado seleccionado"
                disabled
                class="bg-gray-300 text-white rounded-full w-12 h-12 flex items-center justify-center text-xl shadow-md cursor-not-allowed opacity-70 transition-all duration-200">
            ✎
        </button>

        <a href="{{ route('nomina.step1', ['fresh' => 1]) }}"
           title="Crear nueva nomina"
           class="bg-green-600 hover:bg-green-700 text-white
                  rounded-full w-12 h-12 flex items-center
                  justify-center text-2xl shadow-md hover:shadow-lg
                  transition-all duration-200">

            +
        </a>
    </div>

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
                    <th class="px-4 py-3 text-right font-semibold tracking-wide">Novedades</th>
                    <th class="px-4 py-3 text-right font-semibold tracking-wide">Salario final</th>
                </tr>
            </thead>

            <tbody class="bg-white">
                @forelse($salarios as $salario)
                    <tr data-doc="{{ $salario->contrato->usuario->doc }}"
                        data-salario-id="{{ $salario->id_salario }}"
                        class="nomina-row border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer">

                        <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                            <input type="radio"
                                   name="selected_nomina"
                                   class="nomina-select-radio h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                   value="{{ $salario->id_salario }}"
                                   aria-label="Seleccionar empleado {{ $salario->contrato->usuario->nombre_completo }}">
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            {{ $salario->contrato->usuario->doc }}
                        </td>

                        <td class="px-4 py-3 text-gray-900 font-medium">
                            {{ \Illuminate\Support\Str::title(mb_strtolower((string) ($salario->contrato->usuario->nombre_completo ?? ''))) }}
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

                        @if ((float) ($salario->total_novedades ?? 0) > 0)
                            <td class="px-4 py-3 text-right font-medium whitespace-nowrap text-emerald-600">
                                +${{ number_format((float) $salario->total_novedades, 0, ',', '.') }}
                            </td>
                        @elseif ((float) ($salario->total_novedades ?? 0) < 0)
                            <td class="px-4 py-3 text-right font-medium whitespace-nowrap text-red-600">
                                -${{ number_format(abs((float) $salario->total_novedades), 0, ',', '.') }}
                            </td>
                        @else
                            <td class="px-4 py-3 text-right font-medium whitespace-nowrap text-gray-500">
                                No tiene
                            </td>
                        @endif

                        <td class="px-4 py-3 text-right font-semibold text-gray-900 whitespace-nowrap">
                            ${{ number_format(((float) $salario->salario_neto) + ((float) ($salario->total_novedades ?? 0)), 0, ',', '.') }}
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
                Mostrando {{ $salarios->firstItem() ?? 0 }} a {{ $salarios->lastItem() ?? 0 }} de {{ $salarios->total() }} empleados
            </p>
            <div>
                {{ $salarios->links() }}
            </div>
        </div>
    @endif

</div>
