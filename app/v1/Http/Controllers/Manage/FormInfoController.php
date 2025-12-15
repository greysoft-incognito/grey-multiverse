<?php

namespace V1\Http\Controllers\Manage;

use App\Models\Form;
use App\Models\FormInfo;
use Illuminate\Http\Request;
use V1\Http\Controllers\Controller;
use V1\Http\Resources\FormInfoCollection;
use V1\Http\Resources\FormInfoResource;
use V1\Services\HttpStatus;

class FormInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        \Gate::authorize('usable', 'form.list');
        $info = FormInfo::get();

        return (new FormInfoCollection($info))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Form $form)
    {
        \Gate::authorize('usable', 'form.list');
        $request->validate([
            'priority' => 'nullable|numeric|min:1|max:5',
            'title' => 'required|string|min:3|max:55',
            'subtitle' => 'nullable|string|min:3|max:55',
            'content' => 'required_without:list|string|min:15',
            'list' => 'nullable|array',
            'icon' => 'nullable|string',
            'icon_color' => 'nullable|string',
            'increment_icon' => 'nullable|boolean',
            'position' => 'required|string|in:left,right',
            'type' => 'required|string|in:text,list,cta,video,image',
            'template' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,png|max:1524',
        ]);

        $info = new FormInfo;
        $info->form_id = $form->id;
        $info->priority = $request->priority;
        $info->title = $request->title;
        $info->subtitle = $request->subtitle;
        $info->content = $request->content;
        $info->list = $request->list;
        $info->image = $request->image;
        $info->icon = $request->icon;
        $info->icon_color = $request->icon_color;
        $info->increment_icon = $request->increment_icon;
        $info->template = $request->template;
        $info->type = $request->type;
        $info->position = $request->position;
        $info->save();

        return (new FormInfoResource($info))->additional([
            'message' => __('Form info added successfully.'),
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
    public function show(Form $form, FormInfo $info)
    {
        \Gate::authorize('usable', 'form.show');

        return (new FormInfoResource($info))->additional([
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
    public function update(Request $request, Form $form, $id)
    {
        \Gate::authorize('usable', 'form.list');
        $request->validate([
            'priority' => 'nullable|numeric|min:1|max:5',
            'title' => 'required|string|min:3|max:55',
            'subtitle' => 'nullable|string|min:3|max:55',
            'content' => 'required_without:list|string|min:15',
            'list' => 'nullable|array',
            'icon' => 'nullable|string',
            'icon_color' => 'nullable|string',
            'increment_icon' => 'nullable|boolean',
            'position' => 'required|string|in:left,right',
            'type' => 'required|string|in:text,list,cta,video,image',
            'template' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,png|max:1524',
        ]);

        $info = $form->infos()->findOrFail($id);
        $info->priority = $request->priority;
        $info->title = $request->title;
        $info->subtitle = $request->subtitle;
        $info->content = $request->content;
        $info->list = $request->list;
        $info->image = $request->image;
        $info->icon = $request->icon;
        $info->icon_color = $request->icon_color;
        $info->increment_icon = $request->increment_icon;
        $info->template = $request->template;
        $info->type = $request->type;
        $info->position = $request->position;
        $info->save();

        return (new FormInfoResource($info))->additional([
            'message' => __("{$info->title} has been updated successfully."),
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
    public function destroy(Request $request, Form $form, $id)
    {
        \Gate::authorize('usable', 'form.delete');
        if ($request->items) {
            $count = collect($request->items)->map(function ($form, $item) {
                $form = $form->infos()->whereId($item)->first();
                if ($form) {
                    return $form->delete();
                }

                return false;
            })->filter(fn ($i) => $i !== false)->count();

            return $this->buildResponse([
                'message' => "{$count} form infos have been deleted.",
                'status' => 'success',
                'status_code' => HttpStatus::OK,
            ]);
        } else {
            $form = $form->infos()->whereId($id)->first();
        }

        if ($form) {
            $form->delete();

            return $this->buildResponse([
                'message' => "{$form->title} has been deleted.",
                'status' => 'success',
                'status_code' => HttpStatus::ACCEPTED,
            ]);
        }

        return $this->buildResponse([
            'message' => 'The requested form info no longer exists.',
            'status' => 'error',
            'status_code' => HttpStatus::NOT_FOUND,
        ]);
    }
}
