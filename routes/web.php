<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:verification-link'])
    ->name('verification.verify');

Route::get('/reset-password/{token}', function () {
    return view('welcome');
})->name('password.reset');

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
