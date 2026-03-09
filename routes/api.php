<?php

use App\Http\Controllers\Api\EmpleadoAutocompleteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->get('/empleados', EmpleadoAutocompleteController::class)
    ->name('api.empleados.autocomplete');
