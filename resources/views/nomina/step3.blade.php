@extends('layouts.app')

@section('title', 'Deducciones')
@section('page-title', 'Deducciones')

@section('content')

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-8">

    <div class="mb-8">
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full w-full"></div>
        </div>
        <p class="text-sm text-gray-500 mt-2">Paso 3 de 3</p>
    </div>

    <h2 class="text-xl font-semibold mb-6">Deducciones</h2>

    <form method="POST" action="{{ route('nomina.store') }}">
        @csrf

        <div class="grid grid-cols-2 gap-6">

            <div>
                <label>Salud (%)</label>
                <input name="eps" value="4"
                       class="w-full border px-4 py-2 rounded-lg">
            </div>

            <div>
                <label>Pensión (%)</label>
                <input name="afp" value="4"
                       class="w-full border px-4 py-2 rounded-lg">
            </div>

        </div>

        <div class="flex justify-between mt-8">
            <a href="{{ route('nomina.step2') }}" class="text-gray-500">← Volver</a>
            <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                Guardar Nómina
            </button>
        </div>

    </form>
</div>
@endsection
