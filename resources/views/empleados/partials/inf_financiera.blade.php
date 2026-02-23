<h2 class="text-2xl font-bold text-gray-800 mb-6">
    Paso 3: Información Financiera y Seguridad Social
</h2>

<div class="mb-10">
    <div class="flex justify-between text-sm mb-2">
        <span class="text-gray-500">Información Personal</span>
        <span class="text-gray-500">Información Contractual y Laboral</span>
        <span class="font-semibold text-[rgb(16,185,129)] text-right">
            Información Financiera y Seguridad Social
        </span>
    </div>

    <div class="relative h-1 bg-gray-200 rounded-full">
        <div class="absolute h-1 bg-[rgb(16,185,129)] rounded-full w-full"></div>

        <div class="absolute -top-3 left-0 w-full flex justify-between">
            <div class="w-6 h-6 bg-[rgb(16,185,129)] text-white rounded-full flex items-center justify-center text-xs font-bold">
                1
            </div>

            <div class="w-6 h-6 bg-[rgb(16,185,129)] text-white rounded-full flex items-center justify-center text-xs font-bold">
                2
            </div>

            <div class="w-6 h-6 bg-[rgb(16,185,129)] text-white rounded-full flex items-center justify-center text-xs font-bold">
                3
            </div>
        </div>
    </div>
</div>

<form id="step3" novalidate>
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Forma de pago
            </label>
            <select
                class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="id_forma_pago"
                required>
                <option value="">Seleccionar...</option>
                @foreach ( $formapagos as $formapago )
                    <option value="{{ $formapago->id_forma_pago }}">{{ $formapago->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_forma_pago"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Método de pago
            </label>
            <select
                class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="id_metodo_pago"
                required>
                <option value="">Seleccionar...</option>
                @foreach ( $metodopago as $metopago )
                    <option value="{{ $metopago->id_metodo_pago }}">{{ $metopago->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_metodo_pago"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tipo de cuenta
            </label>
            <select
                class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="tipo_cuenta"
                required>
                <option value="">Seleccionar...</option>
                @foreach ( $tipocuenta as $tipcuenta )
                    <option value="{{ $tipcuenta->id_tipo_cuenta }}">{{ $tipcuenta->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="tipo_cuenta"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Número de cuenta
            </label>
            <input
                type="text"
                placeholder="Ej: 1234567890"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="numero_cuenta"
                required>
            <p class="error-message text-red-500 text-sm hidden" data-error="numero_cuenta"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                EPS
            </label>
            <select
                class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="id_eps"
                required>
                <option value="">Seleccionar...</option>
                @foreach ( $Eps as $eps )
                    <option value="{{ $eps->id_eps }}">{{ $eps->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_eps"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                AFP
            </label>
            <select
                class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="id_afp"
                required>
                <option value="">Seleccionar...</option>
                @foreach ( $Afp as $afp )
                    <option value="{{ $afp->id_afp }}">{{ $afp->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_afp"></p>
        </div>
    </div>

    <!-- BOTONES -->
    <div class="mt-10 flex justify-end">
        <button
            type="button"
            @click="previousStep()"
            class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition">
            Atrás
        </button>

        <button
            type="submit"
            class="ml-4 bg-[rgb(16,185,129)] text-white py-2 px-6 rounded-md hover:bg-[rgb(14,160,112)] transition">
            Finalizar
        </button>
    </div>
</form>


<script>
    $(document).ready(function () {
        // Función para validar el formulario de Step 3
        function validateStep3Form() {
            let isValid = true;
            const form = $('#step3');
            
            // Limpiar errores previos
            form.find('.error-message').addClass('hidden').text('');
            form.find('input, select').removeClass('border-red-500 is-invalid');
            
            // Validar campos required
            const requiredFields = {
                'id_forma_pago': 'La forma de pago es requerida',
                'id_metodo_pago': 'El método de pago es requerido',
                'tipo_cuenta': 'El tipo de cuenta es requerido',
                'numero_cuenta': 'El número de cuenta es requerido',
                'id_eps': 'La EPS es requerida',
                'id_afp': 'La AFP es requerida',
            };
            
            $.each(requiredFields, function(fieldName, errorMessage) {
                const field = form.find(`[name="${fieldName}"]`);
                const value = field.val();
                
                if (!value || value === '') {
                    isValid = false;
                    field.addClass('border-red-500 is-invalid');
                    form.find(`[data-error="${fieldName}"]`).removeClass('hidden').text(errorMessage);
                }
            });
            
            return isValid;
        }
        
        // Manejar el envío del formulario
        $('#step3').on('submit', function (e) {
            e.preventDefault();

            // Validar en cliente primero
            if (!validateStep3Form()) {
                return false;
            }

            let form = $(this);
            let url = "{{ route('employees.final') }}";
            let data = form.serialize();


            // Limpiar errores anteriores
            form.find('.error-message').addClass('hidden').text('');
            form.find('input, select').removeClass('border-red-500');

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.success === true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registro exitoso',
                            text: 'Empleado registrado correctamente',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            window.dispatchEvent(new CustomEvent('step3-success'));
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.errors?.general?.[0] || 'Ocurrió un error',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                },
                error: function (xhr) {
                    
                    // Limpiar errores anteriores
                    form.find('.error-message').addClass('hidden').text('');
                    form.find('input, select').removeClass('border-red-500 is-invalid');

                    if (xhr.status === 422) {
                        // Errores de validación
                        let errors = xhr.responseJSON?.errors || {};
                        $.each(errors, function (key, messages) {
                            let errorField = form.find(`[data-error="${key}"]`);
                            const errorMessage = Array.isArray(messages) ? messages[0] : messages;
                            if (errorField.length) {
                                errorField.removeClass('hidden').text(errorMessage);
                            }
                            form.find(`[name="${key}"]`).addClass('border-red-500 is-invalid');
                        });

                        const generalError = xhr.responseJSON?.errors?.general?.[0];
                        if (generalError) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al registrar',
                                text: generalError,
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    } else if (xhr.status === 400) {
                        const sessionMessage = xhr.responseJSON?.errors?.general?.[0]
                            || xhr.responseJSON?.message
                            || 'Sesión expirada';

                        Swal.fire({
                            icon: 'error',
                            title: 'Sesión expirada',
                            text: sessionMessage,
                            confirmButtonColor: '#ef4444'
                        });
                    } else if (xhr.status === 500) {
                        const errorMessage = xhr.responseJSON?.errors?.general?.[0]
                            || xhr.responseJSON?.message
                            || 'Error al registrar empleado';

                        Swal.fire({
                            icon: 'error',
                            title: 'Error al registrar',
                            text: errorMessage,
                            confirmButtonColor: '#ef4444'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error inesperado al registrar el empleado',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }
            });
        });

        // Limpiar errores cuando el usuario escriba
        $('input, select').on('change focusout', function() {
            let fieldName = $(this).attr('name');
            if (fieldName) {
                $('[data-error="' + fieldName + '"]').addClass('hidden').text('');
                $(this).removeClass('border-red-500 is-invalid');
            }
        });
    });
</script>
