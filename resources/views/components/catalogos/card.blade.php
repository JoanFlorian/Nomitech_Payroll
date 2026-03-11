@props([
    'href',
    'label',
    'icon' => 'bi-collection',
    'toneClass' => 'catalog-tone-sky',
])

<a href="{{ $href }}"
   class="catalog-card {{ $toneClass }} text-decoration-none h-100 d-block rounded-4 p-4 position-relative overflow-hidden border border-0">
    <span class="catalog-glow"></span>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="text-uppercase small fw-semibold catalog-eyebrow">Catalogo</div>
            <h3 class="h5 fw-bold text-dark mb-0 mt-2">{{ $label }}</h3>
        </div>
        <div class="catalog-icon rounded-3 d-inline-flex align-items-center justify-content-center">
            <i class="bi {{ $icon }} fs-4"></i>
        </div>
    </div>
    <p class="text-secondary mt-3 mb-0 small">Gestiona registros por empresa con reglas SaaS y seguridad por NIT.</p>
</a>
