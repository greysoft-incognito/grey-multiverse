<?php

use App\Models\Form;
use App\Models\GenericFormData;
use App\Models\Reservation;
use App\Services\HttpStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/{type}/data/qr/{id}', function ($type, $id) {
    // header('Content-Type: image/png');
    if ($type === 'form') {
        $data = GenericFormData::findOrFail($id);
        $encoded = "grey:multiverse:form={$data->form_id}:data={$data->id}";
    } elseif ($type === 'reservation') {
        $data = Reservation::findOrFail($id);
        $encoded = "grey:multiverse:reservation={$data->id}:space={$data->space_id}";
    } else {
        abort(HttpStatus::BAD_REQUEST, 'Invalid type');
    }

    $qr = QrCode::eyeColor(2, 141, 53, 74, 125, 115, 118)
        ->format('png')
        ->size(400)
        // ->encoding('UTF-8')
        ->eye('circle')
        ->style('dot', 0.99)
        // ->errorCorrection('M')
        ->eyeColor(1, 125, 115, 118, 141, 53, 74)
        ->generate($encoded);
    Response::make($qr)->header('Content-Type', 'image/png')->send();
})->name('form.data.qr');

Route::get('download/formdata/{timestamp}/{form}/{batch?}', function ($timestamp, $data, $batch = null) {
    // Auth::logout();
    $setTime = Carbon::createFromTimestamp($timestamp);
    if ($setTime->diffInSeconds(now()) > 36000) {
        abort(HttpStatus::BAD_REQUEST, 'Link expired');
    }

    $id = str(base64url_decode($data))->explode('/')->last();
    $form = Form::findOrFail($id);
    $storage = Storage::disk('protected');

    $path = 'exports/'.$form->id.'/data-batch'.$batch.'.xlsx';

    if ($storage->exists($path)) {
        $mime = $storage->mimeType($path);

        // create response and add encoded image data
        return Response::download($storage->path($path), $form->name.'-'.$setTime->format('Y-m-d H_i_s').'.xlsx', [
            'Content-Type' => $mime,
            'Cross-Origin-Resource-Policy' => 'cross-origin',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
})->middleware('auth.basic')->name('download.formdata');
