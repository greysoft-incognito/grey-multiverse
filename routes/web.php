<?php

use App\Enums\HttpStatus;
use App\Models\Form;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return [
        'api' => config('app.name'),
        'hello' => 'Star Gazer',
        'welcome' => 'Welcome to the GreyMultiverse API v2!',
        'lastUpdated' => File::exists(base_path('.updated'))
            ? new \Carbon\Carbon(File::lastModified(base_path('.updated')))
            : now(),
    ];
});

// Route::middleware('web')->get('install', function () {
//     if (file_exists(database_path('dump.sql'))) {
//         \Illuminate\Support\Facades\DB::unprepared(file_get_contents(database_path('dump.sql')));
//     } else {
//         Artisan::call('migrate:fresh');
//         dump(\Artisan::output());
//     }

//     \Artisan::call('optimize:clear');
//     dump(\Artisan::output());
// })->name('install');

Route::middleware('web')->get('refresh', function () {
    Artisan::call('migrate');
    dump(\Artisan::output());
    \Artisan::call('app:sync-roles');
    dump(\Artisan::output());
    \Artisan::call('optimize:clear');
    dump(\Artisan::output());
})->name('install');

Route::get('download/formdata/{timestamp}/{form}/{batch?}', function ($timestamp, $data, $batch = null) {
    $setTime = Carbon::createFromTimestamp($timestamp);
    if ($setTime->diffInSeconds(now()) > 36000) {
        abort(HttpStatus::BAD_REQUEST->value, 'Link expired');
    }

    $storage = Storage::disk('protected');

    // public Form|Company|Appointment|User $form,
    $id = str(base64url_decode($data))->explode('/')->last();

    /** @var Form|\App\Models\User|\App\Models\BizMatch\Company|\App\Models\BizMatch\Appointment $class */
    $model = app(str(str(base64url_decode($data))->explode('/')->first())->replace('.', '\\')->toString());

    if ($model instanceof Form) {
        $form = $model->findOrFail($id);
        $groupName = 'forms-'.$form->id;
    } else {
        $groupName = str(get_class($model))->afterLast('\\')->lower()->plural()->append('-dataset')->toString();
    }

    $path = 'exports/'.$groupName.'/data-batch-'.$batch.'.xlsx';

    if ($storage->exists($path)) {
        $mime = $storage->mimeType($path);

        // create response and add encoded image data
        return Response::download($storage->path($path), $groupName.'-'.$setTime->format('Y-m-d H_i_s').'.xlsx', [
            'Content-Type' => $mime,
            'Cross-Origin-Resource-Policy' => 'cross-origin',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
    abort(HttpStatus::NOT_FOUND->value, 'Link Does Not Exist');
})->middleware('auth.basic')->name('download.formdata');

Route::middleware('web')->group(base_path('routes/routes-v1/web.php'));
