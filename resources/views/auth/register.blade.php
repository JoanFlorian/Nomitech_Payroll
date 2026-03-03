<x-guest-layout>
    <x-auth.background-shapes />

    @php
        $initialData = $initialData ?? [
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
            'password' => old('password', ''),
            'password_confirmation' => old('password_confirmation', ''),
            'plan_id' => old('plan_id', $selected_plan_id ?? '')
        ];
    @endphp

    <div x-data="{ 
        showExitModal: false,
        exitUrl: '/',
        handleBack(url = '/') {
            // Guardar la URL de destino
            this.exitUrl = url;

            // Obtener todos los campos de entrada del formulario (incluyendo ocultos para los searchable-select)
            const formInputs = Array.from(document.querySelectorAll('form input, form select'));
            
            // Campos que NO deben activar el modal (Sistema o por defecto)
            const ignoredFields = ['_token', 'pais', 'plan_id'];

            // Verificar si alguno tiene contenido significativo
            const hasData = formInputs.some(input => {
                if (ignoredFields.includes(input.name)) return false;
                
                // Si es un select o un input, verificamos que tenga valor
                return input.value.trim() !== '' && input.value !== '0';
            });

            if (hasData) {
                this.showExitModal = true;
            } else {
                window.location.href = url;
            }
        }
    }">
        <!-- Modal de confirmación de salida -->
        <template x-teleport="body">
            <div x-show="showExitModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4" 
                 x-cloak>
                
                <!-- Fondo oscuro (sin blur) con figuritas decorativas más grandes y variadas -->
                <div class="absolute inset-0 bg-gray-900/85" @click="showExitModal = false">
                    <!-- Figuritas decorativas Marca: Azul #1565C0, Verde #2AA58C -->
                    <div class="absolute top-[5%] left-[10%] w-32 h-32 rounded-full bg-[#1565C0]/20 blur-[2px] rotate-12"></div>
                    <div class="absolute top-[15%] right-[15%] w-48 h-48 rounded-3xl bg-[#2AA58C]/15 blur-[1px] -rotate-12"></div>
                    <div class="absolute bottom-[10%] left-[20%] w-40 h-40 rounded-xl bg-[#2AA58C]/20 rotate-45"></div>
                    <div class="absolute bottom-[20%] right-[10%] w-56 h-56 rounded-full bg-[#1565C0]/15 blur-[3px]"></div>
                    <div class="absolute top-[40%] left-[-5%] w-24 h-24 rounded-full bg-[#2AA58C]/25"></div>
                    <div class="absolute top-[55%] right-[-5%] w-36 h-36 rounded-2xl bg-[#1565C0]/20 -rotate-6"></div>
                    <div class="absolute top-[70%] left-[45%] w-16 h-16 rounded-full bg-[#2AA58C]/20"></div>
                </div>

                <!-- Contenido del Modal -->
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative z-10 border border-gray-100"
                     x-show="showExitModal"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <span class="material-icons text-orange-600 text-xl">report_problem</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 leading-tight">¿Confirmas salir?</h3>
                    </div>
                    
                    <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                        Detectamos que has empezado a completar el formulario. Si sales ahora, perderás toda la información ingresada.
                    </p>
                    
                    <div class="flex items-center justify-end gap-3">
                        <button @click="showExitModal = false" 
                                class="px-5 py-2.5 text-sm font-bold text-white bg-[#2AA58C] rounded-xl hover:bg-[#248f76] transition-all duration-200 cursor-pointer shadow-md shadow-[#2AA58C]/20">
                            Continuar registro
                        </button>
                        <a :href="exitUrl" 
                           class="px-5 py-2.5 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 transition-all cursor-pointer shadow-md shadow-red-200">
                            Salir y borrar
                        </a>
                    </div>
                </div>
            </div>
        </template>

        <x-ui.card>
            <div class="mb-6 -mt-2">
                <button @click="handleBack('/')" type="button" class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-[#2AA58C] transition-all group focus:outline-none">
                    <span class="material-icons text-xl mr-2 group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    Volver al inicio
                </button>
            </div>
        <x-auth.form-header 
            title="{{ $title ?? 'Crea tu cuenta Nomitech' }}"
            description="{{ $description ?? 'Por favor, proporciona la información básica de tu empresa para crear tu cuenta Nomitech.' }}" 
        />

        <form 
            method="POST" 
            action="{{ $action ?? route('register') }}"
            x-data="registerForm(@js($initialData))"
            @submit.prevent="validateForm() && $el.submit()"
            novalidate
        >
            @csrf

            @if(isset($isPendingPayment) && $isPendingPayment)
                <div class="mb-6 p-3 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center gap-3">
                    <span class="material-icons text-emerald-600">info_outline</span>
                    <p class="text-sm text-emerald-800">
                        Hemos recuperado tus datos de registro anteriores. Por favor, verifícalos y completa el pago para activar tu cuenta.
                    </p>
                </div>
            @endif

            <!-- Plan selection -->
            @if(isset($plans) && $plans->count())
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <label for="plan_id" class="block text-sm font-medium text-gray-700">Selecciona tu plan</label>
                        @if(isset($isPendingPayment) && $isPendingPayment)
                            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold border border-emerald-200">
                                PUEDES CAMBIAR TU PLAN AQUÍ
                            </span>
                        @endif
                    </div>
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
                <div class="grid grid-cols-4 gap-x-4">
                    <div class="col-span-3">
                        <x-form.input name="nit" icon="badge" placeholder="NIT (Solo números)" 
                            x-model="nit" @blur="handleBlur('nit')" @input="handleInput('nit')"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" inputmode="numeric" />
                        <span x-show="errors.nit" x-text="errors.nit" class="text-red-500 text-[11px] leading-tight mt-1 block font-medium"></span>
                    </div>
                    <div class="col-span-1">
                        <x-form.input name="nit_dv" icon="pin" placeholder="DV" required 
                            maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            x-model="nit_dv"
                            @blur="handleBlur('nit_dv')"
                            @input="handleInput('nit_dv')"
                            oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length > 1) this.value = this.value.slice(0, 1);"
                            title="Debe ser numérico de un solo dígito." />
                        <span x-show="errors.nit_dv" 
                              x-text="errors.nit_dv" 
                              class="text-red-500 text-xs mt-1 block font-medium"></span>
                    </div>
                </div>
                
                <!-- NIE035: PAÍS (Hidden, fixed to CO) -->
                <input type="hidden" name="pais" value="CO">

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
                        x-model="direccion_empresa" @blur="handleBlur('direccion_empresa')" @input="handleInput('direccion_empresa')"
                        maxlength="150"
                        title="Incluye referencia vial (Calle, Carrera, Cra, Cl, Av, Transversal, Diagonal, # o No)." />
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
                    <x-form.input name="password" icon="lock" 
                        placeholder="{{ isset($isPendingPayment) && $isPendingPayment ? 'Confirmar Nueva Contraseña' : 'Contraseña' }}" 
                        type="password" 
                        x-model="password" @blur="handleBlur('password')" @input="handleInput('password')" />
                    @if(isset($isPendingPayment) && $isPendingPayment)
                        <span class="text-[10px] text-emerald-600 mt-1 block font-medium">Por seguridad, introduce tu contraseña de acceso</span>
                    @endif
                    <span x-show="errors.password" x-text="errors.password" class="text-red-500 text-xs mt-1 block"></span>
                </div>

                <div>
                    <x-form.input name="password_confirmation" icon="lock_outline" placeholder="Confirmar contraseña" type="password" 
                        x-model="password_confirmation" @blur="handleBlur('password_confirmation')" @input="handleInput('password_confirmation')" />
                    <span x-show="errors.password_confirmation" x-text="errors.password_confirmation" class="text-red-500 text-xs mt-1 block"></span>
                </div>
            </x-form.grid>

            <div class="mt-10 flex flex-col-reverse sm:flex-row items-center justify-end gap-4">
                <x-ui.button-secondary @click="handleBack('/')">
                    Atrás
                </x-ui.button-secondary>

                <button 
                    type="submit"
                    :disabled="isSubmitting"
                    :class="{'opacity-50 cursor-not-allowed': isSubmitting}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-[#2AA58C] text-white font-medium rounded-lg hover:bg-[#248f76] transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#2AA58C] focus:ring-offset-2"
                >
                    <span x-show="!isSubmitting">{{ $submitText ?? 'Crear cuenta' }}</span>
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
</div>
</x-guest-layout>