@extends('layouts.app')

@section('title', 'Empleados')
@section('page-title', 'EMPLEADOS')

@section('content')
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
        <tr class="border-b border-gray-200 hover:bg-gray-50">
            <td class="py-4 px-6 text-gray-800">Ana María López</td>
            <td class="py-4 px-6 text-gray-800">1023456789</td>
            <td class="py-4 px-6 text-gray-800">Término Fijo</td>
            <td class="py-4 px-6 text-gray-800">$2,500,000</td>
        </tr>
        <!-- más filas -->
    </tbody>
</table>
@endsection
