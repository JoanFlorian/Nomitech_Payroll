@extends('layouts.app')

@section('title', 'Nueva Nómina')
@section('page-title', 'Nueva Nómina')

@section('content')

@php($s1 = $step1 ?? [])
@php($isEditing = $isEditing ?? false)

<div class="relative">
    <div class="max-w-6xl mx-auto px-4 md:px-6 lg:px-8 py-6 md:py-8" aria-hidden="true">
        @include('nomina.partials.index_content', ['salarios' => $salarios ?? collect()])
    </div>

    <div class="fixed inset-0 bg-gray-900/85 z-40" aria-hidden="true"></div>
    <div class="fixed inset-0 z-40 pointer-events-none overflow-hidden" aria-hidden="true">
        @include('nomina.partials.modal_figures')
    </div>

    <div class="fixed inset-0 z-50 p-3 md:p-4 flex items-center justify-center">
        <div class="relative w-full max-w-[1120px] max-h-[92vh] overflow-auto bg-white rounded-3xl shadow-2xl border border-gray-200 p-3 md:p-4 modal-enter">
            <a href="{{ route('nomina.index') }}"
               class="absolute top-3 right-3 z-20 h-10 w-10 rounded-full bg-white border border-gray-300 text-gray-700 hover:text-gray-900 hover:bg-gray-50 flex items-center justify-center shadow-sm transition"
               aria-label="Cerrar">
                <span class="text-xl leading-none font-bold">&times;</span>
            </a>

    {{-- HERO + FORMULARIO --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-1">
            <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-blue-500 text-white p-5 md:p-6 shadow-lg">
                <div class="text-xs uppercase tracking-widest text-blue-100">Nueva Nómina</div>
                <h2 class="text-2xl font-bold mt-2">Paso 1: Datos del empleado</h2>
                <p class="text-blue-100 mt-3 text-sm">Busca el contrato activo y confirma el salario base antes de continuar.</p>

                <div class="mt-6">
                    <div class="flex items-center justify-between text-xs font-semibold text-blue-100 mb-2">
                        <span>Progreso</span>
                        <span>1 de 3</span>
                    </div>
                    <div class="w-full bg-blue-400/40 rounded-full h-2">
                        <div class="bg-white h-2 rounded-full transition-all duration-300" style="width: 33.33%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-3 rounded-2xl border border-blue-100 bg-white/80 p-4 shadow-sm">
                <div class="text-sm font-semibold text-slate-800">Consejo rápido</div>
                <p class="text-xs text-slate-500 mt-1">Usa el documento del empleado tal como aparece en el contrato.</p>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-md border border-gray-100 p-5 md:p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-2xl font-bold text-gray-800">Buscar Empleado</h3>
                    <span class="hidden md:inline-flex items-center gap-2 text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full">
                        Verifica el contrato activo
                    </span>
                </div>

        <form method="POST" action="{{ route('nomina.step1.post') }}" id="formNomina">
            @csrf

            {{-- MOSTRAR ERRORES Y MENSAJES FLASH --}}
            @if(session('error'))
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-red-900 mb-1">Error</p>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button type="button" class="flex-shrink-0 text-red-400 hover:text-red-600 transition" onclick="this.closest('div').remove()">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-red-900 mb-1">Errores de validación</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="flex-shrink-0 text-red-400 hover:text-red-600 transition" onclick="this.closest('div').remove()">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            @endif

            {{-- EMPLEADO --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Buscar empleado <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                          <input id="empleado_busqueda" name="empleado_busqueda" type="text"
                                    class="w-full border-2 border-gray-300 px-4 py-3 rounded-xl text-base focus:border-blue-500 focus:outline-none transition bg-white shadow-sm {{ $isEditing ? 'bg-gray-50 cursor-not-allowed' : '' }}"
                              value="{{ old('empleado_busqueda', $s1['empleado_busqueda'] ?? '') }}"
                           placeholder="Escribe nombre o documento"
                           pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\- ]+"
                           maxlength="120"
                           autocomplete="off"
                                    @readonly($isEditing)
                           required>
                    <div id="loadingSpinner" class="hidden absolute right-4 top-3.5">
                        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <div id="sugerenciasEmpleados" class="hidden absolute z-30 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-64 overflow-y-auto">
                        <ul id="listaSugerencias" class="py-1"></ul>
                    </div>
                </div>
                <p id="docHelp" class="text-xs text-gray-500 mt-2">Escribe nombre o documento y selecciona una opción.</p>
                <p id="errorMsg" class="hidden text-red-600 text-sm mt-2 font-medium"></p>
            </div>

            {{-- DATOS PERSONALES - 2 COLUMNAS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre Completo</label>
                    <input id="nombre" type="text"
                           class="w-full border-2 border-gray-200 px-4 py-3 rounded-xl bg-gray-50 text-gray-700 cursor-not-allowed text-sm"
                              value="{{ old('nombre', $s1['nombre'] ?? '') }}"
                           disabled>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                    <input id="telefono" type="text"
                              class="w-full border-2 border-gray-200 px-4 py-3 rounded-xl bg-gray-50 text-gray-700 cursor-not-allowed text-sm"
                           value="{{ old('telefono', $s1['telefono'] ?? '') }}"
                           disabled>
                </div>
            </div>

            {{-- SALARIO, DIAS Y FECHA PAGO --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Salario Base</label>
                    <input id="salario_base" name="salario_base" type="text"
                           class="w-full border-2 border-gray-200 px-4 py-3 rounded-xl bg-gray-50 text-gray-700 cursor-not-allowed font-semibold text-sm"
                              value="{{ old('salario_base', isset($s1['salario_base']) ? number_format((float) $s1['salario_base'], 0, ',', '.') : '') }}"
                           readonly>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Dias Trabajados <span class="text-red-500">*</span>
                    </label>
                    <input id="dias_trabajados" name="dias_trabajados" type="number"
                        min="0" max="{{ $s1['max_dias_trabajados'] ?? 30 }}" step="1"
                        data-max-sln="{{ $s1['max_dias_trabajados'] ?? 30 }}"
                        data-dias-sln="{{ $s1['dias_sln'] ?? 0 }}"
                        inputmode="numeric"
                        pattern="[0-9]+"
                        value="{{ old('dias_trabajados', $s1['dias_trabajados'] ?? ($s1['max_dias_trabajados'] ?? 30)) }}"
                        class="w-full border-2 border-gray-300 px-4 py-3 rounded-xl text-sm focus:border-blue-500 focus:outline-none transition bg-white shadow-sm"
                        required>
                    <p id="diasError" class="hidden text-red-600 text-sm mt-2 font-medium"></p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Fecha de Pago <span class="text-red-500">*</span>
                    </label>
                    <input id="fecha_pago" name="fecha_pago" type="date"
                              class="w-full border-2 border-gray-300 px-4 py-3 rounded-xl text-sm focus:border-blue-500 focus:outline-none transition bg-white shadow-sm {{ $isEditing ? 'bg-gray-50 cursor-not-allowed' : '' }}"
                           value="{{ old('fecha_pago', $s1['fecha_pago'] ?? '') }}"
                              @readonly($isEditing)
                           required>
                    <p id="fechaError" class="hidden text-red-600 text-sm mt-2 font-medium"></p>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-blue-100 bg-blue-50/60 p-3.5">
                <h4 class="text-sm font-semibold text-blue-900 mb-3">Resumen de salario proporcional</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-lg bg-white border border-blue-100 p-3">
                        <div class="text-xs text-gray-500">Valor dia</div>
                        <div id="resumen_valor_dia" class="font-semibold text-gray-800">$0</div>
                    </div>
                    <div class="rounded-lg bg-white border border-blue-100 p-3">
                        <div class="text-xs text-gray-500">Dias trabajados</div>
                        <div id="resumen_dias_trabajados" class="font-semibold text-gray-800">0</div>
                    </div>
                    <div class="rounded-lg bg-white border border-blue-100 p-3">
                        <div class="text-xs text-gray-500">Salario proporcional</div>
                        <div id="resumen_salario_proporcional" class="font-semibold text-blue-700">$0</div>
                    </div>
                </div>
            </div>

            {{-- CAMPOS OCULTOS --}}
            <input type="hidden" name="doc" id="doc" value="{{ old('doc', $s1['doc'] ?? '') }}">
            <input type="hidden" name="id_contrato" id="id_contrato" value="{{ old('id_contrato', $s1['id_contrato'] ?? '') }}">

            {{-- BOTONES --}}
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('nomina.index') }}"
                   class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition text-sm text-center">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition shadow-md hover:shadow-lg text-sm">
                    Continuar →
                </button>
            </div>
        </form>
    </div>

        </div>
    </div>

        </div>
    </div>
</div>

@include('nomina.partials.step1_script')
@include('nomina.partials.modal_assets')

@endsection
