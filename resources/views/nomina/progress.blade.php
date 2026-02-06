@php
$step = session('nomina.step', 1);
@endphp

<div class="mb-4">
    <div class="progress" style="height:8px">
        <div class="progress-bar bg-primary"
             style="width: {{ $step * 33 }}%">
        </div>
    </div>

    <div class="d-flex justify-content-between mt-2 small text-muted">
        <span>Empleado</span>
        <span>Devengos</span>
        <span>Deducciones</span>
    </div>
</div>
