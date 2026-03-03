@props(['type' => 'submit', 'disabled' => false])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'w-full sm:w-auto py-3 px-8 btn-primary rounded-lg font-semibold transition-all duration-300 ' . ($disabled ? 'opacity-50 cursor-not-allowed' : '')]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>