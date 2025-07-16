<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ConnectionRequest\ConnectionRequestController;

// auth routes (no authentication required)
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/connection/send', [ConnectionRequestController::class, 'send']);
    Route::post('/connection/respond/{id}', [ConnectionRequestController::class, 'respond']);
    Route::get('/connection/my-requests', [ConnectionRequestController::class, 'listMyConnections']);
});