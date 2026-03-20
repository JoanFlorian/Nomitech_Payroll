@extends('layouts.app')

@section('title', 'Gestión de Roles y Permisos')
@section('page-title', 'ROLES Y ACCESOS')

@section('content')
<div x-data="roleManager()" class="max-w-[1600px] mx-auto space-y-8 animate-fadeIn">
    
    <!-- HEADER SECTION -->
    <div class="relative overflow-hidden rounded-2xl bg-white p-6 border border-gray-100 shadow-xl">
        <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-blue-50/50 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-emerald-50/50 blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-100">
                        <i class="fas fa-shield-alt text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Control de Accesos</h1>
                        <p class="text-slate-500 text-sm font-medium">Gestiona roles globales y excepciones de permisos por usuario.</p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3 bg-slate-50 p-1.5 rounded-2xl border border-slate-100">
                <div class="px-3 py-1.5 bg-white rounded-xl shadow-sm border border-slate-100">
                    <span class="text-[9px] uppercase tracking-wider text-slate-400 font-bold block">Empresa</span>
                    <span class="text-xs font-bold text-slate-700">{{ auth()->user()->empresa->first()?->razon_social ?? 'SaaS' }}</span>
                </div>
                <div class="px-3 py-1.5 bg-white rounded-xl shadow-sm border border-slate-100">
                    <span class="text-[9px] uppercase tracking-wider text-slate-400 font-bold block">Frecuencia</span>
                    <span class="text-xs font-bold text-slate-700">{{ auth()->user()->empresa->first()?->licencia?->plan?->nombre ?? 'SaaS' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN INTERFACE -->
    <div class="flex flex-col lg:flex-row gap-8 min-h-[70vh]">
        
        <!-- ASIDE: ROLES -->
        <aside class="w-full lg:w-72 shrink-0">
            <div class="sticky top-24 space-y-4">
                <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 px-4">Roles de Sistema</h3>
                <div class="grid grid-cols-1 gap-1.5">
                    @foreach($roles as $rol)
                    <button 
                        @click="selectRole({{ $rol->id_rol }}, '{{ $rol->nombre }}')"
                        :class="selectedRoleId == {{ $rol->id_rol }} ? 'bg-blue-600 text-white shadow-lg border-blue-500 translate-x-1' : 'bg-white text-slate-600 hover:bg-blue-50 hover:border-blue-100 border-gray-100'"
                        class="group flex items-center gap-3 p-3 rounded-2xl border transition-all duration-300 transform text-left"
                    >
                        <div :class="selectedRoleId == {{ $rol->id_rol }} ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600 group-hover:bg-white'"
                             class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors">
                            <i class="fas {{ match($rol->id_rol) {
                                1 => 'fa-user-tie',
                                2 => 'fa-user-cog',
                                3 => 'fa-user',
                                4 => 'fa-crown',
                                6 => 'fa-calculator',
                                default => 'fa-id-badge'
                            } }} text-xs"></i>
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <span class="block font-bold text-xs truncate uppercase tracking-tight">{{ $rol->nombre }}</span>
                            <span class="block text-[9px] opacity-70 font-semibold tracking-wide">
                                {{ $rol->usuarios_count . ' Empleados' }}
                            </span>
                        </div>
                    </button>
                    @endforeach
                </div>
                
                <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100/50 mt-6 md:block hidden">
                    <p class="text-[10px] text-blue-700 leading-relaxed font-semibold">
                        <i class="fas fa-info-circle mr-1"></i>
                        Cambios afectan a todos los usuarios con el mismo rol.
                    </p>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 min-w-0 space-y-8">
            
            <!-- INITIAL STATE -->
            <div x-show="!selectedRoleId" class="h-full flex flex-col items-center justify-center p-12 bg-white rounded-[3rem] border border-gray-100 border-dashed animate-pulse">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-mouse-pointer text-slate-300 text-3xl"></i>
                </div>
                <h2 class="text-xl font-black text-slate-800">Selecciona un Rol</h2>
                <p class="text-slate-400 text-sm max-w-xs text-center mt-2">Elige un rol a la izquierda para ver y gestionar los empleados asociados.</p>
            </div>

            <!-- EMPLOYEES GRID -->
            <div x-show="selectedRoleId && !selectedUser" x-cloak class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-xl font-bold text-slate-800">
                        Empleados: <span class="text-blue-600" x-text="selectedRoleName"></span>
                    </h2>
                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-bold" x-text="employees.length + ' Usuarios'"></span>
                </div>

                <div x-show="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="i in 6">
                        <div class="h-32 bg-slate-50 rounded-2xl animate-pulse border border-slate-100"></div>
                    </template>
                </div>

                <div x-show="!loading && employees.length == 0" class="p-12 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                    <i class="fas fa-search text-slate-100 text-4xl mb-3"></i>
                    <p class="text-slate-400 text-sm font-bold">No hay empleados asignados.</p>
                </div>

                <div x-show="!loading && employees.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="emp in employees" :key="emp.doc">
                        <article class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden hover:shadow-lg transition-all duration-300 group">
                            <div class="relative z-10 space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-50 text-blue-600 font-bold text-sm flex items-center justify-center border border-blue-50">
                                        <span x-text="emp.primer_nombre ? emp.primer_nombre[0] : ''"></span>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h3 class="font-bold text-slate-800 text-xs truncate uppercase tracking-tight" x-text="emp.primer_nombre + ' ' + emp.primer_apellido"></h3>
                                        <p class="text-[9px] font-bold text-slate-400 font-mono" x-text="emp.doc"></p>
                                    </div>
                                </div>
                                
                                <button 
                                    @click="selectEmployee(emp)"
                                    :disabled="emp.is_owner || emp.id_rol == 1 || emp.id_rol == 4"
                                    :class="(emp.is_owner || emp.id_rol == 1 || emp.id_rol == 4) ? 'bg-slate-100 text-slate-300 cursor-not-allowed' : 'bg-slate-800 text-white hover:bg-blue-600 shadow-sm'"
                                    class="w-full py-2 font-bold text-[10px] rounded-xl transition-all transform active:scale-95 flex items-center justify-center gap-2 uppercase tracking-wider"
                                >
                                    <template x-if="emp.is_owner || emp.id_rol == 1 || emp.id_rol == 4">
                                        <span><i class="fas fa-lock mr-2"></i>Bloqueado</span>
                                    </template>
                                    <template x-if="!(emp.is_owner || emp.id_rol == 1 || emp.id_rol == 4)">
                                        <span><i class="fas fa-key mr-2"></i>Permisos</span>
                                    </template>
                                </button>
                            </div>
                        </article>
                    </template>
                </div>
            </div>

            <!-- PERMISSION MANAGEMENT VIEW -->
            <div x-show="selectedUser" x-cloak class="space-y-8 animate-fadeIn">
                <!-- User Back Bar -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <button @click="backToList()" class="group flex items-center gap-3 text-slate-500 hover:text-blue-600 font-black text-sm transition-all focus:outline-none">
                        <div class="w-10 h-10 rounded-full bg-white border border-gray-100 shadow-sm flex items-center justify-center group-hover:border-blue-200 group-hover:shadow-md">
                            <i class="fas fa-arrow-left"></i>
                        </div>
                        Volver al listado
                    </button>
                    
                    <div class="flex items-center gap-4 bg-white px-6 py-3 rounded-full border border-gray-100 shadow-sm">
                        <div class="flex items-center gap-3 border-r border-slate-100 pr-4">
                            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-[10px] uppercase font-black" x-text="selectedUser?.primer_nombre ? selectedUser.primer_nombre[0] : ''"></div>
                            <div>
                                <span class="block text-xs font-black text-slate-900 leading-tight" x-text="selectedUser?.primer_nombre + ' ' + selectedUser?.primer_apellido"></span>
                                <span class="block text-[10px] text-slate-400 font-mono tracking-tighter" x-text="selectedUser?.doc"></span>
                            </div>
                        </div>
                        <div class="pl-2">
                             <div class="flex items-center gap-2">
                                <span class="text-[9px] uppercase font-black text-slate-400 tracking-widest">Rol:</span>
                                <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase" x-text="selectedRoleName"></span>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Grid -->
                <div x-show="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 animate-fadeIn">
                    <template x-for="i in 6">
                        <div class="h-48 bg-white/50 rounded-[2.5rem] border border-gray-100 animate-pulse flex flex-col p-6 gap-4">
                            <div class="flex justify-between items-start">
                                <div class="w-14 h-14 bg-slate-100 rounded-2xl"></div>
                                <div class="w-12 h-6 bg-slate-100 rounded-full"></div>
                            </div>
                            <div class="space-y-2">
                                <div class="w-2/3 h-5 bg-slate-100 rounded-lg"></div>
                                <div class="w-1/2 h-4 bg-slate-100 rounded-lg"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && permissionGroups.length == 0" class="p-12 text-center bg-white rounded-3xl border border-gray-100 shadow-sm animate-fadeIn">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-folder-open text-slate-100 text-2xl"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 uppercase tracking-tight">No hay permisos</h3>
                    <p class="text-slate-400 text-xs mt-2 font-medium">No se encontraron módulos configurados.</p>
                </div>

                <!-- Module Cards Selection -->
                <div x-show="!loading && !activeModule && permissionGroups.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 animate-fadeIn">
                    <template x-for="(mod, index) in permissionGroups" :key="index">
                        <div 
                            @click="activeModule = mod.module"
                            class="group relative bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-blue-100 transition-all duration-300 cursor-pointer overflow-hidden"
                        >
                            <div class="absolute -top-10 -right-10 h-24 w-24 rounded-full bg-blue-50/50 transition-transform group-hover:scale-110 pointer-events-none"></div>
                            <div class="relative z-10 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 transition-transform group-hover:scale-105 shadow-sm">
                                        <i class="bi text-xl" :class="getModuleIcon(mod.module)"></i>
                                    </div>
                                    <div class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded-full text-[9px] font-bold uppercase tracking-tight">
                                        <span x-text="(mod.permissions || []).filter(p => !['edit_payroll', 'reopen_payroll', 'create_payroll', 'create_period', 'create_payroll_period', 'delete_employee'].includes(p.name) && p.active).length + '/' + (mod.permissions || []).filter(p => !['edit_payroll', 'reopen_payroll', 'create_payroll', 'create_period', 'create_payroll_period', 'delete_employee'].includes(p.name)).length"></span>
                                    </div>
                                </div>
                                
                                <div class="space-y-1">
                                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-tight" x-text="getTranslation(mod.module, 'name')"></h4>
                                    <p class="text-[10px] leading-tight text-slate-500 font-medium" x-text="getTranslation(mod.module, 'desc')"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="activeModule" class="bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden relative animate-fadeInUp">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 via-emerald-500 to-blue-600"></div>
                    <div class="p-6 md:p-8 space-y-4">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-50 pb-4">
                            <div class="flex items-center gap-3">
                                <button @click="activeModule = ''" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </button>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shadow-sm">
                                        <i class="bi text-lg" :class="getModuleIcon(activeModule)"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-800 uppercase tracking-tight" x-text="getTranslation(activeModule, 'name')"></h3>
                                        <p class="text-[10px] font-medium text-slate-400" x-text="getTranslation(activeModule, 'desc')"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <template x-for="group in permissionGroups" :key="group.module">
                                <template x-if="activeModule == group.module">
                                    <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <template x-for="perm in group.permissions.filter(p => !['edit_payroll', 'reopen_payroll', 'create_payroll', 'create_period', 'create_payroll_period', 'delete_employee'].includes(p.name))" :key="perm.id">
                                            <div class="flex flex-col gap-2">
                                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-50 hover:border-blue-100 hover:bg-blue-50/20 transition-all bg-slate-50/30">
                                                    <div class="flex items-center gap-3">
                                                        <div :class="perm.active ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-200 border border-slate-100'"
                                                             class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                                                            <i class="fas" :class="perm.active ? 'fa-check text-[8px]' : 'fa-lock text-[8px]'"></i>
                                                        </div>
                                                        <div>
                                                            <span class="block font-bold text-slate-700 text-[11px] uppercase tracking-tight" x-text="getPermLabel(perm.name)"></span>
                                                            <p class="text-[9px] text-slate-400 font-medium" x-text="getPermDesc(perm.name)"></p>
                                                            <div class="flex items-center gap-2 mt-0.5">
                                                                <template x-if="perm.inherited">
                                                                    <span class="text-[7px] font-bold px-1.5 py-0.5 bg-blue-50 text-blue-500 rounded-md uppercase">Heredado del Rol</span>
                                                                </template>
                                                                <template x-if="!perm.inherited">
                                                                    <span class="text-[7px] font-bold px-1.5 py-0.5 bg-amber-50 text-amber-500 rounded-md uppercase">Manual</span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <label class="relative inline-flex items-center cursor-pointer scale-90">
                                                        <input type="checkbox" x-model="perm.active" @change="togglePermission(perm)" class="sr-only peer">
                                                        <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
                                                    </label>
                                                </div>
                                                <template x-if="!perm.active && perm.name.startsWith('view_')">
                                                    <div class="bg-red-50 text-red-600 text-[10px] p-2 leading-tight rounded-lg flex items-start gap-2 border border-red-100 animate-fadeIn">
                                                        <i class="fas fa-exclamation-triangle mt-0.5"></i>
                                                        <p>Con este permiso desactivado el usuario no podrá acceder al módulo completo ni utilizar otros permisos relacionados.</p>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function roleManager() {
    return {
        selectedRoleId: null,
        selectedRoleName: '',
        employees: [],
        selectedUser: null,
        permissionGroups: [],
        activeModule: '',
        loading: false,
        translations: {
            modules: {
                empleados: { name: 'Empleados', desc: 'Gestión de personal y contratos.', icon: 'bi-people' },
                nomina: { name: 'Nómina', desc: 'Procesamiento de cálculos y pagos de salarios.', icon: 'bi-receipt' },
                novedades: { name: 'Novedades', desc: 'Configuración de novedades para el cálculo de salario y pila.', icon: 'bi-journal-text' },
                reportes: { name: 'Reportes', desc: 'Informes y exportaciones de datos.', icon: 'bi-bar-chart' },
                permisos: { name: 'Roles y Permisos', desc: 'Control de accesos y seguridad.', icon: 'bi-shield-check' },
                periodos: { name: 'Periodos de Liquidación', desc: 'Ciclos de liquidación y cierres.', icon: 'bi-calendar3' },
                provisiones: { name: 'Provisiones', desc: 'Cálculo de prestaciones sociales.', icon: 'bi-box-seam' },
                pila: { name: 'PILA', desc: 'Seguridad social y parafiscales.', icon: 'bi-file-earmark-text' },
                catalogos: { name: 'Catálogos', desc: 'Tablas maestras y configuración.', icon: 'bi-collection' },
                'nomina electronica': { name: 'Nómina Electrónica', desc: 'Soporte de pago y envíos a la DIAN.', icon: 'bi-send-check' }
            },
            perms: {
                view: { name: 'Visualizar', desc: 'Permite consultar la información.' },
                create: { name: 'Registrar', desc: 'Permite crear nuevos registros.' },
                edit: { name: 'Modificar', desc: 'Permite editar datos existentes.' },
                delete: { name: 'Eliminar', desc: 'Permite borrar registros del sistema.' },
                export: { name: 'Exportar', desc: 'Permite descargar reportes.' },
                calculate: { name: 'Calcular', desc: 'Realiza cálculos automáticos.' },
                close: { name: 'Cerrar', desc: 'Finaliza y bloquea el registro.' },
                manage: { name: 'Gestionar', desc: 'Administración avanzada del módulo.' }
            }
        },

        getTranslation(module, field) {
            if (!module) return '';
            const mod = module.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "");
            if (this.translations.modules[mod]) {
                return this.translations.modules[mod][field];
            }
            return field === 'name' ? module : '';
        },

        getModuleIcon(module) {
            if (!module) return 'bi-folder';
            const mod = module.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "");
            return this.translations.modules[mod]?.icon || 'bi-folder';
        },

        getPermLabel(key) {
            const labels = {
                'view_payroll': 'Ver Nómina',
                'calculate_payroll': 'Calcular y Editar Nómina',
                'export_payroll': 'Exportar Nómina',
                'view_employees': 'Consultar Empleados',
                'create_employee': 'Registrar Empleados',
                'edit_employee': 'Modificar Contratos/Sueldos',
                'renew_contract': 'Renovar Contratos',
                'export_employees': 'Exportar Empleados',
                'view_novedades': 'Ver Novedades',
                'create_novedad': 'Cargar Extras/Novedades',
                'edit_novedad': 'Ajustar Novedades',
                'delete_novedad': 'Eliminar Novedades',
                'view_provisions': 'Ver Provisiones',
                'manage_provisions': 'Gestionar Prestaciones',
                'view_periods': 'Ver Periodos',
                'create_period': 'Abrir Periodos',
                'close_period': 'Cerrar y Bloquear Periodo',
                'export_period': 'Exportar Acumulados',
                'view_reports': 'Ver Reportes',
                'export_reports': 'Exportar Estadísticas',
                'manage_catalogos': 'Gestionar Catálogos',
                'view_pila': 'Ver Seguridad Social',
                'export_pila': 'Exportar Archivos PILA',
                'view_electronic_payroll': 'Ver Nómina Electrónica',
                'transmit_electronic_payroll': 'Trasmitir a DIAN'
            };
            return labels[key] || key.replace(/_/g, ' ').toUpperCase();
        },

        getPermDesc(key) {
            const descs = {
                'view_payroll': 'Ver calculo de salario de cada empleado en periodo de liquidacion activo.',
                'calculate_payroll': 'Calcular y editar nominas de manera individual o masivamente para los empleados',
                'export_payroll': 'Descargar cálculos de nómina en formatos PDF y Excel para soporte.',
                'view_employees': 'Ver lista de empleados con su informacion basica',
                'create_employee': 'Configurar nuevos trabajadores con sus términos legales y de pago.',
                'edit_employee': 'Actualizar condiciones de contrato y ajustes al sueldo base mes a mes.',
                'renew_contract': 'Extender vigencia de contratos y actualizar términos salariales vencidos.',
                'export_employees': 'Descargar bases de datos de empleados con sus indicadores salariales.',
                'view_novedades': 'Monitorear horas extras, recargos y deducciones aplicadas al pago actual.',
                'create_novedad': 'Cargar horas extras, comisiones y otros conceptos que afectan el neto a pagar.',
                'edit_novedad': 'Corregir valores o cantidades de novedades registradas en el periodo activo.',
                'delete_novedad': 'Retirar cargos o abonos adicionales registrados por error en el salario.',
                'view_provisions': 'Consultar acumulados de Prima, Cesantías e Intereses de ley por pagar.',
                'manage_provisions': 'Tramitar el pago efectivo de prestaciones sociales y liquidaciones de retiro.',
                'view_periods': 'Consultar el histórico de ciclos y estados de liquidación anteriores.',
                'create_period': 'Definir nuevas fechas de corte y periodos para procesamiento de pagos.',
                'close_period': 'Pagar la nomina de todos los empleados asociadas al periodo de liquidacion activo.',
                'export_period': 'Generar el reporte de acumulados totales y centros de costo del periodo.',
                'view_reports': 'Acceso a tableros de control y resúmenes estadísticos de costos laborales.',
                'export_reports': 'Descargar informes gerenciales sobre la operación de nómina y tesorería.',
                'manage_catalogos': 'Administrar tablas maestras como Bancos, EPS y Fondos de Pensiones.',
                'view_pila': 'Consultar planillas de seguridad social y aportes parafiscales generados.',
                'export_pila': 'Descargar archivos planos para el pago en operadores de información.',
                'view_electronic_payroll': 'Ver historial de documentos enviados a la entidad tributaria.',
                'transmit_electronic_payroll': 'Generar y enviar el soporte de pago de nómina electrónica a la DIAN.'
            };
            return descs[key] || 'Permite realizar acciones específicas dentro de este módulo.';
        },

        async selectRole(roleId, roleName) {
            this.selectedRoleId = roleId;
            this.selectedRoleName = roleName;
            this.selectedUser = null;
            this.loading = true;
            this.employees = [];
            
            try {
                const response = await fetch(`/admin/roles/employees?role_id=${roleId}`);
                if (!response.ok) throw new Error('Error al cargar empleados');
                this.employees = await response.json();
            } catch (error) {
                console.error(error);
                Toast.fire({ icon: 'error', title: 'Error al cargar empleados' });
            } finally {
                this.loading = false;
            }
        },

        async selectEmployee(user) {
            this.selectedUser = user;
            this.loading = true;
            this.permissionGroups = [];
            
            try {
                this.activeModule = ''; // Asegurar que se vean las cards
                const response = await fetch(`{{ route('admin.roles.get-employee-permissions') }}?id_usuario=${user.id}`);
                if (!response.ok) throw new Error('Error al cargar permisos');
                const data = await response.json();
                this.permissionGroups = Array.isArray(data) ? data : [];
            } catch (error) {
                console.error(error);
                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'Error al cargar los permisos del usuario', text: error.message });
                } else {
                    alert('Error al cargar los permisos: ' + error.message);
                }
            } finally {
                this.loading = false;
            }
        },

        async togglePermission(perm) {
            try {
                const response = await fetch('{{ route("admin.roles.user-permissions") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        id_usuario: this.selectedUser.id,
                        permission_id: perm.id,
                        active: perm.active ? 1 : 0
                    })
                });

                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Error al actualizar permiso');
                
                // Actualizar estado de herencia si el servidor lo indica
                if (data.inherited !== undefined) {
                    perm.inherited = data.inherited;
                }
                
                Swal.fire({ icon: 'success', title: data.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            } catch (error) {
                console.error(error);
                perm.active = !perm.active; // Revertir frontend en caso de error
                Swal.fire({ icon: 'error', title: error.message || 'Error al actualizar permiso', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
            }
        },

        backToList() {
            this.selectedUser = null;
            this.permissionGroups = [];
            this.activeModule = '';
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }

.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeInUp {
    animation: fadeInUp 0.5s ease-out forwards;
}
</style>
@endsection
