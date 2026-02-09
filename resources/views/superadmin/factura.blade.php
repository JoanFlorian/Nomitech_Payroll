{{-- MODAL FACTURA --}}
<div id="modalFactura" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h2 class="text-xl font-bold text-blue-600">Factura de Compra</h2>
            <button type="button" onclick="cerrarFactura()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-6">
            {{-- EMPRESA EMISORA --}}
            <div>
                <div class="flex items-start gap-4 mb-4">
                    <div class="bg-blue-100 p-3 rounded">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="empresaEmisora" class="font-bold text-lg"></h3>
                        <p id="empresaRazonSocial" class="text-sm text-gray-600"></p>
                        <p id="empresaNit" class="text-sm text-gray-600"></p>
                        <p id="empresaDireccion" class="text-sm text-gray-600"></p>
                        <p id="empresaEmail" class="text-sm text-gray-600"></p>
                    </div>
                </div>
            </div>

            {{-- NÚMERO Y FECHA --}}
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 font-semibold">NÚMERO DE FACTURA</p>
                    <h4 id="numeroFactura" class="text-lg font-bold text-blue-600"></h4>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-semibold">FECHA</p>
                    <p id="fechaFactura" class="font-semibold"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-semibold">ESTADO</p>
                    <p id="estadoFactura" class="font-semibold text-green-600"></p>
                </div>
            </div>

            {{-- FACTURADO A --}}
            <div class="border-t pt-4">
                <h4 class="text-xs text-gray-500 font-semibold mb-2">FACTURADO A</h4>
                <p id="clienteNombre" class="font-bold"></p>
                <p id="clienteNit" class="text-sm text-gray-600"></p>
                <p id="clienteDireccion" class="text-sm text-gray-600"></p>
            </div>

            {{-- MÉTODO DE PAGO --}}
            <div class="border-t pt-4">
                <h4 class="text-xs text-gray-500 font-semibold mb-2">MÉTODO DE PAGO</h4>
                <div class="flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 8H4V6h16m0 12H4v-2h16m0-6H4v-2h16z"/>
                    </svg>
                    <p id="metodoPago" class="font-semibold"></p>
                </div>
            </div>

            {{-- ITEMS --}}
            <div class="border-t pt-4">
                <h4 class="text-xs text-gray-500 font-semibold mb-3">CONCEPTO</h4>
                <div id="itemsContainer" class="space-y-4"></div>
            </div>

            {{-- TOTALES --}}
            <div class="border-t pt-4">
                <div class="space-y-2">
                    <div class="flex justify-between text-right">
                        <span class="text-gray-600">Subtotal</span>
                        <span id="subtotal" class="font-semibold"></span>
                    </div>
                    <div class="flex justify-between text-right">
                        <span class="text-gray-600">IVA (19%)</span>
                        <span id="iva" class="font-semibold"></span>
                    </div>
                    <div class="flex justify-between text-right border-t pt-2">
                        <span class="font-bold">Total</span>
                        <span id="total" class="text-lg font-bold text-blue-600"></span>
                    </div>
                </div>
            </div>

            {{-- BOTONES --}}
            <div class="border-t pt-6 flex gap-3 justify-center">
                <button type="button" onclick="cerrarFactura()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50">
                    Cerrar
                </button>
                <button type="button" id="btnDescargarPdf" class="px-6 py-2 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18h14v2H5z"/>
                    </svg>
                    Descargar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let pagoIdActual = null;

function verFactura(pagoId) {
    pagoIdActual = pagoId;
    
    fetch(`/superadmin/factura/${pagoId}`)
        .then(response => response.json())
        .then(data => {
            // Empresa emisora
            document.getElementById('empresaEmisora').textContent = data.empresa_emisora.nombre;
            document.getElementById('empresaRazonSocial').textContent = data.empresa_emisora.razon_social;
            document.getElementById('empresaNit').textContent = 'NIT: ' + data.empresa_emisora.nit;
            document.getElementById('empresaDireccion').textContent = data.empresa_emisora.direccion;
            document.getElementById('empresaEmail').textContent = data.empresa_emisora.email;

            // Número y fecha
            document.getElementById('numeroFactura').textContent = data.numero_factura;
            document.getElementById('fechaFactura').textContent = 'Fecha: ' + data.fecha;
            document.getElementById('estadoFactura').textContent = data.estado;

            // Cliente
            document.getElementById('clienteNombre').textContent = data.cliente.razon_social;
            document.getElementById('clienteNit').textContent = 'NIT: ' + data.cliente.nit;
            document.getElementById('clienteDireccion').textContent = data.cliente.direccion;

            // Método de pago
            document.getElementById('metodoPago').textContent = data.metodo_pago;

            // Items
            const itemsContainer = document.getElementById('itemsContainer');
            itemsContainer.innerHTML = '';
            data.items.forEach(item => {
                const itemHtml = `
                    <div class="space-y-1">
                        <div class="flex justify-between">
                            <span class="font-semibold">${item.concepto}</span>
                            <span class="text-right">$${parseFloat(item.precio_unitario).toLocaleString('es-CO', {minimumFractionDigits: 2})}</span>
                        </div>
                        <p class="text-sm text-gray-600">${item.descripcion}</p>
                        <div class="grid grid-cols-2 gap-4 text-sm pt-2">
                            <div>
                                <span class="text-gray-600">Cantidad</span>
                                <p class="font-semibold">${item.cantidad}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-gray-600">Precio Unitario</span>
                                <p class="font-semibold">$${parseFloat(item.precio_unitario).toLocaleString('es-CO', {minimumFractionDigits: 2})}</p>
                            </div>
                        </div>
                    </div>
                `;
                itemsContainer.innerHTML += itemHtml;
            });

            // Totales
            document.getElementById('subtotal').textContent = '$' + parseFloat(data.subtotal).toLocaleString('es-CO', {minimumFractionDigits: 2});
            document.getElementById('iva').textContent = '$' + parseFloat(data.iva).toLocaleString('es-CO', {minimumFractionDigits: 2});
            document.getElementById('total').textContent = '$' + parseFloat(data.total).toLocaleString('es-CO', {minimumFractionDigits: 2});

            // Mostrar modal
            document.getElementById('modalFactura').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar la factura');
        });
}

function cerrarFactura() {
    document.getElementById('modalFactura').classList.add('hidden');
}

function descargarPdf() {
    if (pagoIdActual) {
        window.open(`/superadmin/factura/${pagoIdActual}/pdf`, '_blank');
    }
}

// Configurar evento del botón descargar PDF
document.addEventListener('DOMContentLoaded', function() {
    const btnDescargarPdf = document.getElementById('btnDescargarPdf');
    if (btnDescargarPdf) {
        btnDescargarPdf.addEventListener('click', descargarPdf);
    }
});

// Cerrar modal al presionar Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarFactura();
    }
});
</script>
