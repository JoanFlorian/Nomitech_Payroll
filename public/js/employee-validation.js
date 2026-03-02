(function () {
    const LETTERS_REGEX = /^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/;
    const NUMBERS_REGEX = /^[0-9]+$/;
    const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const ACCOUNT_REGEX = /^[0-9]{6,20}$/;
    const INTERNAL_CODE_REGEX = /^[0-9]+$/;
    const ADDRESS_REGEX = /^(?=.*[A-Za-z])(?=.*(calle|carrera|cra\.?|cl\.?|av\.?|avenida|transversal|diagonal|#|no\.?)).+$/i;

    function getErrorElement(form, fieldName) {
        return form.querySelector(`[data-error="${fieldName}"]`);
    }

    function clearFieldError(input) {
        if (!input) {
            return;
        }

        const form = input.closest('form');
        const fieldName = input.name;
        const errorElement = form ? getErrorElement(form, fieldName) : null;

        input.classList.remove('is-invalid', 'border-red-500');

        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.remove('d-block');
        }
    }

    function setFieldError(input, message) {
        if (!input) {
            return;
        }

        const form = input.closest('form');
        const fieldName = input.name;
        const errorElement = form ? getErrorElement(form, fieldName) : null;

        input.classList.add('is-invalid', 'border-red-500');

        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('d-block');
        }
    }

    function validarInput(input, condicion, mensaje, showError = true) {
        if (!input) {
            return true;
        }

        const value = (input.value || '').trim();

        if (!showError) {
            return Boolean(condicion);
        }

        if (condicion) {
            clearFieldError(input);
            return true;
        }

        if (value !== '' || input.required) {
            setFieldError(input, mensaje);
        } else {
            clearFieldError(input);
        }

        return false;
    }

    function getField(form, name) {
        return form.querySelector(`[name="${name}"]`);
    }

    function validateStep1Field(form, fieldName, showError = true) {
        const input = getField(form, fieldName);
        const value = input ? (input.value || '').trim() : '';

        switch (fieldName) {
            case 'id_tipo_doc':
                return validarInput(input, value !== '', 'El tipo de documento es obligatorio.', showError);
            case 'doc':
                return validarInput(input, NUMBERS_REGEX.test(value) && value.length >= 5 && value.length <= 15, 'El documento debe tener entre 5 y 15 dígitos numéricos.', showError);
            case 'primer_nombre':
                return validarInput(input, LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 30, 'El primer nombre debe tener entre 3 y 30 caracteres, solo letras y espacios.', showError);
            case 'otros_nombres':
                if (value === '') {
                    if (showError) {
                        clearFieldError(input);
                    }
                    return true;
                }
                return validarInput(input, LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 50, 'Los otros nombres deben tener entre 3 y 50 caracteres, solo letras y espacios.', showError);
            case 'primer_apellido':
                return validarInput(input, LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 30, 'El primer apellido debe tener entre 3 y 30 caracteres, solo letras y espacios.', showError);
            case 'segundo_apellido':
                if (value === '') {
                    if (showError) {
                        clearFieldError(input);
                    }
                    return true;
                }
                return validarInput(input, LETTERS_REGEX.test(value) && value.length >= 3 && value.length <= 30, 'El segundo apellido debe tener entre 3 y 30 caracteres, solo letras y espacios.', showError);
            case 'email':
                return validarInput(input, EMAIL_REGEX.test(value) && value.length <= 255, 'Debe ingresar un correo electrónico válido de máximo 255 caracteres.', showError);
            case 'telefono':
                return validarInput(input, NUMBERS_REGEX.test(value) && value.length === 10, 'El teléfono debe tener exactamente 10 dígitos numéricos.', showError);
            case 'departamento':
                return validarInput(input, value !== '', 'El departamento es obligatorio.', showError);
            case 'ciudad':
                return validarInput(input, value !== '', 'La ciudad es obligatoria.', showError);
            case 'direccion':
                return validarInput(input, value !== '' && value.length <= 150 && ADDRESS_REGEX.test(value), 'La dirección debe incluir texto válido y una referencia vial (ej: Calle, Carrera, Cra, Cl, Av, Transversal, Diagonal, # o No).', showError);
            default:
                return true;
        }
    }

    function validateStep2Field(form, fieldName, showError = true) {
        const input = getField(form, fieldName);
        const value = input ? (input.value || '').trim() : '';
        const fechaInicioInput = getField(form, 'fecha_inicio');
        const fechaInicioValue = fechaInicioInput ? (fechaInicioInput.value || '').trim() : '';

        switch (fieldName) {
            case 'fecha_inicio':
                return validarInput(input, value !== '', 'La fecha de ingreso es obligatoria.', showError);
            case 'fecha_fin':
                if (value === '') {
                    if (showError) {
                        clearFieldError(input);
                    }
                    return true;
                }

                if (!fechaInicioValue) {
                    return validarInput(input, false, 'Debe ingresar primero la fecha de inicio.', showError);
                }

                return validarInput(input, value >= fechaInicioValue, 'La fecha fin no puede ser menor que la fecha de inicio.', showError);
            case 'id_tipo_contrato':
                return validarInput(input, value !== '', 'El tipo de contrato es obligatorio.', showError);
            case 'nivel_riesgo':
                return validarInput(input, value !== '', 'El nivel de riesgo es obligatorio.', showError);
            case 'salario':
                return validarInput(input, value !== '' && !Number.isNaN(Number(value)) && Number(value) >= 0.01 && Number(value) <= 999999999, 'El salario debe estar entre 0.01 y 999999999.', showError);
            case 'id_tipo_trabajador':
                return validarInput(input, value !== '', 'El tipo de trabajador es obligatorio.', showError);
            case 'id_sub_tipo_trabajador':
                return validarInput(input, value !== '', 'El subtipo de trabajador es obligatorio.', showError);
            case 'id_arl':
                return validarInput(input, value !== '', 'La ARL es obligatoria.', showError);
            case 'horas_diarias':
                return validarInput(input, value !== '' && Number(value) >= 1 && Number(value) <= 12, 'Las horas diarias deben estar entre 1 y 12.', showError);
            case 'codigo_interno':
                return validarInput(input, INTERNAL_CODE_REGEX.test(value) && value.length >= 3 && value.length <= 20, 'El código interno debe tener entre 3 y 20 dígitos numéricos.', showError);
            default:
                return true;
        }
    }

    function validateStep3Field(form, fieldName, showError = true) {
        const input = getField(form, fieldName);
        const value = input ? (input.value || '').trim() : '';

        switch (fieldName) {
            case 'id_forma_pago':
                return validarInput(input, value !== '', 'La forma de pago es obligatoria.', showError);
            case 'id_metodo_pago':
                return validarInput(input, value !== '', 'El método de pago es obligatorio.', showError);
            case 'tipo_cuenta':
                return validarInput(input, value !== '', 'El tipo de cuenta es obligatorio.', showError);
            case 'numero_cuenta':
                return validarInput(input, ACCOUNT_REGEX.test(value), 'El número de cuenta debe tener entre 6 y 20 dígitos numéricos.', showError);
            case 'id_eps':
                return validarInput(input, value !== '', 'La EPS es obligatoria.', showError);
            case 'id_afp':
                return validarInput(input, value !== '', 'La AFP es obligatoria.', showError);
            default:
                return true;
        }
    }

    function validateFieldByStep(form, fieldName, showError = true) {
        if (!form) {
            return true;
        }

        if (form.id === 'step1') {
            return validateStep1Field(form, fieldName, showError);
        }

        if (form.id === 'step2') {
            return validateStep2Field(form, fieldName, showError);
        }

        if (form.id === 'step3') {
            return validateStep3Field(form, fieldName, showError);
        }

        return true;
    }

    function fieldsForStep(form) {
        if (!form) {
            return [];
        }

        if (form.id === 'step1') {
            return ['id_tipo_doc', 'doc', 'primer_nombre', 'otros_nombres', 'primer_apellido', 'segundo_apellido', 'email', 'telefono', 'departamento', 'ciudad', 'direccion'];
        }

        if (form.id === 'step2') {
            return ['fecha_inicio', 'fecha_fin', 'id_tipo_contrato', 'nivel_riesgo', 'salario', 'id_tipo_trabajador', 'id_sub_tipo_trabajador', 'id_arl', 'horas_diarias', 'codigo_interno'];
        }

        if (form.id === 'step3') {
            return ['id_forma_pago', 'id_metodo_pago', 'tipo_cuenta', 'numero_cuenta', 'id_eps', 'id_afp'];
        }

        return [];
    }

    function validarFormularioStep(stepElement, botonElement, showErrors = true) {
        const fields = fieldsForStep(stepElement);
        let isValid = true;

        fields.forEach((fieldName) => {
            const fieldIsValid = validateFieldByStep(stepElement, fieldName, showErrors);
            if (!fieldIsValid) {
                isValid = false;
            }
        });

        return isValid;
    }

    function showServerValidationErrors(form, errors) {
        const allInputs = form.querySelectorAll('input[name], select[name], textarea[name]');
        allInputs.forEach((input) => clearFieldError(input));

        Object.entries(errors || {}).forEach(([fieldName, messages]) => {
            const input = getField(form, fieldName);
            const message = Array.isArray(messages) ? messages[0] : messages;

            if (input) {
                setFieldError(input, message);
            }
        });
    }

    async function submitStepForm(form, fallbackErrorMessage, successEventName) {
        const submitButton = form.querySelector('button[type="submit"]');

        if (!validarFormularioStep(form, submitButton, true)) {
            return;
        }

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const payload = await response.json().catch(() => ({}));

            if (response.ok && payload.success === true) {
                window.dispatchEvent(new CustomEvent(successEventName));
                return;
            }

            if (response.status === 422) {
                showServerValidationErrors(form, payload.errors || {});
                validarFormularioStep(form, submitButton, false);
                return;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: payload?.errors?.general?.[0] || payload?.message || fallbackErrorMessage,
                confirmButtonColor: '#ef4444'
            });
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: fallbackErrorMessage,
                confirmButtonColor: '#ef4444'
            });
        }
    }

    function initCommonRealtimeValidation(form) {
        const submitButton = form.querySelector('button[type="submit"]');

        form.querySelectorAll('input[name]:not([type="hidden"]), textarea[name]').forEach((input) => {
            input.addEventListener('input', function () {
                validateFieldByStep(form, input.name, true);
                validarFormularioStep(form, submitButton, false);
            });

            input.addEventListener('blur', function () {
                validateFieldByStep(form, input.name, true);
                validarFormularioStep(form, submitButton, false);
            });
        });

        form.querySelectorAll('select[name]').forEach((select) => {
            select.addEventListener('change', function () {
                clearFieldError(select);
                validarFormularioStep(form, submitButton, false);
            });
        });

        validarFormularioStep(form, submitButton, false);
    }

    function initStep1Validation() {
        const form = document.getElementById('step1');
        if (!form) {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        const docInput = getField(form, 'doc');
        const telefonoInput = getField(form, 'telefono');

        if (docInput) {
            docInput.addEventListener('input', function () {
                docInput.value = (docInput.value || '').replace(/\D/g, '').slice(0, 15);
            });
        }

        if (telefonoInput) {
            telefonoInput.addEventListener('input', function () {
                telefonoInput.value = (telefonoInput.value || '').replace(/\D/g, '').slice(0, 10);
            });
        }

        initCommonRealtimeValidation(form);

        document.addEventListener('selected', function (event) {
            const container = event.target && event.target.closest ? event.target.closest('#deptStep1, #cityStep1') : null;
            if (!container) {
                return;
            }

            if (container.id === 'deptStep1') {
                const cityInput = getField(form, 'ciudad');
                if (cityInput) {
                    cityInput.value = '';
                    clearFieldError(cityInput);
                }
                clearFieldError(getField(form, 'departamento'));
            }

            if (container.id === 'cityStep1') {
                clearFieldError(getField(form, 'ciudad'));
            }

            validarFormularioStep(form, submitButton, false);
        });

        document.addEventListener('input', function (event) {
            const container = event.target && event.target.closest ? event.target.closest('#deptStep1, #cityStep1') : null;
            if (!container) {
                return;
            }

            if (container.id === 'deptStep1') {
                clearFieldError(getField(form, 'departamento'));
            }

            if (container.id === 'cityStep1') {
                clearFieldError(getField(form, 'ciudad'));
            }

            validarFormularioStep(form, submitButton, false);
        });

        window.addEventListener('step1-city-options-updated', function () {
            validarFormularioStep(form, submitButton, false);
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            submitStepForm(form, 'Error interno al procesar el paso 1. Intenta nuevamente.', 'step1-success');
        });
    }

    function initStep2Validation() {
        const form = document.getElementById('step2');
        if (!form) {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        initCommonRealtimeValidation(form);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            submitStepForm(form, 'Error interno al procesar el paso 2. Intenta nuevamente.', 'step2-success');
        });

        validarFormularioStep(form, submitButton, false);
    }

    function initStep3Validation() {
        const form = document.getElementById('step3');
        if (!form) {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        initCommonRealtimeValidation(form);

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!validarFormularioStep(form, submitButton)) {
                return;
            }

            const formData = new FormData(form);

            fetch(form.action, {
                method: form.method || 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            }).then(async function (response) {
                const payload = await response.json().catch(() => ({}));

                if (response.ok && payload.success === true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro exitoso',
                        text: 'Empleado registrado correctamente',
                        confirmButtonColor: '#10b981'
                    }).then(function () {
                        window.dispatchEvent(new CustomEvent('step3-success'));
                    });
                    return;
                }

                if (response.status === 422) {
                    showServerValidationErrors(form, payload.errors || {});
                    validarFormularioStep(form, submitButton, false);

                    if (payload?.errors?.general?.[0]) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al registrar',
                            text: payload.errors.general[0],
                            confirmButtonColor: '#ef4444'
                        });
                    }
                    return;
                }

                if (response.status === 400) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sesión expirada',
                        text: payload?.errors?.general?.[0] || payload?.message || 'Sesión expirada',
                        confirmButtonColor: '#ef4444'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error al registrar',
                    text: payload?.errors?.general?.[0] || payload?.message || 'Error al registrar empleado',
                    confirmButtonColor: '#ef4444'
                });
            }).catch(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error inesperado al registrar el empleado',
                    confirmButtonColor: '#ef4444'
                });
            });
        });

        validarFormularioStep(form, submitButton, false);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initStep1Validation();
        initStep2Validation();
        initStep3Validation();
    });

    window.employeeValidation = {
        refreshAll: function () {
            ['step1', 'step2', 'step3'].forEach(function (id) {
                const form = document.getElementById(id);
                if (!form) {
                    return;
                }

                const button = form.querySelector('button[type="submit"]');
                validarFormularioStep(form, button, false);
            });
        },
        validarInput,
        validarFormularioStep,
        initStep1Validation,
        initStep2Validation,
        initStep3Validation
    };
})();
