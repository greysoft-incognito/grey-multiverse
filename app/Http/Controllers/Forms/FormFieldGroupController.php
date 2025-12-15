<?php

namespace App\Http\Controllers\Forms;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormFieldGroupCollection;
use App\Http\Resources\Forms\FormFieldGroupResource;
use App\Http\Resources\Forms\FormResource;
use App\Models\Form;
use App\Models\FormFieldGroup;
use Illuminate\Http\Request;

class FormFieldGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Form $form)
    {
        $groups = $form->fieldGroups()->paginate($request->get('limit', 30));

        $request->merge(['with' => 'fields']);

        if ($groups->count() < 1) {
            $group = $form->fieldGroups()->make();
            $groups = new \Illuminate\Database\Eloquent\Collection([$group]);
        }

        return (new FormFieldGroupCollection($groups))->additional([
            ...($request->boolean('loadForm') ? ['form' => new FormResource($form)] : []),
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK->value,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Form $form, FormFieldGroup $field_group)
    {
        \Gate::authorize('usable', 'formfield.show');

        return (new FormFieldGroupResource($field_group))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK->value,
        ]);
    }
}
