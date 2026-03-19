{{-- MODAL EDITAR EMPLEADO --}}
<div x-show="showEditModal" x-cloak @keydown.escape="closeModals()"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" style="display: none;">
    <div @click.outside="closeModals()"
        class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex overflow-hidden">
        {{-- SIDEBAR --}}
        <div
            class="w-full md:w-1/3 bg-gradient-to-b from-[#1565C0] to-[#0D47A1] text-white p-8 md:p-10 flex flex-col justify-center">
            <h2 class="text-3xl font-bold">Nomitech</h2>
            <p class="mt-4 text-sm text-blue-100 leading-relaxed">
                Actualiza los datos del empleado en tres pasos.
            </p>

            <div class="mt-8 space-y-4">
                <div class="flex items-center" :class="editWizardStep >= 1 ? 'opacity-100' : 'opacity-50'">
                    <div :class="editWizardStep >= 1 ? 'bg-green-400' : 'bg-blue-300'"
                        class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                        <i :class="editWizardStep > 1 ? 'fas fa-check' : ''" x-show="editWizardStep > 1"></i>
                        <span x-show="editWizardStep <= 1">1</span>
                    </div>
                    <span class="font-medium">Datos Personales</span>
                </div>

                <div class="flex items-center" :class="editWizardStep >= 2 ? 'opacity-100' : 'opacity-50'">
                    <div :class="editWizardStep >= 2 ? 'bg-green-400' : 'bg-blue-300'"
                        class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                        <i :class="editWizardStep > 2 ? 'fas fa-check' : ''" x-show="editWizardStep > 2"></i>
                        <span x-show="editWizardStep <= 2">2</span>
                    </div>
                    <span class="font-medium">Datos Laborales</span>
                </div>

                <div class="flex items-center" :class="editWizardStep >= 3 ? 'opacity-100' : 'opacity-50'">
                    <div :class="editWizardStep >= 3 ? 'bg-green-400' : 'bg-blue-300'"
                        class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                        <i :class="editWizardStep > 3 ? 'fas fa-check' : ''" x-show="editWizardStep > 3"></i>
                        <span x-show="editWizardStep <= 3">3</span>
                    </div>
                    <span class="font-medium">Datos Financieros</span>
                </div>
            </div>
        </div>

        {{-- CONTENIDO --}}
        <div class="w-full md:w-2/3 p-8 md:p-10 overflow-y-auto">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-2xl font-bold text-gray-800" x-text="isRenewal ? 'Renovación de Contrato' : 'Editar Empleado'"></h2>
                <button @click="closeModals()" class="text-gray-500 hover:text-gray-800 text-2xl">
                    &times;
                </button>
            </div>

            {{-- BANNER DE INFORMACIÓN PARA RENOVACIÓN --}}
            <template x-if="isRenewal">
                <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-emerald-600 mt-0.5"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-emerald-800 font-medium">
                                Modo Renovación Activo
                            </p>
                            <p class="text-xs text-emerald-700 mt-1">
                                Estás creando un nuevo contrato para este empleado. Toda la información ha sido precargada; verifica y ajusta las fechas y el salario para el nuevo periodo.
                            </p>
                        </div>
                    </div>
                </div>
            </template>

            <div class="mb-10">
                <div class="flex justify-between text-xs mb-2">
                    <span :class="editWizardStep >= 1 ? 'font-semibold text-[rgb(16,185,129)]' : 'text-gray-500'">
                        Información Personal
                    </span>
                    <span :class="editWizardStep >= 2 ? 'font-semibold text-[rgb(16,185,129)]' : 'text-gray-500'">
                        Información Contractual y Laboral
                    </span>
                    <span class="text-right"
                        :class="editWizardStep >= 3 ? 'font-semibold text-[rgb(16,185,129)]' : 'text-gray-500'">
                        Información Financiera y Seguridad Social
                    </span>
                </div>

                <div class="relative h-1 bg-gray-200 rounded-full">
                    <div class="absolute h-1 bg-[rgb(16,185,129)] rounded-full" :class="{
                            'w-1/3': editWizardStep === 1,
                            'w-2/3': editWizardStep === 2,
                            'w-full': editWizardStep === 3
                        }"></div>

                    <div class="absolute -top-3 left-0 w-full flex justify-between">
                        <div :class="editWizardStep >= 1 ? 'bg-[rgb(16,185,129)] text-white' : 'bg-gray-300 text-gray-600'"
                            class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            1
                        </div>

                        <div :class="editWizardStep >= 2 ? 'bg-[rgb(16,185,129)] text-white' : 'bg-gray-300 text-gray-600'"
                            class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            2
                        </div>

                        <div :class="editWizardStep >= 3 ? 'bg-[rgb(16,185,129)] text-white' : 'bg-gray-300 text-gray-600'"
                            class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            3
                        </div>
                    </div>
                </div>
            </div>

            <form id="editEmployeeForm" novalidate>
                @csrf
                <input type="hidden" id="editDocField" name="doc">

                <div x-show="editWizardStep === 1" x-cloak>
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Datos Personales</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Documento</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_tipo_doc" id="editIdTipoDoc">
                                @foreach ($tipodoc as $tipo)
                                    <option value="{{ $tipo->id_tipo_doc }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_doc"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de Documento</label>
                            <input type="number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed sm:text-sm"
                                name="numero_documento" id="editNumeroDoc" readonly>
                            <p class="error-message text-red-500 text-sm hidden" data-error="numero_documento"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primer Nombre</label>
                            <input type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] capitalize sm:text-sm"
                                name="primer_nombre" id="editPrimerNombre" minlength="3" maxlength="30"
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                            <p class="error-message text-red-500 text-sm hidden" data-error="primer_nombre"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Otros Nombres</label>
                            <input type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] capitalize sm:text-sm"
                                name="otros_nombres" id="editOtrosNombres" minlength="3" maxlength="50"
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                            <p class="error-message text-red-500 text-sm hidden" data-error="otros_nombres"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primer Apellido</label>
                            <input type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] capitalize sm:text-sm"
                                name="primer_apellido" id="editPrimerApellido" minlength="3" maxlength="30"
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                            <p class="error-message text-red-500 text-sm hidden" data-error="primer_apellido"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Segundo Apellido</label>
                            <input type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] capitalize sm:text-sm"
                                name="segundo_apellido" id="editSegundoApellido" minlength="3" maxlength="30"
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                            <p class="error-message text-red-500 text-sm hidden" data-error="segundo_apellido"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                            <x-form.searchable-select name="id_departamento" id="editIdDepartamento" icon="location_on" placeholder="Departamento"
                                :options="$departamento->pluck('nombre', 'id_departamento')" />
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_departamento"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                            <x-form.searchable-select name="id_ciudad" id="editIdCiudad" icon="location_city" placeholder="Ciudad / Municipio"
                                :options="$ciudad->pluck('nombre', 'id_ciudad')" />
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_ciudad"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <input type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] capitalize sm:text-sm"
                                name="direccion" id="editDireccion" maxlength="150"
                                title="Incluye referencia vial (Calle, Carrera, Cra, Cl, Av, Transversal, Diagonal, # o No).">
                            <p class="error-message text-red-500 text-sm hidden" data-error="direccion"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                            <input type="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="email" id="editEmail" required>
                            <p class="error-message text-red-500 text-sm hidden" data-error="email"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="telefono" id="editTelefono" required>
                            <p class="error-message text-red-500 text-sm hidden" data-error="telefono"></p>
                        </div>
                    </div>
                </div>

                <div x-show="editWizardStep === 2" x-cloak>
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Datos Laborales</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Trabajador</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_tipo_trabajador" id="editIdTipoTrabajador">
                                @foreach ($tipotrabajadores as $tipo)
                                    <option value="{{ $tipo->id_tipo_trabajador }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_trabajador"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subtipo de Trabajador</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_sub_tipo_trabajador" id="editIdSubTipoTrabajador">
                                @foreach ($suptrabajadores as $sub)
                                    <option value="{{ $sub->id_sub_tipo_trabajador }}">{{ $sub->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_sub_tipo_trabajador">
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Contrato</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_tipo_contrato" id="editIdTipoContrato">
                                @foreach ($contratos as $contrato)
                                    <option value="{{ $contrato->id_tipo_contrato }}">{{ $contrato->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_contrato"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio</label>
                            <input type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                :class="isRenewal ? 'ring-2 ring-emerald-500 border-emerald-500 bg-emerald-50/30' : ''"
                                name="fecha_inicio" id="editFechaInicio">
                            <p class="error-message text-red-500 text-sm hidden" data-error="fecha_inicio"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Fin</label>
                            <input type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                :class="isRenewal ? 'ring-2 ring-emerald-500 border-emerald-500 bg-emerald-50/30' : ''"
                                name="fecha_fin" id="editFechaFin">
                            <p id="editFechaFinHint" class="text-xs text-gray-500 mt-1">Debe ser posterior a la fecha de
                                inicio.</p>
                            <p class="error-message text-red-500 text-sm hidden" data-error="fecha_fin"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ARL</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_arl" id="editIdArl" disabled>
                                @foreach ($Arl as $arl)
                                    <option value="{{ $arl->id_arl }}">{{ $arl->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_arl"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Horas Diarias</label>
                            <input type="number" min="1" max="12"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="horas_diarias" id="editHorasDiarias">
                            <p class="error-message text-red-500 text-sm hidden" data-error="horas_diarias"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salario Base</label>
                            <input type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                :class="!isRenewal ? 'bg-gray-100 cursor-not-allowed text-gray-500' : 'bg-white'"
                                name="salario"
                                id="editSalario"
                                :disabled="!isRenewal"
                                inputmode="decimal"
                                autocomplete="off"
                                placeholder="Ej: 2.000.000,00">
                            <p class="error-message text-red-500 text-sm hidden" data-error="salario"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rol del sistema</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_rol" id="editIdRol">
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id_rol }}">{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_rol"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código Interno</label>
                            <input type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="codigo_interno" id="editCodigoInterno" inputmode="numeric" minlength="3"
                                maxlength="20" pattern="[0-9]+">
                            <p class="error-message text-red-500 text-sm hidden" data-error="codigo_interno"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nivel de Riesgo</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="nivel_riesgo_id" id="editNivelRiesgo">
                                <option value="">Seleccionar...</option>
                                @foreach($niveles as $nivel)
                                    <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="nivel_riesgo_id"></p>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox"
                                class="h-4 w-4 text-[#1565C0] border-gray-300 rounded focus:ring-[#1565C0]"
                                name="alto_riesgo" id="editAltoRiesgo">
                            <label for="editAltoRiesgo" class="ml-2 block text-sm text-gray-700">
                                Trabajador de alto riesgo
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox"
                                class="h-4 w-4 text-[#1565C0] border-gray-300 rounded focus:ring-[#1565C0]"
                                name="bajo_riesgo" id="editBajoRiesgo">
                            <label for="editBajoRiesgo" class="ml-2 block text-sm text-gray-700">
                                Trabajador de bajo riesgo
                            </label>
                        </div>
                    </div>
                </div>

                <div x-show="editWizardStep === 3" x-cloak>
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Datos Financieros y Seguridad Social
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pago</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_forma_pago" id="editIdFormaPago">
                                @foreach ($formapagos as $forma)
                                    <option value="{{ $forma->id_forma_pago }}">{{ $forma->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_forma_pago"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_metodo_pago" id="editIdMetodoPago">
                                @foreach ($metodopago as $metodo)
                                    <option value="{{ $metodo->id_metodo_pago }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_metodo_pago"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cuenta</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="tipo_cuenta" id="editTipoCuenta">
                                @foreach ($tipocuenta as $tipo)
                                    <option value="{{ $tipo->id_tipo_cuenta }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="tipo_cuenta"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de Cuenta</label>
                            <input type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="numero_cuenta" id="editNumeroCuenta" inputmode="numeric" minlength="6"
                                maxlength="20" pattern="[0-9]{6,20}">
                            <p class="error-message text-red-500 text-sm hidden" data-error="numero_cuenta"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">EPS</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_eps" id="editIdEps" disabled>
                                @foreach ($Eps as $eps)
                                    <option value="{{ $eps->id_eps }}">{{ $eps->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_eps"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">AFP</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_afp" id="editIdAfp" disabled>
                                @foreach ($Afp as $afp)
                                    <option value="{{ $afp->id_afp }}">{{ $afp->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_afp"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Caja de Compensación</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="id_caja" id="editIdCaja">
                                <option value="">Seleccionar...</option>
                                @foreach ($Cajas as $caja)
                                    <option value="{{ $caja->id_caja }}">{{ $caja->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_caja"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fondo de Cesantías</label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                name="fondo_cesantias" id="editFondoCesantias">
                                <option value="">Seleccionar (Opcional)...</option>
                                <option value="Protección">Protección</option>
                                <option value="Porvenir">Porvenir</option>
                                <option value="Colfondos">Colfondos</option>
                                <option value="FNA">Fondo Nacional del Ahorro (FNA)</option>
                                <option value="Skandia">Skandia</option>
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="fondo_cesantias"></p>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox"
                                class="h-4 w-4 text-[#1565C0] border-gray-300 rounded focus:ring-[#1565C0]"
                                name="activo" id="editActivo">
                            <label for="editActivo" class="ml-2 block text-sm text-gray-700">
                                Empleado activo
                            </label>
                        </div>
                    </div>

                    <!-- Configuración de saldos iniciales (Migración) -->
                        <div class="mt-8 border-t pt-8">
                            <div class="mb-6">
                                <h4 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                    <i class="fas fa-history text-[#1565C0]"></i>
                                    Continuidad de Provisiones Anteriores
                                </h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    Use estos campos <strong>únicamente</strong> si el empleado ya tiene saldos acumulados por provisiones que la empresa realizó previamente fuera de este sistema.
                                </p>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 ring-1 ring-blue-100 shadow-sm">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Prima inicial</label>
                                        <input type="text" id="editPrimaInicial" name="prima_inicial" placeholder="0" 
                                            class="migration-input-edit w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                                                focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                            data-type="money">
                                        <div class="error-message text-xs text-red-500 mt-1 hidden" data-error="prima_inicial"></div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Cesantías inicial</label>
                                        <input type="text" id="editCesantiasInicial" name="cesantias_inicial" placeholder="0" 
                                            class="migration-input-edit w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                                                focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                            data-type="money">
                                        <div class="error-message text-xs text-red-500 mt-1 hidden" data-error="cesantias_inicial"></div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Intereses cesantías inicial</label>
                                        <input type="text" id="editInteresesInicial" name="intereses_inicial" placeholder="0" 
                                            class="migration-input-edit w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                                                focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                            data-type="money">
                                        <div class="error-message text-xs text-red-500 mt-1 hidden" data-error="intereses_inicial"></div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Vacaciones inicial (en días)</label>
                                        <input type="text" id="editVacacionesInicial" name="vacaciones_inicial" placeholder="Ej: 15" 
                                            class="migration-input-edit w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                                                focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                                            data-type="days">
                                        <div class="error-message text-xs text-red-500 mt-1 hidden" data-error="vacaciones_inicial"></div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 italic mt-4">
                                    Nota: Estos valores solo deben ingresarse si el empleado tiene saldos pendientes de periodos no liquidados en este sistema.
                                </p>
                            </div>
                    </div>
                </div>

                <div class="mt-10 flex justify-between gap-4">
                    <button type="button" @click="closeModals({ discardProgress: true })"
                        class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition">
                        Cancelar
                    </button>

                    <div class="flex gap-3">
                        <button type="button" x-show="editWizardStep > 1" @click="previousEditStep()"
                            class="bg-white border border-gray-300 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-50 transition">
                            Atrás
                        </button>
                        <button type="button" x-show="editWizardStep < 3" @click="nextEditStep()"
                            class="bg-[#1565C0] text-white py-2 px-6 rounded-md hover:bg-[#0D47A1] transition">
                            Continuar
                        </button>
                        @can('edit_employee')
                        <button type="submit" x-show="editWizardStep === 3 && !isRenewal"
                            class="bg-[#1565C0] text-white py-2 px-6 rounded-md hover:bg-[#0D47A1] transition">
                            Guardar Cambios
                        </button>
                        @endcan

                        @can('renew_contract')
                        <button type="submit" x-show="editWizardStep === 3 && isRenewal"
                            class="bg-[#1565C0] text-white py-2 px-6 rounded-md hover:bg-[#0D47A1] transition">
                            Finalizar Renovación
                        </button>
                        @endcan
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const EDIT_LETTERS_REGEX = /^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/;
    const EDIT_NUMBERS_REGEX = /^[0-9]+$/;
    const EDIT_ACCOUNT_REGEX = /^[0-9]{6,20}$/;
    const EDIT_ADDRESS_REGEX = /^(?=.*[A-Za-z])(?=.*(calle|carrera|cra\.?|cl\.?|av\.?|avenida|transversal|diagonal|#|no\.?)).+$/i;
    const EDIT_SMMLV = Number(@json((float) config('nomina.salario_minimo', config('nomina.smmlv', 0))));
    const EDIT_SALARY_FORMATTER = new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

    function parseEditLocalizedNumber(rawValue) {
        if (typeof rawValue === 'number') return rawValue;
        
        const value = (rawValue ?? '').toString().trim();
        if (value === '') {
            return NaN;
        }

        // Si contiene coma, o más de un punto, es definitivamente formato es-CO (1.234.567,89)
        const hasComma = value.includes(',');
        const dots = (value.match(/\./g) || []).length;

        if (hasComma || dots > 1) {
            const sanitized = value.replace(/\./g, '').replace(',', '.').replace(/[^\d.-]/g, '');
            return parseFloat(sanitized);
        }

        if (dots === 1) {
            // Un solo punto: ¿1.234 (mil) o 1234.56 (DB)?
            const parts = value.split('.');
            if (parts[1].length >= 3) {
                // Si el punto está seguido de 3 o más dígitos, lo tratamos como separador de miles.
                return parseFloat(value.replace(/\./g, '').replace(/[^\d.-]/g, ''));
            }
            // Si tiene menos de 3 dígitos después del punto, es un decimal crudo de BD
            return parseFloat(value.replace(/[^\d.-]/g, ''));
        }

        const sanitized = value.replace(/[^\d.-]/g, '');
        const parsed = parseFloat(sanitized);
        return Number.isFinite(parsed) ? parsed : NaN;
    }

    function formatEditLocalizedNumber(rawValue) {
        const numericValue = parseEditLocalizedNumber(rawValue);
        if (!Number.isFinite(numericValue)) {
            return '';
        }

        return EDIT_SALARY_FORMATTER.format(numericValue);
    }

    function resolveEditAprendizStage(form) {
        const etapaInput = form.querySelector('[name="etapa_aprendiz"]');
        const etapaValue = etapaInput ? (etapaInput.value ?? '').toString().trim().toLowerCase() : '';

        if (etapaValue.includes('lectiva')) {
            return 'lectiva';
        }

        if (etapaValue.includes('productiva')) {
            return 'productiva';
        }

        const tipoTrabajadorInput = form.querySelector('[name="id_tipo_trabajador"]');
        const tipoTrabajadorValue = tipoTrabajadorInput ? (tipoTrabajadorInput.value ?? '').toString().trim() : '';

        if (tipoTrabajadorValue === '12') {
            return 'lectiva';
        }

        if (tipoTrabajadorValue === '19') {
            return 'productiva';
        }

        const selectedText = tipoTrabajadorInput && tipoTrabajadorInput.selectedOptions && tipoTrabajadorInput.selectedOptions[0]
            ? (tipoTrabajadorInput.selectedOptions[0].textContent ?? '').toString().toLowerCase()
            : '';

        if (selectedText.includes('lectiva')) {
            return 'lectiva';
        }

        if (selectedText.includes('productiva')) {
            return 'productiva';
        }

        return null;
    }

    function updateEditFechaFinMin() {
        const fechaInicioInput = document.getElementById('editFechaInicio');
        const fechaFinInput = document.getElementById('editFechaFin');
        const tipoContratoInput = document.getElementById('editIdTipoContrato');

        if (!fechaFinInput) {
            return;
        }

        if (isEditIndefiniteContractSelected(tipoContratoInput)) {
            fechaFinInput.removeAttribute('min');
            return;
        }

        const fechaInicioValue = fechaInicioInput ? (fechaInicioInput.value ?? '').toString().trim() : '';

        if (!fechaInicioValue) {
            fechaFinInput.removeAttribute('min');
            return;
        }

        const nextDate = new Date(`${fechaInicioValue}T00:00:00`);
        nextDate.setDate(nextDate.getDate() + 1);
        const minFechaFin = nextDate.toISOString().split('T')[0];
        fechaFinInput.setAttribute('min', minFechaFin);

        const fechaFinValue = (fechaFinInput.value ?? '').toString().trim();
        if (fechaFinValue !== '' && fechaFinValue <= fechaInicioValue) {
            fechaFinInput.value = '';
            clearEditFieldError(fechaFinInput);
        }
    }

    function isEditIndefiniteContractSelected(tipoContratoInput) {
        if (!tipoContratoInput) {
            return false;
        }

        const selectedOption = tipoContratoInput.selectedOptions && tipoContratoInput.selectedOptions[0]
            ? tipoContratoInput.selectedOptions[0]
            : null;

        if (!selectedOption) {
            return false;
        }

        const optionText = (selectedOption.textContent ?? '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        return optionText.includes('indefinid');
    }

    function isEditFixedTermContractSelected(tipoContratoInput) {
        if (!tipoContratoInput) {
            return false;
        }

        const selectedOption = tipoContratoInput.selectedOptions && tipoContratoInput.selectedOptions[0]
            ? tipoContratoInput.selectedOptions[0]
            : null;

        if (!selectedOption) {
            return false;
        }

        const optionText = (selectedOption.textContent ?? '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        return optionText.includes('fijo');
    }

    function syncEditFechaFinByContractType() {
        const fechaFinInput = document.getElementById('editFechaFin');
        const fechaFinHint = document.getElementById('editFechaFinHint');
        const tipoContratoInput = document.getElementById('editIdTipoContrato');

        if (!fechaFinInput) {
            return;
        }

        const isIndefinite = isEditIndefiniteContractSelected(tipoContratoInput);

        if (isIndefinite) {
            fechaFinInput.value = '';
            fechaFinInput.setAttribute('disabled', 'disabled');
            fechaFinInput.classList.add('bg-gray-100', 'cursor-not-allowed');
            clearEditFieldError(fechaFinInput);

            if (fechaFinHint) {
                fechaFinHint.textContent = 'Contrato indefinido: no debe registrar fecha de fin.';
            }

            return;
        }

        fechaFinInput.removeAttribute('disabled');
        fechaFinInput.classList.remove('bg-gray-100', 'cursor-not-allowed');

        if (fechaFinHint) {
            fechaFinHint.textContent = isEditFixedTermContractSelected(tipoContratoInput)
                ? 'Obligatoria para contrato a término fijo. Debe ser posterior a la fecha de inicio.'
                : 'Debe ser posterior a la fecha de inicio.';
        }

        updateEditFechaFinMin();
    }

    function getEditRiskLevelNumber(rawValue) {
        const value = (rawValue ?? '').toString().trim().toUpperCase();
        if (value === '') {
            return null;
        }

        const normalized = value.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

        const digitMatch = normalized.match(/\b([1-5])\b/);
        if (digitMatch) {
            return Number(digitMatch[1]);
        }

        const romanMatch = normalized.match(/\b(III|IV|II|V|I)\b/);
        if (!romanMatch) {
            return null;
        }

        const token = romanMatch[1];
        const romanMap = {
            I: 1,
            II: 2,
            III: 3,
            IV: 4,
            V: 5,
        };

        return romanMap[token] ?? Number(token);
    }

    function syncEditRiskClassification() {
        const nivelRiesgo = document.getElementById('editNivelRiesgo');
        const altoRiesgo = document.getElementById('editAltoRiesgo');
        const bajoRiesgo = document.getElementById('editBajoRiesgo');

        if (!nivelRiesgo || !altoRiesgo || !bajoRiesgo) {
            return;
        }

        const riskLevel = getEditRiskLevelNumber(nivelRiesgo.value);

        if (riskLevel === null) {
            altoRiesgo.checked = false;
            bajoRiesgo.checked = false;
            return;
        }

        if (riskLevel >= 3) {
            altoRiesgo.checked = true;
            bajoRiesgo.checked = false;
            return;
        }

        altoRiesgo.checked = false;
        bajoRiesgo.checked = true;
    }

    function isEditCashFormaPagoSelected() {
        const formaPagoInput = document.getElementById('editIdFormaPago');
        if (!formaPagoInput) {
            return false;
        }

        const selectedOption = formaPagoInput.selectedOptions && formaPagoInput.selectedOptions[0]
            ? formaPagoInput.selectedOptions[0]
            : null;

        if (!selectedOption) {
            return false;
        }

        const optionText = (selectedOption.textContent ?? '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();

        // Considerar 'efectivo' o 'contado' como pago en efectivo (paridad con registro)
        return optionText.includes('efectivo') || optionText.includes('contado');
    }

    function syncEditBankFieldsByPaymentMethod() {
        const fields = [
            document.getElementById('editBanco'),
            document.getElementById('editIdBanco'),
            document.getElementById('editTipoCuenta'),
            document.getElementById('editNumeroCuenta'),
        ].filter(Boolean);

        if (fields.length === 0) {
            return;
        }

        const isCash = isEditCashFormaPagoSelected();

        fields.forEach((field) => {
            if (isCash) {
                field.value = '';
                field.setAttribute('disabled', 'disabled');
                field.classList.add('bg-gray-100', 'cursor-not-allowed');
                clearEditFieldError(field);
                return;
            }

            field.removeAttribute('disabled');
            field.classList.remove('bg-gray-100', 'cursor-not-allowed');
        });
    }

    function normalizeEditFieldValue(field) {
        if (field.type === 'checkbox') {
            return field.checked ? '1' : '0';
        }

        if (field.name === 'salario') {
            const parsedSalary = parseEditLocalizedNumber(field.value);
            return Number.isFinite(parsedSalary) ? String(parsedSalary) : '';
        }

        return (field.value ?? '').toString().trim();
    }

    function snapshotEditFormValues() {
        const form = document.getElementById('editEmployeeForm');
        if (!form) {
            return;
        }

        form.querySelectorAll('input, select, textarea').forEach(field => {
            const name = field.getAttribute('name');
            if (!name || name === 'doc' || name === '_token') {
                return;
            }

            field.dataset.initialValue = normalizeEditFieldValue(field);
        });
    }

    function buildChangedFieldsFormData(form) {
        const formData = new FormData();
        const token = form.querySelector('input[name="_token"]')?.value;
        if (token) {
            formData.append('_token', token);
        }

        let changedCount = 0;

        form.querySelectorAll('input, select, textarea').forEach(field => {
            const name = field.getAttribute('name');
            if (!name || name === 'doc' || name === '_token') {
                return;
            }

            if (field.disabled) {
                return;
            }

            const currentValue = normalizeEditFieldValue(field);
            const initialValue = (field.dataset.initialValue ?? '').toString();

            if (currentValue !== initialValue) {
                formData.append(name, currentValue);
                changedCount++;
            }
        });

        return { formData, changedCount };
    }

    function setEditFieldError(input, message) {
        if (!input) {
            return;
        }

        const errorEl = document.querySelector(`#editEmployeeForm [data-error="${input.name}"]`);
        if (!errorEl) return;

        errorEl.textContent = message;
        errorEl.classList.remove('hidden');

        // Distinguir entre Error (Rojo) y Advertencia (Ámbar)
        const isWarning = message.toLowerCase().includes('alerta') || message.toLowerCase().includes('advertencia');
        
        if (isWarning) {
            input.classList.remove('border-red-500');
            input.classList.add('border-amber-500', 'text-amber-700');
            errorEl.classList.remove('text-red-500');
            errorEl.classList.add('text-amber-600');
        } else {
            input.classList.add('border-red-500');
            input.classList.remove('border-amber-500', 'text-amber-700');
            errorEl.classList.add('text-red-500');
            errorEl.classList.remove('text-amber-600');
        }
    }

    function clearEditFieldError(input) {
        if (!input) {
            return;
        }

        input.classList.remove('border-red-500', 'border-amber-500', 'text-amber-700');
        const errorEl = document.querySelector(`#editEmployeeForm [data-error="${input.name}"]`);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
            errorEl.classList.remove('text-red-500', 'text-amber-600');
        }
    }

    function validateEditInput(input, condition, message, showError = true) {
        if (!input) {
            return true;
        }

        const value = (input.value ?? '').toString().trim();

        if (condition) {
            if (showError) {
                clearEditFieldError(input);
            }
            return true;
        }

        if (showError && (value !== '' || input.required)) {
            setEditFieldError(input, message);
        }

        return false;
    }

    function getFirstEditErrorMessage() {
        const form = document.getElementById('editEmployeeForm');
        if (!form) {
            return null;
        }

        const firstError = form.querySelector('.error-message:not(.hidden)');
        if (!firstError) {
            return null;
        }

        const message = (firstError.textContent ?? '').toString().trim();
        return message !== '' ? message : null;
    }

    function showEditValidationAlert(message = null) {
        const errorMessage = message || getFirstEditErrorMessage() || 'No puedes continuar hasta corregir los errores del formulario.';

        Swal.fire({
            icon: 'warning',
            title: 'Corrige los errores',
            text: errorMessage,
            confirmButtonColor: '#1565C0',
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    }

    window.showEditValidationAlert = showEditValidationAlert;

    function validateEditField(stepNumber, fieldName, showError = true) {
        const form = document.getElementById('editEmployeeForm');
        if (!form) {
            return true;
        }

        const input = form.querySelector(`[name="${fieldName}"]`);
        const value = input ? (input.value ?? '').toString().trim() : '';

        if (stepNumber === 1) {
            switch (fieldName) {
                case 'id_tipo_doc':
                    return validateEditInput(input, value !== '', 'El tipo de documento es obligatorio.', showError);
                case 'primer_nombre':
                    return validateEditInput(input, EDIT_LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 30, 'El primer nombre debe tener entre 3 y 30 caracteres y solo letras y espacios.', showError);
                case 'otros_nombres':
                    if (value === '') {
                        if (showError) clearEditFieldError(input);
                        return true;
                    }
                    return validateEditInput(input, EDIT_LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 50, 'Los otros nombres deben tener entre 3 y 50 caracteres y solo letras y espacios.', showError);
                case 'primer_apellido':
                    return validateEditInput(input, EDIT_LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 30, 'El primer apellido debe tener entre 3 y 30 caracteres y solo letras y espacios.', showError);
                case 'segundo_apellido':
                    if (value === '') {
                        if (showError) clearEditFieldError(input);
                        return true;
                    }
                    return validateEditInput(input, EDIT_LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 30, 'El segundo apellido debe tener entre 3 y 30 caracteres y solo letras y espacios.', showError);
                case 'id_ciudad':
                    return validateEditInput(input, value !== '', 'La ciudad es obligatoria.', showError);
                case 'direccion':
                    return validateEditInput(input, value !== '' && value.length <= 150 && EDIT_ADDRESS_REGEX.test(value), 'La dirección debe incluir texto válido y una referencia vial (ej: Calle, Carrera, Cra, Cl, Av, Transversal, Diagonal, # o No).', showError);
                case 'email':
                    return validateEditInput(input, /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) && value.length <= 255, 'Debe ingresar un correo electrónico válido.', showError);
                case 'telefono':
                    return validateEditInput(input, /^[0-9]{10}$/.test(value), 'El teléfono debe tener exactamente 10 dígitos.', showError);
                default:
                    return true;
            }
        }

        if (stepNumber === 2) {
            const fechaInicio = form.querySelector('[name="fecha_inicio"]');
            const fechaInicioValue = fechaInicio ? (fechaInicio.value ?? '').toString().trim() : '';

            switch (fieldName) {
                case 'id_tipo_trabajador':
                    return validateEditInput(input, value !== '', 'Debe seleccionar el tipo de trabajador.', showError);
                case 'id_sub_tipo_trabajador':
                    return validateEditInput(input, value !== '', 'Debe seleccionar el sub tipo de trabajador.', showError);
                case 'id_tipo_contrato':
                    return validateEditInput(input, value !== '', 'Debe seleccionar el tipo de contrato.', showError);
                case 'id_arl':
                    return validateEditInput(input, value !== '', 'Debe seleccionar la ARL.', showError);
                case 'fecha_inicio':
                    return validateEditInput(input, value !== '', 'La fecha de inicio es obligatoria.', showError);
                case 'fecha_fin':
                    {
                        const tipoContratoInput = form.querySelector('[name="id_tipo_contrato"]');
                        if (isEditIndefiniteContractSelected(tipoContratoInput)) {
                            return validateEditInput(input, value === '', 'Para contrato indefinido no debe registrar fecha de fin.', showError);
                        }

                        if (value === '') {
                            if (isEditFixedTermContractSelected(tipoContratoInput)) {
                                return validateEditInput(input, false, 'La fecha de fin es obligatoria para contratos a término fijo.', showError);
                            }
                            if (showError) clearEditFieldError(input);
                            return true;
                        }

                        if (!fechaInicioValue) {
                            return validateEditInput(input, false, 'Debe ingresar primero la fecha de inicio.', showError);
                        }
                        return validateEditInput(input, value > fechaInicioValue, 'La fecha fin debe ser posterior a la fecha de inicio.', showError);
                    }
                case 'horas_diarias':
                    return validateEditInput(input, value !== '' && Number(value) >= 1 && Number(value) <= 12, 'Las horas diarias deben estar entre 1 y 12.', showError);
                case 'salario':
                    if (value === '') {
                        return validateEditInput(input, false, 'El salario es obligatorio.', showError);
                    }

                    const salario = parseEditLocalizedNumber(value);
                    if (Number.isNaN(salario) || salario < 0 || salario > 999999999) {
                        return validateEditInput(input, false, 'El salario debe estar entre 0 y 999999999.', showError);
                    }

                    const tipoContratoInput = form.querySelector('[name="id_tipo_contrato"]');
                    const idTipoContrato = Number(tipoContratoInput ? tipoContratoInput.value : 0);

                    if (idTipoContrato === 1 || idTipoContrato === 2 || idTipoContrato === 3) {
                        return validateEditInput(
                            input,
                            EDIT_SMMLV > 0 ? salario >= EDIT_SMMLV : true,
                            'El salario base no puede ser inferior al salario mínimo legal vigente para este tipo de contrato.',
                            showError
                        );
                    }

                    if (idTipoContrato === 4) {
                        const etapaAprendiz = resolveEditAprendizStage(form);

                        if (!etapaAprendiz) {
                            return validateEditInput(input, false, 'Para contrato de aprendizaje debe indicar la etapa del aprendiz (lectiva o productiva).', showError);
                        }

                        if (etapaAprendiz === 'lectiva') {
                            const minimoLectiva = EDIT_SMMLV > 0 ? EDIT_SMMLV * 0.75 : 0;
                            return validateEditInput(input, salario >= minimoLectiva, 'Para etapa lectiva, el salario base no puede ser inferior al 75% del salario mínimo legal vigente.', showError);
                        }

                        return validateEditInput(
                            input,
                            EDIT_SMMLV > 0 ? salario >= EDIT_SMMLV : true,
                            'Para etapa productiva, el salario base no puede ser inferior al salario mínimo legal vigente.',
                            showError
                        );
                    }

                    return validateEditInput(input, true, '', showError);
                case 'codigo_interno':
                    if (value === '') {
                        if (showError) clearEditFieldError(input);
                        return true;
                    }

                    return validateEditInput(input, EDIT_NUMBERS_REGEX.test(value) && value.length >= 3 && value.length <= 20, 'El código interno debe tener entre 3 y 20 dígitos numéricos.', showError);
                case 'nivel_riesgo_id':
                    return validateEditInput(input, value !== '' && !Number.isNaN(Number(value)), 'Debe seleccionar un nivel de riesgo válido.', showError);
                default:
                    return true;
            }
        }

        if (stepNumber === 3) {
            const isCashPayment = isEditCashFormaPagoSelected();

            switch (fieldName) {
                case 'id_forma_pago':
                    return validateEditInput(input, value !== '', 'Debe seleccionar la forma de pago.', showError);
                case 'id_metodo_pago':
                    return validateEditInput(input, value !== '', 'Debe seleccionar el método de pago.', showError);
                case 'tipo_cuenta':
                    if (isCashPayment) {
                        if (showError) {
                            clearEditFieldError(input);
                        }
                        return true;
                    }
                    return validateEditInput(input, value !== '', 'Debe seleccionar el tipo de cuenta.', showError);
                case 'numero_cuenta':
                    if (isCashPayment) {
                        if (showError) {
                            clearEditFieldError(input);
                        }
                        return true;
                    }
                    return validateEditInput(input, EDIT_ACCOUNT_REGEX.test(value), 'El número de cuenta debe tener entre 6 y 20 dígitos numéricos.', showError);
                case 'id_eps':
                    return validateEditInput(input, value !== '', 'Debe seleccionar la EPS.', showError);
                case 'id_afp':
                    return validateEditInput(input, value !== '', 'Debe seleccionar la AFP.', showError);
                case 'id_caja':
                    return validateEditInput(input, value !== '', 'Debe seleccionar la caja de compensación.', showError);
                case 'fondo_cesantias':
                    if (value === '') {
                        if (showError) clearEditFieldError(input);
                        return true;
                    }
                    return validateEditInput(input, value.length <= 100, 'El fondo de cesantías no puede superar 100 caracteres.', showError);
                case 'cesantias_inicial':
                    if (value === '' || value === '0') {
                        if (showError) clearEditFieldError(input);
                        return true;
                    }
                    const cVal = parseEditLocalizedNumber(value);
                    if (Number.isNaN(cVal) || cVal < 0) {
                        return validateEditInput(input, false, 'Debe ingresar un valor válido.', showError);
                    }
                    if (cVal > 9999999999) {
                        return validateEditInput(input, false, 'Advertencia: El valor ingresado es inusualmente alto.', showError);
                    }
                    if (cVal > 0 && cVal < 5000) {
                        return validateEditInput(input, false, 'Alerta: El valor es inusualmente bajo para un empleado activo.', showError);
                    }
                    return validateEditInput(input, true, '', showError);
                case 'prima_inicial':
                    if (value === '' || value === '0') {
                        if (showError) clearEditFieldError(input);
                        return true;
                    }
                    const pVal = parseEditLocalizedNumber(value);
                    if (Number.isNaN(pVal) || pVal < 0) {
                        return validateEditInput(input, false, 'Debe ingresar un valor válido.', showError);
                    }
                    if (pVal > 9999999999) {
                        return validateEditInput(input, false, 'Advertencia: El valor ingresado es inusualmente alto.', showError);
                    }
                    if (pVal > 0 && pVal < 5000) {
                        return validateEditInput(input, false, 'Alerta: El valor es inusualmente bajo para un empleado activo.', showError);
                    }
                    return validateEditInput(input, true, '', showError);
                case 'intereses_inicial':
                    if (value === '' || value === '0') {
                        if (showError) clearEditFieldError(input);
                        return true;
                    }
                    const iVal = parseEditLocalizedNumber(value);
                    if (Number.isNaN(iVal) || iVal < 0) {
                        return validateEditInput(input, false, 'Debe ingresar un valor válido.', showError);
                    }
                    if (iVal > 9999999999) {
                        return validateEditInput(input, false, 'Advertencia: El valor ingresado es inusualmente alto.', showError);
                    }
                    if (iVal > 0 && iVal < 1000) {
                        return validateEditInput(input, false, 'Alerta: El valor es inusualmente bajo.', showError);
                    }
                    const editCesantias = document.getElementById('editCesantiasInicial');
                    if (editCesantias) {
                        const cesantiasVal = parseEditLocalizedNumber(editCesantias.value);
                        if (cesantiasVal > 0 && iVal > (cesantiasVal * 0.15)) {
                            return validateEditInput(input, false, 'Advertencia: Los intereses parecen ser incoherentes con las cesantías.', showError);
                        }
                    }
                    return validateEditInput(input, true, '', showError);
                case 'vacaciones_inicial':
                    if (value === '' || value === '0') {
                        if (showError) clearEditFieldError(input);
                        return true;
                    }
                    const vVal = parseEditLocalizedNumber(value);
                    if (Number.isNaN(vVal) || vVal < 0) {
                        return validateEditInput(input, false, 'Debe ingresar un valor válido.', showError);
                    }
                    if (vVal > 180) {
                        return validateEditInput(input, false, 'Error: No se permite acumular más de 180 días.', showError);
                    }
                    if (vVal > 60) {
                        return validateEditInput(input, false, 'Alerta: El trabajador tiene más de 60 días acumulados.', showError);
                    }
                    return validateEditInput(input, true, '', showError);
                default:
                    return true;
            }
        }

        return true;
    }

    function validateEditStepByNumber(stepNumber, showError = true) {
        const form = document.getElementById('editEmployeeForm');
        if (!form) {
            return true;
        }

        const fieldsByStep = {
            1: ['id_tipo_doc', 'primer_nombre', 'otros_nombres', 'primer_apellido', 'segundo_apellido', 'id_ciudad', 'direccion', 'email', 'telefono'],
            2: ['id_tipo_trabajador', 'id_sub_tipo_trabajador', 'id_tipo_contrato', 'id_arl', 'fecha_inicio', 'fecha_fin', 'horas_diarias', 'salario', 'codigo_interno', 'nivel_riesgo_id'],
            3: ['id_forma_pago', 'id_metodo_pago', 'tipo_cuenta', 'numero_cuenta', 'id_eps', 'id_afp', 'id_caja', 'fondo_cesantias', 'prima_inicial', 'cesantias_inicial', 'intereses_inicial', 'vacaciones_inicial'],
        };

        const fields = fieldsByStep[stepNumber] || [];
        let isValid = true;

        const tipoContratoField = form.querySelector('[name="id_tipo_contrato"]');
        const tipoTrabajadorField = form.querySelector('[name="id_tipo_trabajador"]');
        const metodoPagoField = form.querySelector('[name="id_metodo_pago"]');
        const tipoContratoChanged = tipoContratoField
            ? normalizeEditFieldValue(tipoContratoField) !== (tipoContratoField.dataset.initialValue ?? '').toString()
            : false;
        const tipoTrabajadorChanged = tipoTrabajadorField
            ? normalizeEditFieldValue(tipoTrabajadorField) !== (tipoTrabajadorField.dataset.initialValue ?? '').toString()
            : false;
        const metodoPagoChanged = metodoPagoField
            ? normalizeEditFieldValue(metodoPagoField) !== (metodoPagoField.dataset.initialValue ?? '').toString()
            : false;
        const forceValidateSalary = stepNumber === 2 && (tipoContratoChanged || tipoTrabajadorChanged);
        const forceValidateBankFields = stepNumber === 3 && metodoPagoChanged && !isEditCashPaymentMethodSelected();

        fields.forEach((fieldName) => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field) {
                return;
            }

            const initialValue = (field.dataset.initialValue ?? '').toString();
            const currentValue = normalizeEditFieldValue(field);
            const hasChanged = currentValue !== initialValue;

            if (
                !hasChanged
                && !(forceValidateSalary && fieldName === 'salario')
                && !(forceValidateBankFields && (fieldName === 'tipo_cuenta' || fieldName === 'numero_cuenta'))
            ) {
                if (showError) {
                    clearEditFieldError(field);
                }
                return;
            }

            if (!validateEditField(stepNumber, fieldName, showError)) {
                isValid = false;
            }
        });

        return isValid;
    }

    window.validateEditStepByNumber = validateEditStepByNumber;

    function loadEmployee(doc, options = {}) {
        const isRenewal = options.isRenewal || false;
        
        fetch(`/employees/${doc}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Error al cargar empleado');
                return response.json();
            })
            .then(data => {
                document.getElementById('editDocField').value = data.usuario.doc;
                document.getElementById('editIdTipoDoc').value = data.usuario.id_tipo_doc;
                document.getElementById('editNumeroDoc').value = data.usuario.doc;
                document.getElementById('editPrimerNombre').value = data.usuario.primer_nombre;
                document.getElementById('editOtrosNombres').value = data.usuario.otros_nombres || '';
                document.getElementById('editPrimerApellido').value = data.usuario.primer_apellido;
                document.getElementById('editSegundoApellido').value = data.usuario.segundo_apellido || '';
                
                // Set the department and then the city
                if (data.usuario.ciudad && data.usuario.ciudad.id_departamento) {
                    window.dispatchEvent(new CustomEvent('set-value-editIdDepartamento', { detail: String(data.usuario.ciudad.id_departamento) }));
                } else {
                    window.dispatchEvent(new CustomEvent('set-value-editIdDepartamento', { detail: null }));
                }

                if (data.usuario.id_ciudad) {
                    // Esperar a que las opciones de ciudad se hayan cargado por el handler 'selected' del departamento
                    setTimeout(() => {
                        window.dispatchEvent(new CustomEvent('set-value-editIdCiudad', { detail: String(data.usuario.id_ciudad) }));
                    }, 500);
                } else {
                    window.dispatchEvent(new CustomEvent('set-value-editIdCiudad', { detail: null }));
                }
                document.getElementById('editDireccion').value = data.usuario.direccion || '';
                document.getElementById('editEmail').value = data.usuario.correo || '';
                document.getElementById('editTelefono').value = data.usuario.telefono || '';
                document.getElementById('editIdRol').value = data.usuario.id_rol || '';

                const contrato = data.contrato;
                const cuenta = data.cuenta;
                if (contrato) {
                    document.getElementById('editIdTipoTrabajador').value = contrato.id_tipo_trabajador || '';
                    document.getElementById('editIdSubTipoTrabajador').value = contrato.id_sub_tipo_trabajador || '';
                    document.getElementById('editIdTipoContrato').value = contrato.id_tipo_contrato || '';
                    document.getElementById('editIdArl').value = contrato.id_arl || '';
                    if (isRenewal && contrato.fecha_fin) {
                        try {
                            const lastDate = new Date(contrato.fecha_fin + 'T00:00:00');
                            if (!isNaN(lastDate.getTime())) {
                                lastDate.setDate(lastDate.getDate() + 1);
                                document.getElementById('editFechaInicio').value = lastDate.toISOString().split('T')[0];
                                document.getElementById('editFechaFin').value = ''; 
                            } else {
                                document.getElementById('editFechaInicio').value = contrato.fecha_inicio || '';
                                document.getElementById('editFechaFin').value = contrato.fecha_fin || '';
                            }
                        } catch (e) {
                            console.warn('Error al procesar fecha de fin:', e);
                            document.getElementById('editFechaInicio').value = contrato.fecha_inicio || '';
                            document.getElementById('editFechaFin').value = contrato.fecha_fin || '';
                        }
                    } else {
                        document.getElementById('editFechaInicio').value = contrato.fecha_inicio || '';
                        document.getElementById('editFechaFin').value = contrato.fecha_fin || '';
                    }
                    syncEditFechaFinByContractType();
                    document.getElementById('editHorasDiarias').value = contrato.horas_diarias != null ? contrato.horas_diarias : '';
                    document.getElementById('editSalario').value = formatEditLocalizedNumber(contrato.salario_base || '');
                    document.getElementById('editCodigoInterno').value = contrato.codigo_interno || '';
                    document.getElementById('editNivelRiesgo').value = contrato.nivel_riesgo_id || '';
                    document.getElementById('editAltoRiesgo').checked = contrato.alto_riesgo == 1;
                    document.getElementById('editBajoRiesgo').checked = contrato.alto_riesgo != 1;

                    document.getElementById('editIdFormaPago').value = contrato.id_forma_pago || '';
                    document.getElementById('editIdMetodoPago').value = contrato.id_metodo_pago || '';
                    
                    // Sincronizar campos bancarios (habilitar/deshabilitar) según método de pago
                    syncEditBankFieldsByPaymentMethod();

                    // Cargar valores bancarios DESPUÉS del sync para que no se borren
                    document.getElementById('editTipoCuenta').value = cuenta?.id_tipo_cuenta || '';
                    document.getElementById('editNumeroCuenta').value = cuenta?.numero_cuenta || '';
                    
                    document.getElementById('editIdEps').value = contrato.id_eps || '';
                    document.getElementById('editIdAfp').value = contrato.id_afp || '';
                    document.getElementById('editIdCaja').value = contrato.id_caja || '';
                    document.getElementById('editFondoCesantias').value = data.usuario.fondo_cesantias || '';
                    document.getElementById('editActivo').checked = contrato.activo == 1;
                } else {
                    document.getElementById('editIdTipoTrabajador').value = '';
                    document.getElementById('editIdSubTipoTrabajador').value = '';
                    document.getElementById('editIdTipoContrato').value = '';
                    document.getElementById('editIdArl').value = '';
                    document.getElementById('editFechaInicio').value = '';
                    document.getElementById('editFechaFin').value = '';
                    syncEditFechaFinByContractType();
                    document.getElementById('editHorasDiarias').value = '';
                    document.getElementById('editSalario').value = '';
                    document.getElementById('editCodigoInterno').value = '';
                    document.getElementById('editNivelRiesgo').value = '';
                    document.getElementById('editAltoRiesgo').checked = false;
                    document.getElementById('editBajoRiesgo').checked = false;

                    document.getElementById('editIdFormaPago').value = '';
                    document.getElementById('editIdMetodoPago').value = '';
                    document.getElementById('editTipoCuenta').value = '';
                    document.getElementById('editNumeroCuenta').value = '';
                    document.getElementById('editIdEps').value = '';
                    document.getElementById('editIdAfp').value = '';
                    document.getElementById('editFondoCesantias').value = '';
                    document.getElementById('editActivo').checked = false;
                }

                // Clear migration fields on load (they are usually for new/migration cases)
                ['editPrimaInicial', 'editCesantiasInicial', 'editInteresesInicial', 'editVacacionesInicial'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.value = '0';
                        clearEditFieldError(el);
                    }
                });

                syncEditRiskClassification();
                syncEditBankFieldsByPaymentMethod();

                snapshotEditFormValues();

                const moduleData = typeof getEmpleadosModuleData === 'function' ? getEmpleadosModuleData() : null;
                const draft = moduleData && moduleData.editDrafts ? moduleData.editDrafts[doc] : null;
                if (draft && draft.fields && typeof draft.fields === 'object') {
                    Object.entries(draft.fields).forEach(([name, rawValue]) => {
                        const field = document.querySelector(`#editEmployeeForm [name="${name}"]`);
                        if (!field) {
                            return;
                        }

                        if (field.type === 'checkbox') {
                            field.checked = Boolean(rawValue);
                        } else {
                            field.value = rawValue ?? '';
                        }

                        field.dispatchEvent(new Event('input', { bubbles: true }));
                        field.dispatchEvent(new Event('change', { bubbles: true }));
                    });

                    if (moduleData) {
                        moduleData.editWizardStep = Math.min(3, Math.max(1, Number(draft.editWizardStep || 1)));
                    }

                    syncEditFechaFinByContractType();
                    syncEditRiskClassification();
                    syncEditBankFieldsByPaymentMethod();
                }

                [1, 2, 3].forEach((step) => validateEditStepByNumber(step, false));
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cargar los datos del empleado', 'error');
            });
    }

    window.loadEmployee = loadEmployee;



    document.addEventListener('DOMContentLoaded', function () {
        // Salary input: show raw number on focus, formatted on blur
        const editSalarioInput = document.getElementById('editSalario');
        if (editSalarioInput) {
            editSalarioInput.addEventListener('focus', function () {
                const parsed = parseEditLocalizedNumber(this.value);
                if (Number.isFinite(parsed)) {
                    this.value = parsed % 1 === 0 ? String(Math.round(parsed)) : String(parsed);
                }
            });
            editSalarioInput.addEventListener('blur', function () {
                const parsed = parseEditLocalizedNumber(this.value);
                if (Number.isFinite(parsed)) {
                    this.value = EDIT_SALARY_FORMATTER.format(parsed);
                }
            });
        }

        // Trigger date/validation sync when contract type changes
        const editIdTipoContratoEl = document.getElementById('editIdTipoContrato');
        if (editIdTipoContratoEl) {
            editIdTipoContratoEl.addEventListener('change', function () {
                syncEditFechaFinByContractType();
            });
        }

        const editEmployeeForm = document.getElementById('editEmployeeForm');
        if (editEmployeeForm) {
            editEmployeeForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const doc = document.getElementById('editDocField').value;
                const invalidStep = [1, 2, 3].find((step) => !validateEditStepByNumber(step, true));

                if (invalidStep) {
                    if (typeof getEmpleadosModuleData === 'function') {
                        const moduleData = getEmpleadosModuleData();
                        if (moduleData) {
                            moduleData.editWizardStep = invalidStep;
                        }
                    }
                    showEditValidationAlert('No puedes finalizar hasta corregir los errores del formulario.');
                    return;
                }

                const moduleData = typeof getEmpleadosModuleData === 'function' ? getEmpleadosModuleData() : null;
                const isRenewal = moduleData ? moduleData.isRenewal : false;
                const { formData, changedCount } = buildChangedFieldsFormData(this);

                if (!isRenewal && changedCount === 0) {
                    Swal.fire('Sin cambios', 'No hay datos modificados para guardar', 'info');
                    return;
                }

                const url = isRenewal ? `/employees/${doc}/renew` : `/employees/${doc}/update`;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                })
                .then(response => {
                    if (response.status === 422) {
                        return response.json().then(data => {
                            const fieldStepMap = {
                                id_tipo_doc: 1, primer_nombre: 1, otros_nombres: 1, primer_apellido: 1, segundo_apellido: 1,
                                id_ciudad: 1, direccion: 1, id_tipo_trabajador: 2, id_sub_tipo_trabajador: 2,
                                id_tipo_contrato: 2, id_arl: 2, fecha_inicio: 2, fecha_fin: 2, horas_diarias: 2,
                                salario: 2, salario_base: 2, codigo_interno: 2, nivel_riesgo_id: 2,
                                id_forma_pago: 3, id_metodo_pago: 3, tipo_cuenta: 3, numero_cuenta: 3,
                                id_eps: 3, id_afp: 3,
                            };

                            let firstFieldWithError = null;
                            let firstMessage = null;

                            for (const field in data.errors) {
                                if (!firstFieldWithError) {
                                    firstFieldWithError = field;
                                    firstMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                                }
                                const fieldInput = document.querySelector(`#editEmployeeForm [name="${field}"]`);
                                if (fieldInput) setEditFieldError(fieldInput, data.errors[field][0]);
                            }

                            const errorStep = fieldStepMap[firstFieldWithError] || 3;
                            if (moduleData) {
                                moduleData.editWizardStep = errorStep;
                            }

                            if (typeof window.showEditValidationAlert === 'function') {
                                window.showEditValidationAlert(firstMessage || 'No puedes guardar cambios hasta corregir los errores del formulario.');
                            }
                            throw new Error('Error de validación');
                        });
                    }
                    if (!response.ok) throw new Error('Error al procesar la solicitud');
                    return response.json();
                })
                .then(() => {
                    if (moduleData && moduleData.editDrafts && doc) {
                        delete moduleData.editDrafts[doc];
                    }
                    const successText = isRenewal ? 'Se ha creado un nuevo contrato correctamente.' : 'Empleado actualizado correctamente';
                    Swal.fire('Éxito', successText, 'success').then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (error.message !== 'Error de validación') {
                        Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
                    }
                });
            });
        }
        const editFechaInicio = document.getElementById('editFechaInicio');
        const editTipoContrato = document.getElementById('editIdTipoContrato');
        const editTipoTrabajador = document.getElementById('editIdTipoTrabajador');
        const editNivelRiesgo = document.getElementById('editNivelRiesgo');
        const editAltoRiesgo = document.getElementById('editAltoRiesgo');
        const editBajoRiesgo = document.getElementById('editBajoRiesgo');
        const editIdFormaPago = document.getElementById('editIdFormaPago');
        const editIdMetodoPago = document.getElementById('editIdMetodoPago');
        const editSalario = document.getElementById('editSalario');
        const editCodigoInterno = document.getElementById('editCodigoInterno');
        const editNumeroCuenta = document.getElementById('editNumeroCuenta');

        ['editPrimerNombre', 'editOtrosNombres', 'editPrimerApellido', 'editSegundoApellido'].forEach(function (id) {
            const input = document.getElementById(id);
            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                input.value = (input.value ?? '').toString().replace(/[^a-zA-ZÁÉÍÓÚáéíóúñÑ\s]/g, '');
            });
        });

        if (editCodigoInterno) {
            editCodigoInterno.addEventListener('input', function () {
                editCodigoInterno.value = (editCodigoInterno.value ?? '').toString().replace(/\D/g, '').slice(0, 20);
            });
        }

        if (editNumeroCuenta) {
            editNumeroCuenta.addEventListener('input', function () {
                editNumeroCuenta.value = (editNumeroCuenta.value ?? '').toString().replace(/\D/g, '').slice(0, 20);
            });
        }

        if (editFechaInicio) {
            editFechaInicio.addEventListener('change', updateEditFechaFinMin);
            editFechaInicio.addEventListener('input', updateEditFechaFinMin);
        }

        if (editTipoContrato) {
            editTipoContrato.addEventListener('change', function () {
                syncEditFechaFinByContractType();
                validateEditField(2, 'fecha_fin', true);

                if (!editSalario) {
                    return;
                }

                validateEditField(2, 'salario', true);
            });
        }

        if (editTipoTrabajador) {
            editTipoTrabajador.addEventListener('change', function () {
                if (!editSalario) {
                    return;
                }

                const contractId = Number((editTipoContrato ? editTipoContrato.value : '').toString().trim() || 0);
                if (contractId === 4) {
                    validateEditField(2, 'salario', true);
                }
            });
        }

        if (editNivelRiesgo) {
            editNivelRiesgo.addEventListener('change', function () {
                syncEditRiskClassification();
            });
        }

        [editAltoRiesgo, editBajoRiesgo].filter(Boolean).forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                syncEditRiskClassification();
            });
        });

        if (editIdFormaPago) {
            editIdFormaPago.addEventListener('change', function () {
                syncEditBankFieldsByPaymentMethod();
                validateEditField(3, 'tipo_cuenta', true);
                validateEditField(3, 'numero_cuenta', true);
            });
        }

        if (editIdMetodoPago) {
            editIdMetodoPago.addEventListener('change', function () {
                // El método de pago también puede disparar el sync si es necesario en el futuro
                // pero por ahora la forma de pago es el driver principal según el registro.
                syncEditBankFieldsByPaymentMethod();
            });
        }

        if (editSalario) {
            editSalario.addEventListener('input', function () {
                editSalario.value = formatEditLocalizedNumber(editSalario.value);
            });

            editSalario.addEventListener('blur', function () {
                editSalario.value = formatEditLocalizedNumber(editSalario.value);
            });

            editSalario.value = formatEditLocalizedNumber(editSalario.value);
        }

        // Auto-calculate Intereses (12% of Cesantías) in Edit Modal
        const editCesantias = document.getElementById('editCesantiasInicial');
        const editIntereses = document.getElementById('editInteresesInicial');
        
        if (editCesantias && editIntereses) {
            editCesantias.addEventListener('input', function() {
                const cesVal = parseEditLocalizedNumber(this.value);
                if (!Number.isNaN(cesVal) && cesVal > 0) {
                    const autoIntereses = cesVal * 0.12;
                    // Format as whole number (migration values are usually integers, but we allow decimals if needed)
                    // Here we'll default to integer for auto-calc
                    editIntereses.value = Math.round(autoIntereses).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    validateEditField(3, 'intereses_inicial', true);
                }
            });
        }

        // Migration fields formatting and validation
        const migrationInputsEdit = document.querySelectorAll('.migration-input-edit');
        migrationInputsEdit.forEach(input => {
            // Clear 0 on focus
            input.addEventListener('focus', function() {
                if (this.value === '0' || this.value === '0,00' || this.value === '') {
                    this.value = '';
                }
            });

            // Restore 0 on blur if empty
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.value = '0';
                    validateEditField(3, this.name, true);
                }
            });

            input.addEventListener('input', function() {
                let cursorPosition = this.selectionStart;
                let originalLength = this.value.length;
                
                if (this.dataset.type === 'money') {
                    // Use formatting: dots for thousands, one comma for decimals
                    let sanitized = this.value.replace(/[^\d,]/g, '');
                    let parts = sanitized.split(',');
                    let integerPart = parts[0];
                    let decimalPart = parts.length > 1 ? parts.slice(1).join('') : null;
                    
                    let formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    
                    if (decimalPart !== null) {
                        this.value = formattedInteger + ',' + decimalPart.substring(0, 2);
                    } else {
                        this.value = formattedInteger;
                    }
                } else {
                    // For days, allow numeric and one comma
                    let val = this.value.replace(/[^\d,]/g, '');
                    let parts = val.split(',');
                    if (parts.length > 2) {
                        val = parts[0] + ',' + parts.slice(1).join('');
                    }
                    this.value = val;
                }

                let newLength = this.value.length;
                this.setSelectionRange(cursorPosition + (newLength - originalLength), cursorPosition + (newLength - originalLength));
                
                validateEditField(3, this.name, true);
            });
        });

        syncEditFechaFinByContractType();
        syncEditRiskClassification();
        syncEditBankFieldsByPaymentMethod();
    });

    function handleEditDeptSelectionChange(event) {
        const deptSelect = event.target?.closest?.('#editIdDepartamento');
        if (!deptSelect) {
            return;
        }

        const selectedKey = event?.detail?.key ?? event?.detail ?? '';
        syncEditCityOptionsByDepartment(selectedKey);
    }

    document.addEventListener('selected', handleEditDeptSelectionChange);

    function syncEditCityOptionsByDepartment(deptId) {
        const normalizedId = deptId !== undefined && deptId !== null ? String(deptId) : '';

        if (!normalizedId) {
            window.dispatchEvent(new CustomEvent('set-options-editIdCiudad', { detail: {} }));
            return;
        }

        fetch(`/api/cities/${encodeURIComponent(normalizedId)}`)
            .then(response => response.ok ? response.json() : [])
            .then(data => {
                const options = {};
                (data || []).forEach(item => {
                    if (item && item.id_ciudad && item.nombre) {
                        options[String(item.id_ciudad)] = item.nombre;
                    }
                });
                window.dispatchEvent(new CustomEvent('set-options-editIdCiudad', { detail: options }));
            })
            .catch(() => {
                window.dispatchEvent(new CustomEvent('set-options-editIdCiudad', { detail: {} }));
            });
    }
</script>