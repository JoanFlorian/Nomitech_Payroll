export default (initialData = {}) => ({
    // Campos del formulario
    nit: initialData.nit || '',
    nit_dv: initialData.nit_dv || '',
    razon_social: initialData.razon_social || '',
    id_departamento: initialData.id_departamento || '',
    id_ciudad: initialData.id_ciudad || '',
    direccion_empresa: initialData.direccion_empresa || '',
    documento: initialData.documento || '',
    id_tipo_doc: initialData.id_tipo_doc || '',
    primer_apellido: initialData.primer_apellido || '',
    segundo_apellido: initialData.segundo_apellido || '',
    primer_nombre: initialData.primer_nombre || '',
    otros_nombres: initialData.otros_nombres || '',
    telefono_celular: initialData.telefono_celular || '',
    email: initialData.email || '',
    password: '',
    password_confirmation: '',
    plan_id: initialData.plan_id || '',

    // Estado de validación
    errors: {},
    touched: {},
    isSubmitting: false,

    init() {
        // Cargar plan de la URL si no viene por old input
        if (!this.plan_id) {
            const urlParams = new URLSearchParams(window.location.search);
            this.plan_id = urlParams.get('plan_id') || '';
        }

        this.$watch('id_departamento', (val) => {
            if (this.touched['id_departamento']) this.validateField('id_departamento', val);
            this.fetchCities(val);
        });
        this.$watch('id_ciudad', (val) => {
            if (this.touched['id_ciudad']) this.validateField('id_ciudad', val);
        });
    },

    // Marcar campo como tocado y validar
    handleBlur(field) {
        this.touched[field] = true;
        this.validateField(field, this[field]);
    },

    // Validar mientras escribe (opcional para ciertos campos)
    handleInput(field) {
        if (this.touched[field]) {
            this.validateField(field, this[field]);
        }
    },

    validateField(field, value) {
        const rules = {
            nit: () => {
                if (!value) return "El NIT es requerido";
                if (!/^[0-9]+$/.test(value)) return "El NIT debe ser solo numérico";
                if (value.length < 5 || value.length > 15) return "El NIT debe tener entre 5 y 15 dígitos";
                return null;
            },
            nit_dv: () => {
                if (!value) return "El DV es requerido";
                if (!/^[0-9]+$/.test(value)) return "El DV debe ser numérico";
                if (value.length < 1 || value.length > 2) return "El DV debe tener 1 o 2 dígitos";
                return null;
            },
            razon_social: () => {
                if (!value) return "La Razón Social es requerida";
                if (value.length < 3) return "La Razón Social debe tener al menos 3 caracteres";
                if (value.length > 60) return "La Razón Social no debe exceder los 60 caracteres";
                return null;
            },
            direccion_empresa: () => {
                if (!value) return "La Dirección es requerida";
                if (value.length < 5) return "La Dirección debe tener al menos 5 caracteres";
                if (value.length > 60) return "La Dirección no debe exceder los 60 caracteres";
                return null;
            },
            documento: () => {
                if (!value) return "El Documento es requerido";
                if (!/^[0-9]+$/.test(value)) return "El Documento debe ser numérico";
                if (value.length < 6 || value.length > 12) return "El Documento debe tener entre 6 y 12 dígitos";
                return null;
            },
            id_tipo_doc: () => !value ? "El Tipo de Documento es requerido" : null,
            id_departamento: () => !value ? "El Departamento es requerido" : null,
            id_ciudad: () => !value ? "El Municipio/Ciudad es requerido" : null,
            primer_apellido: () => {
                if (!value) return "El Primer Apellido es requerido";
                if (value.length < 3 || value.length > 60) return "El Primer Apellido debe tener entre 3 y 60 caracteres";
                return null;
            },
            segundo_apellido: () => {
                if (!value) return "El Segundo Apellido es requerido";
                if (value.length < 3 || value.length > 60) return "El Segundo Apellido debe tener entre 3 y 60 caracteres";
                return null;
            },
            primer_nombre: () => {
                if (!value) return "El Primer Nombre es requerido";
                if (value.length < 3 || value.length > 60) return "El Primer Nombre debe tener entre 3 y 60 caracteres";
                return null;
            },
            otros_nombres: () => {
                if (!value) return null; // Nullable
                if (value.length < 3 || value.length > 60) return "Los Otros Nombres deben tener entre 3 y 60 caracteres";
                return null;
            },
            telefono_celular: () => {
                if (!value) return "El Teléfono Celular es requerido";
                if (!/^3[0-9]{9}$/.test(value)) return "El Teléfono Celular debe ser un número de Colombia válido (10 dígitos)";
                return null;
            },
            email: () => {
                if (!value) return "El Correo Electrónico es requerido";
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return "El Correo Electrónico debe ser una dirección válida";
                return null;
            },
            password: () => {
                if (!value) return "La Contraseña es requerida";
                if (value.length < 8) return "La Contraseña debe tener al menos 8 caracteres";
                if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])/.test(value)) {
                    return "La Contraseña debe contener mayúscula, minúscula, número y símbolo";
                }
                return null;
            },
            password_confirmation: () => {
                if (!value) return "La Confirmación de la Contraseña es requerida";
                if (value !== this.password) return "La Confirmación de la Contraseña no coincide";
                return null;
            }
        };

        if (rules[field]) {
            const error = rules[field]();
            if (error) {
                this.errors[field] = error;
            } else {
                delete this.errors[field];
            }
        }
    },

    validateForm() {
        const fields = [
            'nit', 'nit_dv', 'razon_social', 'id_departamento', 'id_ciudad',
            'direccion_empresa', 'documento', 'id_tipo_doc', 'primer_apellido',
            'segundo_apellido', 'primer_nombre', 'telefono_celular', 'email',
            'password', 'password_confirmation'
        ];

        fields.forEach(f => {
            this.touched[f] = true;
            this.validateField(f, this[f]);
        });

        if (Object.keys(this.errors).length > 0) {
            // Scroll al primer error
            const firstErrorField = Object.keys(this.errors)[0];
            const element = document.getElementsByName(firstErrorField)[0];
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                element.focus();
            }
            return false;
        }

        this.isSubmitting = true;
        return true;
    },

    // ========================================
    // MÉTODOS DE API Y UTILIDADES
    // ========================================

    async onCityChange(id) {
        if (!id) return;
        this.id_ciudad = String(id);
        try {
            let res = await fetch('/api/city-details/' + id);
            let data = await res.json();
            if (data && data.id_departamento) {
                this.id_departamento = String(data.id_departamento);
            }
        } catch (e) {
            console.error(e);
        }
    },

    async fetchCities(deptId) {
        if (!deptId) return;
        try {
            let res = await fetch('/api/cities/' + deptId);
            let data = await res.json();
            let options = {};
            data.forEach(item => {
                options[String(item.id_ciudad)] = item.nombre;
            });
            window.dispatchEvent(new CustomEvent('set-options-citySelector', { detail: options }));
        } catch (e) {
            console.error(e);
        }
    }
});
