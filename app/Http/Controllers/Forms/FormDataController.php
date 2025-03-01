<?php

namespace App\Http\Controllers\Forms;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Requests\SaveFormdataRequest;
use App\Http\Resources\Forms\FormDataCollection;
use App\Http\Resources\Forms\FormDataResource;
use App\Models\Form;
use App\Models\FormData;
use App\Models\User;
use App\Notifications\FormSubmitedSuccessfully;
use Illuminate\Http\Request;
use Valorin\Random\Random;

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
        $forms = FormData::where('user_id', auth('sanctum')->id())->paginate($request->get('limit', 30));

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
        $user = null;
        $token = null;

        if ($dontProcess === true) {
            return $data->first();
        }

        if ($request->hasMultipleEntries() || (!$request->user_id && !$request->user('sanctum'))) {
            $formdata = $form->data()->createMany($data);
        } else {
            $entry = $form->data()->updateOrCreate(
                $data->first(),
                ['user_id' => $request->user_id ?? $request->user('sanctum')?->id]
            );

            $formdata = collect([$entry]);
        }

        $formdata->each(fn(FormData $data) => $data->notify(new FormSubmitedSuccessfully()));
        $userData = $formdata->first();

        if ($form->fieldGroups()->where('authenticator', true)->exists() && $userData->email) {
            $authCont = new RegisteredUserController();
            $password = Random::string(8);
            [
                'data' => $user,
                'token' => $token,
            ] = $authCont->store($request->merge([
                'name' => $userData->name,
                'email' => $userData->email,
                'phone' => $userData->phone,
                'password' => $password,
                'password_confirmation' => $password,
                'firstname' => $userData->firstname,
                'lastname' => $userData->lastname,
            ]))->content();
        }

        $resource = $request->hasMultipleEntries()
            ? new FormDataCollection($formdata)
            : new FormDataResource($userData);

        $user ??= ($uid = $request->input('user_id', $request->user)) ? User::find($uid) : $request->user('sanctum');

        if ($user && $user->reg_status !== 'completed') {
            $user->reg_status = 'ongoing';
            $user->saveQuietly();
        }

        return $resource->additional([
            'message' => __('You data has been submitted successfully.'),
            'status' => 'success',
            'token' => $token,
            'statusCode' => HttpStatus::CREATED,
            'user' => $user,
        ])->response()->setStatusCode(HttpStatus::CREATED->value);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Form $form, string $id)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $form->data()->where('user_id', auth('sanctum')->id());

        $data = $id === 'current'
            ? $query->latest()->firstOrNew()
            : $query->find($id);

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
