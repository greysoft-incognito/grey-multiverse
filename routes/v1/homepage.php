<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Guest\FormController;
use App\Http\Controllers\v1\Guest\FormDataController;
use App\Http\Controllers\v1\Guest\FormFieldController;
use App\Http\Controllers\v1\HomeController;

Route::name('home.')->group(function () {
    Route::get('/', function () {
        return [
            'Welcome to the GreyMultiverse API v1' => AppInfo::basic(),
        ];
    });

    Route::apiResource('get/forms', FormController::class)->only(['index', 'show']);
    Route::get('get/form-fields/form/{form}', [FormFieldController::class, 'form']);
    Route::apiResource('get/form-fields', FormFieldController::class)->parameters(['form-fields' => 'id'])->only(['index', 'show']);
    Route::apiResource('get/form-data/{form}', FormDataController::class)->parameters(['{form}' => 'id'])->only(['store', 'update', 'show']);

    Route::name('data.')->controller(HomeController::class)->group(function () {
        Route::get('/get/settings', 'settings')->name('settings');
        Route::get('/get/verification/{action}/{task?}', 'manageFormFields')->name('verification.data');
        Route::post('/get/verification/{action}', 'manageFormFields')->middleware(['auth:sanctum']);
    });
});