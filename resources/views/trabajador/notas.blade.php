@extends('layouts.app')

@section('title', 'Mis Notas de Ajuste')
@section('page-title', 'Mis Notas de Ajuste')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-100 bg-gradient-to-r from-slate-50 to-blue-50 p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Seguimiento de notas enviadas</h2>
                <p class="mt-2 text-sm text-gray-600">Consulta el estado de cada nota, la respuesta del administrador y elimina solo las que siguen pendientes.</p>
            </div>
            <a href="{{ route('trabajador.desprendibles') }}" class="inline-flex items-center rounded-xl bg-[#1565C0] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0D47A1]">
                <span class="material-icons mr-2 text-base">receipt_long</span>
                Crear nota desde desprendibles
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto modern-scroll">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Desprendible</th>
                        <th class="px-6 py-4">Mensaje</th>
                        <th class="px-6 py-4">Respuesta admin</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($notasEnviadas as $nota)
                        <tr>
                            <td class="px-6 py-4">{{ optional($nota->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $nota->salario?->periodo?->nombre ?? 'Sin periodo' }}</div>
                                <div class="text-xs text-gray-500">ID #{{ $nota->id_salario ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">{{ \Illuminate\Support\Str::limit($nota->mensaje, 120) }}</td>
                            <td class="px-6 py-4">{{ $nota->respuesta_admin ? \Illuminate\Support\Str::limit($nota->respuesta_admin, 120) : 'Sin respuesta aún' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClass = match($nota->estado) {
                                        \App\Models\NotaAjuste::ESTADO_RESUELTO => 'bg-green-100 text-green-700',
                                        \App\Models\NotaAjuste::ESTADO_OMITIDO => 'bg-slate-200 text-slate-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst($nota->estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($nota->estado === \App\Models\NotaAjuste::ESTADO_PENDIENTE)
                                    <form action="{{ route('trabajador.notas.destroy', $nota) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar esta nota pendiente?');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100">
                                            Eliminar Nota
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">Aún no has enviado notas de ajuste.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-6 py-4">
            {{ $notasEnviadas->links() }}
        </div>
    </div>
</div>
@endsection
