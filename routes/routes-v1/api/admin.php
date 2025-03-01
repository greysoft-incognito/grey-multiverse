<?php

use Illuminate\Support\Facades\Route;
use V1\Http\Controllers\Admin\AdminController;
use V1\Http\Controllers\Admin\ReservationController;
use V1\Http\Controllers\Admin\SpacesController;
use V1\Http\Controllers\Admin\TransactionController;
use V1\Http\Controllers\Manage\FormController as SuFormController;
use V1\Http\Controllers\Manage\FormDataController as SuFormDataController;
use V1\Http\Controllers\Manage\FormFieldController as SuFormFieldController;
use V1\Http\Controllers\Manage\FormInfoController;
use V1\Http\Controllers\Manage\UsersController;

Route::middleware(['auth:sanctum', 'admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::post('configuration', [AdminController::class, 'saveSettings']);

    Route::apiResource('/spaces', SpacesController::class, ['as' => 'spaces']);
    Route::name('spaces.')->prefix('spaces')->controller(SpacesController::class)->group(function () {
        Route::get('/reservations/{status}', [ReservationController::class, 'all'])->name('all');
        Route::name('reservations.')->prefix('{space}/reservations')->controller(ReservationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{reservation}', 'show')->name('show');
            Route::put('/{reservation}/status', 'status')->name('status');
        });
    });

    Route::apiResource('/transactions', TransactionController::class);

    Route::apiResource('forms', SuFormController::class, ['as' => 'v1.forms']);
    Route::apiResource('form-infos/{form}', FormInfoController::class)->parameter('{form}', 'info');

    Route::name('form-fields.')->prefix('form-fields')->group(function () {
        Route::get('/', [SuFormFieldController::class, 'all'])->name('all');
        Route::post('/{form}/multiple', [SuFormFieldController::class, 'multiple'])->name('multiple');
        Route::apiResource('/{form}', SuFormFieldController::class)->parameters(['{form}' => 'field']);
    });

    Route::name('form-data.')->prefix('form-data')->group(function () {
        Route::get('/all', [SuFormDataController::class, 'all'])->name('all');
        Route::get('/stats', [SuFormDataController::class, 'stats'])->name('stats');
        Route::apiResource('/{form}', SuFormDataController::class)->parameters(['{form}' => 'id']);
    });

    Route::apiResource('users', UsersController::class, ['as' => 'v1.users']);
});
