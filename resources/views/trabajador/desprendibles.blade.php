@extends('layouts.app')

@section('title', 'Mis Desprendibles')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Mis Desprendibles</h1>
                <p class="text-gray-600">Consulta y descarga tus desprendibles de nómina</p>
            </div>
            <a href="{{ route('trabajador.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <span class="material-icons mr-2 text-sm">arrow_back</span>
                Volver
            </a>
        </div>

        <!-- Tabla de Desprendibles -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            @if($desprendibles->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Periodo</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Fecha Pago</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold">Devengado</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold">Deducciones</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold">Neto</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($desprendibles as $desprendible)
                                <tr class="hover:bg-blue-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-800">
                                            {{ $desprendible->periodo->nombre ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ \Carbon\Carbon::parse($desprendible->created_at)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-green-600 font-semibold">
                                            ${{ number_format($desprendible->total_devengos, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-orange-600 font-semibold">
                                            ${{ number_format($desprendible->total_deducciones, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-blue-600 font-bold text-lg">
                                            ${{ number_format($desprendible->salario_neto, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Ver Detalle -->
                                            <a href="{{ route('trabajador.desprendible.ver', $desprendible->id_salario) }}" 
                                               class="inline-flex items-center px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
                                               title="Ver detalle">
                                                <span class="material-icons text-sm">visibility</span>
                                            </a>
                                            
                                            <!-- Descargar PDF -->
                                            <a href="{{ route('trabajador.desprendible.pdf', $desprendible->id_salario) }}" 
                                               class="inline-flex items-center px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
                                               title="Descargar PDF">
                                                <span class="material-icons text-sm">download</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $desprendibles->links() }}
                </div>
            @else
                <!-- Estado Vacío -->
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-icons text-gray-400 text-4xl">description</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No tienes desprendibles disponibles</h3>
                    <p class="text-gray-500">Tus desprendibles de nómina aparecerán aquí cuando sean procesados.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
