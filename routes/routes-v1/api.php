<?php

use Illuminate\Support\Facades\Route;
use V1\Http\Controllers\Admin\ReservationController;
use V1\Http\Controllers\Manage\FormController as SuFormController;
use V1\Http\Controllers\Manage\FormDataController as SuFormDataController;
use V1\Http\Controllers\Manage\FormFieldController as SuFormFieldController;
use V1\Http\Controllers\PaymentController;
use V1\Http\Controllers\ScanHistoryController;
use V1\Http\Controllers\TransactionController;

header('SameSite:  None');
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Broadcast::routes(['middleware' => ['auth:sanctum']]);

// dd(File::exists(base_path('routes/v1/api.php')));
Route::middleware(['auth:sanctum'])->group(function () {
    Route::name('user.')->prefix('user')->group(function () {
        // Load user's scan history
        Route::get('scan-history', [ScanHistoryController::class, 'index'])->name('scan-history');
        // schow scan history
        Route::get('scan-history/{scan}', [ScanHistoryController::class, 'show'])->name('scan-history.show');

        Route::get('transactions/{status?}', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{reference}/invoice', [TransactionController::class, 'invoice'])->name('transactions.invoice');
        Route::apiResource('transactions', TransactionController::class)->except('index');
    });

    Route::name('manage.')->prefix('manage')->group(function () {
        Route::apiResource('forms', SuFormController::class)->only(['index', 'show']);
        Route::get('form-fields', [SuFormFieldController::class, 'all'])->name('form-fields');
        Route::apiResource('form-fields/{form}', SuFormFieldController::class)
            ->parameters(['{form}' => 'field'])->except(['store', 'update', 'destroy']);
        Route::get('form-data/all', [SuFormDataController::class, 'all'])->name('form-data.all');
        Route::post('qr/form-data', [SuFormDataController::class, 'decodeQr'])->name('decode.qr');
        Route::post('qr/reservation-data', [ReservationController::class, 'decodeQr'])->name('decode.reservation.qr');

        Route::name('form-data.')->prefix('form-data')->group(function () {
            Route::get('stats/{form}', [SuFormDataController::class, 'stats'])->name('stats');
            Route::apiResource('{form}', SuFormDataController::class)->parameters(['{form}' => 'id']);
        });
    });

    Route::name('payment.')->prefix('payment')->controller(PaymentController::class)->group(function () {
        Route::post('/initialize', 'store')->name('initialize');
        Route::get('/paystack/verify/{type?}', 'paystackVerify')->name('payment.paystack.verify');
        Route::delete('/terminate', 'terminateTransaction')->name('terminate');
    });

    Route::get('/playground', function () {
        return (new Shout())->viewable();
    })->name('playground');
});

$files = glob(__DIR__.'/api/*.php');

foreach ($files as $file) {
    Route::middleware('api')->group($file);
}
