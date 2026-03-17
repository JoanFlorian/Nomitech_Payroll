@extends('layouts.app')

@section('title', 'Empleados')
@section('page-title', 'EMPLEADOS')

@section('content')

<div x-data="empleadosModule()" @open-modal-registro.window="openRegistroModal()" @close-modal.window="closeModals()">
    @if ($canView)
    <div class="employee-index-compact p-6">
        {{-- ENCABEZADO --}}
        <div class="mb-8">
            <div class="flex justify-between items-center mb-5">
                <h1 class="text-3xl font-bold text-gray-900">Gestor de Empleados</h1>
                @can('export_employees')
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-2 py-1">
                    @php
                        $searchParam = request('search');
                        $baseParams = $searchParam ? ['search' => $searchParam] : [];
                        $exportGeneralParams = [];
                        $exportGeneralParams = $baseParams;
                        $exportActivosParams = array_merge($baseParams, ['estado' => 'activos']);
                        $exportInactivosParams = array_merge($baseParams, ['estado' => 'inactivos']);
                        $exportSinContratoParams = array_merge($baseParams, ['estado' => 'sin_contrato']);
                        $exportOptions = [
                            'General (todos) - Excel' => route('employees.export.excel', $exportGeneralParams),
                            'General (todos) - PDF' => route('employees.export.pdf', $exportGeneralParams),
                            'Solo activos - Excel' => route('employees.export.excel', $exportActivosParams),
                            'Solo activos - PDF' => route('employees.export.pdf', $exportActivosParams),
                            'Solo inactivos - Excel' => route('employees.export.excel', $exportInactivosParams),
                            'Solo inactivos - PDF' => route('employees.export.pdf', $exportInactivosParams),
                            'Sin contrato - Excel' => route('employees.export.excel', $exportSinContratoParams),
                            'Sin contrato - PDF' => route('employees.export.pdf', $exportSinContratoParams),
                        ];
                    @endphp
                    <select id="exportEmployeesSelect"
                        class="min-w-[260px] border border-gray-300 rounded-md pl-3 pr-10 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach ($exportOptions as $label => $url)
                            <option value="{{ $url }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="button"
                        onclick="window.location.href = document.getElementById('exportEmployeesSelect').value"
                        class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-sm font-semibold transition">
                        <i class="fas fa-download mr-2"></i>Exportar
                    </button>
                </div>
                @endcan
            </div>

            {{-- BÚSQUEDA Y FILTROS --}}
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                {{-- BARRA DE BÚSQUEDA --}}
                <form method="GET" action="{{ route('empleados.index') }}" class="mb-4 -mt-2">
                    <div class="flex flex-col md:flex-row gap-3">
                        <div class="flex-1">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}"
                                placeholder="Buscar por nombre, documento o correo..."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                            >
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition duration-200">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                    </div>
                </form>

                {{-- FILTROS POR ESTADO --}}
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('empleados.index') }}" 
                    class="px-4 py-1.5 rounded-full text-sm font-semibold transition duration-200 {{ !request('estado') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                        <i class="fas fa-list mr-2"></i>Todos ({{ $totalEmpleados }})
                    </a>
                    <a href="{{ route('empleados.index', ['estado' => 'activos']) }}" 
                    class="px-4 py-1.5 rounded-full text-sm font-semibold transition duration-200 {{ request('estado') === 'activos' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                        <i class="fas fa-check-circle mr-2"></i>Activos ({{ $activosCount }})
                    </a>
                    <a href="{{ route('empleados.index', ['estado' => 'inactivos']) }}" 
                    class="px-4 py-1.5 rounded-full text-sm font-semibold transition duration-200 {{ request('estado') === 'inactivos' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                        <i class="fas fa-times-circle mr-2"></i>Inactivos ({{ $inactivosCount }})
                    </a>
                    <a href="{{ route('empleados.index', ['estado' => 'sin_contrato']) }}" 
                    class="px-4 py-1.5 rounded-full text-sm font-semibold transition duration-200 {{ request('estado') === 'sin_contrato' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
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
                                    <p class="font-semibold text-gray-900">{{ \Illuminate\Support\Str::title(trim(($usuario->primer_nombre ?? '') . ' ' . ($usuario->primer_apellido ?? ''))) }}</p>
                                    <p class="text-xs text-gray-500">{{ \Illuminate\Support\Str::title(trim(($usuario->otros_nombres ?? '') . ' ' . ($usuario->segundo_apellido ?? ''))) }}</p>
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
                            @php
                                $estadoDinamico = $contrato->estado_dinamico ?? $contrato->estado ?? 'ACTIVO';
                            @endphp
                            @switch($estadoDinamico)
                                @case('PROGRAMADO')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800">
                                        <i class="fas fa-calendar-alt mr-1"></i>Programado
                                    </span>
                                    @break
                                @case('ACTIVO')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Activo
                                    </span>
                                    @break
                                @case('POR_VENCER')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        <i class="fas fa-clock mr-1"></i>Por Vencer
                                    </span>
                                    @break
                                @case('VENCIDO')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Vencido
                                    </span>
                                    @break
                                @case('TERMINADO')
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-600">
                                        <i class="fas fa-ban mr-1"></i>Terminado
                                    </span>
                                    @break
                                @default
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                        {{ $estadoDinamico }}
                                    </span>
                            @endswitch
                        </td>
                        {{-- ACCIONES --}}
                        <td class="py-4 px-6 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @can('edit_employee')
                                <button 
                                    @click="openEditModal('{{ $usuario->doc }}')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded text-xs font-semibold transition duration-200 inline-flex items-center gap-1"
                                >
                                    <i class="fas fa-edit"></i>Editar
                                </button>
                                @endcan

                                @if(in_array($estadoDinamico ?? '', ['POR_VENCER', 'VENCIDO']))
                                @can('renew_contract')
                                <button 
                                    @click="openRenewalModal('{{ $usuario->doc }}')"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white py-2 px-3 rounded text-xs font-semibold transition duration-200 inline-flex items-center gap-1"
                                >
                                    <i class="fas fa-sync-alt"></i>Renovar
                                </button>
                                @endcan
                                @endif
                            </div>
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
                                    <p class="font-semibold text-gray-900">{{ \Illuminate\Support\Str::title(trim(($usuario->primer_nombre ?? '') . ' ' . ($usuario->primer_apellido ?? ''))) }}</p>
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
                            @can('edit_employee')
                            <button 
                                @click="openEditModal('{{ $usuario->doc }}')"
                                class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded text-xs font-semibold transition duration-200 inline-flex items-center gap-1"
                            >
                                <i class="fas fa-edit"></i>Editar
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 px-6">
                            <div class="text-center">
                                <i class="fas fa-inbox text-6xl text-gray-300 mb-4 block"></i>
                                <p class="text-gray-600 font-semibold mb-4">No hay empleados registrados</p>
                                @can('create_employee')
                                <button @click="openRegistroModal()" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg font-semibold transition duration-200 inline-flex items-center gap-2">
                                    <i class="fas fa-plus"></i>Crear nuevo empleado
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="bg-gray-50 px-6 py-6 border-t border-gray-200">
            <div class="flex flex-col items-center gap-3 md:relative">
                <p class="text-sm text-gray-600 self-start text-left md:absolute md:left-0 md:top-1/2 md:-translate-y-1/2">
                    Mostrando <span class="font-bold text-gray-900">{{ $empleados->count() }}</span> de 
                    <span class="font-bold text-gray-900">{{ $empleados->total() }}</span> empleado(s)
                </p>
                <div class="w-full">
                    <div class="flex justify-center">
                        {{ $empleados->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BOTÓN FLOTANTE --}}
    @can('create_employee')
    <button 
        @click="openRegistroModal()"
        class="fixed bottom-2 right-5 z-40 bg-green-500 hover:bg-green-600 text-white rounded-full w-14 h-14 flex items-center justify-center text-3xl font-bold shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 active:scale-95"
        title="Crear nuevo empleado"
    >
        <span>+</span>
    </button>
    @endcan

    </div> {{-- .employee-index-compact --}}
    @else
        <div class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100 m-6">
            <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mb-6">
                <i class="bi bi-shield-lock text-4xl text-amber-500"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2 font-manrope">Acceso Restringido</h2>
            <p class="text-gray-500 text-center max-w-md font-manrope">No tienes los permisos necesarios para visualizar la lista de empleados. <br> Contacta al administrador si crees que esto es un error.</p>
        </div>
    @endif

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

    {{-- MODAL EDITAR / RENOVAR EMPLEADO --}}
    @include('empleados.partials.modal_edit')

    <style>
        .employee-index-compact {
            transform: scale(0.95);
            transform-origin: top left;
        }
    </style>
</div> {{-- x-data --}}

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('dismissedAlerts', []);
    });

    window.dismissAlert = function(type) {
        let current = Alpine.store('dismissedAlerts') || [];
        if (!current.includes(type)) {
            Alpine.store('dismissedAlerts', [...current, type]);
        }
    };

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
        ['step1', 'step2', 'step3'].forEach(function (id) {
            const form = document.getElementById(id);
            if (!form) {
                return;
            }

            form.reset();
            form.querySelectorAll('.error-message').forEach(function (errorNode) {
                errorNode.textContent = '';
                errorNode.classList.remove('d-block');
            });

            form.querySelectorAll('.is-invalid, .border-red-500').forEach(function (node) {
                node.classList.remove('is-invalid', 'border-red-500');
            });
        });

        if (window.employeeValidation && typeof window.employeeValidation.refreshAll === 'function') {
            window.employeeValidation.refreshAll();
        }
    }

    function collectFormState(form) {
        if (!form) {
            return {};
        }

        const state = {};
        form.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (field) {
            const name = field.getAttribute('name');
            if (!name || name === '_token') {
                return;
            }

            if (field.type === 'checkbox') {
                state[name] = field.checked;
                return;
            }

            state[name] = field.value;
        });

        return state;
    }

    function applyFormState(form, state) {
        if (!form || !state || typeof state !== 'object') {
            return;
        }

        Object.entries(state).forEach(function ([name, value]) {
            const field = form.querySelector(`[name="${name}"]`);
            if (!field) {
                return;
            }

            if (field.type === 'checkbox') {
                field.checked = Boolean(value);
                field.dispatchEvent(new Event('change', { bubbles: true }));
                return;
            }

            field.value = value ?? '';
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    function captureRegistroDraft(moduleData) {
        if (!moduleData) {
            return;
        }

        moduleData.registroDraft = {
            wizardStep: Number(moduleData.wizardStep || 1),
            step1: collectFormState(document.getElementById('step1')),
            step2: collectFormState(document.getElementById('step2')),
            step3: collectFormState(document.getElementById('step3')),
        };
    }

    function clearRegistroDraft(moduleData) {
        if (!moduleData) {
            return;
        }

        moduleData.registroDraft = null;
    }

    function restoreRegistroDraft(moduleData) {
        if (!moduleData || !moduleData.registroDraft) {
            return false;
        }

        applyFormState(document.getElementById('step1'), moduleData.registroDraft.step1);
        applyFormState(document.getElementById('step2'), moduleData.registroDraft.step2);
        applyFormState(document.getElementById('step3'), moduleData.registroDraft.step3);

        moduleData.wizardStep = Math.min(3, Math.max(1, Number(moduleData.registroDraft.wizardStep || 1)));

        if (window.employeeValidation && typeof window.employeeValidation.refreshAll === 'function') {
            window.employeeValidation.refreshAll();
        }

        return true;
    }

    function captureEditDraft(moduleData) {
        if (!moduleData || !moduleData.currentDoc) {
            return;
        }

        const form = document.getElementById('editEmployeeForm');
        if (!form) {
            return;
        }

        moduleData.editDrafts[moduleData.currentDoc] = {
            editWizardStep: Number(moduleData.editWizardStep || 1),
            fields: collectFormState(form),
        };
    }

    function clearEditDraft(moduleData, doc = null) {
        if (!moduleData || !moduleData.editDrafts) {
            return;
        }

        const targetDoc = doc || moduleData.currentDoc;
        if (!targetDoc) {
            return;
        }

        delete moduleData.editDrafts[targetDoc];
    }

    window.goToWizardStep = function (step) {
        const moduleData = getEmpleadosModuleData();
        if (moduleData) {
            moduleData.wizardStep = step;
        }

        if (window.employeeValidation && typeof window.employeeValidation.refreshAll === 'function') {
            window.employeeValidation.refreshAll();
        }
    };

    function clearEmployeeWizardSession() {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');

        fetch("{{ route('employees.clear-session') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        }).catch(() => {});
    }

    // Función para el módulo de empleados
    function empleadosModule() {
        return {
            showRegistroModal: false,
            showEditModal: false,
            isRenewal: false,
            wizardStep: 1,
            editWizardStep: 1,
            currentDoc: null,
            registroDraft: null,
            editDrafts: {},
            dismissedAlerts: [],
            renewalData: {
                doc: '',
                nombre: '',
                fecha_inicio_anterior: '',
                fecha_fin_anterior: '',
                salario_base: 0,
                id_tipo_contrato: '',
                horas_diarias: 8,
                nueva_fecha_inicio: '',
                nueva_fecha_fin: '',
                nuevo_salario: 0,
                continuityMessage: '',
                hasContinuity: false,
            },
            renewalLoading: false,
            
            openRegistroModal() {
                this.showRegistroModal = true;

                if (!restoreRegistroDraft(this)) {
                    this.wizardStep = 1;
                    resetEmployeeWizardForms();
                }
            },
            
            async openRenewalModal(doc) {
                this.currentDoc = doc;
                this.isRenewal = true;
                this.editWizardStep = 2; // Enfocar datos laborales
                this.showEditModal = true;

                if (typeof window.loadEmployee === 'function') {
                    // El modo renovación se encargará de sugerir fechas tras cargar
                    window.loadEmployee(doc, { isRenewal: true });
                }
            },


            
            openEditModal(doc) {
                this.currentDoc = doc;
                this.isRenewal = false;
                this.showEditModal = true;

                const draft = this.editDrafts[doc] || null;
                this.editWizardStep = draft ? Number(draft.editWizardStep || 1) : 1;

                if (typeof window.loadEmployee === 'function') {
                    window.loadEmployee(doc);
                }
            },
            
            closeModals(options = {}) {
                if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                    return;
                }

                const discardProgress = Boolean(options && options.discardProgress);

                if (this.showRegistroModal) {
                    if (discardProgress) {
                        clearRegistroDraft(this);
                        this.wizardStep = 1;
                        resetEmployeeWizardForms();
                        clearEmployeeWizardSession();
                    } else {
                        captureRegistroDraft(this);
                    }
                }

                if (this.showEditModal) {
                    if (discardProgress) {
                        clearEditDraft(this);
                    } else {
                        captureEditDraft(this);
                    }
                }

                this.showRegistroModal = false;
                this.showEditModal = false;
                this.isRenewal = false;
                this.editWizardStep = 1;
                this.currentDoc = null;
            },

            dismissAlert(type) {
                window.dismissAlert(type);
            },
            
            nextStep() {
                if (this.wizardStep < 3) {
                    window.goToWizardStep(this.wizardStep + 1);
                }
            },
            
            previousStep() {
                if (this.wizardStep > 1) {
                    window.goToWizardStep(this.wizardStep - 1);
                }
            },

            nextEditStep() {
                if (this.editWizardStep < 3) {
                    if (typeof window.validateEditStepByNumber === 'function' && !window.validateEditStepByNumber(this.editWizardStep, true)) {
                        if (typeof window.showEditValidationAlert === 'function') {
                            window.showEditValidationAlert('No puedes continuar hasta corregir los errores del formulario.');
                        }
                        return;
                    }
                    this.editWizardStep++;
                }
            },

            previousEditStep() {
                if (this.editWizardStep > 1) {
                    this.editWizardStep--;
                }
            },
            
            completedWizard() {
                this.closeModals({ discardProgress: true });
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

    window.employeeValidationRules = {
        smmlv: Number(@json((float) config('nomina.salario_minimo', config('nomina.smmlv', 0))))
    };
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/employee-validation.js') }}"></script>
@endpush
