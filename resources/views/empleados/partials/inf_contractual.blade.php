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

<form id="step2">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="fecha-inicio">
                Fecha de inicio
            </label>
            <input type="date" id="fecha_inicio" name="fecha_inicio"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"/>
                        <p class="error-message text-red-500 text-sm hidden" data-error="fecha_inicio"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="fecha-fin">
                Fecha de fin
            </label>
            <input type="date" id="fecha_fin" name="fecha_fin"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"/>
                        <p class="error-message text-red-500 text-sm hidden" data-error="fecha_fin"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="horas-diarias">
                Horas diarias a trabajar
            </label>
            <input type="number" id="horas_diarias" name="horas_diarias" placeholder="Ej: 8"
                min="0" max="24"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"/>
                        <p class="error-message text-red-500 text-sm hidden" data-error="horas_diarias"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_tipo_trabajador">
                Tipo de trabajador
            </label>
            <select id="id_tipo_trabajador"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_tipo_trabajador">
                @foreach ( $tipotrabajadores as $tipotrabajador )
                    <option value="{{ $tipotrabajador->id_tipo_trabajador }}">{{ $tipotrabajador->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_trabajador"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_sub_tipo_trabajador">
                Subtipo de trabajador
            </label>
            <select id="id_sub_tipo_trabajador"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_sub_tipo_trabajador">
                @foreach ( $suptrabajadores as $suptrabajador )
                    <option value="{{ $suptrabajador->id_sub_tipo_trabajador  }}">{{ $suptrabajador->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_sub_tipo_trabajador"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_tipo_contrato">
                Tipo de contrato
            </label>
            <select id="id_tipo_contrato"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_tipo_contrato">
                @foreach ( $contratos as $contrato )
                    <option value="{{ $contrato->id_tipo_contrato   }}">{{ $contrato->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_contrato"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="salario">
                Salario básico
            </label>
            <input type="number" id="salario" placeholder="Ej: 2000000"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="salario"/>
                        <p class="error-message text-red-500 text-sm hidden" data-error="salario"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="codigo-interno">
                Código interno del trabajador 
            </label>
            <input type="text" id="codigo_interno" placeholder="Ej: EMP001"
                class="w-full border border-gray-300 rounded-md px-3 py-2 shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="codigo_interno"/>
                        <p class="error-message text-red-500 text-sm hidden" data-error="codigo_interno"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="id_arl">
                ARL
            </label>
            <select id="id_arl"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="id_arl">
                @foreach ( $Arl as $arl )
                    <option value="{{ $arl->id_arl}}">{{ $arl->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_arl"></p>
        </div>

        <div class="flex items-center">
            <input id="alto_riesgo" type="checkbox"
                class="h-4 w-4 text-[#1565C0] border-gray-300 rounded focus:ring-[#1565C0]" name="alto_riesgo" />
            <label for="alto-riesgo" class="ml-2 block text-sm text-gray-700">
                Trabajador de alto riesgo
            </label>
            <p class="error-message text-red-500 text-sm hidden" data-error="alto_riesgo"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="nivel-riesgo">
                Nivel de riesgo
            </label>
            <select id="nivel_riesgo"
                    class="form-select w-full border border-gray-300 rounded-md px-3 py-2 bg-white shadow-sm
                        focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="nivel_riesgo">
                <option value="">Seleccione</option>
                <option>Nivel I</option>
                <option>Nivel II</option>
                <option>Nivel III</option>
                <option>Nivel IV</option>
                <option>Nivel V</option>
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="nivel_riesgo"></p>
        </div>
    </div>

    <!-- BOTONES -->
    <div class="mt-10 flex justify-end">
        <button type="button"
                @click="step = 1"
                class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300">
            Atrás
        </button>

        <button type="button"
        id="btnStep2"
                class="ml-4 bg-[rgb(16,185,129)] text-white py-2 px-6 rounded-md hover:bg-[rgb(14,160,112)]">
            Continuar
        </button>
    </div>
</form>

    <button type="hidden" @click="step = 3" id="siguienteStep1"></button>

<!-- jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function () {

    $('#btnStep2').on('click', function () {

        let form = $('#step2');
        let url  = "{{ route('employees.step2') }}";
        let data = form.serialize();

        $.ajax({
            url: url,
            type: 'POST',
            data: data,

            success: function (response) {
                $("#siguienteStep1").click();
            },

            error: function (xhr) {

                $('.error-message').addClass('hidden').text('');
                $('input, select').removeClass('border-red-500');

                if (xhr.status === 422) {

                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function (key, value) {

                        let errorField = $('[data-error="' + key + '"]');

                        errorField.removeClass('hidden');
                        errorField.text(value[0]);

                        $('[name="' + key + '"]').addClass('border-red-500');
                    });
                }
            }
        });

    });

});

</script>
