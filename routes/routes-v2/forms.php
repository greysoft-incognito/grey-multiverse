<?php

use App\Http\Controllers\Forms\FormController;
use App\Http\Controllers\Forms\FormDataController;
use App\Http\Controllers\Forms\FormFieldController;
use App\Http\Controllers\Forms\FormFieldGroupController;
use App\Http\Middleware\CheckFormDataAccess;
use Illuminate\Support\Facades\Route;

Route::name('v2.forms.')->prefix('forms')->group(function () {
    Route::get('{form}/fields', [FormFieldController::class, 'scoped']);

    Route::apiResource('fields', FormFieldController::class)
        ->only(['index', 'show']);

    Route::apiResource('{form}/data', FormDataController::class)
        ->middleware([CheckFormDataAccess::class])
        ->except(['update'])
        ->scoped();

    Route::put('{form}/data/draft/{data}', [FormDataController::class, 'draft'])
        ->middleware([CheckFormDataAccess::class]);

    Route::apiResource('/{form}/field-groups', FormFieldGroupController::class)
        ->only(['index', 'show'])
        ->scoped();

    Route::apiResource('/', FormController::class)
        ->parameter('', 'form')
        ->only(['index', 'show']);
});
