@extends('layouts.app')

@section('title', 'Nómina')
@section('page-title', 'NÓMINA')

@section('content')

	@include('nomina.partials.index_content')

	<script>
		document.addEventListener('DOMContentLoaded', function () {

		const rows = Array.from(document.querySelectorAll('.nomina-row'));
		const radios = Array.from(document.querySelectorAll('.nomina-select-radio'));
		const editButton = document.getElementById('btn-editar-empleado');
		const editUrlTemplate = @json(route('nomina.edit', ['idSalario' => '__ID__']));
		const searchInput = document.getElementById('buscar-empleado');
		const employeesDataList = document.getElementById('empleados-sugeridos');

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
				item.classList.remove('bg-blue-50', 'ring-1', 'ring-blue-200');
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
				item.classList.remove('bg-blue-50', 'ring-1', 'ring-blue-200');
			});

			row.classList.add('bg-blue-50', 'ring-1', 'ring-blue-200');
			selectedSalaryId = row.dataset.salarioId || null;

			const rowRadio = row.querySelector('.nomina-select-radio');
			if (rowRadio) {
				rowRadio.checked = true;
			}

			selectedRow = row;

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

			let selectedSalaryId = null;

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

			const selectRow = function (row) {
				rows.forEach(function (item) {
					item.classList.remove('bg-blue-50', 'ring-1', 'ring-blue-200');
				});

				row.classList.add('bg-blue-50', 'ring-1', 'ring-blue-200');
				selectedSalaryId = row.dataset.salarioId || null;

				const rowRadio = row.querySelector('.nomina-select-radio');
				if (rowRadio) {
					rowRadio.checked = true;
				}

				setEditButtonEnabled(Boolean(selectedSalaryId));
			};

			rows.forEach(function (row) {
				row.addEventListener('click', function () {
					selectRow(row);
				});
			});

			radios.forEach(function (radio) {
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
	</script>

	@include('periodos.partials.modal_cerrar')

@endsection