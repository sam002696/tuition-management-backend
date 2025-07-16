<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ConnectionRequest\ConnectionRequestController;
use App\Http\Controllers\TuitionEvent\TuitionEventController;

// auth routes (no authentication required)
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');


Route::middleware(['auth:sanctum'])->group(function () {

    // connection request routes

    Route::post('/connection/send', [ConnectionRequestController::class, 'send']);
    Route::post('/connection/respond/{id}', [ConnectionRequestController::class, 'respond']);
    Route::get('/connection/my-requests', [ConnectionRequestController::class, 'listMyConnections']);


    // tuition event routes

    Route::post('/tuition-events', [TuitionEventController::class, 'create']);
    Route::post('/tuition-events/respond/{id}', [TuitionEventController::class, 'respond']);
    Route::get('/tuition-events/my', [TuitionEventController::class, 'myEvents']);
    Route::get('/tuition-events/pending', [TuitionEventController::class, 'myPendingEvents']);
});