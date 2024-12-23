<?php

namespace App\Http\Controllers\Forms;

use App\Models\Form;
use App\Models\GenericFormData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormDataCollection;
use App\Http\Resources\Forms\FormDataResource;
use V1\Notifications\FormSubmitedSuccessfully;
use App\Enums\HttpStatus;
use App\Http\Requests\SaveFormdataRequest;
use App\Models\User;

class FormDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Form $form)
    {
        $forms = $form->data()->paginate($request->get('limit', 30));

        return (new FormDataCollection($forms))->additional([
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
    public function all(Request $request)
    {
        $forms = GenericFormData::paginate($request->get('limit', 30));

        return (new FormDataCollection($forms))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SaveFormdataRequest $request, Form $form, $dontProcess = false)
    {
        $data = $request->input('data');

        if ($dontProcess === true) {
            return $data->first();
        }

        $formdata = $form->data()->createMany($data);
        $formdata->each(fn(GenericFormData $data) => $data->notify(new FormSubmitedSuccessfully()));

        $resource = $request->getMult() === '*.'
            ? new FormDataCollection($formdata)
            : new FormDataResource($formdata->first());

        $user = ($uid = $request->input('user_id', $request->user)) ? User::find($uid) : $request->user('sanctum');
        if ($user) {
            $user->reg_status = 'ongoing';
            $user->saveQuietly();
        }

        return $resource->additional([
            'message' => __('You data has been submitted successfully.'),
            'status' => 'success',
            'statusCode' => HttpStatus::CREATED,
        ])->response()->setStatusCode(HttpStatus::CREATED->value);
    }

    /**
     * Display the specified resource.
     *
     * @param  GenericFormData $data
     * @return \Illuminate\Http\Response
     */
    public function show(GenericFormData $data)
    {
        return (new FormDataResource($data))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        \Gate::authorize('usable', 'formdata.update');
        //
    }
}