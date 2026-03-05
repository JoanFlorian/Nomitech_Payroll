<div id="edit-novelty-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-xl md:text-2xl font-bold text-gray-800">Editar Novedad</h3>
            <button type="button" id="close-edit-modal-btn" class="w-9 h-9 inline-flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-600 transition">
                <span class="material-icons text-[20px]">close</span>
            </button>
        </div>

        <form id="edit-novelty-form" method="POST" class="relative z-10">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-novedad-id" name="edit_novedad_id" value="{{ old('edit_novedad_id') }}">
            <input type="hidden" id="edit-doc-empleado" name="empleado_id" value="{{ old('empleado_id', old('doc_empleado')) }}">

            <div class="p-6 md:p-8 space-y-5 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit-employee-name" class="block text-sm font-medium text-gray-700 mb-1">Nombres</label>
                        <input id="edit-employee-name" type="text" readonly class="w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm cursor-not-allowed">
                    </div>
                    <div>
                        <label for="edit-employee-lastname" class="block text-sm font-medium text-gray-700 mb-1">Apellidos</label>
                        <input id="edit-employee-lastname" type="text" readonly class="w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm cursor-not-allowed">
                    </div>
                </div>

                <div>
                    <label for="edit-employee-doc" class="block text-sm font-medium text-gray-700 mb-1">Documento</label>
                    <input id="edit-employee-doc" type="text" readonly class="w-full bg-gray-100 border border-gray-300 rounded-md shadow-sm cursor-not-allowed">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                    <div>
                        <label for="edit-novelty-type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de novedad</label>
                        <select id="edit-novelty-type" name="tipo_novedad" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                            <option value="">Seleccione un tipo</option>
                            @foreach (['Licencia', 'Incapacidad', 'Permiso', 'Suspensión'] as $tipoNovedad)
                                <option value="{{ $tipoNovedad }}" {{ old('tipo_novedad') === $tipoNovedad ? 'selected' : '' }}>{{ $tipoNovedad }}</option>
                            @endforeach
                        </select>
                        <div id="edit-licencia-remunerada-wrap" class="mt-2 hidden">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" id="edit-licencia-remunerada" name="es_remunerado" value="1" {{ old('es_remunerado', old('licencia_remunerada', '0')) ? 'checked' : '' }}>
                                <span id="edit-remunerada-label">Novedad remunerada</span>
                            </label>
                        </div>
                        <p id="edit-novelty-type-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('tipo_novedad')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidad de cantidad</label>
                        <div class="flex items-center gap-4 h-[42px] px-3 border border-gray-300 rounded-md shadow-sm">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="radio" name="unidad_cantidad" value="dias" id="edit-unit-days" {{ old('unidad_cantidad', 'dias') === 'dias' ? 'checked' : '' }}>
                                Días
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="radio" name="unidad_cantidad" value="horas" id="edit-unit-hours" {{ old('unidad_cantidad') === 'horas' ? 'checked' : '' }}>
                                Horas
                            </label>
                        </div>
                        <p id="edit-quantity-unit-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('unidad_cantidad')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edit-quantity-days" class="block text-sm font-medium text-gray-700 mb-1">Cantidad en días</label>
                        <input id="edit-quantity-days" name="cantidad_dias" type="number" step="0.01" min="0.01" value="{{ old('cantidad_dias') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 10">
                        <p id="edit-quantity-days-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('cantidad_dias')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edit-quantity-hours" class="block text-sm font-medium text-gray-700 mb-1">Cantidad en horas</label>
                        <input id="edit-quantity-hours" name="cantidad_horas" type="number" step="0.01" min="0.01" value="{{ old('cantidad_horas') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 8">
                        <p id="edit-quantity-hours-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('cantidad_horas')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edit-start-date" class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
                        <input id="edit-start-date" name="fecha_inicio" type="date" value="{{ old('fecha_inicio') }}" required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                        <p id="edit-start-date-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('fecha_inicio')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edit-end-date" class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
                        <input id="edit-end-date" name="fecha_fin" type="date" value="{{ old('fecha_fin') }}" required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                        <p id="edit-end-date-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('fecha_fin')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="edit-payment-display" class="block text-sm font-medium text-gray-700 mb-1">Pago manual (opcional)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                            <input id="edit-payment-display" type="text" class="w-full pl-7 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Valor a pagar o descontar por esta novedad" autocomplete="off">
                        </div>
                        <input type="hidden" id="edit-payment" name="pago_manual" value="{{ old('pago_manual', old('pago')) }}">
                        <p id="edit-payment-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('pago_manual')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="edit-observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                        <textarea id="edit-observaciones" name="observaciones" rows="3" maxlength="500" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Detalle adicional de la novedad (opcional)">{{ old('observaciones') }}</textarea>
                    </div>

                    <div class="md:col-span-2 rounded-lg border border-blue-100 bg-blue-50/60 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-blue-700 font-semibold">Valor estimado</p>
                        <p id="edit-estimated-value" class="mt-1 text-xl font-bold text-blue-900">$ 0</p>
                        <p id="edit-estimated-note" class="mt-1 text-xs text-blue-700">Se calcula automáticamente según salario, tipo de novedad y cantidad.</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <button id="delete-novedad-btn" type="button" class="inline-flex items-center justify-center gap-2 bg-red-50 text-red-700 border border-red-200 font-semibold py-2 px-4 rounded-md hover:bg-red-100 transition">
                    <span class="material-icons text-[18px]">delete</span>
                    Eliminar novedad
                </button>

                <div class="flex items-center gap-2">
                    <button id="cancel-edit-btn" type="button" class="bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-md hover:bg-gray-300 transition-colors duration-300">Cancelar</button>
                    <button id="update-btn" type="submit" class="bg-blue-600 text-white font-medium py-2 px-6 rounded-md shadow-sm hover:bg-blue-700 transition-colors duration-300">Guardar cambios</button>
                </div>
            </div>
        </form>

        <form id="delete-novedad-form" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
