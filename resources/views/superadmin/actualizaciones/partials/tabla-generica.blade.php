<!-- Buscador mejorado -->
<div class="mb-5">
    <div class="relative">
        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input 
            type="text" 
            id="buscar-items" 
            placeholder="Buscar {{ $config['placeholderBusqueda'] ?? 'por nombre o código' }}..." 
            class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-3 focus:ring-blue-100 transition bg-gray-50 hover:bg-white"
        >
    </div>
    <p class="text-xs text-gray-400 mt-2 ml-1">
        <i class="bi bi-info-circle mr-1"></i>{{ $items->count() }} registros encontrados
    </p>
</div>

<!-- Tabla mejorada -->
<div class="overflow-y-auto max-h-[28rem] rounded-xl border border-gray-200 shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10">
            <tr>
                @foreach($config['columnas'] as $columna)
                <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b-2 border-gray-200">{{ $columna['label'] }}</th>
                @endforeach
                <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-600 uppercase tracking-wider border-b-2 border-gray-200">Acción</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($items as $index => $item)
            <tr class="fila-item hover:bg-blue-50/50 transition-colors duration-150 {{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30' }}">
                @foreach($config['columnas'] as $columna)
                <td class="px-5 py-3.5 columna-{{ $columna['clave'] }} text-gray-700 font-medium">
                    @if($columna['tipo'] === 'relacion')
                        {{ $item->{$columna['relacion']}?->{$columna['mostrar']} ?? 'N/A' }}
                    @else
                        @if($columna['clave'] === 'telefono')
                            @if($item->{$columna['clave']})
                                <span class="inline-flex items-center gap-1.5 text-gray-600">
                                    <i class="bi bi-telephone text-xs text-blue-500"></i>
                                    {{ $item->{$columna['clave']} }}
                                </span>
                            @else
                                <span class="text-gray-400 italic text-xs">Sin teléfono</span>
                            @endif
                        @elseif(str_starts_with($columna['clave'], 'id_'))
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-mono font-semibold">
                                {{ $item->{$columna['clave']} ?? '-' }}
                            </span>
                        @else
                            {{ $item->{$columna['clave']} ?? '-' }}
                        @endif
                    @endif
                </td>
                @endforeach
                <td class="px-5 py-3.5 text-center">
                    <button 
                        type="button"
                        onclick="abrirEdicionModal('{{ $config['tipo'] }}', {{ $item->{$config['campoId']} }}, {{ json_encode($item->toArray()) }}, {{ json_encode($config) }})"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-all duration-150 shadow-sm hover:shadow-md"
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
