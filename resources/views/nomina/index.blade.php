@extends('layouts.app')

@section('title', 'Nómina')
@section('page-title', 'NÓMINA')

@section('content')

<div class="bg-white rounded-xl shadow p-8">

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('nomina.index') }}"
          class="grid grid-cols-2 gap-6 mb-6">

        <div>
            <label class="block text-sm text-gray-600 mb-1">
                Periodo de liquidación
            </label>
            <input type="text"
                   value="Periodo activo"
                   disabled
                   class="w-full border px-4 py-2 rounded-lg bg-gray-100">
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">
                Buscar empleado
            </label>
            <input type="text"
                   name="documento"
                   value="{{ request('documento') }}"
                   placeholder="Documento"
                   class="w-full border px-4 py-2 rounded-lg">
        </div>

    </form>

    {{-- BOTÓN NUEVA NÓMINA --}}
    <div class="flex justify-end mb-6">
        <a href="{{ route('nomina.step1') }}"
           title="Crear nueva nómina"
           class="bg-green-600 hover:bg-green-700 text-white
                  rounded-full w-12 h-12 flex items-center
                  justify-center text-2xl shadow-lg transition">

            +
        </a>
    </div>

    {{-- TABLA --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">

            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-4 py-3 text-left">Documento</th>
                    <th class="px-4 py-3 text-left">Empleado</th>
                    <th class="px-4 py-3 text-left">Devengos</th>
                    <th class="px-4 py-3 text-left">Deducciones</th>
                    <th class="px-4 py-3 text-right">Salario Neto</th>
                </tr>
            </thead>

            <tbody class="bg-white">
                @forelse($salarios as $salario)
                    <tr class="border-b hover:bg-gray-50">

                        <td class="px-4 py-3">
                            {{ $salario->contrato->usuario->doc }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $salario->contrato->usuario->nombre_completo }}
                        </td>

                        <td class="px-4 py-3">
                            ${{ number_format($salario->total_devengos, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3 text-red-600">
                            (${{ number_format(
                                $salario->total_deducciones,
                                0, ',', '.'
                            ) }})
                        </td>

                        <td class="px-4 py-3 text-right font-semibold">
                            ${{ number_format($salario->salario_neto, 0, ',', '.') }}
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-400">
                            No hay registros de nómina
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

</div>

@endsection
