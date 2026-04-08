<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\AuditLogController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('role:ADMIN');
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update'])->middleware('role:ADMIN');
        Route::patch('/{id}/status', [UserController::class, 'updateStatus'])->middleware('role:ADMIN');
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
    });

    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{id}', [TaskController::class, 'show']);
        Route::put('/{id}', [TaskController::class, 'update']);
        Route::delete('/{id}', [TaskController::class, 'destroy']);
        Route::post('/{id}/approve', [TaskController::class, 'approve'])->middleware('role:ADMIN');
        Route::post('/{id}/reject', [TaskController::class, 'reject'])->middleware('role:ADMIN');
        
        Route::get('/{taskId}/comments', [CommentController::class, 'index']);
        Route::post('/{taskId}/comments', [CommentController::class, 'store']);
        Route::delete('/{taskId}/comments/{commentId}', [CommentController::class, 'destroy']);
    });

    Route::prefix('audit-logs')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->middleware('role:ADMIN');
        Route::get('/{id}', [AuditLogController::class, 'show'])->middleware('role:ADMIN');
    });
});
