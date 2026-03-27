@extends('layouts.app')

@section('title', 'Nomina Operativa')
@section('page-title', 'NOMINA OPERATIVA')

@section('content')
	@include('nomina.partials.index_content')

	<div id="modal-realizar-nomina" class="fixed inset-0 z-[90] hidden" aria-hidden="true">
		<div class="absolute inset-0 bg-slate-900/55"></div>
		<div class="relative flex min-h-full items-center justify-center p-4">
			<div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-2xl">
				<div class="border-b border-slate-100 px-5 py-4">
					<h3 class="text-lg font-extrabold text-slate-900">Nómina Masiva</h3>
					<p class="mt-1 text-sm text-slate-500">Se calculará la nómina de todos los empleados activos del periodo seleccionado.</p>
				</div>
				<div class="px-5 py-4">
					<p class="text-sm text-slate-700">¿Deseas continuar con este proceso?</p>
				</div>
				<div class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-4">
					<button type="button" id="btn-cancelar-realizar" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
						Cancelar
					</button>
					<button type="button" id="btn-confirmar-realizar" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-indigo-700">
						Aceptar
					</button>
				</div>
			</div>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const rows = Array.from(document.querySelectorAll('.nomina-row'));
			const radios = Array.from(document.querySelectorAll('.nomina-select-radio'));
			const editButton = document.getElementById('btn-editar-empleado');
			const massPayrollForm = document.getElementById('realizar-nomina-form');
			const massPayrollModal = document.getElementById('modal-realizar-nomina');
			const massPayrollCancelBtn = document.getElementById('btn-cancelar-realizar');
			const massPayrollConfirmBtn = document.getElementById('btn-confirmar-realizar');
			const editUrlTemplate = @json(route('nomina.edit', ['idSalario' => '__ID__']));
			const searchInput = document.getElementById('buscar-empleado');
			const employeesDataList = document.getElementById('empleados-sugeridos');

			if (massPayrollForm && massPayrollModal && massPayrollCancelBtn && massPayrollConfirmBtn) {
				const openMassPayrollModal = function () {
					massPayrollModal.classList.remove('hidden');
					massPayrollModal.setAttribute('aria-hidden', 'false');
				};

				const closeMassPayrollModal = function () {
					massPayrollModal.classList.add('hidden');
					massPayrollModal.setAttribute('aria-hidden', 'true');
				};

				massPayrollForm.addEventListener('submit', function (event) {
					if (massPayrollForm.dataset.confirmed === '1') {
						massPayrollForm.dataset.confirmed = '0';
						return;
					}

					event.preventDefault();
					openMassPayrollModal();
				});

				massPayrollCancelBtn.addEventListener('click', closeMassPayrollModal);

				massPayrollConfirmBtn.addEventListener('click', function () {
					massPayrollForm.dataset.confirmed = '1';
					closeMassPayrollModal();
					massPayrollForm.requestSubmit();
				});

				massPayrollModal.addEventListener('click', function (event) {
					if (event.target === massPayrollModal || event.target.classList.contains('bg-slate-900/55')) {
						closeMassPayrollModal();
					}
				});
			}

			if (searchInput && employeesDataList) {
				let debounceTimer = null;
				let activeController = null;

				const formatEmployeeName = function (name) {
					return (name || '')
						.toLocaleLowerCase('es-CO')
						.replace(/(^|\s)(\S)/g, function (_, space, letter) {
							return `${space}${letter.toLocaleUpperCase('es-CO')}`;
						});
				};

				const renderSuggestions = function (employees) {
					employeesDataList.innerHTML = '';
					employees.forEach(function (employee) {
						const formattedName = formatEmployeeName(employee.nombre);
						const option = document.createElement('option');
						option.value = employee.doc || '';
						option.label = `${employee.doc || ''} - ${formattedName}`;
						employeesDataList.appendChild(option);
					});
				};

				const fetchSuggestions = function (value) {
					const query = (value || '').trim();

					if (query.length < 2) {
						employeesDataList.innerHTML = '';
						return;
					}

					if (activeController) {
						activeController.abort();
					}

					activeController = new AbortController();
					const url = `/nomina/buscar-empleados?q=${encodeURIComponent(query)}`;

					fetch(url, {
						headers: {
							'X-Requested-With': 'XMLHttpRequest'
						},
						signal: activeController.signal
					})
						.then(function (response) {
							if (!response.ok) {
								throw new Error('No se pudo obtener la lista de empleados.');
							}
							return response.json();
						})
						.then(function (employees) {
							renderSuggestions(Array.isArray(employees) ? employees : []);
						})
						.catch(function (error) {
							if (error.name !== 'AbortError') {
								employeesDataList.innerHTML = '';
							}
						});
				};

				searchInput.addEventListener('input', function () {
					const value = this.value;
					clearTimeout(debounceTimer);
					debounceTimer = setTimeout(function () {
						fetchSuggestions(value);
					}, 250);
				});
			}

			if (!editButton || rows.length === 0) {
				return;
			}

			let selectedSalaryId = null;
			let selectedRow = null;

			const setEditButtonEnabled = function (enabled) {
				editButton.disabled = !enabled;
				if (enabled) {
					editButton.classList.remove('bg-gray-300', 'cursor-not-allowed', 'opacity-70');
					editButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
				} else {
					editButton.classList.add('bg-gray-300', 'cursor-not-allowed', 'opacity-70');
					editButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
				}
			};

			const clearSelection = function () {
				rows.forEach(function (item) {
					item.classList.remove('nomina-row-selected');
					const radio = item.querySelector('.nomina-select-radio');
					if (radio) {
						radio.checked = false;
					}
				});

				selectedSalaryId = null;
				selectedRow = null;
				setEditButtonEnabled(false);
			};

			const selectRow = function (row) {
				rows.forEach(function (item) {
					item.classList.remove('nomina-row-selected');
				});

				row.classList.add('nomina-row-selected');
				selectedSalaryId = row.dataset.salarioId || null;
				selectedRow = row;

				const rowRadio = row.querySelector('.nomina-select-radio');
				if (rowRadio) {
					rowRadio.checked = true;
				}

				setEditButtonEnabled(Boolean(selectedSalaryId));
			};

			rows.forEach(function (row) {
				row.addEventListener('click', function () {
					if (selectedRow === row) {
						clearSelection();
						return;
					}
					selectRow(row);
				});
			});

			radios.forEach(function (radio) {
				radio.addEventListener('click', function (event) {
					const row = this.closest('.nomina-row');
					if (!row) {
						return;
					}

					if (selectedRow === row) {
						event.preventDefault();
						clearSelection();
					}
				});

				radio.addEventListener('change', function () {
					const row = this.closest('.nomina-row');
					if (row) {
						selectRow(row);
					}
				});
			});

			editButton.addEventListener('click', function () {
				if (!selectedSalaryId) {
					return;
				}

				window.location.href = editUrlTemplate.replace('__ID__', selectedSalaryId);
			});

			setEditButtonEnabled(false);
		});

		function updateAutoClose(id, fecha) {
			if (!fecha) {
				if (!confirm('¿Desea eliminar la programación de cierre automático?')) return;
			}

			const url = "{{ route('periodos.update-auto-close', ['id' => ':id']) }}".replace(':id', id);

			fetch(url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': '{{ csrf_token() }}',
					'Accept': 'application/json'
				},
				body: JSON.stringify({
					fecha_cierre_automatico: fecha
				})
			})
			.then(res => res.json())
			.then(data => {
				if (data.success) {
					if (window.Swal) {
						Swal.fire({
							icon: 'success',
							title: 'Programado',
							text: data.message,
							timer: 2000,
							showConfirmButton: false
						});
					} else {
						alert(data.message);
					}
				} else {
					alert('Error: ' + data.message);
				}
			})
			.catch(err => {
				console.error(err);
				alert('Error al actualizar la fecha.');
			});
		}
	</script>

	@include('periodos.partials.modal_cerrar')

@endsection