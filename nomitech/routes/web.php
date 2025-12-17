<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login2', function () {
    return view('auth.login');
});

Route::get('/empleados', function () {
    return view('empleados.index');
});
