<?php

namespace App\Http\Controllers\Forms;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormCollection;
use App\Http\Resources\Forms\FormResource;
use App\Models\Form;
use Illuminate\Http\Request;

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
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Form $form)
    {
        return (new FormResource($form))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ]);
    }
}
