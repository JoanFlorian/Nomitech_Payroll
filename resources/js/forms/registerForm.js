/**
 * Register Form Component
 * Lógica de validación y manejo del formulario de registro
 */

export default () => ({
    departmentId: '',
    cityId: '',
    docType: '',
    errors: {},
    isSubmitting: false,

    init() {
        this.$watch('departmentId', (val) => this.fetchCities(val));
    },

    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================

    validateRequired(fieldName, value, label) {
        if (!value || value.toString().trim() === '') {
            this.errors[fieldName] = `${label} es obligatorio`;
            return false;
        }
        delete this.errors[fieldName];
        return true;
    },

    validateMaxLength(fieldName, value, maxLength, label) {
        if (value && value.length > maxLength) {
            this.errors[fieldName] = `${label} no puede exceder ${maxLength} caracteres`;
            return false;
        }
        delete this.errors[fieldName];
        return true;
    },

    validateMinLength(fieldName, value, minLength, label) {
        if (value && value.length < minLength) {
            this.errors[fieldName] = `${label} debe tener al menos ${minLength} caracteres`;
            return false;
        }
        delete this.errors[fieldName];
        return true;
    },

    validateNumeric(fieldName, value, label) {
        if (value && !/^[0-9]+$/.test(value)) {
            this.errors[fieldName] = `${label} debe contener solo números`;
            return false;
        }
        delete this.errors[fieldName];
        return true;
    },

    validateExactLength(fieldName, value, length, label) {
        if (value && value.length !== length) {
            this.errors[fieldName] = `${label} debe tener exactamente ${length} dígito${length > 1 ? 's' : ''}`;
            return false;
        }
        delete this.errors[fieldName];
        return true;
    },

    validateEmail(fieldName, value, label) {
        if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            this.errors[fieldName] = `${label} no es válido`;
            return false;
        }
        delete this.errors[fieldName];
        return true;
    },

    validatePasswordConfirmation(password, confirmation) {
        if (password && confirmation && password !== confirmation) {
            this.errors['contrasena_confirmation'] = 'Las contraseñas no coinciden';
            return false;
        }
        delete this.errors['contrasena_confirmation'];
        return true;
    },

    // Validación de nombres (solo letras, espacios, acentos)
    validateAlphabetic(fieldName, value, label) {
        if (value && !/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(value)) {
            this.errors[fieldName] = `${label} solo puede contener letras`;
            return false;
        }
        delete this.errors[fieldName];
        return true;
    },

    // Validación de teléfono (solo números, espacios, guiones, paréntesis)
    validatePhone(fieldName, value, label) {
        if (value && !/^[0-9\s\-()]+$/.test(value)) {
            this.errors[fieldName] = `${label} solo puede contener números`;
            return false;
        }
        delete this.errors[fieldName];
        return true;
    },

    // ========================================
    // VALIDACIÓN COMPLETA DEL FORMULARIO
    // ========================================

    validateForm(event) {
        this.errors = {};
        let isValid = true;

        const form = event.target;
        const formData = new FormData(form);

        // NIE033: NIT - requerido, numérico, max 20
        const nit = formData.get('nit');
        if (!this.validateRequired('nit', nit, 'NIT')) isValid = false;
        else if (!this.validateNumeric('nit', nit, 'NIT')) isValid = false;
        else if (!this.validateMaxLength('nit', nit, 20, 'NIT')) isValid = false;

        // NIE034: DV - requerido, numérico, exactamente 1 dígito
        const dv = formData.get('dv');
        if (!this.validateRequired('dv', dv, 'Dígito de Verificación')) isValid = false;
        else if (!this.validateNumeric('dv', dv, 'Dígito de Verificación')) isValid = false;
        else if (!this.validateExactLength('dv', dv, 1, 'Dígito de Verificación')) isValid = false;

        // NIE035: País - requerido
        const pais = formData.get('pais');
        if (!this.validateRequired('pais', pais, 'País')) isValid = false;

        // NIE036: Departamento - requerido
        if (!this.validateRequired('id_departamento', this.departmentId, 'Departamento')) isValid = false;

        // NIE037: Ciudad - requerido
        if (!this.validateRequired('id_ciudad', this.cityId, 'Ciudad')) isValid = false;

        // NIE038: Dirección - requerido, max 255
        const direccion = formData.get('direccion_empresa');
        if (!this.validateRequired('direccion_empresa', direccion, 'Dirección')) isValid = false;
        else if (!this.validateMaxLength('direccion_empresa', direccion, 255, 'Dirección')) isValid = false;

        // Campos de usuario
        const doc = formData.get('doc');
        if (!this.validateRequired('doc', doc, 'Número de Documento')) isValid = false;
        else if (!this.validateMaxLength('doc', doc, 20, 'Número de Documento')) isValid = false;

        if (!this.validateRequired('id_tipo_doc', this.docType, 'Tipo de Documento')) isValid = false;

        const primerApellido = formData.get('primer_apellido');
        if (!this.validateRequired('primer_apellido', primerApellido, 'Primer Apellido')) isValid = false;
        else if (!this.validateAlphabetic('primer_apellido', primerApellido, 'Primer Apellido')) isValid = false;
        else if (!this.validateMaxLength('primer_apellido', primerApellido, 60, 'Primer Apellido')) isValid = false;

        const primerNombre = formData.get('primer_nombre');
        if (!this.validateRequired('primer_nombre', primerNombre, 'Primer Nombre')) isValid = false;
        else if (!this.validateAlphabetic('primer_nombre', primerNombre, 'Primer Nombre')) isValid = false;
        else if (!this.validateMaxLength('primer_nombre', primerNombre, 60, 'Primer Nombre')) isValid = false;

        const telefono = formData.get('telefono');
        if (!this.validateRequired('telefono', telefono, 'Teléfono')) isValid = false;
        else if (!this.validatePhone('telefono', telefono, 'Teléfono')) isValid = false;
        else if (!this.validateMaxLength('telefono', telefono, 20, 'Teléfono')) isValid = false;

        const correo = formData.get('correo');
        if (!this.validateRequired('correo', correo, 'Correo electrónico')) isValid = false;
        else if (!this.validateEmail('correo', correo, 'Correo electrónico')) isValid = false;
        else if (!this.validateMaxLength('correo', correo, 256, 'Correo electrónico')) isValid = false;

        const contrasena = formData.get('contrasena');
        if (!this.validateRequired('contrasena', contrasena, 'Contraseña')) isValid = false;
        else if (!this.validateMinLength('contrasena', contrasena, 8, 'Contraseña')) isValid = false;

        const contrasenaConfirmation = formData.get('contrasena_confirmation');
        if (!this.validatePasswordConfirmation(contrasena, contrasenaConfirmation)) isValid = false;

        // Validaciones opcionales con max length y formato
        const razonSocial = formData.get('razon_social');
        if (razonSocial) this.validateMaxLength('razon_social', razonSocial, 60, 'Razón Social');

        const segundoApellido = formData.get('segundo_apellido');
        if (segundoApellido) {
            if (!this.validateAlphabetic('segundo_apellido', segundoApellido, 'Segundo Apellido')) isValid = false;
            else this.validateMaxLength('segundo_apellido', segundoApellido, 60, 'Segundo Apellido');
        }

        const otrosNombres = formData.get('otros_nombres');
        if (otrosNombres) {
            if (!this.validateAlphabetic('otros_nombres', otrosNombres, 'Otros Nombres')) isValid = false;
            else this.validateMaxLength('otros_nombres', otrosNombres, 60, 'Otros Nombres');
        }

        if (!isValid) {
            event.preventDefault();
            // Scroll al primer error
            const firstErrorField = Object.keys(this.errors)[0];
            const element = document.querySelector(`[name="${firstErrorField}"]`);
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
    // MÉTODOS DE API
    // ========================================

    async onCityChange(id) {
        if (!id) return;
        try {
            let res = await fetch('/api/city-details/' + id);
            let data = await res.json();
            if (data && data.id_departamento) {
                this.departmentId = String(data.id_departamento);
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
