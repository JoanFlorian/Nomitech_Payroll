<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroUsuarios;
use App\Http\Controllers\PricingController;

Route::get('/', [PricingController::class, 'index']);

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

    // Polling Endpoint
    Route::get('/api/payment/status/{sessionId}', [App\Http\Controllers\CheckoutController::class, 'checkStatus'])->name('payment.status');

    Route::get('/licencia/pending', function () {
        return "Licencia Inactiva o Pago Pendiente. Por favor complete el pago."; // View: licencia.pending
    })->name('licencia.pending');

    Route::get('/licencia/required', function () {
        return "No se encontró una licencia activa para su empresa. Por favor adquiera un plan."; // View: licencia.required
    })->name('licencia.required');

    Route::get('/licencia/expired', function () {
        return "Licencia Expirada. Por favor renueve su plan."; // View: licencia.expired
    })->name('licencia.expired');

    Route::get('/empresa/select', function () {
        return "Seleccionar Empresa"; // View: auth.empresa-select
    })->name('empresa.select');
});

// Protected App Routes (Auth + Active License)
Route::middleware(['auth', 'ensure_active_license'])->group(function () {
    Route::get('/empleados', function () {
        return view('empleados.index');
    })->name('empleados.index');

    /* Wizard registro empleado */
    Route::post('/employees/step-1', [RegistroUsuarios::class, 'storeStep1'])->name('employees.step1');
    Route::post('/employees/step-2', [RegistroUsuarios::class, 'storeStep2'])->name('employees.step2');
    Route::post('/employees/final', [RegistroUsuarios::class, 'storeFinal'])->name('employees.final');
});
