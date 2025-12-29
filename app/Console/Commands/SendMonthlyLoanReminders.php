<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Notifications\LoanMonthlyReminder;

class SendMonthlyLoanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:send-monthly-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly reminders to members with active loans';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding active loans for monthly reminders...');

        // Active loans: Approved or Disbursed, and NOT fully repaid
        // Active loans: Approved or Disbursed, and NOT fully repaid
        $loans = Loan::whereIn('status', [Loan::STATUS_APPROVED, Loan::STATUS_DISBURSED])
            ->where('repaid', false)
            ->get();

        $count = 0;
        foreach ($loans as $loan) {
            // Double check balance just in case
            if ($loan->balance > 0) {
                if ($loan->member) {
                    $loan->member->notify(new LoanMonthlyReminder($loan));
                    $count++;
                    $this->line("Sent monthly reminder to {$loan->member->full_name} for loan #{$loan->id}");
                }
            }
        }

        $this->info("Completed. Reminders sent: {$count}");
    }
}
