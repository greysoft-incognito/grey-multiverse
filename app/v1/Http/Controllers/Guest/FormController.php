<?php

namespace V1\Http\Controllers\Guest;

use App\Models\Form;
use Illuminate\Http\Request;
use V1\Http\Controllers\Controller;
use V1\Http\Resources\FormCollection;
use V1\Http\Resources\FormResource;
use V1\Services\HttpStatus;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $forms = Form::paginate($request->get('limit', 30));

        return (new FormCollection($forms))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $form = Form::whereId($id)->orWhere('slug', $id)->firstOrFail();

        return (new FormResource($form))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }
}
