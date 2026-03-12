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

use App\Http\Controllers\ReportesController;

use Illuminate\Http\Request;

Route::get('/', [PricingController::class, 'index']);

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

// Protected App Routes (Auth + Active License + Contractual Access)
Route::middleware(['auth', 'ensure_active_license', 'contractual_access', 'prevent_back_history'])->group(function () {
    // Empleados
    Route::get('/empleados', [App\Http\Controllers\EmployeesController::class, 'index'])->name('empleados.index');
    Route::get('/employees/export', [App\Http\Controllers\EmployeesController::class, 'export'])->name('employees.export');
    Route::get('/employees/export/excel', [App\Http\Controllers\EmployeesController::class, 'exportarEmpleadosExcel'])->name('employees.export.excel');
    Route::get('/employees/export/pdf', [App\Http\Controllers\EmployeesController::class, 'exportarEmpleadosPdf'])->name('employees.export.pdf');
    Route::get('/employees/{doc}/edit', [RegistroUsuarios::class, 'editEmployee'])->name('employees.edit');
    Route::post('/employees/{doc}/update', [RegistroUsuarios::class, 'updateEmployee'])->name('employees.update');

    /* Wizard registro empleado */
    Route::post('/employees/step-1', [RegistroUsuarios::class, 'storeStep1'])->name('employees.step1');
    Route::post('/employees/step-2', [RegistroUsuarios::class, 'storeStep2'])->name('employees.step2');
    Route::post('/employees/final', [RegistroUsuarios::class, 'storeFinal'])->name('employees.final');
    Route::post('/employees/clear-session', [RegistroUsuarios::class, 'clearWizardSession'])->name('employees.clear-session');

    // Nómina Routes
    Route::get('/nomina', [NominaController::class, 'index'])->name('nomina.index');
    Route::get('/nomina/step-1', [NominaController::class, 'step1'])->name('nomina.step1');
    Route::post('/nomina/step-1', [NominaController::class, 'postStep1'])->name('nomina.step1.post');
    Route::get('/nomina/{idSalario}/editar', [NominaController::class, 'edit'])->name('nomina.edit');
    Route::get('/nomina/step-2', [NominaController::class, 'step2'])->name('nomina.step2');
    Route::post('/nomina/step-2', [NominaController::class, 'postStep2'])->name('nomina.step2.post');
    Route::get('/nomina/step-2/ingresos', [NominaController::class, 'step2Ingresos'])->name('nomina.step2.ingresos');
    Route::post('/nomina/step-2/ingresos', [NominaController::class, 'postStep2Ingresos'])->name('nomina.step2.ingresos.post');
    Route::get('/nomina/step-3', [NominaController::class, 'step3'])->name('nomina.step3');
    Route::get('/nomina/export/excel', [NominaController::class, 'exportarNominaExcel'])->name('nomina.export.excel');
    Route::get('/nomina/export/pdf', [NominaController::class, 'exportarNominaPdf'])->name('nomina.export.pdf');
    Route::post('/nomina/realizar', [NominaController::class, 'realizarNominaMasiva'])->name('nomina.realizar');
    Route::post('/nomina/store', [NominaController::class, 'store'])->name('nomina.store');
    Route::get('/nomina/buscar-empleado/{doc}', [NominaController::class, 'buscarEmpleado']);
    Route::get('/nomina/buscar-empleados', [NominaController::class, 'buscarEmpleados']);
    Route::get('/nomina/validar-duplicado/{idContrato}', [NominaController::class, 'checkDuplicate']);

    // Novedades y Reportes
    Route::get('/novedades', [NovedadController::class, 'index'])->name('novedades.index');
    Route::get('/novedades/historial', [NovedadController::class, 'historialContrato'])->name('novedades.historial');
    Route::get('/novedades/historial-novedades', [NovedadController::class, 'historialNovedades'])->name('novedades.historial_novedades');
    Route::post('/novedades/calculo/preview', [NovedadCalculoController::class, 'preview'])->name('novedades.calculo.preview');
    Route::post('/novedades', [NovedadController::class, 'store'])->name('novedades.store');
    Route::put('/novedades/{id_novedad}', [NovedadController::class, 'update'])->name('novedades.update');
    Route::delete('/novedades/{id_novedad}', [NovedadController::class, 'destroy'])->name('novedades.destroy');

    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/exportar/pdf', [ReportesController::class, 'exportarPdf'])->name('reportes.export.pdf');
    Route::get('/reportes/exportar/excel', [ReportesController::class, 'exportarExcel'])->name('reportes.export.excel');

    // Provisiones
    Route::get('/provisiones', [\App\Http\Controllers\ProvisionesController::class, 'index'])->name('provisiones.index');
    Route::get('/provisiones/{doc}/historial', [\App\Http\Controllers\ProvisionesController::class, 'historial'])->name('provisiones.historial');
    Route::post('/provisiones/liquidar-individual', [\App\Http\Controllers\ProvisionesController::class, 'liquidarIndividual'])->name('provisiones.liquidar.individual');
    Route::post('/provisiones/liquidar-masivo', [\App\Http\Controllers\ProvisionesController::class, 'liquidarMasivo'])->name('provisiones.liquidar.masivo');
    Route::post('/provisiones/pagar-prestacion', [\App\Http\Controllers\ProvisionesController::class, 'pagarPrestacion'])->name('provisiones.pagar-prestacion');
    Route::post('/provisiones/cesantias/retiro-parcial', [\App\Http\Controllers\ProvisionesController::class, 'retiroParcialCesantias'])->name('provisiones.cesantias.retiro-parcial');
    Route::post('/provisiones/cesantias/retiro-empresa', [\App\Http\Controllers\ProvisionesController::class, 'retiroEmpresa'])->name('provisiones.cesantias.retiro-empresa');
    Route::post('/provisiones/cesantias/autorizacion-fondo', [\App\Http\Controllers\ProvisionesController::class, 'autorizacionFondo'])->name('provisiones.cesantias.autorizacion-fondo');
    Route::post('/provisiones/cesantias/consignacion-anual', [\App\Http\Controllers\ProvisionesController::class, 'generarConsignacionAnual'])->name('provisiones.cesantias.consignacion-anual');
    Route::get('/provisiones/cesantias/certificado/{withdrawal_id}', [\App\Http\Controllers\ProvisionesController::class, 'descargarCertificado'])->name('provisiones.cesantias.certificado');
    Route::get('/provisiones/comprobante/{movement_id}', [\App\Http\Controllers\ProvisionesController::class, 'descargarComprobantePrestacion'])->name('provisiones.comprobante');

    // Nómina Electrónica
    Route::get('/nomina-electronica', [\App\Http\Controllers\NominaElectronicaController::class, 'index'])->name('nomina-electronica.index');

    // Gestión de Periodos
    Route::get('/periodos', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'index'])
        ->name('periodos.index');
    Route::post('/periodos', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'store'])
        ->name('periodos.store');
    Route::get('/periodos/{id}/select', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'select'])
        ->name('periodos.select');
    Route::get('/periodos/{id}/suggest', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'suggestNext'])
        ->name('periodos.suggest');
    Route::get('/periodos/{id}/export-preview', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'getExportPreview'])
        ->name('periodos.export.preview');
    Route::post('/periodos/{id}/cerrar', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'close'])
        ->name('periodos.cerrar');
    Route::post('/periodos/{id}/exportar', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'exportar'])
        ->name('periodos.exportar');
    Route::get('/periodos/exportacion/{id}/descargar', [\App\Http\Controllers\PeriodoLiquidacionController::class, 'downloadExport'])
        ->name('periodos.exportar.descargar');
});

Route::middleware(['auth', 'ensure_active_license', 'contractual_access', 'admin_empresa', 'prevent_back_history'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/catalogos', [CatalogosEmpresaController::class, 'index'])->name('catalogos.index');
        Route::get('/catalogos/{catalogo}', [CatalogosEmpresaController::class, 'show'])->name('catalogos.show');
        Route::get('/catalogos/{catalogo}/data', [CatalogosEmpresaController::class, 'data'])->name('catalogos.data');
        Route::post('/catalogos/{catalogo}', [CatalogosEmpresaController::class, 'store'])->name('catalogos.store');
        Route::put('/catalogos/{catalogo}/{id}', [CatalogosEmpresaController::class, 'update'])->name('catalogos.update');
        Route::patch('/catalogos/{catalogo}/{id}/estado', [CatalogosEmpresaController::class, 'toggleEstado'])->name('catalogos.toggle-estado');
    });

// Superadmin routes protected by auth and role
Route::middleware(['auth', 'is_superadmin', 'prevent_back_history'])->prefix('superadmin')->name('superadmin.')->group(function () {
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

// Logout robusto (GET por compatibilidad con sidebar actual)
Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');