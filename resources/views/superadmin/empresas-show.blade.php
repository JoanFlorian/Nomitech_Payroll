@extends('layouts.superadmin')

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow border p-6 relative">

        <h2 class="text-xl font-bold mb-4">
            {{ $empresa->razon_social }}
        </h2>

        <p class="text-sm text-green-600 mb-4">
             Empresa Cliente Verificada
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Información Legal -->
            <div>
                <h3 class="font-semibold mb-3">Información Legal</h3>

                <div class="space-y-2 text-sm">
                    <p><strong>Representante:</strong> {{ $empresa->representante->nombre ?? 'No asignado' }}</p>
                    <p><strong>NIT:</strong> {{ $empresa->nit }}</p>
                    <p><strong>Dirección:</strong> {{ $empresa->direccion }}</p>
                    <p><strong>Ciudad:</strong> {{ $empresa->ciudad->nombre ?? '-' }}</p>
                </div>
            </div>

            <!-- Estado de Membresía -->
            <div>
                <h3 class="font-semibold mb-3">Estado de Membresía</h3>

                <div class="space-y-2 text-sm">
                    <p><strong>Plan:</strong> {{ $empresa->licencia->plan->nombre ?? 'Demo' }}</p>
                    <p><strong>Fecha de vencimiento:</strong> {{ $empresa->licencia->fecha_fin ?? 'No aplica' }}</p>
                    <p><strong>Estado:</strong> {{ strtoupper($empresa->licencia->estado_calculado ?? 'PRUEBA') }}</p>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3 mt-6">
            <button onclick="document.getElementById('modalEditar').classList.remove('hidden')"
                class="border px-4 py-2 rounded">
                Editar Datos
            </button>

            <a href="#" class="bg-green-600 text-white px-4 py-2 rounded">
                Descargar Certificado
            </a>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div id="modalEditar" class="fixed inset-0 bg-black/40 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
        <h3 class="font-semibold mb-4">Editar datos de la empresa</h3>

        <form method="POST" action="{{ route('superadmin.empresas.update', $empresa->id_empresa) }}">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <input type="text" name="razon_social" value="{{ $empresa->razon_social }}" class="w-full border p-2 rounded">
                <input type="text" name="direccion" value="{{ $empresa->direccion }}" class="w-full border p-2 rounded">
                <input type="email" name="correo" value="{{ $empresa->correo }}" class="w-full border p-2 rounded">
                <input type="text" name="telefono" value="{{ $empresa->telefono }}" class="w-full border p-2 rounded">
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button"
                        onclick="document.getElementById('modalEditar').classList.add('hidden')"
                        class="px-3 py-2 border rounded">
                    Cancelar
                </button>

                <button class="bg-blue-600 text-white px-4 py-2 rounded">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
