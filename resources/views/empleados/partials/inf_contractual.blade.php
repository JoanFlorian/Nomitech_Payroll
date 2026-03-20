<h2 class="text-2xl font-bold text-gray-800 mb-6">
    Paso 2: Información Contractual y Laboral
</h2>

<!-- Indicador de pasos -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-2">
        <div class="flex-1 text-center">
            <div class="text-xs font-medium text-gray-500">
                Información Personal
            </div>
        </div>
        <div class="flex-1 text-center">
            <div class="text-xs font-bold text-[rgb(16,185,129)]">
                Información Contractual y Laboral
            </div>
        </div>
        <div class="flex-1 text-center">
            <div class="text-xs font-medium text-gray-500">
                Información Financiera y Seguridad Social
            </div>
        </div>
    </div>

    <div class="relative">
        <div class="h-1 bg-gray-200 rounded"></div>
        <div class="absolute top-0 left-0 h-1 bg-[rgb(16,185,129)] rounded" style="width: 66.66%;"></div>
        <div class="absolute -top-2.5 flex justify-between w-full">
            <div class="w-6 h-6 bg-[rgb(16,185,129)] rounded-full flex items-center justify-center text-white text-xs font-bold">1</div>
            <div class="w-6 h-6 bg-[rgb(16,185,129)] rounded-full flex items-center justify-center text-white text-xs font-bold">2</div>
            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs font-bold">3</div>
        </div>
    </div>
</div>

<form id="step2" novalidate action="{{ route('employees.step2') }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_tipo_contrato">
                Tipo de contrato
            </label>
            <select id="id_tipo_contrato"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                    name="id_tipo_contrato"
                    required>
                <option value="">Seleccionar...</option>
                @foreach ( $contratos as $contrato )
                    <option value="{{ $contrato->id_tipo_contrato }}">{{ $contrato->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="id_tipo_contrato"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_tipo_trabajador">
                Tipo de trabajador
            </label>
            <select id="id_tipo_trabajador"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                    name="id_tipo_trabajador"
                    required>
                <option value="">Seleccionar...</option>
                @foreach ( $tipotrabajadores as $tipotrabajador )
                    <option value="{{ $tipotrabajador->id_tipo_trabajador }}">{{ $tipotrabajador->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="id_tipo_trabajador"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_sub_tipo_trabajador">
                Subtipo de trabajador
            </label>
            <select id="id_sub_tipo_trabajador"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                    name="id_sub_tipo_trabajador"
                    required>
                <option value="">Seleccionar...</option>
                @foreach ( $suptrabajadores as $suptrabajador )
                    <option value="{{ $suptrabajador->id_sub_tipo_trabajador }}">{{ $suptrabajador->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="id_sub_tipo_trabajador"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="horas-diarias">
                Horas diarias a trabajar
            </label>
            <input type="number" id="horas_diarias" name="horas_diarias" placeholder="Ej: 8"
                min="1" max="12"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                required/>
            <div class="error-message invalid-feedback" data-error="horas_diarias"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="fecha-inicio">
                Fecha de inicio
            </label>
            <input type="date" id="fecha_inicio" name="fecha_inicio"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"
                required/>
            <div class="error-message invalid-feedback" data-error="fecha_inicio"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="fecha-fin">
                Fecha de fin
            </label>
            <input type="date" id="fecha_fin" name="fecha_fin"
                @if($activePeriod) min="{{ $activePeriod->fecha_inicio->format('Y-m-d') }}" @endif
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"/>
            <p id="fecha_fin_hint" class="text-xs text-gray-500 mt-1">
                @if($activePeriod) 
                    Debe ser igual o posterior al inicio del periodo actual ({{ $activePeriod->fecha_inicio->format('d/m/Y') }}).
                @else
                    Debe ser posterior a la fecha de inicio.
                @endif
            </p>
            <div class="error-message invalid-feedback" data-error="fecha_fin"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="salario">
                Salario básico
            </label>
            <input type="text" id="salario" placeholder="Ej: 2.000.000,00"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="salario"
                inputmode="decimal"
                autocomplete="off"
                required/>
            <div class="error-message invalid-feedback" data-error="salario"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="codigo-interno">
                Código interno del trabajador 
            </label>
            <input type="text" id="codigo_interno" placeholder="Ej: 1001"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                name="codigo_interno"
                inputmode="numeric"
                minlength="3"
                maxlength="20"
                pattern="[0-9]+"/>
            <div class="error-message invalid-feedback" data-error="codigo_interno"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_arl">
                ARL
            </label>
            <select id="id_arl"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" 
                    name="id_arl"
                    required>
                <option value="">Seleccionar...</option>
                @foreach ( $Arl as $arl )
                    <option value="{{ $arl->id_arl }}">{{ $arl->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="id_arl"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="nivel-riesgo">
                Nivel de riesgo
            </label>
            <select id="nivel_riesgo" name="nivel_riesgo_id" class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" required>
                <option value="">Seleccionar...</option>
                @foreach($niveles as $nivel)
                    <option value="{{ $nivel->id }}" {{ isset($contrato->nivel_riesgo_id) && $contrato->nivel_riesgo_id == $nivel->id ? 'selected' : '' }}>
                        {{ $nivel->nombre }}
                    </option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="nivel_riesgo_id"></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_rol">
                Rol del sistema
            </label>
            <select id="id_rol" name="id_rol" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm">
                @foreach($roles as $rol)
                    <option value="{{ $rol->id_rol }}" {{ $rol->nombre == 'Empleado' ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                @endforeach
            </select>
            <div class="error-message invalid-feedback" data-error="id_rol"></div>
        </div>

        <div class="flex items-center">
            <input id="alto_riesgo" type="checkbox"
                class="h-4 w-4 text-[#1565C0] border-gray-300 rounded focus:ring-[#1565C0]" 
                name="alto_riesgo" />
            <label for="alto_riesgo" class="ml-2 block text-sm text-gray-700">
                Trabajador de alto riesgo
            </label>
            <div class="error-message invalid-feedback" data-error="alto_riesgo"></div>
        </div>

        <div class="flex items-center">
            <input id="bajo_riesgo" type="checkbox"
                class="h-4 w-4 text-[#1565C0] border-gray-300 rounded focus:ring-[#1565C0]"
                name="bajo_riesgo" />
            <label for="bajo_riesgo" class="ml-2 block text-sm text-gray-700">
                Trabajador de bajo riesgo
            </label>
        </div>
    </div>

    <!-- BOTONES -->
    <div class="mt-10 flex justify-end gap-3">
        <button type="button"
                @click="previousStep()"
                class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition">
            Atrás
        </button>

        <button type="submit"
                class="bg-[rgb(16,185,129)] text-white py-2 px-6 rounded-md hover:bg-[rgb(14,160,112)] transition">
            Continuar
        </button>
    </div>
</form>
