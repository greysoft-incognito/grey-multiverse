<?php

namespace App\Services;

class FormProcessor
{

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function validate(Request $request, Form $form)
    {
        $errors = collect([]);

        $form->fields = $form->fields->map(function ($field) use ($form) {
            if ($field->alias === 'learning_paths' && (bool) $form->learningPaths) {
                $field->options = collect($form->learningPaths)->map(function ($path) {
                    $path->label = $path->title;
                    $path->value = $path->id;

                    return $path;
                });
            }

            return $field;
        });

        $custom_messages = $form->fields->filter(fn($f) => $f->custom_error)->mapWithKeys(function ($field, $key) {
            if ($field->required_if) {
                return ["data.$field->name.required_if" => $field->custom_error];
            } elseif ($field->required) {
                return ["data.$field->name.required" => $field->custom_error];
            }
        })->toArray();

        $custom_attributes = $form->fields->mapWithKeys(function ($field, $key) {
            return ['data.' . $field->name => $field->label];
        })->toArray();

        $validation_rules = $form->fields->mapWithKeys(function ($field, $key) {
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

        foreach ($request->get('data', []) as $key => $value) {
            if ($form->fields->pluck('name')->doesntContain($key)) {
                $errors->push([$key => "$key is not a valid input."]);
            }
            if ($form->fields->pluck('name')->contains($key)) {
                $field = $form->fields->firstWhere('name', $key);

                if ($field->required && $field->type === 'date' && $field->compare) {
                    $parseDate = \DateTime::createFromFormat('D M d Y H:i:s e+', $value);
                    $parseCompare = \DateTime::createFromFormat('D M d Y H:i:s e+', $field->compare);
                    $date = ($parseDate !== false) ? CarbonImmutable::parse($parseDate) : new Carbon($value);
                    $compare = ($parseCompare !== false) ? CarbonImmutable::parse($parseCompare) : new Carbon($field->compare);
                    $compare = $compare->format('jS M, Y');
                    $diff = $date->diffInYears($compare);

                    if ($field->min && $diff < $field->min) {
                        $errors->push(['data.' . $key => __('The minimum :1 requirement for this application is :0, your :2 puts you at :3 by :4.', [$field->max, $field->alias, $field->label, $diff, $compare])]);
                    }

                    if ($field->max && $diff > $field->max) {
                        $errors->push(['data.' . $key => __('The :1 limit for this application is :0, your :2 puts you at :3 by :4.', [$field->max, $field->alias, $field->label, $diff, $compare])]);
                    }
                }

                if ($field->key) {
                }
                if ($field->key && GenericFormData::whereJsonContains("data->{$key}", $value)->exists()) {
                    $errors->push(['data.' . $key => __('The :0 has already been taken.', [$field->label])]);
                }
            }
        }

        $validator = Validator::make($request->all(), $validation_rules, $custom_messages, $custom_attributes);
        $validator->after(function ($validator) use ($errors) {
            if ($errors->count() > 0) {
                foreach ($errors->toArray() as $key => $error) {
                    $validator->errors()->add(collect($error)->keys()->first(), collect($error)->first());
                }
            }
        });
        $validator->validate();
    }
}
