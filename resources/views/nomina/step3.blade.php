@extends('layouts.app')

@section('title', 'Deducciones')
@section('page-title', 'Deducciones')

@section('content')

<div class="w-full max-w-6xl mx-auto px-4">

    {{-- PROGRESO --}}
    <div class="mb-3">
        <div class="flex justify-between text-xs font-medium text-gray-600 mb-1">
            <span>Paso 3 de 3</span>
            <span>Deducciones</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" style="width: 100%"></div>
        </div>
    </div>

    {{-- FORMULARIO --}}
    <div class="bg-white rounded-lg shadow-md p-4">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Deducciones</h2>

        <form method="POST" action="{{ route('nomina.store') }}">
            @csrf

            {{-- SALUD Y PENSIÓN - 2 COLUMNAS --}}
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Salud (%)</label>
                    <input name="eps" value="4" type="number" step="0.01"
                           class="w-full border-2 border-gray-300 px-3 py-1.5 rounded text-sm focus:border-blue-500 focus:outline-none transition"
                           placeholder="4">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pensión (%)</label>
                    <input name="afp" value="4" type="number" step="0.01"
                           class="w-full border-2 border-gray-300 px-3 py-1.5 rounded text-sm focus:border-blue-500 focus:outline-none transition"
                           placeholder="4">
                </div>
            </div>

            {{-- BOTONES --}}
            <div class="flex justify-between pt-3 border-t border-gray-200">
                <a href="{{ route('nomina.step2') }}"
                   class="px-5 py-1.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded font-medium transition text-xs">
                    ← Volver
                </a>
                <button type="submit"
                        class="px-6 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded font-semibold transition shadow-md hover:shadow-lg text-xs">
                    Guardar Nómina
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
