<x-guest-layout>
    <x-auth.background-shapes />

    <x-ui.card>
        <x-auth.form-header title="Crea tu cuenta Nomitech"
            description="Por favor, proporciona la información básica de tu empresa para crear tu cuenta Nomitech." />

        <form 
            method="POST" 
            action="{{ route('register') }}"
            x-data="registerForm()"
            @submit.prevent="validateForm($event) && $el.submit()"
        >
            @csrf
            
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <span class="material-icons text-red-600 mr-2">error_outline</span>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-red-800 mb-2">Por favor corrige los siguientes errores:</h3>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Frontend Validation Errors -->
            <div x-show="Object.keys(errors).length > 0" x-cloak class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                <div class="flex items-start">
                    <span class="material-icons text-orange-600 mr-2">warning</span>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-orange-800 mb-2">Revisa los siguientes campos:</h3>
                        <ul class="list-disc list-inside text-sm text-orange-700 space-y-1">
                            <template x-for="[field, message] in Object.entries(errors)" :key="field">
                                <li x-text="message"></li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
            
            <x-form.grid>
                <!-- SECTION: DATOS DE EMPRESA -->
                <p class="md:col-span-2 text-sm text-gray-500 font-medium uppercase tracking-wide">Datos de Empresa</p>

                <!-- NIE032: RAZÓN SOCIAL -->
                <x-form.input name="razon_social" icon="business" placeholder="Razón social o nombre legal" col-span
                    maxlength="60" />

                <!-- NIE033 & NIE034: NIT & DV -->
                <!-- Replacing x-form.nit-group for strict control -->
                <div class="grid grid-cols-4 gap-x-4">
                    <div class="col-span-3">
                        <x-form.input name="nit" icon="badge" placeholder="NIT (Sin guiones)" required 
                            maxlength="20" inputmode="numeric" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            title="Solo números, sin guiones ni espacios." />
                    </div>
                    <div class="col-span-1">
                        <x-form.input name="dv" icon="pin" placeholder="DV" required 
                            maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            title="Debe ser numérico." />
                    </div>
                </div>
                
                <!-- NIE035: PAÍS (Fixed to CO) -->
                <x-form.input name="pais" icon="public" placeholder="País" value="CO" readonly required
                    maxlength="2"
                    class="bg-gray-100 text-gray-500 cursor-not-allowed" />

                <!-- NIE036: DEPARTAMENTO -->
                <x-form.searchable-select 
                    name="id_departamento" 
                    icon="location_on" 
                    placeholder="Departamento"
                    x-model="departmentId"
                    :options="$departamentos" 
                    required 
                />

                <!-- NIE037: CIUDAD -->
                <x-form.searchable-select 
                    name="id_ciudad" 
                    id="citySelector"
                    icon="location_city" 
                    placeholder="Ciudad / Municipio"
                    x-model="cityId"
                    endpoint="/api/cities/search?q="
                    @selected="onCityChange($event.detail.key)"
                    required 
                />
            
                <!-- NIE038: DIRECCIÓN -->
                <x-form.input name="direccion_empresa" icon="home" placeholder="Dirección Empresa" col-span required 
                     maxlength="255" />

                <div class="md:col-span-2 border-t border-gray-100 my-2"></div>
                <p class="md:col-span-2 text-sm text-gray-500 font-medium mb-2 uppercase tracking-wide">Representante
                    Legal</p>

                <x-form.searchable-select 
                    name="id_tipo_doc" 
                    icon="badge" 
                    placeholder="Tipo de Documento"
                    x-model="docType"
                    :options="$tiposDocumento" 
                    :searchable="false"
                    required 
                />
                
                <x-form.input name="doc" icon="numbers" placeholder="Número de Documento" required maxlength="20" />

                <!-- NIE210: PRIMER APELLIDO -->
                <x-form.input name="primer_apellido" icon="person_outline" placeholder="Primer apellido" required maxlength="60"
                    pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+"
                    oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '')"
                    title="Solo se permiten letras" />
                
                <!-- NIE211: SEGUNDO APELLIDO -->
                <x-form.input name="segundo_apellido" icon="person_outline" placeholder="Segundo apellido" maxlength="60"
                    pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+"
                    oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '')"
                    title="Solo se permiten letras" />
                
                <!-- NIE212: PRIMER NOMBRE -->
                <x-form.input name="primer_nombre" icon="person" placeholder="Primer nombre" required maxlength="60"
                    pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+"
                    oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '')"
                    title="Solo se permiten letras" />
                
                <!-- NIE213: OTROS NOMBRES -->
                <x-form.input name="otros_nombres" icon="person" placeholder="Otros nombres" maxlength="60"
                    pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+"
                    oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '')"
                    title="Solo se permiten letras" />

                <x-form.input name="telefono" icon="phone" placeholder="Celular / Teléfono" required maxlength="20"
                    inputmode="numeric" pattern="[0-9\s\-()]+"
                    oninput="this.value = this.value.replace(/[^0-9\s\-()]/g, '')"
                    title="Solo se permiten números" />
                <x-form.input name="correo" icon="email" placeholder="Correo electrónico" type="email" required maxlength="256" />
                
                <x-form.input name="contrasena" icon="lock" placeholder="Contraseña" type="password" required minlength="8" />
                <x-form.input name="contrasena_confirmation" icon="lock_outline" placeholder="Confirmar contraseña"
                    type="password" required minlength="8" />
            </x-form.grid>

            <div class="mt-10 flex flex-col-reverse sm:flex-row items-center justify-end gap-4">
                <x-ui.button-secondary href="{{ route('login') }}">
                    Atrás
                </x-ui.button-secondary>

                <button 
                    type="submit"
                    :disabled="isSubmitting"
                    :class="{'opacity-50 cursor-not-allowed': isSubmitting}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-[#2AA58C] text-white font-medium rounded-lg hover:bg-[#248f76] transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#2AA58C] focus:ring-offset-2"
                >
                    <span x-show="!isSubmitting">Crear cuenta</span>
                    <span x-show="isSubmitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando...
                    </span>
                </button>
            </div>
        </form>
    </x-ui.card>
</x-guest-layout>