@extends('layouts.app')

@section('title', 'Notas de Ajuste')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Notas de Ajuste</h1>
                <p class="text-gray-600">Consulta tus notas de ajuste y observaciones</p>
            </div>
            <a href="{{ route('trabajador.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <span class="material-icons mr-2 text-sm">arrow_back</span>
                Volver
            </a>
        </div>

        <!-- Tabla de Notas -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            @if($notas->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Fecha</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Concepto</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Tipo</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold">Valor</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Observación</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Periodo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($notas as $nota)
                                <tr class="hover:bg-green-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="text-gray-700 font-medium">
                                            {{ \Carbon\Carbon::parse($nota->created_at)->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-gray-800 font-medium">
                                            {{ $nota->tipo_novedad }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($nota->valor > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="material-icons text-xs mr-1">add_circle</span>
                                                Devengo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <span class="material-icons text-xs mr-1">remove_circle</span>
                                                Deducción
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-bold {{ $nota->valor > 0 ? 'text-green-600' : 'text-orange-600' }}">
                                            ${{ number_format(abs($nota->valor), 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-gray-600 text-sm">
                                            {{ $nota->observaciones ?? 'Sin observaciones' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-gray-700">
                                            {{ $nota->salario->periodo->nombre ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $notas->links() }}
                </div>
            @else
                <!-- Estado Vacío -->
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-icons text-gray-400 text-4xl">receipt_long</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No tienes notas de ajuste</h3>
                    <p class="text-gray-500">Las notas de ajuste y observaciones aparecerán aquí cuando sean registradas.</p>
                </div>
            @endif
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <span class="material-icons text-blue-500 mr-3 mt-0.5">info</span>
                <div>
                    <h4 class="font-semibold text-blue-900 mb-1">Información sobre Notas de Ajuste</h4>
                    <p class="text-sm text-blue-800">
                        Las notas de ajuste son modificaciones realizadas a tu nómina por diversos conceptos.
                        Los <strong>devengos</strong> son valores que se suman a tu pago, mientras que las <strong>deducciones</strong> son valores que se restan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
