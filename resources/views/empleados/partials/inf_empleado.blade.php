<h2 class="text-2xl font-bold text-gray-800 mb-6">
    Paso 1: Información Personal
</h2>

<!-- Indicador de pasos -->
<div class="mb-10">
    <div class="flex justify-between text-sm mb-2">
        <span class="font-semibold text-[rgb(16,185,129)]">
            Información Personal
        </span>
        <span class="text-gray-500">Información Contractual y Laboral</span>
        <span class="text-gray-500 text-right">
            Información Financiera y Seguridad Social
        </span>
    </div>

    <div class="relative h-1 bg-gray-200 rounded-full">
        <div class="absolute h-1 bg-[rgb(16,185,129)] rounded-full w-1/3"></div>

        <div class="absolute -top-3 left-0 w-full flex justify-between">
            <div class="w-6 h-6 bg-[rgb(16,185,129)] text-white rounded-full flex items-center justify-center text-xs font-bold">
                1
            </div>

            <div class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs font-bold">
                2
            </div>

            <div class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs font-bold">
                3
            </div>
        </div>
    </div>
</div>

<form id="step1" novalidate>
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tipo de documento
            </label>
            <select
                class="form-select mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"  name="id_tipo_doc">
                <option value="">Seleccionar...</option>
                @foreach ( $tipodoc as $tipodo )
                    <option value="{{ $tipodo->id_tipo_doc }}">{{ $tipodo->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_doc"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Número de documento 
            </label>
            <input
                type="number"
                placeholder="Ej: 1234567890"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="doc"
                required>
            <p class="error-message text-red-500 text-sm hidden" data-error="doc"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Primer apellido
            </label>
            <input
                type="text"
                placeholder="Ej: Pérez"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="primer_apellido"
                required>
            <p class="error-message text-red-500 text-sm hidden" data-error="primer_apellido"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Segundo apellido
            </label>
            <input
                type="text"
                placeholder="Ej: Gómez"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="segundo_apellido">
            <p class="error-message text-red-500 text-sm hidden" data-error="segundo_apellido"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Primer nombre
            </label>
            <input
                type="text"
                placeholder="Ej: Juan"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="primer_nombre"
                required>
            <p class="error-message text-red-500 text-sm hidden" data-error="primer_nombre"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Otros nombres
            </label>
            <input
                type="text"
                placeholder="Ej: Carlos"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="otros_nombres">
            <p class="error-message text-red-500 text-sm hidden" data-error="otros_nombres"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Departamento 
            </label>
            <select
                class="form-select mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="departamento"
                required>
                <option value="">Seleccionar...</option>
                @foreach ( $departamento as $depa )
                    <option value="{{ $depa->id_departamento }}">{{ $depa->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="departamento"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Ciudad
            </label>
            <select
                class="form-select mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="ciudad"
                required>
                <option value="">Seleccionar...</option>
                @foreach ( $ciudad as $ciud )
                    <option value="{{ $ciud->id_ciudad }}" data-departamento="{{$ciud->id_departamento}}">{{ $ciud->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="ciudad"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Dirección
            </label>
            <input
                type="text"
                placeholder="Ej: Calle 10 #42-15"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="direccion"
                required>
            <p class="error-message text-red-500 text-sm hidden" data-error="direccion"></p>
        </div>

    </div>

    <div class="mt-10 flex justify-end gap-3">
        <button
            type="button"
            @click="closeModals()"
            class="bg-gray-200 text-gray-800 font-medium py-2 px-6 rounded-md hover:bg-gray-300 transition">
            Cancelar
        </button>
        <button
            type="submit"
            class="bg-[rgb(16,185,129)] text-white font-medium py-2 px-6 rounded-md hover:bg-[rgb(14,160,112)] transition">
            Continuar
        </button>
    </div>
</form> 

<script>
    function clearStep1Errors() {
        const form = $('#step1');
        form.find('.error-message').addClass('hidden').text('');
        form.find('input, select').removeClass('border-red-500 is-invalid');
    }

    function showStep1ValidationErrors(errors) {
        const form = $('#step1');
        $.each(errors || {}, function (key, messages) {
            const errorMessage = Array.isArray(messages) ? messages[0] : messages;
            const errorField = form.find(`[data-error="${key}"]`);
            if (errorField.length) {
                errorField.removeClass('hidden').text(errorMessage);
            }
            form.find(`[name="${key}"]`).addClass('border-red-500 is-invalid');
        });
    }

    function validateStep1Form() {
        let isValid = true;
        const form = $('#step1');

        clearStep1Errors();

        const requiredFields = {
            'id_tipo_doc': 'El tipo de documento es requerido',
            'doc': 'El número de documento es requerido',
            'primer_nombre': 'El primer nombre es requerido',
            'primer_apellido': 'El primer apellido es requerido',
            'direccion': 'La dirección es requerida',
            'ciudad': 'La ciudad es requerida',
            'departamento': 'El departamento es requerido',
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

    function moveToStep2() {
        $('#step1').hide();
        $('#step2').show();

        if (typeof window.goToWizardStep === 'function') {
            window.goToWizardStep(2);
        }
    }

    $(document).on('submit', '#step1', function (e) {
        e.preventDefault();

        if (!validateStep1Form()) {
            return false;
        }

        const form = $(this);

        $.ajax({
            url: "{{ route('employees.step1') }}",
            type: 'POST',
            data: form.serialize(),
            success: function (response) {
                if (response.success === true) {
                    moveToStep2();
                    window.dispatchEvent(new CustomEvent('step1-success'));
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response?.errors?.general?.[0] || response?.message || 'Ocurrió un error en el paso 1.',
                    confirmButtonColor: '#ef4444'
                });
            },
            error: function (error) {
                if (error.status === 422) {
                    showStep1ValidationErrors(error.responseJSON?.errors || {});
                    return;
                }

                const message = error.responseJSON?.errors?.general?.[0]
                    || error.responseJSON?.message
                    || 'Error interno al procesar el paso 1. Intenta nuevamente.';

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
    $(document).on('change focusout', '#step1 input, #step1 select', function() {
        let fieldName = $(this).attr('name');
        if (fieldName) {
            const form = $('#step1');
            form.find(`[data-error="${fieldName}"]`).addClass('hidden').text('');
            $(this).removeClass('border-red-500 is-invalid');
        }
    });
</script>
