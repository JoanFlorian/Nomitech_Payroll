<div id="edit-novelty-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-xl md:text-2xl font-bold text-gray-800">Editar Novedad</h3>
            <button type="button" id="close-edit-modal-btn" onclick="window.__closeEditModal && window.__closeEditModal()" class="w-9 h-9 inline-flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-600 transition">
                <span class="material-icons text-[20px]">close</span>
            </button>
        </div>

        <form id="edit-novelty-form" action="{{ route('novedades.update', ['id_novedad' => 0]) }}" method="POST" class="relative z-10">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-novedad-id" name="edit_novedad_id" value="{{ old('edit_novedad_id') }}">
            <input type="hidden" id="edit-doc-empleado" name="empleado_id" value="{{ old('empleado_id', old('doc_empleado')) }}">
            <input type="hidden" id="edit-salario-base" name="salario_base" value="{{ old('salario_base', 0) }}">

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
                        <input id="edit-quantity-days" name="cantidad_dias" type="number" step="0.01" min="0.01" max="126" value="{{ old('cantidad_dias') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 10">
                        <p id="edit-quantity-days-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('cantidad_dias')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edit-quantity-hours" class="block text-sm font-medium text-gray-700 mb-1">Cantidad en horas</label>
                        <input id="edit-quantity-hours" name="cantidad_horas" type="number" step="0.01" min="0.01" max="240" value="{{ old('cantidad_horas') }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-[#1565C0] focus:border-[#1565C0] transition" placeholder="Ej: 8">
                        <p id="edit-quantity-hours-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        @error('cantidad_horas')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Select de tipo de incapacidad eliminado en edición --}}

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
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" id="edit-certificado-medico" name="certificado_medico" value="1" {{ old('certificado_medico') ? 'checked' : '' }}>
                            <span>Certificado médico adjunto/verificado</span>
                        </label>
                    </div>

                    <div id="edit-eps-wrap" class="hidden">
                        <label for="edit-eps-id" class="block text-sm font-medium text-gray-700 mb-1">EPS</label>
                        <select id="edit-eps-id" name="id_eps" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                            <option value="">Seleccione EPS</option>
                            @foreach (($epsList ?? collect()) as $eps)
                                <option value="{{ $eps->id_eps }}" {{ (string) old('id_eps') === (string) $eps->id_eps ? 'selected' : '' }}>{{ $eps->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="edit-afp-wrap" class="hidden">
                        <label for="edit-afp-id" class="block text-sm font-medium text-gray-700 mb-1">AFP</label>
                        <select id="edit-afp-id" name="id_afp" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                            <option value="">Seleccione AFP</option>
                            @foreach (($afpList ?? collect()) as $afp)
                                <option value="{{ $afp->id_afp }}" {{ (string) old('id_afp') === (string) $afp->id_afp ? 'selected' : '' }}>{{ $afp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="edit-arl-wrap" class="hidden">
                        <label for="edit-arl-id" class="block text-sm font-medium text-gray-700 mb-1">ARL / Nivel de riesgo</label>
                        <select id="edit-arl-id" name="id_arl" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] transition">
                            <option value="">Seleccione ARL</option>
                            @foreach (($arlList ?? collect()) as $arl)
                                <option value="{{ $arl->id_arl }}" {{ (string) old('id_arl') === (string) $arl->id_arl ? 'selected' : '' }}>{{ $arl->nombre }}</option>
                            @endforeach
                        </select>
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

@push('scripts')
<script>
(function() {
    console.log('=== Edit Modal Script Loading ===');
    
    const updateUrl = @json(route('novedades.update', ['id_novedad' => '__ID__']));
    const deleteUrl = @json(route('novedades.destroy', ['id_novedad' => '__ID__']));

    function openEditModal() {
        console.log('openEditModal called');
        const modal = document.getElementById('edit-novelty-modal');
        if (!modal) {
            console.error('Modal not found: edit-novelty-modal');
            return;
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        console.log('Modal opened');
    }

    function closeEditModal() {
        const modal = document.getElementById('edit-novelty-modal');
        if (!modal) return;
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    function openEditByButton(button) {
        console.log('openEditByButton called', button);
        if (!button) {
            console.error('No button provided');
            return;
        }

        const id = button.dataset.novedadId || '';
        console.log('Novedad ID:', id);
        
        const editForm = document.getElementById('edit-novelty-form');
        const deleteForm = document.getElementById('delete-novedad-form');
        const editNovedadId = document.getElementById('edit-novedad-id');

        const setValue = (elementId, value) => {
            const el = document.getElementById(elementId);
            if (el) el.value = value || '';
        };

        setValue('edit-doc-empleado', button.dataset.doc);
        setValue('edit-employee-name', button.dataset.nombres);
        setValue('edit-employee-lastname', button.dataset.apellidos);
        setValue('edit-employee-doc', button.dataset.doc);
        setValue('edit-novelty-type', button.dataset.tipo);
        setValue('edit-quantity-days', button.dataset.dias);
        setValue('edit-quantity-hours', button.dataset.horas);
        setValue('edit-start-date', button.dataset.fechaInicio);
        setValue('edit-end-date', button.dataset.fechaFin);
        setValue('edit-observaciones', button.dataset.observaciones);
        setValue('edit-salario-base', button.dataset.salarioBase);
        setValue('edit-tipo-licencia', button.dataset.tipoLicencia);
        setValue('edit-tipo-incapacidad', button.dataset.tipoIncapacidad);
        setValue('edit-eps-id', button.dataset.idEps);
        setValue('edit-afp-id', button.dataset.idAfp);
        setValue('edit-arl-id', button.dataset.idArl);

        const editPago = document.getElementById('edit-payment');
        const editPagoDisplay = document.getElementById('edit-payment-display');
        if (editPago) editPago.value = button.dataset.pago || '';
        if (editPagoDisplay) editPagoDisplay.value = button.dataset.pago || '';

        const unidad = button.dataset.unidad || 'dias';
        const unidadRadio = document.querySelector('#edit-novelty-form input[name="unidad_cantidad"][value="' + unidad + '"]');
        if (unidadRadio) unidadRadio.checked = true;

        const licenciaRemunerada = document.getElementById('edit-licencia-remunerada');
        if (licenciaRemunerada) {
            licenciaRemunerada.checked = String(button.dataset.licenciaRemunerada || '0') === '1';
        }

        const certificado = document.getElementById('edit-certificado-medico');
        if (certificado) {
            certificado.checked = String(button.dataset.certificadoMedico || '0') === '1';
        }

        if (editNovedadId) editNovedadId.value = id;
        if (editForm && id) editForm.action = updateUrl.replace('__ID__', String(id));
        if (deleteForm && id) deleteForm.action = deleteUrl.replace('__ID__', String(id));

        openEditModal();
    }

    async function deleteNovedadByButton(button) {
        console.log('deleteNovedadByButton called', button);
        if (!button) return;
        
        const id = button.dataset.novedadId || '';
        const deleteForm = document.getElementById('delete-novedad-form');
        
        if (!id || !deleteForm) {
            console.error('Missing id or deleteForm', { id, deleteForm });
            return;
        }

        let confirmed = false;
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
            confirmed = result.isConfirmed;
        } else {
            confirmed = window.confirm('¿Seguro que deseas eliminar esta novedad? Esta acción no se puede deshacer.');
        }

        if (!confirmed) return;

        deleteForm.action = deleteUrl.replace('__ID__', String(id));
        console.log('Submitting delete form to:', deleteForm.action);
        deleteForm.submit();
    }

    // Registrar en window
    window.__openEditModal = openEditModal;
    window.__closeEditModal = closeEditModal;
    window.__openEditByButton = openEditByButton;
    window.__deleteNovedadByButton = deleteNovedadByButton;

    console.log('=== Edit Modal Functions Registered ===');
    console.log('__openEditByButton:', typeof window.__openEditByButton);
})();
</script>
@endpush
