<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroUsuarios;
use App\Http\Controllers\facturacioncontroller;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login2', function () {
    return view('auth.login');
});

Route::get('/empleados', function () {
    return view('empleados.index');
})->name('empleados.index');

Route::get('/superadmin', [facturacioncontroller::class, 'facturacion'])->name('superadmin.index');
Route::get('/superadmin/facturacion', [facturacioncontroller::class, 'facturacion'])->name('superadmin.facturacion');
Route::get('/superadmin/factura/{pagoId}/pdf', [facturacioncontroller::class, 'descargarFacturaPdf'])->name('superadmin.factura.pdf');
Route::get('/superadmin/factura/{pagoId}', [facturacioncontroller::class, 'getFactura'])->name('superadmin.factura');

// Superadmin extra pages
Route::get('/superadmin/empresas', function () { return view('superadmin.empresas'); })->name('superadmin.empresas');
Route::get('/superadmin/configuracion', function () { return view('superadmin.configuracion'); })->name('superadmin.configuracion');
Route::get('/superadmin/crear-planes', function () { return view('superadmin.crear-planes'); })->name('superadmin.crear-planes');

// Simple logout helper (GET) — change to POST if using auth scaffolding
Route::get('/logout', function () { Auth::logout(); return redirect('/'); })->name('logout');

/* Wizard registro empleado */
Route::post('/employees/step-1', [RegistroUsuarios::class, 'storeStep1'])->name('employees.step1');
Route::post('/employees/step-2', [RegistroUsuarios::class, 'storeStep2'])->name('employees.step2');
Route::post('/employees/final',  [RegistroUsuarios::class, 'storeFinal'])->name('employees.final');

