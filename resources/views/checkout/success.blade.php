<x-guest-layout>
    <x-auth.background-shapes />

    <x-ui.card>
        <div class="text-center">
            <div class="mb-6">
                <!-- Spinner -->
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-2">Procesando su pago...</h2>
            <p class="text-gray-600 mb-6">
                Estamos confirmando su transacción con Stripe. Esto puede tomar unos segundos.<br>
                Por favor <strong>no cierre esta ventana</strong>.
            </p>

            <div id="status-message" class="hidden p-4 rounded-md bg-blue-50 text-blue-700">
                Esperando confirmación...
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sessionId = "{{ $sessionId }}";
                const statusMessage = document.getElementById('status-message');

                let attempts = 0;
                const maxAttempts = 20; // 1 minute max (3s * 20)

                const checkStatus = async () => {
                    try {
                        const response = await fetch(`/api/payment/status/${sessionId}`);
                        const data = await response.json();

                        if (data.status === 'paid') {
                            // Payment confirmed!
                            statusMessage.classList.remove('hidden', 'bg-blue-50', 'text-blue-700');
                            statusMessage.classList.add('bg-green-50', 'text-green-700');
                            statusMessage.textContent = '¡Pago exitoso! Redirigiendo...';

                            setTimeout(() => {
                                window.location.href = "{{ route('empleados.index') }}"; // Redirect to employees index
                            }, 1000);
                            return true;
                        }
                    } catch (error) {
                        console.error('Error checking status:', error);
                    }
                    return false;
                };

                const interval = setInterval(async () => {
                    attempts++;
                    const isPaid = await checkStatus();

                    if (isPaid) {
                        clearInterval(interval);
                    } else if (attempts >= maxAttempts) {
                        clearInterval(interval);
                        statusMessage.classList.remove('hidden', 'bg-blue-50', 'text-blue-700');
                        statusMessage.classList.add('bg-yellow-50', 'text-yellow-700');
                        statusMessage.innerHTML = 'La confirmación está tardando. <a href="{{ route("empleados.index") }}" class="underline font-bold">Haga clic aquí</a> si ya recibió el correo.';
                    }
                }, 3000);

                // Initial check
                checkStatus();
            });
        </script>
    </x-ui.card>
</x-guest-layout>