<h2 class="text-2xl font-bold text-center text-[#424242] mb-2">INICIA SESIÓN</h2>
<div class="w-20 h-1 bg-[#1565C0] mx-auto mb-8"></div>

<form method="POST" action="{{ route('login.perform') }}">
    @csrf
    
    <div class="mb-6">
        <label class="block text-[#424242] text-sm font-medium mb-2">Email</label>
        <input type="email" name="correo" placeholder="tu@email.com" required class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-white" value="{{ old('correo') }}"/>
        @error('correo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-6">
        <label class="block text-[#424242] text-sm font-medium mb-2">Password</label>
        <input type="password" name="contrasena" placeholder="••••••••" required class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-white"/>
        @error('contrasena') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-4">
        <button type="submit" class="w-full bg-[#1565C0] text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-blue-800 transition shadow-md">
            Iniciar Sesion
        </button>
    </div>

    <div class="mb-6">
        <button type="button" class="w-full bg-[#26A69A] text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-teal-700 transition shadow-md">
            Crear Cuenta
        </button>
    </div>

    <div class="text-center">
        <a href="#" class="text-sm text-[#424242] hover:underline">¿Olvidaste la contraseña?</a>
    </div>
</form>
