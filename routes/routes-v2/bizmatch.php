<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\RescheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->name('bizmatch.')->group(function () {
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('reschedules', RescheduleController::class)->except(['store', 'destroy']);
    Route::apiResource('appointments', AppointmentController::class)->except('destroy');
});
