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
        <button type="submit" id="send-code-button"
            class="w-full bg-[#1565C0] text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-blue-800 transition shadow-md">
            Enviar Código de Verificación
        </button>
        <p id="cooldown-feedback" class="text-sm text-[#424242] mt-2 text-center hidden"></p>
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
        const sendButton = document.getElementById('send-code-button');
        const cooldownFeedback = document.getElementById('cooldown-feedback');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const cooldownSecondsFromServer = Number(@json(session('cooldown_seconds', 0))) || 0;
        const cooldownStorageKey = 'password-reset-cooldown-end';

        if (!form || !input || !feedback || !sendButton || !cooldownFeedback) {
            return;
        }

        function updateButtonCooldown(secondsLeft) {
            if (secondsLeft > 0) {
                sendButton.disabled = true;
                sendButton.classList.add('opacity-60', 'cursor-not-allowed');
                cooldownFeedback.textContent = `Podrás solicitar un nuevo código en ${secondsLeft}s.`;
                cooldownFeedback.classList.remove('hidden');
                return;
            }

            sendButton.disabled = false;
            sendButton.classList.remove('opacity-60', 'cursor-not-allowed');
            cooldownFeedback.textContent = '';
            cooldownFeedback.classList.add('hidden');
        }

        function startCooldown(seconds) {
            const safeSeconds = Math.max(Number(seconds) || 0, 0);
            if (safeSeconds <= 0) {
                updateButtonCooldown(0);
                localStorage.removeItem(cooldownStorageKey);
                return;
            }

            const cooldownEnd = Date.now() + (safeSeconds * 1000);
            localStorage.setItem(cooldownStorageKey, String(cooldownEnd));

            const tick = function () {
                const remaining = Math.max(Math.ceil((cooldownEnd - Date.now()) / 1000), 0);
                updateButtonCooldown(remaining);

                if (remaining <= 0) {
                    localStorage.removeItem(cooldownStorageKey);
                    return;
                }

                setTimeout(tick, 1000);
            };

            tick();
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

        const savedCooldownEnd = Number(localStorage.getItem(cooldownStorageKey) || 0);
        const savedRemaining = Math.max(Math.ceil((savedCooldownEnd - Date.now()) / 1000), 0);

        if (cooldownSecondsFromServer > 0) {
            startCooldown(cooldownSecondsFromServer);
        } else if (savedRemaining > 0) {
            startCooldown(savedRemaining);
        } else {
            updateButtonCooldown(0);
        }

        input.addEventListener('input', validateEmail);

        form.addEventListener('submit', function (event) {
            if (sendButton.disabled) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            if (!validateEmail()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
</script>