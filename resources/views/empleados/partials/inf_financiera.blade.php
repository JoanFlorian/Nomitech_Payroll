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
            <div
                class="w-6 h-6 bg-[rgb(16,185,129)] text-white rounded-full flex items-center justify-center text-xs font-bold">
                1
            </div>

            <div
                class="w-6 h-6 bg-[rgb(16,185,129)] text-white rounded-full flex items-center justify-center text-xs font-bold">
                2
            </div>

            <div
                class="w-6 h-6 bg-[rgb(16,185,129)] text-white rounded-full flex items-center justify-center text-xs font-bold">
                3
            </div>
        </div>
    </div>
</div>

<form id="step3" novalidate action="{{ route('employees.final') }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_forma_pago">
                Forma de pago
            </label>
            <select id="id_forma_pago" class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_forma_pago"
                required>
                <option value="">Seleccionar...</option>
                @foreach ($formapagos as $formapago)
                    <option value="{{ $formapago->id_forma_pago }}">{{ $formapago->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="id_forma_pago"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="tipo_cuenta">
                Tipo de cuenta
            </label>
            <select id="tipo_cuenta" class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="tipo_cuenta">
                <option value="">Seleccionar...</option>
                @foreach ($tipocuenta as $tipcuenta)
                    <option value="{{ $tipcuenta->id_tipo_cuenta }}">{{ $tipcuenta->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="tipo_cuenta"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="numero_cuenta">
                Número de cuenta
            </label>
            <input type="text" id="numero_cuenta" placeholder="Ej: 1234567890" class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="numero_cuenta"
                inputmode="numeric" pattern="[0-9]{6,20}" minlength="6" maxlength="20">
            <div class="error-message invalid-feedback" data-error="numero_cuenta"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                EPS
            </label>
            <select class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_eps" required>
                <option value="">Seleccionar...</option>
                @foreach ($Eps as $eps)
                    <option value="{{ $eps->id_eps }}">{{ $eps->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="id_eps"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                AFP
            </label>
            <select class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_afp" required>
                <option value="">Seleccionar...</option>
                @foreach ($Afp as $afp)
                    <option value="{{ $afp->id_afp }}">{{ $afp->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="id_afp"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Caja de Compensación <span class="text-red-500">*</span>
            </label>
            <select class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_caja" required>
                <option value="">Seleccionar...</option>
                @foreach ($Cajas as $caja)
                    <option value="{{ $caja->id_caja }}">{{ $caja->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="id_caja"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Fondo de Cesantías
            </label>
            <select class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="fondo_cesantias">
                <option value="">Seleccionar (Opcional)...</option>
                <option value="Protección">Protección</option>
                <option value="Porvenir">Porvenir</option>
                <option value="Colfondos">Colfondos</option>
                <option value="FNA">Fondo Nacional del Ahorro (FNA)</option>
                <option value="Skandia">Skandia</option>
            </select>
            <div class="error-message invalid-feedback" data-error="fondo_cesantias"></div>
        </div>
    </div>

    {{-- Sección de Continuidad de Provisiones (Migración) --}}
    <div class="mt-10 border-t pt-8">
        <div class="mb-6">
            <h4 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-history text-[#1565C0]"></i>
                Continuidad de Provisiones Anteriores
            </h4>
            <p class="text-sm text-gray-600 mt-1">
                Ingrese estos saldos <strong>únicamente</strong> si el empleado ya tiene prestaciones acumuladas de periodos de trabajo anteriores que la empresa ya ha provisionado.
            </p>
        </div>

        <div class="bg-blue-50/30 border border-blue-100 rounded-xl p-6 ring-1 ring-blue-50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prima inicial</label>
                    <input type="text" id="prima_inicial" name="prima_inicial" value="0" placeholder="0" 
                        class="migration-input w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                            focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                        data-type="money">
                    <div class="error-message text-xs text-red-500 mt-1 hidden" data-error="prima_inicial"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cesantías inicial</label>
                    <input type="text" id="cesantias_inicial" name="cesantias_inicial" value="0" placeholder="0" 
                        class="migration-input w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                            focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                        data-type="money">
                    <div class="error-message text-xs text-red-500 mt-1 hidden" data-error="cesantias_inicial"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Intereses cesantías inicial</label>
                    <input type="text" id="intereses_inicial" name="intereses_inicial" value="0" placeholder="0" 
                        class="migration-input w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                            focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                        data-type="money">
                    <div class="error-message text-xs text-red-500 mt-1 hidden" data-error="intereses_inicial"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vacaciones inicial (en días)</label>
                    <input type="text" id="vacaciones_inicial" name="vacaciones_inicial" value="0" placeholder="Ej: 15" 
                        class="migration-input w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                            focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                        data-type="days">
                    <div class="error-message text-xs text-red-500 mt-1 hidden" data-error="vacaciones_inicial"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTONES -->
    <div class="mt-10 flex justify-end">
        <button type="button" @click="previousStep()"
            class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition">
            Atrás
        </button>

        <button type="submit"
            class="ml-4 bg-[rgb(16,185,129)] text-white py-2 px-6 rounded-md hover:bg-[rgb(14,160,112)] transition">
            Finalizar
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const migrationInputs = document.querySelectorAll('.migration-input');
    const debounceTimers = {};

    function debounce(func, name, delay = 500) {
        if (debounceTimers[name]) clearTimeout(debounceTimers[name]);
        debounceTimers[name] = setTimeout(func, delay);
    }

    function formatCurrency(value) {
        if (value === null || value === '') return '';
        
        // Remove everything except digits and one comma
        let sanitized = value.toString().replace(/[^\d,]/g, '');
        let parts = sanitized.split(',');
        let integerPart = parts[0];
        let decimalPart = parts.length > 1 ? parts.slice(1).join('') : null;
        
        // Format integer part with dots
        let formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        
        if (decimalPart !== null) {
            return formattedInteger + ',' + decimalPart.substring(0, 2);
        }
        return formattedInteger;
    }

    function parseCurrency(value) {
        if (!value) return 0;
        // Strip dots (thousands) and replace comma with dot (decimal)
        return parseFloat(value.toString().replace(/\./g, '').replace(',', '.')) || 0;
    }

    function validateField(input) {
        const name = input.name;
        const valStr = input.value.trim();
        const val = parseCurrency(valStr);
        const errorEl = document.querySelector(`[data-error="${name}"]`);
        let error = '';

        if (valStr === '' || valStr === '0') {
            // Optional fields, but we show no error if empty or 0
            errorEl.classList.add('hidden');
            input.classList.remove('border-red-500', 'border-amber-500', 'text-amber-700');
            return;
        }

        if (val < 0) {
            error = 'El valor no puede ser negativo.';
        } else if (isNaN(val)) {
            error = 'Debe ingresar un número válido.';
        }

        // Reglas específicas
        if (!error) {
            if (name === 'cesantias_inicial') {
                if (val > 9999999999) {
                    error = 'Advertencia: El valor ingresado es inusualmente alto.';
                } else if (val > 0 && val < 5000) {
                    error = 'Alerta: El valor es inusualmente bajo para un empleado activo.';
                }
            } else if (name === 'prima_inicial') {
                if (val > 0 && val < 5000) {
                    error = 'Alerta: El valor es inusualmente bajo para un empleado activo.';
                }
            } else if (name === 'intereses_inicial') {
                const cesantiasVal = parseCurrency(document.querySelector('[name="cesantias_inicial"]').value);
                if (val > 0 && val < 100) {
                    error = 'Alerta: El valor de intereses es inusualmente bajo.';
                } else if (cesantiasVal > 0 && val > (cesantiasVal * 0.15)) { // 12% + margen
                    error = 'Advertencia: Los intereses parecen ser incoherentes con las cesantías.';
                }
            } else if (name === 'vacaciones_inicial') {
                if (val > 180) {
                    error = 'Error: No se permite acumular más de 180 días.';
                } else if (val > 60) {
                    error = 'Alerta: El trabajador tiene más de 60 días acumulados.';
                }
            }
        }

        if (error) {
            errorEl.textContent = error;
            errorEl.classList.remove('hidden');
            if (error.toLowerCase().includes('error') || error.toLowerCase().includes('obligatorio')) {
                input.classList.add('border-red-500');
                input.classList.remove('border-amber-500');
            } else {
                input.classList.add('border-amber-500');
                input.classList.remove('border-red-500');
                input.classList.add('text-amber-700');
            }
        } else {
            errorEl.classList.add('hidden');
            input.classList.remove('border-red-500', 'border-amber-500', 'text-amber-700');
        }
    }

    // Auto-calculate Intereses (12% of Cesantías)
    const cesantiasInput = document.querySelector('[name="cesantias_inicial"]');
    const interesesInput = document.querySelector('[name="intereses_inicial"]');
    
    if (cesantiasInput && interesesInput) {
        cesantiasInput.addEventListener('input', function() {
            // Only auto-calculate if the user hasn't manually focused/set interests or if it's currently 0
            // Actually, the user asked to auto-calculate, so we do it on every change of cesantías
            // but we allow them to edit it afterwards.
            const cesVal = parseCurrency(this.value);
            if (cesVal > 0) {
                const autoIntereses = cesVal * 0.12;
                interesesInput.value = formatCurrency(autoIntereses.toFixed(0));
                validateField(interesesInput);
            }
        });
    }

    migrationInputs.forEach(input => {
        // Clear 0 on focus
        input.addEventListener('focus', function() {
            if (this.value === '0' || this.value === '0,00' || this.value === '') {
                this.value = '';
            }
        });

        // Restore 0 on blur if empty
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.value = '0';
                validateField(this);
            }
        });

        input.addEventListener('input', function(e) {
            let cursorPosition = this.selectionStart;
            let originalLength = this.value.length;
            
            if (this.dataset.type === 'money') {
                this.value = formatCurrency(this.value);
            } else {
                // For days, allow numeric and one comma
                let val = this.value.replace(/[^\d,]/g, '');
                let parts = val.split(',');
                if (parts.length > 2) {
                    val = parts[0] + ',' + parts.slice(1).join('');
                }
                this.value = val;
            }

            // Adjust cursor position
            let newLength = this.value.length;
            let lengthDiff = newLength - originalLength;
            this.setSelectionRange(cursorPosition + lengthDiff, cursorPosition + lengthDiff);

            debounce(() => validateField(input), input.name);
        });

        // Trigger validation once on load if there's a value
        if (input.value && input.value !== '0') {
            validateField(input);
        }
    });
});
</script>