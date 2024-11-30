<?php

namespace App\Http\Requests;

use App\Models\Form;
use App\Models\GenericFormData;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveFormdataRequest extends FormRequest
{
    /**
     * The form fields
     *
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\GenericFormField>
     */
    protected \Illuminate\Database\Eloquent\Collection $fields;

    public function load(Form $form)
    {
        $this->fields = $form->fields->map(function ($field) use ($form) {
            if ($field->alias === 'learning_paths' && (bool) $form->learningPaths) {
                $field->options = collect($form->learningPaths)->map(function ($path) {
                    $path->label = $path->title;
                    $path->value = $path->id;

                    return $path;
                });
            }

            return $field;
        });
        dd($this->fields, $this->route());
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
    public function rules(string $form): array
    {
        dd($form);
        $this->load($form);

        return $this->fields->mapWithKeys(function ($field) {

            if ($field->type === 'number') {
                $rules[] = 'numeric';
            } elseif ($field->type === 'multiple') {
                $rules[] = 'array';
            } else {
                $rules[] = 'string';
            }

            if ($field->required_if) {
                $rules[] = 'nullable';
                foreach (explode(',', $field->required_if) as $cond) {
                    if (str($cond)->contains('=')) {
                        $rules[] = 'required_if:data.' . str($cond)->replace('=', ',');
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

            if ($field->options) {
                $rules[] = 'in:' . collect($field->options)->pluck('value')->implode(',');
            }

            return ['data.' . $field->name => $rules];
        })->toArray();
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->fields->filter(fn($form) => $form->custom_error)->mapWithKeys(function ($field) {
            if ($field->required_if) {
                return ["data.$field->name.required_if" => $field->custom_error];
            }

            if ($field->required) {
                return ["data.$field->name.required" => $field->custom_error];
            }
        })->toArray();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->fields->mapWithKeys(function ($field) {
            return ['data.' . $field->name => $field->label];
        })->toArray();
    }


    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        $errors = collect([]);

        foreach ($this->get('data', []) as $key => $value) {
            if ($this->fields->pluck('name')->doesntContain($key)) {
                $errors->push([$key => "$key is not a valid input."]);
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
                            'data.' . $key => __(
                                'The minimum :1 requirement for this application is :0, your :2 puts you at :3 by :4.',
                                [
                                    $field->max,
                                    $field->alias,
                                    $field->label,
                                    $diff,
                                    $compare
                                ]
                            )
                        ]);
                    }

                    if ($field->max && $diff > $field->max) {
                        $errors->push([
                            'data.' . $key => __(
                                'The :1 limit for this application is :0, your :2 puts you at :3 by :4.',
                                [
                                    $field->max,
                                    $field->alias,
                                    $field->label,
                                    $diff,
                                    $compare
                                ]
                            )
                        ]);
                    }
                }

                if ($field->key && GenericFormData::whereJsonContains("data->{$key}", $value)->exists()) {
                    $errors->push([
                        'data.' . $key => __('The :0 has already been taken.', [$field->label])
                    ]);
                }
            }
        }

        return [
            function (Validator $validator) use ($errors) {
                if ($errors->count() > 0) {
                    foreach ($errors->toArray() as $error) {
                        $validator->errors()->add(collect($error)->keys()->first(), collect($error)->first());
                    }
                }
            }
        ];
    }
}
