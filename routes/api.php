<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HouseholdMembershipController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\SpoonacularAPI;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password');
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:forgot-password');
Route::post('/auth/email/verification-notification', [AuthController::class, 'resendVerification'])->middleware('throttle:verification-resend');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'me']);
    Route::get('/auth/verify', [AuthController::class, 'verify']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/household-access/redeem', [HouseholdMembershipController::class, 'redeemInvite']);
});

Route::middleware(['auth:sanctum', 'verified', 'household.context'])->group(function () {
    Route::post('/get_recipies', [SpoonacularAPI::class, 'get_recipies']);

    Route::get('/households/my', [HouseholdMembershipController::class, 'myHouseholds']);
    Route::post('/households', [HouseholdMembershipController::class, 'createHousehold']);
    Route::post('/households/active', [HouseholdMembershipController::class, 'setActiveHousehold']);
    Route::post('/households/join-requests', [HouseholdMembershipController::class, 'requestJoin']);
    Route::get('/households/{household}/management', [HouseholdMembershipController::class, 'management']);
    Route::post('/households/{household}/accesses', [HouseholdMembershipController::class, 'sendAccess']);
    Route::patch('/households/{household}/open-membership', [HouseholdMembershipController::class, 'updateOpenMembership']);
    Route::get('/households/{household}/join-requests', [HouseholdMembershipController::class, 'pendingRequests']);
    Route::post('/household-memberships/{membership}/approve', [HouseholdMembershipController::class, 'approve']);
    Route::post('/household-memberships/{membership}/reject', [HouseholdMembershipController::class, 'reject']);
    Route::delete('/household-memberships/{membership}', [HouseholdMembershipController::class, 'removeMember']);

    Route::middleware('household.required')->apiResource('ingredients', IngredientController::class);
});
