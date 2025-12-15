<?php

use Illuminate\Support\Facades\Route;
use V1\Http\Controllers\Guest\FormController;
use V1\Http\Controllers\Guest\FormDataController;
use V1\Http\Controllers\Guest\FormFieldController;
use V1\Http\Controllers\HomeController;
use V1\Http\Controllers\ReservationController;
use V1\Http\Controllers\SpacesController;

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

Route::name('spaces.')->prefix('spaces')->controller(SpacesController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{space}', 'show')->name('show');
    Route::name('reserve.')->prefix('{space}/reserve')->controller(ReservationController::class)->group(function () {
        Route::post('/', 'store')->name('store')->middleware(['auth:sanctum']);
        Route::post('/guest', 'guest')->name('guest');
    });
});
