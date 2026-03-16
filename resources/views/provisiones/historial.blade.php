@extends('layouts.app')

@section('title', 'Historial de Prestaciones')
@section('page-title', 'Historial de Prestaciones')

@section('content')
    <div>
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-lg"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $empleadoNombre }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span
                        class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[11px] font-bold rounded uppercase tracking-wider">Documento:
                        {{ $doc }}</span>
                    <span class="text-xs text-gray-400 italic">Historial completo de movimientos</span>
                </div>
            </div>
            <a href="{{ route('provisiones.index') }}"
                class="w-full md:w-auto bg-white text-gray-700 font-bold py-2 px-5 rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-all duration-300 flex items-center justify-center gap-2 text-sm">
                <i class="bi bi-arrow-left"></i>
                Volver a Provisiones
            </a>
        </div>

        {{-- Filter Panel --}}
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm mb-6">
            <form action="{{ route('provisiones.historial', $doc) }}" method="GET" class="flex flex-wrap items-end gap-5">
                {{-- Type --}}
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Tipo de
                        Prestación</label>
                    <div class="relative">
                        <select name="type"
                            class="w-full bg-gray-50 border-gray-200 rounded-lg text-sm focus:ring-blue-500/20 focus:border-blue-500 transition-all pl-3 pr-10 py-2 appearance-none">
                            <option value="">Todas las prestaciones</option>
                            <option value="prima" {{ request('type') == 'prima' ? 'selected' : '' }}>Prima de Servicios
                            </option>
                            <option value="cesantias" {{ request('type') == 'cesantias' ? 'selected' : '' }}>Cesantías
                            </option>
                            <option value="intereses_cesantias" {{ request('type') == 'intereses_cesantias' ? 'selected' : '' }}>Intereses de Cesantías</option>
                            <option value="vacaciones" {{ request('type') == 'vacaciones' ? 'selected' : '' }}>Vacaciones
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                            <i class="bi bi-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Month-Year (Period) --}}
                <div class="w-full md:w-56">
                    <label
                        class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1 text-nowrap">Periodo
                        Mensual</label>
                    <input type="month" name="period" value="{{ request('period') }}"
                        class="w-full bg-gray-50 border-gray-200 rounded-lg text-sm focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium py-2">
                </div>

                {{-- Exact Date --}}
                <div class="w-full md:w-48">
                    <label
                        class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1 text-nowrap">Día
                        Específico</label>
                    <input type="date" name="date" value="{{ request('date') }}"
                        class="w-full bg-gray-50 border-gray-200 rounded-lg text-sm focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium py-2">
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="bg-[#1565C0] text-white font-bold py-2 px-6 rounded-lg shadow-sm hover:bg-[#0D47A1] transition-all flex items-center gap-2 text-sm">
                        <i class="bi bi-search text-xs"></i> Aplicar
                    </button>
                    @if(request()->hasAny(['type', 'period', 'date']))
                        <a href="{{ route('provisiones.historial', $doc) }}"
                            class="inline-flex items-center justify-center w-9 h-9 border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-100 hover:bg-red-50 rounded-lg transition-all"
                            title="Limpiar filtros">
                            <i class="bi bi-trash3 text-sm"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Movements Table --}}
        @if($movements->isEmpty())
            <div class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-200">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-journal-x text-3xl text-gray-300"></i>
                </div>
                <p class="text-gray-500 text-lg font-medium">No se encontraron movimientos.</p>
                @if(request()->hasAny(['type', 'period', 'date']))
                    <p class="text-gray-400 text-sm mt-1">Intenta ajustando los filtros de búsqueda.</p>
                    <a href="{{ route('provisiones.historial', $doc) }}"
                        class="mt-4 inline-flex items-center text-[#1565C0] font-bold hover:underline">
                        Limpiar todos los filtros
                    </a>
                @else
                    <p class="text-gray-400 text-sm mt-1">Este empleado aún no tiene registros en el historial.</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Fecha</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Tipo /
                                Origen</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Prestación
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Destino /
                                Estado</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Valor
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Detalle /
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @foreach($movements as $movement)
                            @php
                                $isNegative = $movement->amount < 0;
                                $badgeColors = match ($movement->movement_type) {
                                    'accrual' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                    'payment' => 'bg-red-50 text-red-700 border border-red-200',
                                    'withdrawal' => 'bg-orange-50 text-orange-700 border border-orange-200',
                                    'adjustment' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                    'initial' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                    default => 'bg-gray-50 text-gray-700 border border-gray-200',
                                };
                                $sourceBadge = match ($movement->source) {
                                    'payroll' => 'bg-blue-100 text-blue-700',
                                    'liquidation' => 'bg-red-100 text-red-700',
                                    'migration' => 'bg-purple-100 text-purple-700',
                                    'manual' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };

                                $destStr = match ($movement->destination) {
                                    'employee' => 'Directo a Empleado',
                                    'fund' => 'Fondo Cesantías',
                                    default => 'N/A',
                                };

                                $statusStr = match ($movement->status) {
                                    'pending' => 'Pendiente',
                                    'processed' => 'Procesado',
                                    'reported' => 'Reportado',
                                    default => 'Aplicado',
                                };

                                $statusColor = match ($movement->status) {
                                    'pending' => 'text-amber-600',
                                    'processed' => 'text-emerald-600',
                                    'reported' => 'text-indigo-600',
                                    default => 'text-blue-600',
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                    {{ $movement->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1.5">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase w-fit {{ $badgeColors }}">
                                            {{ \App\Models\BenefitLedger::movementTypeLabel($movement->movement_type) }}
                                        </span>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase w-fit {{ $sourceBadge }}">
                                            <i class="bi bi-diagram-2 mr-1"></i> {{ ucfirst($movement->source) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">
                                    {{ \App\Models\BenefitLedger::benefitTypeLabel($movement->benefit_type) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-semibold text-gray-700">
                                            <i class="bi bi-box-arrow-right mr-1 text-gray-400"></i> {{ $destStr }}
                                        </span>
                                        <span
                                            class="text-[11px] font-bold uppercase mt-0.5 flex items-center gap-1 {{ $statusColor }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $statusStr }}
                                        </span>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-black {{ $isNegative ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ $isNegative ? '-' : '+' }}${{ number_format(abs($movement->amount), 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="flex flex-col gap-2">
                                        <span class="max-w-xs truncate" title="{{ $movement->reference }}">
                                            {{ $movement->reference ?? '—' }}
                                        </span>
                                        @if(in_array($movement->movement_type, ['payment', 'withdrawal']) && $movement->amount < 0)
                                            <a href="javascript:void(0)" 
                                                onclick="openPdfModal('{{ route('provisiones.comprobante', $movement->id) }}', true)"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold text-[#1565C0] hover:text-[#0D47A1] transition-colors w-fit">
                                                <i class="bi bi-file-earmark-pdf-fill"></i> Comprobante
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $movements->links() }}
                </div>
            </div>
        @endif
    </div>

    @push('modals')
    {{-- PDF Preview Modal --}}
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden rounded-2xl">
                <div class="modal-header bg-[#1565C0] text-white py-4 px-6 border-0">
                    <h5 class="modal-title font-bold flex items-center gap-2" id="pdfModalLabel">
                        <i class="bi bi-file-earmark-text"></i> Vista Previa de Comprobante
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-gray-100" style="height: 80vh; position: relative;">
                    <div id="pdfLoader" class="absolute inset-0 flex flex-col items-center justify-center bg-white z-10">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="text-gray-500 font-medium">Cargando vista previa...</p>
                    </div>
                    <iframe id="pdfFrame" src="" class="w-full h-full border-0" onload="document.getElementById('pdfLoader').style.display='none'"></iframe>
                </div>
                <div class="modal-footer bg-white py-4 px-6 border-top border-gray-100 flex justify-between">
                    <button type="button" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-200 transition-all" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                    <div class="flex gap-3">
                        <a id="downloadBtn" href="#" class="px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-lg shadow-sm hover:bg-emerald-700 transition-all flex items-center gap-2">
                            <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openPdfModal(baseUrl, allowDownload) {
            const modalElement = document.getElementById('pdfModal');
            // Use getOrCreateInstance to avoid multiple backdrops and conflicts
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            
            const frame = document.getElementById('pdfFrame');
            const downloadBtn = document.getElementById('downloadBtn');
            const loader = document.getElementById('pdfLoader');
            
            // Set URLs
            const viewUrl = baseUrl + '?is_modal=1';
            const downloadUrl = baseUrl + '?format=pdf&mode=attachment';
            
            // Reset state
            loader.style.display = 'flex';
            frame.src = '';
            
            // Configure download button visibility
            if (allowDownload) {
                downloadBtn.style.display = 'flex';
                downloadBtn.href = downloadUrl;
            } else {
                downloadBtn.style.display = 'none';
            }
            
            // Load and show
            frame.src = viewUrl;
            modal.show();
        }
    </script>
    @endpush
@endsection