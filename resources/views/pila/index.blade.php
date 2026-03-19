@extends('layouts.app')

@section('title', 'Plantilla PILA')
@section('page-title', 'Plantilla PILA')

@push('styles')
@vite('resources/css/pila.css')
@endpush

@section('content')
@php
    $hasCalculo = (bool) ($hasCalculo ?? false);
    $planillaEstado = (string) ($planillaEstado ?? 'pendiente');
    $canGenerate = (bool) ($canGenerate ?? false);
    $archivoGenerado = (string) ($archivoGenerado ?? '');
    $salud = (float) ($totales['salud'] ?? 0);
    $pension = (float) ($totales['pension'] ?? 0);
    $arl = (float) ($totales['arl'] ?? 0);
    $caja = (float) ($totales['caja'] ?? 0);
    $granTotal = $salud + $pension + $arl + $caja;

    $pctSalud = $granTotal > 0 ? round(($salud / $granTotal) * 100, 1) : 0;
    $pctPension = $granTotal > 0 ? round(($pension / $granTotal) * 100, 1) : 0;
    $pctArl = $granTotal > 0 ? round(($arl / $granTotal) * 100, 1) : 0;
    $pctCaja = $granTotal > 0 ? max(0, round(100 - $pctSalud - $pctPension - $pctArl, 1)) : 0;

    $stepPeriodoEmpresa = $hasCalculo;
    $stepCalculo = $hasCalculo;
    $stepGenerada = strtolower($planillaEstado) === 'generada';
    $progress = $stepGenerada ? 100 : ($stepCalculo ? 75 : 10);
@endphp

<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->has('pila'))
        <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ $errors->first('pila') }}</span>
        </div>
    @endif

    @php $advertencias = $advertencias ?? []; @endphp
    @if(count($advertencias) > 0)
        <div class="alert alert-warning shadow-sm border-0 mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                <strong>{{ count($advertencias) }} advertencia(s) — revise antes de generar</strong>
                <button class="btn btn-sm btn-link p-0 ms-auto text-warning" type="button"
                    data-bs-toggle="collapse" data-bs-target="#collapseAdvertencias" aria-expanded="false">
                    Ver detalle <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="collapse mt-2" id="collapseAdvertencias">
                <ul class="mb-0 small ps-3">
                    @foreach($advertencias as $adv)
                        <li>{{ $adv }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#historialPilaModal">
            <i class="bi bi-clock-history me-1"></i>
            Ver historial
        </button>
    </div>

    <div class="card pila-card pila-filter-card mb-4">
        <div class="card-body p-4">
            <p class="pila-section-title mb-2">Seccion 1</p>
            <h3 class="h5 fw-bold mb-3 text-primary">Filtros de liquidacion</h3>

            <form method="GET" action="{{ route('pila.index') }}" class="row g-4 align-items-end">

    {{-- PERIODO --}}
    <div class="col-12 col-lg-4">
        <label for="id_periodo" class="form-label fw-semibold text-secondary small text-uppercase">
            Periodo de liquidación
        </label>

        <div class="input-group input-group-pila">
            <span class="input-group-text bg-light border-0">
                <i class="bi bi-calendar3"></i>
            </span>

            <select name="id_periodo" id="id_periodo" class="form-select border-0 shadow-sm" required>
                <option value="">Seleccione un periodo pendiente</option>

                @foreach($periodos as $periodo)
                    <option value="{{ $periodo->id_periodo }}"
                        {{ (int) $selectedPeriodoId === (int) $periodo->id_periodo ? 'selected' : '' }}>
                        {{ $periodo->fecha_inicio->format('Y-m-d') }}
                        a
                        {{ $periodo->fecha_fin->format('Y-m-d') }}
                        ({{ strtoupper($periodo->estado) }})
                    </option>
                @endforeach
            </select>
        </div>

        @if($periodos->isEmpty())
            <small class="text-danger mt-2 d-block">
                No hay periodos pendientes para la empresa actual.
            </small>
        @endif
    </div>


    {{-- EMPRESA --}}
    <div class="col-12 col-lg-4">
        <label for="empresa" class="form-label fw-semibold text-secondary small text-uppercase">
            Empresa
        </label>

        <div class="input-group">
            <span class="input-group-text bg-light border-0">
                <i class="bi bi-building"></i>
            </span>

            <input
                type="text"
                id="empresa"
                class="form-control border-0 shadow-sm"
                value="{{ ($empresaActual->razon_social ?? 'Empresa no disponible') . (($empresaActual->nit ?? null) ? ' - NIT ' . $empresaActual->nit : '') }}"
                readonly
            >
        </div>

        <input type="hidden" name="id_empresa" value="{{ $selectedEmpresaId }}">
    </div>


    {{-- BOTON --}}
    <div class="col-12 col-lg-4">
        @can('view_pila')
        <button type="submit" class="btn btn-primary w-100 py-2 btn-pila-main shadow-sm">
            <i class="bi bi-calculator-fill me-2"></i>
            Calcular Seguridad Social
        </button>
        @endcan
    </div>

</form>
        </div>
    </div>

    {{-- RESUMEN COMPACTO DE APORTES --}}
    <div class="card pila-card mb-4">
        <div class="card-body p-4">
            <p class="pila-section-title mb-3">Seccion 2 - RESUMEN DE APORTES</p>
            
            <div class="row g-0">
                <div class="col-6 col-lg-3 px-3 py-2 border-end border-light">
                    <p class="text-muted small mb-1"><i class="bi bi-heart-pulse text-primary me-1"></i>Salud</p>
                    <h5 class="fw-bold text-primary mb-0">${{ number_format($totales['salud'] ?? 0, 0, ',', '.') }}</h5>
                </div>
                <div class="col-6 col-lg-3 px-3 py-2 border-end border-light">
                    <p class="text-muted small mb-1"><i class="bi bi-person-vcard text-info me-1"></i>Pensión</p>
                    <h5 class="fw-bold text-info mb-0">${{ number_format($totales['pension'] ?? 0, 0, ',', '.') }}</h5>
                </div>
                <div class="col-6 col-lg-3 px-3 py-2 border-end border-light">
                    <p class="text-muted small mb-1"><i class="bi bi-shield-fill-check text-warning me-1"></i>ARL</p>
                    <h5 class="fw-bold text-warning mb-0">${{ number_format($totales['arl'] ?? 0, 0, ',', '.') }}</h5>
                </div>
                <div class="col-6 col-lg-3 px-3 py-2">
                    <p class="text-muted small mb-1"><i class="bi bi-bank text-success me-1"></i>Caja Comp.</p>
                    <h5 class="fw-bold text-success mb-0">${{ number_format($totales['caja'] ?? 0, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card pila-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
                <div>
                    <p class="pila-section-title mb-1">Seccion 3</p>
                    <h3 class="h5 mb-0 fw-bold text-dark">Detalle de empleados</h3>
                    <span class="badge rounded-pill badge-soft mt-2" id="employeesCountBadge">0 empleados</span>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row gap-3 align-items-stretch align-items-md-center">
                    <div class="input-group pila-search">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="employeeSearch" class="form-control" placeholder="Buscar por documento o nombre">
                    </div>

                    @if($stepGenerada)
                        <form method="GET" action="{{ route('pila.descargar') }}" id="downloadPilaForm" style="flex: 1;">
                            <input type="hidden" name="id_periodo" value="{{ $selectedPeriodoId }}">
                            <button type="submit" class="btn btn-primary btn-pila-generate w-100">
                                <i class="bi bi-download me-2"></i>
                                Descargar PILA
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-success btn-pila-generate flex-grow-1"
                            id="btnGenerarConfirm" {{ $canGenerate ? '' : 'disabled' }}
                            data-bs-toggle="modal" data-bs-target="#confirmGenerateModal">
                            <i class="bi bi-file-earmark-check-fill me-2"></i>
                            Generar PILA
                        </button>
                    @endif
                </div>

            @if($stepGenerada)
                <div class="alert alert-success border-0 py-2 px-3 small mb-3 mt-3 d-flex align-items-center gap-2">
                    <i class="bi bi-lock-fill"></i>
                    <span>Planilla generada y bloqueada. No se puede volver a generar para este periodo.</span>
                </div>
            @endif

            <div class="table-responsive border rounded-3">
                <table class="table pila-table table-hover align-middle mb-0" id="pilaTable">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Empleado</th>
                            <th class="text-end" style="background: linear-gradient(135deg, #0b5ed7 0%, #084298 100%); color: #fff;">IBC</th>
                            <th class="text-center text-nowrap">Niv. Riesgo</th>
                            <th class="text-end">Salud Empresa</th>
                            <th class="text-end">Pensión Empresa</th>
                            <th class="text-end text-nowrap">Valor ARL</th>
                            <th class="text-end">Caja Comp.</th>
                            <th class="text-center">Días</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detalles as $detalle)
                            @php
                                $ibc         = (float) ($detalle['ibc'] ?? $detalle['ibc_salud'] ?? 0);
                                $nivelRiesgo = (int)   ($detalle['nivel_riesgo_arl'] ?? 1);
                                $saludEmpresa   = (float) ($detalle['aporte_salud']   ?? 0);
                                $pensionEmpresa = (float) ($detalle['aporte_pension'] ?? 0);
                                $valorArl    = (float) ($detalle['valor_arl']    ?? $detalle['aporte_arl'] ?? 0);
                                $caja        = (float) ($detalle['aporte_caja']  ?? 0);
                            @endphp
                            <tr>
                                <td class="fw-semibold text-primary">{{ $detalle['doc_empleado'] }}</td>
                                <td class="fw-semibold" style="text-transform: uppercase;">{{ $detalle['empleado_nombre'] }}</td>
                                <td class="text-end fw-bold text-primary" style="background: rgba(13, 110, 253, 0.08); border-left: 3px solid #0d6efd;">${{ number_format($ibc, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        {{ $nivelRiesgo }}
                                    </span>
                                </td>
                                <td class="text-end">${{ number_format($saludEmpresa, 0, ',', '.') }}</td>
                                <td class="text-end">${{ number_format($pensionEmpresa, 0, ',', '.') }}</td>
                                <td class="text-end text-warning fw-semibold">${{ number_format($valorArl, 0, ',', '.') }}</td>
                                <td class="text-end">${{ number_format($caja, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $detalle['dias_cotizados'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-3 d-block mb-2"></i>
                                    {{ $hasCalculo
                                        ? 'No hay empleados con nómina registrada para los filtros seleccionados.'
                                        : 'Seleccione un periodo y presione Calcular Seguridad Social.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-3">
                <small class="text-muted" id="paginationInfo">Mostrando 0 registros</small>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="paginationList"></ul>
                </nav>
            </div>
        </div>
    </div>

</div>

@include('pila.partials.historial-pila-modal')

{{-- MODAL DE CONFIRMACIÓN PARA GENERAR PILA --}}
<div class="modal fade" id="confirmGenerateModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Generar Planilla PILA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-0 mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <span>Se generará el archivo PILA con los siguientes datos:</span>
                </div>
                
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Período:</span>
                            <span class="fw-semibold">{{ date('M Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Empleados:</span>
                            <span class="fw-semibold" id="confirmEmployeeCount">0</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="text-muted fw-bold">Total a Pagar:</span>
                            <span class="fw-bold text-success h5 mb-0">${{ number_format($granTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmAccept" required>
                    <label class="form-check-label" for="confirmAccept">
                        Confirmo que los datos son correctos y autorizo la generación de la planilla
                    </label>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ route('pila.generar') }}" id="confirmGenerateForm" class="d-inline">
                    @csrf
                    <input type="hidden" name="id_empresa" value="{{ $selectedEmpresaId }}">
                    <input type="hidden" name="id_periodo" value="{{ $selectedPeriodoId }}">
                    <button type="submit" class="btn btn-success btn-lg" id="btnConfirmGenerate" disabled>
                        <i class="bi bi-check-circle me-2"></i>
                        Confirmar y Generar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@vite('resources/js/pila.js')

<script>
    // Actualizar conteo de empleados en el modal de confirmación
    function updateEmployeeCount() {
        const tableRows = document.querySelectorAll('#pilaTable tbody tr');
        const count = tableRows.length > 0 && !tableRows[0].querySelector('[colspan]') ? tableRows.length : 0;
        document.getElementById('confirmEmployeeCount').textContent = count;
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateEmployeeCount();

        const modalEl = document.getElementById('confirmGenerateModal');
        const historialModalEl = document.getElementById('historialPilaModal');
        const confirmAccept = document.getElementById('confirmAccept');
        const btnConfirmGenerate = document.getElementById('btnConfirmGenerate');

        if (historialModalEl && historialModalEl.parentElement !== document.body) {
            document.body.appendChild(historialModalEl);
        }

        if (historialModalEl) {
            historialModalEl.addEventListener('shown.bs.modal', function() {
                historialModalEl.style.zIndex = '2005';

                const backdrops = document.querySelectorAll('.modal-backdrop');
                const lastBackdrop = backdrops.length ? backdrops[backdrops.length - 1] : null;
                if (lastBackdrop) {
                    lastBackdrop.style.zIndex = '2000';
                }
            });
        }

        if (!modalEl || !confirmAccept || !btnConfirmGenerate) {
            return;
        }

        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        // Habilitar/deshabilitar botón según checkbox
        confirmAccept.addEventListener('change', function() {
            btnConfirmGenerate.disabled = !this.checked;
        });

        // Forzar capas: backdrop debajo, modal arriba.
        modalEl.addEventListener('shown.bs.modal', function() {
            modalEl.style.zIndex = '2005';

            const backdrops = document.querySelectorAll('.modal-backdrop');
            const lastBackdrop = backdrops.length ? backdrops[backdrops.length - 1] : null;
            if (lastBackdrop) {
                lastBackdrop.style.zIndex = '2000';
            }
        });

        // Reset modal cuando se cierra (limpiar checkbox)
        modalEl.addEventListener('hidden.bs.modal', function() {
            confirmAccept.checked = false;
            btnConfirmGenerate.disabled = true;
        });
    });
</script>
@endpush
