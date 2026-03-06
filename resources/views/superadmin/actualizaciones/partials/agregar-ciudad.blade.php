<!-- MODAL AGREGAR CIUDAD -->
<div id="modalAgregarCiudad" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-3xl w-full max-w-2xl p-10 relative shadow-2xl border border-gray-200">

        <!-- Botón cerrar con estilo -->
        <button onclick="closeAddModal()" class="absolute top-6 right-6 text-gray-400 hover:text-red-500 transition transform hover:scale-110">
            <i class="bi bi-x-lg text-2xl"></i>
        </button>

        <!-- Header decorativo con gradiente -->
        <div class="mb-8 pb-6 border-b-2 border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600 flex items-center justify-center shadow-lg">
                    <i class="bi bi-geo-alt text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Agregar Nueva Ciudad</h2>
                    <p class="text-gray-500 mt-1">Registra una ciudad en el sistema</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('superadmin.ciudades.store') }}" class="space-y-6">
            @csrf

            <!-- Grid de dos columnas para inputs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Departamento Select -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-800 mb-3">
                        <i class="bi bi-building text-blue-600 mr-2"></i>Departamento<span class="text-red-500">*</span>
                    </label>
                    <select name="cod_dep" class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-3 focus:ring-blue-200 transition bg-white hover:border-gray-400" required>
                        <option value="">-- Selecciona un departamento --</option>
                        @foreach(collect($departamentos ?? [])->filter() as $departamento)
                            <option value="{{ $departamento->codigo }}">
                                {{ $departamento->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Código Ciudad -->
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-3">
                        <i class="bi bi-hash text-green-600 mr-2"></i>Código<span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="codigo" 
                        placeholder="05001" 
                        inputmode="numeric"
                        minlength="6"
                        maxlength="10"
                        pattern="[0-9]{6,10}"
                        title="Código inválido. El código debe tener mínimo 6 dígitos."
                        class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-green-500 focus:ring-3 focus:ring-green-200 transition hover:border-gray-400" 
                        required
                    >
                </div>

                <!-- Nombre Ciudad -->
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-3">
                        <i class="bi bi-pin-map text-purple-600 mr-2"></i>Nombre<span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nombre" 
                        placeholder="Medellín" 
                        maxlength="100"
                        pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+$"
                        title="El nombre solo permite letras y espacios."
                        class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-purple-500 focus:ring-3 focus:ring-purple-200 transition hover:border-gray-400" 
                        required
                    >
                </div>
            </div>

            <!-- Botones estilizados -->
            <div class="flex gap-4 pt-8 border-t-2 border-gray-100">
                <button 
                    type="button" 
                    onclick="closeAddModal()"
                    class="flex-1 px-6 py-3 text-sm font-bold rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-100 hover:border-gray-400 transition transform hover:scale-105 flex items-center justify-center gap-2"
                >
                    <i class="bi bi-x-lg"></i> Cancelar
                </button>
                <button 
                    type="submit"
                    class="flex-1 px-6 py-3 text-sm font-bold rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 text-white hover:from-green-600 hover:to-emerald-700 transition shadow-lg transform hover:scale-105 flex items-center justify-center gap-2"
                >
                    <i class="bi bi-check-circle"></i> Guardar Ciudad
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalCiudad = document.getElementById('modalAgregarCiudad');
        if (!modalCiudad) {
            return;
        }

        const formCiudad = modalCiudad.querySelector('form');
        const inputCodigo = modalCiudad.querySelector('input[name="codigo"]');
        const inputNombre = modalCiudad.querySelector('input[name="nombre"]');

        if (inputCodigo) {
            inputCodigo.addEventListener('input', function () {
                this.value = (this.value || '').replace(/\D/g, '').slice(0, 10);
            });
        }

        if (inputNombre) {
            inputNombre.addEventListener('input', function () {
                this.value = (this.value || '')
                    .replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]/g, '')
                    .replace(/\s{2,}/g, ' ');
            });
            inputNombre.addEventListener('blur', function () {
                this.value = (this.value || '').trim();
            });
        }

        if (formCiudad) {
            formCiudad.addEventListener('submit', function (e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    const firstInvalid = this.querySelector(':invalid');
                    const validationMessage = firstInvalid?.validationMessage || 'Revisa los campos del formulario.';

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Datos inválidos',
                            text: validationMessage,
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            firstInvalid?.focus();
                            this.reportValidity();
                        });
                        return;
                    }

                    this.reportValidity();
                }
            });
        }
    });
</script>
