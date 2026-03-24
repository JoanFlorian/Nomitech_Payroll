@extends('layouts.app')

@section('title', 'Catalogos Empresa')
@section('page-title', 'Catalogos de Empresa')

@push('styles')
@vite('resources/css/catalogos.css')
@endpush

@section('content')
<div class="catalog-shell">
    <div class="catalog-hero d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="catalog-hero-icon">
                <i class="bi bi-grid-1x2-fill fs-4"></i>
            </span>
            <div>
                <h2 class="h4 mb-1 fw-bold">Catalogos de Empresa</h2>
                <p class="mb-0 small text-white-50">Administra catalogos de forma aislada, segura y escalable para cada empresa.</p>
            </div>
        </div>
        <span class="badge rounded-pill text-bg-light text-dark px-3 py-2">SaaS Multiempresa</span>
    </div>

    @php
        $toneBySlug = [
            'afp' => 'catalog-tone-blue',
            'banco' => 'catalog-tone-gray',
            'caja_compensacion' => 'catalog-tone-amber',
            'eps' => 'catalog-tone-red',
            'arl' => 'catalog-tone-green',
            'forma_pago' => 'catalog-tone-sky',
            'metodo_pago' => 'catalog-tone-blue',
            'rol' => 'catalog-tone-gray',
            'tipo_contrato' => 'catalog-tone-green',
        ];
    @endphp

    <div class="row g-3">
        @foreach($catalogos as $slug => $item)
            <div class="col-12 col-md-6 col-xl-3">
                <x-catalogos.card
                    :href="route('admin.catalogos.show', $slug)"
                    :label="$item['label']"
                    :icon="$item['icon']"
                    :tone-class="$toneBySlug[$slug] ?? 'catalog-tone-sky'"
                />
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">
        <small class="text-secondary">
            Mostrando {{ $catalogos->firstItem() ?? 0 }} a {{ $catalogos->lastItem() ?? 0 }} de {{ $catalogos->total() }} catalogos
        </small>

        @if($catalogos->hasPages())
            <nav aria-label="Paginacion de catalogos">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $catalogos->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $catalogos->previousPageUrl() ?? '#' }}" aria-label="Anterior">Anterior</a>
                    </li>

                    @foreach($catalogos->getUrlRange(1, $catalogos->lastPage()) as $page => $url)
                        <li class="page-item {{ $page === $catalogos->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    <li class="page-item {{ $catalogos->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $catalogos->nextPageUrl() ?? '#' }}" aria-label="Siguiente">Siguiente</a>
                    </li>
                </ul>
            </nav>
        @endif
    </div>
</div>
@endsection
