<?php

namespace App\Http\Controllers\Admin\Forms;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormResource;
use App\Models\Form;
use App\Models\GenericFormField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\Providers;
use App\Traits\TimeTools;
use Illuminate\Support\Facades\DB;

class FormExtraController extends Controller
{
    use TimeTools;

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function config(Request $request, Form $form)
    {
        \Gate::authorize('usable', 'form.update');

        $validateField = static fn() => Rule::exists(GenericFormField::class, 'name')->where('form_id', $form->id);

        $valid = $request->validate([
            'chartables' => 'nullable|array',
            'chartables.*.field_name' => ['required', 'string', $validateField()],
            'chartables.*.chart_type' => 'required|in:line,bar,pie',
            'chartables.*.cols' => 'required|numeric|in:12,8,6,4,3',
            'statcards' => 'nullable|array',
            'statcards.*.field' => ['required', 'string', $validateField()],
            'statcards.*.key' => 'required|string',
            'statcards.*.cols' => 'required|numeric|in:12,8,6,4,3',
            'statcards.*.value' => 'required|string',
            'fields_map' => 'required|array',
            'fields_map.name' => ['required', 'string', $validateField()],
            'fields_map.email' => ['required', 'string', $validateField()],
            'fields_map.phone' => ['required', 'string', $validateField()],
            'auto_assign_reviewers' => ['nullable', 'boolean'],
        ]);

        $form->config = collect($form->config)->merge($valid);
        $form->save();

        return (new FormResource($form))->additional([
            'message' => __("{$form->name} config has been updated successfully."),
            'status' => 'success',
            'statusCode' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Display the stats for the resources resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function stats(Request $request, Form $form)
    {
        \Gate::authorize('usable', 'formdata.stats');

        $fields = $form->fields;
        $partern = '[a-zA-Z0-9_]+\|[a-zA-Z0-9_]+:[a-zA-Z0-9._]';

        /**
         * Validate the data query
         */
        $this->validate(
            $request,
            [
                'data' => [
                    'bail',
                    'nullable',
                    "regex:/^({$partern}+)(,{$partern}+)*$/",
                    function (string $attribute, mixed $value, \Closure $fail) use ($fields) {
                        str($value)->explode(',')->each(function ($group) use ($fields, $fail) {
                            $stat = str($group)->explode(':');
                            [$_, $field_name] = str($stat->first())->explode('|');

                            if (!$fields->contains(fn($field) => $field->name === $field_name)) {
                                $fail(__("`{$field_name}` is an invalid field name, supported fields include: :0.", [
                                    $fields->map(fn($field) => $field->name)->join(', ', ' and ')
                                ]));
                            }
                        });
                    },
                ],
            ],
            [
                'data.regex' => 'The data field format is invalid, a valid format looks like `key|field:value` or `key|field:value,key|field:value`, underscores are supported and periods are also supported for values if you need to check a list.'
            ]
        );

        if ($request->data) {
            $request_data = str($request->data)->explode(',');

            $data = $request_data->map(function ($value) use ($form, $fields) {
                $stat = str($value)->explode(':');

                [$key, $field] = str($stat->first())->explode('|');
                $options = str($stat->last())->explode('.');

                $query = $form->data();
                $query->where(fn($q) => $options->each(fn($val) => $q->orWhereJsonContains("data->{$field}", $val)));

                return [
                    'label' => $key,
                    'value' => $query->count(),
                    'cols' => 3
                ];
            })->prepend([
                'label' => 'total_submissions',
                'value' => $form->data()->count(),
                'cols' => 3
            ]);
        } else {
            $data = collect([[
                'label' => 'total_submissions',
                'value' => $form->data()->count(),
                'cols' => 3
            ]]);
        }

        /**
         * Generate the stat cards data
         */
        if (isset($form->config['statcards'])) {
            $data = $data->merge(collect($form->config['statcards'])->map(function ($statcard) use ($form) {
                $query = $form->data();
                $query->whereJsonContains("data->{$statcard['field']}", $statcard['value']);

                return [
                    'label' => $statcard['key'],
                    'value' => $query->count(),
                    'cols' => 3
                ];
            }));
        }

        /**
         * Generate the chart data
         */
        $chart_data = [];

        if (isset($form->config['chartables'])) {
            $driver = DB::connection()->getDriverName();

            /**
             * Set the query based on the database driver
             */
            if (in_array($driver, ['mysql', 'mariadb'])) {
                $json_query = "JSON_UNQUOTE(JSON_EXTRACT(data, '$.:field_name'))";
            } elseif ($driver === 'pgsql') {
                $json_query = "data->>':field_name'";
            } else {
                throw new \Exception("Unsupported database driver: $driver");
            }

            /**
             * Parse and get the chart data
             */
            $chart_data = collect($form->config['chartables'])->map(function ($chartable) use ($json_query, $form) {
                $json_query = str($json_query)->replace(':field_name', $chartable['field_name'])->toString();

                $data = $form->data()->selectRaw("$json_query as `{$chartable['field_name']}`, COUNT(*) as count")
                    ->groupBy($chartable['field_name'])
                    ->get();

                return $this->formatForChartJs(
                    $data,
                    $chartable['field_name'],
                    'count',
                    ['type' => $chartable['chart_type'], 'cols' => $chartable['cols']]
                );
            });
        }

        return Providers::response()->success([
            'data' => $data->values(),
            'chart_data' => $chart_data,
            'form' => new FormResource($form),
        ]);
    }
}
