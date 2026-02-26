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
        <input type="password" name="contrasena" id="contrasena" placeholder="••••••••" required minlength="8" maxlength="64"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-white"
            aria-describedby="contrasena-feedback" />
        <p id="contrasena-feedback" class="invalid-feedback text-red-600 text-sm mt-1 hidden">La contraseña debe contener al menos una letra, un número y un símbolo.</p>
        @error('contrasena') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mb-6">
        <label class="block text-[#424242] text-sm font-medium mb-2">Confirmar Contraseña</label>
        <input type="password" name="contrasena_confirmation" id="contrasena_confirmation" placeholder="••••••••" required minlength="8" maxlength="64"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-white" />
        <p id="contrasena-confirmation-feedback" class="invalid-feedback text-red-600 text-sm mt-1 hidden">Las contraseñas no coinciden.</p>
    </div>

    <div class="mb-4">
        <button type="submit"
            class="w-full bg-[#1565C0] text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-blue-800 transition shadow-md">
            Restablecer Contraseña
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="{{ route('password.update') }}"]');
        const passwordInput = document.getElementById('contrasena');
        const confirmInput = document.getElementById('contrasena_confirmation');
        const passwordFeedback = document.getElementById('contrasena-feedback');
        const confirmFeedback = document.getElementById('contrasena-confirmation-feedback');
        const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/;
        const minPasswordLength = 8;
        const maxPasswordLength = 64;

        if (!form || !passwordInput || !confirmInput) {
            return;
        }

        function showInvalid(input, feedback) {
            input.classList.add('is-invalid');
            if (feedback) {
                feedback.classList.remove('hidden');
            }
        }

        function clearInvalid(input, feedback) {
            input.classList.remove('is-invalid');
            if (feedback) {
                feedback.classList.add('hidden');
            }
        }

        function validatePassword() {
            const value = passwordInput.value || '';
            if (value.length === 0) {
                passwordFeedback.textContent = 'El campo contraseña es obligatorio.';
                showInvalid(passwordInput, passwordFeedback);
                return false;
            }

            if (value.length < minPasswordLength) {
                passwordFeedback.textContent = `La contraseña debe tener al menos ${minPasswordLength} caracteres.`;
                showInvalid(passwordInput, passwordFeedback);
                return false;
            }

            if (value.length > maxPasswordLength) {
                passwordFeedback.textContent = `La contraseña no puede superar los ${maxPasswordLength} caracteres.`;
                showInvalid(passwordInput, passwordFeedback);
                return false;
            }

            const isValid = passwordRegex.test(value);
            if (isValid) {
                clearInvalid(passwordInput, passwordFeedback);
                return true;
            }

            passwordFeedback.textContent = 'La contraseña debe contener al menos una letra, un número y un símbolo.';
            showInvalid(passwordInput, passwordFeedback);
            return false;
        }

        function validateConfirmation() {
            const passwordValue = passwordInput.value || '';
            const confirmValue = confirmInput.value || '';

            if (confirmValue.length === 0) {
                confirmFeedback.textContent = 'La confirmación de contraseña es obligatoria.';
                showInvalid(confirmInput, confirmFeedback);
                return false;
            }

            const matches = passwordValue === confirmValue;
            if (matches) {
                clearInvalid(confirmInput, confirmFeedback);
                return true;
            }

            confirmFeedback.textContent = 'Las contraseñas no coinciden.';
            showInvalid(confirmInput, confirmFeedback);
            return false;
        }

        passwordInput.addEventListener('input', function () {
            validatePassword();
            validateConfirmation();
        });

        confirmInput.addEventListener('input', validateConfirmation);

        form.addEventListener('submit', function (event) {
            const passwordOk = validatePassword();
            const confirmationOk = validateConfirmation();

            if (!passwordOk || !confirmationOk) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
</script>