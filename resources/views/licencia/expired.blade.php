<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licencia Expirada | Nomitech</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #1564C0;
            --accent: #0CB983;
            --background-light: #f8fafc;
            --background-dark: #0f172a;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background-color: var(--background-light);
        }

        .premium-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .premium-card:hover {
            transform: translateY(-8px);
        }

        .glass-background {
            background: radial-gradient(circle at top right, rgba(21, 100, 192, 0.05), transparent),
                radial-gradient(circle at bottom left, rgba(12, 185, 131, 0.05), transparent);
        }
    </style>
</head>

<body class="glass-background min-h-screen">

    <header class="py-8 px-6">
        <div class="max-w-7xl mx-auto flex items-center gap-2">
            <div class="size-9 bg-primary rounded-lg flex items-center justify-center text-white">
                <span class="material-symbols-outlined text-[24px]">account_balance_wallet</span>
            </div>
            <span class="text-2xl font-extrabold text-primary">Nomitech</span>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="text-center mb-16">
            <div
                class="inline-flex items-center justify-center size-20 bg-red-100 text-red-600 rounded-2xl mb-6 shadow-sm">
                <span class="material-symbols-outlined text-4xl">timer_off</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4 tracking-tight">Tu licencia ha expirado
            </h1>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto font-medium">
                Detectamos que el periodo de validez de tu plan ha terminado. Para seguir gestionando tu nómina y
                cumplir con la DIAN, selecciona un plan para renovar.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($planes as $plan)
                    <div
                        class="premium-card relative p-8 rounded-[2rem] bg-white border-2 {{ $plan->id == $currentPlanId ? 'border-primary shadow-2xl shadow-primary/10' : 'border-slate-100 shadow-xl shadow-slate-200/50' }} flex flex-col">

                        @if($plan->id == $currentPlanId)
                            <div
                                class="absolute -top-4 left-1/2 -translate-x-1/2 px-6 py-1.5 bg-primary text-white text-xs font-bold rounded-full shadow-lg">
                                TU PLAN ANTERIOR
                            </div>
                        @endif

                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="font-extrabold text-2xl text-slate-800 mb-1">{{ $plan->nombre }}</h3>
                                <p class="text-slate-500 text-sm font-medium">{{ $plan->descripcion }}</p>
                            </div>
                            @if($plan->destacado)
                                <span
                                    class="px-3 py-1 bg-accent/10 text-accent text-[10px] font-black uppercase tracking-wider rounded-lg border border-accent/20">
                                    Recomendado
                                </span>
                            @endif
                        </div>

                        <div class="mb-8">
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-4xl font-black text-slate-900">${{ number_format($plan->valor, 0, ',', '.') }}</span>
                                <span class="text-slate-500 font-bold">/ mes</span>
                            </div>
                        </div>

                        <div class="space-y-4 mb-10 flex-grow">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Lo que incluye:</p>
                            @php
                                $features = is_string($plan->features) ? json_decode($plan->features, true) : $plan->features;
                            @endphp
                            @if($features && is_array($features))
                                @foreach($features as $feature)
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="size-5 rounded-full bg-accent/10 flex items-center justify-center text-accent shrink-0 mt-0.5">
                                            <span class="material-symbols-outlined text-[14px] font-bold">check</span>
                                        </div>
                                        <span class="text-sm text-slate-600 font-medium">{{ $feature }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <form action="{{ route('licencia.renew') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit"
                                class="w-full py-4 rounded-2xl font-extrabold transition-all 
                                    {{ $plan->destacado || $plan->id == $currentPlanId
                ? 'bg-primary text-white shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0'
                : 'bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100 hover:border-slate-300' }}">
                                {{ $plan->id == $currentPlanId ? 'Renovar ahora' : 'Cambiar y renovar' }}
                            </button>
                        </form>
                    </div>
            @endforeach
        </div>

        <div class="mt-20 border-t border-slate-200 pt-12 text-center">
            <p class="text-slate-400 text-sm font-medium mb-6 italic">¿Necesitas ayuda con tu renovación? Contacta a
                nuestro equipo de soporte.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                <a href="{{ route('logout') }}"
                    class="flex items-center gap-2 px-6 py-2 text-slate-500 hover:text-primary font-bold transition-all group">
                    <span
                        class="material-symbols-outlined group-hover:-translate-x-1 transition-transform">logout</span>
                    Cerrar Sesión
                </a>
                <span class="hidden sm:block w-px h-6 bg-slate-200"></span>
                <a href="mailto:soporte@nomitech.com"
                    class="flex items-center gap-2 px-6 py-2 text-slate-500 hover:text-primary font-bold transition-all group">
                    <span class="material-symbols-outlined">help_center</span>
                    Centro de Ayuda
                </a>
            </div>
        </div>
    </main>

    <footer class="py-12 text-center">
        <p class="text-slate-400 text-xs font-medium">© 2026 Nomitech. Todos los derechos reservados.</p>
    </footer>

</body>

</html>