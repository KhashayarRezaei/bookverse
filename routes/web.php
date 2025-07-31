<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Horizon Dashboard (protected by admin middleware)
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::get('/horizon', function () {
        return redirect('/horizon/dashboard');
    });
});
