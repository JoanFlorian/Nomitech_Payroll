<?php

use App\Http\Controllers\Api\EmpleadoAutocompleteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->get('/empleados', EmpleadoAutocompleteController::class)
    ->name('api.empleados.autocomplete');

// Webhook para Cron Externo (Render Free Tier)
Route::get('/cron/run-schedule', function (\Illuminate\Http\Request $request) {
    // Token de seguridad simple
    $token = 'nomitech-cron-safe-789'; 
    
    if ($request->query('token') !== $token) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    // Ejecutamos los comandos directamente para asegurar que se ejecuten 
    // independientemente de si el minuto coincide con el scheduler (frecuencia)
    \Illuminate\Support\Facades\Artisan::call('periods:auto-close');
    $outputPeriods = \Illuminate\Support\Facades\Artisan::output();
    
    \Illuminate\Support\Facades\Artisan::call('provisions:auto-liquidate');
    $outputProvisions = \Illuminate\Support\Facades\Artisan::output();
    
    return response()->json([
        'success' => true,
        'message' => 'Commands executed',
        'server_time' => now('America/Bogota')->toDateTimeString(),
        'details' => [
            'periods' => $outputPeriods,
            'provisions' => $outputProvisions
        ]
    ]);
});
