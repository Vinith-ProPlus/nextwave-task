<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TaskController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum', 'log.api'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // User routes
    Route::apiResource('users', UserController::class);
    Route::get('/users/{user}/tasks', [UserController::class, 'tasks']);

    // Task routes
    Route::apiResource('tasks', TaskController::class);
});
