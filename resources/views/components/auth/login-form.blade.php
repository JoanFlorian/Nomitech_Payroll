<h2 class="text-2xl font-bold text-center text-[#424242] mb-2">INICIA SESIÓN</h2>
<div class="w-20 h-1 bg-[#1565C0] mx-auto mb-8"></div>

<form method="POST" action="{{ route('login.perform') }}">
    @csrf

    <div class="mb-6">
        <label class="block text-[#424242] text-sm font-medium mb-2">Correo electrónico</label>
        <input type="email" name="correo" id="login-correo" placeholder="tu@email.com" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-white"
            value="{{ old('correo') }}" aria-describedby="login-correo-feedback" />
        <p id="login-correo-feedback" class="invalid-feedback text-red-600 text-sm mt-1 hidden">El correo electrónico es obligatorio.</p>
        @error('correo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-6">
        <label class="block text-[#424242] text-sm font-medium mb-2">Contraseña</label>
        <input type="password" name="contrasena" id="login-contrasena" placeholder="••••••••" required minlength="8"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-white"
            aria-describedby="login-contrasena-feedback" />
        <p id="login-contrasena-feedback" class="invalid-feedback text-red-600 text-sm mt-1 hidden">La contraseña es obligatoria.</p>
        @error('contrasena') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-4">
        <button type="submit"
            class="w-full bg-[#1565C0] text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-blue-800 transition shadow-md">
            Iniciar Sesión
        </button>
    </div>



    <div class="text-center">
        <a href="{{ route('password.request') }}" class="text-sm text-[#424242] hover:underline">¿Olvidaste la
            contraseña?</a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="{{ route('login.perform') }}"]');
        const correoInput = document.getElementById('login-correo');
        const correoFeedback = document.getElementById('login-correo-feedback');
        const contrasenaInput = document.getElementById('login-contrasena');
        const contrasenaFeedback = document.getElementById('login-contrasena-feedback');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!form || !correoInput || !contrasenaInput) {
            return;
        }

        function showInvalid(input, feedback, message) {
            input.classList.add('is-invalid');
            if (feedback) {
                feedback.textContent = message;
                feedback.classList.remove('hidden');
            }
        }

        function clearInvalid(input, feedback) {
            input.classList.remove('is-invalid');
            if (feedback) {
                feedback.classList.add('hidden');
            }
        }

        function validateCorreo() {
            const value = (correoInput.value || '').trim();

            if (!value) {
                showInvalid(correoInput, correoFeedback, 'El correo electrónico es obligatorio.');
                return false;
            }

            if (!emailRegex.test(value)) {
                showInvalid(correoInput, correoFeedback, 'El correo electrónico no es válido.');
                return false;
            }

            clearInvalid(correoInput, correoFeedback);
            return true;
        }

        function validateContrasena() {
            const value = contrasenaInput.value || '';

            if (!value) {
                showInvalid(contrasenaInput, contrasenaFeedback, 'La contraseña es obligatoria.');
                return false;
            }

            if (value.length < 8) {
                showInvalid(contrasenaInput, contrasenaFeedback, 'La contraseña debe tener al menos 8 caracteres.');
                return false;
            }

            clearInvalid(contrasenaInput, contrasenaFeedback);
            return true;
        }

        correoInput.addEventListener('input', validateCorreo);
        contrasenaInput.addEventListener('input', validateContrasena);

        form.addEventListener('submit', function (event) {
            const correoOk = validateCorreo();
            const contrasenaOk = validateContrasena();

            if (!correoOk || !contrasenaOk) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
</script>