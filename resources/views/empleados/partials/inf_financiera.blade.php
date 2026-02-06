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



<form action="{{ route('employees.final') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Forma de pago
            </label>
            <select
                class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_forma_pago">
                @foreach ( $formapagos as $formapago )
                    <option value="{{ $formapago->id_forma_pago}}">{{ $formapago->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Método de pago
            </label>
            <select
                class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_metodo_pago">
                @foreach ( $metodopago as $metopago )
                    <option value="{{ $metopago->id_metodo_pago }}">{{ $metopago->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tipo de cuenta
            </label>
            <select
                class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="tipo_cuenta">
                @foreach ( $tipocuenta as $tipcuenta )
                    <option value="{{ $tipcuenta->id_tipo_cuenta  }}">{{ $tipcuenta->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Número de cuenta
            </label>
            <input
                type="text"
                placeholder="Ej: 1234567890"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="numero_cuenta">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                EPS
            </label>
            <select
                class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_eps">
                @foreach ( $Eps as $eps )
                    <option value="{{ $eps->id_eps}}">{{ $eps->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                AFP
            </label>
            <select
                class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                    focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_afp">
                @foreach ( $Afp as $afp )
                    <option value="{{ $afp->id_afp}}">{{ $afp->nombre}}</option>
                @endforeach
            </select>
        </div>

    </div>

    <!-- BOTONES -->
    <div class="mt-10 flex justify-end">
        <button
            type="button"
            @click="step = 2"
            class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300">
            Atrás
        </button>

        <button
            type="submit"
            class="ml-4 bg-[rgb(16,185,129)] text-white py-2 px-6 rounded-md hover:bg-[rgb(14,160,112)]">
            Finalizar
        </button>
    </div>
</form>


