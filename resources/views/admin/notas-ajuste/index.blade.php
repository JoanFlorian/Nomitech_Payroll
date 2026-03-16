@extends('layouts.app')

@section('title', 'Notas de Ajuste')
@section('page-title', 'Notas de Ajuste')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-100 bg-gradient-to-r from-slate-50 to-blue-50 p-6 shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900">Solicitudes enviadas por trabajadores</h2>
        <p class="mt-2 text-sm text-gray-600">Revisa notas pendientes y resueltas. Las omitidas ya no se muestran en este panel.</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto modern-scroll">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-6 py-4">Trabajador</th>
                        <th class="px-6 py-4">Documento</th>
                        <th class="px-6 py-4">Desprendible</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Mensaje</th>
                        <th class="px-6 py-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($notas as $nota)
                        <tr class="{{ $nota->estado === \App\Models\NotaAjuste::ESTADO_PENDIENTE ? 'bg-amber-50/40' : '' }}">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $nota->usuario?->nombre_completo ?? 'Trabajador no disponible' }}</td>
                            <td class="px-6 py-4">{{ $nota->usuario?->doc ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $nota->salario?->periodo?->nombre ?? 'Sin periodo' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $nota->estado === \App\Models\NotaAjuste::ESTADO_PENDIENTE ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
                                    {{ ucfirst($nota->estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ optional($nota->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">{{ \Illuminate\Support\Str::limit($nota->mensaje, 90) }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.notas-ajuste.show', $nota) }}" class="inline-flex items-center rounded-xl bg-[#1565C0] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#0D47A1]">
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No hay notas de ajuste registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-6 py-4">
            {{ $notas->links() }}
        </div>
    </div>
</div>
@endsection