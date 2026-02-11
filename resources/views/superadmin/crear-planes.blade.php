@extends('layouts.superadmin')

@section('content')
<div class="p-6 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">

    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

        <!-- Header -->
        <div class="mb-8 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Crear nuevo plan</h2>
                <p class="text-sm text-gray-500">
                    Configura los parámetros del nuevo paquete de licenciamiento.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-2">
                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.planes.store') }}" class="space-y-8">
            @csrf

            <!-- Card: Info del plan -->
            <div class="border border-gray-100 rounded-2xl p-6 bg-gray-50/60">
                <h3 class="font-semibold text-gray-800 mb-4">Información del plan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Nombre del plan</label>
                        <input type="text" name="nombre"
                               class="w-full border rounded-xl px-3 py-2 outline-none transition
                                      focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ej: Premium Plus" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Duración (meses)</label>
                        <select name="duracion"
                                class="w-full border rounded-xl px-3 py-2 outline-none transition
                                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="1">1 mes</option>
                            <option value="3">3 meses</option>
                            <option value="6">6 meses</option>
                            <option value="12">12 meses (Anual)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Precio</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                            <input type="number" name="valor" step="0.01" min="0"
                                   class="w-full border rounded-xl pl-8 pr-3 py-2 outline-none transition
                                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Máximo de empleados</label>
                        <input type="number" name="num_empl" min="1"
                               class="w-full border rounded-xl px-3 py-2 outline-none transition
                                      focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ej: 50" required>
                    </div>
                </div>
            </div>

            <!-- Card: Descripción -->
            <div class="border border-gray-100 rounded-2xl p-6 bg-gray-50/60">
                <h3 class="font-semibold text-gray-800 mb-4">Descripción</h3>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Descripción del plan</label>
                    <textarea name="descripcion" rows="4"
                              class="w-full border rounded-xl px-3 py-2 outline-none transition
                                     focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Describe los beneficios del plan..."></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-6 border-t">
                <a href="{{ url('/superadmin') }}"
                   class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-100 transition">
                    Cancelar
                </a>

                <button type="submit"
                        class="px-5 py-2 rounded-lg bg-blue-600 text-white font-medium
                               hover:bg-blue-700 shadow-sm hover:shadow transition">
                    Guardar plan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
