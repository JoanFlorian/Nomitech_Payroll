<!-- MODAL GENÉRICO DE EDICIÓN -->
<div id="modalEdicion" dusk="modal-edicion"
    class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div
        class="bg-white rounded-2xl w-full max-w-2xl p-8 relative shadow-2xl border border-gray-100 max-h-screen overflow-y-auto">

        <!-- Botón cerrar -->
        <button onclick="cerrarModalEdicion()"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition p-2">
            <i class="bi bi-x-lg text-xl"></i>
        </button>

        <!-- Header dinámico -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <div class="flex items-start gap-4">
                <div id="headerIcono"
                    class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-lg flex-shrink-0">
                    <i class="bi bi-pencil-square text-white text-xl"></i>
                </div>
                <div>
                    <h2 id="headerTitulo" class="text-2xl font-bold text-gray-900">Editar</h2>
                    <p id="headerSubtitulo" class="text-gray-500 text-sm mt-1">Actualiza la información</p>
                </div>
            </div>
        </div>

        <!-- Formulario dinámico -->
        <form id="formEdicion" method="POST" action="" class="space-y-6" novalidate>
            @csrf
            @method('PUT')

            <input type="hidden" id="itemId" name="id">

            <!-- Contenedor de campos dinámicos -->
            <div id="camposContenedor" class="space-y-4">
                <!-- Se genera dinámicamente -->
            </div>

            <!-- Botones -->
            <div class="flex gap-3 pt-6 border-t border-gray-200">
                <button type="button" onclick="cerrarModalEdicion()"
                    class="flex-1 px-4 py-3 text-sm font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 active:bg-gray-100 transition flex items-center justify-center gap-2">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="submit" id="btnGuardar" dusk="btn-guardar-edicion"
                    class="flex-1 px-4 py-3 text-sm font-semibold rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 active:from-blue-700 active:to-blue-800 transition shadow-md flex items-center justify-center gap-2">
                    <i class="bi bi-check-circle"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function mostrarAlertaCodigoInvalidoEdicion(mensaje) {
        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Código inválido',
                text: mensaje,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        alert(mensaje);
    }

    function mostrarToastCodigoEdicion(mensaje) {
        if (!window.Swal) {
            return;
        }

        Swal.fire({
            icon: 'info',
            title: mensaje,
            toast: true,
            position: 'top-end',
            timer: 1800,
            showConfirmButton: false
        });
    }

    function mostrarToastNombreEdicion(mensaje) {
        if (!window.Swal) {
            return;
        }

        Swal.fire({
            icon: 'info',
            title: mensaje,
            toast: true,
            position: 'top-end',
            timer: 1800,
            showConfirmButton: false
        });
    }

    const ultimoToastEnVivoEdicion = {};

    function mostrarToastEnVivoEdicion(clave, mensaje) {
        if (!window.Swal) {
            return;
        }

        const ahora = Date.now();
        if (ultimoToastEnVivoEdicion[clave] && (ahora - ultimoToastEnVivoEdicion[clave]) < 1200) {
            return;
        }
        ultimoToastEnVivoEdicion[clave] = ahora;

        Swal.fire({
            icon: 'info',
            title: mensaje,
            toast: true,
            position: 'top-end',
            timer: 1600,
            showConfirmButton: false
        });
    }

    function mostrarAlertaValidacionEdicion(mensaje) {
        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Validación requerida',
                text: mensaje,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        alert(mensaje);
    }

    function limpiarNombreEdicion(valor) {
        return valor
            .replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]/g, '')
            .replace(/\s{2,}/g, ' ');
    }

    function limpiarSoloNumerosEdicion(valor) {
        return (valor || '').replace(/\D/g, '');
    }

    function limpiarTelefonoEdicion(valor) {
        return (valor || '').replace(/\D/g, '');
    }

    function limpiarCodigoAlfa2Edicion(valor) {
        return (valor || '').replace(/[^A-Za-z]/g, '').toUpperCase().slice(0, 2);
    }

    function limpiarTextoBasicoEdicion(valor) {
        return (valor || '').replace(/\s{2,}/g, ' ').trim();
    }

    function configurarValidacionesCamposEdicion(contenedor) {
        const inputNombre = contenedor.querySelector('input[name="nombre"]');
        if (inputNombre) {
            inputNombre.setAttribute('pattern', '^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\\s]+$');
            inputNombre.setAttribute('maxlength', '100');
            inputNombre.setAttribute('title', 'Solo se permiten letras y espacios.');

            inputNombre.addEventListener('input', function () {
                const valorOriginal = this.value;
                const valorLimpio = limpiarNombreEdicion(valorOriginal);

                if (valorOriginal !== valorLimpio) {
                    this.value = valorLimpio;
                    mostrarToastNombreEdicion('El nombre solo permite letras y espacios.');
                }
            });

            inputNombre.addEventListener('blur', function () {
                this.value = limpiarNombreEdicion(this.value).trim();
            });
        }

        const inputCodigoAlfa2 = contenedor.querySelector('input[name="codigo_alfa2"]');
        if (inputCodigoAlfa2) {
            inputCodigoAlfa2.setAttribute('pattern', '^[A-Za-z]{2}$');
            inputCodigoAlfa2.setAttribute('maxlength', '2');
            inputCodigoAlfa2.setAttribute('title', 'Debe contener exactamente 2 letras.');

            inputCodigoAlfa2.addEventListener('input', function () {
                this.value = limpiarCodigoAlfa2Edicion(this.value);
            });
        }

        const inputNit = contenedor.querySelector('input[name="nit"]');
        if (inputNit) {
            inputNit.setAttribute('inputmode', 'numeric');
            inputNit.setAttribute('pattern', '^[0-9]{6,15}$');
            inputNit.setAttribute('maxlength', '15');
            inputNit.setAttribute('title', 'El NIT debe contener solo números (6 a 15 dígitos).');

            inputNit.addEventListener('input', function () {
                this.value = limpiarSoloNumerosEdicion(this.value).slice(0, 15);
            });
        }

        const inputCodigoEntidad = contenedor.querySelector('input[name="id_eps"], input[name="id_arl"], input[name="id_banco"]');
        if (inputCodigoEntidad) {
            inputCodigoEntidad.setAttribute('inputmode', 'numeric');
            const esBanco = inputCodigoEntidad.name === 'id_banco';
            inputCodigoEntidad.setAttribute('pattern', esBanco ? '^[0-9]{1,11}$' : '^[0-9]{6,15}$');
            inputCodigoEntidad.setAttribute('maxlength', esBanco ? '11' : '15');
            inputCodigoEntidad.setAttribute('title', esBanco
                ? 'El código debe contener solo números (máximo 11 dígitos).'
                : 'El código debe contener solo números (6 a 15 dígitos).');

            inputCodigoEntidad.addEventListener('input', function () {
                const valorOriginal = this.value;
                const valorLimpio = limpiarSoloNumerosEdicion(this.value).slice(0, esBanco ? 11 : 15);
                this.value = valorLimpio;

                if (valorOriginal !== valorLimpio) {
                    mostrarToastEnVivoEdicion(
                        this.name,
                        esBanco
                            ? 'Código banco: solo números y máximo 11 dígitos.'
                            : 'Código: solo números (6 a 15 dígitos).'
                    );
                }
            });
        }

        const inputDocRepresentante = contenedor.querySelector('input[name="doc_representante"]');
        if (inputDocRepresentante) {
            inputDocRepresentante.setAttribute('inputmode', 'numeric');
            inputDocRepresentante.setAttribute('pattern', '^[0-9]{6,15}$');
            inputDocRepresentante.setAttribute('maxlength', '15');

            inputDocRepresentante.addEventListener('input', function () {
                this.value = limpiarSoloNumerosEdicion(this.value).slice(0, 15);
            });
        }

        const inputTelefono = contenedor.querySelector('input[name="telefono"]');
        if (inputTelefono) {
            inputTelefono.setAttribute('inputmode', 'numeric');
            inputTelefono.setAttribute('pattern', '^[0-9]{1,11}$');
            inputTelefono.setAttribute('maxlength', '11');
            inputTelefono.setAttribute('title', 'El teléfono solo permite números y máximo 11 dígitos.');

            inputTelefono.addEventListener('input', function () {
                const valorOriginal = this.value;
                const valorLimpio = limpiarTelefonoEdicion(this.value).slice(0, 11);
                this.value = valorLimpio;

                if (valorOriginal !== valorLimpio) {
                    mostrarToastEnVivoEdicion('telefono', 'Teléfono: solo números y máximo 11 dígitos.');
                }

                if (valorOriginal.length > 11) {
                    mostrarToastEnVivoEdicion('telefono-max', 'Teléfono: máximo 11 dígitos.');
                }
            });

            inputTelefono.addEventListener('blur', function () {
                this.value = limpiarTelefonoEdicion(this.value).slice(0, 11);
            });
        }

        const inputRazonSocial = contenedor.querySelector('input[name="razon_social"]');
        if (inputRazonSocial) {
            inputRazonSocial.setAttribute('maxlength', '120');
            inputRazonSocial.addEventListener('blur', function () {
                this.value = limpiarTextoBasicoEdicion(this.value);
            });
        }

        const inputDireccion = contenedor.querySelector('input[name="direccion"]');
        if (inputDireccion) {
            inputDireccion.setAttribute('maxlength', '120');
            inputDireccion.addEventListener('blur', function () {
                this.value = limpiarTextoBasicoEdicion(this.value);
            });
        }

        const inputCorreo = contenedor.querySelector('input[name="correo"]');
        if (inputCorreo) {
            inputCorreo.setAttribute('maxlength', '120');
            inputCorreo.addEventListener('blur', function () {
                this.value = (this.value || '').trim().toUpperCase();
            });
        }

        const textareaDescripcion = contenedor.querySelector('textarea[name="descripcion"]');
        if (textareaDescripcion) {
            textareaDescripcion.setAttribute('maxlength', '255');
            textareaDescripcion.addEventListener('blur', function () {
                this.value = limpiarTextoBasicoEdicion(this.value);
            });
        }
    }

    function validarCampoCodigoEdicion(inputCodigo, mostrarAlerta = false) {
        if (!inputCodigo) {
            return true;
        }

        const valor = (inputCodigo.value || '').trim();

        if (!/^\d*$/.test(valor)) {
            if (mostrarAlerta) {
                mostrarAlertaCodigoInvalidoEdicion('El código no puede tener letras ni caracteres especiales.');
            }
            inputCodigo.value = valor.replace(/\D/g, '');
            return false;
        }

        if (valor.length > 0 && valor.length !== 8) {
            if (mostrarAlerta) {
                mostrarAlertaCodigoInvalidoEdicion('El código debe tener exactamente 8 dígitos.');
            }
            return false;
        }

        return true;
    }

    let configuracionEdicion = {
        tipo: null,
        campos: [],
        colores: {
            icono: 'from-blue-400 to-blue-600',
            boton: 'from-blue-500 to-blue-600'
        }
    };

    function abrirEdicionModal(tipo, id, datos, configuracion) {
        abrirEdicionGenerico(tipo, id, datos, configuracion);
    }

    function abrirEdicionGenerico(tipo, id, datos, configuracion) {
        if (typeof configuracion === 'string') {
            configuracion = JSON.parse(configuracion);
        }

        configuracionEdicion = configuracion;

        const modal = document.getElementById('modalEdicion');
        const headerIcono = document.getElementById('headerIcono');
        const headerTitulo = document.getElementById('headerTitulo');
        const headerSubtitulo = document.getElementById('headerSubtitulo');
        const itemIdInput = document.getElementById('itemId');
        const contenedor = document.getElementById('camposContenedor');
        const formEdicion = document.getElementById('formEdicion');
        const btnGuardar = document.getElementById('btnGuardar');

        // Configurar header
        headerIcono.className = `w-14 h-14 rounded-xl bg-gradient-to-br ${configuracion.colores.icono} flex items-center justify-center shadow-lg flex-shrink-0`;
        headerTitulo.textContent = `Editar ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`;
        headerSubtitulo.textContent = `Modifica los datos de ${tipo.toLowerCase()}`;

        // Configurar botón guardar
        btnGuardar.className = `flex-1 px-4 py-3 text-sm font-semibold rounded-lg bg-gradient-to-r ${configuracion.colores.boton} text-white hover:opacity-90 transition shadow-md flex items-center justify-center gap-2`;

        // Configurar ID
        itemIdInput.value = id;
        itemIdInput.name = 'id';

        // Agregar tipo a los datos que se enviarán
        let datosConTipo = { ...datos, tipo: tipo };

        // Limpiar y generar campos
        contenedor.innerHTML = '';

        configuracion.campos.forEach(campo => {
            const valor = datosConTipo[campo.clave] || '';
            let html = '';

            if (campo.tipo === 'text' || campo.tipo === 'email') {
                html = `
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                        <i class="bi ${campo.icono} mr-2"></i>${campo.label}
                    </label>
                    <input 
                        type="${campo.tipo}" 
                        name="${campo.clave}" 
                        value="${valor}"
                        placeholder="${campo.placeholder || ''}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50 hover:bg-white" 
                        ${campo.requerido ? 'required' : ''}
                    >
                </div>
            `;
            } else if (campo.tipo === 'select') {
                let options = '<option value="">Selecciona una opción</option>';
                if (campo.opciones) {
                    options += campo.opciones.map(opt =>
                        `<option value="${opt.id}" ${opt.id == valor ? 'selected' : ''}>${opt.nombre}</option>`
                    ).join('');
                }
                html = `
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                        <i class="bi ${campo.icono} mr-2"></i>${campo.label}
                    </label>
                    <select 
                        name="${campo.clave}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50 hover:bg-white"
                        ${campo.requerido ? 'required' : ''}
                    >
                        ${options}
                    </select>
                </div>
            `;
            } else if (campo.tipo === 'textarea') {
                html = `
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                        <i class="bi ${campo.icono} mr-2"></i>${campo.label}
                    </label>
                    <textarea 
                        name="${campo.clave}"
                        placeholder="${campo.placeholder || ''}"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50 hover:bg-white resize-none"
                        ${campo.requerido ? 'required' : ''}
                    >${valor}</textarea>
                </div>
            `;
            }

            contenedor.innerHTML += html;
        });

        // Agregar input hidden para tipo
        contenedor.innerHTML += `<input type="hidden" name="tipo" value="${tipo}">`;

        const inputCodigo = contenedor.querySelector('input[name="codigo"]');
        if (inputCodigo) {
            inputCodigo.setAttribute('inputmode', 'numeric');
            inputCodigo.setAttribute('minlength', '8');
            inputCodigo.setAttribute('maxlength', '8');
            inputCodigo.setAttribute('pattern', '[0-9]{8}');

            inputCodigo.addEventListener('input', function () {
                const valorOriginal = this.value;
                const soloNumeros = valorOriginal.replace(/\D/g, '').slice(0, 8);

                if (valorOriginal !== soloNumeros) {
                    this.value = soloNumeros;
                    mostrarToastCodigoEdicion('Solo números y máximo 8 dígitos.');
                }
            });

            inputCodigo.addEventListener('blur', function () {
                validarCampoCodigoEdicion(this, true);
            });
        }

        configurarValidacionesCamposEdicion(contenedor);

        // Configurar formulario
        formEdicion.action = configuracion.ruta.replace(':id', id);

        // Cerrar modal anterior
        document.getElementById('modalListadoEdicion').classList.add('hidden');
        document.getElementById('modalListadoEdicion').classList.remove('flex');

        // Abrir modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function cerrarModalEdicion() {
        const modal = document.getElementById('modalEdicion');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function obtenerEtiquetaCampoEdicion(input) {
        if (!input || !input.name) {
            return 'Este campo';
        }

        const etiqueta = input.closest('div')?.querySelector('label');
        if (etiqueta) {
            return etiqueta.textContent.replace('*', '').trim();
        }

        return input.name;
    }

    function validarCamposCriticosEdicion(formulario) {
        const inputTelefono = formulario.querySelector('input[name="telefono"]');
        if (inputTelefono) {
            const valorTelefono = (inputTelefono.value || '').trim();
            if (valorTelefono && !/^\d{1,11}$/.test(valorTelefono)) {
                mostrarAlertaValidacionEdicion('El campo Teléfono solo permite números y máximo 11 dígitos.');
                inputTelefono.focus();
                return false;
            }
        }

        const inputCodigo8 = formulario.querySelector('input[name="codigo"]');
        if (inputCodigo8) {
            const valorCodigo = (inputCodigo8.value || '').trim();
            if (valorCodigo && !/^\d{8}$/.test(valorCodigo)) {
                mostrarAlertaValidacionEdicion('El campo Código debe tener exactamente 8 dígitos numéricos.');
                inputCodigo8.focus();
                return false;
            }
        }

        const inputCodigoEntidad = formulario.querySelector('input[name="id_banco"], input[name="id_eps"], input[name="id_arl"]');
        if (inputCodigoEntidad) {
            const valor = (inputCodigoEntidad.value || '').trim();
            const esBanco = inputCodigoEntidad.name === 'id_banco';
            const patron = esBanco ? /^\d{1,11}$/ : /^\d{6,15}$/;

            if (valor && !patron.test(valor)) {
                mostrarAlertaValidacionEdicion(esBanco
                    ? 'El código del banco debe tener solo números y máximo 11 dígitos.'
                    : 'El código debe tener solo números entre 6 y 15 dígitos.');
                inputCodigoEntidad.focus();
                return false;
            }
        }

        return true;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const formEdicion = document.getElementById('formEdicion');
        if (formEdicion) {
            formEdicion.addEventListener('submit', function (e) {
                const inputCodigo = this.querySelector('input[name="codigo"]');
                if (!validarCampoCodigoEdicion(inputCodigo, true)) {
                    e.preventDefault();
                    inputCodigo?.focus();
                    return;
                }

                if (!validarCamposCriticosEdicion(this)) {
                    e.preventDefault();
                    return;
                }

                if (!this.checkValidity()) {
                    e.preventDefault();
                    const primerInvalido = this.querySelector(':invalid');
                    const nombreCampo = obtenerEtiquetaCampoEdicion(primerInvalido);
                    mostrarAlertaValidacionEdicion(`Revisa el campo ${nombreCampo}.`);
                    this.reportValidity();
                    primerInvalido?.focus();
                    return;
                }

                e.preventDefault();
                this.submit();
            });
        }
    });
</script>