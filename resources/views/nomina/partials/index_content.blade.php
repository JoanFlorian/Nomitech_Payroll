@php($salarios = $salarios ?? collect())

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 md:p-8">

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('nomina.index') }}"
          class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Periodo de liquidacion
            </label>
            <input type="text"
                   id="periodo-liquidacion"
                   name="periodo"
                   placeholder="Seleccionar fecha"
                   class="w-full border border-gray-300 bg-white px-4 py-2.5 rounded-xl hover:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:border-blue-600 focus:outline-none cursor-pointer transition">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Buscar empleado
            </label>
            <input type="text"
                   name="documento"
                   value="{{ request('documento') }}"
                   placeholder="Documento"
                   class="w-full border border-gray-300 bg-white px-4 py-2.5 rounded-xl hover:border-blue-500 focus:ring-2 focus:ring-blue-100 focus:border-blue-600 focus:outline-none transition">
        </div>

    </form>

    {{-- BOTON NUEVA NOMINA --}}
    <div class="flex justify-end mb-6">
        <a href="{{ route('nomina.step1') }}"
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
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">

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
                        <td colspan="6" class="text-center py-10 text-gray-400">
                            No hay registros de nomina
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

</div>
