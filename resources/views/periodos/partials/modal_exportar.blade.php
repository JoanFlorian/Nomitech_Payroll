{{-- MODAL EXPORTAR BANCO / PAB --}}
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
            <div class="bg-gradient-to-r from-[#1A237E] to-[#283593] p-6 rounded-t-xl flex justify-between items-center text-white">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <span class="material-icons" id="icono_modal_export">account_balance</span>
                    <span id="titulo_modal_export">Exportar Pagos Bancarios</span>
                </h2>
                <button onclick="cerrarModalExportar()" class="text-white hover:text-gray-200 transition-colors">
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

            <!-- Contenido que se mostrará tras cargar datos -->
            <div id="content_export" class="hidden">
                <form id="formExportarBanco" onsubmit="procesarExportacion(event)" class="space-y-6">
                    @csrf
                    <input type="hidden" name="tipo_exportacion" id="tipo_exportacion" value="bank">

                    <!-- Info Box -->
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

                        {{-- FORMATO CSV (visible para bank/general) --}}
                        <div id="seccion_formato_csv" class="space-y-2">
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

                        {{-- CAMPOS PAB (visibles solo para PAB) --}}
                        <div id="seccion_pab_fields" class="hidden space-y-4">
                            <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="material-icons text-amber-600 text-lg">payments</span>
                                    <p class="text-sm font-bold text-amber-800">Archivo PAB — Bancolombia</p>
                                </div>
                                <p class="text-xs text-amber-700">Archivo plano de posiciones fijas para pagos masivos automatizados.</p>
                            </div>

                            <div>
                                <label for="cuenta_debito" class="block text-sm font-bold text-gray-700 mb-1">Cuenta a Debitar</label>
                                <input type="text" name="cuenta_debito" id="cuenta_debito"
                                    placeholder="Número de cuenta origen"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 text-sm"
                                    maxlength="20">
                                <p class="text-xs text-gray-400 mt-1">Cuenta de la empresa desde donde se debitarán los pagos.</p>
                            </div>

                            <div>
                                <label for="tipo_cuenta_debito" class="block text-sm font-bold text-gray-700 mb-1">Tipo de Cuenta</label>
                                <select name="tipo_cuenta_debito" id="tipo_cuenta_debito"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 text-sm bg-white">
                                    <option value="">Seleccionar...</option>
                                    <option value="S">Ahorros</option>
                                    <option value="D">Corriente</option>
                                </select>
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
                        <h4 class="text-xl font-bold text-gray-900">¡Exportación Exitosa!</h4>
                        <p id="success_pab_msg" class="text-sm text-gray-500 mt-2">Los archivos de dispersión bancaria y resumen han sido generados.</p>
                        <p id="success_general_msg" class="text-sm text-gray-500 mt-2 hidden">El archivo de dispersión bancaria está listo para descargar.</p>
                    </div>
                    
                    <div class="w-full flex flex-col gap-3">
                        <a id="btn_descargar_txt" href="#" target="_blank"
                            class="w-full px-8 py-4 bg-emerald-600 text-white font-bold rounded-2xl hover:bg-emerald-700 shadow-xl shadow-emerald-200 transition-all flex items-center justify-center gap-3">
                            <span class="material-icons">description</span>
                            <span id="text_btn_txt">Descargar Archivo (.txt)</span>
                        </a>
                        <a id="btn_descargar_excel" href="#" target="_blank"
                            class="w-full px-8 py-4 bg-white text-[#1A237E] font-bold rounded-2xl hover:bg-gray-50 border-2 border-[#1A237E] shadow-lg transition-all flex items-center justify-center gap-3 hidden">
                            <span class="material-icons">table_view</span>
                            <span>Descargar Resumen (.xlsx)</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPeriodoId = null;
    let currentTipoExport = 'bank';
    // La vista padre debe redefinir `exportBaseUrl` si requiere una ruta distinta a `/periodos`
    let exportBaseUrl = window.exportBaseUrl || '/periodos';

    function abrirModalExportar(id, tipo = 'bank') {
        currentPeriodoId = id;
        currentTipoExport = tipo;
        document.getElementById('tipo_exportacion').value = tipo;

        // Configurar títulos e ícono según tipo
        const titulos = {
            'bank': 'Exportar Pagos Bancarios',
            'general': 'Resumen General de Nómina (Excel)',
            'pab': 'Generar Archivo PAB — Bancolombia'
        };
        const iconos = {
            'bank': 'account_balance',
            'general': 'analytics',
            'pab': 'payments'
        };
        document.getElementById('titulo_modal_export').textContent = titulos[tipo] || titulos['bank'];
        document.getElementById('icono_modal_export').textContent = iconos[tipo] || iconos['bank'];

        // Mostrar/ocultar secciones según tipo
        const seccionCSV = document.getElementById('seccion_formato_csv');
        const seccionPAB = document.getElementById('seccion_pab_fields');

        if (tipo === 'pab') {
            seccionCSV.classList.add('hidden');
            seccionPAB.classList.remove('hidden');
            // Limpiar campos PAB
            document.getElementById('cuenta_debito').value = '';
            document.getElementById('tipo_cuenta_debito').value = '';
        } else if (tipo === 'general') {
            seccionCSV.classList.add('hidden'); // Ocultamos CSV porque ahora es Excel fixed
            seccionPAB.classList.add('hidden');
            // Forzar formato a XLSX internamente si fuera necesario, 
            // aunque el servicio ya lo forza para 'general'
        } else {
            seccionCSV.classList.remove('hidden');
            seccionPAB.classList.add('hidden');
        }

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

        fetch(`${exportBaseUrl}/${id}/export-preview`)
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

        // Validación de campos PAB
        if (currentTipoExport === 'pab') {
            const cuentaDebito = document.getElementById('cuenta_debito').value.trim();
            const tipoCuentaDebito = document.getElementById('tipo_cuenta_debito').value;

            if (!cuentaDebito) {
                Swal.fire('Campo requerido', 'Debe ingresar la cuenta a debitar.', 'warning');
                return;
            }
            if (!tipoCuentaDebito) {
                Swal.fire('Campo requerido', 'Debe seleccionar el tipo de cuenta a debitar.', 'warning');
                return;
            }
        }

        btn.disabled = true;
        btn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div> Procesando...';

        // Determinar la URL según el tipo de exportación
        let exportUrl;
        if (currentTipoExport === 'pab') {
            exportUrl = `${exportBaseUrl}/${currentPeriodoId}/exportar-pab`;
        } else {
            exportUrl = `${exportBaseUrl}/${currentPeriodoId}/exportar`;
        }

        fetch(exportUrl, {
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
                    
                    const btnTxt = document.getElementById('btn_descargar_txt');
                    const btnExcel = document.getElementById('btn_descargar_excel');
                    const msgPab = document.getElementById('success_pab_msg');
                    const msgGen = document.getElementById('success_general_msg');
                    const labelTxt = document.getElementById('text_btn_txt');

                    btnTxt.href = data.download_url;
                    
                    if (currentTipoExport === 'pab' && data.download_excel_url) {
                        btnExcel.href = data.download_excel_url;
                        btnExcel.classList.remove('hidden');
                        msgPab.classList.remove('hidden');
                        msgGen.classList.add('hidden');
                        labelTxt.textContent = 'Descargar Archivo PAB (.txt)';
                    } else if (currentTipoExport === 'general') {
                        // Para general, el archivo principal ya es el Excel
                        btnTxt.classList.add('hidden');
                        btnExcel.href = data.download_url;
                        btnExcel.classList.remove('hidden');
                        msgPab.classList.add('hidden');
                        msgGen.classList.remove('hidden');
                        msgGen.textContent = 'El resumen general en Excel se ha generado correctamente.';
                    } else {
                        btnExcel.classList.add('hidden');
                        msgPab.classList.add('hidden');
                        msgGen.classList.remove('hidden');
                        labelTxt.textContent = 'Descargar Archivo';
                    }
                } else {
                    Swal.fire('Error', data.message || 'Error al generar el archivo.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<span>Generar Archivo</span>';
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Error al procesar la exportación.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<span>Generar Archivo</span>';
            });
    }
</script>