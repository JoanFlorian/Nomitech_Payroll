<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroUsuarios;

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
