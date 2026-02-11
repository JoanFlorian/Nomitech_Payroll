<x-guest-layout>
    <x-auth.background-shapes />

    @php
        $initialData = [
            'nit' => old('nit', ''),
            'nit_dv' => old('nit_dv', ''),
            'razon_social' => old('razon_social', ''),
            'id_departamento' => old('id_departamento', ''),
            'id_ciudad' => old('id_ciudad', ''),
            'direccion_empresa' => old('direccion_empresa', ''),
            'documento' => old('documento', ''),
            'id_tipo_doc' => old('id_tipo_doc', ''),
            'primer_apellido' => old('primer_apellido', ''),
            'segundo_apellido' => old('segundo_apellido', ''),
            'primer_nombre' => old('primer_nombre', ''),
            'otros_nombres' => old('otros_nombres', ''),
            'telefono_celular' => old('telefono_celular', ''),
            'email' => old('email', ''),
            'plan_id' => old('plan_id', $selected_plan_id ?? '')
        ];
    @endphp

    <x-ui.card>
        <x-auth.form-header title="Crea tu cuenta Nomitech"
            description="Por favor, proporciona la información básica de tu empresa para crear tu cuenta Nomitech." />

        <form 
            method="POST" 
            action="{{ route('register') }}"
            x-data="registerForm(@js($initialData))"
            @submit.prevent="validateForm() && $el.submit()"
            novalidate
        >
            @csrf

            <!-- Plan selection -->
            @if(isset($plans) && $plans->count())
                <div class="mb-6">
                    <label for="plan_id" class="block text-sm font-medium text-gray-700 mb-2">Selecciona tu plan</label>
                    <select name="plan_id" id="plan_id" class="w-full rounded-md border-gray-200 p-2" x-model="plan_id">
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->nombre }} - ${{ number_format($p->valor) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <!-- Global Backend Errors (Fallback) -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <span class="material-icons text-red-600 mr-2">error_outline</span>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-red-800 mb-1">Error al procesar el registro:</h3>
                            <ul class="list-disc list-inside text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <x-form.grid>
                <!-- SECTION: DATOS DE EMPRESA -->
                <p class="md:col-span-2 text-sm text-gray-500 font-medium uppercase tracking-wide">Datos de Empresa</p>

                <!-- RAZÓN SOCIAL -->
                <div class="md:col-span-2">
                    <x-form.input name="razon_social" icon="business" placeholder="Razón social o nombre legal" 
                        x-model="razon_social" @blur="handleBlur('razon_social')" @input="handleInput('razon_social')" />
                    <span x-show="errors.razon_social" x-text="errors.razon_social" class="text-red-500 text-xs mt-1 block"></span>
                </div>

                <!-- NIT & DV -->
                <div class="grid grid-cols-4 gap-x-4 md:col-span-2">
                    <div class="col-span-3">
                        <x-form.input name="nit" icon="badge" placeholder="NIT (Solo números)" 
                            x-model="nit" @blur="handleBlur('nit')" @input="handleInput('nit')"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" inputmode="numeric" />
                        <span x-show="errors.nit" x-text="errors.nit" class="text-red-500 text-xs mt-1 block"></span>
                    </div>
                    <div class="col-span-1">
                        <x-form.input name="nit_dv" icon="pin" placeholder="DV" 
                            x-model="nit_dv" @blur="handleBlur('nit_dv')" @input="handleInput('nit_dv')"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" inputmode="numeric" />
                        <span x-show="errors.nit_dv" x-text="errors.nit_dv" class="text-red-500 text-xs mt-1 block"></span>
                    </div>
                </div>
                
                <!-- PAÍS -->
                <x-form.input name="pais" icon="public" placeholder="País" value="CO" readonly 
                    class="bg-gray-100 text-gray-500 cursor-not-allowed" />

                <!-- DEPARTAMENTO -->
                <div>
                    <x-form.searchable-select 
                        name="id_departamento" 
                        icon="location_on" 
                        placeholder="Departamento"
                        x-model="id_departamento"
                        :options="$departamentos" 
                    />
                    <span x-show="errors.id_departamento" x-text="errors.id_departamento" class="text-red-500 text-xs mt-1 block"></span>
                </div>

                <!-- CIUDAD -->
                <div>
                    <x-form.searchable-select 
                        name="id_ciudad" 
                        id="citySelector"
                        icon="location_city" 
                        placeholder="Ciudad / Municipio"
                        x-model="id_ciudad"
                        endpoint="/api/cities/search?q="
                        @selected="onCityChange($event.detail.key)"
                    />
                    <span x-show="errors.id_ciudad" x-text="errors.id_ciudad" class="text-red-500 text-xs mt-1 block"></span>
                </div>
            
                <!-- DIRECCIÓN -->
                <div class="md:col-span-2">
                    <x-form.input name="direccion_empresa" icon="home" placeholder="Dirección Empresa" 
                        x-model="direccion_empresa" @blur="handleBlur('direccion_empresa')" @input="handleInput('direccion_empresa')" />
                    <span x-show="errors.direccion_empresa" x-text="errors.direccion_empresa" class="text-red-500 text-xs mt-1 block"></span>
                </div>

                <div class="md:col-span-2 border-t border-gray-100 my-2"></div>
                <p class="md:col-span-2 text-sm text-gray-500 font-medium mb-2 uppercase tracking-wide">Representante Legal</p>

                <div>
                    <x-form.searchable-select 
                        name="id_tipo_doc" 
                        icon="badge" 
                        placeholder="Tipo de Documento"
                        x-model="id_tipo_doc"
                        :options="$tiposDocumento" 
                        :searchable="false"
                    />
                    <span x-show="errors.id_tipo_doc" x-text="errors.id_tipo_doc" class="text-red-500 text-xs mt-1 block"></span>
                </div>
                
                <div>
                    <x-form.input name="documento" icon="numbers" placeholder="Número de Documento" 
                        x-model="documento" @blur="handleBlur('documento')" @input="handleInput('documento')"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" inputmode="numeric" />
                    <span x-show="errors.documento" x-text="errors.documento" class="text-red-500 text-xs mt-1 block"></span>
                </div>

                <!-- PRIMER APELLIDO -->
                <div>
                    <x-form.input name="primer_apellido" icon="person_outline" placeholder="Primer apellido" 
                        x-model="primer_apellido" @blur="handleBlur('primer_apellido')" @input="handleInput('primer_apellido')" />
                    <span x-show="errors.primer_apellido" x-text="errors.primer_apellido" class="text-red-500 text-xs mt-1 block"></span>
                </div>
                
                <!-- SEGUNDO APELLIDO -->
                <div>
                    <x-form.input name="segundo_apellido" icon="person_outline" placeholder="Segundo apellido" 
                        x-model="segundo_apellido" @blur="handleBlur('segundo_apellido')" @input="handleInput('segundo_apellido')" />
                    <span x-show="errors.segundo_apellido" x-text="errors.segundo_apellido" class="text-red-500 text-xs mt-1 block"></span>
                </div>
                
                <!-- PRIMER NOMBRE -->
                <div>
                    <x-form.input name="primer_nombre" icon="person" placeholder="Primer nombre" 
                        x-model="primer_nombre" @blur="handleBlur('primer_nombre')" @input="handleInput('primer_nombre')" />
                    <span x-show="errors.primer_nombre" x-text="errors.primer_nombre" class="text-red-500 text-xs mt-1 block"></span>
                </div>
                
                <!-- OTROS NOMBRES -->
                <div>
                    <x-form.input name="otros_nombres" icon="person" placeholder="Otros nombres" 
                        x-model="otros_nombres" @blur="handleBlur('otros_nombres')" @input="handleInput('otros_nombres')" />
                    <span x-show="errors.otros_nombres" x-text="errors.otros_nombres" class="text-red-500 text-xs mt-1 block"></span>
                </div>

                <div>
                    <x-form.input name="telefono_celular" icon="phone" placeholder="Celular (10 dígitos)" 
                        x-model="telefono_celular" @blur="handleBlur('telefono_celular')" @input="handleInput('telefono_celular')"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" inputmode="numeric" />
                    <span x-show="errors.telefono_celular" x-text="errors.telefono_celular" class="text-red-500 text-xs mt-1 block"></span>
                </div>

                <div>
                    <x-form.input name="email" icon="email" placeholder="Correo electrónico" type="email" 
                        x-model="email" @blur="handleBlur('email')" @input="handleInput('email')" />
                    <span x-show="errors.email" x-text="errors.email" class="text-red-500 text-xs mt-1 block"></span>
                </div>
                
                <div>
                    <x-form.input name="password" icon="lock" placeholder="Contraseña" type="password" 
                        x-model="password" @blur="handleBlur('password')" @input="handleInput('password')" />
                    <span x-show="errors.password" x-text="errors.password" class="text-red-500 text-xs mt-1 block"></span>
                </div>

                <div>
                    <x-form.input name="password_confirmation" icon="lock_outline" placeholder="Confirmar contraseña" type="password" 
                        x-model="password_confirmation" @blur="handleBlur('password_confirmation')" @input="handleInput('password_confirmation')" />
                    <span x-show="errors.password_confirmation" x-text="errors.password_confirmation" class="text-red-500 text-xs mt-1 block"></span>
                </div>
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