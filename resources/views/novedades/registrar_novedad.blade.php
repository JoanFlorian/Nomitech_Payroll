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

		<form id="novelty-form" action="{{ route('novedades.store') }}" method="POST" enctype="multipart/form-data" class="relative z-10">
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
					<input type="hidden" id="vacaciones-balance" name="vacaciones_balance" value="{{ old('vacaciones_balance') }}">
                    <input type="hidden" id="vacaciones-registradas" name="vacaciones_registradas" value="{{ old('vacaciones_registradas', 0) }}">
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
								<input type="radio" name="unidad_cantidad" value="dias" id="unit-days" {{ old('unidad_cantidad', 'dias') === 'dias' ? 'checked' : '' }}>
								Días
							</label>
							<label class="inline-flex items-center gap-2 text-sm text-gray-700">
								<input type="radio" name="unidad_cantidad" value="horas" id="unit-hours" {{ old('unidad_cantidad') === 'horas' ? 'checked' : '' }}>
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
						<input id="quantity-days" name="cantidad_dias" type="number" step="0.01" value="{{ old('cantidad_dias') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 10">
						<div id="vacaciones-balance-info" class="hidden mt-2 p-3 bg-indigo-50 border border-indigo-100 rounded-xl">
							<div class="flex items-center gap-2 text-indigo-700">
								<i class="material-icons text-[18px]">wb_sunny</i>
								<span class="text-[10px] font-bold uppercase tracking-wider">Saldo disponible:</span>
								<span id="vacaciones-balance-display" class="font-black">0</span>
								<span class="text-[10px]">días</span>
							</div>
						</div>
						<p id="quantity-days-error" class="mt-1 text-xs text-red-600 hidden"></p>
						@error('cantidad_dias')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					<div>
						<label for="quantity-hours" class="block text-sm font-medium text-gray-700 mb-1">Cantidad en horas</label>
						<input id="quantity-hours" name="cantidad_horas" type="number" step="0.01" value="{{ old('cantidad_hours') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 8">
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
						<div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 space-y-3">
							<div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
								<div>
									<label for="medical-support-file" class="block text-sm font-semibold text-gray-700">Certificado médico / soporte clínico <span class="text-red-500">*</span></label>
									<p class="mt-1 text-xs text-gray-500">Obligatorio para novedades de incapacidad (`INC`, `IGE`, `IRL`). Formatos permitidos: PDF, JPG y PNG. Tamaño máximo: 5 MB.</p>
								</div>
								<label class="inline-flex items-center gap-2 text-xs text-gray-600">
									<input type="checkbox" id="certificado-medico" name="certificado_medico" value="1" {{ old('certificado_medico') ? 'checked' : '' }}>
									<span>Soporte verificado</span>
								</label>
							</div>

							<input id="medical-support-file" name="soporte_medico_archivo" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100">
							<p id="medical-support-file-error" class="mt-1 text-xs text-red-600 hidden"></p>
							@error('soporte_medico_archivo')
								<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
							@enderror
							@error('certificado_medico')
								<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
							@enderror
						</div>
					</div>

					<div id="eps-wrap" class="hidden">
						<label for="eps-id" class="block text-sm font-medium text-gray-700 mb-1">EPS</label>
						<select id="eps-id" name="id_eps" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
							<option value="">Seleccione EPS</option>
							@foreach (($epsList ?? collect()) as $eps)
								<option value="{{ $eps->id_eps }}" {{ (string) old('id_eps') === (string) $eps->id_eps ? 'selected' : '' }}>{{ $eps->nombre }}</option>
							@endforeach
						</select>
						@error('id_eps')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					<div id="afp-wrap" class="hidden">
						<label for="afp-id" class="block text-sm font-medium text-gray-700 mb-1">AFP</label>
						<select id="afp-id" name="id_afp" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
							<option value="">Seleccione AFP</option>
							@foreach (($afpList ?? collect()) as $afp)
								<option value="{{ $afp->id_afp }}" {{ (string) old('id_afp') === (string) $afp->id_afp ? 'selected' : '' }}>{{ $afp->nombre }}</option>
							@endforeach
						</select>
						@error('id_afp')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					<div id="arl-wrap" class="hidden">
						<label for="arl-id" class="block text-sm font-medium text-gray-700 mb-1">ARL / Nivel de riesgo</label>
						<select id="arl-id" name="id_arl" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
							<option value="">Seleccione ARL</option>
							@foreach (($arlList ?? collect()) as $arl)
								<option value="{{ $arl->id_arl }}" {{ (string) old('id_arl') === (string) $arl->id_arl ? 'selected' : '' }}>{{ $arl->nombre }}</option>
							@endforeach
						</select>
						@error('id_arl')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
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
						<input id="end-date" name="fecha_fin" type="date" value="{{ old('fecha_fin') }}"
							class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition">
						<p id="end-date-hint" class="text-[10px] text-gray-500 mt-1">
							@if(isset($periodoActivo) && $periodoActivo)
								Para VAC/SLN debe ser igual o posterior al periodo actual ({{ $periodoActivo->fecha_inicio->format('d/m/Y') }}).
							@else
								Debe ser posterior a la fecha de inicio.
							@endif
						</p>
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

