<x-guest-layout>
    <x-auth.background-shapes />

    <x-ui.card>
        <x-auth.form-header title="Resumen de tu pedido" description="Estás a un paso de activar tu cuenta Nomitech." />

        <div class="space-y-6">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600 font-medium">Plan Seleccionado</span>
                    <span class="text-gray-900 font-bold">{{ $plan->nombre }}</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600 font-medium">Empresa</span>
                    <span class="text-gray-900">{{ $pago->empresa->razon_social }}</span>
                </div>
                <div class="border-t border-gray-200 my-2"></div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-800 font-bold text-lg">Total a Pagar</span>
                    <span class="text-primary-600 font-bold text-xl">
                        ${{ number_format($pago->valor, 0, ',', '.') }} {{ $pago->moneda }}
                    </span>
                </div>
            </div>

            <form action="{{ route('checkout.session', $pago->id) }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Proceder al Pago con Stripe
                </button>
            </form>

            <div class="text-center">
                <a href="{{ route('licencia.pending') }}" class="text-sm text-gray-500 hover:text-gray-900">
                    Cancelar y volver al inicio
                </a>
            </div>
        </div>
    </x-ui.card>
</x-guest-layout>