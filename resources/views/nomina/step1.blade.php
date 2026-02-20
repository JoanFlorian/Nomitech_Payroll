@extends('layouts.app')

@section('title', 'Nueva Nómina')
@section('page-title', 'Nueva Nómina')

@section('content')

<div class="w-full max-w-6xl mx-auto px-4">

    {{-- PROGRESO --}}
    <div class="mb-3">
        <div class="flex justify-between text-xs font-medium text-gray-600 mb-1">
            <span>Paso 1 de 3</span>
            <span>Datos del empleado</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" style="width: 33.33%"></div>
        </div>
    </div>

    {{-- FORMULARIO --}}
    <div class="bg-white rounded-lg shadow-md p-4">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Buscar Empleado</h2>

        <form method="POST" action="{{ route('nomina.step1.post') }}" id="formNomina">
            @csrf

            {{-- DOCUMENTO --}}
            <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                    Documento <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input id="doc" name="doc" type="text"
                           class="w-full border-2 border-gray-300 px-3 py-1.5 rounded text-sm focus:border-blue-500 focus:outline-none transition bg-white"
                           placeholder="Ingrese documento"
                           required>
                    <div id="loadingSpinner" class="hidden absolute right-3 top-1.5">
                        <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                <p id="errorMsg" class="hidden text-red-500 text-xs mt-0.5"></p>
            </div>

            {{-- DATOS PERSONALES - 2 COLUMNAS --}}
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre Completo</label>
                    <input id="nombre" type="text"
                           class="w-full border-2 border-gray-200 px-3 py-1.5 rounded bg-gray-50 text-gray-700 cursor-not-allowed text-xs"
                           disabled>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Teléfono</label>
                    <input id="telefono" type="text"
                           class="w-full border-2 border-gray-200 px-3 py-1.5 rounded bg-gray-50 text-gray-700 cursor-not-allowed text-xs"
                           disabled>
                </div>
            </div>

            {{-- SALARIO Y FECHA PAGO - 2 COLUMNAS --}}
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Salario Base</label>
                    <input id="salario_base" name="salario_base" type="text"
                           class="w-full border-2 border-gray-200 px-3 py-1.5 rounded bg-gray-50 text-gray-700 cursor-not-allowed font-semibold text-xs"
                           readonly>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Fecha de Pago <span class="text-red-500">*</span>
                    </label>
                    <input id="fecha_pago" name="fecha_pago" type="date"
                           class="w-full border-2 border-gray-300 px-3 py-1.5 rounded text-xs focus:border-blue-500 focus:outline-none transition"
                           required>
                </div>
            </div>

            {{-- CAMPOS OCULTOS --}}
            <input type="hidden" name="id_contrato" id="id_contrato">

            {{-- BOTONES --}}
            <div class="flex justify-between pt-3 border-t border-gray-200">
                <a href="{{ route('nomina.index') }}"
                   class="px-5 py-1.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded font-medium transition text-xs">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold transition shadow-md hover:shadow-lg text-xs">
                    Continuar →
                </button>
            </div>
        </form>
    </div>

</div>

<script>
document.getElementById('doc').addEventListener('blur', function () {
    const docValue = this.value.trim();
    
    if (!docValue) {
        limpiarFormulario();
        return;
    }

    // Mostrar spinner
    document.getElementById('loadingSpinner').classList.remove('hidden');
    document.getElementById('errorMsg').classList.add('hidden');

    fetch(`/nomina/buscar-empleado/${docValue}`)
        .then(response => {
            if (!response.ok) throw new Error('Error en la solicitud');
            return response.json();
        })
        .then(data => {
            document.getElementById('loadingSpinner').classList.add('hidden');
            
            if (!data) {
                mostrarError('Empleado no encontrado o no tiene contratos activos');
                limpiarFormulario();
                return;
            }

            // Llenar los campos
            document.getElementById('nombre').value = data.nombre || '';
            document.getElementById('telefono').value = data.telefono || '';
            document.getElementById('salario_base').value = data.salario_base ? 
                new Intl.NumberFormat('es-CO', {style: 'currency', currency: 'COP'}).format(data.salario_base) : '';
            document.getElementById('id_contrato').value = data.id_contrato || '';

            // Establecer la fecha de pago a hoy si está vacía
            if (!document.getElementById('fecha_pago').value) {
                const hoy = new Date().toISOString().split('T')[0];
                document.getElementById('fecha_pago').value = hoy;
            }
        })
        .catch(error => {
            document.getElementById('loadingSpinner').classList.add('hidden');
            console.error('Error:', error);
            mostrarError('Error al buscar el empleado. Intente de nuevo.');
            limpiarFormulario();
        });
});

// Validar que el documento esté completo antes de enviar
document.getElementById('formNomina').addEventListener('submit', function (e) {
    const idContrato = document.getElementById('id_contrato').value;
    
    if (!idContrato) {
        e.preventDefault();
        mostrarError('Por favor busque un empleado válido antes de continuar');
        return false;
    }
});

function limpiarFormulario() {
    document.getElementById('nombre').value = '';
    document.getElementById('telefono').value = '';
    document.getElementById('salario_base').value = '';
    document.getElementById('id_contrato').value = '';
}

function mostrarError(mensaje) {
    const errorMsg = document.getElementById('errorMsg');
    errorMsg.textContent = mensaje;
    errorMsg.classList.remove('hidden');
}
</script>

@endsection
