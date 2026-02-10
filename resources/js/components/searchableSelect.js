/**
 * Searchable Select Component
 * Componente reutilizable para selects con búsqueda
 */

export default (config) => ({
    open: false,
    search: '',
    selectedKey: config.value,
    options: config.options || {},
    label: '',
    isLoading: false,
    isSearchable: config.searchable ?? true,
    endpoint: config.endpoint,

    init() {
        console.log('Searchable Select Init', config);
        if (!this.options) this.options = {};
        this.updateLabel();

        this.$watch('selectedKey', (val) => {
            this.updateLabel();
            this.$dispatch('input', val);
        });

        // Listen for dynamic updates if ID provided
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
        let found = this.options[this.selectedKey];
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
});
