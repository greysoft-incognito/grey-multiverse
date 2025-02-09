<?php

namespace App\Jobs;

use App\Services\DataExporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExportData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $exporter = new DataExporter(
            chunkSize: 500,
        );

        $exporter->formData()->export();
        $exporter->formData(scanned: true)->export();
        $exporter->companies()->export();
        $exporter->appointments()->export();
    }
}
