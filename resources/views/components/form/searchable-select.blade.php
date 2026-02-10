@props([
    'name',
    'icon',
    'options' => [],
    'placeholder' => 'Selecciona una opción',
    'value' => null,
    'required' => false,
    'colSpan' => false,
    'searchable' => true,
    'endpoint' => null,
    'id' => null,
])

<div
    {{ $attributes->merge(['class' => 'relative ' . ($colSpan ? 'md:col-span-2' : '')]) }}

    data-config="{{ json_encode([
        'value' => $value,
        'options' => $options,
        'searchable' => $searchable,
        'endpoint' => $endpoint,
        'id' => $id
    ], JSON_HEX_APOS | JSON_HEX_QUOT) }}"
    x-data="searchableSelect(JSON.parse($el.dataset.config))"
    x-modelable="selectedKey"
    @click.outside="open = false"
>
    <!-- Hidden Input -->
    <input type="hidden" name="{{ $name }}" x-model="selectedKey" {{ $required ? 'required' : '' }}>

    <!-- Trigger -->
    <div @click="open = !open" class="input-group relative cursor-pointer">
        <span class="material-icons absolute left-3.5 top-1/2 -translate-y-1/2 input-icon">{{ $icon }}</span>
        
        <div 
            class="w-full h-15 input-field flex items-center bg-white pr-10 truncate select-none border text-gray-700 @error($name) border-red-500 @else border-[#BDBDBD] @enderror"
            :class="{'text-gray-400': !selectedKey, 'border-blue-700 ring-2 ring-blue-700/20': open}"
            x-text="label || '{{ $placeholder }}'"
        ></div> 

        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
            <span class="material-icons transition-transform duration-200" :class="{'rotate-180': open}">expand_more</span>
        </div>
    </div> 

    <div 
        x-show="open" 
        style="display: none;"
        class="absolute z-[999] mt-1 w-full max-h-60 overflow-y-auto bg-white rounded-lg shadow-xl border border-gray-100"
    >
        <template x-if="isSearchable">
            <div class="sticky top-0 z-10 p-2 bg-gray-50 border-b border-gray-100">
                <input 
                    x-model="search" 
                    @input.debounce.300ms="performSearch($el.value)" 
                    type="text" 
                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm focus:outline-none focus:border-[#1565C0] text-gray-900" 
                    placeholder="Buscar..."
                    @click.stop
                >
            </div>
        </template>

        <ul>
            <template x-for="[key, val] in Object.entries(filteredOptions)" :key="key">
                <li 
                    @click="select(key, val)"
                    class="px-4 py-2 hover:bg-blue-50 cursor-pointer text-gray-700 text-sm hover:text-[#1565C0] transition-colors"
                    :class="{'bg-blue-50 text-[#1565C0]': selectedKey == key}"
                >
                    <span x-text="val"></span>
                </li>
            </template>
            
            <li x-show="Object.keys(filteredOptions).length === 0" class="px-4 py-3 text-gray-500 text-sm text-center">
                <span x-show="isLoading">Buscando...</span>
                <span x-show="!isLoading && Object.keys(options).length > 0">No hay resultados</span>
                <span x-show="!isLoading && Object.keys(options).length === 0">Sin opciones</span>
            </li>
        </ul>
    </div>

    @error($name)
        <span class="text-red-500 text-xs absolute -bottom-5 left-0">{{ $message }}</span>
    @enderror
</div>
