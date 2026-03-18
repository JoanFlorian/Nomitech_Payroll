(function () {
    const LETTERS_REGEX = /^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/;
    const NUMBERS_REGEX = /^[0-9]+$/;
    const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const ACCOUNT_REGEX = /^[0-9]{6,20}$/;
    const INTERNAL_CODE_REGEX = /^[0-9]+$/;
    const ADDRESS_REGEX = /^(?=.*[A-Za-z])(?=.*(calle|carrera|cra\.?|cl\.?|av\.?|avenida|transversal|diagonal|#|no\.?)).+$/i;
    const EMPLOYEE_SMMLV = Number(window.employeeValidationRules?.smmlv ?? 0);
    const SALARY_FORMATTER = new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

    function parseLocalizedNumber(rawValue) {
        if (typeof rawValue === 'number') return rawValue;

        const value = (rawValue || '').toString().trim();
        if (value === '') {
            return NaN;
        }

        // Detectar si es un formato numérico crudo (ej: "1300000.00" de DB)
        const isRawFloat = /^-?\d+\.\d+$/.test(value);
        if (isRawFloat) {
            return parseFloat(value);
        }

        const isRawInt = /^-?\d+$/.test(value);
        if (isRawInt) {
            return parseInt(value, 10);
        }

        // Limpieza de formato localizado centralizada
        const sanitized = value.replace(/\s+/g, '').replace(/\./g, '').replace(',', '.').replace(/[^\d.-]/g, '');
        const parsed = parseFloat(sanitized);
        return Number.isFinite(parsed) ? parsed : NaN;
    }

    function formatLocalizedNumber(rawValue) {
        const numericValue = parseLocalizedNumber(rawValue);
        if (!Number.isFinite(numericValue)) {
            return '';
        }

        return SALARY_FORMATTER.format(numericValue);
    }

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

    function resolveAprendizStage(form) {
        const etapaInput = getField(form, 'etapa_aprendiz');
        const etapaValue = (etapaInput ? etapaInput.value : '').toString().trim().toLowerCase();

        if (etapaValue.includes('lectiva')) {
            return 'lectiva';
        }

        if (etapaValue.includes('productiva')) {
            return 'productiva';
        }

        const tipoTrabajador = getField(form, 'id_tipo_trabajador');
        const tipoTrabajadorValue = (tipoTrabajador ? tipoTrabajador.value : '').toString().trim();

        if (tipoTrabajadorValue === '12') {
            return 'lectiva';
        }

        if (tipoTrabajadorValue === '19') {
            return 'productiva';
        }

        const selectedText = tipoTrabajador && tipoTrabajador.selectedOptions && tipoTrabajador.selectedOptions[0]
            ? (tipoTrabajador.selectedOptions[0].textContent || '').toString().toLowerCase()
            : '';

        if (selectedText.includes('lectiva')) {
            return 'lectiva';
        }

        if (selectedText.includes('productiva')) {
            return 'productiva';
        }

        return null;
    }

    function isIndefiniteContractSelected(contractInput) {
        if (!contractInput) {
            return false;
        }

        const selectedOption = contractInput.selectedOptions && contractInput.selectedOptions[0]
            ? contractInput.selectedOptions[0]
            : null;

        if (!selectedOption) {
            return false;
        }

        const optionText = (selectedOption.textContent || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        return optionText.includes('indefinid');
    }

    function isSalaryExemptContractSelected(contractInput) {
        if (!contractInput) {
            return false;
        }

        const selectedOption = contractInput.selectedOptions && contractInput.selectedOptions[0]
            ? contractInput.selectedOptions[0]
            : null;

        if (!selectedOption) {
            return false;
        }

        const optionText = (selectedOption.textContent || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();

        if (optionText.includes('prestacion') && optionText.includes('servicio')) {
            return true;
        }

        if (optionText.includes('obra')) {
            return true;
        }

        if (optionText.includes('labor')) {
            return true;
        }

        return false;
    }

    function getRiskLevelNumber(rawValue) {
        const value = (rawValue || '').toString().trim().toUpperCase();
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

    function syncRiskClassification(form) {
        const nivelRiesgoInput = getField(form, 'nivel_riesgo');
        const altoRiesgoInput = getField(form, 'alto_riesgo');
        const bajoRiesgoInput = getField(form, 'bajo_riesgo');

        if (!nivelRiesgoInput || !altoRiesgoInput || !bajoRiesgoInput) {
            return;
        }

        const riskLevel = getRiskLevelNumber(nivelRiesgoInput.value);

        if (riskLevel === null) {
            altoRiesgoInput.checked = false;
            bajoRiesgoInput.checked = false;
            return;
        }

        if (riskLevel >= 3) {
            altoRiesgoInput.checked = true;
            bajoRiesgoInput.checked = false;
            return;
        }

        altoRiesgoInput.checked = false;
        bajoRiesgoInput.checked = true;
    }

    function isCashFormaPagoSelected(formaInput) {
        if (!formaInput) {
            return false;
        }

        const selectedOption = formaInput.selectedOptions && formaInput.selectedOptions[0]
            ? formaInput.selectedOptions[0]
            : null;

        if (!selectedOption) {
            return false;
        }

        const optionText = (selectedOption.textContent || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();

        // Considera 'efectivo' o 'contado' como pago en efectivo
        return optionText.includes('efectivo') || optionText.includes('contado');
    }

    function syncFieldsByFormaPago(form) {
        const formaPagoInput = getField(form, 'id_forma_pago');
        const tipoCuentaInput = getField(form, 'tipo_cuenta');
        const numeroCuentaInput = getField(form, 'numero_cuenta');

        const paymentFields = [tipoCuentaInput, numeroCuentaInput].filter(Boolean);

        if (!formaPagoInput || paymentFields.length === 0) {
            return;
        }

        const isCash = isCashFormaPagoSelected(formaPagoInput);

        paymentFields.forEach((field) => {
            if (isCash) {
                field.value = '';
                field.setAttribute('disabled', 'disabled');
                field.classList.add('bg-gray-100', 'cursor-not-allowed');
                clearFieldError(field);
                return;
            }

            field.removeAttribute('disabled');
            field.classList.remove('bg-gray-100', 'cursor-not-allowed');
        });
    }

    function validateStep1Field(form, fieldName, showError = true) {
        const input = getField(form, fieldName);
        const value = input ? (input.value || '').trim() : '';

        switch (fieldName) {
            case 'id_tipo_doc':
                if (!input) {
                    return false;
                }

                if (value !== '') {
                    if (showError) {
                        clearFieldError(input);
                    }
                    return true;
                }

                if (showError) {
                    setFieldError(input, 'El tipo de documento es obligatorio.');
                }

                return false;
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
                {
                    const contractInput = getField(form, 'id_tipo_contrato');
                    const isIndefiniteContract = isIndefiniteContractSelected(contractInput);

                    if (isIndefiniteContract) {
                        return validarInput(
                            input,
                            value === '',
                            'Para contrato indefinido no debe registrar fecha de fin.',
                            showError
                        );
                    }
                }

                if (value === '') {
                    if (showError) {
                        clearFieldError(input);
                    }
                    return true;
                }

                if (!fechaInicioValue) {
                    return validarInput(input, false, 'Debe ingresar primero la fecha de inicio.', showError);
                }

                return validarInput(input, value > fechaInicioValue, 'La fecha fin debe ser posterior a la fecha de inicio.', showError);
            case 'id_tipo_contrato':
                return validarInput(input, value !== '', 'El tipo de contrato es obligatorio.', showError);
            case 'nivel_riesgo':
                return validarInput(input, value !== '', 'El nivel de riesgo es obligatorio.', showError);
            case 'salario':
                if (value === '') {
                    return validarInput(input, false, 'El salario es obligatorio.', showError);
                }

                const salario = parseLocalizedNumber(value);
                if (Number.isNaN(salario) || salario < 0 || salario > 999999999) {
                    return validarInput(input, false, 'El salario debe estar entre 0 y 999999999.', showError);
                }

                const contractInput = getField(form, 'id_tipo_contrato');
                const contractId = Number(contractInput ? contractInput.value : 0);

                // Excepciones: prestación de servicios y obra/labor.
                if (isSalaryExemptContractSelected(contractInput)) {
                    return validarInput(input, true, '', showError);
                }

                // Contrato de aprendizaje tiene reglas especiales
                if (contractId === 4) {
                    const etapaAprendiz = resolveAprendizStage(form);

                    if (!etapaAprendiz) {
                        return validarInput(input, false, 'Para contrato de aprendizaje debe indicar la etapa del aprendiz (lectiva o productiva).', showError);
                    }

                    if (etapaAprendiz === 'lectiva') {
                        const minimoLectiva = EMPLOYEE_SMMLV > 0 ? EMPLOYEE_SMMLV * 0.75 : 0;
                        return validarInput(input, salario >= minimoLectiva, 'Para etapa lectiva, el salario base no puede ser inferior al 75% del salario mínimo legal vigente.', showError);
                    }

                    return validarInput(input, EMPLOYEE_SMMLV > 0 ? salario >= EMPLOYEE_SMMLV : true, 'Para etapa productiva, el salario base no puede ser inferior al salario mínimo legal vigente.', showError);
                }

                // Todos los demás tipos de contrato requieren salario >= SMMLV
                // 1: Término indefinido, 2: Término fijo, 5: Prácticas, etc.
                return validarInput(
                    input,
                    EMPLOYEE_SMMLV > 0 ? salario >= EMPLOYEE_SMMLV : true,
                    'El salario base no puede ser inferior al salario mínimo legal vigente para este tipo de contrato.',
                    showError
                );
            case 'id_tipo_trabajador':
                return validarInput(input, value !== '', 'El tipo de trabajador es obligatorio.', showError);
            case 'id_sub_tipo_trabajador':
                return validarInput(input, value !== '', 'El subtipo de trabajador es obligatorio.', showError);
            case 'id_arl':
                return validarInput(input, value !== '', 'La ARL es obligatoria.', showError);
            case 'horas_diarias':
                return validarInput(input, value !== '' && Number(value) >= 1 && Number(value) <= 12, 'Las horas diarias deben estar entre 1 y 12.', showError);
            case 'codigo_interno':
                if (value === '') {
                    if (showError) {
                        clearFieldError(input);
                    }
                    return true;
                }

                return validarInput(input, INTERNAL_CODE_REGEX.test(value) && value.length >= 3 && value.length <= 20, 'El código interno debe tener entre 3 y 20 dígitos numéricos.', showError);
            default:
                return true;
        }
    }

    function validateStep3Field(form, fieldName, showError = true) {
        const input = getField(form, fieldName);
        const value = input ? (input.value || '').trim() : '';
        const formaPagoInput = getField(form, 'id_forma_pago');
        const isCashFormaPago = isCashFormaPagoSelected(formaPagoInput);

        switch (fieldName) {
            case 'id_forma_pago':
                return validarInput(input, value !== '', 'La forma de pago es obligatoria.', showError);
            case 'tipo_cuenta':
                // Si forma de pago es efectivo, tipo de cuenta no es requerido
                if (isCashFormaPago) {
                    if (showError) {
                        clearFieldError(input);
                    }
                    return true;
                }
                return validarInput(input, value !== '', 'El tipo de cuenta es obligatorio.', showError);
            case 'numero_cuenta':
                // Si forma de pago es efectivo, número de cuenta no es requerido
                if (isCashFormaPago) {
                    if (showError) {
                        clearFieldError(input);
                    }
                    return true;
                }
                return validarInput(input, value !== '' && ACCOUNT_REGEX.test(value), 'El número de cuenta debe tener entre 6 y 20 dígitos numéricos.', showError);
            case 'id_eps':
                return validarInput(input, value !== '', 'La EPS es obligatoria.', showError);
            case 'id_afp':
                return validarInput(input, value !== '', 'La AFP es obligatoria.', showError);
            case 'id_caja':
                return validarInput(input, value !== '', 'La caja de compensación es obligatoria.', showError);
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
            return ['id_forma_pago', 'tipo_cuenta', 'numero_cuenta', 'id_eps', 'id_afp', 'id_caja'];
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
        const salaryInput = getField(form, 'salario');
        if (salaryInput) {
            const parsedSalary = parseLocalizedNumber(salaryInput.value);
            if (Number.isFinite(parsedSalary)) {
                formData.set('salario', String(parsedSalary));
            }
        }

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

        ['primer_nombre', 'otros_nombres', 'primer_apellido', 'segundo_apellido'].forEach(function (fieldName) {
            const fieldInput = getField(form, fieldName);
            if (!fieldInput) {
                return;
            }

            fieldInput.addEventListener('input', function () {
                // Keep only letters (including accents) and spaces.
                fieldInput.value = (fieldInput.value || '').replace(/[^a-zA-ZÁÉÍÓÚáéíóúñÑ\s]/g, '');
            });
        });

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
        const fechaInicioInput = getField(form, 'fecha_inicio');
        const fechaFinInput = getField(form, 'fecha_fin');
        const fechaFinHint = document.getElementById('fecha_fin_hint');
        const tipoContratoInput = getField(form, 'id_tipo_contrato');

        function updateFechaFinMin() {
            if (!fechaFinInput) {
                return;
            }

            if (isIndefiniteContractSelected(tipoContratoInput)) {
                fechaFinInput.removeAttribute('min');
                return;
            }

            const fechaInicioValue = fechaInicioInput ? (fechaInicioInput.value || '').trim() : '';
            if (!fechaInicioValue) {
                fechaFinInput.removeAttribute('min');
                return;
            }

            const nextDate = new Date(`${fechaInicioValue}T00:00:00`);
            nextDate.setDate(nextDate.getDate() + 1);
            const minFechaFin = nextDate.toISOString().split('T')[0];
            fechaFinInput.setAttribute('min', minFechaFin);

            const fechaFinValue = (fechaFinInput.value || '').trim();
            if (fechaFinValue !== '' && fechaFinValue <= fechaInicioValue) {
                fechaFinInput.value = '';
                clearFieldError(fechaFinInput);
            }
        }

        function syncFechaFinByContractType() {
            if (!fechaFinInput) {
                return;
            }

            const isIndefiniteContract = isIndefiniteContractSelected(tipoContratoInput);

            if (isIndefiniteContract) {
                fechaFinInput.value = '';
                fechaFinInput.setAttribute('disabled', 'disabled');
                fechaFinInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                clearFieldError(fechaFinInput);

                if (fechaFinHint) {
                    fechaFinHint.textContent = 'Contrato indefinido: no debe registrar fecha de fin.';
                }

                return;
            }

            fechaFinInput.removeAttribute('disabled');
            fechaFinInput.classList.remove('bg-gray-100', 'cursor-not-allowed');

            if (fechaFinHint) {
                fechaFinHint.textContent = 'Debe ser posterior a la fecha de inicio.';
            }

            updateFechaFinMin();
        }

        if (fechaInicioInput) {
            fechaInicioInput.addEventListener('change', updateFechaFinMin);
            fechaInicioInput.addEventListener('input', updateFechaFinMin);
        }

        const tipoTrabajadorInput = getField(form, 'id_tipo_trabajador');
        const salarioInput = getField(form, 'salario');
        const codigoInternoInput = getField(form, 'codigo_interno');
        const nivelRiesgoInput = getField(form, 'nivel_riesgo');
        const altoRiesgoInput = getField(form, 'alto_riesgo');
        const bajoRiesgoInput = getField(form, 'bajo_riesgo');

        if (codigoInternoInput) {
            codigoInternoInput.addEventListener('input', function () {
                codigoInternoInput.value = (codigoInternoInput.value || '').replace(/\D/g, '').slice(0, 20);
            });
        }

        if (salarioInput) {
            salarioInput.addEventListener('input', function () {
                const formatted = formatLocalizedNumber(salarioInput.value);
                salarioInput.value = formatted;
            });

            salarioInput.addEventListener('blur', function () {
                const formatted = formatLocalizedNumber(salarioInput.value);
                salarioInput.value = formatted;
            });

            salarioInput.value = formatLocalizedNumber(salarioInput.value);
        }

        if (tipoContratoInput) {
            tipoContratoInput.addEventListener('change', function () {
                syncFechaFinByContractType();
                validateStep2Field(form, 'fecha_fin', true);
                if (salarioInput) {
                    validateStep2Field(form, 'salario', true);
                }
            });
        }

        if (tipoTrabajadorInput) {
            tipoTrabajadorInput.addEventListener('change', function () {
                const contractId = Number((tipoContratoInput ? tipoContratoInput.value : '').toString().trim() || 0);
                if (contractId === 4 && salarioInput) {
                    validateStep2Field(form, 'salario', true);
                }
            });
        }

        if (nivelRiesgoInput) {
            nivelRiesgoInput.addEventListener('change', function () {
                syncRiskClassification(form);
            });
        }

        [altoRiesgoInput, bajoRiesgoInput].filter(Boolean).forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                syncRiskClassification(form);
            });
        });

        syncFechaFinByContractType();
        syncRiskClassification(form);
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
        const numeroCuentaInput = getField(form, 'numero_cuenta');
        const formaPagoInput = getField(form, 'id_forma_pago');

        if (numeroCuentaInput) {
            numeroCuentaInput.addEventListener('input', function () {
                numeroCuentaInput.value = (numeroCuentaInput.value || '').replace(/\D/g, '').slice(0, 20);
            });
        }

        // Listener para forma de pago - bloquea campos cuando es efectivo
        if (formaPagoInput) {
            formaPagoInput.addEventListener('change', function () {
                syncFieldsByFormaPago(form);
                validateStep3Field(form, 'tipo_cuenta', true);
                validateStep3Field(form, 'numero_cuenta', true);
            });
        }

        initCommonRealtimeValidation(form);
        syncFieldsByFormaPago(form);

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
