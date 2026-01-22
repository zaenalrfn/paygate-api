<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPaymentJob implements ShouldQueue
{
    use Queueable;

    public $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Logic to process the successful payment
        // e.g., Send email, update inventory, notify heavy systems

        \Log::info("Processing payment for transaction: " . $this->transaction->transaction_code);

        // Example: Update status to processed if needed, or trigger other events
        // $this->transaction->update(['status' => 'PROCESSED']);

        \Log::info("Payment processing complete.");
    }
}
