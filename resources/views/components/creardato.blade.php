{{-- Botón flotante + icono --}}
<button
    @click="$dispatch('open-empleado-modal')"
    class="fixed bottom-8 right-8 bg-[rgb(16,185,129)] text-white
        w-16 h-16 rounded-full flex items-center justify-center
        shadow-lg hover:bg-emerald-600 transition-colors z-50">
    <span class="material-icons text-4xl">add</span>
</button>
