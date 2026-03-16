@extends('layouts.app')

@section('title', 'Detalle Nota de Ajuste')
@section('page-title', 'Detalle Nota de Ajuste')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $nota->usuario?->nombre_completo ?? 'Trabajador no disponible' }}</h2>
            <p class="mt-1 text-sm text-gray-500">Nota enviada {{ optional($nota->created_at)->diffForHumans() }}</p>
        </div>
        <a href="{{ route('admin.notas-ajuste.index') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <span class="material-icons mr-2 text-base">arrow_back</span>
            Volver al listado
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre completo</p>
            <p class="mt-2 text-lg font-semibold text-gray-900">{{ $nota->usuario?->nombre_completo ?? 'N/A' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Documento</p>
            <p class="mt-2 text-lg font-semibold text-gray-900">{{ $nota->usuario?->doc ?? 'N/A' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha de envío</p>
            <p class="mt-2 text-lg font-semibold text-gray-900">{{ optional($nota->created_at)->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-900">Contenido completo de la nota</h3>
            @php
                $estadoClass = match($nota->estado) {
                    \App\Models\NotaAjuste::ESTADO_RESUELTO => 'bg-green-100 text-green-700',
                    \App\Models\NotaAjuste::ESTADO_OMITIDO => 'bg-slate-200 text-slate-700',
                    default => 'bg-amber-100 text-amber-700',
                };
            @endphp
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estadoClass }}">{{ ucfirst($nota->estado) }}</span>
        </div>

        <div class="rounded-2xl bg-gray-50 p-5 text-sm leading-7 text-gray-700 whitespace-pre-line">{{ $nota->mensaje }}</div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900">Desprendible relacionado</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Periodo</p>
                <p class="mt-2 text-sm font-semibold text-gray-900">{{ $nota->salario?->periodo?->nombre ?? 'N/A' }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha pago</p>
                <p class="mt-2 text-sm font-semibold text-gray-900">{{ optional($nota->salario?->created_at)->format('d/m/Y') ?? 'N/A' }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Devengado</p>
                <p class="mt-2 text-sm font-semibold text-green-700">${{ number_format((float) ($nota->salario?->total_devengos ?? 0), 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Deducciones</p>
                <p class="mt-2 text-sm font-semibold text-orange-700">${{ number_format((float) ($nota->salario?->total_deducciones ?? 0), 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Neto</p>
                <p class="mt-2 text-sm font-semibold text-blue-700">${{ number_format((float) ($nota->salario?->salario_neto ?? 0), 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900">Respuesta del administrador</h3>

        <form action="{{ route('admin.notas-ajuste.update', $nota) }}" method="POST" class="mt-4 space-y-4">
            @csrf

            <div>
                <textarea
                    id="respuesta_admin"
                    name="respuesta_admin"
                    rows="6"
                    required
                    minlength="5"
                    maxlength="2000"
                    class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm text-gray-800 shadow-sm transition focus:border-[#1565C0] focus:outline-none focus:ring-4 focus:ring-blue-100 @error('respuesta_admin') border-red-500 @enderror"
                    placeholder="Escribe la respuesta para el trabajador..."
                >{{ old('respuesta_admin', $nota->respuesta_admin) }}</textarea>
                @error('respuesta_admin')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="submit" name="accion" value="resuelto" class="inline-flex items-center rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
                    Responder y marcar como RESUELTO
                </button>
                <button type="submit" name="accion" value="omitido" class="inline-flex items-center rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                    Responder y marcar como OMITIDO
                </button>
            </div>
        </form>
    </div>
</div>
@endsection