@extends('layouts.app')

@section('title', 'Novedades')
@section('page-title', 'NOVEDADES')

@section('content')
@php
	$novedades = $novedades ?? collect();
@endphp

<div class="max-w-6xl mx-auto space-y-6">
	<div class="relative overflow-hidden rounded-xl bg-white p-6 border border-gray-100 shadow-sm">
		<div class="pointer-events-none absolute -top-10 -right-10 h-44 w-44 rounded-full bg-blue-100/50"></div>
		<div class="pointer-events-none absolute bottom-6 left-6 h-24 w-24 rotate-45 rounded-xl bg-gray-100/70"></div>

		<div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
			<div class="max-w-3xl">
				<p class="text-gray-500 leading-relaxed">
					En este módulo podrás registrar y gestionar novedades laborales asociadas a los empleados,
					como incapacidades, licencias, suspensiones, ausencias, entre otras.
				</p>
			</div>

			<button
				id="add-novelty-btn"
				type="button"
				class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-500 px-6 py-3 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:bg-emerald-600 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
			>
				<span class="material-icons text-[20px]">add</span>
				Añadir Novedad
			</button>
		</div>
	</div>

	@if (session('success'))
		<div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
			{{ session('success') }}
		</div>
	@endif

	<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
		<div class="bg-white border border-gray-100 rounded-xl p-4">
			<p class="text-xs uppercase tracking-wide text-gray-500">Total novedades</p>
			<p class="mt-1 text-2xl font-bold text-gray-900">{{ $novedades->count() }}</p>
		</div>
		<div class="bg-white border border-gray-100 rounded-xl p-4">
			<p class="text-xs uppercase tracking-wide text-gray-500">Última actualización</p>
			<p class="mt-1 text-sm font-semibold text-gray-900">{{ $novedades->first() ? \Carbon\Carbon::parse($novedades->first()->updated_at)->format('d/m/Y H:i') : 'Sin registros' }}</p>
		</div>
		<div class="bg-white border border-gray-100 rounded-xl p-4">
			<p class="text-xs uppercase tracking-wide text-gray-500">Estado</p>
			<p class="mt-1 text-sm font-semibold text-emerald-700">Módulo activo</p>
		</div>
	</div>

	<section class="space-y-5">
		@forelse ($novedades as $novedad)
			@php
				$empleado = optional(optional($novedad->salario)->contrato)->usuario;
				$nombreCompleto = trim(collect([
					$empleado->primer_nombre ?? null,
					$empleado->otros_nombres ?? null,
					$empleado->primer_apellido ?? null,
					$empleado->segundo_apellido ?? null,
				])->filter()->implode(' '));
				$nombreCompleto = $nombreCompleto ? \Illuminate\Support\Str::title($nombreCompleto) : '';

				$tipoNombre = $novedad->tipoNovedad->nombre ?? 'Sin tipo';
				$tipo = mb_strtolower($tipoNombre);
				$iniciales = strtoupper(mb_substr($empleado->primer_nombre ?? 'N', 0, 1) . mb_substr($empleado->primer_apellido ?? 'N', 0, 1));
				$unidadCantidad = $novedad->unidad_cantidad ?? 'dias';
				$unidadLabel = $unidadCantidad === 'horas' ? 'horas' : 'días';
				$cantidadDisplay = rtrim(rtrim(number_format((float) $novedad->cantidad, 2, '.', ''), '0'), '.');

				$badgeClass = match ($tipo) {
					'licencia' => 'bg-blue-100 text-blue-700',
					'incapacidad' => 'bg-emerald-100 text-emerald-700',
					'permiso' => 'bg-indigo-100 text-indigo-700',
					'suspensión', 'suspension' => 'bg-amber-100 text-amber-700',
					default => 'bg-gray-100 text-gray-700',
				};
			@endphp

			<article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden hover:shadow-md transition-all duration-300">
				<div class="pointer-events-none absolute -top-14 -right-14 h-36 w-36 rounded-full bg-blue-50"></div>
				<div class="pointer-events-none absolute -bottom-12 -left-10 h-28 w-28 rotate-45 bg-emerald-50 rounded-xl"></div>

				<div class="relative z-10">
					<div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-4">
						<div class="flex items-center gap-4">
							<div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-emerald-100 text-blue-700 font-bold flex items-center justify-center shadow-sm">
								{{ $iniciales }}
							</div>
							<div>
								<h3 class="text-lg font-bold text-gray-900">{{ $nombreCompleto ?: 'Empleado sin nombre' }}</h3>
								<p class="text-sm text-gray-500">C.C. {{ $empleado->doc ?? '—' }}</p>
							</div>
						</div>

						<div class="flex items-center gap-1.5">
							<button
								type="button"
								class="open-edit-modal text-gray-400 hover:text-blue-600 transition-colors p-2 rounded-full hover:bg-blue-50"
								title="Editar"
								data-novedad-id="{{ $novedad->id_novedad }}"
								data-doc="{{ $empleado->doc ?? '' }}"
								data-nombres="{{ \Illuminate\Support\Str::title(trim(($empleado->primer_nombre ?? '') . ' ' . ($empleado->otros_nombres ?? ''))) }}"
								data-apellidos="{{ \Illuminate\Support\Str::title(trim(($empleado->primer_apellido ?? '') . ' ' . ($empleado->segundo_apellido ?? ''))) }}"
								data-tipo="{{ $tipoNombre }}"
								data-licencia-remunerada="{{ (int) ($novedad->es_remunerado ?? $novedad->licencia_remunerada ?? 0) }}"
								data-unidad="{{ $unidadCantidad }}"
								data-cantidad="{{ (float) $novedad->cantidad }}"
								data-dias="{{ (float) ($novedad->dias ?? ($unidadCantidad === 'dias' ? $novedad->cantidad : 0)) }}"
								data-horas="{{ (float) ($novedad->horas ?? ($unidadCantidad === 'horas' ? $novedad->cantidad : 0)) }}"
								data-fecha-inicio="{{ $novedad->fecha_inicio ? \Carbon\Carbon::parse($novedad->fecha_inicio)->format('Y-m-d') : '' }}"
								data-fecha-fin="{{ $novedad->fecha_fin ? \Carbon\Carbon::parse($novedad->fecha_fin)->format('Y-m-d') : '' }}"
								data-pago="{{ (float) $novedad->pago }}"
								data-observaciones="{{ $novedad->observaciones ?? '' }}"
							>
								<span class="material-icons text-[20px]">edit</span>
							</button>
							<button
								type="button"
								class="trigger-delete-direct text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50"
								title="Eliminar"
								data-novedad-id="{{ $novedad->id_novedad }}"
							>
								<span class="material-icons text-[20px]">delete</span>
							</button>
						</div>
					</div>

					<hr class="border-gray-100 mb-4">

					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-x-8 gap-y-4 text-sm">
						<div>
							<p class="text-gray-500 mb-1">Tipo de novedad</p>
							<p class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">{{ $tipoNombre }}</p>
						</div>
						<div>
							<p class="text-gray-500 mb-1">Fecha inicio</p>
							<p class="font-semibold text-gray-800">{{ $novedad->fecha_inicio ? \Carbon\Carbon::parse($novedad->fecha_inicio)->format('d/m/Y') : '—' }}</p>
						</div>
						<div>
							<p class="text-gray-500 mb-1">Fecha fin</p>
							<p class="font-semibold text-gray-800">{{ $novedad->fecha_fin ? \Carbon\Carbon::parse($novedad->fecha_fin)->format('d/m/Y') : '—' }}</p>
						</div>
						<div>
							<p class="text-gray-500 mb-1">Cantidad</p>
							<p class="font-semibold text-gray-800">{{ $cantidadDisplay }} {{ $unidadLabel }}</p>
						</div>
						<div>
							<p class="text-gray-500 mb-1">Pago</p>
							<p class="font-semibold text-gray-800">$ {{ number_format((float) $novedad->pago, 0, ',', '.') }}</p>
						</div>
					</div>
				</div>
			</article>
		@empty
			<div class="bg-white border border-dashed border-gray-300 rounded-xl p-10 text-center">
				<i class="bi bi-card-list text-4xl text-gray-300"></i>
				<p class="mt-3 text-gray-600 font-medium">No hay novedades registradas por el momento.</p>
				<p class="text-sm text-gray-500 mt-1">Usa el botón <span class="font-semibold">Añadir Novedad</span> para crear la primera.</p>
			</div>
		@endforelse
	</section>
</div>

@include('novedades.registrar_novedad')
@include('novedades.editar_novedad')
@endsection

@push('scripts')
@php
	$shouldOpenModalJs = session('open_novedad_modal') || ($errors->any() && old('_method') !== 'PUT');
	$shouldOpenEditModalJs = $errors->any() && old('_method') === 'PUT';
	$oldEditDataJs = [
		'id' => old('edit_novedad_id'),
		'doc' => old('empleado_id', old('doc_empleado')),
		'tipo' => old('tipo_novedad'),
		'licencia_remunerada' => old('es_remunerado', old('licencia_remunerada', '0')),
		'unidad_cantidad' => old('unidad_cantidad'),
		'cantidad_dias' => old('cantidad_dias'),
		'cantidad_horas' => old('cantidad_horas'),
		'fecha_inicio' => old('fecha_inicio'),
		'fecha_fin' => old('fecha_fin'),
		'pago_manual' => old('pago_manual', old('pago')),
		'observaciones' => old('observaciones'),
	];
@endphp
<script>
	document.addEventListener('DOMContentLoaded', () => {
		const employees = @json($empleadosBusqueda ?? []);
		const shouldOpenModal = @json($shouldOpenModalJs);
		const shouldOpenEditModal = @json($shouldOpenEditModalJs);
		const oldEditData = @json($oldEditDataJs);
		const updateUrlTemplate = @json(route('novedades.update', ['id_novedad' => '__ID__']));
		const deleteUrlTemplate = @json(route('novedades.destroy', ['id_novedad' => '__ID__']));

		const modal = document.getElementById('novelty-modal');
		const addNoveltyBtn = document.getElementById('add-novelty-btn');
		const cancelBtn = document.getElementById('cancel-btn');
		const closeModalBtn = document.getElementById('close-modal-btn');

		const form = document.getElementById('novelty-form');
		const employeeSearch = document.getElementById('employee-search');
		const docEmpleadoInput = document.getElementById('doc_empleado');
		const employeeDetails = document.getElementById('employee-details');
		const employeeNameInput = document.getElementById('employee-name');
		const employeeLastnameInput = document.getElementById('employee-lastname');
		const suggestions = document.getElementById('employee-suggestions');

		const noveltyType = document.getElementById('novelty-type');
		const unitQuantityInputs = document.querySelectorAll('input[name="unidad_cantidad"]');
		const quantityDaysInput = document.getElementById('quantity-days');
		const quantityHoursInput = document.getElementById('quantity-hours');
		const startDateInput = document.getElementById('start-date');
		const endDateInput = document.getElementById('end-date');
		const paymentDisplayInput = document.getElementById('payment-display');
		const paymentInput = document.getElementById('payment');
		const estimatedValueElement = document.getElementById('estimated-value');
		const estimatedNoteElement = document.getElementById('estimated-note');
		const licenciaRemuneradaWrap = document.getElementById('licencia-remunerada-wrap');
		const licenciaRemuneradaInput = document.getElementById('licencia-remunerada');
		const remuneradaLabel = document.getElementById('remunerada-label');
		const observationsInput = document.getElementById('observaciones');
		const createUnitDaysRadio = document.querySelector('input[name="unidad_cantidad"][value="dias"]');
		const createUnitHoursRadio = document.querySelector('input[name="unidad_cantidad"][value="horas"]');

		const editModal = document.getElementById('edit-novelty-modal');
		const closeEditModalBtn = document.getElementById('close-edit-modal-btn');
		const cancelEditBtn = document.getElementById('cancel-edit-btn');
		const editForm = document.getElementById('edit-novelty-form');
		const deleteNovedadBtn = document.getElementById('delete-novedad-btn');
		const deleteForm = document.getElementById('delete-novedad-form');
		const editButtons = document.querySelectorAll('.open-edit-modal');
		const deleteDirectButtons = document.querySelectorAll('.trigger-delete-direct');

		const editNovedadIdInput = document.getElementById('edit-novedad-id');
		const editDocEmpleadoInput = document.getElementById('edit-doc-empleado');
		const editEmployeeNameInput = document.getElementById('edit-employee-name');
		const editEmployeeLastnameInput = document.getElementById('edit-employee-lastname');
		const editEmployeeDocInput = document.getElementById('edit-employee-doc');
		const editNoveltyTypeInput = document.getElementById('edit-novelty-type');
		const editUnitQuantityInputs = document.querySelectorAll('#edit-novelty-form input[name="unidad_cantidad"]');
		const editQuantityDaysInput = document.getElementById('edit-quantity-days');
		const editQuantityHoursInput = document.getElementById('edit-quantity-hours');
		const editStartDateInput = document.getElementById('edit-start-date');
		const editEndDateInput = document.getElementById('edit-end-date');
		const editPaymentDisplayInput = document.getElementById('edit-payment-display');
		const editPaymentInput = document.getElementById('edit-payment');
		const editEstimatedValueElement = document.getElementById('edit-estimated-value');
		const editEstimatedNoteElement = document.getElementById('edit-estimated-note');
		const editLicenciaRemuneradaWrap = document.getElementById('edit-licencia-remunerada-wrap');
		const editLicenciaRemuneradaInput = document.getElementById('edit-licencia-remunerada');
		const editRemuneradaLabel = document.getElementById('edit-remunerada-label');
		const editObservacionesInput = document.getElementById('edit-observaciones');
		const editUnitDaysRadio = document.getElementById('edit-unit-days');
		const editUnitHoursRadio = document.getElementById('edit-unit-hours');

		const editErrorElements = {
			noveltyType: document.getElementById('edit-novelty-type-error'),
			quantityUnit: document.getElementById('edit-quantity-unit-error'),
			quantityDays: document.getElementById('edit-quantity-days-error'),
			quantityHours: document.getElementById('edit-quantity-hours-error'),
			startDate: document.getElementById('edit-start-date-error'),
			endDate: document.getElementById('edit-end-date-error'),
			payment: document.getElementById('edit-payment-error'),
		};

		const errorElements = {
			employee: document.getElementById('employee-search-error'),
			noveltyType: document.getElementById('novelty-type-error'),
			quantityUnit: document.getElementById('quantity-unit-error'),
			quantityDays: document.getElementById('quantity-days-error'),
			quantityHours: document.getElementById('quantity-hours-error'),
			startDate: document.getElementById('start-date-error'),
			endDate: document.getElementById('end-date-error'),
			payment: document.getElementById('payment-error'),
		};

		const openModal = () => {
			modal.classList.remove('hidden');
			modal.classList.add('flex');
		};

		const closeModal = () => {
			modal.classList.remove('flex');
			modal.classList.add('hidden');
		};

		const openEditModal = () => {
			editModal.classList.remove('hidden');
			editModal.classList.add('flex');
		};

		const closeEditModal = () => {
			editModal.classList.remove('flex');
			editModal.classList.add('hidden');
		};

		addNoveltyBtn?.addEventListener('click', openModal);
		cancelBtn?.addEventListener('click', closeModal);
		closeModalBtn?.addEventListener('click', closeModal);
		cancelEditBtn?.addEventListener('click', closeEditModal);
		closeEditModalBtn?.addEventListener('click', closeEditModal);

		modal?.addEventListener('click', (event) => {
			if (event.target === modal) {
				closeModal();
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
				closeModal();
			}

			if (event.key === 'Escape' && !editModal.classList.contains('hidden')) {
				closeEditModal();
			}
		});

		editModal?.addEventListener('click', (event) => {
			if (event.target === editModal) {
				closeEditModal();
			}
		});

		if (shouldOpenModal) {
			openModal();
		}

		const normalize = (value) => {
			return (value || '')
				.toString()
				.normalize('NFD')
				.replace(/[\u0300-\u036f]/g, '')
				.toLowerCase()
				.trim();
		};

		const toTitleCase = (value) => {
			if (!value) return '';
			return value
				.toString()
				.toLowerCase()
				.replace(/(^|\s)\S/g, (letter) => letter.toUpperCase());
		};

		const formatter = new Intl.NumberFormat('es-CO', {
			style: 'currency',
			currency: 'COP',
			maximumFractionDigits: 0,
		});

		const formatCurrency = (value) => {
			const numericValue = Number(value) || 0;
			return formatter.format(Math.round(numericValue));
		};

		const getEmployeeByDoc = (doc) => {
			if (!doc) return null;
			return employees.find((employee) => String(employee.doc || '') === String(doc)) || null;
		};

		const getEmployeeSalary = (doc) => {
			const employee = getEmployeeByDoc(doc);
			const salary = Number(employee?.salario_base || 0);
			return Number.isFinite(salary) ? salary : 0;
		};

		const calculateEstimatedValue = ({ tipo, unidad, cantidad, salarioBase, licenciaRemunerada, pagoManual }) => {
			if (Number.isFinite(pagoManual)) {
				return pagoManual;
			}

			if (!tipo || !unidad || !Number.isFinite(cantidad) || cantidad <= 0 || !Number.isFinite(salarioBase) || salarioBase <= 0) {
				return 0;
			}

			const salarioDia = salarioBase / 30;
			const salarioHora = salarioBase / 240;

			switch ((tipo || '').toLowerCase()) {
				case 'licencia':
					return licenciaRemunerada ? 0 : -(salarioDia * cantidad);
				case 'incapacidad':
					return -((salarioDia * 0.6667) * cantidad);
				case 'permiso':
					return licenciaRemunerada
						? 0
						: -(unidad === 'horas' ? (salarioHora * cantidad) : (salarioDia * cantidad));
				case 'suspensión':
				case 'suspension':
					return -(salarioDia * cantidad);
				default:
					return 0;
			}
		};

		const getCreateCantidad = () => {
			const unit = getSelectedCreateUnit();
			if (unit === 'dias') return Number(quantityDaysInput.value || 0);
			if (unit === 'horas') return Number(quantityHoursInput.value || 0);
			return 0;
		};

		const getEditCantidad = () => {
			const unit = getSelectedEditUnit();
			if (unit === 'dias') return Number(editQuantityDaysInput.value || 0);
			if (unit === 'horas') return Number(editQuantityHoursInput.value || 0);
			return 0;
		};

		const updateCreateEstimatedValue = () => {
			if (!estimatedValueElement || !estimatedNoteElement) return;

			const doc = docEmpleadoInput.value;
			const unit = getSelectedCreateUnit();
			const cantidad = getCreateCantidad();
			const tipo = (noveltyType.value || '').toLowerCase();
			const salarioBase = getEmployeeSalary(doc);
			const paymentValue = paymentInput.value ? getPaymentNumber() : NaN;
			const estimated = calculateEstimatedValue({
				tipo,
				unidad: unit,
				cantidad,
				salarioBase,
				licenciaRemunerada: !!licenciaRemuneradaInput?.checked,
				pagoManual: paymentValue,
			});

			estimatedValueElement.textContent = formatCurrency(estimated);

			if (Number.isFinite(paymentValue) && paymentValue >= 0) {
				estimatedNoteElement.textContent = 'Se está usando el pago manual ingresado.';
				return;
			}

			if (!doc) {
				estimatedNoteElement.textContent = 'Selecciona empleado, tipo y cantidad para calcular automáticamente.';
				return;
			}

			if (!salarioBase) {
				estimatedNoteElement.textContent = 'No se encontró salario base para el empleado seleccionado.';
				return;
			}

			estimatedNoteElement.textContent = 'Cálculo automático según salario base y tipo de novedad.';
		};

		const updateEditEstimatedValue = () => {
			if (!editEstimatedValueElement || !editEstimatedNoteElement) return;

			const doc = editDocEmpleadoInput.value;
			const unit = getSelectedEditUnit();
			const cantidad = getEditCantidad();
			const tipo = (editNoveltyTypeInput.value || '').toLowerCase();
			const salarioBase = getEmployeeSalary(doc);
			const paymentValue = editPaymentInput.value ? getEditPaymentNumber() : NaN;
			const estimated = calculateEstimatedValue({
				tipo,
				unidad: unit,
				cantidad,
				salarioBase,
				licenciaRemunerada: !!editLicenciaRemuneradaInput?.checked,
				pagoManual: paymentValue,
			});

			editEstimatedValueElement.textContent = formatCurrency(estimated);

			if (Number.isFinite(paymentValue) && paymentValue >= 0) {
				editEstimatedNoteElement.textContent = 'Se está usando el pago manual ingresado.';
				return;
			}

			if (!doc) {
				editEstimatedNoteElement.textContent = 'Selecciona empleado, tipo y cantidad para calcular automáticamente.';
				return;
			}

			if (!salarioBase) {
				editEstimatedNoteElement.textContent = 'No se encontró salario base para el empleado seleccionado.';
				return;
			}

			editEstimatedNoteElement.textContent = 'Cálculo automático según salario base y tipo de novedad.';
		};

		const clearEmployeeSelection = () => {
			docEmpleadoInput.value = '';
			employeeNameInput.value = '';
			employeeLastnameInput.value = '';
			employeeDetails.classList.add('hidden');
			updateCreateEstimatedValue();
		};

		const setEmployeeSelection = (employee) => {
			docEmpleadoInput.value = employee.doc || '';
			const fullName = toTitleCase(employee.nombre_completo || '');
			employeeSearch.value = `${fullName} - ${employee.doc || ''}`.trim();
			employeeNameInput.value = toTitleCase(employee.nombres || '');
			employeeLastnameInput.value = toTitleCase(employee.apellidos || '');
			employeeDetails.classList.remove('hidden');
			suggestions.classList.add('hidden');
			suggestions.innerHTML = '';
			updateCreateEstimatedValue();
		};

		const renderSuggestions = (query) => {
			if (!query) {
				suggestions.innerHTML = '';
				suggestions.classList.add('hidden');
				return;
			}

			const matches = employees
				.filter((employee) => {
					const fullName = normalize(employee.nombre_completo);
					const documentNumber = normalize(employee.doc);
					return fullName.includes(query) || documentNumber.includes(query);
				})
				.slice(0, 8);

			if (matches.length === 0) {
				suggestions.innerHTML = '<li class="px-3 py-2 text-sm text-gray-500">No se encontraron empleados.</li>';
				suggestions.classList.remove('hidden');
				return;
			}

			suggestions.innerHTML = matches
				.map((employee) => {
					const fullName = toTitleCase(employee.nombre_completo || '');
					return `<li>
						<button type="button" class="employee-option w-full text-left px-3 py-2 hover:bg-gray-50 text-sm" data-doc="${employee.doc}">
							<span class="font-medium text-gray-800">${fullName}</span>
							<span class="text-gray-500"> - ${employee.doc}</span>
						</button>
					</li>`;
				})
				.join('');

			suggestions.classList.remove('hidden');

			suggestions.querySelectorAll('.employee-option').forEach((option) => {
				option.addEventListener('click', () => {
					const employee = employees.find((item) => item.doc === option.dataset.doc);
					if (employee) {
						setEmployeeSelection(employee);
					}
				});
			});
		};

		employeeSearch?.addEventListener('input', () => {
			clearEmployeeSelection();
			renderSuggestions(normalize(employeeSearch.value));
			clearFieldError('employee');
		});

		document.addEventListener('click', (event) => {
			if (!suggestions.contains(event.target) && event.target !== employeeSearch) {
				suggestions.classList.add('hidden');
			}
		});

		const markInvalid = (input) => {
			input.classList.add('border-red-400', 'focus:border-red-500', 'focus:ring-red-500');
		};

		const clearInvalid = (input) => {
			input.classList.remove('border-red-400', 'focus:border-red-500', 'focus:ring-red-500');
		};

		const showFieldError = (field, message) => {
			const element = errorElements[field];
			if (!element) return;
			element.textContent = message;
			element.classList.remove('hidden');
		};

		const clearFieldError = (field) => {
			const element = errorElements[field];
			if (!element) return;
			element.textContent = '';
			element.classList.add('hidden');
		};

		const clearAllErrors = () => {
			Object.keys(errorElements).forEach(clearFieldError);

			[employeeSearch, noveltyType, quantityDaysInput, quantityHoursInput, startDateInput, endDateInput, paymentDisplayInput]
				.forEach((input) => clearInvalid(input));
		};

		const showEditFieldError = (field, message) => {
			const element = editErrorElements[field];
			if (!element) return;
			element.textContent = message;
			element.classList.remove('hidden');
		};

		const clearEditFieldError = (field) => {
			const element = editErrorElements[field];
			if (!element) return;
			element.textContent = '';
			element.classList.add('hidden');
		};

		const clearAllEditErrors = () => {
			Object.keys(editErrorElements).forEach(clearEditFieldError);
			[editNoveltyTypeInput, editQuantityDaysInput, editQuantityHoursInput, editStartDateInput, editEndDateInput, editPaymentDisplayInput]
				.forEach((input) => clearInvalid(input));
		};

		const getSelectedCreateUnit = () => {
			const checked = document.querySelector('input[name="unidad_cantidad"]:checked');
			return checked ? checked.value : '';
		};

		const updateLicenciaRemuneradaVisibility = () => {
			const type = (noveltyType.value || '').toLowerCase();
			const supportsRemunerada = type === 'licencia' || type === 'permiso';
			if (licenciaRemuneradaWrap) {
				licenciaRemuneradaWrap.classList.toggle('hidden', !supportsRemunerada);
			}
			if (remuneradaLabel) {
				remuneradaLabel.textContent = type === 'permiso' ? 'Permiso remunerado' : 'Licencia remunerada';
			}
			if (!supportsRemunerada && licenciaRemuneradaInput) {
				licenciaRemuneradaInput.checked = false;
			}
		};

		const updateCreateQuantityMode = () => {
			const type = (noveltyType.value || '').toLowerCase();
			const allowsHours = type === 'permiso';

			if (!allowsHours && createUnitDaysRadio) {
				createUnitDaysRadio.checked = true;
			}
			if (createUnitHoursRadio) {
				createUnitHoursRadio.disabled = !allowsHours;
			}

			const unit = getSelectedCreateUnit();
			const isDias = unit === 'dias';
			const isHoras = unit === 'horas' && allowsHours;

			quantityDaysInput.disabled = !isDias;
			quantityHoursInput.disabled = !isHoras;

			if (!isDias) quantityDaysInput.value = '';
			if (!isHoras) quantityHoursInput.value = '';
		};

		const getSelectedEditUnit = () => {
			const checked = document.querySelector('#edit-novelty-form input[name="unidad_cantidad"]:checked');
			return checked ? checked.value : '';
		};

		const updateEditLicenciaRemuneradaVisibility = () => {
			const type = (editNoveltyTypeInput.value || '').toLowerCase();
			const supportsRemunerada = type === 'licencia' || type === 'permiso';
			if (editLicenciaRemuneradaWrap) {
				editLicenciaRemuneradaWrap.classList.toggle('hidden', !supportsRemunerada);
			}
			if (editRemuneradaLabel) {
				editRemuneradaLabel.textContent = type === 'permiso' ? 'Permiso remunerado' : 'Licencia remunerada';
			}
			if (!supportsRemunerada && editLicenciaRemuneradaInput) {
				editLicenciaRemuneradaInput.checked = false;
			}
		};

		const updateEditQuantityMode = () => {
			const type = (editNoveltyTypeInput.value || '').toLowerCase();
			const allowsHours = type === 'permiso';

			if (!allowsHours && editUnitDaysRadio) {
				editUnitDaysRadio.checked = true;
			}
			if (editUnitHoursRadio) {
				editUnitHoursRadio.disabled = !allowsHours;
			}

			const unit = getSelectedEditUnit();
			const isDias = unit === 'dias';
			const isHoras = unit === 'horas' && allowsHours;

			editQuantityDaysInput.disabled = !isDias;
			editQuantityHoursInput.disabled = !isHoras;

			if (!isDias) editQuantityDaysInput.value = '';
			if (!isHoras) editQuantityHoursInput.value = '';
		};

		const validateDateRange = () => {
			const startDate = startDateInput.value;
			const endDate = endDateInput.value;

			if (startDate) {
				endDateInput.min = startDate;
			} else {
				endDateInput.removeAttribute('min');
			}

			if (!startDate || !endDate) {
				clearFieldError('endDate');
				clearInvalid(endDateInput);
				endDateInput.setCustomValidity('');
				return true;
			}

			const isValidRange = new Date(endDate) >= new Date(startDate);

			if (!isValidRange) {
				showFieldError('endDate', 'La fecha de fin no puede ser menor que la fecha de inicio.');
				markInvalid(endDateInput);
				endDateInput.setCustomValidity('La fecha de fin no puede ser menor que la fecha de inicio.');
				return false;
			}

			clearFieldError('endDate');
			clearInvalid(endDateInput);
			endDateInput.setCustomValidity('');
			return true;
		};

		const getPaymentNumber = () => {
			const value = (paymentInput.value || '').replace(',', '.');
			const parsed = Number(value);
			return Number.isFinite(parsed) ? parsed : NaN;
		};

		const getEditPaymentNumber = () => {
			const value = (editPaymentInput.value || '').replace(',', '.');
			const parsed = Number(value);
			return Number.isFinite(parsed) ? parsed : NaN;
		};

		paymentDisplayInput?.addEventListener('input', () => {
			const rawDigits = paymentDisplayInput.value.replace(/\D/g, '');
			if (!rawDigits) {
				paymentDisplayInput.value = '';
				paymentInput.value = '';
				updateCreateEstimatedValue();
				return;
			}

			const numericValue = Number(rawDigits);
			paymentInput.value = String(numericValue);
			paymentDisplayInput.value = formatter.format(numericValue);
			clearFieldError('payment');
			clearInvalid(paymentDisplayInput);
			updateCreateEstimatedValue();
		});

		editPaymentDisplayInput?.addEventListener('input', () => {
			const rawDigits = editPaymentDisplayInput.value.replace(/\D/g, '');
			if (!rawDigits) {
				editPaymentDisplayInput.value = '';
				editPaymentInput.value = '';
				updateEditEstimatedValue();
				return;
			}

			const numericValue = Number(rawDigits);
			editPaymentInput.value = String(numericValue);
			editPaymentDisplayInput.value = formatter.format(numericValue);
			clearEditFieldError('payment');
			clearInvalid(editPaymentDisplayInput);
			updateEditEstimatedValue();
		});

		[noveltyType, quantityDaysInput, quantityHoursInput, startDateInput, endDateInput].forEach((field) => {
			field?.addEventListener('change', () => {
				clearInvalid(field);
				updateCreateEstimatedValue();
			});
		});
		noveltyType?.addEventListener('change', () => {
			updateLicenciaRemuneradaVisibility();
			updateCreateQuantityMode();
			updateCreateEstimatedValue();
		});

		[editNoveltyTypeInput, editQuantityDaysInput, editQuantityHoursInput, editStartDateInput, editEndDateInput].forEach((field) => {
			field?.addEventListener('change', () => {
				clearInvalid(field);
				updateEditEstimatedValue();
			});
		});
		editNoveltyTypeInput?.addEventListener('change', () => {
			updateEditLicenciaRemuneradaVisibility();
			updateEditQuantityMode();
			updateEditEstimatedValue();
		});

		quantityDaysInput?.addEventListener('input', updateCreateEstimatedValue);
		quantityHoursInput?.addEventListener('input', updateCreateEstimatedValue);
		editQuantityDaysInput?.addEventListener('input', updateEditEstimatedValue);
		editQuantityHoursInput?.addEventListener('input', updateEditEstimatedValue);
		licenciaRemuneradaInput?.addEventListener('change', updateCreateEstimatedValue);
		editLicenciaRemuneradaInput?.addEventListener('change', updateEditEstimatedValue);

		unitQuantityInputs.forEach((input) => {
			input.addEventListener('change', () => {
				clearFieldError('quantityUnit');
				clearFieldError('quantityDays');
				clearFieldError('quantityHours');
				updateCreateQuantityMode();
				updateCreateEstimatedValue();
			});
		});

		editUnitQuantityInputs.forEach((input) => {
			input.addEventListener('change', () => {
				clearEditFieldError('quantityUnit');
				clearEditFieldError('quantityDays');
				clearEditFieldError('quantityHours');
				updateEditQuantityMode();
				updateEditEstimatedValue();
			});
		});

		const preselectedDoc = docEmpleadoInput.value;
		if (preselectedDoc) {
			const selectedEmployee = employees.find((employee) => employee.doc === preselectedDoc);
			if (selectedEmployee) {
				setEmployeeSelection(selectedEmployee);
			}
		}

		if (paymentInput.value) {
			const initialPayment = Number(paymentInput.value);
			if (Number.isFinite(initialPayment)) {
				paymentDisplayInput.value = formatter.format(initialPayment);
			}
		}

		updateCreateQuantityMode();
		updateLicenciaRemuneradaVisibility();
		updateCreateEstimatedValue();

		startDateInput?.addEventListener('change', validateDateRange);
		endDateInput?.addEventListener('change', validateDateRange);
		validateDateRange();

		const validateEditDateRange = () => {
			const startDate = editStartDateInput.value;
			const endDate = editEndDateInput.value;

			if (startDate) {
				editEndDateInput.min = startDate;
			} else {
				editEndDateInput.removeAttribute('min');
			}

			if (!startDate || !endDate) {
				clearEditFieldError('endDate');
				clearInvalid(editEndDateInput);
				editEndDateInput.setCustomValidity('');
				return true;
			}

			const isValidRange = new Date(endDate) >= new Date(startDate);
			if (!isValidRange) {
				showEditFieldError('endDate', 'La fecha de fin no puede ser menor que la fecha de inicio.');
				markInvalid(editEndDateInput);
				editEndDateInput.setCustomValidity('La fecha de fin no puede ser menor que la fecha de inicio.');
				return false;
			}

			clearEditFieldError('endDate');
			clearInvalid(editEndDateInput);
			editEndDateInput.setCustomValidity('');
			return true;
		};

		editStartDateInput?.addEventListener('change', validateEditDateRange);
		editEndDateInput?.addEventListener('change', validateEditDateRange);

		const setEditModalData = (data) => {
			editNovedadIdInput.value = data.id || '';
			editDocEmpleadoInput.value = data.doc || '';
			editEmployeeDocInput.value = data.doc || '';
			editEmployeeNameInput.value = toTitleCase(data.nombres || '');
			editEmployeeLastnameInput.value = toTitleCase(data.apellidos || '');
			editNoveltyTypeInput.value = data.tipo || '';
			if (editLicenciaRemuneradaInput) {
				editLicenciaRemuneradaInput.checked = String(data.licenciaRemunerada ?? '1') !== '0';
			}
			const unit = data.unidad || 'dias';
			const unitRadio = document.querySelector(`#edit-novelty-form input[name="unidad_cantidad"][value="${unit}"]`);
			if (unitRadio) unitRadio.checked = true;
			editQuantityDaysInput.value = data.dias || (unit === 'dias' ? (data.cantidad || '') : '');
			editQuantityHoursInput.value = data.horas || (unit === 'horas' ? (data.cantidad || '') : '');
			editStartDateInput.value = data.fechaInicio || '';
			editEndDateInput.value = data.fechaFin || '';
			if (editObservacionesInput) {
				editObservacionesInput.value = data.observaciones || '';
			}

			const parsedPago = Number(data.pago || 0);
			if (Number.isFinite(parsedPago) && parsedPago !== 0) {
				editPaymentInput.value = String(parsedPago);
				editPaymentDisplayInput.value = formatter.format(parsedPago);
			} else {
				editPaymentInput.value = '';
				editPaymentDisplayInput.value = '';
			}

			editForm.action = updateUrlTemplate.replace('__ID__', String(data.id));
			deleteForm.action = deleteUrlTemplate.replace('__ID__', String(data.id));
			updateEditQuantityMode();
			updateEditLicenciaRemuneradaVisibility();
			validateEditDateRange();
			updateEditEstimatedValue();
		};

		editButtons.forEach((button) => {
			button.addEventListener('click', () => {
				clearAllEditErrors();
				setEditModalData({
					id: button.dataset.novedadId,
					doc: button.dataset.doc,
					nombres: button.dataset.nombres,
					apellidos: button.dataset.apellidos,
					tipo: button.dataset.tipo,
					licenciaRemunerada: button.dataset.licenciaRemunerada,
					unidad: button.dataset.unidad,
					cantidad: button.dataset.cantidad,
					dias: button.dataset.dias,
					horas: button.dataset.horas,
					fechaInicio: button.dataset.fechaInicio,
					fechaFin: button.dataset.fechaFin,
					pago: button.dataset.pago,
					observaciones: button.dataset.observaciones,
				});
				openEditModal();
			});
		});

		deleteNovedadBtn?.addEventListener('click', () => {
			if (confirm('¿Seguro que deseas eliminar esta novedad? Esta acción no se puede deshacer.')) {
				deleteForm.submit();
			}
		});

		deleteDirectButtons.forEach((button) => {
			button.addEventListener('click', () => {
				const id = button.dataset.novedadId;
				if (!id) return;
				if (confirm('¿Seguro que deseas eliminar esta novedad? Esta acción no se puede deshacer.')) {
					deleteForm.action = deleteUrlTemplate.replace('__ID__', id);
					deleteForm.submit();
				}
			});
		});

		form?.addEventListener('submit', (event) => {
			clearAllErrors();
			let isValid = true;

			if (!docEmpleadoInput.value) {
				isValid = false;
				showFieldError('employee', 'Debe buscar y seleccionar un empleado válido.');
				markInvalid(employeeSearch);
			}

			if (!noveltyType.value) {
				isValid = false;
				showFieldError('noveltyType', 'Debe seleccionar el tipo de novedad.');
				markInvalid(noveltyType);
			}

			const selectedUnit = getSelectedCreateUnit();
			if (!selectedUnit) {
				isValid = false;
				showFieldError('quantityUnit', 'Debe seleccionar si la cantidad corresponde a días u horas.');
			}

			if (selectedUnit === 'dias') {
				if (!quantityDaysInput.value) {
					isValid = false;
					showFieldError('quantityDays', 'Debe ingresar la cantidad en días.');
					markInvalid(quantityDaysInput);
				} else if (Number(quantityDaysInput.value) <= 0) {
					isValid = false;
					showFieldError('quantityDays', 'La cantidad de días debe ser mayor a 0.');
					markInvalid(quantityDaysInput);
				}
			}

			if (selectedUnit === 'horas') {
				if (!quantityHoursInput.value) {
					isValid = false;
					showFieldError('quantityHours', 'Debe ingresar la cantidad en horas.');
					markInvalid(quantityHoursInput);
				} else if (Number(quantityHoursInput.value) <= 0) {
					isValid = false;
					showFieldError('quantityHours', 'La cantidad de horas debe ser mayor a 0.');
					markInvalid(quantityHoursInput);
				}
			}

			if (!startDateInput.value) {
				isValid = false;
				showFieldError('startDate', 'La fecha de inicio es obligatoria.');
				markInvalid(startDateInput);
			}

			if (!endDateInput.value) {
				isValid = false;
				showFieldError('endDate', 'La fecha de fin es obligatoria.');
				markInvalid(endDateInput);
			} else if (!validateDateRange()) {
				isValid = false;
			}

			if (paymentInput.value) {
				const paymentNumber = getPaymentNumber();
				if (Number.isNaN(paymentNumber)) {
					isValid = false;
					showFieldError('payment', 'El pago debe ser un valor numérico válido.');
					markInvalid(paymentDisplayInput);
				}
			}

			if (!isValid) {
				event.preventDefault();
			}
		});

		editForm?.addEventListener('submit', (event) => {
			clearAllEditErrors();
			let isValid = true;

			if (!editNoveltyTypeInput.value) {
				isValid = false;
				showEditFieldError('noveltyType', 'Debe seleccionar el tipo de novedad.');
				markInvalid(editNoveltyTypeInput);
			}

			const selectedEditUnit = getSelectedEditUnit();
			if (!selectedEditUnit) {
				isValid = false;
				showEditFieldError('quantityUnit', 'Debe seleccionar si la cantidad corresponde a días u horas.');
			}

			if (selectedEditUnit === 'dias') {
				if (!editQuantityDaysInput.value) {
					isValid = false;
					showEditFieldError('quantityDays', 'Debe ingresar la cantidad en días.');
					markInvalid(editQuantityDaysInput);
				} else if (Number(editQuantityDaysInput.value) <= 0) {
					isValid = false;
					showEditFieldError('quantityDays', 'La cantidad de días debe ser mayor a 0.');
					markInvalid(editQuantityDaysInput);
				}
			}

			if (selectedEditUnit === 'horas') {
				if (!editQuantityHoursInput.value) {
					isValid = false;
					showEditFieldError('quantityHours', 'Debe ingresar la cantidad en horas.');
					markInvalid(editQuantityHoursInput);
				} else if (Number(editQuantityHoursInput.value) <= 0) {
					isValid = false;
					showEditFieldError('quantityHours', 'La cantidad de horas debe ser mayor a 0.');
					markInvalid(editQuantityHoursInput);
				}
			}

			if (!editStartDateInput.value) {
				isValid = false;
				showEditFieldError('startDate', 'La fecha de inicio es obligatoria.');
				markInvalid(editStartDateInput);
			}

			if (!editEndDateInput.value) {
				isValid = false;
				showEditFieldError('endDate', 'La fecha de fin es obligatoria.');
				markInvalid(editEndDateInput);
			} else if (!validateEditDateRange()) {
				isValid = false;
			}

			if (editPaymentInput.value) {
				const paymentNumber = getEditPaymentNumber();
				if (Number.isNaN(paymentNumber)) {
					isValid = false;
					showEditFieldError('payment', 'El pago debe ser un valor numérico válido.');
					markInvalid(editPaymentDisplayInput);
				}
			}

			if (!isValid) {
				event.preventDefault();
			}
		});

		if (shouldOpenEditModal && oldEditData.id) {
			const sourceButton = document.querySelector(`.open-edit-modal[data-novedad-id="${oldEditData.id}"]`);
			if (sourceButton) {
				setEditModalData({
					id: oldEditData.id,
					doc: oldEditData.doc || sourceButton.dataset.doc,
					nombres: sourceButton.dataset.nombres,
					apellidos: sourceButton.dataset.apellidos,
					tipo: oldEditData.tipo || sourceButton.dataset.tipo,
					licenciaRemunerada: oldEditData.licencia_remunerada,
					unidad: oldEditData.unidad_cantidad || sourceButton.dataset.unidad,
					cantidad: (oldEditData.unidad_cantidad === 'horas'
						? oldEditData.cantidad_horas
						: oldEditData.cantidad_dias) || sourceButton.dataset.cantidad,
					dias: oldEditData.cantidad_dias || sourceButton.dataset.dias,
					horas: oldEditData.cantidad_horas || sourceButton.dataset.horas,
					fechaInicio: oldEditData.fecha_inicio || sourceButton.dataset.fechaInicio,
					fechaFin: oldEditData.fecha_fin || sourceButton.dataset.fechaFin,
					pago: oldEditData.pago_manual || sourceButton.dataset.pago,
					observaciones: oldEditData.observaciones || sourceButton.dataset.observaciones,
				});
				openEditModal();
			}
		}
	});
</script>
@endpush
