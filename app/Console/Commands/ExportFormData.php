<?php

namespace App\Console\Commands;

use App\Models\Form;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Maatwebsite\Excel\Facades\Excel;
use V1\Jobs\SendReport;
use V1\Mail\ReportGenerated;
use V1\Services\GenericDataExport;

class ExportFormData extends Command
{
    protected $items = [];

    protected $sheets = [];

    protected \App\Models\Form $form;

    protected int $batch = 0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export {--Q|queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helps prepare and export generic form data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $queue = $this->option('queue');

        $this->export($queue);
        $this->export($queue, true);

        return 0;
    }

    public function export($queue = false, $scanned = false)
    {
        $query = Form::query();

        if ($scanned === true) {
            $query->whereHas('data', function ($q) {
                $q->whereHas('scans');
            });
            $this->info('Exporting scanned form data...');
        } else {
            $this->info('Exporting form data...');
        }

        $this->batch++;

        $query->where('data_emails', '!=', null)->get()->map(function (Form $form) use ($queue, $scanned) {
            $this->form = $form;
            $formData = $form->data();
            if ($scanned === true) {
                $this->batch++;
                $formData->scanned();
            }

            $formData->chunk(1000, function ($items, $sheets) {
                $this->info('Exporting chunk of ' . $items->count() . ' items to sheets ' . $sheets . ' of ' . $this->form->name . '...');

                $this->pushItem($this->parseItem($items->first())->keys()->toArray());
                $items->each(function ($item) {
                    $this->info('Exporting item ' . $item->id . ' (' . $item->name_attribute . ')...');
                    $item = $this->parseItem($item)->toArray();
                    $this->pushItem($item);
                });

                $this->sheets[] = $this->items;
                $this->items = [];
            });

            $title = $scanned ? $form->name . ' (Scanned data)' : $form->name;
            $this->exportItems($this->sheets, $queue, $form, $title);
            $this->sheets = [];
            $this->batch = 0;
            $this->info('Done!');
        });
    }

    public function parseItem($item)
    {
        return $item->form->fields->mapWithKeys(function ($field) use ($item) {
            $value = $item->data[$field->name] ?? null;
            $label = $field->label ?? $field->name;

            if ($field->options) {
                $value = collect($field->options)->where('value', $value)->first()['label'] ?? $value;
            }

            return [$label => is_array($value) ? implode(', ', $value) : $value];
        });
    }

    public function pushItem($item)
    {
        $this->items[] = $item;
    }

    public function exportItems($items, $queue = false, $form = null, $title = null)
    {
        if (! is_array($items) || empty($items)) {
            return false;
        }

        if ($queue === true) {
            SendReport::dispatch($this->form, $this->batch, $title);
        } else {
            $this->form->data_emails->filter(fn($e) => $e->isNotEmpty())->each(function ($email) use ($title) {
                RateLimiter::attempt(
                    'send-report:' . $email . $this->batch,
                    5,
                    function () use ($email, $title) {
                        Mail::to($email->toString())->send(new ReportGenerated($this->form, $this->batch, $title));
                    },
                );
            });
        }

        return Excel::store(
            new GenericDataExport($items, $this->form, $title),
            'exports/' . ($this->form->id ?? $this->form->id ?? 'form') . '/data-batch' . $this->batch . '.xlsx',
            'protected'
        );
    }
}
