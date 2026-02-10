<!DOCTYPE html>
<html lang="es" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO -->
    <title>Software de Nómina Electrónica en Colombia | Nomitech</title>
    <meta name="description"
        content="Software de nómina electrónica para empresas en Colombia. Calcula salarios, prestaciones sociales y reporta a la DIAN automáticamente con Nomitech. Prueba gratis.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://nomitech.com/">

    <!-- Open Graph -->
    <meta property="og:title" content="Software de Nómina Electrónica en Colombia | Nomitech">
    <meta property="og:description" content="Automatiza la nómina, seguridad social y reportes a la DIAN con Nomitech.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://nomitech.com/">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght
    @400;500;600;700;800&display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Estilos específicos para Landing Page -->
    <style>
        :root {
            --primary: #1564C0;
            --accent: #0CB983;
            --background-light: #f8fafc;
            --background-dark: #0f172a;
        }

        .landing-page .bg-primary {
            background-color: var(--primary);
        }

        .landing-page .text-primary {
            color: var(--primary);
        }

        .landing-page .hover\:text-primary:hover {
            color: var(--primary);
        }

        .landing-page .border-primary {
            border-color: var(--primary);
        }

        .landing-page .bg-accent {
            background-color: var(--accent);
        }

        .landing-page .text-accent {
            color: var(--accent);
        }

        .landing-page .shadow-accent\/20 {
            --tw-shadow-color: rgba(12, 185, 131, 0.2);
        }

        .landing-page .border-accent\/30 {
            border-color: rgba(12, 185, 131, 0.3);
        }

        .landing-page .bg-accent\/20 {
            background-color: rgba(12, 185, 131, 0.2);
        }

        .landing-page .bg-accent\/10 {
            background-color: rgba(12, 185, 131, 0.1);
        }

        .landing-page .bg-primary\/5 {
            background-color: rgba(21, 100, 192, 0.05);
        }

        .landing-page .bg-primary\/40 {
            background-color: rgba(21, 100, 192, 0.4);
        }

        .landing-page .bg-primary\/60 {
            background-color: rgba(21, 100, 192, 0.6);
        }

        .landing-page .bg-primary\/80 {
            background-color: rgba(21, 100, 192, 0.8);
        }

        .landing-page .bg-background-light {
            background-color: var(--background-light);
        }

        .landing-page .dark:bg-background-dark {
            background-color: var(--background-dark);
        }

        .landing-page .font-display {
            font-family: 'Manrope', sans-serif;
        }

        .landing-page .hero-gradient {
            background: linear-gradient(135deg, #1564C0 0%, #0d47a1 100%);
        }

        .landing-page .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        /* Swiper Carousel Customization */
        .pricing-swiper {
            padding: 50px 0;
        }

        .pricing-swiper .swiper-slide {
            display: flex;
            height: auto;
        }

        .pricing-swiper .swiper-button-next,
        .pricing-swiper .swiper-button-prev {
            color: var(--primary);
            background-color: rgba(21, 100, 192, 0.1);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .pricing-swiper .swiper-button-next:hover,
        .pricing-swiper .swiper-button-prev:hover {
            background-color: rgba(21, 100, 192, 0.2);
        }

        .pricing-swiper .swiper-button-next::after,
        .pricing-swiper .swiper-button-prev::after {
            font-size: 20px;
        }

        @media (max-width: 768px) {
            .pricing-swiper {
                padding: 30px 0;
            }

            .pricing-swiper .swiper-button-next,
            .pricing-swiper .swiper-button-prev {
                display: none;
            }
        }
    </style>

    <!-- Schema JSON-LD -->
    <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "SoftwareApplication",
            "name": "Nomitech",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "description": "Software de nómina electrónica en Colombia con cálculo automático y reporte a la DIAN.",
            "offers": {
                "@@type": "Offer",
                "price": "49",
                "priceCurrency": "USD"
            }
        }
    </script>
</head>

<body class="landing-page bg-background-light dark:bg-background-dark font-display text-[#1e293b] dark:text-slate-200 transition-colors duration-300">

    <!-- HEADER -->
    <header
        class="fixed top-0 left-0 right-0 z-50 bg-white/90 dark:bg-background-dark/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="size-9 bg-primary rounded-lg flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-[24px]">account_balance_wallet</span>
                </div>
                <span class="text-2xl font-extrabold text-primary">Nomitech</span>
            </div>
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-sm font-bold hover:text-primary transition-colors" href="#features">Funciones</a>
                <a class="text-sm font-bold hover:text-primary transition-colors" href="#workflow">Flujo de trabajo</a>
                <a class="text-sm font-bold hover:text-primary transition-colors" href="#compliance">Cumplimiento</a>
                <a class="text-sm font-bold hover:text-primary transition-colors" href="#pricing">Precios</a>
            </nav>
            <div class="flex items-center gap-4">
                <a href="/login"
                    class="px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all">
                    Iniciar sesión
                </a>
                <a href="{{ route('register.create') }}"
                    class="px-6 py-2.5 bg-accent text-white text-sm font-bold rounded-lg shadow-lg shadow-accent/20 hover:opacity-90 transition-all">
                    Ver planes
                </a>
            </div>
        </div>
    </header>

    <main class="pt-20">

        <!-- HERO -->
        <!-- HERO -->
        <section class="hero-gradient relative overflow-hidden py-24 md:py-32">
            <div class="absolute inset-0 opacity-20 pointer-events-none">
                <svg class="absolute bottom-0 w-full" viewBox="0 0 1440 320">
                    <path
                        d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,213.3C672,224,768,224,864,197.3C960,171,1056,117,1152,101.3C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                        fill="#ffffff" fill-opacity="1"></path>
                </svg>
            </div>

            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center relative z-10">
                <div class="text-white">
                    <span
                        class="inline-block px-4 py-1.5 bg-white/10 backdrop-blur-md rounded-full text-xs font-bold tracking-widest uppercase mb-6 border border-white/20">
                        Solución compatible con la DIAN
                    </span>
                    <h1 class="text-5xl md:text-6xl font-extrabold leading-[1.1] mb-8">
                        La nómina de tu empresa,
                        <span class="text-white/80">calculada y reportada sin errores</span>
                    </h1>
                    <p class="text-lg md:text-xl text-white/90 font-medium mb-10 max-w-lg leading-relaxed">
                        Automatiza tu nómina electrónica en minutos y cumple al 100% con la normativa de la DIAN en
                        Colombia.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('register.create') }}"
                            class="px-8 py-4 bg-white text-primary text-base font-extrabold rounded-lg shadow-xl shadow-black/10 hover:bg-gray-50 transition-all">
                            Iniciar prueba gratuita
                        </a>
                        <a href="/demo"
                            class="px-8 py-4 bg-white/10 backdrop-blur-md text-white text-base font-extrabold rounded-lg border border-white/30 hover:bg-white/20 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">play_circle</span> Ver demo
                        </a>
                    </div>
                </div>

                <!-- DASHBOARD TARJETA ORIGINAL -->
                <div class="relative">
                    <div
                        class="bg-white/95 dark:bg-slate-900 rounded-2xl shadow-2xl p-6 border border-white/20 overflow-hidden transform rotate-2 hover:rotate-0 transition-transform duration-500">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="font-bold text-slate-800 dark:text-white">Resumen de Nómina</h3>
                            <div class="flex gap-2">
                                <div class="size-3 rounded-full bg-red-400"></div>
                                <div class="size-3 rounded-full bg-amber-400"></div>
                                <div class="size-3 rounded-full bg-emerald-400"></div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div
                                class="flex justify-between items-center p-4 bg-background-light dark:bg-slate-800 rounded-xl">
                                <div>
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        Total procesado
                                    </p>
                                    <p class="text-2xl font-black text-primary">$45,230.00</p>
                                </div>
                                <span
                                    class="px-3 py-1 bg-accent/20 text-accent text-xs font-bold rounded-full border border-accent/30 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                    Reportado a la DIAN
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                                    <p class="text-xs font-bold text-slate-500 mb-1">Total empleados</p>
                                    <p class="text-xl font-bold">45</p>
                                </div>
                                <div class="p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                                    <p class="text-xs font-bold text-slate-500 mb-1">Estado</p>
                                    <p class="text-xl font-bold text-accent">Activo</p>
                                </div>
                            </div>
                            <div class="bg-slate-100 dark:bg-slate-800 h-24 rounded-xl flex items-end p-2 gap-1">
                                <div class="bg-primary/40 w-full rounded-t-sm" style="height: 40%"></div>
                                <div class="bg-primary/60 w-full rounded-t-sm" style="height: 60%"></div>
                                <div class="bg-primary/40 w-full rounded-t-sm" style="height: 30%"></div>
                                <div class="bg-primary w-full rounded-t-sm" style="height: 90%"></div>
                                <div class="bg-primary/80 w-full rounded-t-sm" style="height: 70%"></div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute -bottom-8 -left-8 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 max-w-[200px] hidden md:block">
                        <div class="flex items-center gap-3">
                            <div class="size-10 bg-accent/10 rounded-full flex items-center justify-center text-accent">
                                <span class="material-symbols-outlined">verified_user</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold">100% Seguro</p>
                                <p class="text-[10px] text-slate-500">Transacciones cifradas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section class="py-24 max-w-7xl mx-auto px-6" id="features">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-extrabold mb-4">Módulos Integrales</h2>
                <p class="text-slate-500 font-medium max-w-2xl mx-auto italic">
                    Gestión de nómina de punta a punta para empresas colombianas modernas.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <div
                    class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-lg transition-all">
                    <div class="size-12 bg-primary/5 rounded-xl flex items-center justify-center text-primary mb-5">
                        <span class="material-symbols-outlined text-2xl">groups</span>
                    </div>
                    <h3 class="font-bold mb-2">Empleados</h3>
                    <p class="text-sm text-slate-500">Base de datos centralizada para contratos, documentos e historial.
                    </p>
                </div>
                <div
                    class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-lg transition-all">
                    <div class="size-12 bg-primary/5 rounded-xl flex items-center justify-center text-primary mb-5">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                    <h3 class="font-bold mb-2">Nómina</h3>
                    <p class="text-sm text-slate-500">Procesamiento automático de salarios con novedades y horas extra
                        configurables.</p>
                </div>
                <div
                    class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-lg transition-all">
                    <div class="size-12 bg-primary/5 rounded-xl flex items-center justify-center text-primary mb-5">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </div>
                    <h3 class="font-bold mb-2">Provisiones</h3>
                    <p class="text-sm text-slate-500">Cálculo preciso de provisiones y seguridad social (PILA).</p>
                </div>
                <div
                    class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-lg transition-all">
                    <div class="size-12 bg-primary/5 rounded-xl flex items-center justify-center text-primary mb-5">
                        <span class="material-symbols-outlined text-2xl">update</span>
                    </div>
                    <h3 class="font-bold mb-2">Actualizaciones</h3>
                    <p class="text-sm text-slate-500">Ajustes legislativos en tiempo real conforme a la normativa
                        colombiana.</p>
                </div>
                <div
                    class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-lg transition-all">
                    <div class="size-12 bg-primary/5 rounded-xl flex items-center justify-center text-primary mb-5">
                        <span class="material-symbols-outlined text-2xl">description</span>
                    </div>
                    <h3 class="font-bold mb-2">Nómina Electrónica</h3>
                    <p class="text-sm text-slate-500">Transmisión directa a la DIAN con respuesta de validación.</p>
                </div>
            </div>
        </section>

        <!-- WORKFLOW -->
        <section class="py-24 bg-slate-50 dark:bg-slate-900/50" id="workflow">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col lg:flex-row gap-16 items-center">
                    <div class="lg:w-1/2">
                        <h2 class="text-3xl lg:text-4xl font-extrabold mb-8 leading-tight">
                            Automatización sin esfuerzo en
                            <span class="text-primary">3 pasos simples</span>
                        </h2>
                        <div class="space-y-10">
                            <div class="flex gap-6">
                                <div
                                    class="shrink-0 size-12 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xl">
                                    1
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold mb-2">Sincroniza empleados</h4>
                                    <p class="text-slate-500 leading-relaxed">
                                        Importa tus empleados desde Excel o ERP. Configura contratos, salarios y
                                        novedades recurrentes en minutos.
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-6">
                                <div
                                    class="shrink-0 size-12 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xl">
                                    2
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold mb-2">Cálculos automáticos</h4>
                                    <p class="text-slate-500 leading-relaxed">
                                        Nuestro motor procesa provisiones, seguridad social y deducciones legales
                                        automáticamente según la normativa vigente.
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-6">
                                <div
                                    class="shrink-0 size-12 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xl">
                                    3
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold mb-2">Envío a la DIAN en un clic</h4>
                                    <p class="text-slate-500 leading-relaxed">
                                        Finaliza la nómina y transmite los documentos electrónicos a la DIAN con
                                        validación inmediata.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:w-1/2 w-full">
                        <div
                            class="bg-white dark:bg-slate-800 rounded-3xl p-2 shadow-2xl border border-slate-200 dark:border-slate-700">
                            <div class="bg-primary rounded-2xl p-12 text-center text-white">
                                <span class="material-symbols-outlined text-[64px] mb-6">bolt</span>
                                <h3 class="text-2xl font-bold mb-4">¿Listo para optimizar?</h3>
                                <p class="mb-8 text-white/70">
                                    Nuestro equipo de implementación se encarga de la configuración inicial para que te
                                    concentres en hacer crecer tu empresa.
                                </p>
                                <a href="{{ route('register.create') }}"
                                    class="px-8 py-4 bg-accent text-white font-bold rounded-xl hover:bg-opacity-90 transition-all">
                                    Comenzar hoy
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- COMPLIANCE -->
        <section class="py-24 max-w-7xl mx-auto px-6" id="compliance">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="order-2 lg:order-1">
                    <div class="grid grid-cols-2 gap-6">
                        <div
                            class="p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
                            <span class="material-symbols-outlined text-accent text-4xl mb-4">gavel</span>
                            <h4 class="font-bold mb-2">Cumplimiento DIAN</h4>
                            <p class="text-xs text-slate-500">
                                Integración total con las especificaciones de nómina electrónica 1.0.
                            </p>
                        </div>
                        <div
                            class="p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
                            <span class="material-symbols-outlined text-accent text-4xl mb-4">shield_with_heart</span>
                            <h4 class="font-bold mb-2">Preparado para UGPP</h4>
                            <p class="text-xs text-slate-500">
                                Reportes optimizados para auditorías de seguridad social.
                            </p>
                        </div>
                        <div
                            class="p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
                            <span class="material-symbols-outlined text-accent text-4xl mb-4">lock</span>
                            <h4 class="font-bold mb-2">Seguridad de datos</h4>
                            <p class="text-xs text-slate-500">
                                Cifrado de nivel bancario para datos sensibles.
                            </p>
                        </div>
                        <div
                            class="p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
                            <span class="material-symbols-outlined text-accent text-4xl mb-4">fact_check</span>
                            <h4 class="font-bold mb-2">Auditoría completa</h4>
                            <p class="text-xs text-slate-500">
                                Registros trazables para cada cálculo y modificación.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <h2 class="text-3xl lg:text-4xl font-extrabold mb-6">
                        Diseñado para los más estrictos
                        <span class="text-primary">estándares colombianos</span>
                    </h2>
                    <p class="text-slate-500 text-lg mb-8 leading-relaxed">
                        Nomitech es más que software: es tu respaldo legal. Actualizamos constantemente el sistema para
                        cumplir con la DIAN, la UGPP y el Ministerio de Trabajo.
                    </p>
                    <div class="flex items-center gap-3 p-4 bg-slate-100 dark:bg-slate-800 rounded-xl inline-flex">
                        <span class="material-symbols-outlined text-primary">verified_user</span>
                        <span class="text-sm font-bold">Cumplimiento 100% garantizado</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- PRICING -->
        <section class="py-24 bg-white dark:bg-slate-900" id="pricing">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-20">
                    <h2 class="text-3xl lg:text-4xl font-extrabold mb-4">Elige tu plan</h2>
                    <p class="text-slate-500 font-medium">
                        Precios profesionales diseñados para PYMES en crecimiento en Colombia.
                    </p>
                </div>

                <!-- Swiper Carousel -->
                <div class="swiper pricing-swiper">
                    <div class="swiper-wrapper">
                        @forelse($planes as $plan)
                            <div class="swiper-slide">
                                <div class="h-full p-8 rounded-3xl {{ $plan->destacado ? 'border-2 border-primary bg-white dark:bg-slate-900 shadow-xl' : 'border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50' }} flex flex-col">
                                    @if($plan->destacado)
                                        <span class="inline-block mb-4 px-4 py-1 text-xs font-bold text-primary bg-primary/10 rounded-full w-fit">
                                            Más popular
                                        </span>
                                    @endif
                                    
                                    <h3 class="font-extrabold text-xl mb-2">{{ $plan->nombre }}</h3>
                                    <p class="text-slate-500 text-sm mb-6">
                                        {{ $plan->descripcion }}
                                    </p>
                                    <p class="text-4xl font-extrabold mb-6">
                                        ${{ number_format($plan->valor) }}<span class="text-base font-medium text-slate-500"> / mes</span>
                                    </p>
                                    <ul class="space-y-3 text-sm mb-8 flex-grow">
                                        @if($plan->features && is_array($plan->features))
                                            @foreach($plan->features as $feature)
                                                <li class="flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-accent text-base">check</span>
                                                    {{ $feature }}
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                    <a href="{{ route('register.create', ['plan_id' => $plan->id]) }}"
                                        class="w-full py-3 rounded-xl {{ $plan->destacado ? 'bg-primary text-white font-bold hover:opacity-90' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-bold hover:bg-slate-100 dark:hover:bg-slate-700' }} text-center transition-all">
                                        {{ $plan->destacado ? 'Comenzar ahora' : 'Elegir plan' }}
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="swiper-slide">
                                <div class="p-8 rounded-3xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex flex-col text-center">
                                    <p class="text-slate-500">No hay planes disponibles</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Swiper Navigation -->
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
        </section>


    </main>

    <!-- FOOTER -->
    <footer class="bg-slate-950 text-slate-400">
        <div class="max-w-7xl mx-auto px-6 py-20 grid grid-cols-1 md:grid-cols-4 gap-12">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="size-9 bg-primary rounded-lg flex items-center justify-center text-white">
                        <span class="material-symbols-outlined">account_balance_wallet</span>
                    </div>
                    <span class="text-xl font-extrabold text-white">Nomitech</span>
                </div>
                <p class="text-sm leading-relaxed">
                    Plataforma de nómina electrónica diseñada para cumplir la normativa colombiana.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-white mb-4">Producto</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#features" class="hover:text-white">Características</a></li>
                    <li><a href="#workflow" class="hover:text-white">Flujo de trabajo</a></li>
                    <li><a href="#pricing" class="hover:text-white">Precios</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-white mb-4">Empresa</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/sobre-nosotros" class="hover:text-white">Sobre nosotros</a></li>
                    <li><a href="/contacto" class="hover:text-white">Contacto</a></li>
                    <li><a href="/soporte" class="hover:text-white">Soporte</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-white mb-4">Legal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/terminos" class="hover:text-white">Términos y condiciones</a></li>
                    <li><a href="/privacidad" class="hover:text-white">Política de privacidad</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-6 py-6 text-sm text-center">
                © 2026 Nomitech. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- Initialize Swiper Carousel -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pricingSwiper = new Swiper('.pricing-swiper', {
                slidesPerView: 1,
                spaceBetween: 30,
                loop: false,
                navigation: {
                    nextEl: '.pricing-swiper .swiper-button-next',
                    prevEl: '.pricing-swiper .swiper-button-prev',
                },
                breakpoints: {
                    640: {
                        slidesPerView: 1,
                        spaceBetween: 20,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 24,
                    },
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 30,
                    },
                },
            });
        });
    </script>

</body>

</html>