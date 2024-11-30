<?php

use App\Helpers\Providers;
use Illuminate\Support\Facades\Route;

Route::get('/initialize', function () {
    return Providers::response()->success([
        'data' => [
            'configuration' => Providers::config(),
            'settings' => [
                'timemap' => config('api.timemap'),
                'dates' => config('api.dates'),
            ],
            'csrf_token' => csrf_token()
        ],
        'message' => 'Initialised',
        'status' => 'success',
    ]);
});