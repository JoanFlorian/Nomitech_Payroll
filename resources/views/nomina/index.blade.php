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

			if (!editButton || rows.length === 0) {
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