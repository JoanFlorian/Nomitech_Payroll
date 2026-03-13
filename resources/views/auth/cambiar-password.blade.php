<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cambiar Contraseña - Nomitech</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fade-in 0.45s ease-out both; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-[#1565C0] to-[#0D47A1] flex items-center justify-center p-4">

    <div class="w-full max-w-md fade-in">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-br from-[#1565C0] to-[#1976D2] px-8 pt-8 pb-6 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-icons text-white text-3xl">lock_reset</span>
                </div>
                <h1 class="text-2xl font-bold text-white">Cambiar Contraseña</h1>
                <p class="text-blue-100 text-sm mt-2">Debes establecer una nueva contraseña para continuar</p>
            </div>

            <!-- Body -->
            <div class="px-8 py-8">
                <!-- Aviso de seguridad -->
                <div class="mb-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                    <div class="flex items-start gap-3">
                        <span class="material-icons text-amber-500 text-xl mt-0.5">info</span>
                        <p class="text-sm text-amber-800 leading-relaxed">
                            Tu contraseña temporal es tu <strong>número de documento</strong>.
                            Por seguridad, debes cambiarla antes de continuar. La nueva contraseña
                            no puede ser igual a la contraseña temporal.
                        </p>
                    </div>
                </div>

                <!-- Errores -->
                @if ($errors->any())
                    <div class="mb-5 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start gap-2">
                            <span class="material-icons text-red-500 text-lg">error_outline</span>
                            <ul class="text-sm text-red-700 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Formulario -->
                <form method="POST" action="{{ route('cambiar-password.update') }}" class="space-y-5">
                    @csrf

                    <!-- Nueva contraseña -->
                    <div>
                        <label for="contrasena" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nueva contraseña <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="contrasena"
                                name="contrasena"
                                required
                                minlength="8"
                                placeholder="Mínimo 8 caracteres"
                                class="w-full px-4 py-3 pr-12 border-2 border-gray-300 rounded-xl focus:border-[#1565C0] focus:outline-none transition text-sm @error('contrasena') border-red-400 @enderror"
                            >
                            <button type="button" onclick="togglePassword('contrasena', 'ico1')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="material-icons text-xl" id="ico1">visibility</span>
                            </button>
                        </div>

                        <!-- Indicador de fortaleza -->
                        <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div id="strength-bar" class="h-full rounded-full transition-all duration-300 w-0"></div>
                        </div>
                        <p id="strength-text" class="text-xs text-gray-400 mt-1"></p>

                        <!-- Checklist de requisitos -->
                        <ul class="mt-3 space-y-1 text-xs" id="req-list">
                            <li id="req-len"  class="flex items-center gap-1.5 text-gray-400"><span class="material-icons text-sm">radio_button_unchecked</span> Mínimo 8 caracteres</li>
                            <li id="req-upper" class="flex items-center gap-1.5 text-gray-400"><span class="material-icons text-sm">radio_button_unchecked</span> Al menos una mayúscula</li>
                            <li id="req-lower" class="flex items-center gap-1.5 text-gray-400"><span class="material-icons text-sm">radio_button_unchecked</span> Al menos una minúscula</li>
                            <li id="req-num"  class="flex items-center gap-1.5 text-gray-400"><span class="material-icons text-sm">radio_button_unchecked</span> Al menos un número</li>
                            <li id="req-sym"  class="flex items-center gap-1.5 text-gray-400"><span class="material-icons text-sm">radio_button_unchecked</span> Al menos un símbolo (!&#64;#$%&amp;*_-)</li>
                        </ul>
                    </div>

                    <!-- Confirmar contraseña -->
                    <div>
                        <label for="contrasena_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirmar contraseña <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="contrasena_confirmation"
                                name="contrasena_confirmation"
                                required
                                minlength="6"
                                placeholder="Repite la nueva contraseña"
                                class="w-full px-4 py-3 pr-12 border-2 border-gray-300 rounded-xl focus:border-[#1565C0] focus:outline-none transition text-sm"
                            >
                            <button type="button" onclick="togglePassword('contrasena_confirmation', 'ico2')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="material-icons text-xl" id="ico2">visibility</span>
                            </button>
                        </div>
                        <p id="match-text" class="text-xs mt-1"></p>
                    </div>

                    <!-- Botón -->
                    <button type="submit" id="btn-submit"
                        class="w-full py-3 bg-gradient-to-r from-[#1565C0] to-[#1976D2] hover:from-[#0D47A1] hover:to-[#1565C0] text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 mt-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                        <span class="material-icons text-lg">save</span>
                        Guardar nueva contraseña
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-blue-200 text-xs mt-4">
            &copy; {{ date('Y') }} Nomitech &mdash; Todos los derechos reservados
        </p>
    </div>

    <script>
        // Toggle visibilidad contraseña
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon  = document.getElementById(iconId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                field.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        const passInput = document.getElementById('contrasena');
        const confInput = document.getElementById('contrasena_confirmation');
        const bar        = document.getElementById('strength-bar');
        const strengthTxt = document.getElementById('strength-text');
        const matchTxt   = document.getElementById('match-text');
        const btnSubmit  = document.getElementById('btn-submit');

        // Requisitos
        const reqs = {
            len:   { el: document.getElementById('req-len'),   test: v => v.length >= 8 },
            upper: { el: document.getElementById('req-upper'), test: v => /[A-Z]/.test(v) },
            lower: { el: document.getElementById('req-lower'), test: v => /[a-z]/.test(v) },
            num:   { el: document.getElementById('req-num'),   test: v => /[0-9]/.test(v) },
            sym:   { el: document.getElementById('req-sym'),   test: v => /[!@#$%&*_\-]/.test(v) },
        };

        function setReq(item, ok) {
            const icon = item.el.querySelector('.material-icons');
            if (ok) {
                item.el.className = 'flex items-center gap-1.5 text-green-600';
                icon.textContent  = 'check_circle';
            } else {
                item.el.className = 'flex items-center gap-1.5 text-gray-400';
                icon.textContent  = 'radio_button_unchecked';
            }
        }

        function allReqsMet(val) {
            return Object.values(reqs).every(r => r.test(val));
        }

        passInput.addEventListener('input', () => {
            const val = passInput.value;

            // Actualizar checklist
            let passed = 0;
            Object.values(reqs).forEach(r => {
                const ok = r.test(val);
                setReq(r, ok);
                if (ok) passed++;
            });

            // Barra de fortaleza
            const levels = [
                { pct: '0%',   color: '',              text: '' },
                { pct: '20%',  color: 'bg-red-400',    text: 'Muy débil' },
                { pct: '40%',  color: 'bg-orange-400', text: 'Débil' },
                { pct: '60%',  color: 'bg-yellow-400', text: 'Moderada' },
                { pct: '80%',  color: 'bg-green-400',  text: 'Fuerte' },
                { pct: '100%', color: 'bg-green-600',  text: 'Muy fuerte' },
            ];
            bar.className = `h-full rounded-full transition-all duration-300 ${levels[passed].color}`;
            bar.style.width = levels[passed].pct;
            strengthTxt.textContent = levels[passed].text;

            checkMatch();
            updateSubmit();
        });

        confInput.addEventListener('input', () => {
            checkMatch();
            updateSubmit();
        });

        function checkMatch() {
            if (!confInput.value) { matchTxt.textContent = ''; return; }
            if (passInput.value === confInput.value) {
                matchTxt.textContent = '✓ Las contraseñas coinciden';
                matchTxt.className   = 'text-xs mt-1 text-green-600';
            } else {
                matchTxt.textContent = '✗ Las contraseñas no coinciden';
                matchTxt.className   = 'text-xs mt-1 text-red-500';
            }
        }

        function updateSubmit() {
            const ready = allReqsMet(passInput.value) && passInput.value === confInput.value;
            btnSubmit.disabled = !ready;
        }
    </script>
</body>
</html>
