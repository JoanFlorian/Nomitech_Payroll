<?php

use App\Http\Controllers\Api\EmpleadoAutocompleteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->get('/empleados', EmpleadoAutocompleteController::class)
    ->name('api.empleados.autocomplete');

// Webhook para Cron Externo (Render Free Tier)
Route::get('/cron/auto-close', function (\Illuminate\Http\Request $request) {
    // Token de seguridad simple
    $token = 'nomitech-cron-safe-789'; 
    
    if ($request->query('token') !== $token) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    \Illuminate\Support\Facades\Artisan::call('periods:auto-close');
    $output = \Illuminate\Support\Facades\Artisan::output();
    
    return response()->json([
        'success' => true,
        'message' => 'Auto-close command executed',
        'output' => $output
    ]);
});
