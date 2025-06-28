<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;

Route::get('/', [DashboardController::class, 'login'])->name('login');
Route::get('/login', [DashboardController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [DashboardController::class, 'users'])->name('users.index');
    Route::get('/tasks', [DashboardController::class, 'tasks'])->name('tasks.index');
});
