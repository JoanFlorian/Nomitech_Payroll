@props(['type' => 'button', 'href' => null])

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'w-full sm:w-auto py-3 px-6 btn-secondary rounded-lg font-semibold transition-colors text-center inline-block']) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'w-full sm:w-auto py-3 px-6 btn-secondary rounded-lg font-semibold transition-colors']) }}>
        {{ $slot }}
    </button>
@endif