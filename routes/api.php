<?php

use Illuminate\Support\Facades\Route;
use V1\Services\AppInfo;

foreach ([null, 1, 2] as $v) {
    Route::get($v ? "v{$v}" : '', function () use ($v) {
        return [
            'api' => config('app.name'),
            'Welcome to the GreyMultiverse API'.($v ? " v{$v}" : '') => AppInfo::basic($v),
        ];
    });
}

Route::middleware('api')->prefix('v1')->group(base_path('routes/routes-v1/api.php'));

$files = glob(__DIR__.'/routes-v2/*.php');

foreach ($files as $file) {
    Route::middleware('api')->prefix('v2')->group($file);
}