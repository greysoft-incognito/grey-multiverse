<?php

namespace V1\Http\Controllers\Guest;

use App\Models\Form;
use App\Models\GenericFormField;
use Illuminate\Http\Request;
use V1\Http\Controllers\Controller;
use V1\Http\Resources\FormFieldCollection;
use V1\Http\Resources\FormFieldResource;
use V1\Services\HttpStatus;

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
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function form(Request $request, $id)
    {
        $fields = Form::find($id)->fields()->paginate($request->get('limit', 30));

        return (new FormFieldCollection($fields))->additional([
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
        $field = GenericFormField::find($id);

        return (new FormFieldResource($field))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }
}
