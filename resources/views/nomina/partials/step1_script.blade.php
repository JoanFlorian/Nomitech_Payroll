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
const formatCOP = (n) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP' }).format(n);

let timer = null;
let lastResults = [];

const hideErrors = () => [errorMsg, fechaError].forEach(el => el.classList.add('hidden'));
const showError = (text) => { errorMsg.textContent = text; errorMsg.classList.remove('hidden'); };
const showFechaError = (text) => { fechaError.textContent = text; fechaError.classList.remove('hidden'); };
const markNeutral = (el) => { el.classList.remove('border-red-500','border-green-500','focus:border-red-500','focus:border-green-500'); el.classList.add('border-gray-300','focus:border-blue-500'); };
const markError = (el) => { el.classList.remove('border-gray-300','border-green-500','focus:border-blue-500','focus:border-green-500'); el.classList.add('border-red-500','focus:border-red-500'); };
const markOk = (el) => { el.classList.remove('border-gray-300','border-red-500','focus:border-blue-500','focus:border-red-500'); el.classList.add('border-green-500','focus:border-green-500'); };

function clearEmployeeData() {
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
        const resp = await fetch(`/nomina/buscar-empleados?q=${encodeURIComponent(term)}`);
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
    if ($('nombre').value && $('telefono').value && $('salario_base').value) {
        markOk(empleadoInput);
        return;
    }

    try {
        const resp = await fetch(`/nomina/buscar-empleado/${encodeURIComponent(docInput.value)}`);
        if (!resp.ok) return;
        const emp = await resp.json();
        if (!emp) return;
        selectEmployee(emp);
    } catch (e) {
        // Si falla, conserva los valores actuales del formulario
    }
}

function validateEmployee() {
    if (docInput.value && idContratoInput.value) return markOk(empleadoInput), true;
    markError(empleadoInput);
    showError('Selecciona un empleado de la lista.');
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
        docInput.value = '';
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
    if (!(validateEmployee() && validateFecha())) e.preventDefault();
});

hydrateSavedEmployee();
</script>
