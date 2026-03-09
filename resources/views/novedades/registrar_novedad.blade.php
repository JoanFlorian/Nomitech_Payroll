<div id="novelty-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
	<div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative">
		<div class="pointer-events-none absolute -top-10 -left-10 h-24 w-24 rounded-full bg-blue-100/50"></div>
		<div class="pointer-events-none absolute bottom-6 right-10 h-16 w-16 rotate-45 rounded-lg bg-emerald-100/50"></div>

		<div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between relative z-10">
			<h3 class="text-xl md:text-2xl font-bold text-gray-800">Registrar Novedad</h3>
			<button type="button" id="close-modal-btn" onclick="window.__closeNoveltyModal && window.__closeNoveltyModal()" class="w-9 h-9 inline-flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-600 transition">
				<span class="material-icons text-[20px]">close</span>
			</button>
		</div>

		<form id="novelty-form" action="{{ route('novedades.store') }}" method="POST" class="relative z-10">
			@csrf
			<div class="p-6 md:p-8 space-y-5 max-h-[70vh] overflow-y-auto">
				<div>
					<label for="employee-search" class="block text-sm font-semibold text-gray-700 mb-2">
						Buscar empleado <span class="text-red-500">*</span>
					</label>
					<div class="relative">
						<input
							id="employee-search"
							type="text"
							autocomplete="off"
							class="w-full border-2 border-gray-300 px-4 py-3 rounded-xl text-base focus:border-blue-500 focus:outline-none transition bg-white shadow-sm"
							placeholder="Escribe nombre o documento"
							value="{{ old('employee_search') }}"
						>
						<div id="employee-loading-spinner" class="hidden absolute right-4 top-3.5">
							<svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
						</div>
					</div>
					<input type="hidden" id="doc_empleado" name="empleado_id" value="{{ old('empleado_id', old('doc_empleado')) }}">
					<input type="hidden" id="salario-base" name="salario_base" value="{{ old('salario_base') }}">
					<ul id="employee-suggestions" class="hidden mt-2 w-full rounded-xl border border-gray-200 bg-white shadow-lg max-h-64 overflow-y-auto"></ul>
					<p class="text-xs text-gray-500 mt-2">Escribe nombre o documento y selecciona una opción.</p>
					<p id="employee-search-error" class="mt-1 text-xs text-red-600 hidden"></p>
					@error('empleado_id')
						<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
					@enderror
				</div>

				<div id="employee-details" class="hidden grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
					<div>
						<label for="employee-name" class="block text-sm font-medium text-gray-700 mb-1">Nombres</label>
						<input id="employee-name" type="text" readonly class="w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm cursor-not-allowed" value="{{ old('employee_name') }}">
					</div>
					<div>
						<label for="employee-lastname" class="block text-sm font-medium text-gray-700 mb-1">Apellidos</label>
						<input id="employee-lastname" type="text" readonly class="w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm cursor-not-allowed" value="{{ old('employee_lastname') }}">
					</div>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
					<div>
						<label for="novelty-type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de novedad</label>
						<select id="novelty-type" name="tipo_novedad" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
							<option value="">Seleccione un tipo</option>
							@foreach ([
								['value' => 'TDE', 'label' => 'TDE - Traslado desde EPS'],
								['value' => 'TAE', 'label' => 'TAE - Traslado a EPS'],
								['value' => 'TDP', 'label' => 'TDP - Traslado desde AFP'],
								['value' => 'TAP', 'label' => 'TAP - Traslado a AFP'],
								['value' => 'VSP', 'label' => 'VSP - Variación permanente de salario'],
								['value' => 'VST', 'label' => 'VST - Variación transitoria de salario'],
								['value' => 'SLN', 'label' => 'SLN - Suspensión o licencia no remunerada'],
								['value' => 'IGE', 'label' => 'IGE - Incapacidad enfermedad general'],
								['value' => 'IRL', 'label' => 'IRL - Incapacidad riesgo laboral'],
								['value' => 'LMAT', 'label' => 'LMAT - Licencia de maternidad'],
								['value' => 'LPAT', 'label' => 'LPAT - Licencia de paternidad'],
								['value' => 'VAC', 'label' => 'VAC - Vacaciones'],
								['value' => 'VCT', 'label' => 'VCT - Variación centro de trabajo'],
								['value' => 'INC', 'label' => 'INC - Incapacidad'],
								['value' => 'LIC', 'label' => 'LIC - Licencia'],
							] as $tipoNovedad)
								<option value="{{ $tipoNovedad['value'] }}" {{ old('tipo_novedad') === $tipoNovedad['value'] ? 'selected' : '' }}>{{ $tipoNovedad['label'] }}</option>
							@endforeach
						</select>
						<div id="licencia-remunerada-wrap" class="mt-2 hidden">
							<label class="inline-flex items-center gap-2 text-sm text-gray-700">
								<input type="checkbox" id="licencia-remunerada" name="es_remunerado" value="1" {{ old('es_remunerado', old('licencia_remunerada', '0')) ? 'checked' : '' }}>
								<span id="remunerada-label">Novedad remunerada</span>
							</label>
						</div>
						<p id="novelty-type-error" class="mt-1 text-xs text-red-600 hidden"></p>
						@error('tipo_novedad')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700 mb-1">Unidad de cantidad</label>
						<div class="flex items-center gap-4 h-[42px] px-3 border border-gray-300 rounded-md shadow-sm">
							<label class="inline-flex items-center gap-2 text-sm text-gray-700">
								<input type="radio" name="unidad_cantidad" value="dias" {{ old('unidad_cantidad', 'dias') === 'dias' ? 'checked' : '' }}>
								Días
							</label>
							<label class="inline-flex items-center gap-2 text-sm text-gray-700">
								<input type="radio" name="unidad_cantidad" value="horas" {{ old('unidad_cantidad') === 'horas' ? 'checked' : '' }}>
								Horas
							</label>
						</div>
						<p id="quantity-unit-error" class="mt-1 text-xs text-red-600 hidden"></p>
						@error('unidad_cantidad')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					<div>
						<label for="quantity-days" class="block text-sm font-medium text-gray-700 mb-1">Cantidad en días</label>
						<input id="quantity-days" name="cantidad_dias" type="number" step="0.01" min="0.01" max="126" value="{{ old('cantidad_dias') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 10">
						<p id="quantity-days-error" class="mt-1 text-xs text-red-600 hidden"></p>
						@error('cantidad_dias')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					<div>
						<label for="quantity-hours" class="block text-sm font-medium text-gray-700 mb-1">Cantidad en horas</label>
						<input id="quantity-hours" name="cantidad_horas" type="number" step="0.01" min="0.01" max="240" value="{{ old('cantidad_horas') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 8">
						<p id="quantity-hours-error" class="mt-1 text-xs text-red-600 hidden"></p>
						@error('cantidad_horas')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					{{-- Select de tipo de incapacidad eliminado: ahora se manejan como novedades separadas (IGE / IRL) --}}

					<div id="tipo-licencia-wrap" class="hidden">
						<label for="tipo-licencia" class="block text-sm font-medium text-gray-700 mb-1">Tipo de licencia</label>
						<select id="tipo-licencia" name="tipo_licencia" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
							<option value="">Seleccione</option>
							<option value="luto" {{ old('tipo_licencia') === 'luto' ? 'selected' : '' }}>Luto</option>
							<option value="calamidad_domestica" {{ old('tipo_licencia') === 'calamidad_domestica' ? 'selected' : '' }}>Calamidad doméstica</option>
							<option value="permiso_especial" {{ old('tipo_licencia') === 'permiso_especial' ? 'selected' : '' }}>Permiso especial</option>
							<option value="remunerada" {{ old('tipo_licencia') === 'remunerada' ? 'selected' : '' }}>Remunerada</option>
							<option value="no_remunerada" {{ old('tipo_licencia') === 'no_remunerada' ? 'selected' : '' }}>No remunerada</option>
						</select>
					</div>

					<div id="certificado-medico-wrap" class="hidden md:col-span-2">
						<label class="inline-flex items-center gap-2 text-sm text-gray-700">
							<input type="checkbox" id="certificado-medico" name="certificado_medico" value="1" {{ old('certificado_medico') ? 'checked' : '' }}>
							<span>Certificado médico adjunto/verificado</span>
						</label>
					</div>

					<div id="eps-wrap" class="hidden">
						<label for="eps-id" class="block text-sm font-medium text-gray-700 mb-1">EPS</label>
						<select id="eps-id" name="id_eps" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
							<option value="">Seleccione EPS</option>
							@foreach (($epsList ?? collect()) as $eps)
								<option value="{{ $eps->id_eps }}" {{ (string) old('id_eps') === (string) $eps->id_eps ? 'selected' : '' }}>{{ $eps->nombre }}</option>
							@endforeach
						</select>
					</div>

					<div id="afp-wrap" class="hidden">
						<label for="afp-id" class="block text-sm font-medium text-gray-700 mb-1">AFP</label>
						<select id="afp-id" name="id_afp" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
							<option value="">Seleccione AFP</option>
							@foreach (($afpList ?? collect()) as $afp)
								<option value="{{ $afp->id_afp }}" {{ (string) old('id_afp') === (string) $afp->id_afp ? 'selected' : '' }}>{{ $afp->nombre }}</option>
							@endforeach
						</select>
					</div>

					<div id="arl-wrap" class="hidden">
						<label for="arl-id" class="block text-sm font-medium text-gray-700 mb-1">ARL / Nivel de riesgo</label>
						<select id="arl-id" name="id_arl" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
							<option value="">Seleccione ARL</option>
							@foreach (($arlList ?? collect()) as $arl)
								<option value="{{ $arl->id_arl }}" {{ (string) old('id_arl') === (string) $arl->id_arl ? 'selected' : '' }}>{{ $arl->nombre }}</option>
							@endforeach
						</select>
					</div>

					<div>
						<label for="start-date" class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
						<input id="start-date" name="fecha_inicio" type="date" value="{{ old('fecha_inicio') }}" required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition">
						<p id="start-date-error" class="mt-1 text-xs text-red-600 hidden"></p>
						@error('fecha_inicio')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					<div>
						<label for="end-date" class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
						<input id="end-date" name="fecha_fin" type="date" value="{{ old('fecha_fin') }}" required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition">
						<p id="end-date-error" class="mt-1 text-xs text-red-600 hidden"></p>
						@error('fecha_fin')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					<div class="md:col-span-2">
						<label for="payment-display" class="block text-sm font-medium text-gray-700 mb-1">Pago manual (opcional)</label>
						<div class="relative">
							<span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
							<input
								id="payment-display"
								type="number"
								step="0.01"
								min="0"
								inputmode="decimal"
								class="w-full pl-7 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition"
								placeholder="Valor a pagar o descontar por esta novedad"
								autocomplete="off"
							>
						</div>
						<input type="hidden" id="payment" name="pago_manual" value="{{ old('pago_manual', old('pago')) }}">
						<p class="mt-2 text-xs text-gray-500 italic">Ingrese el valor correspondiente que será aplicado en la nómina.</p>
						<p id="payment-auto-message" class="mt-1 text-xs text-blue-700 hidden">Esta novedad se calcula automáticamente según el salario del empleado y la cantidad de días u horas registradas.</p>
						<p id="payment-error" class="mt-1 text-xs text-red-600 hidden"></p>
						@error('pago_manual')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					<div class="md:col-span-2">
						<label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
						<textarea id="observaciones" name="observaciones" rows="3" maxlength="500" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Detalle adicional de la novedad (opcional)">{{ old('observaciones') }}</textarea>
					</div>

					<div class="md:col-span-2 rounded-lg border border-blue-100 bg-blue-50/60 px-4 py-3">
						<p class="text-xs uppercase tracking-wide text-blue-700 font-semibold">Valor estimado</p>
						<div class="mt-2">
							<span id="novelty-nature-badge" class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200">Naturaleza: -</span>
						</div>
						<p id="estimated-value" class="mt-1 text-xl font-bold text-blue-900">$ 0</p>
						<p id="estimated-note" class="mt-1 text-xs text-blue-700">Selecciona empleado, tipo y cantidad para calcular automáticamente.</p>
					</div>
				</div>
			</div>

			<div class="bg-gray-50 px-6 py-4 flex justify-end items-center border-t border-gray-200">
				<button id="cancel-btn" type="button" onclick="window.__closeNoveltyModal && window.__closeNoveltyModal()" class="bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-colors duration-300 mr-3">Cancelar</button>
				<button id="save-btn" type="submit" class="bg-emerald-500 text-white font-medium py-2 px-6 rounded-md shadow-sm hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-300">Guardar</button>
			</div>
		</form>
	</div>
</div>

<script>
	window.__openNoveltyModal = function () {
		const modal = document.getElementById('novelty-modal');
		if (!modal) return;
		modal.classList.remove('hidden');
		modal.classList.add('flex');
	};

	window.__closeNoveltyModal = function () {
		const modal = document.getElementById('novelty-modal');
		if (!modal) return;
		modal.classList.remove('flex');
		modal.classList.add('hidden');
	};

	document.addEventListener('DOMContentLoaded', function () {
		// Elementos base
		const employeeSearch = document.getElementById('employee-search');
		const employeeSuggestions = document.getElementById('employee-suggestions');
		const employeeLoading = document.getElementById('employee-loading-spinner');
		const employeeDetails = document.getElementById('employee-details');
		const employeeName = document.getElementById('employee-name');
		const employeeLastname = document.getElementById('employee-lastname');
		const employeeHidden = document.getElementById('doc_empleado');
		const salarioBaseInput = document.getElementById('salario-base');

		const noveltyType = document.getElementById('novelty-type');
		const unitRadios = document.querySelectorAll('input[name="unidad_cantidad"]');
		const quantityDays = document.getElementById('quantity-days');
		const quantityHours = document.getElementById('quantity-hours');
		const startDate = document.getElementById('start-date');
		const endDate = document.getElementById('end-date');
		const paymentDisplay = document.getElementById('payment-display');
		const paymentHidden = document.getElementById('payment');
		const paymentAutoMsg = document.getElementById('payment-auto-message');
		const paymentError = document.getElementById('payment-error');
		const estimatedValue = document.getElementById('estimated-value');
		const estimatedNote = document.getElementById('estimated-note');
		const natureBadge = document.getElementById('novelty-nature-badge');
		const tipoIncapacidadWrap = document.getElementById('tipo-incapacidad-wrap');
		const tipoIncapacidad = document.getElementById('tipo-incapacidad');
		const tipoLicenciaWrap = document.getElementById('tipo-licencia-wrap');
		const tipoLicencia = document.getElementById('tipo-licencia');
		const licenciaRemuneradaWrap = document.getElementById('licencia-remunerada-wrap');
		const licenciaRemunerada = document.getElementById('licencia-remunerada');
		const remuneradaLabel = document.getElementById('remunerada-label');
		const certificadoWrap = document.getElementById('certificado-medico-wrap');
		const certificadoInput = document.getElementById('certificado-medico');
		const epsWrap = document.getElementById('eps-wrap');
		const epsId = document.getElementById('eps-id');
		const afpWrap = document.getElementById('afp-wrap');
		const afpId = document.getElementById('afp-id');
		const arlWrap = document.getElementById('arl-wrap');
		const arlId = document.getElementById('arl-id');

		if (!employeeSearch || !noveltyType) {
			return;
		}

		const empleadosApiUrl = @json(url('/api/empleados'));

		const formatter = new Intl.NumberFormat('es-CO', {
			style: 'currency',
			currency: 'COP',
			maximumFractionDigits: 0,
		});

		const formatCurrency = (value) => formatter.format(Math.round(Number(value) || 0));

		const normalizeType = (value) => String(value || '').trim().toUpperCase();

		const NOVELTY_CONFIG = {
			LMAT: { diasFijos: 126, editableDias: false, usaHoras: false, valorManual: false, tipo: 'DEVENGADO' },
			LPAT: { diasFijos: 14, editableDias: false, usaHoras: false, valorManual: false, tipo: 'DEVENGADO' },
			VAC: { diasFijos: null, editableDias: true, usaHoras: false, valorManual: false, tipo: 'DEVENGADO' },
			SLN: { diasFijos: null, editableDias: true, usaHoras: false, valorManual: false, tipo: 'DEDUCCION' },
			IGE: { diasFijos: null, editableDias: true, usaHoras: false, valorManual: false, tipo: 'DEVENGADO' },
			IRL: { diasFijos: null, editableDias: true, usaHoras: false, valorManual: false, tipo: 'DEVENGADO' },
			INC: { diasFijos: null, editableDias: true, usaHoras: false, valorManual: false, tipo: 'DEVENGADO' },
			LIC: { diasFijos: null, editableDias: true, usaHoras: false, valorManual: false, tipo: 'DEDUCCION' },
			VSP: { diasFijos: null, editableDias: false, usaHoras: false, valorManual: true, tipo: 'SIN_MOVIMIENTO' },
			VST: { diasFijos: null, editableDias: false, usaHoras: false, valorManual: true, tipo: 'DEVENGADO' },
			TDE: { diasFijos: null, editableDias: false, usaHoras: false, valorManual: false, tipo: 'SIN_MOVIMIENTO' },
			TAE: { diasFijos: null, editableDias: false, usaHoras: false, valorManual: false, tipo: 'SIN_MOVIMIENTO' },
			TDP: { diasFijos: null, editableDias: false, usaHoras: false, valorManual: false, tipo: 'SIN_MOVIMIENTO' },
			TAP: { diasFijos: null, editableDias: false, usaHoras: false, valorManual: false, tipo: 'SIN_MOVIMIENTO' },
			VCT: { diasFijos: null, editableDias: false, usaHoras: false, valorManual: false, tipo: 'SIN_MOVIMIENTO' },
		};

		const isManualType = (tipo) => ['VSP', 'VST'].includes(normalizeType(tipo));
		const isAutomaticType = (tipo) => !isManualType(tipo);

		const updateNatureBadge = (tipo) => {
			const cfg = NOVELTY_CONFIG[normalizeType(tipo)];
			if (!natureBadge) return;
			if (!cfg) {
				natureBadge.textContent = 'Naturaleza: -';
				natureBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200';
				return;
			}
			if (cfg.tipo === 'SIN_MOVIMIENTO') {
				natureBadge.textContent = 'Naturaleza: SIN MOVIMIENTO';
				natureBadge.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200';
				return;
			}
			const isDev = cfg.tipo === 'DEVENGADO';
			natureBadge.textContent = `Naturaleza: ${isDev ? 'DEVENGADO' : 'DEDUCCION'}`;
			natureBadge.className = isDev
				? 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200'
				: 'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200';
		};

		const getSelectedUnit = () => {
			const checked = document.querySelector('input[name="unidad_cantidad"]:checked');
			return checked ? checked.value : 'dias';
		};

		const applyNoveltyConfig = () => {
			const tipo = normalizeType(noveltyType.value);
			const cfg = NOVELTY_CONFIG[tipo] || null;
			const unit = getSelectedUnit();

			updateNatureBadge(tipo);

			// Licencias / incapacidades / EPS/AFP/ARL
			if (tipoLicenciaWrap) tipoLicenciaWrap.classList.toggle('hidden', tipo !== 'LIC');
			if (tipoIncapacidadWrap) tipoIncapacidadWrap.classList.toggle('hidden', !['INC'].includes(tipo));
			if (certificadoWrap) certificadoWrap.classList.toggle('hidden', !['IGE', 'IRL', 'INC'].includes(tipo));
			if (epsWrap) epsWrap.classList.toggle('hidden', !['TDE', 'TAE'].includes(tipo));
			if (afpWrap) afpWrap.classList.toggle('hidden', !['TDP', 'TAP'].includes(tipo));
			if (arlWrap) arlWrap.classList.toggle('hidden', tipo !== 'VCT');

			// Licencia remunerada solo para LIC
			if (licenciaRemuneradaWrap) licenciaRemuneradaWrap.classList.toggle('hidden', tipo !== 'LIC');
			if (remuneradaLabel) remuneradaLabel.textContent = 'Marcar como remunerada (no descontar)';
			if (tipo !== 'LIC' && licenciaRemunerada) licenciaRemunerada.checked = false;

			// Configuración días/horas
			const noCantidad = ['TDE', 'TAE', 'TDP', 'TAP', 'VSP', 'VST', 'VCT'].includes(tipo);
			const fixedDays = cfg ? cfg.diasFijos : null;
			const allowsHours = ['IGE', 'IRL', 'INC'].includes(tipo);
			const forceDaysOnly = (cfg && !cfg.usaHoras) || noCantidad;

			unitRadios.forEach((r) => {
				if (!r) return;
				if (r.value === 'dias') {
					r.disabled = noCantidad;
					if (forceDaysOnly) r.checked = true;
				}
				if (r.value === 'horas') {
					r.disabled = !allowsHours || noCantidad;
				}
			});

			const currentUnit = getSelectedUnit();
			const isDias = currentUnit === 'dias' && !noCantidad;
			const isHoras = currentUnit === 'horas' && !noCantidad && allowsHours;

			if (quantityDays) {
				quantityDays.disabled = !isDias;
				quantityDays.readOnly = fixedDays !== null;
				if (fixedDays !== null && isDias) quantityDays.value = String(fixedDays);
				if (!isDias) quantityDays.value = '';
			}
			if (quantityHours) {
				quantityHours.disabled = !isHoras;
				if (!isHoras) quantityHours.value = '';
			}

			// Pago manual
			const esManual = isManualType(tipo);
			const esAutomatica = !esManual;
			if (paymentDisplay) {
				paymentDisplay.readOnly = esAutomatica;
				paymentDisplay.disabled = esAutomatica;
				paymentDisplay.classList.toggle('bg-gray-100', esAutomatica);
				paymentDisplay.classList.toggle('cursor-not-allowed', esAutomatica);
				if (esAutomatica) paymentDisplay.value = '';
			}
			if (paymentHidden) {
				paymentHidden.disabled = esAutomatica;
				if (esAutomatica) paymentHidden.value = '';
			}
			if (paymentAutoMsg) {
				paymentAutoMsg.classList.remove('hidden');
				paymentAutoMsg.textContent = esManual
					? 'Esta novedad permite valor manual (VSP/VST).'
					: 'Esta novedad se calcula automáticamente según el salario base y la cantidad.';
			}
		};

		const getNumeric = (input) => {
			if (!input) return 0;
			const v = Number(input.value || 0);
			return Number.isFinite(v) ? v : 0;
		};

		const recalcEstimated = () => {
			const tipo = normalizeType(noveltyType.value);
			const cfg = NOVELTY_CONFIG[tipo] || null;
			const salarioBase = Number(salarioBaseInput?.value || 0);
			if (!estimatedValue || !estimatedNote) return;

			if (!tipo || !cfg || !salarioBase) {
				estimatedValue.textContent = formatCurrency(0);
				estimatedNote.textContent = 'Selecciona empleado, tipo y cantidad para calcular automáticamente.';
				return;
			}

			const unit = getSelectedUnit();
			let dias = getNumeric(quantityDays);
			let horas = getNumeric(quantityHours);

			if (cfg.diasFijos !== null) dias = cfg.diasFijos;

			const valorDia = salarioBase / 30;
			const valorHora = salarioBase / 240;
			let valor = 0;

			if (isManualType(tipo)) {
				const v = Number(paymentDisplay?.value || 0);
				valor = Number.isFinite(v) ? v : 0;
				estimatedNote.textContent = 'Novedad manual: se usará el valor ingresado en pago manual (respaldado por backend).';
				estimatedValue.textContent = formatCurrency(valor);
				updateNatureBadge(tipo);
				return;
			}

			// Automáticas según reglas del prompt
			switch (tipo) {
				case 'LMAT':
					valor = valorDia * 126;
					break;
				case 'LPAT':
					valor = valorDia * 14;
					break;
				case 'VAC':
					valor = valorDia * dias;
					break;
				case 'SLN':
					valor = valorDia * dias; // descuento
					break;
				case 'IGE':
					valor = valorDia * dias * 0.6667;
					break;
				case 'IRL':
					valor = valorDia * dias;
					break;
				case 'INC':
					valor = valorDia * dias;
					break;
				default:
					if (unit === 'dias') valor = valorDia * dias;
					if (unit === 'horas') valor = valorHora * horas;
			}

			estimatedValue.textContent = formatCurrency(valor);
			if (cfg.tipo === 'DEDUCCION') {
				estimatedNote.textContent = 'Deducción automática basada en salario base y días registrados.';
			} else if (cfg.tipo === 'DEVENGADO') {
				estimatedNote.textContent = 'Devengo automático basado en salario base y días/horas registradas.';
			} else {
				estimatedNote.textContent = 'Esta novedad no afecta el valor de devengo/deducción (sin movimiento).';
			}

			updateNatureBadge(tipo);
		};

		// Autocomplete empleados (solo API, filtrado por empresa en sesión)
		let employeeTimer = null;
		let lastResults = [];

		const renderEmployeeSuggestions = async (query) => {
			const term = String(query || '').trim();
			if (!term) {
				if (employeeSuggestions) {
					employeeSuggestions.innerHTML = '';
					employeeSuggestions.classList.add('hidden');
				}
				return;
			}

			if (employeeLoading) employeeLoading.classList.remove('hidden');
			try {
				const url = new URL(empleadosApiUrl, window.location.origin);
				url.searchParams.set('search', term);
				url.searchParams.set('limit', '12');
				const resp = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
				if (!resp.ok) throw new Error('No se pudieron cargar empleados.');
				const json = await resp.json().catch(() => ({ data: [] }));
				const rows = Array.isArray(json.data) ? json.data : [];
				lastResults = rows;

				if (!employeeSuggestions) return;
				if (rows.length === 0) {
					employeeSuggestions.innerHTML = '<li class="px-4 py-3 text-sm text-gray-500">No hay empleados activos para la empresa de la sesión.</li>';
					employeeSuggestions.classList.remove('hidden');
					return;
				}

				employeeSuggestions.innerHTML = rows.map((row) => {
					const nombre = String(row.nombre || '');
					const doc = String(row.documento || row.id || '');
					return `<li>
						<button type="button" class="employee-option w-full text-left px-4 py-2.5 hover:bg-blue-50 border-b border-gray-100 last:border-b-0" data-doc="${doc}">
							<div class="text-sm font-medium text-gray-800">${nombre}</div>
							<div class="text-xs text-gray-500">Doc: ${doc}</div>
						</button>
					</li>`;
				}).join('');
				employeeSuggestions.classList.remove('hidden');

				employeeSuggestions.querySelectorAll('.employee-option').forEach((btn) => {
					btn.addEventListener('click', () => {
						const doc = btn.getAttribute('data-doc') || '';
						const emp = lastResults.find((e) => String(e.documento || e.id || '') === doc);
						if (!emp) return;
						const nombre = String(emp.nombre || '').trim();
						const salario = Number(emp.salario_base || 0);
						if (employeeHidden) employeeHidden.value = String(emp.documento || emp.id || '');
						if (salarioBaseInput) salarioBaseInput.value = salario > 0 ? String(salario) : '';
						if (employeeSearch) employeeSearch.value = `${nombre} - ${doc}`.trim();
						if (employeeName) employeeName.value = nombre;
						if (employeeLastname) employeeLastname.value = '';
						if (employeeDetails) employeeDetails.classList.remove('hidden');
						if (employeeSuggestions) {
							employeeSuggestions.innerHTML = '';
							employeeSuggestions.classList.add('hidden');
						}
						recalcEstimated();
					});
				});
			} finally {
				if (employeeLoading) employeeLoading.classList.add('hidden');
			}
		};

		employeeSearch.addEventListener('input', () => {
			clearTimeout(employeeTimer);
			if (employeeHidden) employeeHidden.value = '';
			if (salarioBaseInput) salarioBaseInput.value = '';
			if (employeeDetails) employeeDetails.classList.add('hidden');
			employeeTimer = setTimeout(() => {
				renderEmployeeSuggestions(employeeSearch.value);
			}, 220);
		});

		employeeSearch.addEventListener('focus', () => {
			if (employeeSearch.value) renderEmployeeSuggestions(employeeSearch.value);
		});

		document.addEventListener('click', (e) => {
			if (!employeeSuggestions) return;
			if (!employeeSuggestions.contains(e.target) && e.target !== employeeSearch) {
				employeeSuggestions.classList.add('hidden');
			}
		});

		// Validación simple de pago manual (solo números, sin negativos)
		if (paymentDisplay) {
			paymentDisplay.addEventListener('input', () => {
				if (paymentError) paymentError.classList.add('hidden');
				const v = paymentDisplay.value;
				if (v === '') {
					if (paymentHidden) paymentHidden.value = '';
					return;
				}
				const num = Number(v);
				if (!Number.isFinite(num) || num < 0) {
					if (paymentError) {
						paymentError.textContent = 'El pago manual debe ser un número positivo.';
						paymentError.classList.remove('hidden');
					}
					return;
				}
				if (paymentHidden) paymentHidden.value = String(num);
				recalcEstimated();
			});
		}

		// Recalcular ante cambios relevantes
		[noveltyType, quantityDays, quantityHours, startDate, endDate, tipoIncapacidad, tipoLicencia, certificadoInput, epsId, afpId, arlId, licenciaRemunerada].forEach((el) => {
			if (!el) return;
			el.addEventListener('change', () => {
				applyNoveltyConfig();
				recalcEstimated();
			});
		});

		unitRadios.forEach((r) => {
			if (!r) return;
			r.addEventListener('change', () => {
				applyNoveltyConfig();
				recalcEstimated();
			});
		});

		// Inicializar estado al cargar
		applyNoveltyConfig();
		recalcEstimated();
	});
</script>
