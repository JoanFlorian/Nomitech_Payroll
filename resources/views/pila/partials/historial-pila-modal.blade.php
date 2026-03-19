<div class="modal fade" id="historialPilaModal" tabindex="-1" aria-labelledby="historialPilaModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content pila-historial-modal">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="historialPilaModalLabel">
                    Historial de archivos PILA
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <small class="text-muted">
                        Archivos generados por periodo para la empresa activa.
                    </small>
                    <span class="badge rounded-pill badge-soft">
                        {{ ($historialPila ?? collect())->count() }} registros
                    </span>
                </div>

                <div class="table-responsive border rounded-3">
                    <table class="table pila-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Periodo</th>
                                <th class="text-center">Empleados</th>
                                <th>Fecha generacion</th>
                                <th class="text-center">Estado archivo</th>
                                <th class="text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($historialPila ?? collect()) as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        @if(!empty($item->fecha_inicio) && !empty($item->fecha_fin))
                                            {{ \Carbon\Carbon::parse($item->fecha_inicio)->format('Y-m-d') }} a 
                                            {{ \Carbon\Carbon::parse($item->fecha_fin)->format('Y-m-d') }}
                                        @else
                                            #{{ $item->periodo_id }}
                                        @endif
                                    </td>
                                    <td class="text-center">{{ (int) $item->total_empleados }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border">
                                            Disponible
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('pila.historial.descargar', ['id' => $item->id]) }}" 
                                           class="btn btn-sm btn-outline-primary history-download-btn">
                                            <i class="bi bi-download me-1"></i>
                                            Descargar TXT
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No hay historial de archivos PILA.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
#historialPilaModal .pila-historial-modal {
    background-color: #ffffff;
}
</style>