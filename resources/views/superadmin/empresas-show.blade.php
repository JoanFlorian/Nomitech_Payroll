@extends('layouts.superadmin')

@section('content')
    <div class="p-6 bg-gray-50">


        <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg border p-8 mb-0">


            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ $empresa->razon_social }}
                    </h2>
                    <p class="text-sm text-green-600 mt-1">
                        Empresa Cliente Verificada
                    </p>
                </div>

                @php
                    $estado = optional($empresa->licencia)->estado ?? 'prueba';
                @endphp

                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    {{ $estado == 'activa' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $estado == 'por_vencer' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $estado == 'vencida' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $estado == 'prueba' ? 'bg-blue-100 text-blue-700' : '' }}
                ">
                    {{ strtoupper(str_replace('_', ' ', $estado)) }}
                </span>
            </div>

            <!-- Grid principal -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Card: Información Legal -->
                <div class="col-span-2 bg-gray-50 rounded-xl p-6 border">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="bi bi-shield-check text-blue-600"></i>
                        Información Legal
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">Representante</p>
                            <p class="font-medium">
                                {{ optional($empresa->representante)->primer_nombre ?? 'No asignado' }}
                            </p>
                        </div>

                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">NIT</p>
                            <p class="font-medium">{{ $empresa->nit }}</p>
                        </div>

                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">Dirección</p>
                            <p class="font-medium">{{ $empresa->direccion }}</p>
                        </div>

                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">Ciudad</p>
                            <p class="font-medium">{{ optional($empresa->ciudad)->nombre ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card: Estado de Membresía -->
                <div class="bg-gray-50 rounded-xl p-6 border">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="bi bi-star-fill text-yellow-500"></i>
                        Estado de Membresía
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">Plan</p>
                            <p class="font-medium">
                                {{ optional(optional($empresa->licencia)->plan)->nombre ?? 'Demo' }}
                            </p>
                        </div>

                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">Fecha de vencimiento</p>
                            <p class="font-medium">
                                {{ optional($empresa->licencia)->fecha_fin ?? 'No aplica' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8">
                <button onclick="document.getElementById('modalEditar').classList.remove('hidden')"
                    class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                    Editar Datos
                </button>

                <a href="#" class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition shadow">
                    Descargar Certificado
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Editar (Formulario corregido) -->
    <div id="modalEditar" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl w-full max-w-2xl mx-auto shadow-2xl overflow-hidden">

            <!-- Header -->
            <div class="px-6 py-4 border-b flex items-center justify-between bg-gray-50">
                <div>
                    <h3 class="font-semibold text-lg text-gray-900">Editar datos de la empresa</h3>
                    <p class="text-sm text-gray-500">Actualiza la información permitida</p>
                </div>
                <button onclick="document.getElementById('modalEditar').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('superadmin.empresas.update', $empresa->id_empresa) }}">
                @csrf
                @method('PUT')

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Dirección -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input type="text" name="direccion" value="{{ old('direccion', $empresa->direccion) }}"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <!-- Correo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                        <input type="email" name="correo" value="{{ old('correo', $empresa->correo) }}"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <!-- Ciudad -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                        <select name="id_ciudad"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            @foreach($ciudades as $ciudad)
                                <option value="{{ $ciudad->id_ciudad }}" {{ $empresa->id_ciudad == $ciudad->id_ciudad ? 'selected' : '' }}>
                                    {{ $ciudad->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Documento representante -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Documento del representante</label>
                        <input type="text" name="doc_representante"
                            value="{{ old('doc_representante', $empresa->doc_representante) }}"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <!-- Primer nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Primer nombre</label>
                        <input type="text" name="primer_nombre"
                            value="{{ old('primer_nombre', optional($empresa->representante)->primer_nombre) }}"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <!-- Segundo nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Segundo nombre</label>
                        <input type="text" name="segundo_nombre"
                            value="{{ old('segundo_nombre', optional($empresa->representante)->segundo_nombre) }}"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <!-- Primer apellido -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Primer apellido</label>
                        <input type="text" name="primer_apellido"
                            value="{{ old('primer_apellido', optional($empresa->representante)->primer_apellido) }}"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <!-- Segundo apellido -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Segundo apellido</label>
                        <input type="text" name="segundo_apellido"
                            value="{{ old('segundo_apellido', optional($empresa->representante)->segundo_apellido) }}"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modalEditar').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                        Cancelar
                    </button>

                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection