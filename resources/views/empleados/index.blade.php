@extends('layouts.app')

@section('title', 'Empleados')
@section('page-title', 'EMPLEADOS')

@section('content')

<div
    x-data="{ open: false, step: 1 }"
    @open-empleado-modal.window="open = true; step = 1"
>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gradient-to-r from-[#1565C0] to-[#1976D2] text-white">
                        <th class="py-4 px-6 font-semibold text-sm">Nombres y Apellidos</th>
                        <th class="py-4 px-6 font-semibold text-sm">N. Documento</th>
                        <th class="py-4 px-6 font-semibold text-sm">Tipo de Contrato</th>
                        <th class="py-4 px-6 font-semibold text-sm">Salario Neto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($usuarios as $usuario)
                    <?php 
                    $contrato = $usuario->contratos->first();
                    ?>
                    
                    <tr class="hover:bg-blue-50 transition duration-200">
                        <td class="py-4 px-6 text-gray-800 font-medium">{{$usuario->primer_nombre}} {{$usuario->primer_apellido}}</td>
                        <td class="py-4 px-6 text-gray-600">{{$usuario->doc}}</td>
                        {{-- <td class="py-4 px-6 text-gray-800">{{$usuario->contratos()->id_tipo_contrato}}</td> --}}
                        <td class="py-4 px-6 text-gray-600"><span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">{{ $contrato->id_tipo_contrato?? 'N/A' }}</span></td>
                        <td class="py-4 px-6 text-right font-semibold text-gray-800">{{ $contrato->salario_base?? 'N/A' }}</td>
                    </tr>

                        
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <p class="text-sm text-gray-600"><span class="font-semibold text-gray-800">{{ count($usuarios) }}</span> empleado(s)</p>
        </div>
    </div>

    {{-- BOTÓN FLOTANTE --}}
    <x-creardato />

    {{-- MODAL ÚNICO --}}
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
        <div
            @click.outside="open = false"
            class="bg-white rounded-xl shadow-xl w-full max-w-6xl max-h-[90vh] flex overflow-hidden"
        >

            {{-- SIDEBAR --}}
            <div class="w-1/3 bg-gradient-to-b from-[#1565C0] to-[#0D47A1] text-white p-10 flex flex-col justify-center">
                <h2 class="text-2xl font-bold">
                    Nomitech
                </h2>

                <p class="mt-4 text-sm text-blue-100">
                    Bienvenido al proceso de registro de empleados.
                </p>
            </div>


            {{-- CONTENIDO --}}
        <div class="w-2/3 p-10 overflow-y-auto">

            <div x-show="step === 1" x-cloak>
                @include('empleados.partials.inf_empleado')
            </div>

            <div x-show="step === 2" x-cloak>
                @include('empleados.partials.inf_contractual')
            </div>

            <div x-show="step === 3" x-cloak>
                @include('empleados.partials.inf_financiera')
            </div>

        </div>


        </div>
    </div>


</div>

@endsection
