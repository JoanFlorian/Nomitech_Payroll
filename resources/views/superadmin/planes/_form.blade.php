@csrf

<!-- Card: Info del plan -->
<div class="border border-gray-100 rounded-2xl p-6 bg-gray-50/60">
    <h3 class="font-semibold text-gray-800 mb-4">Información del plan</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700">Nombre del plan</label>
            <input type="text" name="nombre" value="{{ old('nombre', $plan->nombre ?? '') }}"
                class="w-full border rounded-xl px-3 py-2 outline-none transition
                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nombre') border-red-500 @enderror" placeholder="Ej: Premium Plus" required>
            @error('nombre')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700">Duración (meses)</label>
            <select name="duracion"
                class="w-full border rounded-xl px-3 py-2 outline-none transition
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('duracion') border-red-500 @enderror" required>
                <option value="1" {{ old('duracion', $plan->duracion ?? '') == 1 ? 'selected' : '' }}>1 mes</option>
                <option value="3" {{ old('duracion', $plan->duracion ?? '') == 3 ? 'selected' : '' }}>3 meses</option>
                <option value="6" {{ old('duracion', $plan->duracion ?? '') == 6 ? 'selected' : '' }}>6 meses</option>
                <option value="12" {{ old('duracion', $plan->duracion ?? '') == 12 ? 'selected' : '' }}>12 meses (Anual)
                </option>
            </select>
            @error('duracion')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700">Precio</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                <input type="number" name="valor" step="0.01" min="0" value="{{ old('valor', $plan->valor ?? '') }}"
                    class="w-full border rounded-xl pl-8 pr-3 py-2 outline-none transition
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('valor') border-red-500 @enderror"
                    placeholder="0.00" required>
            </div>
            @error('valor')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700">Máximo de empleados</label>
            <input type="number" name="num_empl" min="1" value="{{ old('num_empl', $plan->num_empl ?? '') }}"
                class="w-full border rounded-xl px-3 py-2 outline-none transition
                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('num_empl') border-red-500 @enderror" placeholder="Ej: 50" required>
            @error('num_empl')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<!-- Card: Opciones Avanzadas -->
<div class="border border-gray-100 rounded-2xl p-6 bg-gray-50/60 mt-6">
    <h3 class="font-semibold text-gray-800 mb-4">Opciones Avanzadas</h3>

    <div class="flex items-center gap-2 mb-6">
        <input type="checkbox" name="destacado" id="destacado" value="1" {{ old('destacado', $plan->destacado ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
        <label for="destacado" class="text-sm font-medium text-gray-700">Plan Destacado (Recomendado)</label>
    </div>

    <div>
        <label class="block text-sm font-medium mb-3 text-gray-700">Características (Máximo 4)</label>
        <div class="space-y-3">
            @for($i = 0; $i < 4; $i++)
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-xs w-4">{{ $i + 1 }}.</span>
                    <input type="text" name="features[]"
                        value="{{ old('features.' . $i, (isset($plan->features) && is_array($plan->features)) ? ($plan->features[$i] ?? '') : '') }}"
                        class="w-full border rounded-xl px-3 py-2 outline-none transition
                                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ej: Soporte 24/7">
                </div>
            @endfor
        </div>
    </div>
</div>

<!-- Card: Descripción -->
<div class="border border-gray-100 rounded-2xl p-6 bg-gray-50/60 mt-6">
    <h3 class="font-semibold text-gray-800 mb-4">Descripción</h3>

    <div>
        <label class="block text-sm font-medium mb-1 text-gray-700">Descripción del plan</label>
        <textarea name="descripcion" rows="4"
            class="w-full border rounded-xl px-3 py-2 outline-none transition
                         focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descripcion') border-red-500 @enderror"
            placeholder="Describe los beneficios del plan...">{{ old('descripcion', $plan->descripcion ?? '') }}</textarea>
        @error('descripcion')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>