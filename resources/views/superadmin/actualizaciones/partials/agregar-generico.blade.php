<!-- MODAL GENÉRICO DE AGREGAR -->
<div id="modalAgregar" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-2xl p-8 relative shadow-2xl border border-gray-100 max-h-screen overflow-y-auto">

        <!-- Botón cerrar -->
        <button onclick="cerrarModalAgregar()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition p-2">
            <i class="bi bi-x-lg text-xl"></i>
        </button>

        <!-- Header dinámico -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <div class="flex items-start gap-4">
                <div id="headerIconoAgregar" class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-lg flex-shrink-0">
                    <i class="bi bi-plus-lg text-white text-xl"></i>
                </div>
                <div>
                    <h2 id="headerTituloAgregar" class="text-2xl font-bold text-gray-900">Agregar</h2>
                    <p id="headerSubtituloAgregar" class="text-gray-500 text-sm mt-1">Crea un nuevo registro</p>
                </div>
            </div>
        </div>

        <!-- Formulario dinámico -->
        <form id="formAgregar" method="POST" action="" class="space-y-6">
            @csrf
            
            <!-- Contenedor de campos dinámicos -->
            <div id="camposContenedorAgregar" class="space-y-4">
                <!-- Se genera dinámicamente -->
            </div>

            <!-- Botones -->
            <div class="flex gap-3 pt-6 border-t border-gray-200">
                <button 
                    type="button" 
                    onclick="cerrarModalAgregar()"
                    class="flex-1 px-4 py-3 text-sm font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 active:bg-gray-100 transition flex items-center justify-center gap-2"
                >
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button 
                    type="submit"
                    id="btnGuardarAgregar"
                    class="flex-1 px-4 py-3 text-sm font-semibold rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 active:from-blue-700 active:to-blue-800 transition shadow-md flex items-center justify-center gap-2"
                >
                    <i class="bi bi-check-circle"></i> Agregar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalAgregar(tipo, configuracion) {
    if (typeof configuracion === 'string') {
        configuracion = JSON.parse(configuracion);
    }
    
    const modal = document.getElementById('modalAgregar');
    const headerIcono = document.getElementById('headerIconoAgregar');
    const headerTitulo = document.getElementById('headerTituloAgregar');
    const headerSubtitulo = document.getElementById('headerSubtituloAgregar');
    const contenedor = document.getElementById('camposContenedorAgregar');
    const formAgregar = document.getElementById('formAgregar');
    const btnGuardar = document.getElementById('btnGuardarAgregar');
    
    // Configurar header
    headerIcono.className = `w-14 h-14 rounded-xl bg-gradient-to-br ${configuracion.colores.icono} flex items-center justify-center shadow-lg flex-shrink-0`;
    headerTitulo.textContent = `Agregar ${tipo.charAt(0).toUpperCase() + tipo.slice(1).replace(/_/g, ' ')}`;
    headerSubtitulo.textContent = `Crea un nuevo ${tipo.toLowerCase().replace(/_/g, ' ')}`;
    
    // Configurar botón guardar
    btnGuardar.className = `flex-1 px-4 py-3 text-sm font-semibold rounded-lg bg-gradient-to-r ${configuracion.colores.boton} text-white hover:opacity-90 transition shadow-md flex items-center justify-center gap-2`;
    
    // Limpiar y generar campos
    contenedor.innerHTML = '';
    
    configuracion.campos.forEach(campo => {
        let html = '';
        
        if (campo.tipo === 'text' || campo.tipo === 'email') {
            html = `
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                        <i class="bi ${campo.icono} mr-2"></i>${campo.label}
                        ${campo.requerido ? '<span class="text-red-500">*</span>' : ''}
                    </label>
                    <input 
                        type="${campo.tipo}" 
                        name="${campo.clave}" 
                        placeholder="${campo.placeholder || ''}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50 hover:bg-white" 
                        ${campo.requerido ? 'required' : ''}
                    >
                </div>
            `;
        } else if (campo.tipo === 'select') {
            let options = '<option value="">Selecciona una opción</option>';
            if (campo.opciones) {
                options += campo.opciones.map(opt => 
                    `<option value="${opt.id}">${opt.nombre}</option>`
                ).join('');
            }
            html = `
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                        <i class="bi ${campo.icono} mr-2"></i>${campo.label}
                        ${campo.requerido ? '<span class="text-red-500">*</span>' : ''}
                    </label>
                    <select 
                        name="${campo.clave}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50 hover:bg-white"
                        ${campo.requerido ? 'required' : ''}
                    >
                        ${options}
                    </select>
                </div>
            `;
        } else if (campo.tipo === 'textarea') {
            html = `
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                        <i class="bi ${campo.icono} mr-2"></i>${campo.label}
                        ${campo.requerido ? '<span class="text-red-500">*</span>' : ''}
                    </label>
                    <textarea 
                        name="${campo.clave}"
                        placeholder="${campo.placeholder || ''}"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition bg-gray-50 hover:bg-white resize-none"
                        ${campo.requerido ? 'required' : ''}
                    ></textarea>
                </div>
            `;
        }
        
        contenedor.innerHTML += html;
    });
    
    // Agregar input hidden para tipo
    contenedor.innerHTML += `<input type="hidden" name="tipo" value="${tipo}">`;
    
    // Configurar formulario
    formAgregar.action = `/superadmin/${tipo}`;
    
    // Abrir modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarModalAgregar() {
    const modal = document.getElementById('modalAgregar');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('DOMContentLoaded', function() {
    const formAgregar = document.getElementById('formAgregar');
    if (formAgregar) {
        formAgregar.addEventListener('submit', function(e) {
            e.preventDefault();
            this.submit();
        });
    }
});
</script>
