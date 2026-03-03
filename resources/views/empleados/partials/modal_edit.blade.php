{{-- MODAL EDITAR EMPLEADO --}}
<div
    x-show="showEditModal"
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
                Actualiza los datos del empleado en tres pasos.
            </p>

            <div class="mt-8 space-y-4">
                <div class="flex items-center" :class="editWizardStep >= 1 ? 'opacity-100' : 'opacity-50'">
                    <div :class="editWizardStep >= 1 ? 'bg-green-400' : 'bg-blue-300'" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                        <i :class="editWizardStep > 1 ? 'fas fa-check' : ''" x-show="editWizardStep > 1"></i>
                        <span x-show="editWizardStep <= 1">1</span>
                    </div>
                    <span class="font-medium">Datos Personales</span>
                </div>

                <div class="flex items-center" :class="editWizardStep >= 2 ? 'opacity-100' : 'opacity-50'">
                    <div :class="editWizardStep >= 2 ? 'bg-green-400' : 'bg-blue-300'" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                        <i :class="editWizardStep > 2 ? 'fas fa-check' : ''" x-show="editWizardStep > 2"></i>
                        <span x-show="editWizardStep <= 2">2</span>
                    </div>
                    <span class="font-medium">Datos Laborales</span>
                </div>

                <div class="flex items-center" :class="editWizardStep >= 3 ? 'opacity-100' : 'opacity-50'">
                    <div :class="editWizardStep >= 3 ? 'bg-green-400' : 'bg-blue-300'" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                        <i :class="editWizardStep > 3 ? 'fas fa-check' : ''" x-show="editWizardStep > 3"></i>
                        <span x-show="editWizardStep <= 3">3</span>
                    </div>
                    <span class="font-medium">Datos Financieros</span>
                </div>
            </div>
        </div>

        {{-- CONTENIDO --}}
        <div class="w-full md:w-2/3 p-8 md:p-10 overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Editar Empleado</h2>
                <button @click="closeModals()" class="text-gray-500 hover:text-gray-800 text-2xl">
                    &times;
                </button>
            </div>

            <div class="mb-10">
                <div class="flex justify-between text-sm mb-2">
                    <span :class="editWizardStep >= 1 ? 'font-semibold text-[rgb(16,185,129)]' : 'text-gray-500'">
                        Información Personal
                    </span>
                    <span :class="editWizardStep >= 2 ? 'font-semibold text-[rgb(16,185,129)]' : 'text-gray-500'">
                        Información Contractual y Laboral
                    </span>
                    <span class="text-right" :class="editWizardStep >= 3 ? 'font-semibold text-[rgb(16,185,129)]' : 'text-gray-500'">
                        Información Financiera y Seguridad Social
                    </span>
                </div>

                <div class="relative h-1 bg-gray-200 rounded-full">
                    <div
                        class="absolute h-1 bg-[rgb(16,185,129)] rounded-full"
                        :class="{
                            'w-1/3': editWizardStep === 1,
                            'w-2/3': editWizardStep === 2,
                            'w-full': editWizardStep === 3
                        }"
                    ></div>

                    <div class="absolute -top-3 left-0 w-full flex justify-between">
                        <div :class="editWizardStep >= 1 ? 'bg-[rgb(16,185,129)] text-white' : 'bg-gray-300 text-gray-600'" class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            1
                        </div>

                        <div :class="editWizardStep >= 2 ? 'bg-[rgb(16,185,129)] text-white' : 'bg-gray-300 text-gray-600'" class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            2
                        </div>

                        <div :class="editWizardStep >= 3 ? 'bg-[rgb(16,185,129)] text-white' : 'bg-gray-300 text-gray-600'" class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            3
                        </div>
                    </div>
                </div>
            </div>

            <form id="editEmployeeForm" novalidate>
                @csrf
                <input type="hidden" id="editDocField" name="doc">

                <div x-show="editWizardStep === 1" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-3">Datos Personales</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Documento</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="id_tipo_doc" id="editIdTipoDoc">
                                @foreach ($tipodoc as $tipo)
                                    <option value="{{ $tipo->id_tipo_doc }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_doc"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de Documento</label>
                            <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" name="numero_documento" id="editNumeroDoc" readonly>
                            <p class="error-message text-red-500 text-sm hidden" data-error="numero_documento"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primer Nombre</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] capitalize" name="primer_nombre" id="editPrimerNombre" minlength="3" maxlength="30" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                            <p class="error-message text-red-500 text-sm hidden" data-error="primer_nombre"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Otros Nombres</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] capitalize" name="otros_nombres" id="editOtrosNombres" minlength="3" maxlength="50" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                            <p class="error-message text-red-500 text-sm hidden" data-error="otros_nombres"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primer Apellido</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] capitalize" name="primer_apellido" id="editPrimerApellido" minlength="3" maxlength="30" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                            <p class="error-message text-red-500 text-sm hidden" data-error="primer_apellido"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Segundo Apellido</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] capitalize" name="segundo_apellido" id="editSegundoApellido" minlength="3" maxlength="30" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                            <p class="error-message text-red-500 text-sm hidden" data-error="segundo_apellido"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="id_ciudad" id="editIdCiudad">
                                @foreach ($ciudad as $c)
                                    <option value="{{ $c->id_ciudad }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_ciudad"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] capitalize" name="direccion" id="editDireccion" maxlength="150" title="Incluye referencia vial (Calle, Carrera, Cra, Cl, Av, Transversal, Diagonal, # o No).">
                            <p class="error-message text-red-500 text-sm hidden" data-error="direccion"></p>
                        </div>
                    </div>
                </div>

                <div x-show="editWizardStep === 2" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-3">Datos Laborales</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Trabajador</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="id_tipo_trabajador" id="editIdTipoTrabajador">
                                @foreach ($tipotrabajadores as $tipo)
                                    <option value="{{ $tipo->id_tipo_trabajador }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_trabajador"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subtipo de Trabajador</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="id_sub_tipo_trabajador" id="editIdSubTipoTrabajador">
                                @foreach ($suptrabajadores as $sub)
                                    <option value="{{ $sub->id_sub_tipo_trabajador }}">{{ $sub->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_sub_tipo_trabajador"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Contrato</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="id_tipo_contrato" id="editIdTipoContrato">
                                @foreach ($contratos as $contrato)
                                    <option value="{{ $contrato->id_tipo_contrato }}">{{ $contrato->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_contrato"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ARL</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="id_arl" id="editIdArl">
                                @foreach ($Arl as $arl)
                                    <option value="{{ $arl->id_arl }}">{{ $arl->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_arl"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="fecha_inicio" id="editFechaInicio">
                            <p class="error-message text-red-500 text-sm hidden" data-error="fecha_inicio"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Fin</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="fecha_fin" id="editFechaFin">
                            <p class="error-message text-red-500 text-sm hidden" data-error="fecha_fin"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Horas Diarias</label>
                            <input type="number" min="1" max="12" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="horas_diarias" id="editHorasDiarias">
                            <p class="error-message text-red-500 text-sm hidden" data-error="horas_diarias"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salario Base</label>
                            <input type="number" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="salario" id="editSalario">
                            <p class="error-message text-red-500 text-sm hidden" data-error="salario"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código Interno</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="codigo_interno" id="editCodigoInterno" inputmode="numeric" minlength="3" maxlength="20" pattern="[0-9]+">
                            <p class="error-message text-red-500 text-sm hidden" data-error="codigo_interno"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nivel de Riesgo</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="nivel_riesgo" id="editNivelRiesgo">
                                <option value="">Seleccionar...</option>
                                <option value="Nivel I">Nivel I</option>
                                <option value="Nivel II">Nivel II</option>
                                <option value="Nivel III">Nivel III</option>
                                <option value="Nivel IV">Nivel IV</option>
                                <option value="Nivel V">Nivel V</option>
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="nivel_riesgo"></p>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-[#1565C0] border-gray-300 rounded focus:ring-[#1565C0]" name="alto_riesgo" id="editAltoRiesgo">
                            <label for="editAltoRiesgo" class="ml-2 block text-sm text-gray-700">
                                Trabajador de alto riesgo
                            </label>
                        </div>
                    </div>
                </div>

                <div x-show="editWizardStep === 3" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-3">Datos Financieros y Seguridad Social</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pago</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="id_forma_pago" id="editIdFormaPago">
                                @foreach ($formapagos as $forma)
                                    <option value="{{ $forma->id_forma_pago }}">{{ $forma->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_forma_pago"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="id_metodo_pago" id="editIdMetodoPago">
                                @foreach ($metodopago as $metodo)
                                    <option value="{{ $metodo->id_metodo_pago }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_metodo_pago"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Cuenta</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="tipo_cuenta" id="editTipoCuenta">
                                @foreach ($tipocuenta as $tipo)
                                    <option value="{{ $tipo->id_tipo_cuenta }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="tipo_cuenta"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de Cuenta</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="numero_cuenta" id="editNumeroCuenta" inputmode="numeric" minlength="6" maxlength="20" pattern="[0-9]{6,20}">
                            <p class="error-message text-red-500 text-sm hidden" data-error="numero_cuenta"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">EPS</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="id_eps" id="editIdEps">
                                @foreach ($Eps as $eps)
                                    <option value="{{ $eps->id_eps }}">{{ $eps->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_eps"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">AFP</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0]" name="id_afp" id="editIdAfp">
                                @foreach ($Afp as $afp)
                                    <option value="{{ $afp->id_afp }}">{{ $afp->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="error-message text-red-500 text-sm hidden" data-error="id_afp"></p>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-[#1565C0] border-gray-300 rounded focus:ring-[#1565C0]" name="activo" id="editActivo">
                            <label for="editActivo" class="ml-2 block text-sm text-gray-700">
                                Empleado activo
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex justify-between gap-4">
                    <button
                        type="button"
                        @click="closeModals()"
                        class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition"
                    >
                        Cancelar
                    </button>

                    <div class="flex gap-3">
                        <button
                            type="button"
                            x-show="editWizardStep > 1"
                            @click="previousEditStep()"
                            class="bg-white border border-gray-300 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-50 transition"
                        >
                            Atrás
                        </button>
                        <button
                            type="button"
                            x-show="editWizardStep < 3"
                            @click="nextEditStep()"
                            class="bg-[#1565C0] text-white py-2 px-6 rounded-md hover:bg-[#0D47A1] transition"
                        >
                            Continuar
                        </button>
                        <button
                            type="submit"
                            x-show="editWizardStep === 3"
                            class="bg-[#1565C0] text-white py-2 px-6 rounded-md hover:bg-[#0D47A1] transition"
                        >
                            Guardar Cambios
                        </button>
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

function normalizeEditFieldValue(field) {
    if (field.type === 'checkbox') {
        return field.checked ? '1' : '0';
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

    input.classList.add('border-red-500');
    const errorEl = document.querySelector(`#editEmployeeForm [data-error="${input.name}"]`);
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }
}

function clearEditFieldError(input) {
    if (!input) {
        return;
    }

    input.classList.remove('border-red-500');
    const errorEl = document.querySelector(`#editEmployeeForm [data-error="${input.name}"]`);
    if (errorEl) {
        errorEl.textContent = '';
        errorEl.classList.add('hidden');
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
                return validateEditInput(input, EDIT_LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 30, 'El primer nombre debe tener entre 3 y 30 caracteres y solo letras.', showError);
            case 'otros_nombres':
                if (value === '') {
                    if (showError) clearEditFieldError(input);
                    return true;
                }
                return validateEditInput(input, EDIT_LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 50, 'Los otros nombres deben tener entre 3 y 50 caracteres y solo letras.', showError);
            case 'primer_apellido':
                return validateEditInput(input, EDIT_LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 30, 'El primer apellido debe tener entre 3 y 30 caracteres y solo letras.', showError);
            case 'segundo_apellido':
                if (value === '') {
                    if (showError) clearEditFieldError(input);
                    return true;
                }
                return validateEditInput(input, EDIT_LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 30, 'El segundo apellido debe tener entre 3 y 30 caracteres y solo letras.', showError);
            case 'id_ciudad':
                return validateEditInput(input, value !== '', 'La ciudad es obligatoria.', showError);
            case 'direccion':
                return validateEditInput(input, value !== '' && value.length <= 150 && EDIT_ADDRESS_REGEX.test(value), 'La dirección debe incluir referencia vial válida (Calle, Carrera, Cra, Cl, Av, Transversal, Diagonal, # o No).', showError);
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
                if (value === '') {
                    if (showError) clearEditFieldError(input);
                    return true;
                }
                if (!fechaInicioValue) {
                    return validateEditInput(input, false, 'Debe ingresar primero la fecha de inicio.', showError);
                }
                return validateEditInput(input, value >= fechaInicioValue, 'La fecha fin no puede ser menor que la fecha de inicio.', showError);
            case 'horas_diarias':
                return validateEditInput(input, value !== '' && Number(value) >= 1 && Number(value) <= 12, 'Las horas diarias deben estar entre 1 y 12.', showError);
            case 'salario':
                return validateEditInput(input, value !== '' && !Number.isNaN(Number(value)) && Number(value) >= 0.01 && Number(value) <= 999999999, 'El salario debe estar entre 0.01 y 999999999.', showError);
            case 'codigo_interno':
                return validateEditInput(input, EDIT_NUMBERS_REGEX.test(value) && value.length >= 3 && value.length <= 20, 'El código interno debe tener entre 3 y 20 dígitos numéricos.', showError);
            case 'nivel_riesgo':
                return validateEditInput(input, ['Nivel I', 'Nivel II', 'Nivel III', 'Nivel IV', 'Nivel V'].includes(value), 'Debe seleccionar un nivel de riesgo válido.', showError);
            default:
                return true;
        }
    }

    if (stepNumber === 3) {
        switch (fieldName) {
            case 'id_forma_pago':
                return validateEditInput(input, value !== '', 'Debe seleccionar la forma de pago.', showError);
            case 'id_metodo_pago':
                return validateEditInput(input, value !== '', 'Debe seleccionar el método de pago.', showError);
            case 'tipo_cuenta':
                return validateEditInput(input, value !== '', 'Debe seleccionar el tipo de cuenta.', showError);
            case 'numero_cuenta':
                return validateEditInput(input, EDIT_ACCOUNT_REGEX.test(value), 'El número de cuenta debe tener entre 6 y 20 dígitos numéricos.', showError);
            case 'id_eps':
                return validateEditInput(input, value !== '', 'Debe seleccionar la EPS.', showError);
            case 'id_afp':
                return validateEditInput(input, value !== '', 'Debe seleccionar la AFP.', showError);
            default:
                return true;
        }
    }

    return true;
}

function validateEditStepByNumber(stepNumber, showError = true) {
    const fieldsByStep = {
        1: ['id_tipo_doc', 'primer_nombre', 'otros_nombres', 'primer_apellido', 'segundo_apellido', 'id_ciudad', 'direccion'],
        2: ['id_tipo_trabajador', 'id_sub_tipo_trabajador', 'id_tipo_contrato', 'id_arl', 'fecha_inicio', 'fecha_fin', 'horas_diarias', 'salario', 'codigo_interno', 'nivel_riesgo'],
        3: ['id_forma_pago', 'id_metodo_pago', 'tipo_cuenta', 'numero_cuenta', 'id_eps', 'id_afp'],
    };

    const fields = fieldsByStep[stepNumber] || [];
    let isValid = true;

    fields.forEach((fieldName) => {
        if (!validateEditField(stepNumber, fieldName, showError)) {
            isValid = false;
        }
    });

    return isValid;
}

window.validateEditStepByNumber = validateEditStepByNumber;

function loadEmployee(doc) {
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
            document.getElementById('editIdCiudad').value = data.usuario.id_ciudad || '';
            document.getElementById('editDireccion').value = data.usuario.direccion || '';

            const contrato = data.contrato;
            const cuenta = data.cuenta;
            if (contrato) {
                document.getElementById('editIdTipoTrabajador').value = contrato.id_tipo_trabajador || '';
                document.getElementById('editIdSubTipoTrabajador').value = contrato.id_sub_tipo_trabajador || '';
                document.getElementById('editIdTipoContrato').value = contrato.id_tipo_contrato || '';
                document.getElementById('editIdArl').value = contrato.id_arl || '';
                document.getElementById('editFechaInicio').value = contrato.fecha_inicio || '';
                document.getElementById('editFechaFin').value = contrato.fecha_fin || '';
                document.getElementById('editHorasDiarias').value = contrato.horas_diarias || '';
                document.getElementById('editSalario').value = contrato.salario_base || '';
                document.getElementById('editCodigoInterno').value = contrato.codigo_interno || '';
                document.getElementById('editNivelRiesgo').value = contrato.nivel_riesgo || '';
                document.getElementById('editAltoRiesgo').checked = contrato.alto_riesgo == 1;

                document.getElementById('editIdFormaPago').value = contrato.id_forma_pago || '';
                document.getElementById('editIdMetodoPago').value = contrato.id_metodo_pago || '';
                document.getElementById('editTipoCuenta').value = cuenta?.id_tipo_cuenta || '';
                document.getElementById('editNumeroCuenta').value = cuenta?.numero_cuenta || '';
                document.getElementById('editIdEps').value = contrato.id_eps || '';
                document.getElementById('editIdAfp').value = contrato.id_afp || '';
                document.getElementById('editActivo').checked = contrato.activo == 1;
            } else {
                document.getElementById('editIdTipoTrabajador').value = '';
                document.getElementById('editIdSubTipoTrabajador').value = '';
                document.getElementById('editIdTipoContrato').value = '';
                document.getElementById('editIdArl').value = '';
                document.getElementById('editFechaInicio').value = '';
                document.getElementById('editFechaFin').value = '';
                document.getElementById('editHorasDiarias').value = '';
                document.getElementById('editSalario').value = '';
                document.getElementById('editCodigoInterno').value = '';
                document.getElementById('editNivelRiesgo').value = '';
                document.getElementById('editAltoRiesgo').checked = false;

                document.getElementById('editIdFormaPago').value = '';
                document.getElementById('editIdMetodoPago').value = '';
                document.getElementById('editTipoCuenta').value = '';
                document.getElementById('editNumeroCuenta').value = '';
                document.getElementById('editIdEps').value = '';
                document.getElementById('editIdAfp').value = '';
                document.getElementById('editActivo').checked = false;
            }

            snapshotEditFormValues();
            [1, 2, 3].forEach((step) => validateEditStepByNumber(step, false));
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo cargar los datos del empleado', 'error');
        });
}

document.getElementById('editEmployeeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const doc = document.getElementById('editDocField').value;

    if (!validateEditStepByNumber(3, true)) {
        return;
    }

    const { formData, changedCount } = buildChangedFieldsFormData(this);

    if (changedCount === 0) {
        Swal.fire('Sin cambios', 'No hay datos modificados para guardar', 'info');
        return;
    }

    fetch(`/employees/${doc}/update`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: formData,
    })
    .then(response => {
        if (response.status === 422) {
            return response.json().then(data => {
                for (const field in data.errors) {
                    const fieldInput = document.querySelector(`#editEmployeeForm [name="${field}"]`);
                    if (fieldInput) {
                        setEditFieldError(fieldInput, data.errors[field][0]);
                    }
                }
                throw new Error('Error de validación');
            });
        }
        if (!response.ok) throw new Error('Error al actualizar empleado');
        return response.json();
    })
    .then(() => {
        Swal.fire('Éxito', 'Empleado actualizado correctamente', 'success').then(() => {
            window.location.reload();
        });
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.message !== 'Error de validación') {
            Swal.fire('Error', 'No se pudo actualizar el empleado', 'error');
        }
    });
});
</script>
