<?php

namespace App\Http\Controllers\Admin\Forms;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormFieldCollection;
use App\Http\Resources\Forms\FormFieldResource;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FormFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Form $form)
    {
        \Gate::authorize('usable', 'formfield.list');
        $fields = $form->fields()->paginate($request->get('limit', 30));

        return (new FormFieldCollection($fields))->additional([
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
        \Gate::authorize('usable', 'formfield.list');
        $fields = FormField::paginate($request->get('limit', 30));

        return (new FormFieldCollection($fields))->additional([
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
        \Gate::authorize('usable', 'formfield.create');
        $request->validate([
            'name' => 'required|string',
            'label' => 'required|string',
            'value' => 'nullable|string',
            'hint' => 'nullable|string|min:3',
            'custom_error' => 'nullable|string|min:3',
            'compare' => 'nullable|date',
            'options' => 'required_if:element,select|nullable|array',
            'required' => 'nullable|boolean',
            'required_if' => 'nullable|string',
            'restricted' => 'nullable|boolean',
            'expected_value' => 'nullable|string',
            'priority' => 'numeric|nullable',
            'key' => 'nullable|string',
            'min' => ['numeric', Rule::requiredIf($request->compare && $request->type === 'date' && ! $request->max)],
            'max' => ['numeric', Rule::requiredIf($request->compare && $request->type === 'date' && ! $request->min)],
            'points' => ['required', 'numeric'],
            'element' => 'required|string|in:input,textarea,select,locale,checkboxgroup,radiogroup',
            'type' => 'required|string|in:hidden,text,number,email,password,date,time,datetime-local,file,tel,url,checkbox,radio,country,state,lga,city',
        ], [
            'min.required' => 'the Min field is required if Compare is set and Type equals date while Max is missing',
            'max.required' => 'the Max field is required if Compare is set and Type equals date while Min is missing',
        ]);

        $field = $form->fields()->make();
        $field->name = $request->name;
        $field->field_id = $request->name;
        $field->label = $request->label;
        $field->value = $request->value;
        $field->hint = $request->hint;
        $field->custom_error = $request->custom_error;
        $field->compare = $request->compare;
        $field->options = $request->options;
        $field->priority = $request->integer('priority');
        $field->required = $request->boolean('required');
        $field->required_if = $request->required_if;
        $field->restricted = $request->boolean('restricted');
        $field->expected_value = $request->expected_value;
        $field->key = $request->key;
        $field->min = $request->min;
        $field->max = $request->max;
        $field->points = $request->points;
        $field->element = $request->element;
        $field->type = $request->type;
        $field->save();

        return (new FormFieldResource($field))->additional([
            'message' => __('Your form field has been created successfully.'),
            'status' => 'success',
            'statusCode' => HttpStatus::CREATED,
        ])->response()->setStatusCode(HttpStatus::CREATED->value);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Form $form, $id)
    {
        \Gate::authorize('usable', 'formfield.show');
        $field = $form->fields()->findOrFail($id);

        return (new FormFieldResource($field))->additional([
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
    public function multiple(Request $request, Form $form)
    {
        \Gate::authorize('usable', ['formfield.update', 'formfield.create']);

        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.label' => 'required|string',
            'data.*.value' => 'nullable|string',
            'data.*.hint' => 'nullable|string|min:3',
            'data.*.custom_error' => 'nullable|string|min:3',
            'data.*.compare' => 'nullable|date',
            'data.*.options' => 'required_if:element,select|nullable|array',
            'data.*.required' => 'nullable|boolean',
            'data.*.required_if' => 'nullable|string',
            'data.*.restricted' => 'nullable|boolean',
            'data.*.expected_value' => 'nullable|string',
            'data.*.key' => 'alpha_num',
            'data.*.min' => 'numeric',
            'data.*.max' => 'numeric',
            'data.*.points' => ['nullable', 'numeric'],
            'data.*.priority' => 'numeric|nullable',
            'data.*.element' => 'required|string|in:input,textarea,select,locale,checkboxgroup,radiogroup',
            'data.*.type' => 'required|string|in:hidden,text,number,email,password,date,time,datetime-local,file,tel,url,checkbox,radio,country,state,lga,city',
        ], [
            'data.*.min.required' => '[FIELD #:index] The Min field is required if Compare is set and Type equals date while Max is missing',
            'data.*.max.required' => '[FIELD #:index] The Max field is required if Compare is set and Type equals date while Min is missing',
        ], [
            'data.*.name' => '#:index Name',
            'data.*.label' => '#:index Label',
            'data.*.value' => '#:index Value',
            'data.*.hint' => '#:index Hint',
            'data.*.custom_error' => '#:index Custom Error',
            'data.*.compare' => '#:index Compare',
            'data.*.options' => '#:index Options',
            'data.*.required' => '#:index Required',
            'data.*.required_if' => '#:index Required If',
            'data.*.restricted' => '#:index Restricted',
            'data.*.key' => '#:index Key',
            'data.*.min' => '#:index Min',
            'data.*.max' => '#:index Max',
            'data.*.points' => '#:index Points',
            'data.*.priority' => '#:index Priority',
            'data.*.element' => '#:index Element',
            'data.*.type' => '#:index Type',
        ]);

        $validator->sometimes(['data.*.min', 'data.*.max'], 'required', function ($input, $item) {
            return $item->compare && $item->type === 'date' && ! $item->min;
        });

        $validator->validate();

        $count = count($request->data);

        $fields = collect($request->data)->map(function ($data, $i) use ($form, $count) {
            $field = $form->fields()->where('id', $data['id'] ?? null)->firstOrNew();
            $field->name = $data['name'] ?? $field->name ?? null;
            $field->field_id = $data['name'] ?? $field->field_id ?? null;
            $field->label = $data['label'] ?? $field->label ?? null;
            $field->value = $data['value'] ?? $field->value ?? null;
            $field->hint = $data['hint'] ?? $field->hint ?? null;
            $field->custom_error = $data['custom_error'] ?? $field->custom_error ?? null;
            $field->compare = $data['compare'] ?? $field->compare ?? null;
            $field->options = $data['options'] ?? $field->options ?? null;
            $field->required = $data['required'] ?? $field->required ?? null;
            $field->required_if = $data['required_if'] ?? $field->required_if ?? null;
            $field->restricted = $data['restricted'] ?? $field->restricted ?? null;
            $field->expected_value = $data['expected_value'] ?? $field->expected_value ?? null;
            $field->key = $data['key'] ?? $field->key ?? null;
            $field->min = $data['min'] ?? $field->min ?? null;
            $field->max = $data['max'] ?? $field->max ?? null;
            $field->points = $data['points'] ?? $field->points ?? 0;
            $field->priority = (int)$count - $i;
            $field->element = $data['element'] ?? $field->element ?? null;
            $field->type = $data['type'] ?? $field->type ?? null;
            $field->save();
            $field->updated = (bool) ($data['id'] ?? null);

            return $field;
        });

        $count_id = $fields->filter(fn($f) => $f['updated'])->count();
        $count_no_id = $fields->filter(fn($f) => ! $f['updated'])->count();
        $msg = str('Form updated successfully')
            ->when($count_id, fn($str) => $str->append(', :0 field(s) updated'))
            ->when($count_no_id, fn($str) => $str->append(', :1 new field(s) created'));

        return (new FormFieldCollection($fields))->additional([
            'message' => __($msg->toString() . '.', [$count_id, $count_no_id]),
            'status' => 'success',
            'statusCode' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Form $form, $id)
    {
        \Gate::authorize('usable', 'formfield.update');
        $field = $form->fields()->findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'label' => 'required|string',
            'value' => 'nullable|string',
            'hint' => 'nullable|string|min:3',
            'custom_error' => 'nullable|string|min:3',
            'compare' => 'nullable|date',
            'options' => 'required_if:element,select|nullable|array',
            'required' => 'nullable|boolean',
            'required_if' => 'nullable|string',
            'restricted' => 'nullable|boolean',
            'expected_value' => 'nullable|string',
            'key' => 'required|string',
            'min' => ['numeric', Rule::requiredIf($request->compare && $request->type === 'date' && ! $request->max)],
            'max' => ['numeric', Rule::requiredIf($request->compare && $request->type === 'date' && ! $request->min)],
            'points' => ['required', 'numeric'],
            'element' => 'required|string|in:input,textarea,select,locale,checkboxgroup,radiogroup',
            'type' => 'required|string|in:hidden,text,number,email,password,date,time,datetime-local,file,tel,url,checkbox,radio,country,state,lga,city',
        ], [
            'min.required' => 'the Min field is required if Compare is set and Type equals date while Max is missing',
            'max.required' => 'the Max field is required if Compare is set and Type equals date while Min is missing',
        ]);

        $field->name = $request->name;
        $field->field_id = $request->name;
        $field->label = $request->label;
        $field->value = $request->value;
        $field->hint = $request->hint;
        $field->custom_error = $request->custom_error;
        $field->compare = $request->compare;
        $field->options = $request->options;
        $field->required = $request->required;
        $field->required_if = $request->required_if;
        $field->restricted = $request->restricted;
        $field->expected_value = $request->expected_value;
        $field->key = $request->key;
        $field->min = $request->min;
        $field->max = $request->max;
        $field->points = $request->points;
        $field->element = $request->element;
        $field->type = $request->type;
        $field->save();

        return (new FormFieldResource($field))->additional([
            'message' => __("{$field->label} has been updated successfully."),
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
    public function destroy(Request $request, Form $form, string $id)
    {
        \Gate::authorize('usable', 'formfield.delete');

        @['items' => $items] = $this->validate($request, [
            'items' => ['nullable', 'array'],
            'items.*' => ['required', 'numeric'],
        ]);

        $count = count($items ?? []) ? count($items) : 1;
        $form->fields()->whereIn('id', count($items ?? []) ? $items : [$id])->delete();

        return Providers::response()->success([
            'data' => [],
            'message' => "{$count} form feilds have been deleted.",
        ], HttpStatus::ACCEPTED);
    }
}
