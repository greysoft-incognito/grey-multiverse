<?php

namespace App\Jobs;

use App\Mail\ReportGenerated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class DispatchExportsNotifications implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $dataset
     * @param \Illuminate\Support\Collection<int, \Illuminate\Support\Stringable> $data_emails
     * @param string $title
     * @param integer $batch
     */
    public function __construct(
        protected \Illuminate\Database\Eloquent\Model $dataset,
        protected \Illuminate\Support\Collection $data_emails,
        protected string $title,
        protected int $batch = 1,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->data_emails->unique()->filter(fn($e) => $e->isNotEmpty()) as $email) {
            RateLimiter::attempt(
                'send-report:' . $email . $this->batch,
                5,
                fn() => Mail::to($email->toString())->send(
                    new ReportGenerated($this->dataset, $this->batch, $this->title)
                )
            );
        }
    }
}
