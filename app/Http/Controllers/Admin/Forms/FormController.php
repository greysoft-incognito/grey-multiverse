<?php

namespace App\Http\Controllers\Admin\Forms;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormCollection;
use App\Http\Resources\Forms\FormResource;
use App\Models\Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user('sanctum');

        \Gate::authorize('usable', 'form.list');

        $query = Form::query();

        // Search and filter columns
        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('title', 'like', "%$request->search%");
                $query->orWhereFullText('banner_info', $request->search);
                $query->orWhereHas('infos', function (Builder $query) use ($request) {
                    $query->where('title', 'like', "%$request->search%");
                    $query->orWhereFullText('content', $request->search);
                });
            });
        }

        if ($user->hasExactRoles(['reviewer'])) {
            $query->forReviewer($user);
        }

        // Reorder Columns
        if ($request->order && is_array($request->order)) {
            foreach ($request->order as $key => $dir) {
                if ($dir === 'desc') {
                    $query->orderByDesc($key ?? 'id');
                } else {
                    $query->orderBy($key ?? 'id');
                }
            }
        }
        $forms = $query->paginate($request->get('limit', 30));

        return (new FormCollection($forms))->additional(array_merge([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK->value,
        ], $request->search ? ['total_results' => $query->count()] : []));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \Gate::authorize('usable', 'form.create');
        $request->validate([
            'name' => 'required|string|min:3|max:55',
            'title' => 'required|string|min:3|max:55',
            'external_link' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpg,png|max:1524',
            'banner' => 'nullable|image|mimes:jpg,png|max:1524',
            'banner_title' => 'nullable|string',
            'banner_info' => 'nullable|string',
            'socials' => 'nullable|array',
            'deadline' => 'nullable|string',
            'template' => 'nullable',
            'success_message' => 'nullable|string',
            'approval_message' => 'nullable|string',
            'rejection_message' => 'nullable|string',
            'failure_message' => 'nullable|string',
            'dont_notify' => 'nullable|boolean',
            'require_auth' => 'nullable|boolean',
            'data_emails' => 'nullable|array',
            'data_emails.*' => 'required|email',
        ]);

        $form = new Form();
        $form->name = $request->name;
        $form->title = $request->title;
        $form->external_link = $request->external_link;
        $form->logo = $request->logo;
        $form->banner = $request->banner;
        $form->banner_title = $request->banner_title;
        $form->banner_info = $request->banner_info;
        $form->socials = $request->socials;
        $form->deadline = $request->deadline;
        $form->template = $request->template;
        $form->success_message = $request->success_message;
        $form->approval_message = $request->approval_message;
        $form->rejection_message = $request->rejection_message;
        $form->failure_message = $request->failure_message;
        $form->dont_notify = $request->boolean('dont_notify');
        $form->require_auth = $request->boolean('require_auth');
        $form->data_emails = Arr::join($request->data_emails ?? [], ',');
        $form->save();

        return (new FormResource($form))->additional([
            'message' => __('Your form has been created successfully.'),
            'status' => 'success',
            'statusCode' => HttpStatus::OK->value,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Form $form)
    {
        \Gate::authorize('usable', 'form.show');

        return (new FormResource($form))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK->value,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Form $form)
    {
        \Gate::authorize('usable', 'form.update');

        $request->validate([
            'name' => 'required|string|min:3|max:55',
            'title' => 'required|string|min:3|max:55',
            'external_link' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpg,png',
            'banner' => 'nullable|image|mimes:jpg,png',
            'banner_title' => 'nullable|string',
            'banner_info' => 'nullable|string',
            'socials' => 'nullable|array',
            'deadline' => 'nullable|string',
            'template' => 'nullable',
            'success_message' => 'nullable|string',
            'approval_message' => 'nullable|string',
            'rejection_message' => 'nullable|string',
            'failure_message' => 'nullable|string',
            'dont_notify' => 'nullable|boolean',
            'require_auth' => 'nullable|boolean',
            'data_emails' => 'nullable|array',
            'data_emails.*' => 'required|email',
        ]);

        $form->name = $request->name;
        $form->title = $request->title;
        $form->external_link = $request->external_link;
        $form->logo = $request->logo;
        $form->banner = $request->banner;
        $form->banner_title = $request->banner_title;
        $form->banner_info = $request->banner_info;
        $form->socials = $request->socials;
        $form->deadline = $request->deadline;
        $form->template = $request->template;
        $form->success_message = $request->success_message;
        $form->approval_message = $request->approval_message;
        $form->rejection_message = $request->rejection_message;
        $form->failure_message = $request->failure_message;
        $form->dont_notify = $request->boolean('dont_notify');
        $form->require_auth = $request->boolean('require_auth');
        $form->data_emails = Arr::join($request->data_emails ?? $form->data_emails->toArray(), ',');
        $form->save();

        return (new FormResource($form))->additional([
            'message' => __("{$form->name} has been updated successfully."),
            'status' => 'success',
            'statusCode' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        \Gate::authorize('usable', 'form.delete');
        if ($request->items) {
            $count = collect($request->items)->map(function ($item) {
                $form = Form::whereId($item)->first();
                if ($form) {
                    return $form->delete();
                }

                return false;
            })->filter(fn($i) => $i !== false)->count();

            return Providers::response()->info([
                'data' => [],
                'message' => "{$count} forms have been deleted.",
            ], HttpStatus::ACCEPTED);
        } else {
            $form = Form::findOrFail($id);
        }

        if ($form) {
            $form->delete();

            return Providers::response()->info([
                'data' => [],
                'message' => "{$form->name} has been deleted.",
            ], HttpStatus::ACCEPTED);
        }
    }
}
