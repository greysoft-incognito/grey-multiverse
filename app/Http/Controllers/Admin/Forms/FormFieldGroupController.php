<?php

namespace App\Http\Controllers\Admin\Forms;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
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
        \Gate::authorize('usable', 'formfield.list');
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Form $form)
    {
        \Gate::authorize('usable', 'formfield.create');

        $valid = $this->validate($request, [
            'name' => 'required|string',
            'icon' => 'nullable|string',
            'priority' => 'nullable|integer',
            'description' => 'nullable|string|min:3',
            'authenticator' => 'nullable|boolean',
        ]);

        if ($request->boolean('authenticator')) {
            FormFieldGroup::whereAuthenticator(true)->update(['authenticator' => false]);
        }

        $group = $form->fieldGroups()->create($valid);

        return (new FormFieldGroupResource($group))->additional([
            'message' => __("Field Group created successfully"),
            'status' => 'success',
            'statusCode' => HttpStatus::CREATED,
        ])->response()->setStatusCode(HttpStatus::CREATED->value);
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Form $form, FormFieldGroup $field_group)
    {
        \Gate::authorize('usable', ['formfield.update']);

        $valid = $this->validate($request, [
            'name' => 'required|string',
            'icon' => 'nullable|string',
            'priority' => 'nullable|integer',
            'description' => 'nullable|string|min:3',
            'authenticator' => 'nullable|boolean',
        ]);

        if ($request->boolean('authenticator')) {
            FormFieldGroup::whereAuthenticator(true)->update(['authenticator' => false]);
        }

        $field_group->update($valid);

        return (new FormFieldGroupResource($field_group))->additional([
            'message' => __("Field Group updated successfully"),
            'status' => 'success',
            'statusCode' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Update the specified resource in storage.
     */
    public function sync(Request $request, Form $form, FormFieldGroup $field_group)
    {
        \Gate::authorize('usable', ['formfield.update']);

        ['field_ids' => $field_ids] = $this->validate($request, [
            'field_ids' => 'required|array',
            'field_ids.*' => 'required|exists:form_fields,id',
        ]);

        $field_group->fields()->sync($field_ids);

        return (new FormFieldGroupResource($field_group))->additional([
            'message' => __("Field Group has successfully been synced with selected fields"),
            'status' => 'success',
            'statusCode' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Form $form, string $id)
    {
        @['items' => $items] = $this->validate($request, [
            'items' => ['nullable', 'array'],
            'items.*' => ['required', 'numeric'],
        ]);

        $count = count($items ?? []) ? count($items) : 1;
        $form->fieldGroups()->whereIn('id', count($items ?? []) ? $items : [$id])->delete();

        return Providers::response()->success([
            'data' => [],
            'message' => "{$count} form feilds have been deleted.",
        ], HttpStatus::ACCEPTED);
    }
}
