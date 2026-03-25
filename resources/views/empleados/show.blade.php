@extends('layouts.app')

@section('title', 'Detalles del Empleado')
@section('page-title', 'DETALLES DEL EMPLEADO')

@section('content')
<div class="p-6 max-w-5xl mx-auto">

    {{-- NAVIGATION --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('empleados.index') }}"
           class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 text-sm font-medium transition">
            <i class="fas fa-arrow-left"></i> Volver a Empleados
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-[#1565C0] to-[#1976D2] px-8 py-6 text-white">
            <div class="flex items-center gap-5">
                <img id="avatar-preview"
                     src="{{ $usuario->avatar_url }}"
                     alt="Foto de {{ \Illuminate\Support\Str::title(trim(($usuario->primer_nombre ?? '') . ' ' . ($usuario->primer_apellido ?? ''))) }}"
                     class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md flex-shrink-0 cursor-pointer hover:shadow-lg hover:border-blue-300 transition-all duration-200"
                     data-full-src="{{ $usuario->avatar_url }}"
                     role="button"
                     tabindex="0"
                     title="Haz click para ampliar">
                <div>
                    <h1 class="text-2xl font-bold">
                        {{ \Illuminate\Support\Str::title(trim(
                            ($usuario->primer_nombre ?? '') . ' ' .
                            ($usuario->otros_nombres ?? '') . ' ' .
                            ($usuario->primer_apellido ?? '') . ' ' .
                            ($usuario->segundo_apellido ?? '')
                        )) }}
                    </h1>
                    <p class="text-blue-100 text-sm mt-1">
                        <span class="font-medium">Documento:</span> {{ $usuario->doc }}
                    </p>
                    @if($contrato)
                    <p class="text-blue-100 text-sm mt-0.5">
                        <span class="font-medium">Tipo de Contrato:</span>
                        {{ $contrato->tipoContrato->nombre ?? '—' }}
                    </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- BODY --}}
        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-10">

            {{-- DATOS PERSONALES --}}
            <section>
                <h2 class="text-base font-bold text-gray-800 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-blue-600"></i> Datos Personales
                </h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Tipo de Documento</dt>
                        <dd class="text-gray-800 text-right">{{ $tipoDoc->nombre ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">N.º Documento</dt>
                        <dd class="text-gray-800 font-mono text-right">{{ $usuario->doc }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Primer Nombre</dt>
                        <dd class="text-gray-800 text-right">{{ \Illuminate\Support\Str::title($usuario->primer_nombre ?? '—') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Otros Nombres</dt>
                        <dd class="text-gray-800 text-right">{{ \Illuminate\Support\Str::title($usuario->otros_nombres ?? '—') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Primer Apellido</dt>
                        <dd class="text-gray-800 text-right">{{ \Illuminate\Support\Str::title($usuario->primer_apellido ?? '—') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Segundo Apellido</dt>
                        <dd class="text-gray-800 text-right">{{ \Illuminate\Support\Str::title($usuario->segundo_apellido ?? '—') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Ciudad</dt>
                        <dd class="text-gray-800 text-right">{{ $usuario->ciudad->nombre ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Dirección</dt>
                        <dd class="text-gray-800 text-right">{{ $usuario->direccion ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Correo</dt>
                        <dd class="text-gray-800 text-right break-all">{{ $usuario->correo ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Teléfono</dt>
                        <dd class="text-gray-800 text-right">{{ $usuario->telefono ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Rol del sistema</dt>
                        <dd class="text-gray-800 text-right">{{ $usuario->rol->nombre ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- DATOS DEL CONTRATO --}}
            <section>
                <h2 class="text-base font-bold text-gray-800 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                    <i class="fas fa-file-contract text-blue-600"></i> Datos del Contrato
                </h2>

                @if($contrato)
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Tipo de Contrato</dt>
                        <dd class="text-gray-800 text-right">{{ $contrato->tipoContrato->nombre ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Tipo de Trabajador</dt>
                        <dd class="text-gray-800 text-right">{{ $contrato->tipoTrabajador->nombre ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Salario Base</dt>
                        <dd class="text-gray-800 text-right font-semibold">
                            $ {{ number_format((float)($contrato->salario_base ?? 0), 0, ',', '.') }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Fecha de Inicio</dt>
                        <dd class="text-gray-800 text-right">
                            {{ $contrato->fecha_inicio ? $contrato->fecha_inicio->format('d/m/Y') : '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Fecha de Fin</dt>
                        <dd class="text-gray-800 text-right">
                            {{ $contrato->fecha_fin ? $contrato->fecha_fin->format('d/m/Y') : '(Indefinido)' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Horas Diarias</dt>
                        <dd class="text-gray-800 text-right">{{ $contrato->horas_diarias ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Nivel de Riesgo</dt>
                        <dd class="text-gray-800 text-right">{{ $contrato->nivelRiesgo->nombre ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Alto Riesgo</dt>
                        <dd class="text-right">
                            @if($contrato->alto_riesgo)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Sí</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">No</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">ARL</dt>
                        <dd class="text-gray-800 text-right">{{ $contrato->arl->nombre ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">EPS</dt>
                        <dd class="text-gray-800 text-right">{{ $contrato->eps->nombre ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">AFP</dt>
                        <dd class="text-gray-800 text-right">{{ $contrato->afp->nombre ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Forma de Pago</dt>
                        <dd class="text-gray-800 text-right">{{ $contrato->formaPago->nombre ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Estado</dt>
                        <dd class="text-right">
                            @php $estado = $contrato->estado_dinamico ?? 'ACTIVO'; @endphp
                            <span class="inline-block px-2 py-1 rounded-full text-xs font-bold
                                @if($estado === 'ACTIVO') bg-green-100 text-green-800
                                @elseif($estado === 'POR_VENCER') bg-amber-100 text-amber-800
                                @elseif($estado === 'VENCIDO') bg-red-100 text-red-800
                                @elseif($estado === 'TERMINADO') bg-gray-200 text-gray-600
                                @elseif($estado === 'PROGRAMADO') bg-sky-100 text-sky-800
                                @else bg-gray-100 text-gray-600
                                @endif">
                                {{ $estado }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 font-medium shrink-0">Código Interno</dt>
                        <dd class="text-gray-800 text-right font-mono">{{ $contrato->codigo_interno ?? '—' }}</dd>
                    </div>
                </dl>
                @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <i class="fas fa-file-times text-4xl text-orange-300 mb-3"></i>
                    <p class="text-gray-500 italic text-sm">Este empleado no tiene un contrato registrado.</p>
                </div>
                @endif
            </section>

        </div>{{-- /grid --}}
    </div>

    <!-- Modal para ampliación de foto -->
    <div id="avatar-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header del modal -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Foto de Empleado</h3>
                <button id="close-avatar-modal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none transition">
                    ×
                </button>
            </div>
            
            <!-- Contenido del modal -->
            <div class="p-6 flex flex-col items-center justify-center">
                <img id="avatar-modal-img" src="" alt="Foto ampliada" class="w-full max-w-sm rounded-xl shadow-lg object-cover">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Modal de ampliación de foto
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarModal = document.getElementById('avatar-modal');
    const avatarModalImg = document.getElementById('avatar-modal-img');
    const closeAvatarModal = document.getElementById('close-avatar-modal');

    // Abrir modal al hacer click en la imagen
    if (avatarPreview) {
        avatarPreview.addEventListener('click', function () {
            const imageSrc = this.getAttribute('data-full-src') || this.src;
            avatarModalImg.src = imageSrc;
            avatarModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });

        // Abrir modal con Enter o Space
        avatarPreview.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const imageSrc = this.getAttribute('data-full-src') || this.src;
                avatarModalImg.src = imageSrc;
                avatarModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        });
    }

    // Cerrar modal
    function closeModal() {
        avatarModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    if (closeAvatarModal) {
        closeAvatarModal.addEventListener('click', closeModal);
    }

    // Cerrar modal al hacer click en el fondo
    if (avatarModal) {
        avatarModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }

    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !avatarModal.classList.contains('hidden')) {
            closeModal();
        }
    });
</script>
@endpush

@endsection
