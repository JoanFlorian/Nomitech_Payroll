@extends('layouts.app')

@section('title', 'Dashboard del Trabajador')
@section('hide-layout-header', '1')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">
                    Bienvenido, {{ $usuario->nombre_completo }}
                </h1>
                <p class="text-gray-600">Portal del Trabajador - Nomitech</p>
            </div>

            <div class="flex items-center justify-end">
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-right shadow-sm">
                    <p class="text-sm font-semibold text-gray-800">{{ $usuario->nombre_completo }}</p>
                    <p class="text-xs text-gray-500">{{ $usuario->rol->nombre ?? 'Trabajador' }}</p>
                </div>
            </div>
        </div>

        <!-- Mensajes Flash -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
                <div class="flex">
                    <span class="material-icons text-green-500 mr-3">check_circle</span>
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Tarjetas de Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Último Pago -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="material-icons text-blue-600">calendar_today</span>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">Último Pago</h3>
                <p class="text-2xl font-bold text-gray-800">{{ $ultimoPago['fecha'] }}</p>
            </div>

            <!-- Total Devengado -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="material-icons text-green-600">trending_up</span>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">Total Devengado</h3>
                <p class="text-2xl font-bold text-green-600">${{ number_format($ultimoPago['total_devengado'], 0, ',', '.') }}</p>
            </div>

            <!-- Total Deducciones -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <span class="material-icons text-orange-600">trending_down</span>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">Total Deducciones</h3>
                <p class="text-2xl font-bold text-orange-600">${{ number_format($ultimoPago['total_deducciones'], 0, ',', '.') }}</p>
            </div>

            <!-- Neto Pagado -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="material-icons text-purple-600">account_balance_wallet</span>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">Neto Pagado</h3>
                <p class="text-2xl font-bold text-purple-600">${{ number_format($ultimoPago['neto_pagado'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Fila inferior: Perfil laboral + Desglose salarial -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- ── TARJETA PERFIL LABORAL (3 columnas) ── --}}
            <div class="lg:col-span-3 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Header degradado -->
                <div class="bg-gradient-to-r from-[#1565C0] to-[#1976D2] px-6 pt-6 pb-10 relative">
                    <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Mi información laboral</p>
                    <h2 class="text-white text-xl font-bold">{{ $usuario->nombre_completo }}</h2>
                    <p class="text-blue-100 text-sm mt-0.5">{{ $usuario->rol->nombre ?? 'Trabajador' }}</p>
                    <!-- Avatar flotante -->
                    <div class="absolute -bottom-8 left-6 w-16 h-16 rounded-2xl bg-white shadow-lg flex items-center justify-center border-4 border-white">
                        <span class="text-2xl font-bold text-[#1565C0]">
                            {{ strtoupper(substr($usuario->primer_nombre ?? 'T', 0, 1)) }}{{ strtoupper(substr($usuario->primer_apellido ?? '', 0, 1)) }}
                        </span>
                    </div>
                </div>

                <!-- Datos -->
                <div class="pt-12 px-6 pb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="material-icons text-[#1565C0] text-base">badge</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Código interno</p>
                                <p class="text-sm font-semibold text-gray-700">{{ $usuario->codigo_interno ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="material-icons text-green-600 text-base">payments</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Salario base</p>
                                <p class="text-sm font-semibold text-gray-700">${{ number_format($usuario->salario_base ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="material-icons text-orange-500 text-base">event</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Fecha de ingreso</p>
                                <p class="text-sm font-semibold text-gray-700">
                                    {{ $fechaInicioTrabajador ? \Carbon\Carbon::parse($fechaInicioTrabajador)->format('d/m/Y') : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="material-icons text-purple-500 text-base">schedule</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Horas diarias</p>
                                <p class="text-sm font-semibold text-gray-700">{{ $usuario->horas_diarias ?? 8 }} horas</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="material-icons text-teal-500 text-base">email</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Correo</p>
                                <p class="text-sm font-semibold text-gray-700 truncate max-w-[140px]">{{ $usuario->correo ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="material-icons text-pink-500 text-base">phone</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium">Teléfono</p>
                                <p class="text-sm font-semibold text-gray-700">{{ $usuario->telefono ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Botón editar perfil -->
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <a href="{{ route('trabajador.perfil') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-[#1565C0] hover:bg-[#0D47A1] text-white text-sm font-medium rounded-lg transition-colors">
                            <span class="material-icons text-base">edit</span>
                            Actualizar mi información
                        </a>
                    </div>
                </div>
            </div>

            {{-- ── COLUMNA DERECHA (2 columnas) ── --}}
            <div class="lg:col-span-2 flex flex-col gap-6">

                {{-- Desglose del último pago --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 flex-1">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="material-icons text-[#1565C0]">pie_chart</span>
                        <h3 class="font-bold text-gray-800 text-base">Desglose último pago</h3>
                    </div>

                    @php
                        $devengado   = $ultimoPago['total_devengado'] ?? 0;
                        $deducciones = $ultimoPago['total_deducciones'] ?? 0;
                        $neto        = $ultimoPago['neto_pagado'] ?? 0;
                        $pctDed      = $devengado > 0 ? round(($deducciones / $devengado) * 100) : 0;
                        $pctNeto     = 100 - $pctDed;
                    @endphp

                    <!-- Barra visual -->
                    <div class="h-4 rounded-full overflow-hidden bg-gray-100 mb-4 flex">
                        <div class="bg-green-500 transition-all duration-700" style="width: {{ $pctNeto }}%"></div>
                        <div class="bg-orange-400 transition-all duration-700" style="width: {{ $pctDed }}%"></div>
                    </div>
                    <div class="flex gap-4 text-xs text-gray-500 mb-5">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>Neto ({{ $pctNeto }}%)</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span>Deducciones ({{ $pctDed }}%)</span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <span class="text-sm text-gray-500 flex items-center gap-2">
                                <span class="material-icons text-green-500 text-base">add_circle_outline</span>Total devengado
                            </span>
                            <span class="text-sm font-bold text-green-600">${{ number_format($devengado, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <span class="text-sm text-gray-500 flex items-center gap-2">
                                <span class="material-icons text-orange-500 text-base">remove_circle_outline</span>Deducciones
                            </span>
                            <span class="text-sm font-bold text-orange-500">-${{ number_format($deducciones, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-700 font-semibold flex items-center gap-2">
                                <span class="material-icons text-[#1565C0] text-base">account_balance_wallet</span>Neto pagado
                            </span>
                            <span class="text-base font-extrabold text-[#1565C0]">${{ number_format($neto, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Antigüedad --}}
                @php
                    $fechaIngreso = $fechaInicioTrabajador ? \Carbon\Carbon::parse($fechaInicioTrabajador) : null;
                    $intervaloAntiguedad = $fechaIngreso ? $fechaIngreso->diff(now()) : null;
                    $anios = $intervaloAntiguedad ? (int) $intervaloAntiguedad->y : null;
                    $meses = $intervaloAntiguedad ? (int) $intervaloAntiguedad->m : null;
                    $dias = $intervaloAntiguedad ? (int) $intervaloAntiguedad->d : null;
                @endphp
                <div class="bg-gradient-to-br from-[#1565C0] to-[#1976D2] rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-icons text-blue-200">workspace_premium</span>
                        <h3 class="font-bold text-base">Mi antigüedad</h3>
                    </div>
                    @if($fechaIngreso)
                        <div class="flex gap-4">
                            <div class="text-center">
                                <p class="text-3xl font-extrabold">{{ $anios }}</p>
                                <p class="text-blue-200 text-xs mt-0.5">{{ $anios === 1 ? 'año' : 'años' }}</p>
                            </div>
                            <div class="w-px bg-blue-400/40 self-stretch"></div>
                            <div class="text-center">
                                <p class="text-3xl font-extrabold">{{ $meses }}</p>
                                <p class="text-blue-200 text-xs mt-0.5">{{ $meses === 1 ? 'mes' : 'meses' }}</p>
                            </div>
                            <div class="w-px bg-blue-400/40 self-stretch"></div>
                            <div class="text-center">
                                <p class="text-3xl font-extrabold">{{ $dias }}</p>
                                <p class="text-blue-200 text-xs mt-0.5">{{ $dias === 1 ? 'día' : 'días' }}</p>
                            </div>
                        </div>
                        <p class="text-blue-200 text-xs mt-4">
                            Desde el {{ $fechaIngreso->format('d/m/Y') }}
                        </p>
                    @else
                        <p class="text-blue-200 text-sm">Fecha de ingreso no registrada</p>
                    @endif
                </div>

            </div>{{-- fin columna derecha --}}
        </div>{{-- fin fila inferior --}}

    </div>
</div>
@endsection
