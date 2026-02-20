@extends('layouts.app')

@section('title', 'Devengos')
@section('page-title', 'Devengos')

@section('content')

<div class="w-full max-w-6xl mx-auto px-4">

    {{-- PROGRESO --}}
    <div class="mb-3">
        <div class="flex justify-between text-xs font-medium text-gray-600 mb-1">
            <span>Paso 2 de 3</span>
            <span>Devengos</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" style="width: 66.66%"></div>
        </div>
    </div>

    {{-- FORMULARIO --}}
    <div class="bg-white rounded-lg shadow-md p-4">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Devengos</h2>

        <form method="POST" action="{{ route('nomina.step2.post') }}">
            @csrf

            {{-- HORAS EXTRA Y BONIFICACIONES - 2 COLUMNAS --}}
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Horas Extra</label>
                    <input name="horas_extra" value="0" type="number"
                           class="w-full border-2 border-gray-300 px-3 py-1.5 rounded text-sm focus:border-blue-500 focus:outline-none transition"
                           placeholder="0">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Bonificaciones</label>
                    <input name="bonificaciones" value="0" type="number"
                           class="w-full border-2 border-gray-300 px-3 py-1.5 rounded text-sm focus:border-blue-500 focus:outline-none transition"
                           placeholder="0">
                </div>
            </div>

            {{-- COMISIONES Y OTROS DEVENGOS - 2 COLUMNAS --}}
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Comisiones</label>
                    <input name="comisiones" value="0" type="number"
                           class="w-full border-2 border-gray-300 px-3 py-1.5 rounded text-sm focus:border-blue-500 focus:outline-none transition"
                           placeholder="0">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Otros Devengos</label>
                    <input name="otros_devengos" value="0" type="number"
                           class="w-full border-2 border-gray-300 px-3 py-1.5 rounded text-sm focus:border-blue-500 focus:outline-none transition"
                           placeholder="0">
                </div>
            </div>

            {{-- BOTONES --}}
            <div class="flex justify-between pt-3 border-t border-gray-200">
                <a href="{{ route('nomina.step1') }}"
                   class="px-5 py-1.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded font-medium transition text-xs">
                    ← Volver
                </a>
                <button type="submit"
                        class="px-6 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold transition shadow-md hover:shadow-lg text-xs">
                    Continuar →
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
