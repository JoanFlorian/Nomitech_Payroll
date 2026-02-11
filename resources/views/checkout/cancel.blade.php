<x-guest-layout>
    <x-auth.background-shapes />

    <x-ui.card>
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-2">Pago Cancelado</h2>

            <p class="text-gray-600 mb-6">
                El proceso de pago fue cancelado. No se ha realizado ningún cobro.
                Puedes intentar realizar el pago nuevamente cuando desees.
            </p>

            <a href="{{ route('licencia.pending') }}"
                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Volver a Detalles de Licencia
            </a>
        </div>
    </x-ui.card>
</x-guest-layout>