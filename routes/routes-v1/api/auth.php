<?php

use Illuminate\Support\Facades\Route;
use V1\Http\Controllers\Auth\AuthenticatedSessionController;
use V1\Http\Controllers\Auth\EmailPhoneVerificationNotificationController;
use V1\Http\Controllers\Auth\NewPasswordController;
use V1\Http\Controllers\Auth\PasswordResetLinkController;
use V1\Http\Controllers\Auth\RegisteredUserController;
use V1\Http\Controllers\Auth\VerifyEmailPhoneController;

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');

Route::get('/register/preflight/{token}', [RegisteredUserController::class, 'preflight'])
    ->middleware('guest')
    ->name('register.preflight');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::get('/web/login', [AuthenticatedSessionController::class, 'index'])
    ->middleware('guest')
    ->name('web.login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest', 'throttle:code-requests'])
    ->name('password.email');

Route::post('/reset-password/check-code', [NewPasswordController::class, 'check'])
    ->middleware('guest')
    ->name('password.code.check');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.update');

Route::get('/verify-email/{id}/{hash}', [VerifyEmailPhoneController::class, '__invoke'])
    ->middleware(['auth:sanctum', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/verify/with-code/{type?}', [VerifyEmailPhoneController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.verify.code');

Route::get('/send/verification-notification/{type?}', [EmailPhoneVerificationNotificationController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:code-requests'])
    ->name('verification.send');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:sanctum')
->name('logout');