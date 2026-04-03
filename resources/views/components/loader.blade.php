<div id="global-loader" class="fixed inset-0 z-[9999] hidden flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <style>
        .loader-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .loader-wrapper {
            position: relative;
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader-logo {
            width: 45px;
            height: auto;
            position: absolute;
            z-index: 2;
        }

        .loader-spinner {
            width: 100px;
            height: 100px;
            animation: loader-spin 1.1s linear infinite;
        }

        .loader-segments line {
            stroke: #1fd1a5;
            stroke-width: 3;
            stroke-linecap: round;
            opacity: 0.15;
            animation: loader-fade 1.1s linear infinite;
        }

        .loader-segments line:nth-child(1) { animation-delay: 0s; }
        .loader-segments line:nth-child(2) { animation-delay: .1s; }
        .loader-segments line:nth-child(3) { animation-delay: .2s; }
        .loader-segments line:nth-child(4) { animation-delay: .3s; }
        .loader-segments line:nth-child(5) { animation-delay: .4s; }
        .loader-segments line:nth-child(6) { animation-delay: .5s; }
        .loader-segments line:nth-child(7) { animation-delay: .6s; }
        .loader-segments line:nth-child(8) { animation-delay: .7s; }
        .loader-segments line:nth-child(9) { animation-delay: .8s; }
        .loader-segments line:nth-child(10) { animation-delay: .9s; }
        .loader-segments line:nth-child(11) { animation-delay: 1s; }
        .loader-segments line:nth-child(12) { animation-delay: 1.1s; }

        @keyframes loader-spin {
            to { transform: rotate(360deg); }
        }

        @keyframes loader-fade {
            0% { opacity: 1; }
            100% { opacity: 0.15; }
        }

        .loader-label {
            color: #1fd1a5;
            font-size: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
    </style>

    <div class="loader-container">
        <div class="loader-wrapper">
            <svg class="loader-spinner" viewBox="0 0 100 100">
                <g class="loader-segments">
                    <line x1="50" y1="15" x2="50" y2="25"/>
                    <line x1="73" y1="21" x2="68" y2="30"/>
                    <line x1="85" y1="50" x2="75" y2="50"/>
                    <line x1="73" y1="79" x2="68" y2="70"/>
                    <line x1="50" y1="85" x2="50" y2="75"/>
                    <line x1="27" y1="79" x2="32" y2="70"/>
                    <line x1="15" y1="50" x2="25" y2="50"/>
                    <line x1="27" y1="21" x2="32" y2="30"/>
                    <line x1="63" y1="18" x2="60" y2="28"/>
                    <line x1="82" y1="37" x2="72" y2="40"/>
                    <line x1="37" y1="82" x2="40" y2="72"/>
                    <line x1="18" y1="63" x2="28" y2="60"/>
                </g>
            </svg>
            <img src="{{ asset('images/logo_nomitech.svg') }}" class="loader-logo" alt="Nomitech">
        </div>
        <div id="loader-text" class="loader-label">Cargando...</div>
    </div>
</div>

<script>
    window.NomitechLoader = {
        showTimeout: null,
        hideTimeout: null,
        overlay: null,
        delay: 100, // Default delay for generic show()

        show(text = 'Cargando...', customDelay = null) {
            // Cancelar cualquier proceso de ocultación o mostrado previo
            this.clearTimers();
            
            if (!this.overlay) {
                this.overlay = document.getElementById('global-loader');
            }
            
            const loaderText = document.getElementById('loader-text');
            const effectiveDelay = customDelay !== null ? customDelay : this.delay;

            const startShowing = () => {
                if (this.overlay) {
                    if (loaderText) loaderText.textContent = text;
                    this.overlay.classList.remove('hidden');
                    this.overlay.classList.add('flex');
                    // Pequeño delay para permitir que el navegador registre el cambio de hidden a flex antes de la opacidad
                    setTimeout(() => {
                        this.overlay.classList.remove('opacity-0');
                        this.overlay.classList.add('opacity-100');
                    }, 10);
                }
            };

            if (effectiveDelay > 0) {
                this.showTimeout = setTimeout(startShowing, effectiveDelay);
            } else {
                startShowing();
            }
        },

        hide() {
            this.clearTimers();

            if (!this.overlay) {
                this.overlay = document.getElementById('global-loader');
            }

            if (this.overlay) {
                this.overlay.classList.remove('opacity-100');
                this.overlay.classList.add('opacity-0');
                
                this.hideTimeout = setTimeout(() => {
                    this.overlay.classList.add('hidden');
                    this.overlay.classList.remove('flex');
                }, 300);
            }
        },

        clearTimers() {
            if (this.showTimeout) {
                clearTimeout(this.showTimeout);
                this.showTimeout = null;
            }
            if (this.hideTimeout) {
                clearTimeout(this.hideTimeout);
                this.hideTimeout = null;
            }
        }
    };

    // Auto-hook into forms that have data-loader attribute
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form.hasAttribute('data-loader')) {
            const text = form.getAttribute('data-loader-text') || 'Procesando...';
            // Para formularios, mostramos el loader de inmediato para confirmar la acción al usuario
            window.NomitechLoader.show(text, 0);
        }
    });

    // Handle bank export processing manually if needed, but let's ensure global access
</script>
