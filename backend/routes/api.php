<?php

use App\Http\Controllers\Api\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

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
});
