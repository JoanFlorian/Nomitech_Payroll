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
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user-tie text-3xl"></i>
                </div>
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
                        <dd class="text-gray-800 text-right">{{ $contrato->nivel_riesgo ?? '—' }}</dd>
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
</div>
@endsection
