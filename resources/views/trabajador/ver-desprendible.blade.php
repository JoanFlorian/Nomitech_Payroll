@extends('layouts.app')

@section('title', 'Detalle del Desprendible')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Detalle del Desprendible</h1>
                <p class="text-gray-600">Periodo: {{ $desprendible->periodo->nombre ?? 'N/A' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('trabajador.desprendibles') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="material-icons mr-2 text-sm">arrow_back</span>
                    Volver
                </a>
                <a href="{{ route('trabajador.desprendible.pdf', $desprendible->id_salario) }}" class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors">
                    <span class="material-icons mr-2 text-sm">download</span>
                    Descargar PDF
                </a>
            </div>
        </div>

        <!-- Información del Empleado -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <span class="material-icons mr-2 text-blue-600">person</span>
                Información del Empleado
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Empleado</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $desprendible->contrato->usuario->nombre_completo }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Documento</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $desprendible->contrato->usuario->numero_documento }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Periodo</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $desprendible->periodo->nombre ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Fecha de Pago</p>
                    <p class="text-lg font-semibold text-gray-800">{{ \Carbon\Carbon::parse($desprendible->created_at)->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Devengos -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <span class="material-icons mr-2 text-green-600">trending_up</span>
                Devengos
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b-2 border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Concepto</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $totalDevengos = 0; @endphp
                        @foreach($devengos as $concepto => $valor)
                            @if($valor > 0)
                                @php $totalDevengos += $valor; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-700">{{ $concepto }}</td>
                                    <td class="px-4 py-3 text-right text-green-600 font-semibold">
                                        ${{ number_format($valor, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        <tr class="bg-green-50 font-bold">
                            <td class="px-4 py-4 text-gray-800">TOTAL DEVENGOS</td>
                            <td class="px-4 py-4 text-right text-green-700 text-lg">
                                ${{ number_format($desprendible->total_devengos, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Deducciones -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <span class="material-icons mr-2 text-orange-600">trending_down</span>
                Deducciones
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b-2 border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Concepto</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $totalDeducciones = 0; @endphp
                        @foreach($deducciones as $concepto => $valor)
                            @if($valor > 0)
                                @php $totalDeducciones += $valor; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-700">{{ $concepto }}</td>
                                    <td class="px-4 py-3 text-right text-orange-600 font-semibold">
                                        ${{ number_format($valor, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        <tr class="bg-orange-50 font-bold">
                            <td class="px-4 py-4 text-gray-800">TOTAL DEDUCCIONES</td>
                            <td class="px-4 py-4 text-right text-orange-700 text-lg">
                                ${{ number_format($desprendible->total_deducciones, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totales -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 md:p-8 text-white">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-blue-100 text-sm mb-2">Total Devengado</p>
                    <p class="text-3xl font-bold">${{ number_format($desprendible->total_devengos, 0, ',', '.') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-blue-100 text-sm mb-2">Total Deducciones</p>
                    <p class="text-3xl font-bold">${{ number_format($desprendible->total_deducciones, 0, ',', '.') }}</p>
                </div>
                <div class="text-center bg-white/20 rounded-xl p-4">
                    <p class="text-blue-100 text-sm mb-2">Neto a Pagar</p>
                    <p class="text-4xl font-bold">${{ number_format($desprendible->salario_neto, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
