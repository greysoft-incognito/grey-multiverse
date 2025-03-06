<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Admin\Forms\FormController;
use App\Http\Controllers\Admin\Forms\FormDataController;
use App\Http\Controllers\Admin\Forms\FormDataReviewerController;
use App\Http\Controllers\Admin\Forms\FormExtraController;
use App\Http\Controllers\Admin\Forms\FormFieldController;
use App\Http\Controllers\Admin\Forms\FormFieldGroupController;
use App\Http\Controllers\Admin\Forms\FormInfoController;
use App\Http\Controllers\Admin\Forms\ReviewerController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\RescheduleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

$permissionMiddlewares = 'permission:' . implode('|', config('permission-defs.permissions', []));

Route::middleware(['auth:sanctum', $permissionMiddlewares])->prefix('admin')->name('admin.')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('configurations', ConfigurationController::class)->only(['index', 'show', 'store']);

    // Notifications Templates
    Route::apiResource('configurations/notifications/templates', NotificationTemplateController::class)
        ->except(['store', 'destroy']);

    Route::apiResource('companies', CompanyController::class)->except(['store']);
    Route::apiResource('reschedules', RescheduleController::class)->except(['store']);
    Route::apiResource('appointments', AppointmentController::class)->except('store');

    Route::name('forms.')->prefix('forms')->group(function () {
        Route::apiResource('{form}/infos', FormInfoController::class);

        Route::get('all/fields', [FormFieldController::class, 'all'])->name('all');

        Route::post('{form}/fields/multiple', [FormFieldController::class, 'multiple'])->name('multiple');

        Route::apiResource('{form}/fields', FormFieldController::class);

        Route::apiResource('/{form}/field-groups', FormFieldGroupController::class)->scoped();
        Route::put('/{form}/field-groups/{field_group}/sync', [FormFieldGroupController::class, 'sync'])->name('sync');

        Route::get('all/data', [FormDataController::class, 'all'])->name('data.all');
        Route::get('{form}/stats', [FormExtraController::class, 'stats'])->name('stats');
        Route::post('{form}/config', [FormExtraController::class, 'config'])->name('config');

        Route::apiResource('/{form}/reviewers', ReviewerController::class)
            ->except(['show', 'update'])
            ->scoped();

        Route::name('formdata.')->group(function () {
            Route::apiResource('{form}/data', FormDataController::class)->scoped();

            Route::apiResource('/{form}/data/{data}/reviewers', FormDataReviewerController::class)
                ->except(['show', 'update'])
                ->scoped();
        });

        Route::apiResource('/', FormController::class)->parameter('', 'form');
    });
});
