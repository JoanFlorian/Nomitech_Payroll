<script>
const $ = (id) => document.getElementById(id);
const empleadoInput = $('empleado_busqueda');
const docInput = $('doc');
const fechaInput = $('fecha_pago');
const idContratoInput = $('id_contrato');
const form = $('formNomina');
const spinner = $('loadingSpinner');
const errorMsg = $('errorMsg');
const fechaError = $('fechaError');
const box = $('sugerenciasEmpleados');
const list = $('listaSugerencias');
const isEditingNomina = @json((bool)($isEditing ?? false));
const buscarEmpleadosUrl = @json(url('/nomina/buscar-empleados'));
const buscarEmpleadoBaseUrl = @json(url('/nomina/buscar-empleado'));
const formatCOP = (n) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP' }).format(n);

let timer = null;
let lastResults = [];
let selectedEmployee = null;

const hideErrors = () => [errorMsg, fechaError].forEach(el => el.classList.add('hidden'));
const showError = (text) => { errorMsg.textContent = text; errorMsg.classList.remove('hidden'); };
const showFechaError = (text) => { fechaError.textContent = text; fechaError.classList.remove('hidden'); };
const markNeutral = (el) => { el.classList.remove('border-red-500','border-green-500','focus:border-red-500','focus:border-green-500'); el.classList.add('border-gray-300','focus:border-blue-500'); };
const markError = (el) => { el.classList.remove('border-gray-300','border-green-500','focus:border-blue-500','focus:border-green-500'); el.classList.add('border-red-500','focus:border-red-500'); };
const markOk = (el) => { el.classList.remove('border-gray-300','border-red-500','focus:border-blue-500','focus:border-red-500'); el.classList.add('border-green-500','focus:border-green-500'); };

function showAlert(text) {
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
    card.style.transform = 'translateY(6px) scale(0.98)';
    card.style.opacity = '0';
    card.style.transition = 'all 150ms ease-out';

    card.innerHTML = `
        <div style="height: 6px; background: linear-gradient(90deg, #fbbf24 0%, #fb923c 55%, #ef4444 100%);"></div>
        <div style="padding: 20px;">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <div style="height: 32px; width: 32px; min-width: 32px; border-radius: 999px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-weight: 700;">!</div>
                <div style="flex: 1; min-width: 0;">
                    <h4 style="margin: 0; font-size: 14px; line-height: 20px; color: #0f172a; font-weight: 700;">Validacion requerida</h4>
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
    const closeButton = overlay.querySelector('[data-alert-close]');
    if (message) message.textContent = text;

    const closeAlert = () => {
        overlay.remove();
        empleadoInput.focus();
    };

    if (closeButton) {
        closeButton.addEventListener('click', closeAlert);
        closeButton.focus();
    }

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeAlert();
    });
}

function clearEmployeeData() {
    docInput.value = '';
    $('nombre').value = '';
    $('telefono').value = '';
    $('salario_base').value = '';
    idContratoInput.value = '';
}

function renderSuggestions(items) {
    lastResults = items;
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

    box.classList.remove('hidden');
}

function selectEmployee(emp) {
    empleadoInput.value = `${emp.nombre || ''} - ${emp.doc}`.trim();
    docInput.value = emp.doc || '';
    idContratoInput.value = emp.id_contrato || '';
    selectedEmployee = {
        doc: emp.doc || '',
        id_contrato: emp.id_contrato || ''
    };
    $('nombre').value = emp.nombre || '';
    $('telefono').value = emp.telefono || '';
    $('salario_base').value = emp.salario_base ? formatCOP(emp.salario_base) : '';
    if (!fechaInput.value) fechaInput.value = new Date().toISOString().split('T')[0];
    box.classList.add('hidden');
    hideErrors();
    markOk(empleadoInput);
}

async function fetchEmployees(term = '') {
    spinner.classList.remove('hidden');
    try {
        const url = new URL(buscarEmpleadosUrl, window.location.origin);
        url.searchParams.set('q', term);
        const resp = await fetch(url.toString());
        if (!resp.ok) throw new Error('request failed');
        const data = await resp.json();
        renderSuggestions(Array.isArray(data) ? data : []);
    } catch (e) {
        box.classList.add('hidden');
        showError('No se pudieron cargar los empleados.');
    } finally {
        spinner.classList.add('hidden');
    }
}

async function hydrateSavedEmployee() {
    if (!docInput.value) return;
    if (docInput.value && idContratoInput.value) {
        selectedEmployee = {
            doc: docInput.value,
            id_contrato: idContratoInput.value
        };
    }
    if ($('nombre').value && $('telefono').value && $('salario_base').value) {
        markOk(empleadoInput);
        return;
    }

    try {
        const resp = await fetch(`${buscarEmpleadoBaseUrl}/${encodeURIComponent(docInput.value)}`);
        if (!resp.ok) return;
        const emp = await resp.json();
        if (!emp) return;
        selectEmployee(emp);
    } catch (e) {
        // Si falla, conserva los valores actuales del formulario
    }
}

function validateEmployee() {
    const hasValidSelection = Boolean(
        selectedEmployee &&
        selectedEmployee.doc &&
        selectedEmployee.id_contrato &&
        docInput.value &&
        idContratoInput.value &&
        String(selectedEmployee.doc) === String(docInput.value) &&
        String(selectedEmployee.id_contrato) === String(idContratoInput.value)
    );

    if (hasValidSelection) return markOk(empleadoInput), true;
    markError(empleadoInput);
    showError('Debes seleccionar un empleado válido de la lista.');
    return false;
}

function validateFecha() {
    if (!fechaInput.value) return markError(fechaInput), showFechaError('La fecha de pago es obligatoria.'), false;
    if (isEditingNomina) {
        markOk(fechaInput);
        fechaError.classList.add('hidden');
        return true;
    }
    const today = new Date(); today.setHours(0,0,0,0);
    const fecha = new Date(`${fechaInput.value}T00:00:00`);
    if (fecha < today) return markError(fechaInput), showFechaError('La fecha de pago no puede ser menor a hoy.'), false;
    markOk(fechaInput); fechaError.classList.add('hidden');
    return true;
}

if (!isEditingNomina) {
    empleadoInput.addEventListener('focus', () => fetchEmployees(empleadoInput.value.trim()));
    empleadoInput.addEventListener('input', () => {
        selectedEmployee = null;
        clearEmployeeData();
        hideErrors();
        markNeutral(empleadoInput);
        clearTimeout(timer);
        timer = setTimeout(() => fetchEmployees(empleadoInput.value.trim()), 220);
    });
}

if (!isEditingNomina) {
    list.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-index]');
        if (!btn) return;
        selectEmployee(lastResults[Number(btn.dataset.index)]);
    });
}

document.addEventListener('click', (e) => {
    if (!box.contains(e.target) && e.target !== empleadoInput) box.classList.add('hidden');
});

fechaInput.addEventListener('change', validateFecha);
form.addEventListener('submit', (e) => {
    hideErrors();
    const employeeValid = validateEmployee();
    const fechaValid = validateFecha();
    if (!(employeeValid && fechaValid)) {
        e.preventDefault();
        if (!employeeValid) {
            showAlert('Debes seleccionar un empleado válido de la lista.');
        }
    }
});

hydrateSavedEmployee();
</script>
