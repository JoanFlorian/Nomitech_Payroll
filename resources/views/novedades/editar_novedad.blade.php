<div id="edit-novelty-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-xl md:text-2xl font-bold text-gray-800">Editar Novedad</h3>
            <button type="button" id="close-edit-modal-btn" onclick="window.__closeEditModal && window.__closeEditModal()" class="w-9 h-9 inline-flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-600 transition">
                <span class="material-icons text-[20px]">close</span>
            </button>
        </div>

        <form id="edit-novelty-form" action="{{ route('novedades.update', ['id_novedad' => 0]) }}" method="POST" enctype="multipart/form-data" class="relative z-10">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-novedad-id" name="edit_novedad_id" value="{{ old('edit_novedad_id') }}">
            <input type="hidden" id="edit-doc-empleado" name="empleado_id" value="{{ old('empleado_id', old('doc_empleado')) }}">
            <input type="hidden" id="edit-salario-base" name="salario_base" value="{{ old('salario_base', 0) }}">
            <input type="hidden" id="edit-vacaciones-balance" name="vacaciones_balance" value="{{ old('vacaciones_balance') }}">
            <input type="hidden" id="edit-vacaciones-registradas" name="vacaciones_registradas" value="0">
            <input type="hidden" id="edit-original-days" value="0">
            <input type="hidden" id="edit-existing-medical-support" value="0">

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
                        <input id="edit-quantity-days" name="cantidad_dias" type="number" step="0.01" value="{{ old('cantidad_dias') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 10">
                        <div id="edit-vacaciones-balance-info" class="hidden mt-2 p-3 bg-indigo-50 border border-indigo-100 rounded-xl">
                            <div class="flex items-center gap-2 text-indigo-700">
                                <i class="material-icons text-[18px]">wb_sunny</i>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Saldo disponible:</span>
                                <span id="edit-vacaciones-balance-display" class="font-black">0</span>
                                <span class="text-[10px]">días</span>
                            </div>
                        </div>
                        <p id="edit-quantity-days-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('cantidad_dias')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edit-quantity-hours" class="block text-sm font-medium text-gray-700 mb-1">Cantidad en horas</label>
                        <input id="edit-quantity-hours" name="cantidad_horas" type="number" step="0.01" value="{{ old('cantidad_horas') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 8">
                        <p id="edit-quantity-hours-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('cantidad_horas')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="edit-tipo-licencia-wrap" class="hidden">
                        <label for="edit-tipo-licencia" class="block text-sm font-medium text-gray-700 mb-1">Tipo de licencia</label>
                        <select id="edit-tipo-licencia" name="tipo_licencia" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                            <option value="">Seleccione</option>
                            <option value="luto" {{ old('tipo_licencia') === 'luto' ? 'selected' : '' }}>Luto</option>
                            <option value="calamidad_domestica" {{ old('tipo_licencia') === 'calamidad_domestica' ? 'selected' : '' }}>Calamidad doméstica</option>
                            <option value="permiso_especial" {{ old('tipo_licencia') === 'permiso_especial' ? 'selected' : '' }}>Permiso especial</option>
                            <option value="remunerada" {{ old('tipo_licencia') === 'remunerada' ? 'selected' : '' }}>Remunerada</option>
                            <option value="no_remunerada" {{ old('tipo_licencia') === 'no_remunerada' ? 'selected' : '' }}>No remunerada</option>
                        </select>
                    </div>

                    <div id="edit-certificado-medico-wrap" class="hidden md:col-span-2">
                        <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 space-y-3">
                            <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <label for="edit-medical-support-file" class="block text-sm font-semibold text-gray-700">Certificado médico / soporte clínico <span class="text-red-500">*</span></label>
                                    <p class="mt-1 text-xs text-gray-500">Adjunta el soporte en PDF, JPG o PNG. Máximo 5 MB. Si ya existe uno cargado, solo sube un archivo nuevo para reemplazarlo.</p>
                                </div>
                                <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                                    <input type="checkbox" id="edit-certificado-medico" name="certificado_medico" value="1" {{ old('certificado_medico') ? 'checked' : '' }}>
                                    <span>Soporte verificado</span>
                                </label>
                            </div>

                            <input id="edit-medical-support-file" name="soporte_medico_archivo" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100">
                            <p id="edit-medical-support-current-note" class="hidden text-xs text-emerald-700">Ya existe un soporte médico asociado. Solo adjunta uno nuevo si deseas reemplazarlo.</p>
                            <p id="edit-medical-support-file-error" class="mt-1 text-xs text-red-600 hidden"></p>
                            @error('soporte_medico_archivo')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @error('certificado_medico')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div id="edit-eps-wrap" class="hidden">
                        <label for="edit-eps-id" class="block text-sm font-medium text-gray-700 mb-1">EPS</label>
                        <select id="edit-eps-id" name="id_eps" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                            <option value="">Seleccione EPS</option>
                            @foreach (($epsList ?? collect()) as $eps)
                                <option value="{{ $eps->id_eps }}" {{ (string) old('id_eps') === (string) $eps->id_eps ? 'selected' : '' }}>{{ $eps->nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_eps')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="edit-afp-wrap" class="hidden">
                        <label for="edit-afp-id" class="block text-sm font-medium text-gray-700 mb-1">AFP</label>
                        <select id="edit-afp-id" name="id_afp" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                            <option value="">Seleccione AFP</option>
                            @foreach (($afpList ?? collect()) as $afp)
                                <option value="{{ $afp->id_afp }}" {{ (string) old('id_afp') === (string) $afp->id_afp ? 'selected' : '' }}>{{ $afp->nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_afp')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="edit-arl-wrap" class="hidden">
                        <label for="edit-arl-id" class="block text-sm font-medium text-gray-700 mb-1">ARL / Nivel de riesgo</label>
                        <select id="edit-arl-id" name="id_arl" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
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
                        <label for="edit-start-date" class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
                        <div class="relative">
                            <input id="edit-start-date" name="fecha_inicio" type="date" value="{{ old('fecha_inicio') }}" required class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                            <div id="edit-start-date-lock" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-amber-500" title="Fecha protegida por periodo cerrado">
                                <span class="material-icons text-[18px]">lock</span>
                            </div>
                        </div>
                        <p id="edit-start-date-warning" class="mt-1 text-[10px] text-amber-600 font-medium hidden">Esta fecha no se puede modificar porque pertenece a un periodo ya liquidado.</p>
                        <p id="edit-start-date-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('fecha_inicio')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edit-end-date" class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
                        <input id="edit-end-date" name="fecha_fin" type="date" value="{{ old('fecha_fin') }}"
                            class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                        <p id="edit-end-date-hint" class="text-[10px] text-gray-500 mt-1">
                            @if(isset($periodoActivo) && $periodoActivo)
                                Para VAC/SLN debe ser igual o posterior al periodo actual ({{ $periodoActivo->fecha_inicio->format('d/m/Y') }}).
                            @else
                                Debe ser posterior a la fecha de inicio.
                            @endif
                        </p>
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
                        <p id="edit-payment-auto-message" class="mt-1 text-xs text-blue-700 hidden">Esta novedad se calcula automáticamente según el salario del empleado y la cantidad de días u horas registradas.</p>
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
                        <div class="mt-2">
                            <span id="edit-novelty-nature-badge" class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200">Naturaleza: -</span>
                        </div>
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
                    <button id="cancel-edit-btn" type="button" onclick="window.__closeEditModal && window.__closeEditModal()" class="bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-md hover:bg-gray-300 transition-colors duration-300">Cancelar</button>
                    <button id="update-btn" type="submit" class="bg-blue-600 text-white font-medium py-2 px-6 rounded-md shadow-sm hover:bg-blue-700 transition-colors duration-300">Guardar cambios</button>
                </div>
            </div>
        </form>

        <form id="delete-novedad-form" action="{{ route('novedades.destroy', ['id_novedad' => 0]) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
