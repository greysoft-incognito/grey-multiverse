<?php

namespace App\Console\Commands;

use App\Jobs\ExportData;
use App\Services\DataExporter;
use App\Services\SimpleDataExporter;
use Illuminate\Console\Command;

class ExportFormData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export
                            {dataset?* : List of exportable dataset (Allowed: forms, users, appointment, companies)}
                            {--M|modern : Use the modern export interface}
                            {--Q|queue : Queue the process for later}
                            {--d|draft : Export only items in draft, if provided, only FormData will be exported.}
                            {--P|per_page=50 : Number of results to add to each sheet}
                            {--e|emails=* : Email addresses that should get exported data notification}
                            {--f|forms=* : Form IDs to export, if provided, only FormData will be exported.}
                           ';

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
        if ($this->option('modern')) {
            new SimpleDataExporter(
                perPage: (int) $this->option('per_page'),
                scanned: false,
                draft: $this->option('draft'),
                emails: $this->option('emails'),
                formIds: $this->option('forms') ?? [],
                dataset: $this->argument('dataset'),
            );
        } else {
            if ($this->option('queue')) {
                ExportData::dispatch();
            } else {
                $exporter = new DataExporter(
                    chunkSize: 500,
                    console: $this,
                );

                $exporter->formData()->export();
                $exporter->formData(scanned: true)->export();
                $exporter->users()->export();
                $exporter->companies()->export();
                $exporter->appointments()->export();
            }
        }

        return 0;
    }
}
