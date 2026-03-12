{{-- MODAL CERRAR PERIODO --}}
<div id="modalCerrarPeriodo" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Fondo oscuro con figuritas decorativas -->
        <div class="fixed inset-0 bg-gray-900/85 transition-opacity" aria-hidden="true"
            onclick="document.getElementById('modalCerrarPeriodo').classList.add('hidden')">
            <!-- Figuritas decorativas Marca: Azul #1565C0, Verde #2AA58C -->
            <div class="absolute top-[5%] left-[10%] w-32 h-32 rounded-full bg-[#1565C0]/20 blur-[2px] rotate-12"></div>
            <div class="absolute top-[15%] right-[15%] w-48 h-48 rounded-3xl bg-[#2AA58C]/15 blur-[1px] -rotate-12">
            </div>
            <div class="absolute bottom-[10%] left-[20%] w-40 h-40 rounded-xl bg-[#2AA58C]/20 rotate-45"></div>
            <div class="absolute bottom-[20%] right-[10%] w-56 h-56 rounded-full bg-[#1565C0]/15 blur-[3px]"></div>
            <div class="absolute top-[40%] left-[-5%] w-24 h-24 rounded-full bg-[#2AA58C]/25"></div>
            <div class="absolute top-[55%] right-[-5%] w-36 h-36 rounded-2xl bg-[#1565C0]/20 -rotate-6"></div>
            <div class="absolute top-[70%] left-[45%] w-16 h-16 rounded-full bg-[#2AA58C]/20"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div
            class="relative inline-block overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:max-w-lg sm:w-full border border-gray-100 sm:-translate-y-12">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-red-50/50">
                <h3 class="text-lg font-bold text-red-900">Cerrar Periodo de Liquidación</h3>
                <button onclick="document.getElementById('modalCerrarPeriodo').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="formCerrarPeriodo" method="POST">
                @csrf
                <div class="px-6 py-6 space-y-4">
                    <div class="p-4 bg-red-50 border border-red-100 rounded-xl">
                        <p class="text-sm text-red-800 font-medium">
                            ¿Está seguro de cerrar el periodo <span id="cierre_rango" class="font-bold"></span>?
                        </p>
                        <p class="text-xs text-red-600 mt-2">
                            Esta acción bloqueará nuevas liquidaciones y modificaciones en este ciclo. Asegúrese de
                            haber generado todos los comprobantes necesarios.
                        </p>
                    </div>

                    {{-- Generación Automática --}}
                    <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl space-y-3">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="generar_siguiente" id="generar_siguiente" value="1" checked
                                class="mt-1.5 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div>
                                <label for="generar_siguiente" class="text-sm font-bold text-blue-900 cursor-pointer">
                                    Generar automáticamente el siguiente periodo
                                </label>
                                <p class="text-xs text-blue-700 mt-0.5">Basado en la frecuencia actual.</p>
                            </div>
                        </div>

                        <div id="preview_siguiente" class="hidden pl-7 pt-1 border-t border-blue-100 mt-2">
                            <p class="text-[11px] font-bold text-blue-800 uppercase tracking-wider">Sugerencia:</p>
                            <p class="text-xs text-blue-900 mt-1">
                                <span id="sug_rango" class="font-extrabold text-blue-600"></span>
                                <span id="sug_freq"
                                    class="ml-2 px-2 py-0.5 bg-blue-200 text-blue-700 rounded text-[10px] font-bold uppercase"></span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex flex-col sm:flex-row-reverse gap-3 rounded-b-2xl">
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-2.5 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 shadow-lg shadow-red-200 transition-all">
                        Confirmar y Cerrar
                    </button>
                    <button type="button"
                        onclick="document.getElementById('modalCerrarPeriodo').classList.add('hidden')"
                        class="w-full sm:w-auto px-6 py-2.5 bg-white text-gray-700 text-sm font-bold rounded-xl border border-gray-300 hover:bg-gray-50 transition-all">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirModalCierre(id, inicio, fin) {
        const modal = document.getElementById('modalCerrarPeriodo');
        const form = document.getElementById('formCerrarPeriodo');
        const rangeText = document.getElementById('cierre_rango');
        const previewSiguiente = document.getElementById('preview_siguiente');
        const sugRango = document.getElementById('sug_rango');
        const sugFreq = document.getElementById('sug_freq');

        // Construir URL robusta usando route() de Laravel
        const urlCierre = "{{ route('periodos.cerrar', ['id' => ':id']) }}".replace(':id', id);
        form.action = urlCierre;

        rangeText.textContent = `${inicio} - ${fin}`;

        // Reset preview
        previewSiguiente.classList.add('hidden');

        // Fetch suggestion usando route()
        const urlSuggest = "{{ route('periodos.suggest', ['id' => ':id']) }}".replace(':id', id);
        fetch(urlSuggest)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    sugRango.textContent = `${data.inicio_formato} - ${data.fin_formato}`;
                    sugFreq.textContent = data.tipo_frecuencia;
                    previewSiguiente.classList.remove('hidden');
                }
            });

        modal.classList.remove('hidden');
    }
</script>