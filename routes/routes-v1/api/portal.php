<?php

use App\Models\Portal\LearningPath;
use Illuminate\Support\Facades\Route;
use V1\Http\Controllers\Admin\Portal\BlogController as AdminBlogController;
use V1\Http\Controllers\Admin\Portal\CardController;
use V1\Http\Controllers\Admin\Portal\PortalController as AdminPortalController;
use V1\Http\Controllers\Admin\Portal\PortalPageController as AdminPortalPageController;
use V1\Http\Controllers\Admin\Portal\SectionController;
use V1\Http\Controllers\Admin\Portal\SlidersController;
use V1\Http\Controllers\Portal\BlogController;
use V1\Http\Controllers\Portal\PortalController;
use V1\Http\Controllers\Portal\PortalPageController;
use V1\Http\Controllers\Portal\PortalUserController;

Route::middleware(['auth:sanctum', 'admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::apiResource('/portals', AdminPortalController::class);
    Route::name('portals.')->prefix('portals/{portal}')->group(function () {
        Route::apiResource('/blogs', AdminBlogController::class);
        Route::apiResource('/cards', CardController::class);
        Route::apiResource('/pages', AdminPortalPageController::class);
        Route::apiResource('/sections', SectionController::class);
        Route::apiResource('/sliders', SlidersController::class);
    });
});

Route::apiResource('/portals', PortalController::class)->only(['index', 'show']);
Route::name('portals.')->prefix('portals/{portal}')->group(function () {
    Route::apiResource('/blogs', BlogController::class)->only(['index', 'show']);
    Route::get('/pages/index', [PortalPageController::class, 'showIndex'])->name('pages.show.index');
    Route::apiResource('/pages', PortalPageController::class)->only(['index', 'show']);
    Route::apiResource('/learning/paths', LearningPath::class, ['as' => 's'])->only(['index', 'show']);

    Route::controller(PortalUserController::class)->group(function () {
        Route::post('/register', 'register')->name('register');
        Route::post('/login', 'login')->name('login');
        Route::get('/user', 'show')->name('user.show')->middleware('auth:sanctum');
        // Route::post('/user', 'update')->name('user.update');
        // Route::post('/user/password', 'updatePassword')->name('user.update.password');
    });
});