<?php

namespace App\Http\Controllers\Admin\Forms;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormResource;
use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use App\Services\FormPointsCalculator;
use App\Traits\TimeTools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Jobs\CalculateFormDataRankings;

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
        \App\Enums\Permission::FORM_UPDATE->authorize();

        $validateField = static fn() => Rule::exists(FormField::class, 'name')->where('form_id', $form->id);

        $valid = $request->validate([
            'chartables' => 'nullable|array',
            'chartables.*.field_name' => ['required', 'string', function (string $attr, mixed $val, \Closure $fail) use ($form) {
                if ($val === 'generic-trend') {
                    return;
                }
                if (DB::table('form_fields')->whereName($val)->where('form_id', $form->id)->exists()) {
                    return;
                }

                $fail("The {$attr} is invalid.");
            }],
            'chartables.*.chart_period' => 'nullable|in:today,yesterday,week,month,year,last_week,last_month,last_year',
            'chartables.*.chart_title' => 'nullable|string|min:3',
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

            'sort_fields' => ['nullable', 'array'],
            'sort_fields.*' => ['required', 'exists:form_fields,id'],

            'base_url' => ['nullable', 'url'],
            'extended_access' => ['nullable', 'boolean'],
            'questions_chart' =>  ['nullable', 'boolean'],
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
    public function sync(Form $form)
    {
        $calculator = new \App\Services\FormPointsCalculator();
        dd($calculator->calculateFormTotalPoints($form));

        CalculateFormDataRankings::dispatch($form);

        return (new FormResource($form))->additional([
            'message' => __("Sync tasks for this form has been dispatched."),
            'status' => 'success',
            'statusCode' => HttpStatus::CREATED,
        ])->response()->setStatusCode(HttpStatus::CREATED->value);
    }

    /**
     * Display the stats for the resources resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function stats(Request $request, Form $form)
    {
        \App\Enums\Permission::FORMDATA_STATS->authorize();

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
                    function (string $attr, mixed $value, \Closure $fail) use ($fields) {
                        str($value)->explode(',')->each(function ($group) use ($fields, $fail) {
                            $stat = str($group)->explode(':');
                            [$_, $field_name] = str($stat->first())->explode('|');

                            if (! $fields->contains(fn($field) => $field->name === $field_name)) {
                                $fail(__("`{$field_name}` is an invalid field name, supported fields include: :0.", [
                                    $fields->map(fn($field) => $field->name)->join(', ', ' and '),
                                ]));
                            }
                        });
                    },
                ],
            ],
            [
                'data.regex' => 'The data field format is invalid, a valid format looks like `key|field:value` or `key|field:value,key|field:value`, underscores are supported and periods are also supported for values if you need to check a list.',
            ]
        );

        if ($request->data) {
            $request_data = str($request->data)->explode(',');

            $data = $request_data->map(function ($value) use ($form) {
                $stat = str($value)->explode(':');

                [$key, $field] = str($stat->first())->explode('|');
                $options = str($stat->last())->explode('.');

                $query = $form->data();
                $query->where(fn($q) => $options->each(fn($val) => $q->orWhereJsonContains("data->{$field}", $val)));

                return [
                    'label' => $key,
                    'value' => $query->count(),
                    'cols' => 3,
                ];
            })->prepend([
                'label' => 'total_submissions',
                'value' => $form->data()->count(),
                'cols' => 3,
            ])->prepend([
                'label' => 'in_draft',
                'value' => $form->drafts()->count(),
                'cols' => 3,
            ]);
        } else {
            $data = collect([[
                'label' => 'total_submissions',
                'value' => $form->data()->count(),
                'cols' => 3,
            ], [
                'label' => 'in_draft',
                'value' => $form->drafts()->count(),
                'cols' => 3,
            ]]);
        }

        if (isset($form->config['extended_access']) && $form->config['extended_access'] == true) {
            $cb = static function (bool $ver = false) {
                $method = $ver ? 'whereNotNull' : 'whereNull';
                $query = User::isAdmin(false);
                $query->when(dbconfig('verify_email', false), fn($q) => $q->{$method}('email_verified_at'));
                $query->when(dbconfig('verify_phone', false), fn($q) => $q->{$method}('phone_verified_at'));

                return $query->count();
            };

            $data->prepend([
                'label' => 'unverified_users',
                'value' => $cb(),
                'cols' => 3,
            ]);

            $data->prepend([
                'label' => 'registered_users',
                'value' => $cb(true),
                'cols' => 3,
            ]);
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
                    'icon' => $statcard['icon'] ?? 'info',
                    'cols' => 3,
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

                $dataKey = $chartable['field_name'];
                $countKey = 'count';

                if ($chartable['field_name'] === 'generic-trend') {
                    $dataKey = 'date';
                    $countKey = 'aggregate';
                    $data = $this->buildTrend(
                        query: $form->data()->getQuery(),
                        timeframe: $chartable['chart_period'] ?? 'today'
                    );
                } else {
                    $data = $form->data()->selectRaw("$json_query as `{$chartable['field_name']}`, COUNT(id) as count")
                        ->groupBy($chartable['field_name'])
                        ->get();
                }

                return $this->formatForChartJs(
                    $data,
                    $dataKey,
                    $countKey,
                    [
                        'type' => $chartable['chart_type'] ?? 'pie',
                        'cols' => $chartable['cols'] ?? 3,
                        'title' => $chartable['chart_title'] ?? null,
                        'period' => $chartable['chart_period'] ?? null,
                    ]
                );
            });
        }

        if (isset($form->config['questions_chart']) && $form->config['questions_chart'] === true) {
            $chart_data[] = array_merge(
                ['cols' => 12, 'type' => 'bar', 'title' => 'Response Patterns'],
                (new FormPointsCalculator())->questionsChartData($form)
            );
        }

        return Providers::response()->success([
            'data' => $data->values(),
            'chart_data' => $chart_data,
            'form' => new FormResource($form),
        ]);
    }
}
