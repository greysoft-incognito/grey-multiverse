<?php

use App\Models\FormData;
use App\Models\Reservation;
use App\Services\HttpStatus;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

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
        $data = FormData::withDraft()->findOrFail($id);
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
