<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroUsuarios;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\EmployeeWizardController;
use App\Http\Controllers\SuperAdmin\EmpresaController;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/login2', function () {
    return view('auth.login');
});

Route::get('/empleados', function () {
    return view('empleados.index');
})->name('empleados.index');

/* Wizard registro empleado */
Route::post('/employees/step-1', [RegistroUsuarios::class, 'storeStep1'])->name('employees.step1');
Route::post('/employees/step-2', [RegistroUsuarios::class, 'storeStep2'])->name('employees.step2');
Route::post('/employees/final',  [RegistroUsuarios::class, 'storeFinal'])->name('employees.final');


Route::get('/nomina', [NominaController::class, 'index'])->name('nomina.index');

Route::get('/nomina/step-1', [NominaController::class, 'step1'])->name('nomina.step1');
Route::post('/nomina/step-1', [NominaController::class, 'postStep1'])->name('nomina.step1.post');

Route::get('/nomina/step-2', [NominaController::class, 'step2'])->name('nomina.step2');
Route::post('/nomina/step-2', [NominaController::class, 'postStep2'])->name('nomina.step2.post');

Route::get('/nomina/step-3', [NominaController::class, 'step3'])->name('nomina.step3');
Route::post('/nomina/store', [NominaController::class, 'store'])->name('nomina.store');

Route::get('/nomina/buscar-empleado/{doc}', [NominaController::class, 'buscarEmpleado']);


Route::get('/superadmin/empresas', [EmpresaController::class, 'index'])->name('superadmin.empresas.index');
Route::get('/superadmin/empresas/{empresa}', [EmpresaController::class, 'show'])->name('superadmin.empresas.show');
Route::put('/superadmin/empresas/{empresa}', [EmpresaController::class, 'update'])->name('superadmin.empresas.update');