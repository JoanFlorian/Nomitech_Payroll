@extends('layouts.app')

@section('title', 'Empleados')
@section('page-title', 'EMPLEADOS')

@section('content')

<div x-data="empleadosModule()" @open-modal-registro.window="openRegistroModal()" @close-modal.window="closeModals()">

    {{-- ENCABEZADO --}}
    <div class="mb-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-4xl font-bold text-gray-900">Gestor de Empleados</h1>
            <a href="{{ route('employees.export') }}" class="bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-lg font-semibold transition duration-200 shadow-md">
                <i class="fas fa-download mr-2"></i>Exportar a Excel
            </a>
        </div>

        {{-- BÚSQUEDA Y FILTROS --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            {{-- BARRA DE BÚSQUEDA --}}
            <form method="GET" action="{{ route('empleados.index') }}" class="mb-6">
                <div class="flex flex-col md:flex-row gap-3">
                    <div class="flex-1">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Buscar por nombre, documento o correo..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                        >
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                </div>
            </form>

            {{-- FILTROS POR ESTADO --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('empleados.index') }}" 
                   class="px-4 py-2 rounded-full font-semibold transition duration-200 {{ !request('estado') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                    <i class="fas fa-list mr-2"></i>Todos ({{ $totalEmpleados }})
                </a>
                <a href="{{ route('empleados.index', ['estado' => 'activos']) }}" 
                   class="px-4 py-2 rounded-full font-semibold transition duration-200 {{ request('estado') === 'activos' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                    <i class="fas fa-check-circle mr-2"></i>Activos ({{ $activosCount }})
                </a>
                <a href="{{ route('empleados.index', ['estado' => 'inactivos']) }}" 
                   class="px-4 py-2 rounded-full font-semibold transition duration-200 {{ request('estado') === 'inactivos' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                    <i class="fas fa-times-circle mr-2"></i>Inactivos ({{ $inactivosCount }})
                </a>
                <a href="{{ route('empleados.index', ['estado' => 'sin_contrato']) }}" 
                   class="px-4 py-2 rounded-full font-semibold transition duration-200 {{ request('estado') === 'sin_contrato' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                    <i class="fas fa-user-slash mr-2"></i>Sin Contrato ({{ $sinContratoCount }})
                </a>
            </div>
        </div>
    </div>

    {{-- TABLA DE EMPLEADOS --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gradient-to-r from-[#1565C0] to-[#1976D2] text-white">
                        <th class="py-4 px-6 font-semibold text-left">Empleado</th>
                        <th class="py-4 px-6 font-semibold text-left">Documento</th>
                        <th class="py-4 px-6 font-semibold text-left">Tipo Contrato</th>
                        <th class="py-4 px-6 font-semibold text-right">Salario Base</th>
                        <th class="py-4 px-6 font-semibold text-center">Estado</th>
                        <th class="py-4 px-6 font-semibold text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($empleados as $usuario)
                    <?php 
                    $contrato = $usuario->contratos->first();
                    ?>
                    
                    @if ($contrato)
                    <tr class="hover:bg-blue-50 transition duration-200 even:bg-gray-50">
                        {{-- EMPLEADO --}}
                        <td class="py-4 px-6 text-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user-tie text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex flex-col">
                                    <p class="font-semibold text-gray-900">{{ $usuario->primer_nombre }} {{ $usuario->primer_apellido }}</p>
                                    <p class="text-xs text-gray-500">{{ $usuario->otros_nombres }} {{ $usuario->segundo_apellido }}</p>
                                </div>
                            </div>
                        </td>
                        {{-- DOCUMENTO --}}
                        <td class="py-4 px-6 text-gray-600">
                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $usuario->doc }}</span>
                        </td>
                        {{-- TIPO CONTRATO --}}
                        <td class="py-4 px-6 text-gray-700">
                            <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                {{ $contrato->tipoContrato->nombre ?? 'N/A' }}
                            </span>
                        </td>
                        {{-- SALARIO --}}
                        <td class="py-4 px-6 text-right font-semibold text-gray-900">
                            $ {{ number_format($contrato->salario_base ?? 0, 0, ',', '.') }}
                        </td>
                        {{-- ESTADO --}}
                        <td class="py-4 px-6 text-center">
                            @if($contrato->activo)
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Activo
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>Inactivo
                                </span>
                            @endif
                        </td>
                        {{-- ACCIONES --}}
                        <td class="py-4 px-6 text-center">
                            <button 
                                @click="openEditModal('{{ $usuario->doc }}')"
                                class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded text-xs font-semibold transition duration-200 inline-flex items-center gap-1"
                            >
                                <i class="fas fa-edit"></i>Editar
                            </button>
                        </td>
                    </tr>
                    @else
                    <tr class="hover:bg-orange-50 transition duration-200 even:bg-gray-50">
                        {{-- EMPLEADO SIN CONTRATO --}}
                        <td class="py-4 px-6 text-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user-slash text-orange-600 text-sm"></i>
                                </div>
                                <div class="flex flex-col">
                                    <p class="font-semibold text-gray-900">{{ $usuario->primer_nombre }} {{ $usuario->primer_apellido }}</p>
                                    <p class="text-xs text-gray-500">{{ $usuario->email ?? 'Sin email' }}</p>
                                </div>
                            </div>
                        </td>
                        {{-- DOCUMENTO --}}
                        <td class="py-4 px-6 text-gray-600">
                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $usuario->doc }}</span>
                        </td>
                        {{-- TIPO CONTRATO (VACÍO) --}}
                        <td class="py-4 px-6 text-gray-500 italic">—</td>
                        {{-- SALARIO (VACÍO) --}}
                        <td class="py-4 px-6 text-right text-gray-500 italic">—</td>
                        {{-- ESTADO --}}
                        <td class="py-4 px-6 text-center">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800">
                                <i class="fas fa-exclamation-circle mr-1"></i>Sin Contrato
                            </span>
                        </td>
                        {{-- ACCIONES --}}
                        <td class="py-4 px-6 text-center">
                            <button 
                                @click="openEditModal('{{ $usuario->doc }}')"
                                class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded text-xs font-semibold transition duration-200 inline-flex items-center gap-1"
                            >
                                <i class="fas fa-edit"></i>Editar
                            </button>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 px-6">
                            <div class="text-center">
                                <i class="fas fa-inbox text-6xl text-gray-300 mb-4 block"></i>
                                <p class="text-gray-600 font-semibold mb-4">No hay empleados registrados</p>
                                <button @click="openRegistroModal()" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg font-semibold transition duration-200 inline-flex items-center gap-2">
                                    <i class="fas fa-plus"></i>Crear nuevo empleado
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="bg-gray-50 px-6 py-6 border-t border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-600">
                    Mostrando <span class="font-bold text-gray-900">{{ $empleados->count() }}</span> de 
                    <span class="font-bold text-gray-900">{{ $empleados->total() }}</span> empleado(s)
                </p>
                <div class="w-full md:w-auto">
                    <div class="flex justify-center">
                        {{ $empleados->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BOTÓN FLOTANTE --}}
    <button 
        @click="openRegistroModal()"
        class="fixed bottom-8 right-8 z-40 bg-green-500 hover:bg-green-600 text-white rounded-full w-14 h-14 flex items-center justify-center text-3xl font-bold shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 active:scale-95"
        title="Crear nuevo empleado"
    >
        <span>+</span>
    </button>

    {{-- MODAL REGISTRO DE EMPLEADO (WIZARD) --}}
    <div
        x-show="showRegistroModal"
        x-cloak
        @keydown.escape="closeModals()"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        style="display: none;"
    >
        <div
            @click.outside="closeModals()"
            class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex overflow-hidden"
        >
            {{-- SIDEBAR --}}
            <div class="w-full md:w-1/3 bg-gradient-to-b from-[#1565C0] to-[#0D47A1] text-white p-8 md:p-10 flex flex-col justify-center">
                <h2 class="text-3xl font-bold">Nomitech</h2>
                <p class="mt-4 text-sm text-blue-100 leading-relaxed">
                    Completa los tres pasos para registrar un nuevo empleado en el sistema.
                </p>

                <div class="mt-8 space-y-4">
                    {{-- PASO 1 --}}
                    <div class="flex items-center" :class="wizardStep >= 1 ? 'opacity-100' : 'opacity-50'">
                        <div :class="wizardStep >= 1 ? 'bg-green-400' : 'bg-blue-300'" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                            <i :class="wizardStep > 1 ? 'fas fa-check' : ''" x-show="wizardStep > 1"></i>
                            <span x-show="wizardStep <= 1">1</span>
                        </div>
                        <span class="font-medium">Datos Personales</span>
                    </div>

                    {{-- PASO 2 --}}
                    <div class="flex items-center" :class="wizardStep >= 2 ? 'opacity-100' : 'opacity-50'">
                        <div :class="wizardStep >= 2 ? 'bg-green-400' : 'bg-blue-300'" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                            <i :class="wizardStep > 2 ? 'fas fa-check' : ''" x-show="wizardStep > 2"></i>
                            <span x-show="wizardStep <= 2">2</span>
                        </div>
                        <span class="font-medium">Datos Laborales</span>
                    </div>

                    {{-- PASO 3 --}}
                    <div class="flex items-center" :class="wizardStep >= 3 ? 'opacity-100' : 'opacity-50'">
                        <div :class="wizardStep >= 3 ? 'bg-green-400' : 'bg-blue-300'" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                            <i :class="wizardStep > 3 ? 'fas fa-check' : ''" x-show="wizardStep > 3"></i>
                            <span x-show="wizardStep <= 3">3</span>
                        </div>
                        <span class="font-medium">Datos Financieros</span>
                    </div>
                </div>
            </div>

            {{-- CONTENIDO --}}
            <div class="w-full md:w-2/3 p-8 md:p-10 overflow-y-auto">
                {{-- PASO 1 --}}
                <div x-show="wizardStep === 1" x-cloak class="space-y-4">
                    @include('empleados.partials.inf_empleado')
                </div>

                {{-- PASO 2 --}}
                <div x-show="wizardStep === 2" x-cloak class="space-y-4">
                    @include('empleados.partials.inf_contractual')
                </div>

                {{-- PASO 3 --}}
                <div x-show="wizardStep === 3" x-cloak class="space-y-4">
                    @include('empleados.partials.inf_financiera')
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR EMPLEADO --}}
    @include('empleados.partials.modal_edit')

</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function getEmpleadosModuleData() {
        const modalDiv = document.querySelector('[x-data*="empleadosModule"]');
        if (!modalDiv) {
            return null;
        }

        if (modalDiv.__x && modalDiv.__x.$data) {
            return modalDiv.__x.$data;
        }

        if (Array.isArray(modalDiv._x_dataStack) && modalDiv._x_dataStack.length > 0) {
            return modalDiv._x_dataStack[0];
        }

        return null;
    }

    function resetEmployeeWizardForms() {
        ['#step1', '#step2', '#step3'].forEach(function (selector) {
            const form = $(selector);
            if (!form.length) {
                return;
            }

            form.trigger('reset');
            form.find('.error-message').addClass('hidden').text('');
            form.find('.invalid-feedback').remove();
            form.find('.is-invalid, .border-red-500').removeClass('is-invalid border-red-500');
        });

        $('#step1').show();
        $('#step2, #step3').hide();
    }

    window.goToWizardStep = function (step) {
        const moduleData = getEmpleadosModuleData();
        if (moduleData) {
            moduleData.wizardStep = step;
        }

        if (step === 1) {
            $('#step1').show();
            $('#step2, #step3').hide();
        } else if (step === 2) {
            $('#step1').hide();
            $('#step2').show();
            $('#step3').hide();
        } else if (step === 3) {
            $('#step1, #step2').hide();
            $('#step3').show();
        }
    };

    function clearEmployeeWizardSession() {
        $.ajax({
            url: "{{ route('employees.clear-session') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            }
        });
    }

    // Función para el módulo de empleados
    function empleadosModule() {
        return {
            showRegistroModal: false,
            showEditModal: false,
            wizardStep: 1,
            currentDoc: null,
            
            openRegistroModal() {
                this.wizardStep = 1;
                this.showRegistroModal = true;
                resetEmployeeWizardForms();
            },
            
            openEditModal(doc) {
                this.currentDoc = doc;
                this.showEditModal = true;
                // Aquí cargamos los datos del empleado si es necesario
            },
            
            closeModals() {
                this.showRegistroModal = false;
                this.showEditModal = false;
                this.wizardStep = 1;
                this.currentDoc = null;

                resetEmployeeWizardForms();
                clearEmployeeWizardSession();
            },
            
            nextStep() {
                if (this.wizardStep < 3) {
                    this.wizardStep++;
                }
            },
            
            previousStep() {
                if (this.wizardStep > 1) {
                    this.wizardStep--;
                }
            },
            
            completedWizard() {
                this.closeModals();
                // Recargar la página para ver el nuevo empleado
                window.location.reload();
            }
        };
    }

    // Mostrar alerta de éxito si existe en la sesión
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: '{{ session('error') }}',
            timer: 6000,
            timerProgressBar: true,
            position: 'top-end',
            toast: true
        });
    @endif

    // Escuchar eventos de cambio de paso desde los formularios
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar step1-success
        window.addEventListener('step1-success', function() {
            window.goToWizardStep(2);
        });

        // Manejar step2-success
        window.addEventListener('step2-success', function() {
            window.goToWizardStep(3);
        });

        // Manejar step3-success (finalizar wizard)
        window.addEventListener('step3-success', function() {
            const moduleData = getEmpleadosModuleData();
            if (moduleData && typeof moduleData.completedWizard === 'function') {
                moduleData.completedWizard();
            } else {
                resetEmployeeWizardForms();
                window.location.reload();
            }
        });

    });
</script>
@endsection
