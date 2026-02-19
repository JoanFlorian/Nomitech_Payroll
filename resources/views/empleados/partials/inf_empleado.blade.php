<h2 class="text-2xl font-bold text-gray-800 mb-6">
    Paso 1: Información Personal
</h2>

<!-- Indicador de pasos -->
<div class="mb-10">
    <div class="flex justify-between text-sm mb-2">
        <span class="font-semibold text-[rgb(16,185,129)]">
            Información Personal
        </span>
        <span class="text-gray-500">Información Contractual y Laboral</span>
        <span class="text-gray-500 text-right">
            Información Financiera y Seguridad Social
        </span>
    </div>

    <div class="relative h-1 bg-gray-200 rounded-full">
        <div class="absolute h-1 bg-[rgb(16,185,129)] rounded-full w-1/3"></div>

        <div class="absolute -top-3 left-0 w-full flex justify-between">
            <div class="w-6 h-6 bg-[rgb(16,185,129)] text-white rounded-full flex items-center justify-center text-xs font-bold">
                1
            </div>

            <div class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs font-bold">
                2
            </div>

            <div class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs font-bold">
                3
            </div>
        </div>
    </div>
</div>




<form id="step1" novalidate>
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tipo de documento
            </label>
            <select
                class="form-select mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm"  name="id_tipo_doc">
                @foreach ( $tipodoc as $tipodo )
                    <option value="{{ $tipodo->id_tipo_doc }}">{{ $tipodo->nombre }}</option>
                @endforeach
                
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="id_tipo_doc"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Número de documento 
            </label>
            <input
                type="number"
                placeholder="Ej: 1234567890"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="numero_documento">
                <p class="error-message text-red-500 text-sm hidden" data-error="numero_documento"><p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Primer apellido
            </label>
            <input
                type="text"
                placeholder="Ej: Pérez"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="primer_apellido">
                <p class="error-message text-red-500 text-sm hidden" data-error="primer_apellido"><p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Segundo apellido
            </label>
            <input
                type="text"
                placeholder="Ej: Gómez"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="segundo_apellido">
                <p class="error-message text-red-500 text-sm hidden" data-error="segundo_apellido"><p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Primer nombre
            </label>
            <input
                type="text"
                placeholder="Ej: Juan"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="primer_nombre">
                <p class="error-message text-red-500 text-sm hidden" data-error="primer_nombre"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Otros nombres
            </label>
            <input
                type="text"
                placeholder="Ej: Carlos"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="otros_nombres">
                <p class="error-message text-red-500 text-sm hidden" data-error="otros_nombres"></p>
        </div>


        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Departamento 
            </label>
            <select
                class="form-select mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="departamento">
                @foreach ( $departamento as $depa )
                    <option value="{{ $depa->id_departamento }}">{{ $depa->nombre }}</option>
                @endforeach
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="departamento"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Ciudad
            </label>
            <select
                class="form-select mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="ciudad">
                @foreach ( $ciudad as $ciud )
                    <option value="{{ $ciud->id_ciudad  }}" data-departamento="{{$ciud->id_departamento}}">{{ $ciud->nombre }}</option>
                @endforeach
                
            </select>
            <p class="error-message text-red-500 text-sm hidden" data-error="ciudad"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Dirección
            </label>
            <input
                type="text"
                placeholder="Ej: Calle 10 #42-15"
                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#1565C0] focus:border-[#1565C0] sm:text-sm" name="direccion">
                <p class="error-message text-red-500 text-sm hidden" data-error="direccion"></p>
        </div>

    </div>
    <div class="mt-10 flex justify-end">
        <button
            type="submit"
            class="bg-[rgb(16,185,129)] text-white font-medium py-2 px-6 rounded-md hover:bg-[rgb(14,160,112)]">
            Continuar
        </button>

    </div>
</form> 

    <button type="hidden" @click="step = 2" id="siguienteStep"></button>
<!-- jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function () {//cuando todo este cargado en el dom 

        $('#step1').on('submit', function (e) {
            e.preventDefault(); // evita recarga

            let form = $(this);
            let url  = "{{ route('employees.step1') }}";
            let data = form.serialize(); // recoge los inputs
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (response) {
                    $("#siguienteStep").click();
                    console.log(response);
                },
                error: function (xhr) {

                // Limpiar errores anteriores
                $('.error-message').addClass('hidden').text('');
                $('input, select').removeClass('border-red-500');

                if (xhr.status === 422) {

                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function (key, value) {

                        // Mostrar mensaje debajo del input
                        let errorField = $('[data-error="' + key + '"]');

                        errorField.removeClass('hidden');
                        errorField.text(value[0]);

                        // Pintar input en rojo
                        $('[name="' + key + '"]').addClass('border-red-500');
                    });
                }
            }

            });
        });

    });
</script>
