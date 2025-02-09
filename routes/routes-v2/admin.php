<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Admin\Forms\FormController;
use App\Http\Controllers\Admin\Forms\FormDataController;
use App\Http\Controllers\Admin\Forms\FormFieldController;
use App\Http\Controllers\Admin\Forms\FormInfoController;
use App\Http\Controllers\Admin\RescheduleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

$permissionMiddlewares = 'role:'.implode('|', config('permission-defs.roles', []));

Route::get('refresh', function () {
    \Artisan::call('app:sync-roles');
    dump(\Artisan::output());
    \Artisan::call('optimize:clear');
    dump(\Artisan::output());
});

Route::middleware(['auth:sanctum', $permissionMiddlewares])->prefix('admin')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('configurations', ConfigurationController::class)->only(['index', 'show', 'store']);

    Route::apiResource('companies', CompanyController::class)->except(['store']);
    Route::apiResource('reschedules', RescheduleController::class)->except(['store']);
    Route::apiResource('appointments', AppointmentController::class)->except('store');

    Route::name('forms.')->prefix('forms')->group(function () {
        Route::apiResource('{form}/infos', FormInfoController::class);

        Route::get('all/fields', [FormFieldController::class, 'all'])->name('all');

        Route::post('{form}/fields/multiple', [FormFieldController::class, 'multiple'])->name('multiple');

        Route::apiResource('{form}/fields', FormFieldController::class);

        Route::get('all/data', [FormDataController::class, 'all'])->name('data.all');
        Route::get('{form}/stats', [FormDataController::class, 'stats'])->name('stats');
        Route::apiResource('{form}/data', FormDataController::class)->scoped();

        Route::apiResource('/', FormController::class)
            ->parameter('', 'form');
    });
});
