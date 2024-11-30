<?php

namespace App\Http\Controllers\Forms;

use App\Models\Form;
use App\Models\GenericFormData;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormDataCollection;
use App\Http\Resources\Forms\FormDataResource;
use V1\Notifications\FormSubmitedSuccessfully;
use App\Enums\HttpStatus;
use App\Http\Requests\SaveFormdataRequest;

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
        $key = $form->fields->firstWhere('key', true)->name ?? $form->fields->first()->name;
        $data = $request->get('data');

        if (! $data) {
            throw ValidationException::withMessages(['data' => 'No data passed']);
        }

        if ($dontProcess === true) {
            return $data;
        }

        $formdata = $form->data()->create([
            'user_id' => $request->user_id ?? null,
            'data' => $data,
            'key' => $data[$key] ?? '',
        ]);

        $formdata->notify(new FormSubmitedSuccessfully());

        return (new FormDataResource($formdata))->additional([
            'message' => HttpStatus::message(HttpStatus::CREATED),
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
