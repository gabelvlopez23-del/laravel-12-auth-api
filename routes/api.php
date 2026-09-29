<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Endpoints públicos (No requieren token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Endpoints privados (Requieren enviar el Bearer Token en el header Authorization)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
