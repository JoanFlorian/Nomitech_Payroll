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
        <button type="submit" class="btn btn-primary w-100 py-2 btn-pila-main shadow-sm">
            <i class="bi bi-calculator-fill me-2"></i>
            Calcular Seguridad Social
        </button>
    </div>

</form>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <p class="pila-section-title mb-1">Seccion 2</p>
            <h3 class="h5 mb-0 fw-bold text-dark">Resumen de aportes</h3>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card pila-card kpi-card kpi-salud h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-primary-subtle text-primary">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Total Salud</p>
                        <h4 class="mb-0 fw-bold">${{ number_format($totales['salud'] ?? 0, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card pila-card kpi-card kpi-pension h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-info-subtle text-info">
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Total Pension</p>
                        <h4 class="mb-0 fw-bold">${{ number_format($totales['pension'] ?? 0, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card pila-card kpi-card kpi-arl h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-warning-subtle text-warning">
                        <i class="bi bi-shield-fill-check"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Total ARL</p>
                        <h4 class="mb-0 fw-bold">${{ number_format($totales['arl'] ?? 0, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card pila-card kpi-card kpi-caja h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-success-subtle text-success">
                        <i class="bi bi-bank"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Total Caja Compensacion</p>
                        <h4 class="mb-0 fw-bold">${{ number_format($totales['caja'] ?? 0, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card pila-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="pila-section-title mb-1">Analitica</p>
                            <h4 class="h6 fw-bold mb-0">Composicion de aportes</h4>
                        </div>
                        @if($hasCalculo)
                            <span class="badge bg-primary-subtle text-primary">Total: ${{ number_format($granTotal, 2, ',', '.') }}</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Sin calculo</span>
                        @endif
                    </div>

                    @if(!$hasCalculo)
                        <div class="alert alert-light border mb-3 py-2 px-3 small text-muted">
                            Seleccione un periodo y presione Calcular Seguridad Social para visualizar la composicion de aportes.
                        </div>
                    @endif

                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-5">
                            <div
                                class="ring-chart"
                                style="background: {{ $hasCalculo ? 'conic-gradient(#0d6efd 0 ' . $pctSalud . '%, #0dcaf0 ' . $pctSalud . '% ' . ($pctSalud + $pctPension) . '%, #fd7e14 ' . ($pctSalud + $pctPension) . '% ' . ($pctSalud + $pctPension + $pctArl) . '%, #198754 ' . ($pctSalud + $pctPension + $pctArl) . '% 100%)' : 'conic-gradient(#e9ecef 0% 100%)' }};"
                            >
                                <div class="ring-center">
                                    <div class="small text-muted">Total aportes</div>
                                    <div class="fw-bold">{{ $hasCalculo ? '100%' : '0%' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-7">
                            <div class="metric-row">
                                <span class="small fw-semibold"><span class="legend-chip me-1" style="background:#0d6efd;"></span>Salud</span>
                                <div class="metric-track"><div class="metric-fill" style="width: {{ $pctSalud }}%; background:#0d6efd;"></div></div>
                                <span class="small text-end fw-semibold">{{ $pctSalud }}%</span>
                            </div>
                            <div class="metric-row">
                                <span class="small fw-semibold"><span class="legend-chip me-1" style="background:#0dcaf0;"></span>Pension</span>
                                <div class="metric-track"><div class="metric-fill" style="width: {{ $pctPension }}%; background:#0dcaf0;"></div></div>
                                <span class="small text-end fw-semibold">{{ $pctPension }}%</span>
                            </div>
                            <div class="metric-row">
                                <span class="small fw-semibold"><span class="legend-chip me-1" style="background:#fd7e14;"></span>ARL</span>
                                <div class="metric-track"><div class="metric-fill" style="width: {{ $pctArl }}%; background:#fd7e14;"></div></div>
                                <span class="small text-end fw-semibold">{{ $pctArl }}%</span>
                            </div>
                            <div class="metric-row mb-0">
                                <span class="small fw-semibold"><span class="legend-chip me-1" style="background:#198754;"></span>Caja</span>
                                <div class="metric-track"><div class="metric-fill" style="width: {{ $pctCaja }}%; background:#198754;"></div></div>
                                <span class="small text-end fw-semibold">{{ $pctCaja }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card pila-card h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="pila-section-title mb-1">Workflow</p>
                            <h4 class="h6 fw-bold mb-0">Estado de la planilla</h4>
                        </div>
                        <span class="badge bg-success-subtle text-success">{{ $progress }}%</span>
                    </div>

                    <div class="plan-progress-wrap mb-3">
                        <div class="plan-progress-bar mb-2">
                            <div class="plan-progress-fill" style="width: {{ $progress }}%;"></div>
                        </div>
                        <small class="text-muted">Flujo: seleccion de filtros, calculo y generacion de planilla.</small>
                    </div>

                    <div class="d-grid gap-2 mt-auto">
                        <div class="d-flex align-items-center justify-content-between small">
                            <span><i class="bi {{ $stepPeriodoEmpresa ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }} me-2"></i>Filtros seleccionados</span>
                            <span class="fw-semibold">{{ $stepPeriodoEmpresa ? 'OK' : 'Pendiente' }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between small">
                            <span><i class="bi {{ $stepCalculo ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }} me-2"></i>Calculo de aportes</span>
                            <span class="fw-semibold">{{ $stepCalculo ? 'OK' : 'Pendiente' }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between small">
                            <span><i class="bi {{ $stepGenerada ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }} me-2"></i>Planilla generada</span>
                            <span class="fw-semibold">{{ $stepGenerada ? 'OK' : 'Pendiente' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card pila-card">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <p class="pila-section-title mb-1">Seccion 3</p>
                    <h3 class="h5 mb-0 fw-bold text-dark">Tabla de empleados</h3>
                </div>
            </div>

            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-3">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h3 class="h6 fw-bold text-primary mb-0 me-2">Detalle de empleados</h3>
                    <span class="badge rounded-pill badge-soft" id="employeesCountBadge">0 empleados</span>
                </div>

                <div class="d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center">
                    <div class="input-group pila-search" style="min-width: 260px;">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="employeeSearch" class="form-control" placeholder="Buscar por documento o nombre">
                    </div>

                    <form method="POST" action="{{ route('pila.generar') }}" id="generatePilaForm">
                        @csrf
                        <input type="hidden" name="id_empresa" value="{{ $selectedEmpresaId }}">
                        <input type="hidden" name="id_periodo" value="{{ $selectedPeriodoId }}">

                        <button type="submit" class="btn btn-success btn-lg btn-pila-generate" {{ $canGenerate ? '' : 'disabled' }}>
                            <i class="bi bi-file-earmark-check-fill me-2"></i>
                            Generar Planilla PILA
                        </button>
                    </form>

                    @if($stepGenerada)
                        <form method="GET" action="{{ route('pila.descargar') }}" id="downloadPilaForm">
                            <input type="hidden" name="id_periodo" value="{{ $selectedPeriodoId }}">

                            <button type="submit" class="btn btn-outline-primary btn-lg" {{ $hasCalculo && $detalles->isNotEmpty() ? '' : 'disabled' }}>
                                <i class="bi bi-download me-2"></i>
                                Descargar Planilla PILA
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if($hasCalculo && !$canGenerate && $stepGenerada)
                <div class="alert alert-info border-0 py-2 px-3 small mb-3">
                    Esta planilla ya fue generada para el periodo seleccionado y no se detectaron cambios en los datos.
                    @if($archivoGenerado !== '')
                        <br>
                        Archivo generado: {{ $archivoGenerado }}
                    @endif
                </div>
            @endif

            <div class="table-responsive border rounded-3">
                <table class="table pila-table table-hover align-middle mb-0" id="pilaTable">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Empleado</th>
                            <th class="text-end">IBC</th>
                            <th class="text-end">Salud Empresa</th>
                            <th class="text-end">Pension Empresa</th>
                            <th class="text-end">ARL</th>
                            <th class="text-end">Caja Compensacion</th>
                            <th class="text-center">Dias Cotizados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detalles as $detalle)
                            @php
                                $ibc = (float) ($detalle['ibc_salud'] ?? 0);
                                $saludEmpresa = round($ibc * 0.085, 2);
                                $pensionEmpresa = round($ibc * 0.12, 2);
                                $arl = (float) ($detalle['aporte_arl'] ?? 0);
                                $caja = (float) ($detalle['aporte_caja'] ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $detalle['doc_empleado'] }}</td>
                                <td class="fw-semibold">{{ $detalle['empleado_nombre'] }}</td>
                                <td class="text-end fw-semibold">${{ number_format($ibc, 2, ',', '.') }}</td>
                                <td class="text-end">${{ number_format($saludEmpresa, 2, ',', '.') }}</td>
                                <td class="text-end">${{ number_format($pensionEmpresa, 2, ',', '.') }}</td>
                                <td class="text-end">${{ number_format($arl, 2, ',', '.') }}</td>
                                <td class="text-end">${{ number_format($caja, 2, ',', '.') }}</td>
                                <td class="text-center">{{ $detalle['dias_cotizados'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-3 d-block mb-2"></i>
                                    {{ $hasCalculo
                                        ? 'No hay empleados activos para los filtros seleccionados.'
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
@endsection

@push('scripts')
@vite('resources/js/pila.js')
@endpush
