@props(['token', 'correo'])

<h2 class="text-2xl font-bold text-center text-[#424242] mb-2">RESTABLECER CONTRASEÑA</h2>
<div class="w-20 h-1 bg-[#1565C0] mx-auto mb-8"></div>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="mb-6">
        <label class="block text-[#424242] text-sm font-medium mb-2">Email</label>
        <input type="email" name="correo" value="{{ $correo ?? old('correo') }}" required readonly
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-gray-100" />
        @error('correo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-6">
        <label class="block text-[#424242] text-sm font-medium mb-2">Nueva Contraseña</label>
        <input type="password" name="contrasena" placeholder="••••••••" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-white" />
        @error('contrasena') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-6">
        <label class="block text-[#424242] text-sm font-medium mb-2">Confirmar Contraseña</label>
        <input type="password" name="contrasena_confirmation" placeholder="••••••••" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-white" />
    </div>

    <div class="mb-4">
        <button type="submit"
            class="w-full bg-[#1565C0] text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-blue-800 transition shadow-md">
            Restablecer Contraseña
        </button>
    </div>
</form>