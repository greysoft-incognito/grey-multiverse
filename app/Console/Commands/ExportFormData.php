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
                            {--M|modern}
                            {--Q|queue}
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
            new SimpleDataExporter(50);
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
