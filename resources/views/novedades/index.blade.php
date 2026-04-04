@extends('layouts.app')

@section('title', 'Novedades')
@section('page-title', 'NOVEDADES')

@section('content')
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

			<div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
				<a
					href="{{ route('novedades.historial') }}"
					class="inline-flex items-center justify-center gap-2 rounded-full border border-emerald-500 px-5 py-2.5 text-xs font-semibold text-emerald-600 bg-white shadow-sm hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
				>
					<span class="material-icons text-[18px]">history</span>
					Historial contrato
				</a>

				<a
					href="{{ route('novedades.historial_novedades') }}"
					class="inline-flex items-center justify-center gap-2 rounded-full border border-blue-500 px-5 py-2.5 text-xs font-semibold text-blue-600 bg-white shadow-sm hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
				>
					<span class="material-icons text-[18px]">list_alt</span>
					Historial novedades
				</a>

				@can('create_novedad')
				<button
					id="add-novelty-btn"
					type="button"
					onclick="window.__openNoveltyModal && window.__openNoveltyModal()"
					class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-500 px-6 py-3 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:bg-emerald-600 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
				>
					<span class="material-icons text-[20px]">add</span>
					Añadir Novedad
				</button>
				@endcan
			</div>
		</div>
	</div>

	@if (isset($periodoActivo) && $periodoActivo)
		<div class="flex items-center gap-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3">
			<span class="material-icons text-blue-400 text-[20px]">event</span>
			<div class="text-sm">
				<span class="font-semibold text-blue-700">Período activo:</span>
				<span class="text-blue-800 ml-1">{{ \Carbon\Carbon::parse($periodoActivo->fecha_inicio)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($periodoActivo->fecha_fin)->format('d/m/Y') }}</span>
				<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700">{{ strtoupper($periodoActivo->estado) }}</span>
				<span class="ml-3 text-gray-500">· Empleados: {{ ($empleadosBusqueda ?? collect())->count() }}</span>
			</div>
		</div>
	@else
		<div class="flex items-center gap-3 rounded-lg border border-amber-100 bg-amber-50 px-4 py-3">
			<span class="material-icons text-amber-400 text-[20px]">warning</span>
			<p class="text-sm text-amber-700">
				<span class="font-semibold">Sin período activo.</span> Se muestran todas las novedades sin filtro de período.
			</p>
		</div>
	@endif

	@if (session('success'))
		<div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
			{{ session('success') }}
		</div>
	@endif

	@if (session('error'))
		<div class="relative overflow-hidden rounded-xl bg-blue-50 p-5 border border-blue-100 shadow-sm">
			{{-- Decorative shapes (matching header style) --}}
			<div class="pointer-events-none absolute -top-10 -right-10 h-32 w-32 rounded-full bg-blue-100/60"></div>
			<div class="pointer-events-none absolute -bottom-8 -left-8 h-20 w-20 rotate-45 rounded-xl bg-gray-100/60"></div>

			<div class="relative z-10 flex items-center gap-4 text-blue-800">
				<div class="flex-shrink-0 w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shadow-sm">
					<span class="material-icons text-[22px]">info</span>
				</div>
				<div class="flex-1">
					<p class="text-sm font-bold tracking-tight uppercase text-blue-600/80 mb-0.5">Atención del Sistema</p>
					<p class="text-sm font-semibold leading-relaxed">{{ session('error') }}</p>
				</div>
			</div>
		</div>
	@endif

	<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
		<div class="bg-white border border-gray-100 rounded-xl p-4">
			<p class="text-xs uppercase tracking-wide text-gray-500">Total novedades</p>
			<p class="mt-1 text-2xl font-bold text-gray-900">{{ $novedades->total() }}</p>
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
				$tipo = strtoupper((string) ($novedad->tipo_novedad_codigo ?? \Illuminate\Support\Str::before((string) $tipoNombre, ' - ')));
				$tipoLabel = match ($tipo) {
					'TDE' => 'TDE - Traslado desde EPS',
					'TAE' => 'TAE - Traslado a EPS',
					'TDP' => 'TDP - Traslado desde AFP',
					'TAP' => 'TAP - Traslado a AFP',
					'VSP' => 'VSP - Variación permanente de salario',
					'VST' => 'VST - Variación transitoria de salario',
					'SLN' => 'SLN - Suspensión o licencia no remunerada',
					'IGE' => 'IGE - Incapacidad enfermedad general',
					'IRL' => 'IRL - Incapacidad riesgo laboral',
					'LMAT' => 'LMAT - Licencia de maternidad',
					'LPAT' => 'LPAT - Licencia de paternidad',
					'VAC' => 'VAC - Vacaciones',
					'VCT' => 'VCT - Variación centro de trabajo',
					'INC' => 'INC - Incapacidad',
					'LIC' => 'LIC - Licencia',
					default => $tipoNombre,
				};
				$iniciales = strtoupper(mb_substr($empleado->primer_nombre ?? 'N', 0, 1) . mb_substr($empleado->primer_apellido ?? 'N', 0, 1));
				$unidadCantidad = $novedad->unidad_cantidad ?? 'dias';
				$unidadLabel = $unidadCantidad === 'horas' ? 'horas' : 'días';
				$cantidadDisplay = rtrim(rtrim(number_format((float) $novedad->cantidad, 2, '.', ''), '0'), '.');

				$badgeClass = match ($tipo) {
					'IGE', 'IRL', 'INC' => 'bg-emerald-100 text-emerald-700',
					'LMAT', 'LPAT', 'LIC', 'VAC' => 'bg-blue-100 text-blue-700',
					'SLN' => 'bg-amber-100 text-amber-700',
					'VSP', 'VST', 'VCT' => 'bg-cyan-100 text-cyan-700',
					default => 'bg-gray-100 text-gray-700',
				};

				$naturaleza = strtoupper((string) ($novedad->tipo_movimiento ?? 'sin_movimiento'));
				$naturaleza = $naturaleza === 'DEVENGADO' ? 'DEVENGADO' : ($naturaleza === 'DEDUCCION' ? 'DEDUCCION' : 'SIN MOVIMIENTO');
				$naturalezaBadgeClass = $naturaleza === 'DEVENGADO'
					? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
					: ($naturaleza === 'DEDUCCION'
						? 'bg-red-50 text-red-700 border border-red-200'
						: 'bg-gray-50 text-gray-700 border border-gray-200');

				// Cálculos de referencia para visualización.
				$salarioBase = (float) ($novedad->salario_base ?? $novedad->salario?->contrato?->salario_base ?? 0);
				$diasNovedad = (float) ($novedad->dias ?? ($novedad->unidad_cantidad === 'dias' ? $novedad->cantidad : 0));
				$valorDia = $salarioBase > 0 ? ($salarioBase / 30) : 0;

				// Para incapacidades, si por datos antiguos pago llega en 0,
				// mostramos un estimado del pago de EMPRESA (no del total de días).
				$valorEmpresaIncapacidad = 0;
				if ($valorDia > 0 && $diasNovedad > 0) {
					if ($tipo === 'IGE') {
						$valorEmpresaIncapacidad = round($valorDia * min(2, $diasNovedad) * 0.6667);
					} elseif ($tipo === 'IRL') {
						$valorEmpresaIncapacidad = round($valorDia * min(1, $diasNovedad));
					} elseif ($tipo === 'INC') {
						$tipoIncap = strtolower((string) ($novedad->tipo_incapacidad ?? ''));
						if (in_array($tipoIncap, ['irl', 'riesgo_laboral'], true)) {
							$valorEmpresaIncapacidad = round($valorDia * min(1, $diasNovedad));
						} else {
							$valorEmpresaIncapacidad = round($valorDia * min(2, $diasNovedad) * 0.6667);
						}
					}
				}

				$esInformativo = in_array($tipo, ['SLN'], true) || ($tipo === 'LIC' && $naturaleza !== 'DEVENGADO');
				$valorInformativo = $esInformativo && $valorDia > 0 ? round($valorDia * $diasNovedad) : 0;

				$valorFallback = in_array($tipo, ['IGE', 'IRL', 'INC'], true)
					? $valorEmpresaIncapacidad
					: $valorInformativo;

				$pagoDisplay = ((float) $novedad->pago > 0) ? (float) $novedad->pago : $valorFallback;
				$pagoColorClass = $naturaleza === 'DEVENGADO' ? 'text-emerald-700' : ($naturaleza === 'DEDUCCION' || $esInformativo ? 'text-red-600' : 'text-gray-800');
				$pagoLabel = (in_array($tipo, ['IGE', 'IRL', 'INC'], true) && (float) $novedad->pago <= 0)
					? 'Pago empresa (estimado)'
					: (($esInformativo && (float) $novedad->pago <= 0) ? 'Deducción informativa' : 'Pago');
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
							@can('edit_novedad')
							<button
								type="button"
								onclick="window.__openEditByButton && window.__openEditByButton(this)"
								class="open-edit-modal text-gray-400 hover:text-blue-600 transition-colors p-2 rounded-full hover:bg-blue-50"
								title="Editar"
								data-novedad-id="{{ $novedad->id_novedad }}"
								data-doc="{{ $empleado->doc ?? '' }}"
								data-nombres="{{ \Illuminate\Support\Str::title(trim(($empleado->primer_nombre ?? '') . ' ' . ($empleado->otros_nombres ?? ''))) }}"
								data-apellidos="{{ \Illuminate\Support\Str::title(trim(($empleado->primer_apellido ?? '') . ' ' . ($empleado->segundo_apellido ?? ''))) }}"
								data-tipo="{{ $tipo }}"
								data-tipo-licencia="{{ $novedad->tipo_licencia ?? '' }}"
								data-tipo-incapacidad="{{ $novedad->tipo_incapacidad ?? '' }}"
								data-certificado-medico="{{ (int) ($novedad->certificado_medico ?? 0) }}"
								data-has-support-file="{{ !empty($novedad->soporte_medico_path) ? 1 : 0 }}"
								data-soporte-medico-nombre="{{ $novedad->soporte_medico_original_name ?? '' }}"
								data-id-eps="{{ $novedad->salario?->contrato?->id_eps ?? '' }}"
								data-id-afp="{{ $novedad->salario?->contrato?->id_afp ?? '' }}"
								data-id-arl="{{ $novedad->salario?->contrato?->id_arl ?? '' }}"
								data-licencia-remunerada="{{ (int) ($novedad->es_remunerado ?? $novedad->licencia_remunerada ?? 0) }}"
								data-unidad="{{ $unidadCantidad }}"
								data-cantidad="{{ (float) $novedad->cantidad }}"
								data-dias="{{ (float) ($novedad->dias ?? ($unidadCantidad === 'dias' ? $novedad->cantidad : 0)) }}"
								data-horas="{{ (float) ($novedad->horas ?? ($unidadCantidad === 'horas' ? $novedad->cantidad : 0)) }}"
								data-fecha-inicio="{{ $novedad->fecha_inicio ? \Carbon\Carbon::parse($novedad->fecha_inicio)->format('Y-m-d') : '' }}"
								data-fecha-fin="{{ $novedad->fecha_fin ? \Carbon\Carbon::parse($novedad->fecha_fin)->format('Y-m-d') : '' }}"
								data-pago="{{ (float) $novedad->pago }}"
								data-salario-base="{{ (float) ($novedad->salario_base ?? $novedad->salario?->contrato?->salario_base ?? 0) }}"
								data-vacaciones-balance="{{ (float) ($novedad->salario?->contrato?->benefitBalance?->vacaciones_balance ?? 0) }}"
								data-observaciones="{{ $novedad->observaciones ?? '' }}"
							>
								<span class="material-icons text-[20px]">edit</span>
							</button>
							@endcan

							@can('delete_novedad')
								<button
									type="button"
									onclick="window.__deleteNovedadByButton && window.__deleteNovedadByButton(this)"
									class="trigger-delete-direct text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50"
									title="Eliminar"
									data-novedad-id="{{ $novedad->id_novedad }}"
								>
									<span class="material-icons text-[20px]">delete</span>
								</button>
							@endcan
						</div>
					</div>

					<hr class="border-gray-100 mb-4">

					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-x-8 gap-y-4 text-sm">
						<div>
							<p class="text-gray-500 mb-1">Tipo de novedad</p>
							<div class="flex flex-wrap items-center gap-2">
								<p class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">{{ $tipoLabel }}</p>
								<p class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $naturalezaBadgeClass }}">{{ $naturaleza }}</p>
							</div>
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
							<p class="text-gray-500 mb-1">{{ $pagoLabel }}</p>
							<p class="font-semibold {{ $pagoColorClass }}">$ {{ number_format($pagoDisplay, 0, ',', '.') }}</p>
							@if($esInformativo && (float) $novedad->pago <= 0)
								<p class="text-[10px] text-amber-600 mt-0.5">Reduce días trabajados</p>
							@elseif(in_array($tipo, ['IGE', 'IRL', 'INC'], true) && (float) $novedad->pago <= 0)
								<p class="text-[10px] text-emerald-600 mt-0.5">Tope empresa aplicado (EPS/ARL cubre restante)</p>
							@endif
						</div>
					</div>

					@if(!empty($novedad->observaciones))
					<div class="mt-4 pt-3 border-t border-gray-100/60 flex items-start gap-2.5 text-sm text-gray-600 bg-gray-50/50 rounded-lg p-3">
						<span class="material-icons text-[16px] text-blue-500 mt-0.5">chat_bubble_outline</span>
						<p class="leading-relaxed whitespace-pre-line">{{ $novedad->observaciones }}</p>
					</div>
					@endif
				</div>
			</article>
		@empty
			<div class="bg-white border border-dashed border-gray-300 rounded-xl p-10 text-center">
				<i class="bi bi-card-list text-4xl text-gray-300"></i>
				<p class="mt-3 text-gray-600 font-medium">No hay novedades registradas por el momento.</p>
				<p class="text-sm text-gray-500 mt-1">Usa el botón <span class="font-semibold">Añadir Novedad</span> para crear la primera.</p>
			</div>
		@endforelse

		@if ($novedades->hasPages())
			<div class="mt-4">
				{{ $novedades->links() }}
			</div>
		@endif
	</section>
</div>

@include('novedades.registrar_novedad')
@include('novedades.editar_novedad')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@php
	$shouldOpenModalJs = session('open_novedad_modal') || ($errors->any() && old('_method') !== 'PUT');
	$shouldOpenEditModalJs = $errors->any() && old('_method') === 'PUT';
	$oldEditDataJs = [
		'id' => old('edit_novedad_id'),
		'doc' => old('empleado_id', old('doc_empleado')),
		'tipo' => old('tipo_novedad'),
		'tipo_licencia' => old('tipo_licencia'),
		'tipo_incapacidad' => old('tipo_incapacidad'),
		'certificado_medico' => old('certificado_medico'),
		'id_eps' => old('id_eps'),
		'id_afp' => old('id_afp'),
		'id_arl' => old('id_arl'),
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
		console.log('=== Novedades JS Loaded ===');
		const employees = @json($empleadosBusqueda ?? []);
		const shouldOpenModal = @json($shouldOpenModalJs);
		const shouldOpenEditModal = @json($shouldOpenEditModalJs);
		const oldEditData = @json($oldEditDataJs);
		const updateUrlTemplate = @json(route('novedades.update', ['id_novedad' => '__ID__']));
		const deleteUrlTemplate = @json(route('novedades.destroy', ['id_novedad' => '__ID__']));
		const previewCalculationUrl = @json(route('novedades.calculo.preview'));
		const empleadosApiUrl = @json(url('/api/empleados'));
		const closedPeriods = @json($periodosCerrados ?? []);
		const activePeriodStart = @json($periodoActivo ? $periodoActivo->fecha_inicio->format('Y-m-d') : null);
		const activePeriodEnd = @json($periodoActivo ? $periodoActivo->fecha_fin->format('Y-m-d') : null);

		console.log('Delete URL Template:', deleteUrlTemplate);
		
		// ── Early Global Assignment for Buttons ──
		window.__openNoveltyModal = () => {
			const m = document.getElementById('novelty-modal');
			if (m) {
				m.classList.remove('hidden');
				m.classList.add('flex');
			}
		};
		window.__closeNoveltyModal = () => {
			const m = document.getElementById('novelty-modal');
			if (m) {
				m.classList.remove('flex');
				m.classList.add('hidden');
			}
		};
		window.__openEditModal = () => {
			const m = document.getElementById('edit-novelty-modal');
			if (m) {
				m.classList.remove('hidden');
				m.classList.add('flex');
			}
		};
		window.__closeEditModal = () => {
			const m = document.getElementById('edit-novelty-modal');
			if (m) {
				m.classList.remove('flex');
				m.classList.add('hidden');
			}
		};

		const modal = document.getElementById('novelty-modal');
		const addNoveltyBtn = document.getElementById('add-novelty-btn');
		const cancelBtn = document.getElementById('cancel-btn');
		const closeModalBtn = document.getElementById('close-modal-btn');
		
		addNoveltyBtn?.addEventListener('click', window.__openNoveltyModal);
		cancelBtn?.addEventListener('click', window.__closeNoveltyModal);
		closeModalBtn?.addEventListener('click', window.__closeNoveltyModal);

		const form = document.getElementById('novelty-form');
		const employeeSearch = document.getElementById('employee-search');
		const docEmpleadoInput = document.getElementById('doc_empleado');
		const salarioBaseInput = document.getElementById('salario-base');
		const employeeDetails = document.getElementById('employee-details');
		const employeeNameInput = document.getElementById('employee-name');
		const employeeLastnameInput = document.getElementById('employee-lastname');
		const suggestions = document.getElementById('employee-suggestions');
		const employeeLoadingSpinner = document.getElementById('employee-loading-spinner');

		const noveltyType = document.getElementById('novelty-type');
		const unitQuantityInputs = document.querySelectorAll('input[name="unidad_cantidad"]');
		const quantityDaysInput = document.getElementById('quantity-days');
		const quantityHoursInput = document.getElementById('quantity-hours');
		const startDateInput = document.getElementById('start-date');
		const endDateInput = document.getElementById('end-date');
		const paymentDisplayInput = document.getElementById('payment-display');
		const paymentInput = document.getElementById('payment');
		const paymentAutoMessage = document.getElementById('payment-auto-message');
		const estimatedValueElement = document.getElementById('estimated-value');
		const estimatedNoteElement = document.getElementById('estimated-note');
		const noveltyNatureBadge = document.getElementById('novelty-nature-badge');
		const licenciaRemuneradaWrap = document.getElementById('licencia-remunerada-wrap');
		const licenciaRemuneradaInput = document.getElementById('licencia-remunerada');
		const remuneradaLabel = document.getElementById('remunerada-label');
		const observationsInput = document.getElementById('observaciones');
		const createUnitDaysRadio = document.getElementById('unit-days');
		const createUnitHoursRadio = document.getElementById('unit-hours');
		const tipoIncapacidadWrap = document.getElementById('tipo-incapacidad-wrap');
		const tipoIncapacidadInput = document.getElementById('tipo-incapacidad');
		const tipoLicenciaWrap = document.getElementById('tipo-licencia-wrap');
		const tipoLicenciaInput = document.getElementById('tipo-licencia');
		const certificadoMedicoWrap = document.getElementById('certificado-medico-wrap');
		const certificadoMedicoInput = document.getElementById('certificado-medico');
		const medicalSupportFileInput = document.getElementById('medical-support-file');
		const epsWrap = document.getElementById('eps-wrap');
		const epsIdInput = document.getElementById('eps-id');
		const afpWrap = document.getElementById('afp-wrap');
		const afpIdInput = document.getElementById('afp-id');
		const arlWrap = document.getElementById('arl-wrap');
		const arlIdInput = document.getElementById('arl-id');

		const editModal = document.getElementById('edit-novelty-modal');
		const closeEditModalBtn = document.getElementById('close-edit-modal-btn');
		const cancelEditBtn = document.getElementById('cancel-edit-btn');
		const editForm = document.getElementById('edit-novelty-form');
		const deleteNovedadBtn = document.getElementById('delete-novedad-btn');
		const deleteForm = document.getElementById('delete-novedad-form');
		const editButtons = document.querySelectorAll('.open-edit-modal');
		const deleteDirectButtons = document.querySelectorAll('.trigger-delete-direct');

		console.log('Edit buttons found:', editButtons.length);
		console.log('Delete buttons found:', deleteDirectButtons.length);
		console.log('Edit modal found:', !!editModal);
		console.log('Edit form found:', !!editForm);
		console.log('Delete form found:', !!deleteForm);

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
		const editPaymentAutoMessage = document.getElementById('edit-payment-auto-message');
		const editEstimatedValueElement = document.getElementById('edit-estimated-value');
		const editEstimatedNoteElement = document.getElementById('edit-estimated-note');
		const editNoveltyNatureBadge = document.getElementById('edit-novelty-nature-badge');
		const editLicenciaRemuneradaWrap = document.getElementById('edit-licencia-remunerada-wrap');
		const editLicenciaRemuneradaInput = document.getElementById('edit-licencia-remunerada');
		const editRemuneradaLabel = document.getElementById('edit-remunerada-label');
		const editObservacionesInput = document.getElementById('edit-observaciones');
		const editSalarioBaseInput = document.getElementById('edit-salario-base');
		const editUnitDaysRadio = document.getElementById('edit-unit-days');
		const editUnitHoursRadio = document.getElementById('edit-unit-hours');
		const editTipoIncapacidadWrap = document.getElementById('edit-tipo-incapacidad-wrap');
		const editTipoIncapacidadInput = document.getElementById('edit-tipo-incapacidad');
		const editTipoLicenciaWrap = document.getElementById('edit-tipo-licencia-wrap');
		const editTipoLicenciaInput = document.getElementById('edit-tipo-licencia');
		const editCertificadoMedicoWrap = document.getElementById('edit-certificado-medico-wrap');
		const editCertificadoMedicoInput = document.getElementById('edit-certificado-medico');
		const editMedicalSupportFileInput = document.getElementById('edit-medical-support-file');
		const editExistingMedicalSupportInput = document.getElementById('edit-existing-medical-support');
		const editMedicalSupportCurrentNote = document.getElementById('edit-medical-support-current-note');
		const editEpsWrap = document.getElementById('edit-eps-wrap');
		const editEpsIdInput = document.getElementById('edit-eps-id');
		const editAfpWrap = document.getElementById('edit-afp-wrap');
		const editAfpIdInput = document.getElementById('edit-afp-id');
		const editArlWrap = document.getElementById('edit-arl-wrap');
		const editArlIdInput = document.getElementById('edit-arl-id');
		const editVacacionesBalanceInput = document.getElementById('edit-vacaciones-balance');
		const editVacacionesBalanceDisplay = document.getElementById('edit-vacaciones-balance-display');
		const editVacacionesBalanceInfo = document.getElementById('edit-vacaciones-balance-info');
		const editStartDateLock = document.getElementById('edit-start-date-lock');
		const editStartDateWarning = document.getElementById('edit-start-date-warning');

		const editErrorElements = {
			noveltyType: document.getElementById('edit-novelty-type-error'),
			quantityUnit: document.getElementById('edit-quantity-unit-error'),
			quantityDays: document.getElementById('edit-quantity-days-error'),
			quantityHours: document.getElementById('edit-quantity-hours-error'),
			startDate: document.getElementById('edit-start-date-error'),
			endDate: document.getElementById('edit-end-date-error'),
			payment: document.getElementById('edit-payment-error'),
			medicalSupport: document.getElementById('edit-medical-support-file-error'),
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
			medicalSupport: document.getElementById('medical-support-file-error'),
		};

		const openModal = () => {
			if (!modal) return;
			modal.classList.remove('hidden');
			modal.classList.add('flex');
		};

		const closeModal = () => {
			if (!modal) return;
			modal.classList.remove('flex');
			modal.classList.add('hidden');
		};

		const openEditModal = () => {
			if (!editModal) return;
			editModal.classList.remove('hidden');
			editModal.classList.add('flex');
		};

		const closeEditModal = () => {
			if (!editModal) return;
			editModal.classList.remove('flex');
			editModal.classList.add('hidden');
		};

		window.__openNoveltyModal = openModal;
		window.__closeNoveltyModal = closeModal;
		window.__openEditModal = openEditModal;
		window.__closeEditModal = closeEditModal;

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

		const TitleCase = toTitleCase;

		const formatter = new Intl.NumberFormat('es-CO', {
			style: 'currency',
			currency: 'COP',
			maximumFractionDigits: 0,
		});

		const formatCurrency = (value) => {
			const numericValue = Number(value) || 0;
			return formatter.format(Math.round(numericValue));
		};

		const normalizeNoveltyType = (value) => {
			const normalized = normalize(value).replace(/\s+/g, '_').toUpperCase();
			const mapped = {
				TDE: 'TDE',
				TAE: 'TAE',
				TDP: 'TDP',
				TAP: 'TAP',
				VSP: 'VSP',
				VST: 'VST',
				SLN: 'SLN',
				IGE: 'IGE',
				IRL: 'IRL',
				LMAT: 'LMAT',
				LPAT: 'LPAT',
				VAC: 'VAC',
				VCT: 'VCT',
				INC: 'INC',
				LIC: 'LIC',
				INCAPACIDAD_ENFERMEDAD_GENERAL: 'IGE',
				INCAPACIDAD_LABORAL_ARL: 'IRL',
				LICENCIA_MATERNIDAD: 'LMAT',
				LICENCIA_PATERNIDAD: 'LPAT',
				SUSPENSION_CONTRATO: 'SLN',
				VACACIONES: 'VAC',
			};

			return mapped[normalized] || normalized;
		};

		const automaticNoveltyTypes = ['TDE', 'TAE', 'TDP', 'TAP', 'SLN', 'IGE', 'IRL', 'LMAT', 'LPAT', 'VAC', 'VCT', 'INC', 'LIC'];

		const isAutomaticNoveltyType = (tipo) => automaticNoveltyTypes.includes(normalizeNoveltyType(tipo));
		const isManualNoveltyType = (tipo) => ['VSP', 'VST'].includes(normalizeNoveltyType(tipo));

		const getMaxDaysByType = (tipo) => {
			const tipoNormalizado = normalizeNoveltyType(tipo);
			if (tipoNormalizado === 'LMAT') return 126;
			if (tipoNormalizado === 'LPAT') return 14;
			if (['IGE', 'IRL', 'INC'].includes(tipoNormalizado)) return 126;
			return 30;
		};

		const isDevengadoType = (tipo, esRemunerado = false) => {
			const tipoNormalizado = normalizeNoveltyType(tipo);
			if (tipoNormalizado === 'LIC') {
				return Boolean(esRemunerado);
			}

			return ['VST', 'IGE', 'IRL', 'LMAT', 'LPAT', 'VAC', 'INC'].includes(tipoNormalizado);
		};

		const isNeutralType = (tipo) => ['TDE', 'TAE', 'TDP', 'TAP', 'VSP', 'VCT'].includes(normalizeNoveltyType(tipo));

		const updateNatureBadge = (badgeElement, tipo, esRemunerado = false) => {
			if (!badgeElement) return;

			if (!tipo) {
				badgeElement.textContent = 'Naturaleza: -';
				badgeElement.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200';
				return;
			}

			if (isNeutralType(tipo)) {
				badgeElement.textContent = 'Naturaleza: SIN MOVIMIENTO';
				badgeElement.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200';
				return;
			}

			const isDevengado = isDevengadoType(tipo, esRemunerado);
			badgeElement.textContent = `Naturaleza: ${isDevengado ? 'DEVENGADO' : 'DEDUCCION'}`;
			badgeElement.className = isDevengado
				? 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200'
				: 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200';
		};

		const getEmployeeByDoc = (doc) => {
			if (!doc) return null;
			return employees.find((employee) => String(employee.doc || '') === String(doc)) || null;
		};

		const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

		const fetchCalculationPreview = async ({ doc, tipo, unidad, dias, horas, pagoManual, esRemunerado, tipoLicencia, tipoIncapacidad, certificadoMedico, idEps, idAfp, idArl, salarioBase }) => {
			const payload = {
				empleado_id: doc,
				tipo_novedad: normalizeNoveltyType(tipo),
				salario_base: Number(salarioBase || 0),
				unidad_cantidad: unidad,
				valor_manual: Number.isFinite(pagoManual) ? pagoManual : null,
				pago_manual: Number.isFinite(pagoManual) ? pagoManual : null,
				es_remunerado: Boolean(esRemunerado),
				tipo_licencia: tipoLicencia || null,
				tipo_incapacidad: tipoIncapacidad || null,
				certificado_medico: Boolean(certificadoMedico),
				id_eps: idEps || null,
				id_afp: idAfp || null,
				id_arl: idArl || null,
			};

			if (unidad === 'dias') {
				payload.dias = dias;
			}

			if (unidad === 'horas') {
				payload.horas = horas;
			}

			const response = await fetch(previewCalculationUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-CSRF-TOKEN': csrfToken,
				},
				body: JSON.stringify(payload),
			});

			const result = await response.json().catch(() => ({}));
			if (!response.ok || result?.success === false) {
				throw new Error(result?.message || 'No fue posible calcular la novedad.');
			}

			return result;
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

		const updateCreateEstimatedValue = async () => {
			if (!estimatedValueElement || !estimatedNoteElement) return;

			const doc = docEmpleadoInput.value;
			const unit = getSelectedCreateUnit() || 'dias';
			let cantidad = getCreateCantidad();
			// Fallback: leer directamente del input si getCreateCantidad retornó 0
			if (cantidad === 0 && quantityDaysInput && quantityDaysInput.value) {
				cantidad = Number(quantityDaysInput.value) || 0;
			}
			const tipo = noveltyType.value || '';
			const tipoNormalizado = normalizeNoveltyType(tipo);
			const esAutomatica = isAutomaticNoveltyType(tipoNormalizado);
			const esRemunerada = Boolean(licenciaRemuneradaInput?.checked);
			const esInformativo = ['SLN', 'LIC'].includes(tipoNormalizado) && !esRemunerada;
			const tipoLicencia = tipoLicenciaInput?.value || '';
			const tipoIncapacidad = tipoIncapacidadInput?.value || '';
			const certificadoMedico = Boolean(certificadoMedicoInput?.checked || medicalSupportFileInput?.files?.length);
			const idEps = epsIdInput?.value || '';
			const idAfp = afpIdInput?.value || '';
			const idArl = arlIdInput?.value || '';
			const salarioBase = Number(salarioBaseInput?.value || 0);
			const noCantidad = ['TDE', 'TAE', 'TDP', 'TAP', 'VSP', 'VST', 'VCT'].includes(tipoNormalizado);

			console.log('[DIAG-CREATE] updateCreateEstimatedValue:', { doc, unit, cantidad, tipo, tipoNormalizado, esAutomatica, esInformativo, salarioBase, noCantidad });

			updateNatureBadge(noveltyNatureBadge, tipo, esRemunerada);

			// ── Cálculo informativo local PRIORITARIO (no depende del backend) ──
			// Se ejecuta ANTES de cualquier validación para que el usuario vea el valor al escribir días
			if (esInformativo && salarioBase > 0 && cantidad > 0) {
				const localValorDia = salarioBase / 30;
				const localValorHora = salarioBase / 240;
				const valorInformativo = (unit === 'dias' ? localValorDia * cantidad : 0)
					+ (unit === 'horas' ? localValorHora * cantidad : 0);

				estimatedValueElement.textContent = formatCurrency(valorInformativo);
				if (noveltyNatureBadge) {
					noveltyNatureBadge.textContent = 'Naturaleza: DEDUCCIÓN (INF)';
					noveltyNatureBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200';
				}
				estimatedNoteElement.textContent = 'DEDUCCIÓN INFORMATIVA: El descuento real se aplica reduciendo los días trabajados en la nómina. Valor aprox. dejado de percibir: ' + formatCurrency(valorInformativo);
				return;
			}

			const paymentValue = !esAutomatica && paymentInput.value ? getPaymentNumber() : NaN;

			if (!esAutomatica) {
				estimatedValueElement.textContent = formatCurrency(Number.isFinite(paymentValue) ? paymentValue : 0);
				estimatedNoteElement.textContent = 'Novedad manual: se usará el valor ingresado en pago manual.';
				updateNatureBadge(noveltyNatureBadge, tipo, esRemunerada);
				return;
			}

			if (!doc || !tipo || (!noCantidad && (!unit || !Number.isFinite(cantidad) || cantidad < 0))) {
				estimatedValueElement.textContent = formatCurrency(0);
				estimatedNoteElement.textContent = 'Selecciona empleado, tipo y cantidad para calcular automáticamente.';
				return;
			}

			try {
				const result = await fetchCalculationPreview({
					doc,
					tipo,
					unidad: unit,
					dias: unit === 'dias' ? cantidad : 0,
					horas: unit === 'horas' ? cantidad : 0,
					pagoManual: esAutomatica ? null : paymentValue,
					esRemunerado: esRemunerada,
					tipoLicencia,
					tipoIncapacidad,
					certificadoMedico,
					idEps,
					idAfp,
					idArl,
					salarioBase,
				});

				const isDevengado = (result.operacion || '').toLowerCase() === 'devengado';
				const valorMostrar = result.valor || 0;
				
				estimatedValueElement.textContent = formatCurrency(valorMostrar);
				
				updateNatureBadge(noveltyNatureBadge, result.tipo_movimiento === 'sin_movimiento' ? 'TDE' : (result.operacion === 'devengado' ? 'VST' : 'SLN'), esRemunerada);
				if (esAutomatica) {
					const note = tipoNormalizado === 'LMAT'
						? 'Esta novedad se calcula automáticamente según el salario del empleado y los días registrados.'
						: 'Esta novedad se calcula automáticamente según el salario del empleado y la cantidad de días u horas registradas.';
					estimatedNoteElement.textContent = note;
				} else {
					estimatedNoteElement.textContent = isDevengado
						? 'Cálculo validado en backend. Naturaleza: DEVENGADO (suma al salario).'
						: 'Cálculo validado en backend. Naturaleza: DEDUCCION (resta al salario).';
				}
			} catch (error) {
				estimatedValueElement.textContent = formatCurrency(0);
				estimatedNoteElement.textContent = error.message || 'No se pudo calcular el valor automáticamente.';
			}
		};

		const updateEditEstimatedValue = async () => {
			if (!editEstimatedValueElement || !editEstimatedNoteElement) return;

			const doc = editDocEmpleadoInput.value;
			const unit = getSelectedEditUnit();
			let cantidad = getEditCantidad();
			const tipo = normalizeNoveltyType(editNoveltyTypeInput.value || '');
			const tipoNormalizado = normalizeNoveltyType(tipo);
			const esAutomatica = isAutomaticNoveltyType(tipoNormalizado);
			const esRemunerada = Boolean(editLicenciaRemuneradaInput?.checked);
			const esInformativo = ['SLN', 'LIC'].includes(tipoNormalizado) && !esRemunerada;
			const tipoLicencia = editTipoLicenciaInput?.value || '';
			const tipoIncapacidad = editTipoIncapacidadInput?.value || '';
			const certificadoMedico = Boolean(editCertificadoMedicoInput?.checked || editMedicalSupportFileInput?.files?.length || String(editExistingMedicalSupportInput?.value || '0') === '1');
			const idEps = editEpsIdInput?.value || '';
			const idAfp = editAfpIdInput?.value || '';
			const idArl = editArlIdInput?.value || '';
			const salarioBase = Number(editSalarioBaseInput?.value || 0);
			const noCantidad = ['TDE', 'TAE', 'TDP', 'TAP', 'VSP', 'VST', 'VCT'].includes(tipoNormalizado);

			updateNatureBadge(editNoveltyNatureBadge, tipo, esRemunerada);
			const paymentValue = !esAutomatica && editPaymentInput.value ? getEditPaymentNumber() : NaN;

			if (!esAutomatica && Number.isFinite(paymentValue) && paymentValue >= 0) {
				editEstimatedNoteElement.textContent = 'Se está usando el pago manual ingresado (cálculo respaldado por backend).';
			}

			if (!esAutomatica) {
				editEstimatedValueElement.textContent = formatCurrency(Number.isFinite(paymentValue) ? paymentValue : 0);
				editEstimatedNoteElement.textContent = 'Novedad manual: se usará el valor ingresado en pago manual.';
				updateNatureBadge(editNoveltyNatureBadge, tipo, esRemunerada);
				return;
			}

			if (!doc || !tipo || (!noCantidad && (!unit || !Number.isFinite(cantidad) || cantidad < 0))) {
				editEstimatedValueElement.textContent = formatCurrency(0);
				editEstimatedNoteElement.textContent = 'Selecciona empleado, tipo y cantidad para calcular automáticamente.';
				return;
			}

			// ── Cálculo informativo local (no depende del backend) ──
			if (esInformativo && salarioBase > 0 && cantidad > 0) {
				const localValorDia = salarioBase / 30;
				const localValorHora = salarioBase / 240;
				const valorInformativo = (unit === 'dias' ? localValorDia * cantidad : 0)
					+ (unit === 'horas' ? localValorHora * cantidad : 0);

				editEstimatedValueElement.textContent = formatCurrency(valorInformativo);
				editNoveltyNatureBadge.textContent = 'Naturaleza: DEDUCCIÓN (INF)';
				editNoveltyNatureBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200';
				editEstimatedNoteElement.textContent = 'DEDUCCIÓN INFORMATIVA: El descuento real se aplica reduciendo los días trabajados en la nómina. Valor aprox. dejado de percibir: ' + formatCurrency(valorInformativo);
				return;
			}

			try {
				const result = await fetchCalculationPreview({
					doc,
					tipo,
					unidad: unit,
					dias: unit === 'dias' ? cantidad : 0,
					horas: unit === 'horas' ? cantidad : 0,
					pagoManual: esAutomatica ? null : paymentValue,
					esRemunerado: esRemunerada,
					tipoLicencia,
					tipoIncapacidad,
					certificadoMedico,
					idEps,
					idAfp,
					idArl,
					salarioBase,
				});

				const isDevengado = (result.operacion || '').toLowerCase() === 'devengado';
				const valorMostrar = result.valor || 0;
				
				editEstimatedValueElement.textContent = formatCurrency(valorMostrar);
				
				updateNatureBadge(editNoveltyNatureBadge, result.tipo_movimiento === 'sin_movimiento' ? 'TDE' : (result.operacion === 'devengado' ? 'VST' : 'SLN'), esRemunerada);
				if (esAutomatica) {
					const note = tipoNormalizado === 'LMAT'
						? 'Esta novedad se calcula automáticamente según el salario del empleado y los días registrados.'
						: 'Esta novedad se calcula automáticamente según el salario del empleado y la cantidad de días u horas registradas.';
					editEstimatedNoteElement.textContent = note;
				} else {
					editEstimatedNoteElement.textContent = isDevengado
						? 'Cálculo validado en backend. Naturaleza: DEVENGADO (suma al salario).'
						: 'Cálculo validado en backend. Naturaleza: DEDUCCION (resta al salario).';
				}
			} catch (error) {
				editEstimatedValueElement.textContent = formatCurrency(0);
				editEstimatedNoteElement.textContent = error.message || 'No se pudo calcular el valor automáticamente.';
			}
		};

		const escapeHtml = (value) => String(value ?? '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');

		const clearEmployeeSelection = () => {
			docEmpleadoInput.value = '';
			salarioBaseInput.value = '';
			employeeNameInput.value = '';
			employeeLastnameInput.value = '';
			employeeDetails.classList.add('hidden');
			selectedEmployee = null;
			updateCreateEstimatedValue();
		};

		let highlightedSuggestionIndex = -1;
		let lastEmployeeResults = [];
		let selectedEmployee = null;
		let employeeSearchTimer = null;
		let employeeFetchSeq = 0;

		const getSuggestionOptions = () => Array.from(suggestions.querySelectorAll('.employee-option'));

		const updateSuggestionHighlight = (index) => {
			const options = getSuggestionOptions();
			options.forEach((option, optionIndex) => {
				const isActive = optionIndex === index;
				option.classList.toggle('bg-blue-50', isActive);
			});

			highlightedSuggestionIndex = index;
			if (index >= 0 && options[index]) {
				options[index].scrollIntoView({ block: 'nearest' });
			}
		};

		const selectEmployeeByDoc = (doc) => {
			const employee = lastEmployeeResults.find((item) => String(item.doc || '') === String(doc || ''))
				|| employees.find((item) => String(item.doc || '') === String(doc || ''));
			if (employee) {
				setEmployeeSelection(employee);
			}
		};

		const setEmployeeSelection = (employee) => {
			const doc = String(employee.doc || '').trim();
			docEmpleadoInput.value = doc;
			salarioBaseInput.value = Number(employee.salario_base || 0) > 0 ? String(employee.salario_base) : '';
			
			// Actualizar saldo de vacaciones para el modal de creación
			const vacBalance = Number(employee.vacaciones_balance || 0);
			const vacRegistradas = Number(employee.vacaciones_registradas || 0);
			const vacInput = document.getElementById('vacaciones-balance');
			const vacRegInput = document.getElementById('vacaciones-registradas');
			const vacDisplay = document.getElementById('vacaciones-balance-display');
			if (vacInput) vacInput.value = vacBalance;
			if (vacRegInput) vacRegInput.value = vacRegistradas;
			if (vacDisplay) vacDisplay.textContent = vacBalance.toFixed(2);

			const fullName = toTitleCase(employee.nombre_completo || '');
			employeeSearch.value = `${fullName} - ${doc}`.trim();
			employeeNameInput.value = toTitleCase(employee.nombres || fullName || '');
			employeeLastnameInput.value = toTitleCase(employee.apellidos || '');
			employeeDetails.classList.remove('hidden');
			suggestions.classList.add('hidden');
			suggestions.innerHTML = '';
			highlightedSuggestionIndex = -1;
			selectedEmployee = { 
				doc,
				id_eps: employee.id_eps || (employee.contrato ? employee.contrato.id_eps : null),
				eps_nombre: employee.eps_nombre || '',
				id_afp: employee.id_afp || (employee.contrato ? employee.contrato.id_afp : null),
				afp_nombre: employee.afp_nombre || '',
			};
			updateCreateEstimatedValue();
			updateCreateQuantityMode(); // Refrescar origenes si aplica
		};

		const fetchEmployeesSuggestions = async (query = '') => {
			const sequence = ++employeeFetchSeq;
			employeeLoadingSpinner?.classList.remove('hidden');

			try {
				const url = new URL(empleadosApiUrl, window.location.origin);
				url.searchParams.set('search', query);
				url.searchParams.set('limit', '12');

				const response = await fetch(url.toString(), {
					headers: {
						'Accept': 'application/json',
					},
				});

				if (!response.ok) {
					throw new Error('No se pudieron cargar empleados.');
				}

				const json = await response.json().catch(() => ({ data: [] }));
				const rows = Array.isArray(json?.data) ? json.data : [];

				if (sequence !== employeeFetchSeq) {
					return null;
				}

				return rows.map((row) => ({
					doc: String(row.documento || row.doc || row.id || ''),
					nombre_completo: String(row.nombre || ''),
					nombres: String(row.nombre || ''),
					apellidos: '',
					salario_base: Number(row.salario_base || 0),
					id_eps: row.id_eps || null,
					eps_nombre: row.eps_nombre || '',
					id_afp: row.id_afp || null,
					afp_nombre: row.afp_nombre || '',
				}));
			} finally {
				if (sequence === employeeFetchSeq) {
					employeeLoadingSpinner?.classList.add('hidden');
				}
			}
		};

		const renderSuggestions = async (query, options = {}) => {
			const { showAllOnEmpty = false, skipRemote = false, maxResults = 12 } = options;
			const normalizedQuery = normalize(query);

			if (!normalizedQuery && !showAllOnEmpty) {
				suggestions.innerHTML = '';
				suggestions.classList.add('hidden');
				highlightedSuggestionIndex = -1;
				lastEmployeeResults = [];
				return;
			}

			// Base local: respuesta inmediata en UI sin depender de la red.
			let matches = employees
				.filter((employee) => {
					const fullName = normalize(employee.nombre_completo);
					const documentNumber = normalize(employee.doc);
					return !normalizedQuery || fullName.includes(normalizedQuery) || documentNumber.includes(normalizedQuery);
				})
				.slice(0, maxResults);

			// Intento remoto opcional para refrescar datos (sin bloquear visualización local).
			if (!skipRemote) {
				try {
					const remoteMatches = await fetchEmployeesSuggestions(query);
					if (remoteMatches !== null && remoteMatches.length > 0) {
						matches = remoteMatches.slice(0, maxResults);
					}
				} catch (error) {
					// Mantener el resultado local cuando la API no esté disponible.
				}
			}

			lastEmployeeResults = matches;

			if (matches.length === 0) {
				suggestions.innerHTML = '<li class="px-4 py-3 text-sm text-gray-500">No hay empleados activos para la empresa de la sesion.</li>';
				suggestions.classList.remove('hidden');
				highlightedSuggestionIndex = -1;
				return;
			}

			suggestions.innerHTML = matches
				.map((employee) => {
					const fullName = escapeHtml(toTitleCase(employee.nombre_completo || ''));
					const doc = escapeHtml(employee.doc);
					return `<li>
						<button type="button" class="employee-option w-full text-left px-4 py-2.5 hover:bg-blue-50 border-b border-gray-100 last:border-b-0" data-doc="${doc}">
							<div class="text-sm font-medium text-gray-800">${fullName || 'Sin nombre'}</div>
							<div class="text-xs text-gray-500">Doc: ${doc}</div>
						</button>
					</li>`;
				})
				.join('');

			suggestions.classList.remove('hidden');
			highlightedSuggestionIndex = -1;

			suggestions.querySelectorAll('.employee-option').forEach((option) => {
				option.addEventListener('click', () => {
					selectEmployeeByDoc(option.dataset.doc);
					clearFieldError('employee');
					clearInvalid(employeeSearch);
				});

				option.addEventListener('mouseenter', () => {
					const optionsList = getSuggestionOptions();
					const index = optionsList.indexOf(option);
					if (index >= 0) {
						updateSuggestionHighlight(index);
					}
				});
			});
		};

		const showAllEmployeeSuggestions = () => {
			renderSuggestions('', { showAllOnEmpty: true, skipRemote: true, maxResults: 50 });
		};

		employeeSearch?.addEventListener('input', () => {
			clearEmployeeSelection();
			clearFieldError('employee');
			clearInvalid(employeeSearch);
			clearTimeout(employeeSearchTimer);
			employeeSearchTimer = setTimeout(() => {
				renderSuggestions(employeeSearch.value, { showAllOnEmpty: true });
			}, 220);
		});

		employeeSearch?.addEventListener('focus', () => {
			showAllEmployeeSuggestions();
		});

		employeeSearch?.addEventListener('click', () => {
			showAllEmployeeSuggestions();
		});

		employeeSearch?.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				suggestions.classList.add('hidden');
				highlightedSuggestionIndex = -1;
				return;
			}

			if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp' && event.key !== 'Enter') {
				return;
			}

			if (suggestions.classList.contains('hidden')) {
				renderSuggestions(employeeSearch.value, { showAllOnEmpty: true });
			}

			const optionsList = getSuggestionOptions();
			if (!optionsList.length) {
				return;
			}

			if (event.key === 'ArrowDown') {
				event.preventDefault();
				const nextIndex = highlightedSuggestionIndex < optionsList.length - 1 ? highlightedSuggestionIndex + 1 : 0;
				updateSuggestionHighlight(nextIndex);
				return;
			}

			if (event.key === 'ArrowUp') {
				event.preventDefault();
				const prevIndex = highlightedSuggestionIndex > 0 ? highlightedSuggestionIndex - 1 : optionsList.length - 1;
				updateSuggestionHighlight(prevIndex);
				return;
			}

			if (event.key === 'Enter' && highlightedSuggestionIndex >= 0) {
				event.preventDefault();
				selectEmployeeByDoc(optionsList[highlightedSuggestionIndex]?.dataset?.doc);
				clearFieldError('employee');
				clearInvalid(employeeSearch);
			}
		});

		document.addEventListener('click', (event) => {
			if (!suggestions.contains(event.target) && event.target !== employeeSearch) {
				suggestions.classList.add('hidden');
				highlightedSuggestionIndex = -1;
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

			[employeeSearch, noveltyType, quantityDaysInput, quantityHoursInput, startDateInput, endDateInput, paymentDisplayInput, medicalSupportFileInput]
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
			[editNoveltyTypeInput, editQuantityDaysInput, editQuantityHoursInput, editStartDateInput, editEndDateInput, editPaymentDisplayInput, editMedicalSupportFileInput]
				.forEach((input) => clearInvalid(input));
		};

		const medicalSupportTypes = ['IGE', 'IRL', 'INC'];
		const medicalSupportMaxBytes = 5 * 1024 * 1024;
		const medicalSupportExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
		const requiresMedicalSupport = (tipo) => medicalSupportTypes.includes(normalizeNoveltyType(tipo));

		const validateMedicalSupportFileInput = (fileInput, hasExistingSupport, errorHandler, fieldKey) => {
			if (!fileInput?.files?.length) {
				if (hasExistingSupport) {
					return true;
				}

				errorHandler(fieldKey, 'Debe adjuntar el certificado médico para esta novedad.');
				return false;
			}

			const [file] = fileInput.files;
			const extension = (file.name.split('.').pop() || '').toLowerCase();

			if (!medicalSupportExtensions.includes(extension)) {
				errorHandler(fieldKey, 'El soporte médico debe estar en formato PDF, JPG o PNG.');
				return false;
			}

			if (file.size > medicalSupportMaxBytes) {
				errorHandler(fieldKey, 'El soporte médico no puede superar los 5 MB.');
				return false;
			}

			return true;
		};

		const getSelectedCreateUnit = () => {
			const checked = document.querySelector('input[name="unidad_cantidad"]:checked');
			return checked ? checked.value : '';
		};

		const toggleCreateManualPayment = () => {
			const tipo = normalizeNoveltyType(noveltyType?.value || '');
			const esManual = isManualNoveltyType(tipo);
			const esAutomatica = !esManual;

			if (paymentDisplayInput) {
				paymentDisplayInput.readOnly = esAutomatica;
				paymentDisplayInput.disabled = esAutomatica;
				paymentDisplayInput.classList.toggle('bg-gray-100', esAutomatica);
				paymentDisplayInput.classList.toggle('cursor-not-allowed', esAutomatica);
			}

			if (paymentInput) {
				paymentInput.disabled = esAutomatica;
				if (esAutomatica) {
					paymentInput.value = '';
				}
			}

			if (esAutomatica && paymentDisplayInput) {
				paymentDisplayInput.value = '';
			}

			if (paymentAutoMessage) {
				paymentAutoMessage.classList.toggle('hidden', false);
				paymentAutoMessage.textContent = esManual
					? 'Esta novedad permite valor manual (VST).'
					: 'Esta novedad se calcula automáticamente segun salario y cantidad.';
			}
		};

		const updateLicenciaRemuneradaVisibility = () => {
			const tipo = normalizeNoveltyType(noveltyType?.value || '');
			const supportsRemunerada = tipo === 'LIC';
			if (licenciaRemuneradaWrap) {
				licenciaRemuneradaWrap.classList.toggle('hidden', !supportsRemunerada);
			}
			if (remuneradaLabel) {
				remuneradaLabel.textContent = 'Marcar como remunerada (no descontar)';
			}
			if (!supportsRemunerada && licenciaRemuneradaInput) {
				licenciaRemuneradaInput.checked = false;
			}

			if (tipoLicenciaWrap) {
				tipoLicenciaWrap.classList.toggle('hidden', tipo !== 'LIC');
			}
			if (tipoIncapacidadWrap) {
				tipoIncapacidadWrap.classList.toggle('hidden', !(tipo === 'INC'));
			}
			const mustShowMedicalSupport = requiresMedicalSupport(tipo);
			if (certificadoMedicoWrap) {
				certificadoMedicoWrap.classList.toggle('hidden', !mustShowMedicalSupport);
			}
			if (medicalSupportFileInput) {
				medicalSupportFileInput.required = mustShowMedicalSupport;
				if (!mustShowMedicalSupport) {
					medicalSupportFileInput.value = '';
				}
			}
			if (certificadoMedicoInput) {
				certificadoMedicoInput.checked = mustShowMedicalSupport ? Boolean(certificadoMedicoInput.checked || medicalSupportFileInput?.files?.length) : false;
			}
			if (epsWrap) {
				const isTransferEps = ['TDE', 'TAE'].includes(tipo);
				epsWrap.classList.toggle('hidden', !isTransferEps);
				
				const epsLabel = epsWrap.querySelector('label');
				if (epsLabel) {
					epsLabel.textContent = isTransferEps ? 'Nueva EPS (Destino)' : 'EPS';
				}
				
				let epsHint = document.getElementById('eps-origen-hint');
				if (!epsHint) {
					epsHint = document.createElement('p');
					epsHint.id = 'eps-origen-hint';
					epsHint.className = 'text-[10.5px] text-indigo-600 mt-1.5 font-medium flex items-center gap-1 bg-indigo-50 px-2 py-1 rounded w-fit';
					epsWrap.appendChild(epsHint);
				}
				
				if (isTransferEps && selectedEmployee && selectedEmployee.id_eps) {
					epsHint.innerHTML = `<span class="material-icons text-[12px]">info</span> <b>EPS Actual:</b> ${selectedEmployee.eps_nombre || 'N/A'}`;
					epsHint.classList.remove('hidden');
				} else {
					epsHint.classList.add('hidden');
				}
			}
			if (afpWrap) {
				const isTransferAfp = ['TDP', 'TAP'].includes(tipo);
				afpWrap.classList.toggle('hidden', !isTransferAfp);
				
				const afpLabel = afpWrap.querySelector('label');
				if (afpLabel) {
					afpLabel.textContent = isTransferAfp ? 'Nueva AFP (Destino)' : 'AFP';
				}
				
				let afpHint = document.getElementById('afp-origen-hint');
				if (!afpHint) {
					afpHint = document.createElement('p');
					afpHint.id = 'afp-origen-hint';
					afpHint.className = 'text-[10.5px] text-indigo-600 mt-1.5 font-medium flex items-center gap-1 bg-indigo-50 px-2 py-1 rounded w-fit';
					afpWrap.appendChild(afpHint);
				}
				
				if (isTransferAfp && selectedEmployee && selectedEmployee.id_afp) {
					afpHint.innerHTML = `<span class="material-icons text-[12px]">info</span> <b>AFP Actual:</b> ${selectedEmployee.afp_nombre || 'N/A'}`;
					afpHint.classList.remove('hidden');
				} else {
					afpHint.classList.add('hidden');
				}
			}
			if (arlWrap) {
				arlWrap.classList.toggle('hidden', tipo !== 'VCT');
			}
		};

		const updateCreateQuantityMode = () => {
			const type = normalizeNoveltyType(noveltyType.value || '');
			const isTraslado = ['TDE', 'TAE', 'TDP', 'TAP'].includes(type);
			const noCantidad = isTraslado || ['VSP', 'VST', 'VCT'].includes(type);
			const allowsHours = ['IGE', 'IRL', 'INC'].includes(type);
			const fixedDays = type === 'LMAT' ? 126 : (type === 'LPAT' ? 14 : null);
			const forceDaysOnly = ['LMAT', 'LPAT', 'VAC', 'SLN', 'LIC'].includes(type) || noCantidad;
			const maxDays = getMaxDaysByType(type);

			// Ocultar campos para traslados (TDE, TAE, TDP, TAP)
			const endWrap = endDateInput?.closest('div');
			const daysWrap = quantityDaysInput?.closest('div');
			const hoursWrap = quantityHoursInput?.closest('div');
			const unitWrap = document.querySelector('input[name="unidad_cantidad"]')?.closest('div');

			if (isTraslado) {
				if (endWrap) endWrap.classList.add('hidden');
				if (daysWrap) daysWrap.classList.add('hidden');
				if (hoursWrap) hoursWrap.classList.add('hidden');
				if (unitWrap) unitWrap.classList.add('hidden');
				
				// Restringir Fecha Inicio al periodo activo
				if (startDateInput && activePeriodStart && activePeriodEnd) {
					startDateInput.setAttribute('min', activePeriodStart);
					startDateInput.setAttribute('max', activePeriodEnd);
				}

				if (endDateInput && startDateInput && startDateInput.value) {
					endDateInput.value = startDateInput.value;
				}
			} else {
				if (endWrap) endWrap.classList.remove('hidden');
				if (daysWrap) daysWrap.classList.remove('hidden');
				if (hoursWrap) hoursWrap.classList.remove('hidden');
				if (unitWrap) unitWrap.classList.remove('hidden');
				
				if (startDateInput) {
					startDateInput.removeAttribute('min');
					startDateInput.removeAttribute('max');
				}
			}

			// Mostrar/Ocultar info de balance de vacaciones
			const vacBalanceInfo = document.getElementById('vacaciones-balance-info');
			if (vacBalanceInfo) {
				vacBalanceInfo.classList.toggle('hidden', type !== 'VAC');
			}

			if (forceDaysOnly && createUnitDaysRadio) {
				createUnitDaysRadio.checked = true;
				// NO dispatchEvent here — doing so calls updateCreateQuantityMode recursively!
			}
			if (createUnitDaysRadio) {
				createUnitDaysRadio.disabled = noCantidad;
			}
			if (createUnitHoursRadio) {
				createUnitHoursRadio.disabled = !allowsHours || noCantidad;
			}

			const unit = getSelectedCreateUnit();
			const isDias = unit === 'dias' && !noCantidad;
			const isHoras = unit === 'horas' && allowsHours;

			quantityDaysInput.disabled = !isDias;
			quantityHoursInput.disabled = !isHoras;
			quantityDaysInput.readOnly = fixedDays !== null;
			quantityDaysInput.max = String(maxDays);
			quantityDaysInput.min = '0.01';

			if (fixedDays !== null && isDias) {
				quantityDaysInput.value = String(fixedDays);
			}

			if (noCantidad) {
				quantityDaysInput.value = '';
				quantityHoursInput.value = '';
			}

			if (!isDias) quantityDaysInput.value = '';
			if (!isHoras) quantityHoursInput.value = '';
		};

		const getSelectedEditUnit = () => {
			const checked = document.querySelector('#edit-novelty-form input[name="unidad_cantidad"]:checked');
			return checked ? checked.value : '';
		};

		const toggleEditManualPayment = () => {
			const tipo = normalizeNoveltyType(editNoveltyTypeInput?.value || '');
			const esManual = isManualNoveltyType(tipo);
			const esAutomatica = !esManual;

			if (editPaymentDisplayInput) {
				editPaymentDisplayInput.readOnly = esAutomatica;
				editPaymentDisplayInput.disabled = esAutomatica;
				editPaymentDisplayInput.classList.toggle('bg-gray-100', esAutomatica);
				editPaymentDisplayInput.classList.toggle('cursor-not-allowed', esAutomatica);
			}

			if (editPaymentInput) {
				editPaymentInput.disabled = esAutomatica;
				if (esAutomatica) {
					editPaymentInput.value = '';
				}
			}

			if (esAutomatica && editPaymentDisplayInput) {
				editPaymentDisplayInput.value = '';
			}

			if (editPaymentAutoMessage) {
				editPaymentAutoMessage.classList.toggle('hidden', false);
				editPaymentAutoMessage.textContent = esManual
					? 'Esta novedad permite valor manual (VST).'
					: 'Esta novedad se calcula automáticamente segun salario y cantidad.';
			}
		};

		const updateEditLicenciaRemuneradaVisibility = () => {
			const tipo = normalizeNoveltyType(editNoveltyTypeInput?.value || '');
			const supportsRemunerada = tipo === 'LIC';
			if (editLicenciaRemuneradaWrap) {
				editLicenciaRemuneradaWrap.classList.toggle('hidden', !supportsRemunerada);
			}
			if (editRemuneradaLabel) {
				editRemuneradaLabel.textContent = 'Marcar como remunerada (no descontar)';
			}
			if (!supportsRemunerada && editLicenciaRemuneradaInput) {
				editLicenciaRemuneradaInput.checked = false;
			}

			if (editTipoLicenciaWrap) {
				editTipoLicenciaWrap.classList.toggle('hidden', tipo !== 'LIC');
			}
			if (editTipoIncapacidadWrap) {
				editTipoIncapacidadWrap.classList.toggle('hidden', !(tipo === 'INC'));
			}
			const mustShowMedicalSupport = requiresMedicalSupport(tipo);
			const hasExistingMedicalSupport = String(editExistingMedicalSupportInput?.value || '0') === '1';
			if (editCertificadoMedicoWrap) {
				editCertificadoMedicoWrap.classList.toggle('hidden', !mustShowMedicalSupport);
			}
			if (editMedicalSupportFileInput) {
				editMedicalSupportFileInput.required = mustShowMedicalSupport && !hasExistingMedicalSupport;
				if (!mustShowMedicalSupport) {
					editMedicalSupportFileInput.value = '';
				}
			}
			if (editMedicalSupportCurrentNote) {
				editMedicalSupportCurrentNote.classList.toggle('hidden', !mustShowMedicalSupport || !hasExistingMedicalSupport);
			}
			if (editCertificadoMedicoInput) {
				editCertificadoMedicoInput.checked = mustShowMedicalSupport
					? Boolean(editCertificadoMedicoInput.checked || editMedicalSupportFileInput?.files?.length || hasExistingMedicalSupport)
					: false;
			}
			if (editEpsWrap) {
				editEpsWrap.classList.toggle('hidden', !['TDE', 'TAE'].includes(tipo));
			}
			if (editAfpWrap) {
				editAfpWrap.classList.toggle('hidden', !['TDP', 'TAP'].includes(tipo));
			}
			if (editArlWrap) {
				editArlWrap.classList.toggle('hidden', tipo !== 'VCT');
			}
		};

		const updateEditQuantityMode = () => {
			const type = normalizeNoveltyType(editNoveltyTypeInput.value || '');
			const isTraslado = ['TDE', 'TAE', 'TDP', 'TAP'].includes(type);
			const noCantidad = isTraslado || ['VSP', 'VST', 'VCT'].includes(type);
			const allowsHours = ['IGE', 'IRL', 'INC'].includes(type);
			const fixedDays = type === 'LMAT' ? 126 : (type === 'LPAT' ? 14 : null);
			const forceDaysOnly = ['LMAT', 'LPAT', 'VAC', 'SLN', 'LIC'].includes(type) || noCantidad;
			const maxDays = getMaxDaysByType(type);

			// Ocultar campos para traslados (TDE, TAE, TDP, TAP)
			const endWrap = editEndDateInput?.closest('div');
			const daysWrap = editQuantityDaysInput?.closest('div');
			const hoursWrap = editQuantityHoursInput?.closest('div');
			const unitWrap = document.querySelector('#edit-novelty-form input[name="unidad_cantidad"]')?.closest('div');

			if (isTraslado) {
				if (endWrap) endWrap.classList.add('hidden');
				if (daysWrap) daysWrap.classList.add('hidden');
				if (hoursWrap) hoursWrap.classList.add('hidden');
				if (unitWrap) unitWrap.classList.add('hidden');
				
				// Restringir Fecha Inicio al periodo activo (Edición)
				if (editStartDateInput && activePeriodStart && activePeriodEnd) {
					editStartDateInput.setAttribute('min', activePeriodStart);
					editStartDateInput.setAttribute('max', activePeriodEnd);
				}

				if (editEndDateInput && editStartDateInput && editStartDateInput.value) {
					editEndDateInput.value = editStartDateInput.value;
				}
			} else {
				if (endWrap) endWrap.classList.remove('hidden');
				if (daysWrap) daysWrap.classList.remove('hidden');
				if (hoursWrap) hoursWrap.classList.remove('hidden');
				if (unitWrap) unitWrap.classList.remove('hidden');
				
				if (editStartDateInput) {
					editStartDateInput.removeAttribute('min');
					editStartDateInput.removeAttribute('max');
				}
			}

			if (forceDaysOnly && editUnitDaysRadio) {
				editUnitDaysRadio.checked = true;
				// NO dispatchEvent here — doing so would cause infinite recursion
			}
			if (editUnitDaysRadio) {
				editUnitDaysRadio.disabled = noCantidad;
			}
			if (editUnitHoursRadio) {
				editUnitHoursRadio.disabled = !allowsHours || noCantidad;
			}

			const unit = getSelectedEditUnit();
			const isDias = unit === 'dias' && !noCantidad;
			const isHoras = unit === 'horas' && allowsHours;

			editQuantityDaysInput.disabled = !isDias;
			editQuantityHoursInput.disabled = !isHoras;
			editQuantityDaysInput.readOnly = fixedDays !== null;
			editQuantityDaysInput.max = String(maxDays);
			editQuantityDaysInput.min = '0.01';

			if (fixedDays !== null && isDias) {
				editQuantityDaysInput.value = String(fixedDays);
			}

			if (noCantidad) {
				editQuantityDaysInput.value = '';
				editQuantityHoursInput.value = '';
			}

			if (!isDias) editQuantityDaysInput.value = '';
			if (!isHoras) editQuantityHoursInput.value = '';
		};

		const validateDateRange = () => {
			const startDate = startDateInput.value;
			const endDate = endDateInput.value;

		// Removido asignación de min para evitar validación nativa que bloquea inputs ocultos

			if (!startDate || !endDate) {
				clearFieldError('endDate');
				clearInvalid(endDateInput);
				return true;
			}

			const isValidRange = new Date(endDate) >= new Date(startDate);

			if (!isValidRange) {
				showFieldError('endDate', 'La fecha de fin no puede ser menor que la fecha de inicio.');
				markInvalid(endDateInput);
				return false;
			}

			clearFieldError('endDate');
			clearInvalid(endDateInput);
			return true;
		};

		const getPaymentNumber = () => {
			const value = (paymentDisplayInput.value || '').toString().replace(',', '.');
			const parsed = Number(value);
			return Number.isFinite(parsed) ? parsed : NaN;
		};

		const getEditPaymentNumber = () => {
			const value = (editPaymentDisplayInput.value || '').toString().replace(',', '.');
			const parsed = Number(value);
			return Number.isFinite(parsed) ? parsed : NaN;
		};

		paymentDisplayInput?.addEventListener('input', () => {
			if (paymentDisplayInput.disabled) {
				return;
			}

			if (!paymentDisplayInput.value) {
				paymentDisplayInput.value = '';
				paymentInput.value = '';
				updateCreateEstimatedValue();
				return;
			}

			const numericValue = Number(paymentDisplayInput.value);
			paymentInput.value = Number.isFinite(numericValue) ? String(numericValue) : '';
			clearFieldError('payment');
			clearInvalid(paymentDisplayInput);
			updateCreateEstimatedValue();
		});

		editPaymentDisplayInput?.addEventListener('input', () => {
			if (editPaymentDisplayInput.disabled) {
				return;
			}

			if (!editPaymentDisplayInput.value) {
				editPaymentDisplayInput.value = '';
				editPaymentInput.value = '';
				updateEditEstimatedValue();
				return;
			}

			const numericValue = Number(editPaymentDisplayInput.value);
			editPaymentInput.value = Number.isFinite(numericValue) ? String(numericValue) : '';
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
			toggleCreateManualPayment();
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
			toggleEditManualPayment();
			updateEditEstimatedValue();
		});

		quantityDaysInput?.addEventListener('input', updateCreateEstimatedValue);
		quantityHoursInput?.addEventListener('input', updateCreateEstimatedValue);
		tipoIncapacidadInput?.addEventListener('change', updateCreateEstimatedValue);
		tipoLicenciaInput?.addEventListener('change', updateCreateEstimatedValue);
		certificadoMedicoInput?.addEventListener('change', updateCreateEstimatedValue);
		epsIdInput?.addEventListener('change', updateCreateEstimatedValue);
		afpIdInput?.addEventListener('change', updateCreateEstimatedValue);
		arlIdInput?.addEventListener('change', updateCreateEstimatedValue);
		editQuantityDaysInput?.addEventListener('input', updateEditEstimatedValue);
		editQuantityHoursInput?.addEventListener('input', updateEditEstimatedValue);
		editTipoIncapacidadInput?.addEventListener('change', updateEditEstimatedValue);
		editTipoLicenciaInput?.addEventListener('change', updateEditEstimatedValue);
		editCertificadoMedicoInput?.addEventListener('change', updateEditEstimatedValue);
		editEpsIdInput?.addEventListener('change', updateEditEstimatedValue);
		editAfpIdInput?.addEventListener('change', updateEditEstimatedValue);
		editArlIdInput?.addEventListener('change', updateEditEstimatedValue);
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
			const foundEmployee = employees.find((employee) => employee.doc === preselectedDoc);
			if (foundEmployee) {
				setEmployeeSelection(foundEmployee);
			}
		}

		if (modal && !docEmpleadoInput.value && !employeeSearch.value) {
			modal.addEventListener('transitionend', () => {
				if (!modal.classList.contains('hidden') && document.activeElement === employeeSearch) {
					showAllEmployeeSuggestions();
				}
			});
		}

		if (paymentInput.value) {
			const initialPayment = Number(paymentInput.value);
			if (Number.isFinite(initialPayment)) {
				paymentDisplayInput.value = String(initialPayment);
			}
		}

		try {
			updateCreateQuantityMode();
			updateLicenciaRemuneradaVisibility();
			toggleCreateManualPayment();
			updateCreateEstimatedValue();
		} catch (e) { console.warn('Error during create init:', e); }

		startDateInput?.addEventListener('change', validateDateRange);
		endDateInput?.addEventListener('change', validateDateRange);
		validateDateRange();

		// ── Auto-fill fecha_fin based on tipo novedad duration ─────────────────
		const DURACIONES_FIJAS = { LMAT: 126, LPAT: 14, LIC: 30 };

		const autoFillFechaFin = (tipoInput, fechaInicioInput, fechaFinInput, cantidadInput = null) => {
			const tipo = normalizeNoveltyType(tipoInput?.value || '');
			const duracionFija = DURACIONES_FIJAS[tipo];
			const fechaInicio = fechaInicioInput?.value;
			
			if (!fechaInicio) return;

			let dias = 0;
			if (duracionFija) {
				dias = duracionFija;
			} else if (cantidadInput) {
				// Para tipos con días editables (SLN, VAC, IGE, etc.)
				const unit = getSelectedCreateUnit(); // O getSelectedEditUnit si estamos en edit
				// Nota: Para simplificar, asumimos que si hay cantidadInput y es dias, lo usamos
				const val = Number(cantidadInput.value || 0);
				if (val > 0) dias = val;
			}
			
			if (dias <= 0 && !['TDE', 'TAE', 'TDP', 'TAP'].includes(tipo)) return;
			
			// Si es traslado, la duración es 1 día (inicio = fin)
			if (['TDE', 'TAE', 'TDP', 'TAP'].includes(tipo)) {
				dias = 1;
			}
			
			try {
				const fin = new Date(fechaInicio);
				fin.setDate(fin.getDate() + dias - 1);
				const yyyy = fin.getFullYear();
				const mm = String(fin.getMonth() + 1).padStart(2, '0');
				const dd = String(fin.getDate()).padStart(2, '0');
				fechaFinInput.value = `${yyyy}-${mm}-${dd}`;
			} catch (error) {
				console.warn('Error al calcular fecha_fin:', error);
			}
		};

		// Listeners para auto-cálculo de fecha_fin
		if (startDateInput) {
			startDateInput.addEventListener('change', () => {
				autoFillFechaFin(noveltyType, startDateInput, endDateInput, quantityDaysInput);
				validateDateRange();
				updateCreateEstimatedValue();
			});
			startDateInput.addEventListener('input', () => {
				autoFillFechaFin(noveltyType, startDateInput, endDateInput, quantityDaysInput);
			});
		}

		if (quantityDaysInput) {
			quantityDaysInput.addEventListener('input', () => {
				autoFillFechaFin(noveltyType, startDateInput, endDateInput, quantityDaysInput);
				updateCreateEstimatedValue();
			});
		}

		if (noveltyType) {
			noveltyType.addEventListener('change', () => {
				if (startDateInput?.value) {
					autoFillFechaFin(noveltyType, startDateInput, endDateInput, quantityDaysInput);
				}
				// Mostrar mensaje si esta novedad tiene duración fija
				const tipo = normalizeNoveltyType(noveltyType.value || '');
				const duracion = DURACIONES_FIJAS[tipo];
				if (paymentAutoMessage) {
					if (duracion) {
						paymentAutoMessage.classList.toggle('hidden', false);
						paymentAutoMessage.textContent = `Esta novedad tiene duración fija de ${duracion} días. La fecha fin se calculará automáticamente.`;
					} else {
						paymentAutoMessage.classList.toggle('hidden', true);
					}
				}
				updateCreateEstimatedValue();
			});
		}

		const validateEditDateRange = () => {
			const startDate = editStartDateInput.value;
			const endDate = editEndDateInput.value;

		// Removido asignación de min para evitar validación nativa en inputs ocultos

			if (!startDate || !endDate) {
				clearEditFieldError('endDate');
				clearInvalid(editEndDateInput);
				return true;
			}

			const isValidRange = new Date(endDate) >= new Date(startDate);
			if (!isValidRange) {
				showEditFieldError('endDate', 'La fecha de fin no puede ser menor que la fecha de inicio.');
				markInvalid(editEndDateInput);
				return false;
			}

			clearEditFieldError('endDate');
			clearInvalid(editEndDateInput);
			return true;
		};

		editStartDateInput?.addEventListener('change', () => {
			autoFillFechaFin(editNoveltyTypeInput, editStartDateInput, editEndDateInput);
			validateEditDateRange();
		});
		editStartDateInput?.addEventListener('input', () => {
			autoFillFechaFin(editNoveltyTypeInput, editStartDateInput, editEndDateInput);
		});
		editEndDateInput?.addEventListener('change', validateEditDateRange);
		editNoveltyTypeInput?.addEventListener('change', () => {
			if (editStartDateInput?.value) autoFillFechaFin(editNoveltyTypeInput, editStartDateInput, editEndDateInput);
			// Mostrar mensaje si esta novedad tiene duración fija
			const tipo = normalizeNoveltyType(editNoveltyTypeInput.value || '');
			const duracion = DURACIONES_FIJAS[tipo];
			if (editPaymentAutoMessage) {
				if (duracion) {
					editPaymentAutoMessage.classList.toggle('hidden', false);
					editPaymentAutoMessage.textContent = `Esta novedad tiene duración fija de ${duracion} días. La fecha fin se calculará automáticamente.`;
				}
			}
		});

		medicalSupportFileInput?.addEventListener('change', () => {
			clearFieldError('medicalSupport');
			clearInvalid(medicalSupportFileInput);
			if (certificadoMedicoInput) {
				certificadoMedicoInput.checked = Boolean(medicalSupportFileInput.files?.length);
			}
			updateCreateEstimatedValue();
		});

		editMedicalSupportFileInput?.addEventListener('change', () => {
			clearEditFieldError('medicalSupport');
			clearInvalid(editMedicalSupportFileInput);
			if (editCertificadoMedicoInput && editMedicalSupportFileInput.files?.length) {
				editCertificadoMedicoInput.checked = true;
			}
			updateEditEstimatedValue();
		});

		const setEditModalData = (data) => {
			editNovedadIdInput.value = data.id || '';
			editDocEmpleadoInput.value = data.doc || '';
			editEmployeeDocInput.value = data.doc || '';
			editEmployeeNameInput.value = toTitleCase(data.nombres || '');
			editEmployeeLastnameInput.value = toTitleCase(data.apellidos || '');
			editNoveltyTypeInput.value = normalizeNoveltyType(data.tipo || '');
			
			if (editSalarioBaseInput) {
				editSalarioBaseInput.value = data.salarioBase || '0';
			}

			// Manejo de saldo de vacaciones
			const vacBalance = Number(data.vacacionesBalance || 0);
			if (editVacacionesBalanceInput) editVacacionesBalanceInput.value = vacBalance;
			if (editVacacionesBalanceDisplay) editVacacionesBalanceDisplay.textContent = vacBalance.toFixed(2);
			
			// Buscar vacaciones registradas en el array global de empleados si no viene en data
			let vacRegistradas = Number(data.vacacionesRegistradas || 0);
			if (vacRegistradas === 0 && data.doc) {
				const emp = employees.find(e => String(e.doc) === String(data.doc));
				if (emp) vacRegistradas = Number(emp.vacaciones_registradas || 0);
			}
			const editVacRegInput = document.getElementById('edit-vacaciones-registradas');
			if (editVacRegInput) editVacRegInput.value = vacRegistradas;

			const originalDaysValue = data.dias || (data.unidad === 'dias' ? data.cantidad : 0);
			const editOrigDaysInput = document.getElementById('edit-original-days');
			if (editOrigDaysInput) editOrigDaysInput.value = originalDaysValue;

			if (editVacacionesBalanceInfo) {
				editVacacionesBalanceInfo.classList.toggle('hidden', data.tipo !== 'VAC' || !data.vacacionesBalance);
			}

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

			// ── Lógica de Periodos Cerrados (Bloqueo) ──────────────────────────
			const fechaInicioVal = data.fechaInicio || '';
			const isClosed = closedPeriods?.some(p => {
				if (!fechaInicioVal || !p.fecha_inicio || !p.fecha_fin) return false;
				return fechaInicioVal >= p.fecha_inicio && fechaInicioVal <= p.fecha_fin;
			});

			if (editStartDateInput) {
				editStartDateInput.readOnly = isClosed;
				if (isClosed) {
					editStartDateInput.classList.add('bg-gray-100', 'cursor-not-allowed');
					editStartDateInput.title = 'No se puede editar la fecha de inicio de un periodo cerrado';
				} else {
					editStartDateInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
					editStartDateInput.title = '';
				}
			}
			if (editStartDateLock) editStartDateLock.classList.toggle('hidden', !isClosed);
			if (editStartDateWarning) editStartDateWarning.classList.toggle('hidden', !isClosed);
			if (deleteNovedadBtn) deleteNovedadBtn.classList.toggle('hidden', isClosed);
			// ──────────────────────────────────────────────────────────────────

			if (editObservacionesInput) {
				editObservacionesInput.value = data.observaciones || '';
			}
			if (editTipoLicenciaInput) {
				editTipoLicenciaInput.value = data.tipoLicencia || '';
			}
			if (editTipoIncapacidadInput) {
				editTipoIncapacidadInput.value = data.tipoIncapacidad || '';
			}
			const hasSupportFile = String(data.hasSupportFile ?? '0') === '1';
			if (editExistingMedicalSupportInput) {
				editExistingMedicalSupportInput.value = hasSupportFile ? '1' : '0';
			}
			if (editMedicalSupportFileInput) {
				editMedicalSupportFileInput.value = '';
			}
			if (editMedicalSupportCurrentNote) {
				const supportName = data.soporteMedicoNombre || 'un archivo previamente cargado';
				editMedicalSupportCurrentNote.textContent = `Ya existe un soporte médico asociado (${supportName}). Solo adjunta uno nuevo si deseas reemplazarlo.`;
			}
			if (editCertificadoMedicoInput) {
				editCertificadoMedicoInput.checked = String(data.certificadoMedico ?? '0') === '1' || hasSupportFile;
			}
			if (editEpsIdInput) {
				editEpsIdInput.value = data.idEps || '';
			}
			if (editAfpIdInput) {
				editAfpIdInput.value = data.idAfp || '';
			}
			if (editArlIdInput) {
				editArlIdInput.value = data.idArl || '';
			}

			const parsedPago = Number(data.pago || 0);
			if (Number.isFinite(parsedPago) && parsedPago !== 0) {
				editPaymentInput.value = String(parsedPago);
				editPaymentDisplayInput.value = formatter.format(parsedPago);
			} else {
				editPaymentInput.value = '';
				editPaymentDisplayInput.value = '';
			}

			if (editForm && data?.id) {
				editForm.action = updateUrlTemplate.replace('__ID__', String(data.id));
			}
			if (deleteForm && data?.id) {
				deleteForm.action = deleteUrlTemplate.replace('__ID__', String(data.id));
			}

			updateEditQuantityMode();
			updateEditLicenciaRemuneradaVisibility();
			toggleEditManualPayment();
			validateEditDateRange();
			updateEditEstimatedValue();
		};

		document.addEventListener('click', (e) => {
			const editButton = e.target.closest('.open-edit-modal');
			if (!editButton) return;

			e.preventDefault();
			e.stopPropagation();
			console.log('Edit button clicked', editButton.dataset);

			try {
				clearAllEditErrors();
				setEditModalData({
					id: editButton.dataset.novedadId,
					doc: editButton.dataset.doc,
					nombres: editButton.dataset.nombres,
					apellidos: editButton.dataset.apellidos,
					tipo: editButton.dataset.tipo,
					licenciaRemunerada: editButton.dataset.licenciaRemunerada,
					unidad: editButton.dataset.unidad,
					cantidad: editButton.dataset.cantidad,
					dias: editButton.dataset.dias,
					horas: editButton.dataset.horas,
					fechaInicio: editButton.dataset.fechaInicio,
					fechaFin: editButton.dataset.fechaFin,
					pago: editButton.dataset.pago,
					salarioBase: editButton.dataset.salarioBase,
					tipoLicencia: editButton.dataset.tipoLicencia,
					tipoIncapacidad: editButton.dataset.tipoIncapacidad,
					certificadoMedico: editButton.dataset.certificadoMedico,
					hasSupportFile: editButton.dataset.hasSupportFile,
					soporteMedicoNombre: editButton.dataset.soporteMedicoNombre,
					idEps: editButton.dataset.idEps,
					idAfp: editButton.dataset.idAfp,
					idArl: editButton.dataset.idArl,
					observaciones: editButton.dataset.observaciones,
					vacacionesBalance: editButton.dataset.vacacionesBalance,
				});
				openEditModal();
			} catch (error) {
				console.error('Error al abrir modal de edición:', error);
				alert('Error al abrir el modal: ' + error.message);
			}
		});

		const confirmDeleteNovedad = async () => {
			if (window.Swal) {
				const result = await Swal.fire({
				title: '¿Eliminar esta novedad?',
				html: '<p class="text-gray-600 text-sm mt-2">Esta acción no se puede deshacer. La novedad será eliminada permanentemente del sistema.</p>',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: '<i class="bi bi-trash3 mr-2"></i>Sí, eliminar',
				cancelButtonText: '<i class="bi bi-x-circle mr-2"></i>Cancelar',
				confirmButtonColor: '#dc2626',
				cancelButtonColor: '#6b7280',
				reverseButtons: true,
				focusCancel: true,
				customClass: {
					popup: 'rounded-2xl shadow-2xl border border-gray-100',
					title: 'text-xl font-bold text-gray-800',
					htmlContainer: 'text-gray-600',
					confirmButton: 'px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300',
					cancelButton: 'px-6 py-3 rounded-lg font-semibold shadow-sm hover:shadow-md transition-all duration-300',
				},
				buttonsStyling: true,
				allowOutsideClick: false,
				allowEscapeKey: true,
				showClass: {
					popup: 'animate__animated animate__fadeInDown animate__faster'
				},
				hideClass: {
					popup: 'animate__animated animate__fadeOutUp animate__faster'
				}
			});

			return Boolean(result.isConfirmed);
		}
		return confirm('¿Seguro que deseas eliminar esta novedad? Esta acción no se puede deshacer.');
		};

		// ── Exponer funciones a window para compatibilidad con onclick ──
		window.__openEditByButton = (button) => {
			if (!button) return;
			try {
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
					salarioBase: button.dataset.salarioBase,
					tipoLicencia: button.dataset.tipoLicencia,
					tipoIncapacidad: button.dataset.tipoIncapacidad,
					certificadoMedico: button.dataset.certificadoMedico,
					hasSupportFile: button.dataset.hasSupportFile,
					soporteMedicoNombre: button.dataset.soporteMedicoNombre,
					idEps: button.dataset.idEps,
					idAfp: button.dataset.idAfp,
					idArl: button.dataset.idArl,
					observaciones: button.dataset.observaciones,
					vacacionesBalance: button.dataset.vacacionesBalance,
				});
				openEditModal();
			} catch (error) {
				console.error('Error al abrir modal de edición (onclick):', error);
			}
		};

		window.__deleteNovedadByButton = async (button) => {
			if (!button) return;
			const id = button.dataset.novedadId;
			const fechaInicio = button.dataset.fechaInicio;
			if (!id) return;

			// Validar periodo cerrado
			const isClosed = closedPeriods?.some(p => {
				if (!fechaInicio || !p.fecha_inicio || !p.fecha_fin) return false;
				return fechaInicio >= p.fecha_inicio && fechaInicio <= p.fecha_fin;
			});

			if (isClosed) {
				if (window.Swal) {
					Swal.fire({
						icon: 'error',
						title: 'Acción no permitida',
						text: 'No se puede eliminar esta novedad porque pertenece a un periodo ya cerrado.',
						confirmButtonColor: '#1565C0'
					});
				} else {
					alert('No se puede eliminar esta novedad porque pertenece a un periodo ya cerrado.');
				}
				return;
			}

			try {
				const confirmed = await confirmDeleteNovedad();
				if (confirmed) {
					deleteForm.action = deleteUrlTemplate.replace('__ID__', id);
					deleteForm.submit();
				}
			} catch (error) {
				console.error('Error al eliminar (onclick):', error);
			}
		};

		deleteNovedadBtn?.addEventListener('click', async () => {
			// El botón en el modal se oculta/muestra preventivamente, pero agregamos validación aquí también
			const confirmed = await confirmDeleteNovedad();
			if (confirmed) {
				if (!deleteForm?.action) {
					console.error('No se encontró la acción del formulario de eliminación.');
					return;
				}
				deleteForm.submit();
			}
		});

		document.addEventListener('click', async (e) => {
			const deleteButton = e.target.closest('.trigger-delete-direct');
			if (!deleteButton) return;

			e.preventDefault();
			e.stopPropagation();
			console.log('Delete button clicked', deleteButton.dataset);

			const id = deleteButton.dataset.novedadId;
			const fechaInicio = deleteButton.dataset.fechaInicio;
			if (!id) {
				console.error('No se encontró ID de novedad');
				return;
			}

			// Validar si está en un periodo cerrado (misma lógica que el candado)
			const isClosed = closedPeriods?.some(p => {
				if (!fechaInicio || !p.fecha_inicio || !p.fecha_fin) return false;
				return fechaInicio >= p.fecha_inicio && fechaInicio <= p.fecha_fin;
			});

			if (isClosed) {
				if (window.Swal) {
					Swal.fire({
						icon: 'error',
						title: 'Acción no permitida',
						text: 'No se puede eliminar esta novedad porque parte de ella ya ha sido liquidada en un periodo cerrado.',
						confirmButtonColor: '#1565C0'
					});
				} else {
					alert('No se puede eliminar esta novedad porque parte de ella ya ha sido liquidada en un periodo cerrado.');
				}
				return;
			}

			try {
				const confirmed = await confirmDeleteNovedad();
				if (confirmed) {
					if (!deleteForm) {
						console.error('No se encontró el formulario de eliminación.');
						return;
					}
					deleteForm.action = deleteUrlTemplate.replace('__ID__', id);
					console.log('Submitting delete form to:', deleteForm.action);
					deleteForm.submit();
				}
			} catch (error) {
				console.error('Error al eliminar:', error);
				alert('Error al eliminar: ' + error.message);
			}
		});

		form?.addEventListener('submit', (event) => {
			clearAllErrors();
			let isValid = true;
			const hasSelectedEmployee = Boolean(docEmpleadoInput.value && docEmpleadoInput.value.trim() !== '');

			if (!hasSelectedEmployee) {
				isValid = false;
				showFieldError('employee', 'Debe buscar y seleccionar un empleado válido.');
				markInvalid(employeeSearch);
				
				// Alerta visible para el usuario (como en SLN/Licencias)
				if (window.Swal) {
					Swal.fire({
						icon: 'warning',
						title: 'Empleado no seleccionado',
						text: 'Debe buscar y seleccionar un empleado válido antes de guardar la novedad.',
						confirmButtonColor: '#1565C0'
					});
				} else {
					alert('Debe buscar y seleccionar un empleado válido antes de guardar la novedad.');
				}
			}

			if (!noveltyType.value) {
				isValid = false;
				showFieldError('noveltyType', 'Debe seleccionar el tipo de novedad.');
				markInvalid(noveltyType);
				
				if (window.Swal && isValid === true) { // Solo si no falló el empleado
					Swal.fire({
						icon: 'warning',
						title: 'Tipo de novedad faltante',
						text: 'Debe seleccionar el tipo de novedad.',
						confirmButtonColor: '#1565C0'
					});
				}
			}

			const selectedType = normalizeNoveltyType(noveltyType.value || '');
			if (selectedType === 'LIC' && !tipoLicenciaInput?.value) {
				isValid = false;
				showFieldError('noveltyType', 'Debe seleccionar el tipo de licencia.');
			}
			if (requiresMedicalSupport(selectedType)) {
				clearFieldError('medicalSupport');
				const medicalSupportValid = validateMedicalSupportFileInput(medicalSupportFileInput, false, showFieldError, 'medicalSupport');
				if (!medicalSupportValid) {
					isValid = false;
					markInvalid(medicalSupportFileInput);
				}
				if (certificadoMedicoInput && medicalSupportValid) {
					certificadoMedicoInput.checked = true;
				}
			}
			if (['TDE', 'TAE'].includes(selectedType)) {
				if (!epsIdInput?.value) {
					isValid = false;
					showFieldError('noveltyType', 'Debe seleccionar la EPS para el traslado.');
					if (window.Swal) {
						Swal.fire({
							icon: 'warning',
							title: 'EPS requerida',
							text: 'Debe seleccionar la EPS para el traslado.',
							confirmButtonColor: '#1565C0'
						});
					}
				} else if (selectedEmployee && String(selectedEmployee.id_eps) === String(epsIdInput.value)) {
					isValid = false;
					const msg = 'La EPS destino no puede ser igual a la EPS actual de este empleado.';
					showFieldError('noveltyType', msg);
					if (window.Swal) {
						Swal.fire({ icon: 'warning', title: 'Traslado inválido', text: msg, confirmButtonColor: '#1565C0' });
					}
				}
			}
			if (['TDP', 'TAP'].includes(selectedType)) {
				if (!afpIdInput?.value) {
					isValid = false;
					showFieldError('noveltyType', 'Debe seleccionar la AFP para el traslado.');
					if (window.Swal) {
						Swal.fire({
							icon: 'warning',
							title: 'AFP requerida',
							text: 'Debe seleccionar la AFP para el traslado.',
							confirmButtonColor: '#1565C0'
						});
					}
				} else if (selectedEmployee && String(selectedEmployee.id_afp) === String(afpIdInput.value)) {
					isValid = false;
					const msg = 'La AFP destino no puede ser igual a la AFP actual de este empleado.';
					showFieldError('noveltyType', msg);
					if (window.Swal) {
						Swal.fire({ icon: 'warning', title: 'Traslado inválido', text: msg, confirmButtonColor: '#1565C0' });
					}
				}
			}
			if (selectedType === 'VCT' && !arlIdInput?.value) {
				isValid = false;
				showFieldError('noveltyType', 'Debe seleccionar la ARL para la variación de centro de trabajo.');
				if (window.Swal) {
					Swal.fire({
						icon: 'warning',
						title: 'ARL requerida',
						text: 'Debe seleccionar la ARL para la variación de centro de trabajo.',
						confirmButtonColor: '#1565C0'
					});
				}
			}

			const selectedUnit = getSelectedCreateUnit();
			const typeWithoutQuantity = ['TDE', 'TAE', 'TDP', 'TAP', 'VSP', 'VST', 'VCT'].includes(selectedType);
			if (!typeWithoutQuantity && !selectedUnit) {
				isValid = false;
				showFieldError('quantityUnit', 'Debe seleccionar si la cantidad corresponde a días u horas.');
			}

			if (!typeWithoutQuantity && selectedUnit === 'dias') {
				const tipo = normalizeNoveltyType(noveltyType.value || '');
				const maxDays = getMaxDaysByType(tipo);
				const minDays = 0.01;
				let currentDays = Number(quantityDaysInput.value || 0);

				if (tipo === 'LMAT') {
					currentDays = 126;
					quantityDaysInput.value = '126';
				}
				if (tipo === 'LPAT') {
					currentDays = 14;
					quantityDaysInput.value = '14';
				}

				if (!quantityDaysInput.value) {
					isValid = false;
					showFieldError('quantityDays', 'Debe ingresar la cantidad en días.');
					markInvalid(quantityDaysInput);
				}

				// Validación de saldo de vacaciones
				if (tipo === 'VAC') {
					const balance = Number(document.getElementById('vacaciones-balance')?.value || 0);
					const registradas = Number(document.getElementById('vacaciones-registradas')?.value || 0);
					if ((currentDays + registradas) > balance) {
						isValid = false;
						const disponibleReal = Math.max(0, balance - registradas);
						showFieldError('quantityDays', `El empleado tiene ${balance.toFixed(2)} días en total, pero ya ha registrado ${registradas.toFixed(2)} días en este periodo. Saldo restante: ${disponibleReal.toFixed(2)} días.`);
						markInvalid(quantityDaysInput);
					}
				}

				if (tipo === 'LMAT' && currentDays !== 126) {
					isValid = false;
					showFieldError('quantityDays', 'Para licencia de maternidad la cantidad debe ser exactamente 126 días.');
					markInvalid(quantityDaysInput);
				} else if (tipo === 'LPAT' && currentDays !== 14) {
					isValid = false;
					showFieldError('quantityDays', 'Para licencia de paternidad la cantidad debe ser exactamente 14 días.');
					markInvalid(quantityDaysInput);
				} else if (currentDays < minDays) {
					isValid = false;
					showFieldError('quantityDays', tipo === 'LMAT'
						? 'Para licencia de maternidad la cantidad debe ser exactamente 126 días.'
						: (tipo === 'LPAT'
							? 'Para licencia de paternidad debe ingresar máximo 14 días.'
							: 'La cantidad de días no puede ser negativa.'));
					markInvalid(quantityDaysInput);
				} else if (currentDays > maxDays) {
					isValid = false;
					showFieldError('quantityDays', tipo === 'LMAT'
						? 'Para licencia de maternidad la cantidad debe ser exactamente 126 días.'
						: (tipo === 'LPAT'
							? 'Para licencia de paternidad la cantidad máxima es 14 días.'
							: `La cantidad de días no puede superar ${maxDays}.`));
					markInvalid(quantityDaysInput);
				}
			}

			if (!typeWithoutQuantity && selectedUnit === 'horas') {
				if (!quantityHoursInput.value) {
					isValid = false;
					showFieldError('quantityHours', 'Debe ingresar la cantidad en horas.');
					markInvalid(quantityHoursInput);
				} else if (Number(quantityHoursInput.value) < 0) {
					isValid = false;
					showFieldError('quantityHours', 'La cantidad de horas no puede ser negativa.');
					markInvalid(quantityHoursInput);
				} else if (Number(quantityHoursInput.value) > 240) {
					isValid = false;
					showFieldError('quantityHours', 'La cantidad de horas no puede superar 240.');
					markInvalid(quantityHoursInput);
				}
			}

			if (!startDateInput.value) {
				isValid = false;
				showFieldError('startDate', 'La fecha de inicio es obligatoria.');
				markInvalid(startDateInput);
			} else if (['TDE', 'TAE', 'TDP', 'TAP'].includes(selectedType)) {
				// Validar que la fecha esté dentro del periodo activo
				if (activePeriodStart && activePeriodEnd) {
					const date = startDateInput.value;
					if (date < activePeriodStart || date > activePeriodEnd) {
						isValid = false;
						const msg = `La fecha de traslado debe estar dentro del periodo de liquidación actual (${activePeriodStart} a ${activePeriodEnd}).`;
						showFieldError('startDate', msg);
						markInvalid(startDateInput);
						
						if (window.Swal) {
							Swal.fire({
								icon: 'error',
								title: 'Fecha inválida',
								text: msg,
								confirmButtonColor: '#1565C0'
							});
						}
					}
				}
				
				// Forzar sincronización de fecha fin para evitar bloqueos por validación de fecha_fin obligatoria
				if (endDateInput && startDateInput) {
					endDateInput.value = startDateInput.value;
				}
			}

			// Para traslados y similares, fecha_fin se maneja automáticamente — no validar
			if (!typeWithoutQuantity) {
				if (!endDateInput.value) {
					isValid = false;
					showFieldError('endDate', 'La fecha de fin es obligatoria.');
					markInvalid(endDateInput);
				} else if (!validateDateRange()) {
					isValid = false;
				}
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

			const selectedEditType = normalizeNoveltyType(editNoveltyTypeInput.value || '');
			if (selectedEditType === 'LIC' && !editTipoLicenciaInput?.value) {
				isValid = false;
				showEditFieldError('noveltyType', 'Debe seleccionar el tipo de licencia.');
			}
			if (requiresMedicalSupport(selectedEditType)) {
				clearEditFieldError('medicalSupport');
				const hasExistingMedicalSupport = String(editExistingMedicalSupportInput?.value || '0') === '1';
				const medicalSupportValid = validateMedicalSupportFileInput(editMedicalSupportFileInput, hasExistingMedicalSupport, showEditFieldError, 'medicalSupport');
				if (!medicalSupportValid) {
					isValid = false;
					markInvalid(editMedicalSupportFileInput);
				}
				if (editCertificadoMedicoInput && medicalSupportValid) {
					editCertificadoMedicoInput.checked = true;
				}
			}
			if (['TDE', 'TAE'].includes(selectedEditType)) {
				if (!editEpsIdInput?.value) {
					isValid = false;
					showEditFieldError('noveltyType', 'Debe seleccionar la EPS para el traslado.');
					if (window.Swal) {
						Swal.fire({
							icon: 'warning',
							title: 'EPS requerida',
							text: 'Debe seleccionar la EPS para el traslado.',
							confirmButtonColor: '#1565C0'
						});
					}
				} else {
					// Validación de duplicidad en edición
					const idEpsAnterior = document.getElementById('edit-novelty-type').dataset.id_eps;
					if (idEpsAnterior && String(idEpsAnterior) === String(editEpsIdInput.value)) {
						isValid = false;
						const msg = 'La EPS destino no puede ser igual a la EPS actual en la base del empleado.';
						showEditFieldError('noveltyType', msg);
						if (window.Swal) Swal.fire({ icon: 'warning', title: 'Traslado inválido', text: msg, confirmButtonColor: '#1565C0' });
					}
				}
			}
			if (['TDP', 'TAP'].includes(selectedEditType)) {
				if (!editAfpIdInput?.value) {
					isValid = false;
					showEditFieldError('noveltyType', 'Debe seleccionar la AFP para el traslado.');
					if (window.Swal) {
						Swal.fire({
							icon: 'warning',
							title: 'AFP requerida',
							text: 'Debe seleccionar la AFP para el traslado.',
							confirmButtonColor: '#1565C0'
						});
					}
				} else {
					// Validación de duplicidad en edición
					const idAfpAnterior = document.getElementById('edit-novelty-type').dataset.id_afp;
					if (idAfpAnterior && String(idAfpAnterior) === String(editAfpIdInput.value)) {
						isValid = false;
						const msg = 'La AFP destino no puede ser igual a la AFP actual en la base del empleado.';
						showEditFieldError('noveltyType', msg);
						if (window.Swal) Swal.fire({ icon: 'warning', title: 'Traslado inválido', text: msg, confirmButtonColor: '#1565C0' });
					}
				}
			}
			if (selectedEditType === 'VCT' && !editArlIdInput?.value) {
				isValid = false;
				showEditFieldError('noveltyType', 'Debe seleccionar la ARL para la variación de centro de trabajo.');
				if (window.Swal) {
					Swal.fire({
						icon: 'warning',
						title: 'ARL requerida',
						text: 'Debe seleccionar la ARL para la variación de centro de trabajo.',
						confirmButtonColor: '#1565C0'
					});
				}
			}

			const selectedEditUnit = getSelectedEditUnit();
			const editTypeWithoutQuantity = ['TDE', 'TAE', 'TDP', 'TAP', 'VSP', 'VST', 'VCT'].includes(selectedEditType);
			if (!editTypeWithoutQuantity && !selectedEditUnit) {
				isValid = false;
				showEditFieldError('quantityUnit', 'Debe seleccionar si la cantidad corresponde a días u horas.');
			}

			if (!editTypeWithoutQuantity && selectedEditUnit === 'dias') {
				const tipo = normalizeNoveltyType(editNoveltyTypeInput.value || '');
				const maxDays = getMaxDaysByType(tipo);
				const minDays = 0.01;
				let currentDays = Number(editQuantityDaysInput.value || 0);

				if (tipo === 'LMAT') {
					currentDays = 126;
					editQuantityDaysInput.value = '126';
				}
				if (tipo === 'LPAT') {
					currentDays = 14;
					editQuantityDaysInput.value = '14';
				}

				if (!editQuantityDaysInput.value) {
					isValid = false;
					showEditFieldError('quantityDays', 'Debe ingresar la cantidad en días.');
					markInvalid(editQuantityDaysInput);
				}

				// Validación de saldo de vacaciones (Edición)
				if (tipo === 'VAC') {
					const balance = Number(document.getElementById('edit-vacaciones-balance')?.value || 0);
					const registradas = Number(document.getElementById('edit-vacaciones-registradas')?.value || 0);
					const originalDays = Number(document.getElementById('edit-original-days')?.value || 0);
					
					// El total acumulado es: Registradas - Lo que ya tenía esta novedad + El nuevo valor
					if ((currentDays + (registradas - originalDays)) > balance) {
						isValid = false;
						const disponibleOtros = balance - (registradas - originalDays);
						showEditFieldError('quantityDays', `El empleado tiene ${balance.toFixed(2)} días en total. Considerando otras novedades registradas (${(registradas - originalDays).toFixed(2)} días), el máximo permitido para esta novedad es ${disponibleOtros.toFixed(2)} días.`);
						markInvalid(editQuantityDaysInput);
					}
				}

				if (tipo === 'LMAT' && currentDays !== 126) {
					isValid = false;
					showEditFieldError('quantityDays', 'Para licencia de maternidad la cantidad debe ser exactamente 126 días.');
					markInvalid(editQuantityDaysInput);
				} else if (tipo === 'LPAT' && currentDays !== 14) {
					isValid = false;
					showEditFieldError('quantityDays', 'Para licencia de paternidad la cantidad debe ser exactamente 14 días.');
					markInvalid(editQuantityDaysInput);
				} else if (currentDays < minDays) {
					isValid = false;
					showEditFieldError('quantityDays', tipo === 'LMAT'
						? 'Para licencia de maternidad la cantidad debe ser exactamente 126 días.'
						: (tipo === 'LPAT'
							? 'Para licencia de paternidad debe ingresar máximo 14 días.'
						: 'La cantidad de días no puede ser negativa.'));
					markInvalid(editQuantityDaysInput);
				} else if (currentDays > maxDays) {
					isValid = false;
					showEditFieldError('quantityDays', tipo === 'LMAT'
						? 'Para licencia de maternidad la cantidad debe ser exactamente 126 días.'
						: (tipo === 'LPAT'
							? 'Para licencia de paternidad la cantidad máxima es 14 días.'
							: `La cantidad de días no puede superar ${maxDays}.`));
					markInvalid(editQuantityDaysInput);
				}
			}

			if (!editTypeWithoutQuantity && selectedEditUnit === 'horas') {
				if (!editQuantityHoursInput.value) {
					isValid = false;
					showEditFieldError('quantityHours', 'Debe ingresar la cantidad en horas.');
					markInvalid(editQuantityHoursInput);
				} else if (Number(editQuantityHoursInput.value) < 0) {
					isValid = false;
					showEditFieldError('quantityHours', 'La cantidad de horas no puede ser negativa.');
					markInvalid(editQuantityHoursInput);
				} else if (Number(editQuantityHoursInput.value) > 240) {
					isValid = false;
					showEditFieldError('quantityHours', 'La cantidad de horas no puede superar 240.');
					markInvalid(editQuantityHoursInput);
				}
			}

			if (!editStartDateInput.value) {
				isValid = false;
				showEditFieldError('startDate', 'La fecha de inicio es obligatoria.');
				markInvalid(editStartDateInput);
			} else if (['TDE', 'TAE', 'TDP', 'TAP'].includes(selectedEditType)) {
				// Validar que la fecha esté dentro del periodo activo (Edición)
				if (activePeriodStart && activePeriodEnd) {
					const date = editStartDateInput.value;
					if (date < activePeriodStart || date > activePeriodEnd) {
						isValid = false;
						const msg = `La fecha de traslado debe estar dentro del periodo de liquidación actual (${activePeriodStart} a ${activePeriodEnd}).`;
						showEditFieldError('startDate', msg);
						markInvalid(editStartDateInput);
						
						if (window.Swal) {
							Swal.fire({
								icon: 'error',
								title: 'Fecha inválida',
								text: msg,
								confirmButtonColor: '#1565C0'
							});
						}
					}
				}
				
				// Forzar sincronización de fecha fin para edición
				if (editEndDateInput && editStartDateInput) {
					editEndDateInput.value = editStartDateInput.value;
				}
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

		if (shouldOpenModal) {
			window.__openNoveltyModal && window.__openNoveltyModal();
		}

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
					salarioBase: oldEditData.salario_base || sourceButton.dataset.salarioBase,
					tipoLicencia: oldEditData.tipo_licencia || sourceButton.dataset.tipoLicencia,
					tipoIncapacidad: oldEditData.tipo_incapacidad || sourceButton.dataset.tipoIncapacidad,
					certificadoMedico: oldEditData.certificado_medico || sourceButton.dataset.certificadoMedico,
					hasSupportFile: sourceButton.dataset.hasSupportFile,
					soporteMedicoNombre: sourceButton.dataset.soporteMedicoNombre,
					idEps: oldEditData.id_eps || sourceButton.dataset.idEps,
					idAfp: oldEditData.id_afp || sourceButton.dataset.idAfp,
					idArl: oldEditData.id_arl || sourceButton.dataset.idArl,
					observaciones: oldEditData.observaciones || sourceButton.dataset.observaciones,
				});
				openEditModal();
			}
		}
	});

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('success') }}',
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '<span style="color: white; font-weight: bold;">Atención</span>',
            html: '<span style="color: #E2E8F0;">{{ session("error") }}</span>',
            timer: 6000,
            timerProgressBar: true,
            position: 'top-end',
            toast: true,
            showConfirmButton: false,
            background: '#1565C0 url("data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
            iconColor: 'white',
            didOpen: (toast) => {
                const b = toast.querySelector('.swal2-timer-progress-bar');
                if (b) {
                    b.style.backgroundColor = '#10B981'; // Verde Marca
                }
            }
        });
    @endif
</script>
@endpush
