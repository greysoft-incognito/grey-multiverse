<?php

namespace App\Http\Controllers\Forms;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveFormdataRequest;
use App\Http\Resources\Forms\FormDataCollection;
use App\Http\Resources\Forms\FormDataResource;
use App\Models\Form;
use App\Models\GenericFormData;
use App\Models\User;
use Illuminate\Http\Request;
use V1\Notifications\FormSubmitedSuccessfully;

class FormDataController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Form $form)
    {
        $forms = $form
            ->data()
            ->where('user_id', auth('sanctum')->id())
            ->paginate($request->get('limit', 30));

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
        $forms = GenericFormData::where('user_id', auth('sanctum')->id())->paginate($request->get('limit', 30));

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

        $resource = $request->hasMultipleEntries()
            ? new FormDataCollection($formdata)
            : new FormDataResource($formdata->first());

        $user = ($uid = $request->input('user_id', $request->user)) ? User::find($uid) : $request->user('sanctum');
        if ($user && $user->reg_status !== 'completed') {
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
     * @return \Illuminate\Http\Response
     */
    public function show(Form $form, string $id)
    {
        $data = $form
            ->data()
            ->where('user_id', auth('sanctum')->id())
            ->find($id);

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
    public function update(SaveFormdataRequest $request, Form $form, $id) {}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Form $form, string $id)
    {
        $form
            ->data()
            ->where('user_id', auth('sanctum')->id())
            ->where('id', $id)
            ->delete();

        return Providers::response()->success([
            'data' => [],
            'message' => __('Form data deleted successfully.'),
        ], HttpStatus::ACCEPTED);
    }
}