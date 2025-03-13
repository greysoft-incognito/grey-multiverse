<?php

namespace App\Http\Controllers\Admin\Forms;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Forms\FormDataController as GuestFormDataController;
use App\Http\Resources\Forms\FormDataCollection;
use App\Http\Resources\Forms\FormDataResource;
use App\Http\Resources\SortFieldResource;
use App\Models\Form;
use App\Models\FormData;
use App\Notifications\FormReviewComplete;
use Illuminate\Http\Request;

class FormDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Form $form)
    {
        $request->merge([
            'sortable' => $request->boolean('sortable'),
            'only_drafts' => $request->boolean('only_drafts'),
            'load_drafts' => $request->boolean('load_drafts'),
        ]);

        @[
            'rank' => $rank,
            'search' => $search,
            'status' => $status,
            'sortable' => $sortable,
            'sort_field' => $sort_field,
            'sort_value' => $sort_value,
            'only_drafts' => $only_drafts,
            'load_drafts' => $load_drafts,
        ] = $this->validate($request, [
            'rank' => ['nullable', 'in:top,least'],
            'status' => ['nullable', 'in:approved,pending,rejected,submitted'],
            'search' => ['nullable', 'string'],
            'sortable' => ['nullable', 'boolean'],
            'sort_field' => ['nullable', 'string', 'exists:form_fields,name'],
            'sort_value' => ['nullable', 'string'],
            'only_drafts' => ['nullable', 'boolean'],
            'load_drafts' => ['nullable', 'boolean'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user('sanctum');
        $name_field = $form->config['fields_map']['name'] ?? 'name';

        \Gate::authorize('usable', 'formdata.list');

        $query = $form->data();
        $query->where("data->{$name_field}", '!=', null);

        $query
            ->when($rank, fn($q) => $q->ranked($rank), fn($q) => $q->orderBy('rank', 'DESC'))
            ->when($status, fn($q) => $q->whereStatus($status))
            ->when($search, fn($q) => $q->doSearch($search, $form))
            ->when($only_drafts, fn($q) => $q->drafts())
            ->when($load_drafts, fn($q) => $q->withDraft())
            ->when($sort_field && $sort_value, fn($q) => $q->sorted($sort_field, $sort_value))
            ->when($user->hasExactRoles(['reviewer']), fn($q) => $q->forReviewer($user));

        $data = $query->paginate($request->get('limit', 30))->withQueryString();

        return (new FormDataCollection($data))->additional([
            'form' => [
                'id' => $form->id,
                'name' => $form->name,
                'title' => $form->title,
                'slug' => $form->slug,
            ],
            ...($sortable ? ['sortFields' => SortFieldResource::collection($form->sortFields)] : []),
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK->value,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user('sanctum');

        \Gate::authorize('usable', 'formdata.list');

        $query = FormData::query();

        if ($user->hasExactRoles(['reviewer'])) {
            $query->forReviewer($user);
        }

        $forms = $query->paginate($request->get('limit', 30))->withQueryString();

        return (new FormDataCollection($forms))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK->value,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Form $form)
    {
        \Gate::authorize('usable', 'formdata.create');

        return (new GuestFormDataController)->store($request, $form);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Form $form, FormData $data)
    {
        \Gate::authorize('usable', 'formdata.show');

        return (new FormDataResource($data))->additional([
            'form' => [
                'id' => $form->id,
                'name' => $data->form->name,
                'title' => $data->form->title,
                'slug' => $data->form->slug,
            ],
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK->value,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function decodeQr(Request $request)
    {
        \Gate::authorize('usable', 'formdata.show');
        // Use regex to extract the form_id and data_id parts of the following string 'grey:multiverse:form=1:data=23'
        $this->validate($request, [
            'qr' => 'required|regex:/^grey:multiverse:form=(\d+):data=(\d+)$/',
        ], [
            'qr.regex' => 'The QR code is invalid.',
        ]);

        // Decode a regex string into an array
        preg_match('/^grey:multiverse:form=(\d+):data=(\d+)$/', $request->qr, $matches);
        $form_id = $matches[1];
        $form_data_id = $matches[2];
        $qr_code = $matches[0];

        $data = FormData::whereId($form_data_id)->firstOrFail();
        // save this scan to the history
        $data->scans()->create([
            'user_id' => auth()->user()->id,
            'qrcode' => $qr_code,
            'form_id' => $form_id,
        ]);

        $data->scan_date = $data->scans()->latest()->first()->created_at;
        $data->save();

        return (new FormDataResource($data))->additional([
            'form' => [
                'id' => $form_id,
                'name' => $data->form->name,
                'title' => $data->form->title,
                'slug' => $data->form->slug,
            ],
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK->value,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Form $form, FormData $data)
    {
        \Gate::authorize('usable', 'formdata.update');

        [
            'status' => $status,
            'reason' => $reason
        ] = $this->validate($request, [
            'status' => 'required|in:pending,submitted,approved,rejected',
            'reason' => 'required|string|min:15'
        ]);

        $data->status = $status;
        $data->reviewer_id = $request->user('sanctum')?->id;
        $data->status_reason = $reason;
        $data->save();

        if (in_array($data->status, ['approved', 'rejected'])) {
            $data->notify(new FormReviewComplete());
        }

        return (new FormDataResource($data))->additional([
            'message' => __('Submission status has successfully been changed to ":0"', [$status]),
            'status' => 'success',
            'statusCode' => HttpStatus::ACCEPTED->value,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Form $form, FormData $data)
    {
        \Gate::authorize('usable', 'formdata.delete');

        $data->delete();

        return Providers::response()->success([
            'data' => [],
            'message' => __('Form data deleted successfully.'),
        ], HttpStatus::ACCEPTED);
    }
}
