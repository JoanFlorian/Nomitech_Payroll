@props(['title', 'description'])

<div class="text-center mb-10">
    <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $title }}</h1>
    <p class="text-gray-600 mt-4 max-w-2xl mx-auto">
        {{ $description }}
    </p>
</div>