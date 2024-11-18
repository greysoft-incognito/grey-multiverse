<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use V1\Services\AppInfo;

Route::get('/', function () {
    return [
        'Welcome to the GreyMultiverse API v2' => AppInfo::basic(2),
    ];
});

Route::get('/', function (Request $request) {
    return [
        'api' => config('app.name'),
        'hello' => 'Star Gazer',
        'welcome' => 'Welcome to the GreyMultiverse API v2!',
        'lastUpdated' => File::exists(base_path('.updated'))
            ? new \Carbon\Carbon(File::lastModified(base_path('.updated')))
            : now(),
    ];
});

Route::middleware('web')->group(base_path('routes/routes-v1/web.php'));
