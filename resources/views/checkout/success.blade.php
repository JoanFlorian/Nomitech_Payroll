<x-guest-layout>
    <x-auth.background-shapes />

    <x-ui.card>
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-2">¡Pago Exitoso!</h2>

            <p class="text-gray-600 mb-6">
                Hemos recibido tu pago correctamente. Tu licencia se está activando en este momento.
                Recibirás un correo de confirmación en breve.
            </p>

            <a href="{{ route('empleados.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Ir a Empleados
            </a>
        </div>
    </x-ui.card>
</x-guest-layout>