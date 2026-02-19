@extends('layouts.superadmin')

@section('content')
<div class="px-8 pt-3 pb-6">

    <!-- ALERTAS DE ÉXITO/ERROR -->
    @if ($message = Session::get('success'))
    <div id="alertExito" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg shadow-sm flex items-center gap-3 animate-pulse">
        <i class="bi bi-check-circle text-green-600 text-xl"></i>
        <div>
            <p class="text-green-800 font-semibold">¡Éxito!</p>
            <p class="text-green-700 text-sm">{{ $message }}</p>
        </div>
        <button onclick="cerrarAlerta('alertExito')" class="ml-auto text-green-600 hover:text-green-800">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    @if ($message = Session::get('error'))
    <div id="alertError" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg shadow-sm flex items-center gap-3">
        <i class="bi bi-exclamation-circle text-red-600 text-xl"></i>
        <div>
            <p class="text-red-800 font-semibold">¡Error!</p>
            <p class="text-red-700 text-sm">{{ $message }}</p>
        </div>
        <button onclick="cerrarAlerta('alertError')" class="ml-auto text-red-600 hover:text-red-800">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    <h1 class="text-3xl font-bold text-gray-900">
        Módulo de Actualizaciones
    </h1>
    <p class="text-gray-500 mt-1 mb-3">
        Gestiona la configuración básica del sistema de nómina y facturación.
    </p>

    <!-- GRID DE CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">

        @foreach($modulos as $modulo)
            <div class="relative bg-white rounded-lg shadow-sm border p-4 flex flex-col justify-between">

                <!-- Botón Ver (esquina superior derecha) -->
                <button 
                    onclick="openModal('{{ $modulo['titulo'] }}')" 
                    class="absolute top-2 right-2 bg-gray-100 hover:bg-gray-200 text-sm rounded-full px-2 py-1 shadow"
                    title="Ver detalles"
                >
                    <i class="bi bi-eye text-sm"></i>
                </button>

                <div>
                    <div class="w-10 h-10 flex items-center justify-center rounded-md bg-blue-100 text-blue-900 mb-3">
                        <i class="bi {{ $modulo['icono'] }} text-lg"></i>
                    </div>

                    <h3 class="font-semibold text-base text-gray-900 leading-tight">
                        {{ $modulo['titulo'] }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1 leading-snug">
                        {{ $modulo['desc'] }}
                    </p>
                </div>

                <div class="flex gap-2 mt-4">
                  <button 
                     type="button"
                     onclick="openAddModal('{{ strtolower($modulo['titulo']) }}')"
                    class="bg-blue-900 text-white px-3 py-1.5 rounded-md text-xs flex items-center gap-1 hover:bg-blue-800"
                    >
                    <i class="bi bi-plus-lg text-xs"></i> Agregar
                    </button>
                    <button 
                        type="button"
                        onclick="abrirEdicion('{{ strtolower($modulo['titulo']) }}')"
                        class="border px-3 py-1.5 rounded-md text-xs flex items-center gap-1 hover:bg-gray-50 transition"
                    >
                        <i class="bi bi-pencil text-xs"></i> Editar
                    </button>
                </div>

            </div>
        @endforeach

    </div>

   <div class="mt-6"> 
    {{ $modulos->links() }}
 </div>
    

</div>

<!-- MODAL -->
<div id="modalVer" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl w-full max-w-2xl p-6 relative">

        <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-black">
            ✖
        </button>

        <h2 id="modalTitulo" class="text-xl font-bold mb-4">Detalle</h2>

        <div id="modalContenido" class="max-h-96 overflow-y-auto text-sm text-gray-700">
            <!-- Aquí se cargan los datos -->
        </div>

    </div>
</div>

<script>
    // Mapeo de nombre de módulo a tipo de configuración
    const moduloATipo = {
        'ciudades': 'ciudades',
        'tipos de documento': 'tipos_de_documento',
        'bancos': 'bancos',
        'cargos': 'cargos',
        'tipos de contrato': 'tipos_de_contrato',
        'eps': 'eps',
        'arl': 'arl',
        'empresas': 'empresas',
        'departamentos': 'departamentos',
        'estados': 'estados',
        'formas de pago': 'formas_de_pago',
        'métodos de pago': 'metodos_de_pago',
        'países': 'paises',
        'roles': 'roles',
        'tipos hora recargo': 'tipos_hora_recargo'
    };

    
    const configuracionesCliente = {
        ciudades: {
            tipo: 'ciudades',
            campos: [
                { clave: 'codigo', label: 'Código', tipo: 'text', icono: 'bi-hash', requerido: true },
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-pin-map', requerido: true }
            ],
            campoId: 'id_ciudad',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-orange-400 to-orange-600', boton: 'from-orange-500 to-orange-600' }
        },
        tipos_de_documento: {
            tipo: 'tipos_de_documento',
            campos: [
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-person-badge', requerido: true }
            ],
            campoId: 'id_tipo_doc',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-green-400 to-green-600', boton: 'from-green-500 to-green-600' }
        },
        bancos: {
            tipo: 'bancos',
            campos: [
                { clave: 'codigo', label: 'Código', tipo: 'text', icono: 'bi-hash', requerido: true },
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-bank', requerido: true }
            ],
            campoId: 'id_banco',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-purple-400 to-purple-600', boton: 'from-purple-500 to-purple-600' }
        },
        cargos: {
            tipo: 'cargos',
            campos: [
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-briefcase', requerido: true },
                { clave: 'descripcion', label: 'Descripción', tipo: 'textarea', icono: 'bi-file-text' }
            ],
            campoId: 'id_rol',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-indigo-400 to-indigo-600', boton: 'from-indigo-500 to-indigo-600' }
        },
        tipos_de_contrato: {
            tipo: 'tipos_de_contrato',
            campos: [
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-file-earmark', requerido: true },
                { clave: 'seguridad_social', label: '¿Incluye Seguridad Social?', tipo: 'select', icono: 'bi-check-circle', opciones: [
                    { id: 0, nombre: 'No' },
                    { id: 1, nombre: 'Sí' }
                ]}
            ],
            campoId: 'id_tipo_contrato',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-cyan-400 to-cyan-600', boton: 'from-cyan-500 to-cyan-600' }
        },
        eps: {
            tipo: 'eps',
            campos: [
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-heart-pulse', requerido: true }
            ],
            campoId: 'id_eps',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-red-400 to-red-600', boton: 'from-red-500 to-red-600' }
        },
        arl: {
            tipo: 'arl',
            campos: [
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-shield-check', requerido: true }
            ],
            campoId: 'id_arl',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-yellow-400 to-yellow-600', boton: 'from-yellow-500 to-yellow-600' }
        },
        empresas: {
            tipo: 'empresas',
            campos: [
                { clave: 'nit', label: 'NIT', tipo: 'text', icono: 'bi-hash', requerido: true },
                { clave: 'razon_social', label: 'Razón Social', tipo: 'text', icono: 'bi-card-text', requerido: true },
                { clave: 'doc_representante', label: 'Doc. Representante', tipo: 'text', icono: 'bi-person-badge' },
                { clave: 'direccion', label: 'Dirección', tipo: 'text', icono: 'bi-map-pin' },
                { clave: 'correo', label: 'Correo', tipo: 'email', icono: 'bi-envelope' },
                { clave: 'telefono', label: 'Teléfono', tipo: 'text', icono: 'bi-telephone' }
            ],
            campoId: 'id_empresa',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-blue-400 to-blue-600', boton: 'from-blue-500 to-blue-600' }
        },
        departamentos: {
            tipo: 'departamentos',
            campos: [
                { clave: 'codigo', label: 'Código', tipo: 'text', icono: 'bi-hash', requerido: true },
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-map', requerido: true }
            ],
            campoId: 'id_departamento',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-teal-400 to-teal-600', boton: 'from-teal-500 to-teal-600' }
        },
        estados: {
            tipo: 'estados',
            campos: [
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-info-circle', requerido: true }
            ],
            campoId: 'id_estado',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-lime-400 to-lime-600', boton: 'from-lime-500 to-lime-600' }
        },
        formas_de_pago: {
            tipo: 'formas_de_pago',
            campos: [
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-credit-card', requerido: true }
            ],
            campoId: 'id_forma_pago',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-fuchsia-400 to-fuchsia-600', boton: 'from-fuchsia-500 to-fuchsia-600' }
        },
        metodos_de_pago: {
            tipo: 'metodos_de_pago',
            campos: [
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-wallet2', requerido: true }
            ],
            campoId: 'id_metodo_pago',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-rose-400 to-rose-600', boton: 'from-rose-500 to-rose-600' }
        },
        paises: {
            tipo: 'paises',
            campos: [
                { clave: 'codigo', label: 'Código', tipo: 'text', icono: 'bi-hash', requerido: true },
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-globe', requerido: true }
            ],
            campoId: 'id_pais',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-sky-400 to-sky-600', boton: 'from-sky-500 to-sky-600' }
        },
        roles: {
            tipo: 'roles',
            campos: [
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-shield-lock', requerido: true },
                { clave: 'descripcion', label: 'Descripción', tipo: 'textarea', icono: 'bi-file-text' }
            ],
            campoId: 'id_rol',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-violet-400 to-violet-600', boton: 'from-violet-500 to-violet-600' }
        },
        tipos_hora_recargo: {
            tipo: 'tipos_hora_recargo',
            campos: [
                { clave: 'nombre', label: 'Nombre', tipo: 'text', icono: 'bi-clock', requerido: true }
            ],
            campoId: 'id_tipo_hora_recargo',
            ruta: '/superadmin/actualizar/:id',
            colores: { icono: 'from-amber-400 to-amber-600', boton: 'from-amber-500 to-amber-600' }
        }
    };

    function openModal(modulo) {
        document.getElementById('modalVer').classList.remove('hidden');
        document.getElementById('modalVer').classList.add('flex');
        document.getElementById('modalTitulo').innerText = 'Datos de ' + modulo;

        // Convertir nombre de módulo a tipo
        const tipo = moduloATipo[modulo.toLowerCase()] || modulo.toLowerCase().replace(/\s+/g, '_').replace(/[óá]/g, 'o');

        fetch(`/superadmin/actualizaciones/${tipo}/datos`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('modalContenido').innerHTML = html;
            })
            .catch(err => console.log(err));
    }

    function abrirEdicion(modulo) {
        openModal(modulo);
    }

    function closeModal() {
        document.getElementById('modalVer').classList.add('hidden');
        document.getElementById('modalVer').classList.remove('flex');
    }
    
    function openAddModal(modulo) {
        const tipo = moduloATipo[modulo.toLowerCase()] || modulo.toLowerCase().replace(/\s+/g, '_').replace(/[óá]/g, 'o');
        const configuracion = configuracionesCliente[tipo];
        
        if (configuracion) {
            abrirModalAgregar(tipo, configuracion);
        }
    }

    function closeAddModal() {
        cerrarModalAgregar();
    }

    function cerrarAlerta(idAlerta) {
        const alerta = document.getElementById(idAlerta);
        if (alerta) {
            alerta.style.transition = 'opacity 0.3s ease-out';
            alerta.style.opacity = '0';
            setTimeout(() => {
                alerta.remove();
            }, 300);
        }
    }

    // Desaparecer alertas automáticamente después de 3 segundos
    document.addEventListener('DOMContentLoaded', function() {
        const alertExito = document.getElementById('alertExito');
        const alertError = document.getElementById('alertError');
        
        if (alertExito) {
            setTimeout(() => cerrarAlerta('alertExito'), 3000);
        }
        if (alertError) {
            setTimeout(() => cerrarAlerta('alertError'), 3000);
        }
    });

</script>

@include('superadmin.actualizaciones.partials.agregar-generico')
@include('superadmin.actualizaciones.partials.modal-edicion-generico')

@endsection

