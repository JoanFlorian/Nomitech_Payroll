<h2 class="text-2xl font-bold text-center text-[#424242] mb-2">RECUPERAR CONTRASEÑA</h2>
<div class="w-20 h-1 bg-[#1565C0] mx-auto mb-8"></div>

@if (session('status'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('status') }}</span>
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-6">
        <label class="block text-[#424242] text-sm font-medium mb-2">Email</label>
        <input type="email" name="correo" id="forgot-correo" placeholder="tu@email.com" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-white"
            value="{{ old('correo') }}" aria-describedby="forgot-correo-feedback" />
        <p id="forgot-correo-feedback" class="invalid-feedback text-red-600 text-sm mt-1 hidden">El campo correo electrónico es obligatorio.</p>
        @error('correo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-4">
        <button type="submit"
            class="w-full bg-[#1565C0] text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-blue-800 transition shadow-md">
            Enviar Código de Verificación
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="text-sm text-[#424242] hover:underline">Volver al inicio de sesión</a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="{{ route('password.email') }}"]');
        const input = document.getElementById('forgot-correo');
        const feedback = document.getElementById('forgot-correo-feedback');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!form || !input || !feedback) {
            return;
        }

        function showInvalid(message) {
            input.classList.add('is-invalid');
            feedback.textContent = message;
            feedback.classList.remove('hidden');
        }

        function clearInvalid() {
            input.classList.remove('is-invalid');
            feedback.classList.add('hidden');
        }

        function validateEmail() {
            const value = (input.value || '').trim();

            if (!value) {
                showInvalid('El campo correo electrónico es obligatorio.');
                return false;
            }

            if (!emailRegex.test(value)) {
                showInvalid('El correo electrónico no es válido.');
                return false;
            }

            clearInvalid();
            return true;
        }

        input.addEventListener('input', validateEmail);

        form.addEventListener('submit', function (event) {
            if (!validateEmail()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
</script>