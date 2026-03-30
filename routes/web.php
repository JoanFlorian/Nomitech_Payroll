
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroUsuarios;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\SuperAdmin\EmpresaController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\facturacioncontroller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\CodeVerificationController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SuperAdmin\ActualizacionesController;
use App\Http\Controllers\Admin\CatalogosEmpresaController;

use App\Http\Controllers\NovedadController;
use App\Http\Controllers\NovedadCalculoController;
use App\Http\Controllers\PilaController;
use App\Http\Controllers\NotaAjusteController;

use App\Http\Controllers\ReportesController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\Auth\CambiarPasswordController;

use Illuminate\Http\Request;

Route::get('/', [PricingController::class, 'index'])->name('index');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Login POST
Route::post('/login', [LoginController::class, 'store'])->name('login.perform');

// Auth Routes
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'create'])->name('register.create');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'store'])->name('register');

// Password Reset Routes
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('password/verify', [CodeVerificationController::class, 'showVerifyForm'])->name('password.verify.form');
Route::post('password/verify', [CodeVerificationController::class, 'verify'])->name('password.verify');

Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('/api/cities/search', [App\Http\Controllers\Auth\RegisterController::class, 'searchCities']);
Route::get('/api/city-details/{id}', [App\Http\Controllers\Auth\RegisterController::class, 'getCityDetails']);
Route::get('/api/cities/{department}', [App\Http\Controllers\Auth\RegisterController::class, 'getCities']);

// Stripe Webhook
Route::post('/stripe/webhook', [App\Http\Controllers\StripeWebhookController::class, 'handleWebhook']);

// License Status Routes (Protected by auth, but handled by middleware redirection)
Route::middleware(['auth', 'prevent_back_history'])->group(function () {
    Route::get('/checkout/{pago}', [App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{pago}/session', [App\Http\Controllers\CheckoutController::class, 'createSession'])->name('checkout.session');
    Route::get('/checkout-status/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout-status/cancel', [App\Http\Controllers\CheckoutController::class, 'cancel'])->name('checkout.cancel');

    // Polling Endpoint
    Route::get('/api/payment/status/{sessionId}', [App\Http\Controllers\CheckoutController::class, 'checkStatus'])->name('payment.status');

    Route::get('/licencia/pending', [App\Http\Controllers\LicenseRenewalController::class, 'showPending'])->name('licencia.pending');
    Route::post('/licencia/pending', [App\Http\Controllers\LicenseRenewalController::class, 'processPending'])->name('licencia.pending.post');

    Route::get('/licencia/required', function () {
        return redirect('/#pricing');
    })->name('licencia.required');

    Route::get('/licencia/expired', [App\Http\Controllers\LicenseRenewalController::class, 'showExpired'])->name('licencia.expired');
    Route::post('/licencia/renew', [App\Http\Controllers\LicenseRenewalController::class, 'renew'])->name('licencia.renew');

    Route::get('/empresa/select', function () {
        return "Seleccionar Empresa"; // View: auth.empresa-select
    })->name('empresa.select');
});

// Protected App Routes (Auth + Active License + Contractual Access + First Period Check)
Route::middleware(['auth', 'ensure_active_license', 'contractual_access', 'prevent_back_history', 'must_change_password', 'check_first_period'])->group(function () {
    // Empleados
    Route::get('/empleados', [App\Http\Controllers\EmployeesController::class, 'index'])->name('empleados.index');
    Route::get('/empleados/{doc}', [App\Http\Controllers\EmployeesController::class, 'show'])->name('employees.show')->middleware('permission:view_employees');
    Route::get('/employees/export', [App\Http\Controllers\EmployeesController::class, 'export'])->name('employees.export')->middleware('permission:export_employees');
    Route::get('/employees/export/excel', [App\Http\Controllers\EmployeesController::class, 'exportarEmpleadosExcel'])->name('employees.export.excel')->middleware('permission:export_employees');
    Route::get('/employees/export/pdf', [App\Http\Controllers\EmployeesController::class, 'exportarEmpleadosPdf'])->name('employees.export.pdf')->middleware('permission:export_employees');
    Route::get('/employees/{doc}/edit', [RegistroUsuarios::class, 'editEmployee'])->name('employees.edit')->middleware('permission:edit_employee');
    Route::post('/employees/{doc}/update', [RegistroUsuarios::class, 'updateEmployee'])->name('employees.update')->middleware('permission:edit_employee');

    /* Wizard registro empleado */
    Route::post('/employees/step-1', [RegistroUsuarios::class, 'storeStep1'])->name('employees.step1')->middleware('permission:create_employee');
    Route::post('/employees/step-2', [RegistroUsuarios::class, 'storeStep2'])->name('employees.step2')->middleware('permission:create_employee');
    Route::post('/employees/final', [RegistroUsuarios::class, 'storeFinal'])->name('employees.final')->middleware('permission:create_employee');
    Route::post('/employees/clear-session', [RegistroUsuarios::class, 'clearWizardSession'])->name('employees.clear-session')->middleware('permission:create_employee');

    // Rutas de Renovación y Datos de Contrato
    Route::get('/api/employees/{doc}/contract-data', [App\Http\Controllers\EmployeesController::class, 'getContractData'])->middleware('permission:view_employees');
    Route::post('/employees/{doc}/renew', [RegistroUsuarios::class, 'renewContract'])->name('employees.renew')->middleware('permission:renew_contract');

    // Nómina Routes
    Route::get('/nomina', [NominaController::class, 'index'])->name('nomina.index')->middleware('permission:view_payroll');
    Route::get('/nomina/step-1', [NominaController::class, 'step1'])->name('nomina.step1')->middleware('permission:calculate_payroll');
    Route::post('/nomina/step-1', [NominaController::class, 'postStep1'])->name('nomina.step1.post')->middleware('permission:calculate_payroll');
    Route::get('/nomina/{idSalario}/editar', [NominaController::class, 'edit'])->name('nomina.edit')->middleware('permission:calculate_payroll');
    Route::get('/nomina/step-2', [NominaController::class, 'step2'])->name('nomina.step2')->middleware('permission:calculate_payroll');
    Route::post('/nomina/step-2', [NominaController::class, 'postStep2'])->name('nomina.step2.post')->middleware('permission:calculate_payroll');
    Route::get('/nomina/step-2/ingresos', [NominaController::class, 'step2Ingresos'])->name('nomina.step2.ingresos')->middleware('permission:calculate_payroll');
    Route::post('/nomina/step-2/ingresos', [NominaController::class, 'postStep2Ingresos'])->name('nomina.step2.ingresos.post')->middleware('permission:calculate_payroll');
    Route::get('/nomina/step-3', [NominaController::class, 'step3'])->name('nomina.step3')->middleware('permission:calculate_payroll');
    Route::get('/nomina/export/excel', [NominaController::class, 'exportarNominaExcel'])->name('nomina.export.excel')->middleware('permission:export_payroll');
    Route::get('/nomina/export/pdf', [NominaController::class, 'exportarNominaPdf'])->name('nomina.export.pdf')->middleware('permission:export_payroll');
    Route::post('/nomina/realizar', [NominaController::class, 'realizarNominaMasiva'])->name('nomina.realizar')->middleware('permission:calculate_payroll');
    Route::post('/nomina/store', [NominaController::class, 'store'])->name('nomina.store')->middleware('permission:calculate_payroll');
    Route::get('/nomina/buscar-empleado/{doc}', [NominaController::class, 'buscarEmpleado'])->middleware('permission:view_payroll');
    Route::get('/nomina/buscar-empleados', [NominaController::class, 'buscarEmpleados'])->middleware('permission:view_payroll');
    Route::get('/nomina/validar-duplicado/{idContrato}', [NominaController::class, 'checkDuplicate'])->middleware('permission:view_payroll');

    // Novedades y Reportes
    Route::get('/novedades', [NovedadController::class, 'index'])->name('novedades.index')->middleware('permission:view_novedades');

    Route::get('/novedades/historial', [NovedadController::class, 'historialContrato'])->name('novedades.historial')->middleware('permission:view_novedades');
    Route::get('/novedades/historial-novedades', [NovedadController::class, 'historialNovedades'])->name('novedades.historial_novedades')->middleware('permission:view_novedades');
    Route::post('/novedades/calculo/preview', [NovedadCalculoController::class, 'preview'])->name('novedades.calculo.preview')->middleware('permission:create_novedad');
    Route::post('/novedades', [NovedadController::class, 'store'])->name('novedades.store')->middleware('permission:create_novedad');
    Route::put('/novedades/{id_novedad}', [NovedadController::class, 'update'])->name('novedades.update')->middleware('permission:edit_novedad');
    Route::delete('/novedades/{id_novedad}', [NovedadController::class, 'destroy'])->name('novedades.destroy')->middleware('permission:delete_novedad');

    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index')->middleware('permission:view_reports');
    Route::get('/reportes/exportar/pdf', [ReportesController::class, 'exportarPdf'])->name('reportes.export.pdf')->middleware('permission:export_reports');
    Route::get('/reportes/exportar/excel', [ReportesController::class, 'exportarExcel'])->name('reportes.export.excel')->middleware('permission:export_reports');

    // Provisiones
    Route::get('/provisiones', [\App\Http\Controllers\ProvisionesController::class, 'index'])->name('provisiones.index')->middleware('permission:view_provisions');
    Route::get('/provisiones/{doc}/historial', [\App\Http\Controllers\ProvisionesController::class, 'historial'])->name('provisiones.historial')->middleware('permission:view_provisions');
    Route::post('/provisiones/liquidar-individual', [\App\Http\Controllers\ProvisionesController::class, 'liquidarIndividual'])->name('provisiones.liquidar.individual')->middleware('permission:manage_provisions');
    Route::post('/provisiones/liquidar-masivo', [\App\Http\Controllers\ProvisionesController::class, 'liquidarMasivo'])->name('provisiones.liquidar.masivo')->middleware('permission:manage_provisions');
    Route::post('/provisiones/pagar-prestacion', [\App\Http\Controllers\ProvisionesController::class, 'pagarPrestacion'])->name('provisiones.pagar-prestacion')->middleware('permission:manage_provisions');
    Route::post('/provisiones/update-automation', [\App\Http\Controllers\ProvisionesController::class, 'updateAutomation'])->name('provisiones.update-automation')->middleware('permission:manage_provisions');
    Route::post('/provisiones/reset-automation', [\App\Http\Controllers\ProvisionesController::class, 'resetAutomation'])->name('provisiones.reset-automation')->middleware('permission:manage_provisions');
    Route::post('/provisiones/cesantias/retiro-parcial', [\App\Http\Controllers\ProvisionesController::class, 'retiroParcialCesantias'])->name('provisiones.cesantias.retiro-parcial')->middleware('permission:manage_provisions');
    Route::post('/provisiones/cesantias/retiro-empresa', [\App\Http\Controllers\ProvisionesController::class, 'retiroEmpresa'])->name('provisiones.cesantias.retiro-empresa')->middleware('permission:manage_provisions');
    Route::post('/provisiones/cesantias/autorizacion-fondo', [\App\Http\Controllers\ProvisionesController::class, 'autorizacionFondo'])->name('provisiones.cesantias.autorizacion-fondo')->middleware('permission:manage_provisions');
    Route::post('/provisiones/cesantias/consignacion-anual', [\App\Http\Controllers\ProvisionesController::class, 'generarConsignacionAnual'])->name('provisiones.cesantias.consignacion-anual')->middleware('permission:manage_provisions');
    Route::get('/provisiones/cesantias/descargar-consignacion-reciente', [\App\Http\Controllers\ProvisionesController::class, 'descargarConsignacionReciente'])->name('provisiones.cesantias.descargar-consignacion-reciente')->middleware('permission:manage_provisions');
    Route::get('/provisiones/cesantias/certificado/{withdrawal_id}', [\App\Http\Controllers\ProvisionesController::class, 'descargarCertificado'])->name('provisiones.cesantias.certificado')->middleware('permission:view_provisions');
    Route::get('/provisiones/comprobante/{movement_id}', [\App\Http\Controllers\ProvisionesController::class, 'descargarComprobantePrestacion'])->name('provisiones.comprobante')->middleware('permission:view_provisions');

    // Nómina Electrónica
    Route::get('/nomina-electronica', [\App\Http\Controllers\NominaElectronicaController::class, 'index'])->name('nomina-electronica.index')->middleware('permission:view_electronic_payroll');
    Route::get('/nomina-electronica/{id}/detalles', [\App\Http\Controllers\NominaElectronicaController::class, 'getDetalles'])->name('nomina-electronica.detalles')->middleware('permission:view_electronic_payroll');
    Route::get('/nomina-electronica/pdf/{idSalario}', [\App\Http\Controllers\NominaElectronicaController::class, 'descargarPdf'])->name('nomina-electronica.pdf')->middleware('permission:view_electronic_payroll');
    Route::get('/nomina-electronica/{id}/export-preview', [\App\Http\Controllers\NominaElectronicaController::class, 'exportPreview'])->name('nomina-electronica.export-preview')->middleware('permission:export_bank_files');
    Route::post('/nomina-electronica/{id}/exportar', [\App\Http\Controllers\NominaElectronicaController::class, 'exportar'])->name('nomina-electronica.exportar')->middleware('permission:export_bank_files');
    Route::get('/nomina-electronica/exportacion/{id}/descargar', [\App\Http\Controllers\NominaElectronicaController::class, 'downloadExport'])->name('nomina-electronica.exportar.descargar')->middleware('permission:export_bank_files');

    // Gestión de Periodos
    Route::get('/periodos', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'index'])
        ->name('periodos.index')->middleware('permission:view_periods');
    Route::post('/periodos', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'store'])
        ->name('periodos.store')->middleware('permission:create_period');
    Route::post('/periodos/first', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'storeFirstPeriod'])
        ->name('periodos.store-first')->middleware('permission:create_period');
    Route::get('/periodos/{id}/select', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'select'])
        ->name('periodos.select')->middleware('permission:view_periods');
    Route::get('/periodos/{id}/suggest', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'suggestNext'])
        ->name('periodos.suggest')->middleware('permission:view_periods');
    Route::get('/periodos/{id}/export-preview', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'getExportPreview'])
        ->name('periodos.export.preview')->middleware('permission:view_periods');
    Route::post('/periodos/{id}/cerrar', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'close'])
        ->name('periodos.cerrar')->middleware('permission:close_period');
    Route::post('/periodos/{id}/exportar', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'exportar'])
        ->name('periodos.exportar')->middleware('permission:export_period');
    Route::get('/periodos/exportacion/{id}/descargar', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'downloadExport'])
        ->name('periodos.exportar.descargar')->middleware('permission:view_periods');
    Route::post('/periodos/{id}/auto-close-date', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'updateAutoCloseDate'])
        ->name('periodos.update-auto-close')->middleware('permission:close_period');

    // Plantilla PILA
    Route::get('/pila', [PilaController::class, 'index'])->name('pila.index')->middleware('permission:view_pila');
    Route::post('/pila/generar', [PilaController::class, 'generar'])->name('pila.generar')->middleware('permission:export_pila');
    Route::get('/pila/descargar', [PilaController::class, 'descargarPila'])->name('pila.descargar')->middleware('permission:export_pila');
    Route::get('/pila/historial/{id}/descargar', [PilaController::class, 'descargarHistorial'])->name('pila.historial.descargar')->middleware('permission:view_pila');
    
    // Rutas de exportación de PILA (controlador especializado)
    Route::get('/pila/historial/excel/descargar', [\App\Http\Controllers\PilaExportController::class, 'descargarHistorialExcel'])->name('pila.historial.excel')->middleware('permission:export_pila');
    Route::get('/pila/export/registro/{id}', [\App\Http\Controllers\PilaExportController::class, 'descargarRegistroExcel'])->name('pila.export.registro')->middleware('permission:export_pila');
    Route::get('/pila/export/preview', [\App\Http\Controllers\PilaExportController::class, 'previewHistorial'])->name('pila.export.preview')->middleware('permission:view_pila');
});

Route::middleware(['auth', 'ensure_active_license', 'contractual_access', 'admin_empresa', 'permission:manage_catalogos', 'prevent_back_history', 'must_change_password'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware(['permission:manage_catalogos'])->group(function () {
            Route::get('/catalogos', [CatalogosEmpresaController::class, 'index'])->name('catalogos.index');
            Route::get('/catalogos/{catalogo}', [CatalogosEmpresaController::class, 'show'])->name('catalogos.show');
            Route::get('/catalogos/{catalogo}/data', [CatalogosEmpresaController::class, 'data'])->name('catalogos.data');
            Route::post('/catalogos/{catalogo}', [CatalogosEmpresaController::class, 'store'])->name('catalogos.store');
            Route::put('/catalogos/{catalogo}/{id}', [CatalogosEmpresaController::class, 'update'])->name('catalogos.update');
            Route::patch('/catalogos/{catalogo}/{id}/estado', [CatalogosEmpresaController::class, 'toggleEstado'])->name('catalogos.toggle-estado');
        });

        // Gestión de Roles y Permisos (Protected by admin_empresa, but not strictly by manage_catalogos)
        Route::get('/roles', [\App\Http\Controllers\Admin\RoleManagementController::class, 'index'])->name('roles.index');
        Route::get('/roles/employees', [\App\Http\Controllers\Admin\RoleManagementController::class, 'getEmployeesByRole'])->name('roles.get-employees');
        Route::get('/roles/employee-permissions', [\App\Http\Controllers\Admin\RoleManagementController::class, 'getPermissionsByEmployee'])->name('roles.get-employee-permissions');
        Route::post('/roles/permissions', [\App\Http\Controllers\Admin\RoleManagementController::class, 'updatePermission'])->name('roles.update-permission');
        Route::post('/roles/user-permissions', [\App\Http\Controllers\Admin\RoleManagementController::class, 'updateUserPermission'])->name('roles.user-permissions');
        Route::post('/roles/assign-role', [\App\Http\Controllers\Admin\RoleManagementController::class, 'assignRole'])->name('roles.assign-role');

        // Notas de Ajuste (admin)
        Route::get('/notas-ajuste', [NotaAjusteController::class, 'adminIndex'])->name('notas-ajuste.index');
        Route::get('/notas-ajuste/{notaAjuste}', [NotaAjusteController::class, 'show'])->name('notas-ajuste.show');
        Route::put('/notas-ajuste/{notaAjuste}', [NotaAjusteController::class, 'adminUpdate'])->name('notas-ajuste.update');
        Route::patch('/notas-ajuste/{notaAjuste}/ajuste-pago', [NotaAjusteController::class, 'adminApplyPaymentAdjustment'])->name('notas-ajuste.apply-payment-adjustment');
    });

// Superadmin routes protected by auth and role
Route::middleware(['auth', 'is_superadmin', 'prevent_back_history', 'must_change_password'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [facturacioncontroller::class, 'dashboard'])->name('index');
    Route::get('/facturacion', [facturacioncontroller::class, 'facturacion'])->name('facturacion');
    Route::get('/facturacion/exportar/pdf', [facturacioncontroller::class, 'exportarFacturacionPdf'])->name('facturacion.exportar.pdf');
    Route::get('/facturacion/exportar/excel', [facturacioncontroller::class, 'exportarFacturacionExcel'])->name('facturacion.exportar.excel');
    Route::get('/reporte/descargar', [facturacioncontroller::class, 'descargarReporte'])->name('reporte.descargar');
    Route::get('/factura/{pagoId}/pdf', [facturacioncontroller::class, 'descargarFacturaPdf'])->name('factura.pdf');
    Route::get('/factura/{pagoId}', [facturacioncontroller::class, 'getFactura'])->name('factura');

    // Empresas
    Route::get('/empresas', [EmpresaController::class, 'index'])->name('empresas.index');
    Route::get('/empresas/reporte/excel', [EmpresaController::class, 'exportarReporteExcel'])->name('empresas.reporte-excel');
    Route::get('/empresas/{empresa}/validar-correo', [EmpresaController::class, 'validarCorreo'])->name('empresas.validar-correo');
    Route::get('/empresas/{empresa}/certificado-excel', [EmpresaController::class, 'descargarCertificadoExcel'])->name('empresas.certificado-excel');
    Route::get('/empresas/{empresa}', [EmpresaController::class, 'show'])->name('empresas.show');
    Route::put('/empresas/{empresa}', [EmpresaController::class, 'update'])->name('empresas.update');

    // Planes CRUD
    Route::get('/planes', [PlanController::class, 'index'])->name('planes.index');
    Route::get('/planes/create', [PlanController::class, 'create'])->name('planes.create');
    Route::post('/planes', [PlanController::class, 'store'])->name('planes.store');
    Route::get('/planes/{plan}/edit', [PlanController::class, 'edit'])->name('planes.edit');
    Route::put('/planes/{plan}', [PlanController::class, 'update'])->name('planes.update');

    // Actualizaciones y otros
    Route::get('/actualizaciones', [ActualizacionesController::class, 'index'])->name('actualizaciones.principal');
    Route::get('/actualizaciones/{tipo}/datos', [ActualizacionesController::class, 'getDatos'])->name('actualizaciones.datos');
    Route::get('/actualizaciones/{tipo}/exportar-excel', [ActualizacionesController::class, 'exportarExcel'])->name('actualizaciones.exportar');
    Route::post('/ciudades', [ActualizacionesController::class, 'storeCiudad'])->name('ciudades.store');
    Route::post('/{tipo}', [ActualizacionesController::class, 'store'])->name('store');
    Route::put('/actualizar/{id}', [ActualizacionesController::class, 'actualizar'])->name('actualizar');

    // Vistas directas
    Route::get('/empresas-view', function () {
        return view('superadmin.empresas');
    })->name('empresas-view');
    Route::get('/configuracion', function () {
        return view('superadmin.configuracion');
    })->name('configuracion');
});

// Cambiar contraseña obligatorio en primer ingreso
Route::middleware(['auth'])->group(function () {
    Route::get('/cambiar-password', [CambiarPasswordController::class, 'show'])->name('cambiar-password');
    Route::post('/cambiar-password', [CambiarPasswordController::class, 'update'])->name('cambiar-password.update');
});

// Portal del Trabajador (solo autenticación requerida)
Route::middleware(['auth', 'ensure_active_license', 'prevent_back_history', 'must_change_password'])->prefix('trabajador')->name('trabajador.')->group(function () {
    Route::get('/dashboard', [TrabajadorController::class, 'index'])->name('dashboard');
    Route::get('/desprendibles', [TrabajadorController::class, 'desprendibles'])->name('desprendibles');
    Route::get('/desprendible/{id}', [TrabajadorController::class, 'verDesprendible'])->name('desprendible.ver');
    Route::get('/desprendible/{id}/pdf', [TrabajadorController::class, 'descargarDesprendible'])->name('desprendible.pdf');
    Route::get('/notas-ajuste', [NotaAjusteController::class, 'trabajadorIndex'])->name('notas');
    Route::get('/notas-ajuste/crear/{idSalario}', [NotaAjusteController::class, 'createForDesprendible'])->name('notas.create');
    Route::post('/notas-ajuste', [NotaAjusteController::class, 'store'])->name('notas.store');
    Route::delete('/notas-ajuste/{notaAjuste}', [NotaAjusteController::class, 'destroy'])->name('notas.destroy');
    Route::get('/perfil', [TrabajadorController::class, 'perfil'])->name('perfil');
    Route::post('/perfil/foto', [TrabajadorController::class, 'actualizarFotoPerfil'])->name('perfil.foto');
    Route::post('/perfil', [TrabajadorController::class, 'actualizarPerfil'])->name('perfil.actualizar');
});



// Redirección raíz del portal del trabajador
Route::middleware(['auth'])->get('/trabajador', function () {
    return redirect()->route('trabajador.dashboard');
});

// Logout robusto (GET por compatibilidad con sidebar actual)
Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');