<div id="historialPilaModal" class="fixed inset-0 z-[90] hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/55"></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <!-- Modal Header -->
            <div class="sticky top-0 border-b border-slate-100 bg-white px-6 py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900">Historial de archivos PILA</h3>
                    <p class="mt-1 text-sm text-slate-500">Archivos generados por periodo para la empresa activa.</p>
                </div>
                <button type="button" id="btn-cerrar-historial-pila" class="rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 transition" aria-label="Cerrar">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-5">
                <!-- Contador de registros -->
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-600">
                        Total de registros: <span class="font-bold text-slate-900">{{ ($historialPila ?? collect())->count() }}</span>
                    </span>
                </div>

                <!-- Tabla de historial -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="px-4 py-3 text-left font-bold text-slate-700">#</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-700">Periodo</th>
                                <th class="px-4 py-3 text-center font-bold text-slate-700">Empleados</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-700">Fecha generación</th>
                                <th class="px-4 py-3 text-center font-bold text-slate-700">Estado</th>
                                <th class="px-4 py-3 text-center font-bold text-slate-700">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($historialPila ?? collect()) as $item)
                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-slate-700 font-medium">{{ $item->id }}</td>
                                    <td class="px-4 py-3 text-slate-700">
                                        @if(!empty($item->fecha_inicio) && !empty($item->fecha_fin))
                                            <span class="font-medium">{{ \Carbon\Carbon::parse($item->fecha_inicio)->format('Y-m-d') }}</span><br>
                                            <span class="text-xs text-slate-500">a {{ \Carbon\Carbon::parse($item->fecha_fin)->format('Y-m-d') }}</span>
                                        @else
                                            <span class="text-slate-400">#{{ $item->periodo_id }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-700 font-medium">{{ (int) $item->total_empleados }}</td>
                                    <td class="px-4 py-3 text-slate-600 text-xs">{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-medium">
                                            <i class="bi bi-check-circle text-sm"></i>
                                            Disponible
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('pila.historial.descargar', ['id' => $item->id]) }}" 
                                               class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-medium text-xs hover:bg-blue-100 transition-colors"
                                               title="Descargar archivo original en formato TXT">
                                                <i class="bi bi-file-text text-sm"></i>
                                                TXT
                                            </a>
                                            <a href="{{ route('pila.export.registro', ['id' => $item->id]) }}" 
                                               class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 font-medium text-xs hover:bg-purple-100 transition-colors"
                                               title="Descargar este registro en Excel con diseño profesional">
                                                <i class="bi bi-file-earmark-excel text-sm"></i>
                                                Excel
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="bi bi-inbox text-4xl text-slate-300 mb-3"></i>
                                            <p class="text-slate-500 font-medium">No hay historial de archivos PILA.</p>
                                            <p class="text-slate-400 text-sm mt-1">Los archivos generados aparecerán aquí.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4 bg-slate-50">
                <a href="{{ route('pila.historial.excel') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium text-sm hover:bg-emerald-100 transition-colors"
                   title="Descargar todo el historial PILA en Excel con diseño profesional">
                    <i class="bi bi-file-earmark-excel text-sm"></i>
                    Descargar Historial en Excel
                </a>
                <button type="button" id="btn-cerrar-historial-pila-footer" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('historialPilaModal');
        const btnClose = document.getElementById('btn-cerrar-historial-pila');
        const btnCloseFooter = document.getElementById('btn-cerrar-historial-pila-footer');

        function closeModal() {
            modal.classList.add('hidden');
        }

        function openModal() {
            modal.classList.remove('hidden');
        }

        // Cerrar modal
        if (btnClose) btnClose.addEventListener('click', closeModal);
        if (btnCloseFooter) btnCloseFooter.addEventListener('click', closeModal);

        // Cerrar al hacer clic en el overlay
        modal.addEventListener('click', function(e) {
            if (e.target === modal.querySelector('.absolute')) {
                closeModal();
            }
        });

        // Exposer función global para abrir el modal desde otros lugares
        window.mostrarHistorialPila = openModal;
    });
</script>