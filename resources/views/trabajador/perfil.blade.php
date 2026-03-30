@extends('layouts.app')

@section('title', 'Mi Perfil')
@section('hide-layout-header', '1')

@section('content')
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_#e8f4ff,_#f8fafc_45%,_#f1f5ff)] p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-slate-800 mb-2 tracking-tight">Mi Perfil</h1>
                <p class="text-slate-600">Actualiza tu información personal de forma rápida y segura</p>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('trabajador.dashboard') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 shadow-sm">
                    <span class="material-icons mr-2 text-sm">arrow_back</span>
                    Volver
                </a>

                <div class="rounded-2xl border border-slate-200/80 bg-white/95 px-4 py-3 shadow-sm backdrop-blur profile-mini-summary">
                    <div class="flex items-center gap-3">
                        <img src="{{ $usuario->avatar_url }}"
                             alt="Foto de perfil"
                             class="w-12 h-12 rounded-full object-cover border border-slate-200">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-800">{{ $usuario->nombre_completo ?? (($usuario->primer_nombre ?? 'Usuario') . ' ' . ($usuario->primer_apellido ?? '')) }}</p>
                            <p class="text-xs text-slate-500">{{ $usuario->rol->nombre ?? 'Trabajador' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensajes Flash -->
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm animate-fade-in">
                <div class="flex">
                    <span class="material-icons text-emerald-500 mr-3">check_circle</span>
                    <p class="text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm">
                <div class="flex items-start">
                    <span class="material-icons text-rose-500 mr-3 mt-0.5">error</span>
                    <div class="flex-1">
                        <p class="text-rose-800 font-medium mb-2">Por favor corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside text-sm text-rose-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
            <div class="profile-card profile-card-accent xl:sticky xl:top-6">
                <h2 class="profile-card-title">
                    <span class="material-icons mr-2 text-cyan-600">photo_camera</span>
                    Foto de Perfil
                </h2>

                <div class="space-y-4">
                    <div class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/90 p-5">
                        <img id="avatar-preview"
                             src="{{ $usuario->avatar_url }}"
                             alt="Avatar actual"
                             class="w-28 h-28 rounded-full object-cover border-2 border-slate-200 shadow-sm cursor-pointer hover:shadow-lg hover:border-cyan-400 transition-all duration-200"
                             data-full-src="{{ $usuario->avatar_url }}"
                             role="button"
                             tabindex="0"
                             title="Haz click para ampliar">
                        <p id="avatar-status" class="text-xs text-slate-500 text-center">Vista previa actual</p>
                    </div>

                    <form action="{{ route('trabajador.perfil.foto') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="file"
                               id="avatar"
                               name="avatar"
                               accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                               class="block w-full text-sm text-slate-700 bg-white border border-slate-200 rounded-xl p-2 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cyan-100 file:text-cyan-700 hover:file:bg-cyan-200">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <button type="button" id="clear-avatar-selection" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all duration-200">
                                Quitar selección
                            </button>
                            <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white font-medium rounded-xl transition-colors shadow-sm">
                                Guardar foto
                            </button>
                        </div>

                        <p class="text-xs text-slate-500">Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo: 2MB.</p>
                        @error('avatar')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </form>
                </div>
            </div>

            <!-- Formulario de Perfil -->
            <form action="{{ route('trabajador.perfil.actualizar') }}" method="POST" class="profile-card profile-form-card xl:col-span-2">
            @csrf

            <!-- Información Personal -->
            <div class="profile-section">
                <h2 class="profile-card-title">
                    <span class="material-icons mr-2 text-blue-600">person</span>
                    Información Personal
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
                            class="nombre-campo profile-input @error('primer_nombre') border-red-500 @enderror"
                            placeholder="Ej: Carlos"
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
                            class="nombre-campo profile-input @error('otros_nombres') border-red-500 @enderror"
                            placeholder="Ej: Andrés"
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
                            class="nombre-campo profile-input @error('primer_apellido') border-red-500 @enderror"
                            placeholder="Ej: Pérez"
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
                            class="nombre-campo profile-input @error('segundo_apellido') border-red-500 @enderror"
                            placeholder="Ej: Gómez"
                        >
                        @error('segundo_apellido')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="profile-section">
                <h2 class="profile-card-title">
                    <span class="material-icons mr-2 text-emerald-600">contact_phone</span>
                    Información de Contacto
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Tipo de Documento -->
                    <div>
                        <label for="id_tipo_doc" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tipo de Documento <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="id_tipo_doc" 
                            name="id_tipo_doc" 
                            class="profile-input @error('id_tipo_doc') border-red-500 @enderror"
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
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl bg-slate-50 text-slate-500 cursor-not-allowed select-none"
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
                            class="profile-input @error('telefono') border-red-500 @enderror"
                            required
                            pattern="[0-9]{7,15}"
                            title="Mínimo 7 dígitos"
                            placeholder="Ej: 3001234567"
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
                            class="profile-input @error('correo') border-red-500 @enderror"
                            required
                            placeholder="Ej: correo@empresa.com"
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
                            class="profile-input @error('direccion') border-red-500 @enderror"
                            required
                            placeholder="Ej: Calle 123 #45-67"
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
                            class="profile-input @error('id_ciudad') border-red-500 @enderror"
                            required
                            placeholder="Ej: 11001"
                        >
                        <p class="mt-1 text-xs text-gray-500">Código DANE de la ciudad</p>
                        @error('id_ciudad')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información Solo Lectura -->
            <div class="profile-section">
                <h2 class="profile-card-title profile-card-title-muted">
                    <span class="material-icons mr-2 text-slate-600">lock</span>
                    Información Contractual (No Editable)
                    <span class="ml-auto inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 border border-slate-200">Solo lectura</span>
                </h2>

                <div class="contract-meta-grid">
                    <div class="contract-meta-card">
                        <div class="contract-meta-icon bg-emerald-100 text-emerald-700">
                            <span class="material-icons text-base">payments</span>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Salario Base</p>
                            <p class="text-xl font-semibold text-slate-800">${{ number_format($usuario->salario_base ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="contract-meta-card">
                        <div class="contract-meta-icon bg-blue-100 text-blue-700">
                            <span class="material-icons text-base">calendar_month</span>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Fecha de Ingreso</p>
                            <p class="text-xl font-semibold text-slate-800">
                            {{ $usuario->fecha_inicio ? \Carbon\Carbon::parse($usuario->fecha_inicio)->format('d/m/Y') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="profile-warning">
                    <div class="flex items-start">
                        <span class="material-icons text-amber-600 mr-3 mt-0.5">warning</span>
                        <p class="text-sm text-amber-800">
                            <strong>Nota:</strong> La información contractual como salario, tipo de contrato, EPS, AFP, etc., 
                            no puede ser modificada por el trabajador. Si necesitas actualizar estos datos, contacta al departamento de recursos humanos.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="profile-action-bar">
                <a href="{{ route('trabajador.dashboard') }}" class="inline-flex justify-center px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors shadow-sm hover:shadow-md">
                    <span class="material-icons mr-2 text-sm">save</span>
                    Actualizar Perfil
                </button>
            </div>
            </form>
        </div>
    </div>

    <!-- Modal para ampliación de foto -->
    <div id="avatar-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-slate-200">
            <!-- Header del modal -->
            <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800">Foto de Perfil</h3>
                <button id="close-avatar-modal" class="text-slate-500 hover:text-slate-700 text-2xl leading-none transition">
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

    .profile-card {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%);
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 1.1rem;
        box-shadow: 0 24px 38px -28px rgba(15, 23, 42, 0.45);
        padding: 1.5rem;
    }

    .profile-card-accent {
        background:
            radial-gradient(circle at top right, rgba(34, 211, 238, 0.12), transparent 35%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%);
    }

    .profile-form-card {
        position: relative;
    }

    .profile-form-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 1.1rem;
        pointer-events: none;
        background: linear-gradient(135deg, rgba(14, 116, 144, 0.04), transparent 35%, rgba(37, 99, 235, 0.04));
    }

    .profile-mini-summary {
        box-shadow: 0 8px 18px -14px rgba(15, 23, 42, 0.45);
    }

    .profile-section {
        position: relative;
        margin-bottom: 1.25rem;
        padding: 1.25rem;
        border: 1px solid #dbe7f3;
        border-radius: 1rem;
        background: #ffffff;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .profile-section:hover {
        border-color: #bfdbfe;
        box-shadow: 0 12px 24px -22px rgba(30, 64, 175, 0.5);
    }

    .profile-card-title {
        display: flex;
        align-items: center;
        padding-bottom: 0.75rem;
        margin-bottom: 1.25rem;
        border-bottom: 2px solid #e2e8f0;
        color: #1e293b;
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    .profile-card-title-muted {
        border-bottom-color: #dbe7f3;
    }

    .contract-meta-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
    }

    .contract-meta-card {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        border: 1px solid #dbe7f3;
        border-radius: 0.9rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 1rem;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .contract-meta-card:hover {
        transform: translateY(-1px);
        border-color: #bfdbfe;
        box-shadow: 0 10px 24px -20px rgba(30, 64, 175, 0.5);
    }

    .contract-meta-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 0.65rem;
        flex-shrink: 0;
    }

    .profile-warning {
        margin-top: 1rem;
        border-left: 4px solid #f59e0b;
        background: linear-gradient(180deg, #fffbeb 0%, #fff7df 100%);
        border-radius: 0 0.85rem 0.85rem 0;
        padding: 1rem;
    }

    .profile-action-bar {
        position: sticky;
        bottom: 0;
        z-index: 20;
        display: flex;
        flex-direction: column-reverse;
        align-items: stretch;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1.25rem;
        padding: 1rem 0 0;
        border-top: 1px solid #cbd5e1;
        background: linear-gradient(180deg, rgba(255,255,255,0.55) 0%, rgba(255,255,255,0.98) 35%);
        backdrop-filter: blur(6px);
    }

    .profile-input {
        width: 100%;
        border-radius: 0.8rem;
        border: 1.5px solid #cbd5e1;
        background: #fff;
        padding: 0.75rem 1rem;
        color: #0f172a;
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }

    .profile-input::placeholder {
        color: #94a3b8;
    }

    .profile-input:hover {
        border-color: #94a3b8;
    }

    .profile-input:focus {
        outline: none;
        border-color: #0284c7;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.14);
        background-color: #f8fdff;
    }

    @media (min-width: 768px) {
        .profile-card {
            padding: 2rem;
        }

        .profile-section {
            padding: 1.5rem;
        }

        .contract-meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .profile-action-bar {
            flex-direction: row;
            align-items: center;
        }
    }

    @media (max-width: 1279px) {
        .profile-card-accent {
            position: static;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Capitalizar primera letra de cada palabra mientras se escribe
    function ucWords(str) {
        if (!str) return str;
        return str.toLowerCase().replace(/(?:^|\s)[a-záéíóúñü]/g, c => c.toUpperCase());
    }

    function normalizeNameInput(input) {
        const start = input.selectionStart;
        const end = input.selectionEnd;
        const nextValue = ucWords(input.value);

        if (input.value !== nextValue) {
            input.value = nextValue;

            if (typeof start === 'number' && typeof end === 'number') {
                input.setSelectionRange(start, end);
            }
        }
    }

    document.querySelectorAll('.nombre-campo').forEach(function (input) {
        input.addEventListener('input', function () {
            normalizeNameInput(this);
        });

        input.addEventListener('blur', function () {
            normalizeNameInput(this);
        });
    });

    // Modal de ampliación de foto
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarInput = document.getElementById('avatar');
    const clearAvatarSelection = document.getElementById('clear-avatar-selection');
    const avatarStatus = document.getElementById('avatar-status');
    const avatarModal = document.getElementById('avatar-modal');
    const avatarModalImg = document.getElementById('avatar-modal-img');
    const closeAvatarModal = document.getElementById('close-avatar-modal');
    const originalAvatarSrc = avatarPreview ? avatarPreview.src : '';
    let currentAvatarObjectUrl = null;

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function () {
            const selectedFile = this.files && this.files[0];

            if (!selectedFile) {
                if (avatarStatus) avatarStatus.textContent = 'Vista previa actual';
                return;
            }

            if (currentAvatarObjectUrl) {
                URL.revokeObjectURL(currentAvatarObjectUrl);
            }

            currentAvatarObjectUrl = URL.createObjectURL(selectedFile);
            avatarPreview.src = currentAvatarObjectUrl;
            avatarPreview.setAttribute('data-full-src', currentAvatarObjectUrl);

            if (avatarStatus) {
                avatarStatus.textContent = 'Nueva imagen seleccionada';
            }
        });
    }

    if (clearAvatarSelection && avatarInput && avatarPreview) {
        clearAvatarSelection.addEventListener('click', function () {
            avatarInput.value = '';

            if (currentAvatarObjectUrl) {
                URL.revokeObjectURL(currentAvatarObjectUrl);
                currentAvatarObjectUrl = null;
            }

            avatarPreview.src = originalAvatarSrc;
            avatarPreview.setAttribute('data-full-src', originalAvatarSrc);

            if (avatarStatus) {
                avatarStatus.textContent = 'Vista previa actual';
            }
        });
    }

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
