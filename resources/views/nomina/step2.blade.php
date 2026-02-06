@extends('layouts.app')

@section('title', 'Devengos')
@section('page-title', 'Devengos')

@section('content')

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-8">

    <div class="mb-8">
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full w-2/3"></div>
        </div>
        <p class="text-sm text-gray-500 mt-2">Paso 2 de 3</p>
    </div>

    <h2 class="text-xl font-semibold mb-6">Devengos</h2>

    <form method="POST" action="{{ route('nomina.step2.post') }}">
        @csrf

        <div class="grid grid-cols-2 gap-6">

            <div>
                <label>Horas Extra</label>
                <input name="horas_extra" value="0"
                       class="w-full border px-4 py-2 rounded-lg">
            </div>

            <div>
                <label>Bonificaciones</label>
                <input name="bonificaciones" value="0"
                       class="w-full border px-4 py-2 rounded-lg">
            </div>

            <div>
                <label>Comisiones</label>
                <input name="comisiones" value="0"
                       class="w-full border px-4 py-2 rounded-lg">
            </div>

            <div>
                <label>Otros Devengos</label>
                <input name="otros_devengos" value="0"
                       class="w-full border px-4 py-2 rounded-lg">
            </div>

        </div>

        <div class="flex justify-between mt-8">
            <a href="{{ route('nomina.step1') }}" class="text-gray-500">← Volver</a>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Continuar
            </button>
        </div>

    </form>
</div>
@endsection
