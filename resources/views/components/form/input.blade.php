@props([
    'name', 
    'icon', 
    'placeholder', 
    'type' => 'text', 
    'value' => null, 
    'required' => false,
    'colSpan' => false
])

<div class="input-group relative {{ $colSpan ? 'md:col-span-2' : '' }}">
    <span class="material-icons absolute left-3.5 top-1/2 -translate-y-1/2 input-icon">{{ $icon }}</span>
    <input 
        type="{{ $type }}" 
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        class="w-full h-12 input-field @error($name) border-red-500 @enderror" 
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes }}
    />
    @error($name)
        <span class="text-red-500 text-xs absolute -bottom-4 left-0">{{ $message }}</span>
    @enderror
</div>
