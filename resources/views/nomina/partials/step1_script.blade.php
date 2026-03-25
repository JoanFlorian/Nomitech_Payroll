<script>
    const $ = (id) => document.getElementById(id);
    const empleadoInput = $('empleado_busqueda');
    const docInput = $('doc');
    const fechaInput = $('fecha_pago');
    const diasInput = $('dias_trabajados');
    const idContratoInput = $('id_contrato');
    const form = $('formNomina');
    const spinner = $('loadingSpinner');
    const errorMsg = $('errorMsg');
    const fechaError = $('fechaError');
    const diasError = $('diasError');
    const box = $('sugerenciasEmpleados');
    const list = $('listaSugerencias');
    const resumenValorDia = $('resumen_valor_dia');
    const resumenDiasTrabajados = $('resumen_dias_trabajados');
    const resumenSalarioProporcional = $('resumen_salario_proporcional');
    const isEditingNomina = @json((bool) ($isEditing ?? false));
    const formatCOP = (n) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(n || 0);

    const buscarEmpleadosUrl = @json(url('/nomina/buscar-empleados'));
    const buscarEmpleadoBaseUrl = @json(url('/nomina/buscar-empleado'));
    const validarDuplicadoBaseUrl = @json(url('/nomina/validar-duplicado'));

    let timer = null;
    let lastResults = [];
    let selectedEmployee = null;

    const hideErrors = () => [errorMsg, fechaError, diasError].forEach(el => el && el.classList.add('hidden'));
    const showError = (text) => { if (errorMsg) { errorMsg.textContent = text; errorMsg.classList.remove('hidden'); } };
    const showFechaError = (text) => { if (fechaError) { fechaError.textContent = text; fechaError.classList.remove('hidden'); } };
    const showDiasError = (text) => { if (diasError) { diasError.textContent = text; diasError.classList.remove('hidden'); } };
    const markNeutral = (el) => { if (el) { el.classList.remove('border-red-500', 'border-green-500', 'focus:border-red-500', 'focus:border-green-500'); el.classList.add('border-gray-300', 'focus:border-blue-500'); } };
    const markError = (el) => { if (el) { el.classList.remove('border-gray-300', 'border-green-500', 'focus:border-blue-500', 'focus:border-green-500'); el.classList.add('border-red-500', 'focus:border-red-500'); } };
    const markOk = (el) => { if (el) { el.classList.remove('border-gray-300', 'border-red-500', 'focus:border-blue-500', 'focus:border-red-500'); el.classList.add('border-green-500', 'focus:border-green-500'); } };

    function parseCOP(value) {
        if (!value) return 0;
        const cleaned = String(value).replace(/[^\d,.-]/g, '').trim();
        if (!cleaned) return 0;
        const normalized = cleaned.replace(/\./g, '').replace(',', '.');
        const number = Number(normalized);
        return Number.isFinite(number) ? number : 0;
    }

    function actualizarResumenProporcional() {
        const salarioBase = parseCOP($('salario_base')?.value || 0);
        const maxDiasSln = Number(diasInput?.dataset.maxSln || 30);
        let dias = Number(diasInput?.value || 0);
        if (!Number.isFinite(dias)) dias = 0;
        dias = Math.max(0, Math.min(maxDiasSln, Math.trunc(dias)));
        if (diasInput) diasInput.value = String(dias);

        const valorDia = salarioBase / 30;
        const salarioProporcional = valorDia * dias;

        if (resumenValorDia) resumenValorDia.textContent = formatCOP(valorDia);
        if (resumenDiasTrabajados) resumenDiasTrabajados.textContent = String(dias);
        if (resumenSalarioProporcional) resumenSalarioProporcional.textContent = formatCOP(salarioProporcional);
    }

    function showAlert(text, title = 'Validacion requerida') {
        const existing = document.getElementById('nominaCustomAlert');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.id = 'nominaCustomAlert';
        overlay.style.position = 'fixed';
        overlay.style.inset = '0';
        overlay.style.zIndex = '9999';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.padding = '16px';
        overlay.style.background = 'rgba(15, 23, 42, 0.45)';

        const card = document.createElement('div');
        card.style.width = '100%';
        card.style.maxWidth = '460px';
        card.style.background = '#ffffff';
        card.style.border = '1px solid #e2e8f0';
        card.style.borderRadius = '16px';
        card.style.boxShadow = '0 20px 45px rgba(15, 23, 42, 0.28)';
        card.style.overflow = 'hidden';
        card.style.transform = 'translateY(10px) scale(0.97)';
        card.style.opacity = '0';
        card.style.transition = 'all 180ms ease-out';

        card.innerHTML = `
    <div style="height: 6px; background: linear-gradient(90deg, #fbbf24 0%, #fb923c 55%, #ef4444 100%);"></div>
    <div style="padding: 20px;">
        <div style="display: flex; gap: 12px; align-items: flex-start;">
            <div style="height: 32px; width: 32px; min-width: 32px; border-radius: 999px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-weight: 700;">!</div>
            <div style="flex: 1; min-width: 0;">
                <h4 id="nominaCustomAlertTitle" style="margin: 0; font-size: 14px; line-height: 20px; color: #0f172a; font-weight: 700;">Validacion requerida</h4>
                <p id="nominaCustomAlertMessage" style="margin: 6px 0 0; font-size: 14px; line-height: 20px; color: #475569;"></p>
            </div>
        </div>
        <div style="margin-top: 18px; display: flex; justify-content: flex-end;">
            <button type="button" data-alert-close style="padding: 10px 16px; border: 0; border-radius: 10px; background: #2563eb; color: #fff; font-size: 14px; font-weight: 700; cursor: pointer;">
                Aceptar
            </button>
        </div>
    </div>
    `;

        overlay.appendChild(card);
        document.body.appendChild(overlay);

        requestAnimationFrame(() => {
            card.style.transform = 'translateY(0) scale(1)';
            card.style.opacity = '1';
        });

        const message = overlay.querySelector('#nominaCustomAlertMessage');
            const titleEl = overlay.querySelector('#nominaCustomAlertTitle');
        const closeButton = overlay.querySelector('[data-alert-close]');
            if (titleEl) titleEl.textContent = title;
        if (message) message.textContent = text;

        const closeAlert = () => {
            overlay.remove();
            if (empleadoInput) empleadoInput.focus();
        };

        if (closeButton) {
            closeButton.addEventListener('click', closeAlert);
            closeButton.focus();
        }

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeAlert();
        });
    }

    function normalizeEmployeeSearchInput(value) {
        const raw = String(value || '');
        const firstChar = (raw.match(/[^\s]/) || [''])[0];

        // Si inicia con numero, interpretamos busqueda por documento.
        if (/\d/.test(firstChar)) {
            return raw.replace(/\D/g, '');
        }

        // Si inicia con letra, interpretamos busqueda por nombre.
        if (/[A-Za-zÁÉÍÓÚáéíóúÑñ]/.test(firstChar)) {
            return raw
                .replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '')
                .replace(/\s{2,}/g, ' ')
                .trimStart();
        }

        // Fallback: quitar simbolos, mantener letras/numeros/espacios.
        return raw
            .replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]/g, '')
            .replace(/\s{2,}/g, ' ')
            .trimStart();
    }

    function enforceNumericInput(el, max = null) {
        if (!el) return;
        const onlyDigits = String(el.value || '').replace(/\D/g, '');
        if (onlyDigits === '') {
            el.value = '';
            return;
        }
        let n = Number(onlyDigits);
        if (!Number.isFinite(n)) {
            el.value = '';
            return;
        }
        if (max !== null) {
            n = Math.min(max, n);
        }
        el.value = String(n);
    }

    function clearEmployeeData() {
        if (docInput) docInput.value = '';
        if ($('nombre')) $('nombre').value = '';
        if ($('telefono')) $('telefono').value = '';
        if ($('salario_base')) $('salario_base').value = '';
        if (idContratoInput) idContratoInput.value = '';
        actualizarResumenProporcional();
    }

    function renderSuggestions(items) {
        lastResults = items;
        if (list) {
            list.innerHTML = items.length
                ? items.map((emp, i) => `
                <li>
                    <button type="button" data-index="${i}" class="w-full text-left px-4 py-2.5 hover:bg-blue-50 border-b border-gray-100 last:border-b-0">
                        <div class="text-sm font-medium text-gray-800">${emp.nombre || 'Sin nombre'}</div>
                        <div class="text-xs text-gray-500">Doc: ${emp.doc}</div>
                    </button>
                </li>
            `).join('')
                : '<li class="px-4 py-3 text-sm text-gray-500">No se encontraron empleados activos.</li>';
        }
        if (box) box.classList.remove('hidden');
    }

    async function selectEmployee(emp) {
        if (isEditingNomina) {
            // En edición no comprobamos duplicados del mismo modo habitual
            if (empleadoInput) empleadoInput.value = `${emp.nombre || ''} - ${emp.doc}`.trim();
            return;
        }

        if (spinner) spinner.classList.remove('hidden');
        try {
            // Obtener el detalle del empleado para extraer dias_sugeridos
            try {
                const detalladoResp = await fetch(`${buscarEmpleadoBaseUrl}/${encodeURIComponent(emp.doc)}`);
                if (detalladoResp.ok) {
                    const detallado = await detalladoResp.json();
                    if (detallado) {
                        emp = Object.assign({}, emp, detallado);
                    }
                }
            } catch (e) {
                console.warn('No se pudo obtener el detalle del empleado para prorrateo');
            }

            const resp = await fetch(`${validarDuplicadoBaseUrl}/${emp.id_contrato}`);
            const data = await resp.json();

            if (data.duplicate) {
                showAlert(`El empleado ${emp.nombre} ya se encuentra registrado en el periodo de liquidación actual.`);
                clearEmployeeData();
                if (empleadoInput) {
                    empleadoInput.value = '';
                    markNeutral(empleadoInput);
                }
                return;
            }

            if (empleadoInput) empleadoInput.value = `${emp.nombre || ''} - ${emp.doc}`.trim();
            if (docInput) docInput.value = emp.doc || '';
            if (idContratoInput) idContratoInput.value = emp.id_contrato || '';

            selectedEmployee = {
                doc: emp.doc || '',
                id_contrato: emp.id_contrato || ''
            };

            if ($('nombre')) $('nombre').value = emp.nombre || '';
            if ($('telefono')) $('telefono').value = emp.telefono || '';
            if ($('salario_base')) $('salario_base').value = emp.salario_base ? formatCOP(emp.salario_base) : '';

            if (emp.dias_sugeridos !== undefined && emp.dias_sugeridos !== null && diasInput) {
                diasInput.value = emp.dias_sugeridos;
            }

            if (fechaInput && !fechaInput.value) fechaInput.value = new Date().toISOString().split('T')[0];

            if (box) box.classList.add('hidden');
            hideErrors();
            if (empleadoInput) markOk(empleadoInput);
            actualizarResumenProporcional();

        } catch (e) {
            console.error('Error validando duplicado:', e);
        } finally {
            if (spinner) spinner.classList.add('hidden');
        }
    }

    async function fetchEmployees(term = '') {
        if (spinner) spinner.classList.remove('hidden');
        try {
            const resp = await fetch(`${buscarEmpleadosUrl}?q=${encodeURIComponent(term)}&exclude_liquidados=1`);
            if (!resp.ok) throw new Error('request failed');
            const data = await resp.json();
            const items = Array.isArray(data) ? data : [];
            renderSuggestions(items);

            // Si no hay sugerencias y parece documento exacto, avisar si ya esta registrado en el periodo activo.
            const termTrimmed = String(term || '').trim();
            const looksLikeDoc = /^[0-9]{5,}$/.test(termTrimmed);

            if (items.length === 0 && looksLikeDoc) {
                try {
                    const empleadoResp = await fetch(`${buscarEmpleadoBaseUrl}/${encodeURIComponent(termTrimmed)}`);
                    if (!empleadoResp.ok) {
                        return;
                    }

                    const empleado = await empleadoResp.json();
                    if (!empleado || !empleado.id_contrato) {
                        return;
                    }

                    const dupResp = await fetch(`${validarDuplicadoBaseUrl}/${empleado.id_contrato}`);
                    if (!dupResp.ok) {
                        return;
                    }

                    const dupData = await dupResp.json();
                    if (dupData?.duplicate) {
                        showError('Este empleado ya tiene nómina registrada en el periodo activo y por eso no aparece en la lista.');
                    }
                } catch (innerError) {
                    // Si falla la verificacion adicional mantenemos el flujo normal del buscador.
                }
            }
        } catch (e) {
            if (box) box.classList.add('hidden');
            showError('No se pudieron cargar los empleados.');
        } finally {
            if (spinner) spinner.classList.add('hidden');
        }
    }

    async function hydrateSavedEmployee() {
        if (!docInput || !docInput.value) return;

        if (empleadoInput && !empleadoInput.value.trim() && $('nombre') && $('nombre').value) {
            empleadoInput.value = `${$('nombre').value} - ${docInput.value}`.trim();
        }

        if (docInput.value && idContratoInput && idContratoInput.value) {
            selectedEmployee = {
                doc: docInput.value,
                id_contrato: idContratoInput.value
            };
        }

        if ($('nombre') && $('nombre').value && $('telefono') && $('telefono').value && $('salario_base') && $('salario_base').value) {
            if (empleadoInput) markOk(empleadoInput);
            return;
        }

        try {
            const resp = await fetch(`${buscarEmpleadoBaseUrl}/${encodeURIComponent(docInput.value)}`);
            if (!resp.ok) return;
            const emp = await resp.json();
            if (!emp) return;

            if (empleadoInput) empleadoInput.value = `${emp.nombre || ''} - ${emp.doc}`.trim();
            if (idContratoInput) idContratoInput.value = emp.id_contrato || '';
            selectedEmployee = { doc: emp.doc || '', id_contrato: emp.id_contrato || '' };
            if ($('nombre')) $('nombre').value = emp.nombre || '';
            if ($('telefono')) $('telefono').value = emp.telefono || '';
            if ($('salario_base')) $('salario_base').value = emp.salario_base ? formatCOP(emp.salario_base) : '';
            
            if (emp.dias_sugeridos !== undefined && emp.dias_sugeridos !== null && diasInput) {
                diasInput.value = emp.dias_sugeridos;
            }

            if (empleadoInput) markOk(empleadoInput);
            actualizarResumenProporcional();

        } catch (e) {
            // Fallback
        }
    }

    function validateEmployee() {
        const normalized = normalizeEmployeeSearchInput(empleadoInput?.value || '');
        if (empleadoInput && empleadoInput.value !== normalized) {
            empleadoInput.value = normalized;
        }

        const hasValidSelection = Boolean(
            selectedEmployee &&
            selectedEmployee.doc &&
            selectedEmployee.id_contrato &&
            docInput && docInput.value &&
            idContratoInput && idContratoInput.value &&
            String(selectedEmployee.doc) === String(docInput.value) &&
            String(selectedEmployee.id_contrato) === String(idContratoInput.value)
        );

        const hasAutoFilledData = Boolean(
            docInput && docInput.value &&
            idContratoInput && idContratoInput.value &&
            $('nombre') && $('nombre').value &&
            $('salario_base') && $('salario_base').value
        );

        if (hasValidSelection || hasAutoFilledData) {
            if (!selectedEmployee && hasAutoFilledData) {
                selectedEmployee = {
                    doc: docInput.value,
                    id_contrato: idContratoInput.value
                };
            }
            if (empleadoInput) markOk(empleadoInput);
            return true;
        }

        if (!normalized) {
            if (empleadoInput) markError(empleadoInput);
            showError('Debes escribir un nombre o documento y seleccionar una opcion valida.');
            return false;
        }

        if (empleadoInput) markError(empleadoInput);
        showError('Debes seleccionar un empleado válido de la lista.');
        return false;
    }

    function validateFecha() {
        if (!fechaInput || !fechaInput.value) {
            if (fechaInput) markError(fechaInput);
            showFechaError('La fecha de pago es obligatoria.');
            return false;
        }
        if (isEditingNomina) {
            if (fechaInput) markOk(fechaInput);
            if (fechaError) fechaError.classList.add('hidden');
            return true;
        }
        const today = new Date(); today.setHours(0, 0, 0, 0);
        const fecha = new Date(`${fechaInput.value}T00:00:00`);
        if (fecha < today) {
            if (fechaInput) markError(fechaInput);
            showFechaError('La fecha de pago no puede ser menor a hoy.');
            return false;
        }
        if (fechaInput) markOk(fechaInput);
        if (fechaError) fechaError.classList.add('hidden');
        return true;
    }

    function validateDiasTrabajados() {
        if (!diasInput) return true;
        const maxDiasSln = Number(diasInput.dataset.maxSln || 30);
        const diasSln = Number(diasInput.dataset.diasSln || 0);
        let dias = Number(diasInput.value || 0);

        if (!Number.isFinite(dias)) {
            markError(diasInput);
            showDiasError(`Los dias trabajados deben ser un numero valido entre 0 y ${maxDiasSln}.`);
            return false;
        }

        dias = Math.trunc(dias);
        if (dias < 0 || dias > maxDiasSln) {
            markError(diasInput);
            if (diasSln > 0 && dias > maxDiasSln) {
                showDiasError(`El empleado tiene ${diasSln} días de suspensión (SLN). El máximo permitido es ${maxDiasSln} días.`);
            } else {
                showDiasError(`Los dias trabajados deben estar entre 0 y ${maxDiasSln}.`);
            }
            return false;
        }

        diasInput.value = String(dias);
        markOk(diasInput);
        if (diasError) diasError.classList.add('hidden');
        return true;
    }

    if (!isEditingNomina && empleadoInput) {
        empleadoInput.addEventListener('focus', () => fetchEmployees(empleadoInput.value.trim()));
        empleadoInput.addEventListener('input', () => {
            const normalized = normalizeEmployeeSearchInput(empleadoInput.value);
            if (empleadoInput.value !== normalized) {
                empleadoInput.value = normalized;
            }
            selectedEmployee = null;
            clearEmployeeData();
            hideErrors();
            markNeutral(empleadoInput);
            clearTimeout(timer);
            timer = setTimeout(() => fetchEmployees(empleadoInput.value.trim()), 220);
        });

        empleadoInput.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            const normalized = normalizeEmployeeSearchInput(text);
            empleadoInput.value = normalized;
            empleadoInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }

    if (!isEditingNomina && list) {
        list.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-index]');
            if (!btn) return;
            selectEmployee(lastResults[Number(btn.dataset.index)]);
        });
    }

    document.addEventListener('click', (e) => {
        if (box && empleadoInput && !box.contains(e.target) && e.target !== empleadoInput) {
            box.classList.add('hidden');
        }
    });

    if (fechaInput) {
        fechaInput.addEventListener('change', validateFecha);
    }

    if (diasInput) {
        const allowedControlKeys = new Set(['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End']);

        diasInput.addEventListener('keydown', (event) => {
            if (event.ctrlKey || event.metaKey || event.altKey || allowedControlKeys.has(event.key)) {
                return;
            }

            if (!/^[0-9]$/.test(event.key)) {
                event.preventDefault();
                markError(diasInput);
                showDiasError('Solo se permiten números enteros.');
            }
        });

        diasInput.addEventListener('beforeinput', (event) => {
            if (!event.data) {
                return;
            }

            if (!/^[0-9]+$/.test(event.data)) {
                event.preventDefault();
                markError(diasInput);
                showDiasError('Solo se permiten números enteros.');
            }
        });

        diasInput.addEventListener('input', () => {
            const maxDiasSln = Number(diasInput.dataset.maxSln || 30);
            enforceNumericInput(diasInput, maxDiasSln);
            if (diasError) diasError.classList.add('hidden');
            markNeutral(diasInput);
            actualizarResumenProporcional();
        });

        diasInput.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            const onlyDigits = String(text || '').replace(/\D/g, '');
            if (String(text || '').trim() !== '' && onlyDigits !== String(text || '')) {
                markError(diasInput);
                showDiasError('Solo se permiten números enteros.');
            }
            diasInput.value = String(text || '');
            const maxDiasSln = Number(diasInput.dataset.maxSln || 30);
            enforceNumericInput(diasInput, maxDiasSln);
            if (onlyDigits !== '') {
                if (diasError) diasError.classList.add('hidden');
                markNeutral(diasInput);
            }
            actualizarResumenProporcional();
        });

        diasInput.addEventListener('blur', () => {
            validateDiasTrabajados();
            actualizarResumenProporcional();
        });
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            hideErrors();
            const employeeValid = validateEmployee();
            const fechaValid = validateFecha();
            const diasValid = validateDiasTrabajados();
            
            console.log('=== FORM SUBMIT DEBUG ===');
            console.log('employeeValid:', employeeValid);
            console.log('fechaValid:', fechaValid);
            console.log('diasValid:', diasValid);
            console.log('selectedEmployee:', selectedEmployee);
            console.log('docInput.value:', docInput?.value);
            console.log('idContratoInput.value:', idContratoInput?.value);
            console.log('fechaInput.value:', fechaInput?.value);
            console.log('diasInput.value:', diasInput?.value);
            
            if (!(employeeValid && fechaValid && diasValid)) {
                e.preventDefault();
                if (!employeeValid) {
                    showAlert('Debes seleccionar un empleado válido de la lista.', 'Empleado invalido');
                } else if (!diasValid) {
                    const maxDiasSln = Number(diasInput?.dataset.maxSln || 30);
                    showAlert(`Ingresa los dias trabajados entre 0 y ${maxDiasSln}.`, 'Dias invalidos');
                } else if (!fechaValid) {
                    showAlert('La fecha de pago es obligatoria y no puede ser menor a hoy.', 'Fecha invalida');
                }
            } else {
                console.log('✓ FORM VALIDATION PASSED - SUBMITTING');
            }
        });
    }

    hydrateSavedEmployee();
    actualizarResumenProporcional();
</script>