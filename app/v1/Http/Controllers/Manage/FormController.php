<?php

namespace V1\Http\Controllers\Manage;

use App\Models\Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use V1\Http\Controllers\Controller;
use V1\Http\Resources\FormCollection;
use V1\Http\Resources\FormResource;
use V1\Services\HttpStatus;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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
            'status_code' => HttpStatus::OK,
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
            'dont_notify' => 'nullable|boolean',
            'data_emails' => 'nullable|string',
        ]);

        $form = new Form;
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
        $form->dont_notify = $request->dont_notify ?? false;
        $form->data_emails = $request->data_emails;
        $form->save();

        return (new FormResource($form))->additional([
            'message' => __('Your form has been created successfully.'),
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
        \Gate::authorize('usable', 'form.show');
        $form = Form::whereId($id)->orWhere('slug', $id)->firstOrFail();

        return (new FormResource($form))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
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
        \Gate::authorize('usable', 'form.update');
        $form = Form::findOrFail($id);

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
            'dont_notify' => 'nullable|boolean',
            'data_emails' => 'nullable|string',
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
        $form->dont_notify = $request->dont_notify ?? false;
        $form->data_emails = $request->data_emails ?? Arr::join($form->data_emails, ',');
        $form->save();

        return (new FormResource($form))->additional([
            'message' => __("{$form->name} has been updated successfully."),
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED);
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
            })->filter(fn ($i) => $i !== false)->count();

            return $this->buildResponse([
                'message' => "{$count} forms have been deleted.",
                'status' => 'success',
                'status_code' => HttpStatus::OK,
            ]);
        } else {
            $form = Form::whereId($id)->first();
        }

        if ($form) {
            $form->delete();

            return $this->buildResponse([
                'message' => "{$form->name} has been deleted.",
                'status' => 'success',
                'status_code' => HttpStatus::ACCEPTED,
            ]);
        }

        return $this->buildResponse([
            'message' => 'The requested form no longer exists.',
            'status' => 'error',
            'status_code' => HttpStatus::NOT_FOUND,
        ]);
    }
}
