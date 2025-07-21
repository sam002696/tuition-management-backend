<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ConnectionRequest\ConnectionRequestController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\TuitionEvent\TuitionEventController;
use Illuminate\Support\Facades\Route;

// auth routes (no authentication required)
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');


Route::middleware(['auth:sanctum'])->group(function () {

    // connection request routes

    Route::get('/student-details', [ConnectionRequestController::class, 'findStudent']);
    Route::post('/connection/send', [ConnectionRequestController::class, 'send']);
    Route::post('/connection/respond/{id}', [ConnectionRequestController::class, 'respond']);
    Route::get('/connection/my-pending-requests', [ConnectionRequestController::class, 'listMyPendingConnections']);
    Route::get('/connection/my-accepted-requests', [ConnectionRequestController::class, 'listAllAcceptedActiveConnections']);


    // tuition event routes

    Route::post('/tuition-events', [TuitionEventController::class, 'create']);
    Route::post('/tuition-events/respond/{id}', [TuitionEventController::class, 'respond']);
    Route::get('/tuition-events/my', [TuitionEventController::class, 'myEvents']);
    Route::get('/tuition-events/pending', [TuitionEventController::class, 'myPendingEvents']);


    // notification routes
    Route::get('/users/{userId}/notifications', [NotificationController::class, 'index']);
});
