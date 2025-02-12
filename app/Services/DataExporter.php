<?php

namespace App\Services;

use App\Mail\ReportGenerated;
use App\Models\BizMatch\Appointment;
use App\Models\BizMatch\Company;
use App\Models\Form;
use App\Models\GenericFormData;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Maatwebsite\Excel\Facades\Excel;
use V1\Jobs\SendReport;
use V1\Services\GenericDataExport;

class DataExporter
{
    /**
     * @var array<int,array<int,string>>
     */
    protected array $items = [];

    /**
     * @var array<int,array<int,array<int,string>>>
     */
    protected $sheets = [];

    /**
     * Idicates the current data batch being proccessed
     */
    protected int $batch = 0;

    /**
     * Queue the export process
     */
    protected bool $queue = false;

    /**
     * Data export will be aborted if set to true
     */
    protected bool $abort = false;

    /**
     * Let the exporter know that this is a shallow export and has no deep nested dependencies
     */
    protected bool $shallow = false;

    /**
     * Scope to export only scanned
     */
    protected bool $scanned = false;

    /**
     * An array of allowed keys to export from each model
     *
     * @var array<int,string>
     */
    protected array $allowed = [];

    /**
     * Set the default title for this export
     */
    protected ?string $exportTitle = null;

    /**
     * The current query builder instance
     *
     * @var \Illuminate\Support\LazyCollection<int,Form|Company|Appointment|User>
     */
    protected \Illuminate\Support\LazyCollection $dataCollection;

    /**
     * Default collection of email addresses to share exported items with
     *
     * @var \Illuminate\Support\Collection<int, \Illuminate\Support\Stringable>
     */
    protected \Illuminate\Support\Collection $data_emails;

    public function __construct(
        protected ?Command $console = null,
        protected int $chunkSize = 1000,
    ) {
        $this->data_emails = dbconfig('notifiable_emails', collect([]))->map(fn($e) => str($e));
    }

    public function export()
    {
        if ($this->abort === true) {
            return 0;
        }

        $classname = str(get_class($this->dataCollection->first()))->afterLast('\\')->lower();

        if ($this->scanned === true) {
            $this->console?->info("Exporting scanned {$classname} data...");
        } else {
            $this->console?->info("Exporting {$classname} data...");
        }

        // Increment the current batch
        $this->batch++;
        // dd($this->data_emails);
        // Let's loop through our data collection and perform some operations
        foreach ($this->dataCollection as $dataset) {
            $title = $dataset->title ?? $dataset->name ?? $this->exportTitle ?? 'unknown';

            // Set the data_emails so we can mail them this batch
            if (isset($dataset->data_emails)) {
                $this->data_emails->merge($dataset->data_emails);
            }

            // Export GenericFormData dataset
            if (method_exists($dataset, 'data')) {
                $formData = $dataset->data();

                if ($this->scanned === true) {
                    $this->batch++;
                    $formData->scanned();
                }

                $formData->chunk($this->chunkSize, function ($items, $sheets) use ($dataset) {
                    $this->console?->info("Exporting chunk of {$items->count()} items to sheets {$sheets} of {$dataset->name}...");

                    // Extract the Headings for the CSV
                    $this->pushItem($this->parseGeneric($items->first())->keys()->toArray());

                    // Extract the Data for the CSV
                    $items->each(function ($item) {
                        $this->console?->info('Exporting item ' . $item->id . ' (' . $item->name_attribute . ')...');
                        $item = $this->parseGeneric($item)->toArray();
                        $this->pushItem($item);
                    });

                    $this->sheets[] = $this->items;
                    $this->items = [];
                });

                $title = $this->scanned ? $title . ' (Scanned data)' : $title;
                $this->saveAndDispatch($this->sheets, $dataset, $title);
            } else {
                // Export all other dataset
                $item = $this->parseNonGeneric($dataset);
                $this->pushItem($item->values()->toArray());
            }
        }

        if ($this->shallow === true) {
            $this->sheets = collect($this->items)
                ->chunk($this->chunkSize)
                ->map(function ($value, $key) {
                    if ($key !== 0) {
                        $value->prepend($this->items[0]);
                    }

                    return $value;
                })
                ->toArray();

            $this->items = [];
            $this->saveAndDispatch($this->sheets, $dataset, $this->exportTitle);
        }
    }

    private function parseGeneric(GenericFormData $item)
    {
        return $item->form->fields->mapWithKeys(function ($field) use ($item) {
            $label = $field->label ?? $field->name;
            $value = $item->data[$field->name] ?? null;

            if (str($label)->lower()->is('primary')) {
                $value = $value ? 'Yes' : '';
            }

            if ($field->options) {
                $value = collect($field->options)->where('value', $value)->first()['label'] ?? $value;
            }

            return [$label => is_array($value) ? implode(', ', $value) : $value];
        });
    }

    private function parseNonGeneric(Company|Appointment|User $item)
    {
        return collect($item->toArray())
            ->only($this->allowed)
            ->sortBy(fn($_, $key) => array_search($key, $this->allowed))
            ->map(function ($value, $key) {
                if (in_array($key, ['user_id', 'requestor_id', 'invitee_id'])) {
                    /** @var User */
                    $user = User::find($value);
                    $value = in_array($key, ['requestor_id', 'invitee_id']) && $user->company
                        ? $user->company->name
                        : $user?->fullname;
                }

                // Parse date fields
                if (in_array($key, ['booked_for', 'created_at', 'date'])) {
                    $value = Carbon::parse($value)->isoFormat(
                    'MMM DD, YYYY' . ($key === 'booked_for' ? ': hh:mm A' : '')
                    );
                }

                return (string) $value;
            });
    }

    /**
     * Push an item into the items stack
     *
     * @param  array<int,string>  $item
     * @return void
     */
    private function pushItem(array $item)
    {
        $this->items[] = $item;
    }

    /**
     * Save the prepared data and dispatch if required
     *
     * @param [type] $items
     * @param [type] $title
     * @return bool
     */
    private function saveAndDispatch($items, Form|Company|Appointment|User $dataset, $title = null)
    {
        if (! is_array($items) || empty($items)) {
            return false;
        }
        if (isset($this->data_emails) && $this->data_emails->isNotEmpty()) {
            if ($this->queue === true) {
                SendReport::dispatch($dataset, $this->batch, $title, $this->data_emails);
            } else {
                $this->data_emails
                    ->unique()
                    ->filter(fn($e) => $e->isNotEmpty())
                    ->each(fn($email) => $this->dispatchMails($email, $dataset, $title));
            }
        }

        $sid = $dataset instanceof Form ? $dataset->id : 'dataset';
        $name = str(get_class($dataset))->afterLast('\\')->plural()->append('-')->append($sid)->slug();

        $status = Excel::store(
            new GenericDataExport($items, $dataset, $title),
            "exports/{$name}/data-batch{$this->batch}.xlsx",
            'protected'
        );

        $this->sheets = [];
        $this->batch = 0;
        $this->console?->info('Done!');

        return $status;
    }

    /**
     * Dispatch the exported data to the data_emails
     */
    private function dispatchMails(
        \Illuminate\Support\Stringable $email,
        Form|Company|Appointment|User $dataset,
        string $title,
    ): void {
        RateLimiter::attempt(
            'send-report:' . $email . $this->batch,
            5,
            function () use ($email, $title, $dataset) {
                Mail::to($email->toString())->send(new ReportGenerated($dataset, $this->batch, $title));
            },
        );
    }

    /**
     * Export generic form data
     *
     * @return $this
     */
    public function formData(bool $queue = false, bool $scanned = false)
    {
        $query = Form::query()->where('data_emails', '!=', null);

        if ($scanned === true) {
            $query->whereHas('data', function ($q) {
                $q->whereHas('scans');
            });
        }

        $this->dataCollection = $query->cursor();
        $this->queue = $queue;
        $this->scanned = $scanned;

        $this->abort = $this->dataCollection->count() < 1;

        return $this;
    }

    /**
     * Export users
     *
     * @return $this
     */
    public function users(bool $queue = false)
    {
        $query = User::query()->whereDoesntHave('roles')->whereDoesntHave('permissions');

        $this->dataCollection = $query->cursor();
        $this->exportTitle = 'User Data';
        $this->scanned = false;
        $this->shallow = true;
        $this->queue = $queue;

        $this->abort = $this->dataCollection->count() < 1;

        if (! $this->abort) {
            $this->allowed = [
                'id',
                'firstname',
                'lastname',
                'email',
                "phone",
                'city',
                'state',
                'country',
                'created_at',
            ];

            // Extract the Headings for the CSV
            $headings = collect($this->allowed)->map(
                fn($e) => str($e)
                    ->replace('_', ' ')
                    ->title()
                    ->replace(['Id'], ['ID'])
                    ->toString()
            );

            $this->pushItem($headings->toArray());
        }

        return $this;
    }

    /**
     * Export companies
     *
     * @return $this
     */
    public function companies(bool $queue = false)
    {
        $query = Company::query();

        $this->dataCollection = $query->cursor();
        $this->exportTitle = 'Companies Data';
        $this->scanned = false;
        $this->shallow = true;
        $this->queue = $queue;

        $this->abort = $this->dataCollection->count() < 1;

        if (! $this->abort) {
            $this->allowed = [
                'id',
                'name',
                'user_id',
                'industry_category',
                // "description",
                'country',
                'location',
                'conference_objectives',
                'created_at',
            ];

            // Extract the Headings for the CSV
            $headings = collect($this->allowed)->map(
                fn($e) => str($e)
                    ->replace('_', ' ')
                    ->title()
                    ->replace(['User Id', 'Id'], ['Representative', 'ID'])
                    ->toString()
            );
            $this->pushItem($headings->toArray());
        }

        return $this;
    }

    /**
     * Export Appointment
     *
     * @return $this
     */
    public function appointments(bool $queue = false)
    {
        $query = Appointment::query();

        $this->dataCollection = $query->cursor();
        $this->exportTitle = 'Appointment Data';
        $this->scanned = false;
        $this->shallow = true;
        $this->queue = $queue;

        $this->abort = $this->dataCollection->count() < 1;

        if (! $this->abort) {
            $this->allowed = [
                'id',
                'requestor_id',
                'invitee_id',
                'date',
                'time_slot',
                'duration',
                'status',
                'table_number',
                'booked_for',
                'created_at',
            ];

            // Extract the Headings for the CSV
            $headings = collect($this->allowed)->map(
                fn($e) => str($e)
                    ->replace('_', ' ')
                    ->title()
                    ->replace([' Id', 'Id'], ['', 'ID'])
                    ->toString()
            );
            $this->pushItem($headings->toArray());
        }

        return $this;
    }
}