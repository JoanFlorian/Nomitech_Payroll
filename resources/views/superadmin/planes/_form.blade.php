@csrf

<div x-ref="planFormContainer">
    <script>
        function planForm() {
            return {
                nombre: '{{ old('nombre', $plan?->nombre ?? '') }}',
                duracion: '{{ old('duracion', isset($plan) ? floor($plan->duracion / 30) : '1') }}',
                valor: '{{ old('valor', $plan?->valor ?? '') }}',
                num_empl: '{{ old('num_empl', $plan?->num_empl ?? '') }}',
                max_admins: '{{ old('max_admins', $plan?->max_admins ?? '1') }}',
                max_auxiliares: '{{ old('max_auxiliares', $plan?->max_auxiliares ?? '0') }}',
                descripcion: {!! json_encode(old('descripcion', $plan?->descripcion ?? '')) !!},
                stripe_price_id: '{{ old('stripe_price_id', $plan?->stripe_price_id ?? '') }}',
                features: {!! json_encode(old('features', $plan?->features ?? ['', '', '', ''])) !!},
                touched: {
                    nombre: false,
                    duracion: false,
                    valor: false,
                    num_empl: false,
                    max_admins: false,
                    max_auxiliares: false,
                    descripcion: false,
                    stripe_price_id: false,
                    features_count: false,
                    features: [false, false, false, false]
                },
                errors: {
                    nombre: '',
                    duracion: '',
                    valor: '',
                    num_empl: '',
                    max_admins: '',
                    max_auxiliares: '',
                    descripcion: '',
                    stripe_price_id: '',
                    features_count: '',
                    features: ['', '', '', '']
                },
                validate() {
                    let nombreTrim = typeof this.nombre === 'string' ? this.nombre.trim() : this.nombre;
                    let descTrim = typeof this.descripcion === 'string' ? this.descripcion.trim() : this.descripcion;

                    // Nombre
                    if (!nombreTrim) this.errors.nombre = 'El nombre es obligatorio.';
                    else if (nombreTrim.length < 3) this.errors.nombre = 'El nombre debe tener al menos 3 caracteres.';
                    else if (nombreTrim.length > 60) this.errors.nombre = 'El nombre debe tener máximo 60 caracteres.';
                    else this.errors.nombre = '';

                    // Duración
                    let dur = Number(this.duracion);
                    if (!this.duracion || String(this.duracion).trim() === '') this.errors.duracion = 'La duración es obligatoria.';
                    else if (!Number.isInteger(dur) || dur < 1 || dur > 12) this.errors.duracion = 'La duración debe estar entre 1 y 12 meses.';
                    else this.errors.duracion = '';

                    // Valor
                    let valStr = String(this.valor).trim();
                    let valNum = Number(this.valor);
                    if (!this.valor || valStr === '') this.errors.valor = 'El precio es obligatorio.';
                    else if (isNaN(valNum) || valNum <= 0) this.errors.valor = 'El precio debe ser mayor a cero.';
                    else if (valNum > 99999999.99) this.errors.valor = 'El precio debe ser máximo 99,999,999.99.';
                    else if (!/^\d+(\.\d{1,2})?$/.test(valStr)) this.errors.valor = 'El precio debe tener máximo 2 decimales.';
                    else this.errors.valor = '';

                    // Empleados
                    let emp = Number(this.num_empl);
                    if (!this.num_empl || String(this.num_empl).trim() === '') this.errors.num_empl = 'El número de empleados es obligatorio.';
                    else if (!Number.isInteger(emp) || emp < 1 || emp > 10000) this.errors.num_empl = 'El número de empleados debe estar entre 1 y 10,000.';
                    else this.errors.num_empl = '';

                    // Admins
                    let adm = Number(this.max_admins);
                    if (!this.max_admins || String(this.max_admins).trim() === '') this.errors.max_admins = 'La cantidad de admins permitidos es obligatoria.';
                    else if (!Number.isInteger(adm) || adm < 1 || adm > 20) this.errors.max_admins = 'La cantidad de admins permitidos debe estar entre 1 y 20.';
                    else this.errors.max_admins = '';

                    // Auxiliares
                    let aux = Number(this.max_auxiliares);
                    if (this.max_auxiliares === null || String(this.max_auxiliares).trim() === '' || isNaN(aux)) this.errors.max_auxiliares = 'La cantidad de auxiliares permitidos es obligatoria.';
                    else if (!Number.isInteger(aux) || aux < 0 || aux > 20) this.errors.max_auxiliares = 'La cantidad de auxiliares permitidos debe estar entre 0 y 20.';
                    else this.errors.max_auxiliares = '';

                    // Descripción
                    if (descTrim && descTrim.length > 500) this.errors.descripcion = 'La descripción debe tener máximo 500 caracteres.';
                    else this.errors.descripcion = '';

                    // Stripe Price ID
                    let priceTrim = typeof this.stripe_price_id === 'string' ? this.stripe_price_id.trim() : this.stripe_price_id;
                    if (priceTrim && !priceTrim.startsWith('price_')) {
                        this.errors.stripe_price_id = 'El Stripe Price Id debe comenzar con price_.';
                    } else {
                        this.errors.stripe_price_id = '';
                    }

                    // Características Count
                    let filled = this.features.filter(f => typeof f === 'string' && f.trim().length > 0);
                    if (filled.length < 2) this.errors.features_count = 'Las características deben ser al menos 2.';
                    else this.errors.features_count = '';

                    // Características Individual
                    const featRegex = /^(?=.*[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ])[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.,\-&\/\(\)]+$/;
                    this.features.forEach((f, i) => {
                        let fTrim = typeof f === 'string' ? f.trim() : f;
                        if (fTrim && fTrim.length > 0) {
                            if (fTrim.length < 3) this.errors.features[i] = 'La característica debe tener al menos 3 caracteres.';
                            else if (fTrim.length > 25) this.errors.features[i] = 'La característica debe tener máximo 25 caracteres.';
                            else if (!featRegex.test(fTrim)) this.errors.features[i] = 'La característica contiene símbolos no permitidos.';
                            else this.errors.features[i] = '';
                        } else {
                            this.errors.features[i] = '';
                        }
                    });
                },
                submitForm(e) {
                    this.validate();

                    // Marcar todo como tocado para mostrar errores
                    Object.keys(this.touched).forEach(key => {
                        if (key === 'features') {
                            this.touched.features = this.touched.features.map(() => true);
                        } else {
                            this.touched[key] = true;
                        }
                    });
                    this.touched.features_count = true;

                    // Verificar si hay errores
                    let hasErrors = Object.values(this.errors).some(err => {
                        if (Array.isArray(err)) return err.some(e => e !== '');
                        return err !== '';
                    });

                    if (hasErrors) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Hacer scroll al primer error
                        this.$nextTick(() => {
                            const firstError = document.querySelector('.text-red-500:not(:empty)');
                            if (firstError) {
                                firstError.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }
                        });
                        return false;
                    }
                    
                    // Si no hay errores, permitir el envío normal
                    return true;
                },
                init() {
                    this.$watch('nombre', () => this.validate());
                    this.$watch('duracion', () => this.validate());
                    this.$watch('valor', () => this.validate());
                    this.$watch('num_empl', () => this.validate());
                    this.$watch('max_admins', () => this.validate());
                    this.$watch('max_auxiliares', () => this.validate());
                    this.$watch('descripcion', () => this.validate());
                    this.$watch('stripe_price_id', () => this.validate());
                    this.$watch('features', () => this.validate());
                    this.validate();
                }
            }
        }
    </script>

            <!-- Card: Info del plan -->
            <div class="border border-gray-100 rounded-2xl p-6 bg-gray-50/60">
                <h3 class="font-semibold text-gray-800 mb-4">Información del plan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Nombre del plan</label>
                        <input type="text" name="nombre" x-model.debounce.500ms="nombre" @input="touched.nombre = true"
                            @blur="touched.nombre = true; validate()" class="w-full border rounded-xl px-3 py-2 outline-none transition
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="touched.nombre && errors.nombre ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'"
                            placeholder="Ej: Premium Plus">
                        <p x-show="touched.nombre && errors.nombre" x-text="errors.nombre"
                            class="text-xs text-red-500 mt-1">
                        </p>
                        @error('nombre')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Duración (meses)</label>
                        <input type="number" name="duracion" x-model.debounce.500ms="duracion"
                            @input="touched.duracion = true" @blur="touched.duracion = true; validate()" class="w-full border rounded-xl px-3 py-2 outline-none transition
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="touched.duracion && errors.duracion ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'"
                            placeholder="Ej: 1">
                        <p x-show="touched.duracion && errors.duracion" x-text="errors.duracion"
                            class="text-xs text-red-500 mt-1"></p>
                        @error('duracion')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Precio</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                            <input type="number" name="valor" x-model.debounce.500ms="valor"
                                @input="touched.valor = true" @blur="touched.valor = true; validate()" step="0.01"
                                class="w-full border rounded-xl pl-8 pr-3 py-2 outline-none transition
                                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="touched.valor && errors.valor ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'"
                                placeholder="10000.00">
                        </div>
                        <p x-show="touched.valor && errors.valor" x-text="errors.valor"
                            class="text-xs text-red-500 mt-1"></p>
                        @error('valor')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Máximo de empleados</label>
                        <input type="number" name="num_empl" x-model.debounce.500ms="num_empl"
                            @input="touched.num_empl = true" @blur="touched.num_empl = true; validate()" class="w-full border rounded-xl px-3 py-2 outline-none transition
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="touched.num_empl && errors.num_empl ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'"
                            placeholder="Ej: 50">
                        <p x-show="touched.num_empl && errors.num_empl" x-text="errors.num_empl"
                            class="text-xs text-red-500 mt-1"></p>
                        @error('num_empl')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Admins Permitidos</label>
                        <input type="number" name="max_admins" x-model.debounce.500ms="max_admins"
                            @input="touched.max_admins = true" @blur="touched.max_admins = true; validate()" class="w-full border rounded-xl px-3 py-2 outline-none transition
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="touched.max_admins && errors.max_admins ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'"
                            placeholder="Ej: 2">
                        <p x-show="touched.max_admins && errors.max_admins" x-text="errors.max_admins"
                            class="text-xs text-red-500 mt-1"></p>
                        @error('max_admins')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Auxiliares Permitidos</label>
                        <input type="number" name="max_auxiliares" x-model.debounce.500ms="max_auxiliares"
                            @input="touched.max_auxiliares = true" @blur="touched.max_auxiliares = true; validate()" class="w-full border rounded-xl px-3 py-2 outline-none transition
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="touched.max_auxiliares && errors.max_auxiliares ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'"
                            placeholder="Ej: 1">
                        <p x-show="touched.max_auxiliares && errors.max_auxiliares" x-text="errors.max_auxiliares"
                            class="text-xs text-red-500 mt-1"></p>
                        @error('max_auxiliares')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">Stripe Price ID (Opcional)</label>
                        <input type="text" name="stripe_price_id" x-model.debounce.500ms.trim="stripe_price_id"
                            @input="touched.stripe_price_id = true" @blur="touched.stripe_price_id = true; validate()"
                            class="w-full border rounded-xl px-3 py-2 outline-none transition
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="touched.stripe_price_id && errors.stripe_price_id ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'"
                            placeholder="Ej: price_1Qx...">
                        <p x-show="touched.stripe_price_id && errors.stripe_price_id" x-text="errors.stripe_price_id"
                            class="text-xs text-red-500 mt-1"></p>
                        @error('stripe_price_id')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Card: Opciones Avanzadas -->
            <div class="border border-gray-100 rounded-2xl p-6 bg-gray-50/60 mt-6">
                <h3 class="font-semibold text-gray-800 mb-4">Opciones Avanzadas</h3>

                <div class="flex items-center gap-2 mb-6">
                    <input type="checkbox" name="destacado" id="destacado" value="1" {{ old('destacado', $plan->destacado ?? false) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="destacado" class="text-sm font-medium text-gray-700">Plan Destacado
                        (Recomendado)</label>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">Características (Mínimo 2, Máximo
                            4)</label>
                        <p x-show="touched.features_count && errors.features_count" x-text="errors.features_count"
                            class="text-xs text-red-500"></p>
                    </div>

                    <div class="space-y-3">
                        @for ($i = 0; $i < 4; $i++)
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-400 text-xs w-4">{{ $i + 1 }}.</span>
                                    <input type="text" name="features[]" x-model.debounce.500ms="features[{{ $i }}]"
                                        @input="touched.features[{{ $i }}] = true; touched.features_count = true"
                                        @blur="touched.features[{{ $i }}] = true; touched.features_count = true; validate()"
                                        class="w-full border rounded-xl px-3 py-2 outline-none transition
                                                                                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        :class="touched.features[{{ $i }}] && errors.features[{{ $i }}] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'"
                                        placeholder="Ej: Soporte 24/7">
                                </div>
                                <p x-show="touched.features[{{ $i }}] && errors.features[{{ $i }}]"
                                    x-text="errors.features[{{ $i }}]" class="text-xs text-red-500 mt-1 ml-6"></p>
                            </div>
                        @endfor
                    </div>
                    @error('features')
                        <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Card: Descripción -->
            <div class="border border-gray-100 rounded-2xl p-6 bg-gray-50/60 mt-6">
                <h3 class="font-semibold text-gray-800 mb-4">Descripción</h3>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Descripción del plan</label>
                    <textarea name="descripcion" rows="4" x-model.debounce.500ms="descripcion"
                        @input="touched.descripcion = true" @blur="touched.descripcion = true; validate()" class="w-full border rounded-xl px-3 py-2 outline-none transition
                             focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="touched.descripcion && errors.descripcion ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'"
                        placeholder="Describe los beneficios del plan..."></textarea>
                    <p x-show="touched.descripcion && errors.descripcion" x-text="errors.descripcion"
                        class="text-xs text-red-500 mt-1"></p>
                    @error('descripcion')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
</div>