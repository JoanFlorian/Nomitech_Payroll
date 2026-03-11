@props(['origen'])

@php
    $isSystem = $origen === 'system';
@endphp

<span class="badge rounded-pill {{ $isSystem ? 'text-bg-secondary' : 'text-bg-primary' }}">
    {{ $isSystem ? 'Sistema' : 'Empresa' }}
</span>
