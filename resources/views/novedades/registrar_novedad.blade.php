<div id="novelty-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
	<div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative">
		<div class="pointer-events-none absolute -top-10 -left-10 h-24 w-24 rounded-full bg-blue-100/50"></div>
		<div class="pointer-events-none absolute bottom-6 right-10 h-16 w-16 rotate-45 rounded-lg bg-emerald-100/50"></div>

		<div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between relative z-10">
			<h3 class="text-xl md:text-2xl font-bold text-gray-800">Registrar Novedad</h3>
			<button type="button" id="close-modal-btn" class="w-9 h-9 inline-flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-600 transition">
				<span class="material-icons text-[20px]">close</span>
			</button>
		</div>

		<form id="novelty-form" action="{{ route('novedades.store') }}" method="POST" class="relative z-10">
			@csrf
			<div class="p-6 md:p-8 space-y-5 max-h-[70vh] overflow-y-auto">
				<div>
					<label for="employee-search" class="block text-sm font-medium text-gray-700 mb-1">Nombre o documento del empleado</label>
					<div class="relative">
						<span class="material-icons absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">search</span>
						<input
							id="employee-search"
							type="text"
							autocomplete="off"
							class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition"
							placeholder="Buscar por nombre o número de documento"
							value="{{ old('employee_search') }}"
						>
					</div>
					<input type="hidden" id="doc_empleado" name="empleado_id" value="{{ old('empleado_id', old('doc_empleado')) }}">
					<ul id="employee-suggestions" class="hidden mt-2 rounded-lg border border-gray-200 bg-white shadow-sm max-h-48 overflow-auto"></ul>
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
							@foreach (['Licencia', 'Incapacidad', 'Permiso', 'Suspensión'] as $tipoNovedad)
								<option value="{{ $tipoNovedad }}" {{ old('tipo_novedad') === $tipoNovedad ? 'selected' : '' }}>{{ $tipoNovedad }}</option>
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
						<input id="quantity-days" name="cantidad_dias" type="number" step="0.01" min="0.01" value="{{ old('cantidad_dias') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 10">
						<p id="quantity-days-error" class="mt-1 text-xs text-red-600 hidden"></p>
						@error('cantidad_dias')
							<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
						@enderror
					</div>

					<div>
						<label for="quantity-hours" class="block text-sm font-medium text-gray-700 mb-1">Cantidad en horas</label>
						<input id="quantity-hours" name="cantidad_horas" type="number" step="0.01" min="0.01" value="{{ old('cantidad_horas') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 8">
						<p id="quantity-hours-error" class="mt-1 text-xs text-red-600 hidden"></p>
						@error('cantidad_horas')
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
								type="text"
								class="w-full pl-7 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition"
								placeholder="Valor a pagar o descontar por esta novedad"
								autocomplete="off"
							>
						</div>
						<input type="hidden" id="payment" name="pago_manual" value="{{ old('pago_manual', old('pago')) }}">
						<p class="mt-2 text-xs text-gray-500 italic">Ingrese el valor correspondiente que será aplicado en la nómina.</p>
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
						<p id="estimated-value" class="mt-1 text-xl font-bold text-blue-900">$ 0</p>
						<p id="estimated-note" class="mt-1 text-xs text-blue-700">Selecciona empleado, tipo y cantidad para calcular automáticamente.</p>
					</div>
				</div>
			</div>

			<div class="bg-gray-50 px-6 py-4 flex justify-end items-center border-t border-gray-200">
				<button id="cancel-btn" type="button" class="bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-colors duration-300 mr-3">Cancelar</button>
				<button id="save-btn" type="submit" class="bg-emerald-500 text-white font-medium py-2 px-6 rounded-md shadow-sm hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-300">Guardar</button>
			</div>
		</form>
	</div>
</div>
