<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Notifications\LoanOverdue;

class CheckOverdueLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue loans and notify members';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue loans...');

        $loans = Loan::all();

        $count = 0;
        foreach ($loans as $loan) {
            if ($loan->is_overdue) {
                if ($loan->member) {
                    $loan->member->notify(new LoanOverdue($loan));
                    $count++;
                    $this->line("Notified member {$loan->member->full_name} about overdue loan #{$loan->id}");
                }
            }
        }

        $this->info("Completed. Notifications sent: {$count}");
    }
}
