@extends('layouts.app')

@section('title', 'Empleados')
@section('page-title', 'EMPLEADOS')

@section('content')

<div
    x-data="{ open: false, step: 1 }"
    @open-empleado-modal.window="open = true; step = 1"
>

    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-gray-200">
                <th class="py-4 px-6 text-gray-500 font-semibold uppercase text-sm">Nombres y Apellidos</th>
                <th class="py-4 px-6 text-gray-500 font-semibold uppercase text-sm">N. Documento</th>
                <th class="py-4 px-6 text-gray-500 font-semibold uppercase text-sm">Tipo de Contrato</th>
                <th class="py-4 px-6 text-gray-500 font-semibold uppercase text-sm">Salario Neto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $usuario)
            <?php 
            $contrato = $usuario->contratos->first();
            ?>
            
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <td class="py-4 px-6 text-gray-800">{{$usuario->primer_nombre}} {{$usuario->primer_apellido}}</td>
                <td class="py-4 px-6 text-gray-800">{{$usuario->doc}}</td>
                {{-- <td class="py-4 px-6 text-gray-800">{{$usuario->contratos()->id_tipo_contrato}}</td> --}}
                <td class="py-4 px-6 text-gray-800">{{ $contrato->id_tipo_contrato?? 'N/A' }}</td>
                <td class="py-4 px-6 text-gray-800">{{ $contrato->salario_base?? 'N/A' }}</td>
            </tr>

                
            @endforeach
        </tbody>
    </table>

    {{-- BOTÓN FLOTANTE --}}
    <x-creardato />

    {{-- MODAL ÚNICO --}}
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
        <div
            @click.outside="open = false"
            class="bg-white rounded-xl shadow-lg w-full max-w-6xl h-[90vh] flex overflow-hidden"
        >

            {{-- SIDEBAR --}}
        <div class="w-1/3 h-full bg-blue-600 text-white p-10 flex flex-col justify-center">
            <h2 class="text-2xl font-bold">
                Nomitech
            </h2>

            <p class="mt-4 text-sm">
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
