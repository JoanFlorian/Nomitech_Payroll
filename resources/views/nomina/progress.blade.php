@php
$step = session('nomina.step', 1);
@endphp

<div class="mb-4">
    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
        <div class="h-2 rounded-full bg-blue-600 transition-all duration-300"
             style="width: {{ $step * 33 }}%">
        </div>
    </div>

    <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
        <span>Empleado</span>
        <span>Devengos</span>
        <span>Deducciones</span>
    </div>
</div>
