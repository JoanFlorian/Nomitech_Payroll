@props(['correo'])

<h2 class="text-2xl font-bold text-center text-[#424242] mb-2">VERIFICAR CÓDIGO</h2>
<div class="w-20 h-1 bg-[#1565C0] mx-auto mb-8"></div>

@if (session('status'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('status') }}</span>
    </div>
@endif

<form method="POST" action="{{ route('password.verify') }}">
    @csrf

    <input type="hidden" name="correo" value="{{ $correo }}">

    <div class="mb-6">
        <p class="text-[#424242] text-sm mb-4 text-center">
            Hemos enviado un código de 6 dígitos a <strong>{{ $correo }}</strong>. Por favor, ingrésalo a continuación.
        </p>

        <label class="block text-[#424242] text-sm font-medium mb-2">Código de Verificación</label>
        <input type="text" name="code" id="verification-code" placeholder="000000" maxlength="6" required inputmode="numeric" pattern="[0-9]{6}"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1565C0] transition bg-white text-center text-2xl tracking-[1rem] font-bold"
            autocomplete="off" aria-describedby="verification-code-feedback" />
        <p id="verification-code-feedback" class="invalid-feedback text-red-600 text-sm mt-1 text-center hidden">El código de verificación debe contener exactamente 6 números.</p>
        @error('code') <p id="verification-code-server-error" class="text-red-600 text-sm mt-1 text-center">{{ $message }}</p> @enderror
        @error('correo') <p class="text-red-600 text-sm mt-1 text-center">{{ $message }}</p> @enderror
    </div>

    <div class="mb-4">
        <button type="submit"
            class="w-full bg-[#1565C0] text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-blue-800 transition shadow-md">
            Verificar Código
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('password.request') }}" class="text-sm text-[#424242] hover:underline">No recibí el código,
            intentar de nuevo</a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="{{ route('password.verify') }}"]');
        const input = document.getElementById('verification-code');
        const feedback = document.getElementById('verification-code-feedback');
        const serverError = document.getElementById('verification-code-server-error');
        if (!input || !form || !feedback) {
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

        function validateCode() {
            const value = input.value || '';

            if (!value) {
                showInvalid('El código de verificación es obligatorio.');
                return false;
            }

            if (!/^\d{6}$/.test(value)) {
                showInvalid('El código de verificación debe contener exactamente 6 números.');
                return false;
            }

            clearInvalid();
            return true;
        }

        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(0, 6);
            if (serverError) {
                serverError.classList.add('hidden');
            }
            validateCode();
        });

        form.addEventListener('submit', function (event) {
            if (!validateCode()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
</script>