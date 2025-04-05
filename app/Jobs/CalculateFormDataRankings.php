<?php

namespace App\Jobs;

use App\Models\Form;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateFormDataRankings implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(Form $form)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // dump(
        //     (new FormPointsCalculator())->calculatePoints($this),
        //     (new FormPointsCalculator())->questionsChartData($this->form),
        //     $this->calculatePoints(),
        //     $this->calculatePoints(),
        //     $this->form->total_points,
        // );
    }
}
