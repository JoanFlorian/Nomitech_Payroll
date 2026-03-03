@extends('layouts.superadmin')

@section('content')

    <div class="p-6 bg-gray-50">

        <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg border p-8 mb-0">

            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ $empresa->razon_social }}
                    </h2>
                    <p class="text-sm text-green-600 mt-1">
                        Empresa Cliente Verificada
                    </p>
                </div>

                @php
                    $estado = optional($empresa->licencia)->estado ?? 'pendiente_pago';
                @endphp

                <span class="px-3 py-1 rounded-full text-xs font-semibold
                        {{ $estado == 'activa' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $estado == 'por_vencer' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $estado == 'vencida' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $estado == 'pendiente_pago' ? 'bg-blue-100 text-blue-700' : '' }}
                    ">
                    {{ strtoupper(str_replace('_', ' ', $estado)) }}
                </span>
            </div>

            <!-- Grid principal -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Card: Información Legal -->
                <div class="col-span-2 bg-gray-50 rounded-xl p-6 border">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="bi bi-shield-check text-blue-600"></i>
                        Información Legal
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">Representante</p>
                            <p class="font-medium">
                                {{ optional($empresa->representante)->primer_nombre ?? 'No asignado' }}
                            </p>
                        </div>

                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">NIT</p>
                            <p class="font-medium">{{ $empresa->nit }}</p>
                        </div>

                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">Dirección</p>
                            <p class="font-medium break-words" title="{{ $empresa->direccion }}">
                                {{ $empresa->direccion }}
                            </p>
                        </div>

                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">Ciudad</p>
                            <p class="font-medium">{{ optional($empresa->ciudad)->nombre ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card: Estado de Membresía -->
                <div class="bg-gray-50 rounded-xl p-6 border">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="bi bi-star-fill text-yellow-500"></i>
                        Estado de Membresía
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">Plan</p>
                            <p class="font-medium">
                                {{ optional(optional($empresa->licencia)->plan)->nombre ?? 'Demo' }}
                            </p>
                        </div>

                        <div class="bg-white p-4 rounded-lg border">
                            <p class="text-gray-500">Fecha de vencimiento</p>
                            <p class="font-medium">
                                {{ optional($empresa->licencia)->fecha_fin ?? 'No aplica' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8">
                <button type="button" data-modal-open="modalEditar"
                    class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                    Editar Datos
                </button>

                <a href="{{ route('superadmin.empresas.certificado-excel', $empresa) }}" class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition shadow">
                    Descargar Certificado
                </a>
            </div>
        </div>
    </div>

    
<div id="modalEditar" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 md:p-6 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-editar-titulo">
    <div class="bg-white rounded-2xl w-full max-w-3xl mx-auto my-auto shadow-2xl flex flex-col max-h-[calc(100vh-2rem)] md:max-h-[calc(100vh-3rem)] border border-gray-100">

        <!-- Header -->
        <div class="px-6 py-4 border-b border-blue-100 flex items-center justify-between bg-blue-50/70">
            <div>
                <h3 id="modal-editar-titulo" class="font-semibold text-lg text-gray-900">Editar datos de la empresa</h3>
                <p class="text-sm text-gray-500">Actualiza la información permitida</p>
            </div>
            <button type="button" data-modal-close="modalEditar"
                aria-label="Cerrar formulario de edición"
                class="text-gray-400 hover:text-gray-600">
                ✕
            </button>
        </div>

      
        <form id="formEditarEmpresa" method="POST" action="{{ route('superadmin.empresas.update', $empresa->id_empresa) }}" data-validate-correo-url="{{ route('superadmin.empresas.validar-correo', $empresa->id_empresa) }}" class="flex flex-col min-h-0" novalidate>
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="mx-6 mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Contenedor con scroll interno -->
            <div class="px-6 pt-4">
                <p class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 text-xs font-medium px-3 py-1">Campos obligatorios *</p>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 overflow-y-auto flex-1 min-h-0 bg-white">

                <div class="md:col-span-2">
                    <h4 class="text-sm font-semibold text-gray-800">Información de contacto</h4>
                    <div class="h-px bg-gray-100 mt-2"></div>
                </div>

                <!-- Dirección -->
                <div>
                    <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input id="direccion" type="text" name="direccion" value="{{ old('direccion', $empresa->direccion) }}"
                        autocomplete="street-address" placeholder="Ej: Carrera 3 # 15 - 90"
                        pattern="(?=.*[A-Za-z])(?=.*([Cc]alle|[Cc]arrera|[Cc]ra\.?|[Cc]l\.?|[Aa]v\.?|[Aa]venida|[Tt]ransversal|[Dd]iagonal|#|[Nn]o\.?)).+"
                        title="La dirección debe tener formato válido (Calle, Carrera, Av, #, etc)."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 overflow-x-auto whitespace-nowrap focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('direccion') border-red-500 @enderror"
                        required maxlength="150">
                    <p id="direccionRealtimeError" class="text-xs text-red-500 mt-1 min-h-[1rem]"></p>
                    @error('direccion')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input id="telefono" type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                        inputmode="numeric" pattern="^[0-9]{1,11}$" maxlength="11" autocomplete="tel-national"
                        placeholder="Ej: 3115990394"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('telefono') border-red-500 @enderror"
                        required>
                    <p id="telefonoRealtimeError" class="text-xs text-red-500 mt-1 min-h-[1rem]"></p>
                    @error('telefono')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Correo -->
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                    <input id="correo" type="email" name="correo" value="{{ old('correo', $empresa->correo) }}"
                        autocomplete="email" placeholder="Ej: correo@empresa.com"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('correo') border-red-500 @enderror"
                        maxlength="100" required>
                    <p id="correoRealtimeError" class="text-xs text-red-500 mt-1 min-h-[1rem]"></p>
                    @error('correo')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                             <div>
                 <label for="id_ciudad" class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>

               <select id="id_ciudad" name="id_ciudad"
           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
        @error('id_ciudad') border-red-500 @enderror" required>

             <option value="">Seleccione una ciudad</option>

                      @foreach($ciudades as $ciudad)
                         <option value="{{ $ciudad->id_ciudad }}"
                             {{ old('id_ciudad', $empresa->id_ciudad) == $ciudad->id_ciudad ? 'selected' : '' }}>
                {{ $ciudad->nombre }}
            </option>
        @endforeach
                    </select>

    {{-- Espacio reservado para el mensaje (no rompe el diseño) --}}
    <p id="idCiudadRealtimeError" class="mt-1 min-h-[1rem] text-sm text-red-600">
        @error('id_ciudad') {{ $message }} @enderror
    </p>
</div>


                <div class="md:col-span-2 mt-1">
                    <h4 class="text-sm font-semibold text-gray-800">Representante legal</h4>
                    <div class="h-px bg-gray-100 mt-2"></div>
                </div>


                <!-- Documento representante -->
                <div>
                    <label for="doc_representante" class="block text-sm font-medium text-gray-700 mb-1">Documento del representante</label>
                    <input id="doc_representante" type="text" name="doc_representante"
                        value="{{ old('doc_representante', $empresa->doc_representante) }}"
                        inputmode="numeric" pattern="[0-9]{7,12}" minlength="7" maxlength="12" autocomplete="off"
                        placeholder="Ej: 1030280138"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('doc_representante') border-red-500 @enderror"
                        required>
                    <p id="docRealtimeError" class="text-xs text-red-500 mt-1 min-h-[1rem]"></p>
                    @error('doc_representante')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Primer nombre -->
                <div>
                    <label for="primer_nombre" class="block text-sm font-medium text-gray-700 mb-1">Primer nombre</label>
                    <input id="primer_nombre" type="text" name="primer_nombre"
                        value="{{ old('primer_nombre', optional($empresa->representante)->primer_nombre) }}"
                        autocomplete="given-name" autocapitalize="words"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('primer_nombre') border-red-500 @enderror"
                        required maxlength="60">
                    <p id="primerNombreRealtimeError" class="text-xs text-red-500 mt-1 min-h-[1rem]"></p>
                    @error('primer_nombre')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Segundo nombre -->
                <div>
                    <label for="segundo_nombre" class="block text-sm font-medium text-gray-700 mb-1">Segundo nombre</label>
                    <input id="segundo_nombre" type="text" name="segundo_nombre"
                        value="{{ old('segundo_nombre', optional($empresa->representante)->segundo_nombre) }}"
                        autocomplete="additional-name" autocapitalize="words"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('segundo_nombre') border-red-500 @enderror"
                        maxlength="60">
                    <p id="segundoNombreRealtimeError" class="text-xs text-red-500 mt-1 min-h-[1rem]"></p>
                    @error('segundo_nombre')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Primer apellido -->
                <div>
                    <label for="primer_apellido" class="block text-sm font-medium text-gray-700 mb-1">Primer apellido</label>
                    <input id="primer_apellido" type="text" name="primer_apellido"
                        value="{{ old('primer_apellido', optional($empresa->representante)->primer_apellido) }}"
                        autocomplete="family-name" autocapitalize="words"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('primer_apellido') border-red-500 @enderror"
                        required maxlength="60">
                    <p id="primerApellidoRealtimeError" class="text-xs text-red-500 mt-1 min-h-[1rem]"></p>
                    @error('primer_apellido')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Segundo apellido -->
                <div>
                    <label for="segundo_apellido" class="block text-sm font-medium text-gray-700 mb-1">Segundo apellido</label>
                    <input id="segundo_apellido" type="text" name="segundo_apellido"
                        value="{{ old('segundo_apellido', optional($empresa->representante)->segundo_apellido) }}"
                        autocomplete="off" autocapitalize="words"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none @error('segundo_apellido') border-red-500 @enderror"
                        maxlength="60">
                    <p id="segundoApellidoRealtimeError" class="text-xs text-red-500 mt-1 min-h-[1rem]"></p>
                    @error('segundo_apellido')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t bg-gray-50/95 backdrop-blur-sm flex justify-end gap-3 sticky bottom-0">
                <button type="button"
                    data-modal-close="modalEditar"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Cancelar
                </button>

                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalEditar');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }
    });
</script>
@endif

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalEditar');
        const form = document.getElementById('formEditarEmpresa');
        const telefonoInput = document.getElementById('telefono');
        const telefonoRealtimeError = document.getElementById('telefonoRealtimeError');
        const docInput = document.getElementById('doc_representante');
        const docRealtimeError = document.getElementById('docRealtimeError');
        const direccionInput = document.getElementById('direccion');
        const direccionRealtimeError = document.getElementById('direccionRealtimeError');
        const correoInput = document.getElementById('correo');
        const correoRealtimeError = document.getElementById('correoRealtimeError');
        const ciudadInput = document.getElementById('id_ciudad');
        const ciudadRealtimeError = document.getElementById('idCiudadRealtimeError');
        const primerNombreInput = document.getElementById('primer_nombre');
        const primerNombreRealtimeError = document.getElementById('primerNombreRealtimeError');
        const segundoNombreInput = document.getElementById('segundo_nombre');
        const segundoNombreRealtimeError = document.getElementById('segundoNombreRealtimeError');
        const primerApellidoInput = document.getElementById('primer_apellido');
        const primerApellidoRealtimeError = document.getElementById('primerApellidoRealtimeError');
        const segundoApellidoInput = document.getElementById('segundo_apellido');
        const segundoApellidoRealtimeError = document.getElementById('segundoApellidoRealtimeError');
        const correoValidationUrl = form?.dataset.validateCorreoUrl || '';
        let correoDebounceTimer = null;
        let correoValidationRequest = null;
        let ultimoCorreoValidado = null;
        let ultimoCorreoDisponible = null;
        let correoValidationToken = 0;
        const camposNombre = [
            document.getElementById('primer_nombre'),
            document.getElementById('segundo_nombre'),
            document.getElementById('primer_apellido'),
            document.getElementById('segundo_apellido')
        ].filter(Boolean);
        if (!modal) return;

        const normalizarNombreTexto = (valor) => {
            const sinCaracteresInvalidos = valor.replace(/[^\p{L}\s]/gu, '');
            const conEspaciosLimpios = sinCaracteresInvalidos.replace(/\s{2,}/g, ' ').replace(/^\s+/, '');

            return conEspaciosLimpios.replace(/(^|\s)(\p{L})(\p{L}*)/gu, (_, separador, inicial, resto) => {
                return `${separador}${inicial.toLocaleUpperCase('es-CO')}${resto.toLocaleLowerCase('es-CO')}`;
            });
        };

        const setFieldError = (input, errorElement, message = '') => {
            if (!input || !errorElement) return;
            errorElement.textContent = message;
            if (message) {
                input.classList.add('border-red-500');
            } else {
                input.classList.remove('border-red-500');
            }
        };

        const setTelefonoError = (message = '') => {
            if (!telefonoRealtimeError || !telefonoInput) return;
            telefonoRealtimeError.textContent = message;
            if (message) {
                telefonoInput.classList.add('border-red-500');
            } else {
                telefonoInput.classList.remove('border-red-500');
            }
        };

        const validarTelefonoEnVivo = () => {
            if (!telefonoInput) return false;
            const valor = telefonoInput.value.trim();

            if (!valor) {
                setTelefonoError('El teléfono es obligatorio.');
                return false;
            }

            if (valor.length > 11) {
                setTelefonoError('El teléfono debe tener máximo 11 dígitos.');
                return false;
            }

            setTelefonoError('');
            return true;
        };

        const setDocError = (message = '') => {
            if (!docRealtimeError || !docInput) return;
            docRealtimeError.textContent = message;
            if (message) {
                docInput.classList.add('border-red-500');
            } else {
                docInput.classList.remove('border-red-500');
            }
        };

        const validarDocEnVivo = () => {
            if (!docInput) return false;
            const valor = docInput.value.trim();

            if (!valor) {
                setDocError('El documento del representante es obligatorio.');
                return false;
            }

            if (valor.length < 7) {
                setDocError(`Faltan ${7 - valor.length} dígitos.`);
                return false;
            }

            if (valor.length > 12) {
                setDocError('El documento debe tener máximo 12 dígitos.');
                return false;
            }

            setDocError('');
            return true;
        };

        const validarDireccionEnVivo = () => {
            if (!direccionInput) return true;
            const valor = direccionInput.value.trim();

            if (!valor) {
                setFieldError(direccionInput, direccionRealtimeError, 'La dirección es obligatoria.');
                return false;
            }

            if (valor.length > 150) {
                setFieldError(direccionInput, direccionRealtimeError, 'La dirección debe tener máximo 150 caracteres.');
                return false;
            }

            const regexDireccion = /^(?=.*[A-Za-z])(?=.*(calle|carrera|cra\.?|cl\.?|av\.?|avenida|transversal|diagonal|#|no\.?)).+$/i;
            if (!regexDireccion.test(valor)) {
                setFieldError(direccionInput, direccionRealtimeError, 'La dirección debe tener formato válido (Calle, Carrera, Av, #, etc).');
                return false;
            }

            setFieldError(direccionInput, direccionRealtimeError, '');
            return true;
        };

        const validarCorreoEnVivo = () => {
            if (!correoInput) return true;
            const valor = correoInput.value.trim();

            if (!valor) {
                setFieldError(correoInput, correoRealtimeError, 'El correo es obligatorio.');
                return false;
            }

            if (valor.length > 100) {
                setFieldError(correoInput, correoRealtimeError, 'El correo debe tener máximo 100 caracteres.');
                return false;
            }

            const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regexCorreo.test(valor)) {
                setFieldError(correoInput, correoRealtimeError, 'El correo no tiene un formato válido.');
                return false;
            }

            setFieldError(correoInput, correoRealtimeError, '');
            return true;
        };

        const validarCorreoUnicoEnVivo = async (force = false) => {
            if (!correoInput || !correoValidationUrl) {
                return true;
            }

            if (!validarCorreoEnVivo()) {
                return false;
            }

            const correo = correoInput.value.trim();

            if (!force && correo === ultimoCorreoValidado && ultimoCorreoDisponible !== null) {
                return ultimoCorreoDisponible;
            }

            if (correoValidationRequest) {
                correoValidationRequest.abort();
            }

            const tokenActual = ++correoValidationToken;
            correoValidationRequest = new AbortController();

            try {
                const respuesta = await fetch(`${correoValidationUrl}?correo=${encodeURIComponent(correo)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    signal: correoValidationRequest.signal
                });

                if (tokenActual !== correoValidationToken) {
                    return false;
                }

                const data = await respuesta.json();
                const disponible = Boolean(data?.available);
                const mensaje = data?.message || '';

                ultimoCorreoValidado = correo;
                ultimoCorreoDisponible = disponible;

                if (!disponible) {
                    setFieldError(correoInput, correoRealtimeError, mensaje || 'Este correo ya está registrado en otra empresa.');
                    return false;
                }

                setFieldError(correoInput, correoRealtimeError, '');
                return true;
            } catch (error) {
                if (error.name === 'AbortError') {
                    return false;
                }

                setFieldError(correoInput, correoRealtimeError, 'No se pudo validar el correo en este momento.');
                return false;
            }
        };

        const validarCiudadEnVivo = () => {
            if (!ciudadInput) return true;
            const valor = ciudadInput.value;

            if (!valor) {
                setFieldError(ciudadInput, ciudadRealtimeError, 'Debes seleccionar una ciudad.');
                return false;
            }

            setFieldError(ciudadInput, ciudadRealtimeError, '');
            return true;
        };

        const validarNombreEnVivo = (input, errorElement, required = false) => {
            if (!input || !errorElement) return true;
            const valor = (input.value || '').trim();

            if (!valor) {
                if (required) {
                    const etiqueta = input.previousElementSibling?.textContent?.trim() || 'Este campo';
                    setFieldError(input, errorElement, `${etiqueta} es obligatorio.`);
                    return false;
                }

                setFieldError(input, errorElement, '');
                return true;
            }

            if (valor.length > 60) {
                setFieldError(input, errorElement, 'Este campo debe tener máximo 60 caracteres.');
                return false;
            }

            const soloLetras = /^[\p{L}\s]+$/u.test(valor);
            if (!soloLetras) {
                setFieldError(input, errorElement, 'Solo se permiten letras.');
                return false;
            }

            setFieldError(input, errorElement, '');
            return true;
        };

        if (telefonoInput) {
            telefonoInput.addEventListener('keydown', function (event) {
                const teclasPermitidas = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
                const esNumero = /^[0-9]$/.test(event.key);

                if (!esNumero && !teclasPermitidas.includes(event.key)) {
                    event.preventDefault();
                }
            });

            telefonoInput.addEventListener('input', function () {
                const valorOriginal = telefonoInput.value;
                const soloNumeros = valorOriginal.replace(/\D/g, '').slice(0, 11);

                if (valorOriginal !== soloNumeros) {
                    telefonoInput.value = soloNumeros;
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Solo números',
                            text: 'En teléfono solo se permiten dígitos.',
                            timer: 1200,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                }

                validarTelefonoEnVivo();
            });

            telefonoInput.addEventListener('blur', validarTelefonoEnVivo);
        }

        if (docInput) {
            docInput.addEventListener('keydown', function (event) {
                const teclasPermitidas = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
                const esNumero = /^[0-9]$/.test(event.key);

                if (!esNumero && !teclasPermitidas.includes(event.key)) {
                    event.preventDefault();
                }
            });

            docInput.addEventListener('input', function () {
                const valorOriginal = docInput.value;
                const soloNumeros = valorOriginal.replace(/\D/g, '').slice(0, 12);

                if (valorOriginal !== soloNumeros) {
                    docInput.value = soloNumeros;
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Solo números',
                            text: 'En documento solo se permiten dígitos.',
                            timer: 1200,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                }

                validarDocEnVivo();
            });

            docInput.addEventListener('blur', validarDocEnVivo);
        }

        if (direccionInput) {
            direccionInput.addEventListener('input', validarDireccionEnVivo);
            direccionInput.addEventListener('blur', validarDireccionEnVivo);
        }

        if (correoInput) {
            correoInput.addEventListener('input', function () {
                ultimoCorreoValidado = null;
                ultimoCorreoDisponible = null;

                const formatoValido = validarCorreoEnVivo();

                if (correoDebounceTimer) {
                    clearTimeout(correoDebounceTimer);
                }

                if (!formatoValido) {
                    return;
                }

                correoDebounceTimer = setTimeout(() => {
                    validarCorreoUnicoEnVivo();
                }, 450);
            });

            correoInput.addEventListener('blur', async function () {
                if (correoDebounceTimer) {
                    clearTimeout(correoDebounceTimer);
                }

                await validarCorreoUnicoEnVivo(true);
            });
        }

        if (ciudadInput) {
            ciudadInput.addEventListener('change', validarCiudadEnVivo);
            ciudadInput.addEventListener('blur', validarCiudadEnVivo);
        }

        camposNombre.forEach((campo) => {
            campo.addEventListener('input', function () {
                const valorFormateado = normalizarNombreTexto(campo.value);
                if (campo.value !== valorFormateado) {
                    campo.value = valorFormateado;
                }

                if (campo === primerNombreInput) {
                    validarNombreEnVivo(primerNombreInput, primerNombreRealtimeError, true);
                }

                if (campo === segundoNombreInput) {
                    validarNombreEnVivo(segundoNombreInput, segundoNombreRealtimeError, false);
                }

                if (campo === primerApellidoInput) {
                    validarNombreEnVivo(primerApellidoInput, primerApellidoRealtimeError, true);
                }

                if (campo === segundoApellidoInput) {
                    validarNombreEnVivo(segundoApellidoInput, segundoApellidoRealtimeError, false);
                }
            });

            campo.addEventListener('blur', function () {
                if (campo === primerNombreInput) {
                    validarNombreEnVivo(primerNombreInput, primerNombreRealtimeError, true);
                }

                if (campo === segundoNombreInput) {
                    validarNombreEnVivo(segundoNombreInput, segundoNombreRealtimeError, false);
                }

                if (campo === primerApellidoInput) {
                    validarNombreEnVivo(primerApellidoInput, primerApellidoRealtimeError, true);
                }

                if (campo === segundoApellidoInput) {
                    validarNombreEnVivo(segundoApellidoInput, segundoApellidoRealtimeError, false);
                }
            });
        });

        const openModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('[data-modal-open="modalEditar"]').forEach((button) => {
            button.addEventListener('click', openModal);
        });

        document.querySelectorAll('[data-modal-close="modalEditar"]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        if (form) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const telefono = (telefonoInput?.value || '').trim();
                const documento = (docInput?.value || '').trim();

                const telefonoValido = /^\d{1,11}$/.test(telefono);
                const documentoValido = /^\d{7,12}$/.test(documento);
                const direccionValida = validarDireccionEnVivo();
                const correoValido = validarCorreoEnVivo();
                const ciudadValida = validarCiudadEnVivo();
                const primerNombreValido = validarNombreEnVivo(primerNombreInput, primerNombreRealtimeError, true);
                const segundoNombreValido = validarNombreEnVivo(segundoNombreInput, segundoNombreRealtimeError, false);
                const primerApellidoValido = validarNombreEnVivo(primerApellidoInput, primerApellidoRealtimeError, true);
                const segundoApellidoValido = validarNombreEnVivo(segundoApellidoInput, segundoApellidoRealtimeError, false);

                if (!direccionValida) {
                    direccionInput?.focus();
                    return;
                }

                if (!correoValido) {
                    correoInput?.focus();
                    return;
                }

                const correoUnicoValido = await validarCorreoUnicoEnVivo(true);
                if (!correoUnicoValido) {
                    correoInput?.focus();
                    return;
                }

                if (!ciudadValida) {
                    ciudadInput?.focus();
                    return;
                }

                if (!telefonoValido) {
                    validarTelefonoEnVivo();
                    telefonoInput?.focus();
                    return;
                }

                if (!documentoValido) {
                    validarDocEnVivo();
                    docInput?.focus();
                    return;
                }

                if (!primerNombreValido) {
                    primerNombreInput?.focus();
                    return;
                }

                if (!segundoNombreValido) {
                    segundoNombreInput?.focus();
                    return;
                }

                if (!primerApellidoValido) {
                    primerApellidoInput?.focus();
                    return;
                }

                if (!segundoApellidoValido) {
                    segundoApellidoInput?.focus();
                    return;
                }

                for (const campo of camposNombre) {
                    const valor = (campo.value || '').trim();

                    if (!valor) {
                        continue;
                    }

                    const soloLetras = /^[\p{L}\s]+$/u.test(valor);

                    if (!soloLetras) {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Formato inválido',
                                text: 'Nombres y apellidos solo permiten letras.',
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#2563eb'
                            });
                        }
                        campo.focus();
                        return;
                    }

                    campo.value = normalizarNombreTexto(valor).trim();
                }

                form.submit();
            });
        }
    });
</script>

@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Cambios exitosos',
            text: @json(session('success')),
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#2563eb'
        });
    });
</script>
@endif
@endsection