<?php

namespace App\Observers;

use App\Models\Loan;
use App\Notifications\LoanStatusChanged;
use Illuminate\Support\Facades\Notification;

class LoanObserver
{
    /**
     * Handle the Loan "created" event.
     */
    public function created(Loan $loan): void
    {
        // Notify member when they apply for a loan (Status: Applied)
        if ($loan->member) {
            $loan->member->notify(new LoanStatusChanged($loan));
        }
    }

    /**
     * Handle the Loan "updated" event.
     */
    public function updated(Loan $loan): void
    {
        // Check if status has changed
        if ($loan->isDirty('status')) {
            if ($loan->member) {
                // Determine if we should notify for this status
                // We typically notify for Approved, Disbursed, Repaid
                // The 'Applied' status is handled in created()

                $loan->member->notify(new LoanStatusChanged($loan));
            }
        }
    }

    /**
     * Handle the Loan "deleted" event.
     */
    public function deleted(Loan $loan): void
    {
        //
    }

    /**
     * Handle the Loan "restored" event.
     */
    public function restored(Loan $loan): void
    {
        //
    }

    /**
     * Handle the Loan "force deleted" event.
     */
    public function forceDeleted(Loan $loan): void
    {
        //
    }
}
