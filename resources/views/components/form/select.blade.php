@props([
    'name', 
    'icon', 
    'options' => [], 
    'placeholder' => 'Selecciona una opción',
    'value' => null, 
    'required' => false,
    'colSpan' => false
])

<div class="input-group relative {{ $colSpan ? 'md:col-span-2' : '' }}">
    <span class="material-icons absolute left-3.5 top-1/2 -translate-y-1/2 input-icon">{{ $icon }}</span>
    <select 
        name="{{ $name }}"
        class="w-full h-12 input-field appearance-none bg-white @error($name) border-red-500 @enderror" 
        {{ $required ? 'required' : '' }}
    >
        <option value="" disabled {{ is_null($value) ? 'selected' : '' }}>{{ $placeholder }}</option>
        @foreach($options as $key => $label)
            <option value="{{ $key }}" {{ (string)$key === (string)old($name, $value) ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    
    <!-- Custom Arrow -->
    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
        <span class="material-icons">expand_more</span>
    </div>

    @error($name)
        <span class="text-red-500 text-xs absolute -bottom-4 left-0">{{ $message }}</span>
    @enderror
</div>
