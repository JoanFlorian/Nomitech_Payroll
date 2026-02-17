@extends('layouts.app')

@section('title', 'Nueva Nómina')
@section('page-title', 'Nueva Nómina')

@section('content')

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-8">

    {{-- PROGRESO --}}
    <div class="mb-8">
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full w-1/3"></div>
        </div>
        <p class="text-sm text-gray-500 mt-2">Paso 1 de 3</p>
    </div>

    <h2 class="text-xl font-semibold mb-6">Datos del empleado</h2>

    <form method="POST" action="{{ route('nomina.step1.post') }}">
        @csrf

        <div class="grid grid-cols-2 gap-6">

            <div>
                <label class="text-sm text-gray-600">Documento</label>
                <input id="doc" name="doc"
                       class="w-full border px-4 py-2 rounded-lg"
                       placeholder="Documento">
            </div>

            <div>
                <label class="text-sm text-gray-600">Nombre</label>
                <input id="nombre"
                       class="w-full border px-4 py-2 rounded-lg bg-gray-100"
                       disabled>
            </div>

            <div>
                <label class="text-sm text-gray-600">Teléfono</label>
                <input id="telefono"
                       class="w-full border px-4 py-2 rounded-lg bg-gray-100"
                       disabled>
            </div>

            <div>
                <label class="text-sm text-gray-600">Salario Base</label>
                <input id="salario_base" name="salario_base"
                       class="w-full border px-4 py-2 rounded-lg bg-gray-100"
                       readonly>
            </div>

        </div>

        <input type="hidden" name="id_contrato" id="id_contrato">

        <div class="flex justify-end mt-8">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Continuar
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('doc').addEventListener('blur', function () {
    fetch(`/nomina/buscar-empleado/${this.value}`)
        .then(r => r.json())
        .then(d => {
            if (!d) return alert('Empleado no encontrado');
            nombre.value = d.nombre;
            telefono.value = d.telefono;
            salario_base.value = d.salario_base;
            id_contrato.value = d.id_contrato;
        });
});
</script>

@endsection
