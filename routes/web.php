<?php

use App\Enums\HttpStatus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use V1\Services\AppInfo;
use App\Models\Form;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

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

Route::get('download/formdata/{timestamp}/{form}/{batch?}', function ($timestamp, $data, $batch = null) {
    // Auth::logout();
    $setTime = Carbon::createFromTimestamp($timestamp);
    if ($setTime->diffInSeconds(now()) > 36000) {
        abort(HttpStatus::BAD_REQUEST, 'Link expired');
    }

    $id = str(base64url_decode($data))->explode('/')->last();
    $form = Form::findOrFail($id);
    $storage = Storage::disk('protected');

    $path = 'exports/' . $form->id . '/data-batch' . $batch . '.xlsx';

    if ($storage->exists($path)) {
        $mime = $storage->mimeType($path);

        // create response and add encoded image data
        return Response::download($storage->path($path), $form->name . '-' . $setTime->format('Y-m-d H_i_s') . '.xlsx', [
            'Content-Type' => $mime,
            'Cross-Origin-Resource-Policy' => 'cross-origin',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
})->middleware('auth.basic')->name('download.formdata');

Route::middleware('web')->group(base_path('routes/routes-v1/web.php'));