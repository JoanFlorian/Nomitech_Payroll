@extends('layouts.superadmin')

@section('content')
    <div class="p-6 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">

        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

            <!-- Header -->
            <div class="mb-8 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Crear nuevo plan</h2>
                    <p class="text-sm text-gray-500">
                        Configura los parámetros del nuevo paquete de licenciamiento.
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('superadmin.planes.store') }}" class="space-y-8">

                @include('superadmin.planes._form')

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-6 border-t">
                    <a href="{{ route('superadmin.planes.index') }}"
                        class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-100 transition">
                        Cancelar
                    </a>

                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-medium
                                    hover:bg-blue-700 shadow-sm hover:shadow transition">
                        Guardar plan
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection