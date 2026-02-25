<h2 class="text-2xl font-bold text-gray-800 mb-6">
    Paso 1: Información Personal
</h2>
<!-- Indicador de pasos -->
<div class="mb-10">
    <div class="flex justify-between text-sm mb-2">
        <span class="font-semibold text-[rgb(16,185,129)]">
            Información Personal
        </span>
        <span class="text-gray-500">Información Contractual y Laboral</span>
        <span class="text-gray-500 text-right">
            Información Financiera y Seguridad Social
        </span>
    </div>

    <div class="relative h-1 bg-gray-200 rounded-full">
        <div class="absolute h-1 bg-[rgb(16,185,129)] rounded-full w-1/3"></div>

        <div class="absolute -top-3 left-0 w-full flex justify-between">
            <div class="w-6 h-6 bg-[rgb(16,185,129)] text-white rounded-full flex items-center justify-center text-xs font-bold">
                1
            </div>

            <div class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs font-bold">
                2
            </div>

            <div class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs font-bold">
                3
            </div>
        </div>
    </div>
</div>

<form id="step1" novalidate action="{{ route('employees.step1') }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tipo de documento
            </label>
            <select
                class="form-select mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"  name="id_tipo_doc">
                <option value="">Seleccionar...</option>
                @foreach ( $tipodoc as $tipodo )
                    <option value="{{ $tipodo->id_tipo_doc }}">{{ $tipodo->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="id_tipo_doc"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Número de documento 
            </label>
            <input
                type="number"
                placeholder="Ej: 1234567890"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="doc"
                required>
            <div class="error-message invalid-feedback" data-error="doc"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Primer apellido
            </label>
            <input
                type="text"
                placeholder="Ej: Pérez"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="primer_apellido"
                required>
            <div class="error-message invalid-feedback" data-error="primer_apellido"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Segundo apellido
            </label>
            <input
                type="text"
                placeholder="Ej: Gómez"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="segundo_apellido">
            <div class="error-message invalid-feedback" data-error="segundo_apellido"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Primer nombre
            </label>
            <input
                type="text"
                placeholder="Ej: Juan"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="primer_nombre"
                required>
            <div class="error-message invalid-feedback" data-error="primer_nombre"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Otros nombres
            </label>
            <input
                type="text"
                placeholder="Ej: Carlos"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="otros_nombres">
            <div class="error-message invalid-feedback" data-error="otros_nombres"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Correo electrónico
            </label>
            <input
                type="email"
                placeholder="Ej: empleado@empresa.com"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                name="email"
                required>
            <div class="error-message invalid-feedback" data-error="email"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Teléfono
            </label>
            <input
                type="text"
                placeholder="Ej: 3001234567"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                name="telefono"
                required>
            <div class="error-message invalid-feedback" data-error="telefono"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Departamento 
            </label>
            <x-form.searchable-select
                name="departamento"
                id="deptStep1"
                icon="location_on"
                placeholder="Departamento"
                :options="$departamento->pluck('nombre', 'id_departamento')"
            />
            <div class="error-message invalid-feedback" data-error="departamento"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Ciudad
            </label>
            <x-form.searchable-select
                name="ciudad"
                id="cityStep1"
                icon="location_city"
                placeholder="Ciudad / Municipio"
                :options="[]"
            />
            <div class="error-message invalid-feedback" data-error="ciudad"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Dirección
            </label>
            <input
                type="text"
                placeholder="Ej: Calle 10 #42-15"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="direccion"
                required>
            <div class="error-message invalid-feedback" data-error="direccion"></div>
        </div>

    </div>

    <div class="mt-10 flex justify-end gap-3">
        <button
            type="button"
            @click="closeModals()"
            class="bg-gray-200 text-gray-800 font-medium py-2 px-6 rounded-md hover:bg-gray-300 transition">
            Cancelar
        </button>
        <button
            type="submit"
            class="bg-[rgb(16,185,129)] text-white font-medium py-2 px-6 rounded-md hover:bg-[rgb(14,160,112)] transition">
            Continuar
        </button>
    </div>
</form> 

<script>
    function searchableSelect(config) {
        return {
            open: false,
            search: '',
            selectedKey: config.value,
            options: config.options || {},
            label: '',
            isLoading: false,
            isSearchable: config.searchable ?? true,
            endpoint: config.endpoint,

            init() {
                if (!this.options) this.options = {};
                this.updateLabel();

                this.$watch('selectedKey', () => {
                    this.updateLabel();
                    this.$dispatch('input', this.selectedKey);
                });

                if (config.id) {
                    window.addEventListener(`set-options-${config.id}`, (e) => {
                        this.options = e.detail;

                        if (this.selectedKey && this.options[this.selectedKey]) {
                            this.label = this.options[this.selectedKey];
                        } else {
                            this.selectedKey = null;
                            this.label = '';
                        }
                    });
                }
            },

            updateLabel() {
                if (!this.selectedKey) {
                    this.label = '';
                    return;
                }
                const found = this.options[this.selectedKey];
                if (found) {
                    this.label = found;
                }
            },

            get filteredOptions() {
                if (this.search === '') {
                    return this.options;
                }
                const term = this.search.toLowerCase();
                return Object.entries(this.options).reduce((acc, [key, val]) => {
                    if (String(val).toLowerCase().includes(term)) {
                        acc[key] = val;
                    }
                    return acc;
                }, {});
            },

            toggle() {
                this.open = !this.open;
            },

            select(key, value) {
                this.selectedKey = key;
                this.label = value;
                this.open = false;
                this.search = '';
                this.$dispatch('selected', { key: key, value: value });
            },

            async performSearch(query) {
                if (!this.isSearchable) return;
                this.search = query;
                if (!this.endpoint) return;

                if (query.length < 2) return;

                this.isLoading = true;
                try {
                    let url = this.endpoint + encodeURIComponent(query);
                    let res = await fetch(url);
                    let data = await res.json();

                    let newOptions = {};
                    data.forEach(item => {
                        let id = item.id_ciudad || item.id || item.id_departamento;
                        let text = item.nombre || item.name;
                        if (id && text) newOptions[id] = text;
                    });

                    this.options = newOptions;
                } catch (e) {
                    console.error(e);
                }
                this.isLoading = false;
            }
        };
    }

    function ensureSearchableSelect() {
        if (!window.Alpine) {
            return;
        }

        if (!window.__searchableSelectRegistered) {
            window.Alpine.data('searchableSelect', searchableSelect);
            window.__searchableSelectRegistered = true;
        }
    }

    document.addEventListener('alpine:init', ensureSearchableSelect);

    document.addEventListener('DOMContentLoaded', function () {
        ensureSearchableSelect();

        const deptValue = document.querySelector('#deptStep1 input[name="departamento"]')?.value;
        if (deptValue) {
            syncCityOptionsByDepartment(deptValue);
        }
    });

    function handleDeptSelectionChange(event) {
        const deptSelect = event.target?.closest?.('#deptStep1');
        if (!deptSelect) {
            return;
        }

        const selectedKey = event?.detail?.key ?? event?.detail ?? '';
        syncCityOptionsByDepartment(selectedKey);
    }

    document.addEventListener('selected', handleDeptSelectionChange);
    document.addEventListener('input', handleDeptSelectionChange);

    function syncCityOptionsByDepartment(deptId) {
        const normalizedId = deptId !== undefined && deptId !== null ? String(deptId) : '';

        if (!normalizedId) {
            window.dispatchEvent(new CustomEvent('set-options-cityStep1', { detail: {} }));
            return;
        }

        fetch(`/api/cities/${encodeURIComponent(normalizedId)}`)
            .then(response => response.ok ? response.json() : [])
            .then(data => {
                const options = {};
                (data || []).forEach(item => {
                    if (item && item.id_ciudad && item.nombre) {
                        options[String(item.id_ciudad)] = item.nombre;
                    }
                });
                window.dispatchEvent(new CustomEvent('set-options-cityStep1', { detail: options }));
                window.dispatchEvent(new CustomEvent('step1-city-options-updated'));
            })
            .catch(() => {
                window.dispatchEvent(new CustomEvent('set-options-cityStep1', { detail: {} }));
                window.dispatchEvent(new CustomEvent('step1-city-options-updated'));
            });
    }
</script>
