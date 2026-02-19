<div class="mb-4">
    <input 
        type="text" 
        id="buscar-items" 
        placeholder="Buscar {{ $config['placeholderBusqueda'] ?? 'por nombre o código' }}..." 
        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
</div>

<div class="overflow-y-auto max-h-80 border rounded border-gray-200">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 sticky top-0">
            <tr>
                @foreach($config['columnas'] as $columna)
                <th class="p-3 text-left border-b font-semibold text-gray-700">{{ $columna['label'] }}</th>
                @endforeach
                <th class="p-3 text-center border-b font-semibold text-gray-700">Acción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr class="fila-item hover:bg-gray-50 border-b">
                @foreach($config['columnas'] as $columna)
                <td class="p-3 columna-{{ $columna['clave'] }} text-gray-600">
                    @if($columna['tipo'] === 'relacion')
                        {{ $item->{$columna['relacion']}?->{$columna['mostrar']} ?? 'N/A' }}
                    @else
                        {{ $item->{$columna['clave']} ?? '-' }}
                    @endif
                </td>
                @endforeach
                <td class="p-3 text-center">
                    <button 
                        type="button"
                        onclick="abrirEdicionModal('{{ $config['tipo'] }}', {{ $item->{$config['campoId']} }}, {{ json_encode($item->toArray()) }}, {{ json_encode($config) }})"
                        class="px-4 py-2 bg-blue-500 text-white text-xs font-semibold rounded-lg hover:bg-blue-600 active:bg-blue-700 transition shadow-md flex items-center gap-2 mx-auto"
                    >
                        <i class="bi bi-pencil-square"></i> Editar
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBuscar = document.getElementById('buscar-items');
    
    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', function() {
            const busqueda = this.value.toLowerCase().trim();
            const filas = document.querySelectorAll('.fila-item');
            
            filas.forEach(fila => {
                let texto = '';
                fila.querySelectorAll('[class^="columna-"]').forEach(celda => {
                    texto += celda.textContent.toLowerCase().trim() + ' ';
                });
                
                const coincide = texto.includes(busqueda);
                fila.style.display = coincide ? '' : 'none';
            });
        });
    }
});

function abrirEdicion(tipo, id, datos, config) {
    abrirEdicionGenerico(tipo, id, datos, config);
}
</script>
