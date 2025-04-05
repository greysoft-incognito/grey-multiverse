<?php

namespace App\Jobs;

use App\Models\Form;
use App\Services\FormPointsCalculator;
// use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class CalculateFormDataRankings implements ShouldQueue //, ShouldBeUnique
{
    use Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Form $form
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $calculator = new FormPointsCalculator();

        /**
         * Sync the form total points
         */
        $this->form->total_points = $calculator->calculateFormTotalPoints($this->form);
        $this->form->saveQuietly();

        /**
         * Sync the form data total points
         *
         * @var \Illuminate\Support\LazyCollection<int, \App\Models\FormData>
         */
        $submissions = $this->form->data()->whereNot('status', 'pending')
            ->cursor();

        foreach ($submissions as $submission) {
            $rank = $calculator->calculatePoints($submission);

            $submission->update([
                'rank' => $rank
            ]);
        }

        if (app()->runningInConsole()) {
            echo "FormPointsCalculator Proccessed.";
        }
    }
}
