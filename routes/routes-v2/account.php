<?php

use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\User\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('account')->group(function () {
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'destroy']);
    Route::post('/update-profile-picture', [AccountController::class, 'updateProfilePicture'])->name('update.profile.picture');
    Route::post('/update-password', [AccountController::class, 'updatePassword'])->name('update.password');
    Route::apiResource('/', AccountController::class)->only(['index', 'update'])->parameter('', 'identifier');
});
