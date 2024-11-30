<?php

use App\Http\Controllers\Forms\FormController;
use App\Http\Controllers\Forms\FormDataController;
use App\Http\Controllers\Forms\FormFieldController;
use Illuminate\Support\Facades\Route;

Route::name('forms.')->prefix('forms')->group(function () {
    Route::get('{form}/fields', [FormFieldController::class, 'scoped']);

    Route::apiResource('fields', FormFieldController::class)
        ->only(['index', 'show']);

    Route::apiResource('{form}/data', FormDataController::class)
        ->only(['store', 'update', 'show'])
        ->scoped();

    Route::apiResource('/', FormController::class)
        ->parameter('', 'form')
        ->only(['index', 'show']);
});
