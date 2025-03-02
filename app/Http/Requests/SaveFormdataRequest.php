<?php

namespace App\Http\Requests;

use App\Models\Form;
use App\Models\FormData;
use App\Models\FormField;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveFormdataRequest extends FormRequest
{
    protected int|string|null $user_id;

    /**
     * @var string<'*.'|''>
     */
    protected string $mult;

    public function getMult(): string
    {
        return $this->mult;
    }

    public function hasMultipleEntries(): bool
    {
        return $this->getMult() === '*.';
    }

    /**
     * The form fields
     *
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\FormField>
     */
    protected \Illuminate\Database\Eloquent\Collection $fields;

    /**
     * The form
     */
    protected Form $form;

    public function load()
    {
        $this->form ??= $this->route()->parameter('form');
        $this->user_id ??= $this->input('user_id', $this->input('user', $this->user('sanctum')?->id));

        $this->fields ??= $this->form->fields->map(function ($field) {
            if ($field->alias === 'learning_paths' && (bool) $this->form->learningPaths) {
                $field->options = collect($this->form->learningPaths)->map(function ($path) {
                    $path->label = $path->title;
                    $path->value = $path->id;

                    return $path;
                });
            }

            return $field;
        });

        $this->mult ??= collect($this->input('data'))->keys()->every(fn($key) => is_int($key)) ? '*.' : '';
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $this->load();

        return $this->fields->mapWithKeys(function ($field) {

            $rules[] = 'bail';

            if ($field->expected_value_type === 'integer') {
                $rules[] = 'numeric';
            } elseif ($field->expected_value_type === 'array') {
                $rules[] = 'array';
            } elseif ($field->expected_value_type === 'boolean') {
                $rules[] = 'boolean';
            } else {
                $rules[] = 'string';
            }

            if ($field->required_if) {
                // $rules[] = 'nullable';
                foreach (explode(',', $field->required_if) as $cond) {
                    if (str($cond)->contains('=')) {
                        $rules[] = 'required_if:data.' . $this->mult . str($cond)->replace('=', ',');
                    }
                }
            } elseif ($field->required) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }

            if ($field->type === 'url') {
                $rules[] = 'url';
            }

            if ($field->type !== 'date') {
                if ($field->min) {
                    $rules[] = "min:$field->min";
                }

                if ($field->max) {
                    $rules[] = "max:$field->max";
                }
            }

            if ($field->type === 'email') {
                $rules[] = 'email';
            }

            // Validate the expected value
            if ($field->expected_value !== null) {
                $rules[] = function (string $attribute, mixed $val, \Closure $fail) use ($field) {
                    $valid = match ($field->expectedValueType) {
                        'integer' => ((int)$val) === ((int)$field->expected_value),
                        'boolean' => ((bool)$val) === ((bool)$field->expected_value),
                        default => mb_strtolower($val) === mb_strtolower($field->expected_value),
                    };

                    if (!$valid) {
                        $fail("We could not proccess your submission, {$attribute} is not an acceptable value.");
                    }
                };
            }

            if ($field->options && in_array($field->element, ['select', 'checkboxgroup', 'radiogroup'])) {
                $rules[] = 'in:' . collect($field->options)->pluck('value')->implode(',');
            }

            return ['data.' . $this->mult . $field->name => $rules];
        })->merge([
            'data' => 'required',
            'user' => 'nullable|exists:users,id',
            'user_id' => 'nullable|exists:users,id',
        ])->toArray();
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $this->load();

        return $this->fields->mapWithKeys(function ($field) {
            if ($field->required_if) {
                return [
                    "data.{$this->mult}{$field->name}.required_if" =>
                    $field->custom_error ?? ('The ' . $field->label . ' field is required.')
                ];
            }

            if (($field->required || $field->expected_value !== null) && $field->custom_error) {
                return ["data.{$this->mult}{$field->name}" => $field->custom_error];
            }

            return [];
        })->filter()->toArray();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $this->load();

        return $this->fields->mapWithKeys(function ($field, $index) {
            return ['data.' . $this->mult . $field->name => $field->label];
        })->toArray();
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        $this->load();
        $errors = collect([]);

        if ($this->hasMultipleEntries()) {
            foreach ($this->input('data', []) as $i => $data) {
                $errors->push($this->buildRules($data, $i));
            }
            $errors = $errors->collapse();
        } else {
            $errors = $this->buildRules($this->input('data', []));
        }

        return [
            function (Validator $validator) use ($errors) {
                if ($errors->count() > 0) {
                    foreach ($errors->toArray() as $error) {
                        $validator->errors()->add(collect($error)->keys()->first(), collect($error)->first());
                    }
                }
            },
        ];
    }

    protected function buildRules(array $data, int $index = null)
    {
        $errors = collect([]);
        $ind = ! is_null($index) ? $index . '.' : '';
        $failed = [];

        foreach ($data as $key => $value) {
            if ($this->fields->pluck('name')->doesntContain($key)) {
                $errors->push(['data.' . $ind . $key => "$key is not a valid input."]);
                $failed['data.' . $index] = true;
            }

            if ($this->fields->pluck('name')->contains($key)) {
                $field = $this->fields->firstWhere('name', $key);

                if ($field->required && $field->type === 'date' && $field->compare) {
                    $parseDate = \DateTime::createFromFormat('D M d Y H:i:s e+', $value);
                    $parseCompare = \DateTime::createFromFormat('D M d Y H:i:s e+', $field->compare);

                    $date = ($parseDate !== false) ? CarbonImmutable::parse($parseDate) : new Carbon($value);
                    $compare = ($parseCompare !== false) ? CarbonImmutable::parse($parseCompare) : new Carbon($field->compare);
                    $compare = $compare->format('jS M, Y');

                    $diff = $date->diffInYears($compare);

                    if ($field->min && $diff < $field->min) {
                        $errors->push([
                            'data.' . $ind . $key => __(
                                'The minimum :1 requirement for this application is :0, your :2 puts you at :3 by :4.',
                                [
                                    $field->max,
                                    $field->alias,
                                    $field->label,
                                    $diff,
                                    $compare,
                                ]
                            ),
                        ]);
                        $failed['data.' . $index] = true;
                    }

                    if ($field->max && $diff > $field->max) {
                        $errors->push([
                            'data.' . $ind . $key => __(
                                'The :1 limit for this application is :0, your :2 puts you at :3 by :4.',
                                [
                                    $field->max,
                                    $field->alias,
                                    $field->label,
                                    $diff,
                                    $compare,
                                ]
                            ),
                        ]);

                        $failed['data.' . $index] = true;
                    }
                }

                if ($field->key && FormData::whereJsonContains("data->{$key}", $value)->exists()) {
                    $errors->push([
                        'data.' . $ind . $key => __('The :0 has already been taken.', [$field->label]),
                    ]);
                    $failed['data.' . $index] = true;
                }
            }
        }

        if (count($failed)) {
            $errors->push($failed);
        }

        return $errors;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->load();

        if ($this->user_id && $this->hasMultipleEntries()) {
            FormData::whereFormId($this->form->id)
                ->whereUserId($this->user_id)
                ->delete();
            // $data = FormData::whereFormId($this->form->id)->whereFormId($user_id)->first();
        }

        /**
         * The entire block below works but is commented out because it turns out,
         * I don't really need to the conversion
         */

        // $parser = static fn(FormField $field, $val) => match ($field->expectedValueType) {
        //     'integer' => is_numeric($val) ? (int)$val : $val,
        //     'boolean' => (bool)$val,
        //     default => $val,
        // };

        // // Convert all data to thier expected type using the about $parser callback
        // $data = collect($this->input('data', []))->mapWithKeys(function ($value, $key) use ($parser) {
        //     if ($this->hasMultipleEntries()) {
        //         return [$key => collect($value)->mapWithKeys(function ($val, $k) use ($parser) {

        //             $fieldModel = $this->fields->firstWhere('name', $k);
        //             return [$k => $fieldModel ? $parser($fieldModel, $val) : $val];
        //         })];
        //     }

        //     $fieldModel = $this->fields->firstWhere('name', $key);
        //     return [$key => $fieldModel ? $parser($fieldModel, $value) : $value];
        // });

        // $this->merge(['data' => $data->toArray()]);
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $key = $this->fields->firstWhere('key', true)->name ?? '';
        $data = $this->validated('data');

        if ($this->hasMultipleEntries()) {
            $data = collect($data)->map(fn($v, $i) => [
                'user_id' => $this->user_id,
                'data' => $data[$i],
                'key' => $data[$i][$key] ?? '',
            ]);
        } else {
            $data = collect([[
                'user_id' => $this->user_id,
                'data' => $data,
                'key' => $data[$key] ?? '',
            ]]);
        }

        $this->replace(['data' => $data]);
    }
}
