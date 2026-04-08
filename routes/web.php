<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\TaskController as WebTaskController;
use App\Http\Controllers\Web\UserController as WebUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/tasks', [WebTaskController::class, 'index']);
    Route::post('/tasks', [WebTaskController::class, 'store']);
    Route::get('/tasks/create', [WebTaskController::class, 'create']);
    Route::get('/tasks/{task}', [WebTaskController::class, 'show']);
    Route::put('/tasks/{task}', [WebTaskController::class, 'update']);
    Route::delete('/tasks/{task}', [WebTaskController::class, 'destroy']);
    Route::post('/tasks/{task}/approve', [WebTaskController::class, 'approve']);
    Route::post('/tasks/{task}/reject', [WebTaskController::class, 'reject']);
    Route::post('/tasks/{task}/comments', [WebTaskController::class, 'storeComment']);

    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/users', [WebUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [WebUserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [WebUserController::class, 'update']);
        Route::patch('/users/{user}/status', [WebUserController::class, 'updateStatus']);
        Route::get('/users/{user}/permissions', [WebUserController::class, 'permissions'])->name('users.permissions');
        Route::put('/users/{user}/permissions', [WebUserController::class, 'updatePermissions'])->name('users.updatePermissions');
        Route::get('/audit-logs', [WebUserController::class, 'auditLogs']);
    });
});

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});
