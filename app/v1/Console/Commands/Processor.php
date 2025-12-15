<?php

namespace V1\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;

class Processor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:processor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handles automated processes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Running automated processes...');

        $transactions = Transaction::where('status', 'pending')
            ->where('created_at', '<=', now()->subHours(2))
            ->cursor();

        foreach ($transactions as $transaction) {
            $transaction->update([
                'status' => 'failed',
            ]);
        }

        $this->info('Automated processes completed successfully!');

        return 0;
    }
}
