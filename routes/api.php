<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ConnectionRequest\ConnectionRequestController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\TeacherHome\TeacherHomeController;
use App\Http\Controllers\TuitionDetails\TuitionDetailsController;
use App\Http\Controllers\TuitionEvent\TuitionEventController;
use Illuminate\Support\Facades\Route;

// auth routes (no authentication required)
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

// password reset routes
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:6,1');
Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1');


Route::middleware(['auth:sanctum'])->group(function () {

    // teacher home route

    Route::get('/teacher/home', [TeacherHomeController::class, 'index']);


    // change password route

    Route::post('/password/change', [AuthController::class, 'changePassword']);

    // connection request routes

    Route::post('/student-details', [ConnectionRequestController::class, 'findStudent']);
    Route::post('/connection/send', [ConnectionRequestController::class, 'send']);
    Route::post('/connection/respond/{id}', [ConnectionRequestController::class, 'respond']);
    Route::get('/connection/my-pending-requests', [ConnectionRequestController::class, 'listMyPendingConnections']);
    Route::get('/connection/my-accepted-requests', [ConnectionRequestController::class, 'listAllAcceptedActiveConnections']);
    Route::post('/connection/check-connection-status', [ConnectionRequestController::class, 'checkConnectionStatus']);
    Route::get('/connections/count', [ConnectionRequestController::class, 'countConnection']);
    Route::get('connections/{id}', [ConnectionRequestController::class, 'show']);

    // filtered connections

    Route::get('/connections', [ConnectionRequestController::class, 'listConnections']);



    Route::patch('/connections/{id}/disconnect', [ConnectionRequestController::class, 'disconnectStudentConnection']);



    // tuition event routes

    Route::post('/tuition-events', [TuitionEventController::class, 'create']);
    Route::post('/tuition-events/respond/{id}', [TuitionEventController::class, 'respond']);
    Route::get('/tuition-events/my', [TuitionEventController::class, 'myEvents']);
    Route::get('/tuition-events/pending', [TuitionEventController::class, 'myPendingEvents']);
    Route::get('/tuition-events/student', [TuitionEventController::class, 'getEventsWithStudent']);
    Route::get('/tuition-events/teacher', [TuitionEventController::class, 'getEventsWithTeacher']);


    // tuition details routes

    Route::prefix('tuition-details')->group(function () {
        Route::post('/', [TuitionDetailsController::class, 'store']);
        Route::patch('/{id}', [TuitionDetailsController::class, 'update']);
        Route::get('/{id}', [TuitionDetailsController::class, 'show']);
        Route::get('/teacher/{teacherId}/student/{studentId}', [TuitionDetailsController::class, 'getByTeacherAndStudent']);
    });


    // notification routes
    Route::get('/users/{userId}/notifications', [NotificationController::class, 'index']);
});
