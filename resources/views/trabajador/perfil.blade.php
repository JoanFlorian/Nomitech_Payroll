@extends('layouts.app')

@section('title', 'Mi Perfil')
@section('hide-layout-header', '1')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 p-4 md:p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Mi Perfil</h1>
                <p class="text-gray-600">Actualiza tu información personal</p>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('trabajador.dashboard') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="material-icons mr-2 text-sm">arrow_back</span>
                    Volver
                </a>

                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <img src="{{ $usuario->avatar_url }}"
                             alt="Foto de perfil"
                             class="w-12 h-12 rounded-full object-cover border border-gray-200">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-800">{{ $usuario->nombre_completo ?? (($usuario->primer_nombre ?? 'Usuario') . ' ' . ($usuario->primer_apellido ?? '')) }}</p>
                            <p class="text-xs text-gray-500">{{ $usuario->rol->nombre ?? 'Trabajador' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensajes Flash -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm animate-fade-in">
                <div class="flex">
                    <span class="material-icons text-green-500 mr-3">check_circle</span>
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <span class="material-icons text-red-500 mr-3 mt-0.5">error</span>
                    <div class="flex-1">
                        <p class="text-red-800 font-medium mb-2">Por favor corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-6 bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center pb-3 border-b-2 border-indigo-500">
                <span class="material-icons mr-2 text-indigo-600">photo_camera</span>
                Foto de Perfil
            </h2>

            <div class="flex flex-col md:flex-row md:items-center gap-5">
                <img id="avatar-preview"
                     src="{{ $usuario->avatar_url }}"
                     alt="Avatar actual"
                     class="w-24 h-24 rounded-full object-cover border-2 border-gray-200 shadow-sm cursor-pointer hover:shadow-lg hover:border-indigo-400 transition-all duration-200"
                     data-full-src="{{ $usuario->avatar_url }}"
                     role="button"
                     tabindex="0"
                     title="Haz click para ampliar">

                <form action="{{ route('trabajador.perfil.foto') }}" method="POST" enctype="multipart/form-data" class="flex-1">
                    @csrf
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <input type="file"
                               id="avatar"
                               name="avatar"
                               accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                               class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                            Actualizar foto
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo: 2MB.</p>
                    @error('avatar')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </form>
            </div>
        </div>

        <!-- Formulario de Perfil -->
        <form action="{{ route('trabajador.perfil.actualizar') }}" method="POST" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8">
            @csrf

            <!-- Información Personal -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center pb-3 border-b-2 border-blue-500">
                    <span class="material-icons mr-2 text-blue-600">person</span>
                    Información Personal
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Primer Nombre -->
                    <div>
                        <label for="primer_nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                            Primer Nombre <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="primer_nombre" 
                            name="primer_nombre" 
                            value="{{ old('primer_nombre', $usuario->primer_nombre) }}"
                            class="nombre-campo w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition @error('primer_nombre') border-red-500 @enderror"
                            required
                        >
                        @error('primer_nombre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Otros Nombres -->
                    <div>
                        <label for="otros_nombres" class="block text-sm font-semibold text-gray-700 mb-2">
                            Otros Nombres
                        </label>
                        <input 
                            type="text" 
                            id="otros_nombres" 
                            name="otros_nombres" 
                            value="{{ old('otros_nombres', $usuario->otros_nombres) }}"
                            class="nombre-campo w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition @error('otros_nombres') border-red-500 @enderror"
                        >
                        @error('otros_nombres')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Primer Apellido -->
                    <div>
                        <label for="primer_apellido" class="block text-sm font-semibold text-gray-700 mb-2">
                            Primer Apellido <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="primer_apellido" 
                            name="primer_apellido" 
                            value="{{ old('primer_apellido', $usuario->primer_apellido) }}"
                            class="nombre-campo w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition @error('primer_apellido') border-red-500 @enderror"
                            required
                        >
                        @error('primer_apellido')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Segundo Apellido -->
                    <div>
                        <label for="segundo_apellido" class="block text-sm font-semibold text-gray-700 mb-2">
                            Segundo Apellido
                        </label>
                        <input 
                            type="text" 
                            id="segundo_apellido" 
                            name="segundo_apellido" 
                            value="{{ old('segundo_apellido', $usuario->segundo_apellido) }}"
                            class="nombre-campo w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition @error('segundo_apellido') border-red-500 @enderror"
                        >
                        @error('segundo_apellido')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center pb-3 border-b-2 border-green-500">
                    <span class="material-icons mr-2 text-green-600">contact_phone</span>
                    Información de Contacto
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tipo de Documento -->
                    <div>
                        <label for="id_tipo_doc" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tipo de Documento <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_tipo_doc" 
                            name="id_tipo_doc" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition @error('id_tipo_doc') border-red-500 @enderror"
                            required
                        >
                            <option value="1" {{ old('id_tipo_doc', $usuario->id_tipo_doc) == 1 ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                            <option value="2" {{ old('id_tipo_doc', $usuario->id_tipo_doc) == 2 ? 'selected' : '' }}>Cédula de Extranjería</option>
                            <option value="3" {{ old('id_tipo_doc', $usuario->id_tipo_doc) == 3 ? 'selected' : '' }}>Pasaporte</option>
                        </select>
                        @error('id_tipo_doc')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Número de Documento -->
                    <div>
                        <label for="numero_documento" class="block text-sm font-semibold text-gray-700 mb-2">
                            Número de Documento
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="numero_documento" 
                                value="{{ $usuario->doc }}"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed select-none"
                                readonly
                                tabindex="-1"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2">
                                <span class="material-icons text-gray-400 text-base">lock</span>
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Este campo no puede modificarse. Contacta a Recursos Humanos si hay un error.</p>
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="block text-sm font-semibold text-gray-700 mb-2">
                            Teléfono <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="telefono" 
                            name="telefono" 
                            value="{{ old('telefono', $usuario->telefono) }}"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition @error('telefono') border-red-500 @enderror"
                            required
                            pattern="[0-9]{7,15}"
                            title="Mínimo 7 dígitos"
                        >
                        @error('telefono')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Correo Electrónico -->
                    <div>
                        <label for="correo" class="block text-sm font-semibold text-gray-700 mb-2">
                            Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="correo" 
                            name="correo" 
                            value="{{ old('correo', $usuario->correo) }}"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition @error('correo') border-red-500 @enderror"
                            required
                        >
                        @error('correo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dirección -->
                    <div class="md:col-span-2">
                        <label for="direccion" class="block text-sm font-semibold text-gray-700 mb-2">
                            Dirección <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="direccion" 
                            name="direccion" 
                            value="{{ old('direccion', $usuario->direccion) }}"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition @error('direccion') border-red-500 @enderror"
                            required
                        >
                        @error('direccion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ciudad (simplificado) -->
                    <div>
                        <label for="id_ciudad" class="block text-sm font-semibold text-gray-700 mb-2">
                            Ciudad <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="id_ciudad" 
                            name="id_ciudad" 
                            value="{{ old('id_ciudad', $usuario->id_ciudad) }}"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition @error('id_ciudad') border-red-500 @enderror"
                            required
                        >
                        <p class="mt-1 text-xs text-gray-500">Código DANE de la ciudad</p>
                        @error('id_ciudad')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información Solo Lectura -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center pb-3 border-b-2 border-gray-400">
                    <span class="material-icons mr-2 text-gray-600">lock</span>
                    Información Contractual (No Editable)
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Salario Base</p>
                        <p class="text-lg font-semibold text-gray-700">${{ number_format($usuario->salario_base ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Fecha de Ingreso</p>
                        <p class="text-lg font-semibold text-gray-700">
                            {{ $usuario->fecha_inicio ? \Carbon\Carbon::parse($usuario->fecha_inicio)->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
                    <div class="flex items-start">
                        <span class="material-icons text-yellow-600 mr-3 mt-0.5">warning</span>
                        <p class="text-sm text-yellow-800">
                            <strong>Nota:</strong> La información contractual como salario, tipo de contrato, EPS, AFP, etc., 
                            no puede ser modificada por el trabajador. Si necesitas actualizar estos datos, contacta al departamento de recursos humanos.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                <a href="{{ route('trabajador.dashboard') }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors shadow-md">
                    <span class="material-icons mr-2 text-sm">save</span>
                    Actualizar Perfil
                </button>
            </div>
        </form>
    </div>

    <!-- Modal para ampliación de foto -->
    <div id="avatar-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header del modal -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Foto de Perfil</h3>
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

@push('styles')
<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in { animation: fade-in 0.3s ease-out; }
</style>
@endpush

@push('scripts')
<script>
    // Capitalizar primera letra de cada palabra al salir del campo
    function ucWords(str) {
        if (!str) return str;
        return str.toLowerCase().replace(/(?:^|\s)[a-záéíóúñü]/g, c => c.toUpperCase());
    }

    document.querySelectorAll('.nombre-campo').forEach(function (input) {
        input.addEventListener('blur', function () {
            this.value = ucWords(this.value);
        });
    });

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
