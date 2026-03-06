{{-- MODAL EXPORTAR BANCO --}}
<div id="modalExportarBanco" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Fondo oscuro con figuritas decorativas -->
        <div class="fixed inset-0 bg-gray-900/85 transition-opacity" aria-hidden="true" onclick="cerrarModalExportar()">
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
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-emerald-50/50">
                <h3 class="text-lg font-bold text-emerald-900">Exportar Pagos Bancarios</h3>
                <button onclick="cerrarModalExportar()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div id="loading_export" class="p-12 flex flex-col items-center justify-center space-y-4">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600"></div>
                <p class="text-sm text-gray-500 font-medium">Cargando resumen de pagos...</p>
            </div>

            <div id="content_export" class="hidden">
                <form id="formExportarBanco" onsubmit="procesarExportacion(event)">
                    @csrf
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-[10px] uppercase tracking-wider text-gray-500 font-bold">Periodo</p>
                                <p id="exp_periodo" class="text-lg font-bold text-gray-900">-</p>
                            </div>
                            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                                <p class="text-[10px] uppercase tracking-wider text-emerald-600 font-bold">Empleados</p>
                                <p id="exp_empleados" class="text-lg font-bold text-emerald-900">-</p>
                            </div>
                        </div>

                        <div class="p-5 bg-emerald-600 rounded-2xl text-white shadow-lg shadow-emerald-100">
                            <p class="text-xs opacity-80 font-medium uppercase tracking-widest">Total a Dispersar</p>
                            <p id="exp_total" class="text-3xl font-black mt-1">$ 0.00</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-700">Formato de Archivo</label>
                            <div class="grid grid-cols-1 gap-3">
                                <label
                                    class="flex items-center p-4 bg-white border-2 border-emerald-100 rounded-xl cursor-pointer hover:bg-emerald-50 transition-all group">
                                    <input type="radio" name="formato" value="CSV" checked
                                        class="h-5 w-5 text-emerald-600 focus:ring-emerald-500 border-gray-300">
                                    <div class="ml-4">
                                        <p class="text-sm font-bold text-gray-900">CSV Universal</p>
                                        <p class="text-xs text-gray-500">Formato estándar compatible con cualquier hoja
                                            de cálculo.</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex flex-col sm:flex-row-reverse gap-3 rounded-b-2xl">
                        <button type="submit" id="btn_generar_export"
                            class="w-full sm:w-auto px-8 py-3 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition-all flex items-center justify-center gap-2">
                            <span>Generar Archivo</span>
                        </button>
                        <button type="button" onclick="cerrarModalExportar()"
                            class="w-full sm:w-auto px-6 py-3 bg-white text-gray-700 text-sm font-bold rounded-xl border border-gray-300 hover:bg-gray-50 transition-all text-center">
                            Cancelar
                        </button>
                    </div>
                </form>

                {{-- ÉXITO --}}
                <div id="success_export" class="hidden px-6 py-12 flex flex-col items-center text-center space-y-6">
                    <div class="bg-emerald-100 p-4 rounded-full">
                        <svg class="h-12 w-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">¡Archivo Generado!</h4>
                        <p class="text-sm text-gray-500 mt-2">El archivo de dispersión bancaria está listo para ser
                            procesado.</p>
                    </div>
                    <a id="btn_descargar_final" href="#" target="_blank"
                        class="w-full max-w-xs px-8 py-4 bg-emerald-600 text-white font-bold rounded-2xl hover:bg-emerald-700 shadow-xl shadow-emerald-200 transition-all flex items-center justify-center gap-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Descargar Archivo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPeriodoId = null;

    function abrirModalExportar(id) {
        currentPeriodoId = id;
        const modal = document.getElementById('modalExportarBanco');
        const loading = document.getElementById('loading_export');
        const content = document.getElementById('content_export');
        const form = document.getElementById('formExportarBanco');
        const success = document.getElementById('success_export');

        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        form.classList.remove('hidden');
        success.classList.add('hidden');

        fetch(`/periodos/${id}/export-preview`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('exp_periodo').textContent = data.periodo;
                    document.getElementById('exp_empleados').textContent = data.empleados;
                    document.getElementById('exp_total').textContent = `$ ${data.total}`;

                    loading.classList.add('hidden');
                    content.classList.remove('hidden');
                } else {
                    alert('Error al cargar resumen: ' + data.message);
                    cerrarModalExportar();
                }
            })
            .catch(err => {
                alert('Error de conexión');
                cerrarModalExportar();
            });
    }

    function cerrarModalExportar() {
        document.getElementById('modalExportarBanco').classList.add('hidden');
    }

    function procesarExportacion(e) {
        e.preventDefault();
        const btn = document.getElementById('btn_generar_export');
        const form = document.getElementById('formExportarBanco');
        const formData = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div> Procesando...';

        fetch(`/periodos/${currentPeriodoId}/exportar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('formExportarBanco').classList.add('hidden');
                    document.getElementById('success_export').classList.remove('hidden');
                    document.getElementById('btn_descargar_final').href = data.download_url;
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = 'Generar Archivo';
                }
            })
            .catch(err => {
                alert('Error al procesar la exportación');
                btn.disabled = false;
                btn.innerHTML = 'Generar Archivo';
            });
    }
</script>