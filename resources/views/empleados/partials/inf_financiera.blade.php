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
    </div>

    {{-- Saldos Iniciales de Prestaciones (colapsable) --}}
    <div class="mt-8" x-data="{ showBalances: false }">
        <button type="button" @click="showBalances = !showBalances"
            class="flex items-center gap-2 text-sm font-semibold text-[#1565C0] hover:text-[#0D47A1] transition-colors mb-4">
            <i class="bi" :class="showBalances ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            <i class="bi bi-box-seam"></i>
            Saldos Iniciales de Prestaciones (opcional)
        </button>

        <div x-show="showBalances" x-collapse x-cloak class="bg-blue-50/50 border border-blue-100 rounded-xl p-5">
            <p class="text-xs text-gray-500 mb-4">
                Si el empleado ya tiene prestaciones causadas antes de usar el sistema, ingrese los saldos iniciales
                aquí.
                Estos valores se registrarán como movimientos iniciales en el ledger.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prima inicial</label>
                    <input type="number" name="prima_inicial" step="0.01" min="0" value="0" placeholder="0" class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                            focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cesantías inicial</label>
                    <input type="number" name="cesantias_inicial" step="0.01" min="0" value="0" placeholder="0" class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                            focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Intereses cesantías inicial</label>
                    <input type="number" name="intereses_inicial" step="0.01" min="0" value="0" placeholder="0" class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                            focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vacaciones inicial</label>
                    <input type="number" name="vacaciones_inicial" step="0.01" min="0" value="0" placeholder="0" class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                            focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm">
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