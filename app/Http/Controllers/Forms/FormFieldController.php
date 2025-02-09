<?php

namespace App\Http\Controllers\Forms;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormFieldCollection;
use App\Http\Resources\Forms\FormFieldResource;
use App\Models\Form;
use App\Models\GenericFormField;
use Illuminate\Http\Request;

class FormFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $fields = GenericFormField::paginate($request->get('limit', 30));

        return (new FormFieldCollection($fields))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function scoped(Request $request, Form $form)
    {
        $fields = $form->fields()->paginate($request->get('limit', 30));

        return (new FormFieldCollection($fields))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(GenericFormField $field)
    {
        return (new FormFieldResource($field))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ]);
    }
}
