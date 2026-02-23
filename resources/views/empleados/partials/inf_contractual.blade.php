<h2 class="text-2xl font-bold text-gray-800 mb-6">
    Paso 2: Información Contractual y Laboral
</h2>

<!-- Indicador de pasos -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-2">
        <div class="flex-1 text-center">
            <div class="text-xs font-medium text-gray-500">
                Información Personal
            </div>
        </div>
        <div class="flex-1 text-center">
            <div class="text-xs font-bold text-[rgb(16,185,129)]">
                Información Contractual y Laboral
            </div>
        </div>
        <div class="flex-1 text-center">
            <div class="text-xs font-medium text-gray-500">
                Información Financiera y Seguridad Social
            </div>
        </div>
    </div>

    <div class="relative">
        <div class="h-1 bg-gray-200 rounded"></div>
        <div class="absolute top-0 left-0 h-1 bg-[rgb(16,185,129)] rounded" style="width: 66.66%;"></div>
        <div class="absolute -top-2.5 flex justify-between w-full">
            <div class="w-6 h-6 bg-[rgb(16,185,129)] rounded-full flex items-center justify-center text-white text-xs font-bold">1</div>
            <div class="w-6 h-6 bg-[rgb(16,185,129)] rounded-full flex items-center justify-center text-white text-xs font-bold">2</div>
            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs font-bold">3</div>
        </div>
    </div>
</div>

<form id="step2">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="fecha-inicio">
                Fecha de inicio
            </label>
            <input type="date" id="fecha_inicio" name="fecha_inicio"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                required/>
            <p class="error-message text-red-500 text-sm hidden" data-error="fecha_inicio"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="fecha-fin">
                Fecha de fin
            </label>
            <input type="date" id="fecha_fin" name="fecha_fin"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"/>
            <p class="error-message text-red-500 text-sm hidden" data-error="fecha_fin"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="horas-diarias">
                Horas diarias a trabajar
            </label>
            <input type="number" id="horas_diarias" name="horas_diarias" placeholder="Ej: 8"
                min="0" max="24"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                required/>
            <p class="error-message text-red-500 text-sm hidden" data-error="horas_diarias"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_tipo_trabajador">
                Tipo de trabajador
            </label>
            <select id="id_tipo_trabajador"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                    name="id_tipo_trabajador"
                    required>
                <option value="">Seleccionar...</option>
                @foreach ( $tipotrabajadores as $tipotrabajador )
                    <option value="{{ $tipotrabajador->id_tipo_trabajador }}">{{ $tipotrabajador->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_trabajador"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_sub_tipo_trabajador">
                Subtipo de trabajador
            </label>
            <select id="id_sub_tipo_trabajador"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                    name="id_sub_tipo_trabajador"
                    required>
                <option value="">Seleccionar...</option>
                @foreach ( $suptrabajadores as $suptrabajador )
                    <option value="{{ $suptrabajador->id_sub_tipo_trabajador }}">{{ $suptrabajador->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_sub_tipo_trabajador"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_tipo_contrato">
                Tipo de contrato
            </label>
            <select id="id_tipo_contrato"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                    name="id_tipo_contrato"
                    required>
                <option value="">Seleccionar...</option>
                @foreach ( $contratos as $contrato )
                    <option value="{{ $contrato->id_tipo_contrato }}">{{ $contrato->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_contrato"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="salario">
                Salario básico
            </label>
            <input type="number" id="salario" placeholder="Ej: 2000000"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="salario"
                min="0"
                step="0.01"
                required/>
            <p class="error-message text-red-500 text-sm hidden" data-error="salario"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="codigo-interno">
                Código interno del trabajador 
            </label>
            <input type="text" id="codigo_interno" placeholder="Ej: EMP001"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="codigo_interno"
                required/>
            <p class="error-message text-red-500 text-sm hidden" data-error="codigo_interno"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_arl">
                ARL
            </label>
            <select id="id_arl"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                    name="id_arl"
                    required>
                <option value="">Seleccionar...</option>
                @foreach ( $Arl as $arl )
                    <option value="{{ $arl->id_arl }}">{{ $arl->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_arl"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="nivel-riesgo">
                Nivel de riesgo
            </label>
            <select id="nivel_riesgo" name="nivel_riesgo" class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm">
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
            <input id="alto_riesgo" type="checkbox"
                class="h-4 w-4 text-[#1565C0] border-gray-300 rounded focus:ring-[#1565C0]" 
                name="alto_riesgo" />
            <label for="alto_riesgo" class="ml-2 block text-sm text-gray-700">
                Trabajador de alto riesgo
            </label>
            <p class="error-message text-red-500 text-sm hidden" data-error="alto_riesgo"></p>
        </div>
    </div>

    <!-- BOTONES -->
    <div class="mt-10 flex justify-end gap-3">
        <button type="button"
                @click="previousStep()"
                class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition">
            Atrás
        </button>

        <button type="submit"
                class="bg-[rgb(16,185,129)] text-white py-2 px-6 rounded-md hover:bg-[rgb(14,160,112)] transition">
            Continuar
        </button>
    </div>
</form>

<script>
    function clearStep2Errors() {
        const form = $('#step2');
        form.find('.error-message').addClass('hidden').text('');
        form.find('input, select').removeClass('border-red-500 is-invalid');
    }

    function showStep2ValidationErrors(errors) {
        const form = $('#step2');
        $.each(errors || {}, function (key, messages) {
            const errorMessage = Array.isArray(messages) ? messages[0] : messages;
            const errorField = form.find(`[data-error="${key}"]`);
            if (errorField.length) {
                errorField.removeClass('hidden').text(errorMessage);
            }
            form.find(`[name="${key}"]`).addClass('border-red-500 is-invalid');
        });
    }

    function validateStep2Form() {
        let isValid = true;
        const form = $('#step2');

        clearStep2Errors();

        const requiredFields = {
            'fecha_inicio': 'La fecha de inicio es requerida',
            'id_tipo_contrato': 'El tipo de contrato es requerido',
            'id_tipo_trabajador': 'El tipo de trabajador es requerido',
            'id_sub_tipo_trabajador': 'El subtipo de trabajador es requerido',
            'salario': 'El salario es requerido',
            'codigo_interno': 'El código interno es requerido',
            'id_arl': 'La ARL es requerida',
            'horas_diarias': 'Las horas diarias son requeridas',
        };

        $.each(requiredFields, function (fieldName, errorMessage) {
            const field = form.find(`[name="${fieldName}"]`);
            const value = field.val();

            if (!value || value === '') {
                isValid = false;
                field.addClass('border-red-500 is-invalid');
                form.find(`[data-error="${fieldName}"]`).removeClass('hidden').text(errorMessage);
            }
        });

        return isValid;
    }

    function moveToStep3() {
        $('#step2').hide();
        $('#step3').show();

        if (typeof window.goToWizardStep === 'function') {
            window.goToWizardStep(3);
        }
    }

    $(document).on('submit', '#step2', function (e) {
        e.preventDefault();

        if (!validateStep2Form()) {
            return false;
        }

        const form = $(this);

        $.ajax({
            url: "{{ route('employees.step2') }}",
            type: 'POST',
            data: form.serialize(),
            success: function (response) {
                if (response.success === true) {
                    moveToStep3();
                    window.dispatchEvent(new CustomEvent('step2-success'));
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response?.errors?.general?.[0] || response?.message || 'Ocurrió un error en el paso 2.',
                    confirmButtonColor: '#ef4444'
                });
            },
            error: function (error) {
                if (error.status === 422) {
                    showStep2ValidationErrors(error.responseJSON?.errors || {});
                    return;
                }

                const message = error.responseJSON?.errors?.general?.[0]
                    || error.responseJSON?.message
                    || 'Error interno al procesar el paso 2. Intenta nuevamente.';

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonColor: '#ef4444'
                });
            }
        });
    });

    // Limpiar errores cuando el usuario escriba (delegación)
    $(document).on('change focusout', '#step2 input, #step2 select', function() {
        let fieldName = $(this).attr('name');
        if (fieldName) {
            const form = $('#step2');
            form.find(`[data-error="${fieldName}"]`).addClass('hidden').text('');
            $(this).removeClass('border-red-500 is-invalid');
        }
    });
</script>
