<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpoonacularAPI;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password');
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:forgot-password');
    Route::post('/auth/email/verification-notification', [AuthController::class, 'resendVerification'])->middleware('throttle:verification-resend');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/get_recipies', [SpoonacularAPI::class, 'get_recipies']);
});
