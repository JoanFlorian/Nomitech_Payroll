<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroUsuarios;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login2', function () {
    return view('auth.login');
})->name('login');

// Auth Routes
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'create'])->name('register.create');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'store'])->name('register');

Route::get('/api/cities/search', [App\Http\Controllers\Auth\RegisterController::class, 'searchCities']);
Route::get('/api/city-details/{id}', [App\Http\Controllers\Auth\RegisterController::class, 'getCityDetails']);
Route::get('/api/cities/{department}', [App\Http\Controllers\Auth\RegisterController::class, 'getCities']);

// Stripe Webhook
Route::post('/stripe/webhook', [App\Http\Controllers\StripeWebhookController::class, 'handleWebhook']);

// License Status Routes (Protected by auth, but handled by middleware redirection)
Route::middleware('auth')->group(function () {
    Route::get('/checkout/{pago}', [App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{pago}/session', [App\Http\Controllers\CheckoutController::class, 'createSession'])->name('checkout.session');
    Route::get('/checkout-status/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout-status/cancel', [App\Http\Controllers\CheckoutController::class, 'cancel'])->name('checkout.cancel');

    Route::get('/licencia/pending', function () {
        return "Licencia Inactiva o Pago Pendiente. Por favor complete el pago."; // View: licencia.pending
    })->name('licencia.pending');

    Route::get('/licencia/expired', function () {
        return "Licencia Expirada."; // View: licencia.expired
    })->name('licencia.expired');

    Route::get('/empresa/select', function () {
        return "Seleccionar Empresa"; // View: auth.empresa-select
    })->name('empresa.select');
});

Route::get('/empleados', function () {
    return view('empleados.index');
})->name('empleados.index');

/* Wizard registro empleado */
Route::post('/employees/step-1', [RegistroUsuarios::class, 'storeStep1'])->name('employees.step1');
Route::post('/employees/step-2', [RegistroUsuarios::class, 'storeStep2'])->name('employees.step2');
Route::post('/employees/final', [RegistroUsuarios::class, 'storeFinal'])->name('employees.final');
