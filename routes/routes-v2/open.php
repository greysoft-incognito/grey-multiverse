<?php

use App\Helpers\Providers;
use App\Models\BizMatch\Appointment;
use Illuminate\Support\Facades\Route;

Route::get('/initialize', function () {

    Appointment::first()->notifications('1');

    return Providers::response()->success([
        'data' => [
            'configuration' => Providers::config(),
        ],
        'message' => 'Initialised',
        'status' => 'success',
    ]);
});
