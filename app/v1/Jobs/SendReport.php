<?php

namespace V1\Jobs;

use App\Mail\ReportGenerated;
use App\Models\BizMatch\Appointment;
use App\Models\BizMatch\Company;
use App\Models\Form;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class SendReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  null|\Illuminate\Support\Collection<int, \Illuminate\Support\Stringable>  $data_emails
     * @return void
     */
    public function __construct(
        protected Form|Company|Appointment|User $dataset,
        protected int $batch = 0,
        protected ?string $title = null,
        protected ?Collection $data_emails = null,
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->data_emails || $this->data_emails->isEmpty()) {
            return false;
        }

        $this->data_emails->unique()->filter(fn ($e) => $e->isNotEmpty())->each(function ($email) {
            RateLimiter::attempt(
                'send-report:'.$email.$this->batch,
                5,
                function () use ($email) {
                    Mail::to($email->toString())->send(new ReportGenerated($this->dataset, $this->batch, $this->title));
                },
                30
            );
        });
    }
}
