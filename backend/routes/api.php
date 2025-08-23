<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

if (env('APP_DEBUG')) {
    Route::get('/test', function () {
        return response()->json([
            'message' => 'test',
        ]);
    });
}

Route::post('/auth/login', [LoginController::class, 'login'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [LoginController::class, 'logout'])->name('auth.logout');

    Route::put('tasks/update-positions', [TaskController::class, 'updatePositions'])->name('tasks.update-positions');
    Route::resource('tasks', TaskController::class);
});
