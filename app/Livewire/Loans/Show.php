<?php

namespace App\Livewire\Loans;

use App\Models\ChamaSetting;
use App\Models\Loan;
use App\Models\LoanApproval;
use App\Models\LoanRepayment;
use App\Notifications\InAppNotification;
use App\Services\NotifyService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Loan $loan;
    
    // Repayment Form
    public $repaymentAmount;
    public $repaymentDate;
    public $repaymentMethod = 'cash';
    public $repaymentNotes = '';

    // Approval / rejection remark
    public $approvalRemark = '';

    public function mount(Loan $loan)
    {
        $this->loan = $loan;
        $this->repaymentDate = date('Y-m-d');
        $this->repaymentAmount = $loan->balance; // Default to full balance
    }

    public function approve()
    {
        if (! in_array($this->loan->status, ['applied'])) {
            session()->flash('error', 'Only pending loans can be approved.');
            return;
        }

        $memberId = auth()->user()->member_id;

        // Prevent duplicate approval by the same person
        if ($memberId && $this->loan->approvals()->where('member_id', $memberId)->exists()) {
            session()->flash('error', 'You have already approved this loan.');
            return;
        }

        LoanApproval::create([
            'loan_id'   => $this->loan->id,
            'member_id' => $memberId,
            'remark'    => $this->approvalRemark ?: 'Approved',
        ]);

        $this->approvalRemark = '';

        $required = (int) (ChamaSetting::current()->min_loan_approvals ?? 1);
        $current  = $this->loan->approvals()->count();

        // Notify admins of every approval step
        NotifyService::toAdmins(new InAppNotification(
            type:    'loan.approval.step',
            title:   'Loan approval recorded',
            message: "Loan #{$this->loan->id}: {$current}/{$required} approvals collected.",
            url:     route('loans.show', $this->loan),
            icon:    'feather-check',
            color:   'info',
        ));

        // Once threshold met, flip status to approved and notify the borrower
        if ($current >= $required && $this->loan->status === 'applied') {
            $this->loan->update(['status' => 'approved']);
            $this->loan->refresh();

            NotifyService::toMember($this->loan->member_id, new InAppNotification(
                type:    'loan.approved',
                title:   'Loan approved',
                message: 'Your loan of KES ' . number_format($this->loan->amount, 2) . ' has been approved.',
                url:     route('loans.show', $this->loan),
                icon:    'feather-check-circle',
                color:   'success',
            ));
            NotifyService::toAdmins(new InAppNotification(
                type:    'loan.approved.admin',
                title:   'Loan fully approved',
                message: 'Loan #' . $this->loan->id . ' has reached the required approvals and is ready for disbursement.',
                url:     route('loans.show', $this->loan),
                icon:    'feather-check',
                color:   'success',
            ));
        } else {
            $this->loan->refresh();
        }

        session()->flash('success', "Approval recorded ({$current}/{$required}).");
    }

    public function reject()
    {
        if ($this->loan->status !== 'applied') {
            session()->flash('error', 'Only pending loans can be rejected.');
            return;
        }

        $this->loan->update(['status' => 'rejected']);

        LoanApproval::create([
            'loan_id'   => $this->loan->id,
            'member_id' => auth()->user()->member_id,
            'remark'    => $this->approvalRemark ?: 'Rejected',
        ]);

        $this->loan->refresh();
        $this->approvalRemark = '';

        NotifyService::toMember($this->loan->member_id, new InAppNotification(
            type:    'loan.rejected',
            title:   'Loan rejected',
            message: 'Your loan application of KES ' . number_format($this->loan->amount, 2) . ' was not approved.',
            url:     route('loans.show', $this->loan),
            icon:    'feather-x-circle',
            color:   'danger',
        ));

        session()->flash('success', 'Loan rejected.');
    }

    public function disburse()
    {
        if ($this->loan->status !== 'approved') {
            session()->flash('error', 'Only approved loans can be disbursed.');
            return;
        }

        $this->loan->update([
            'status'       => 'disbursed',
            'disbursed_at' => now(),
            'due_at'       => now()->addMonths($this->loan->term_months),
        ]);

        $this->loan->refresh();

        NotifyService::toMember($this->loan->member_id, new InAppNotification(
            type:    'loan.disbursed',
            title:   'Loan disbursed',
            message: 'KES ' . number_format($this->loan->amount, 2) . ' has been disbursed. Due by ' . optional($this->loan->due_at)->format('Y-m-d') . '.',
            url:     route('loans.show', $this->loan),
            icon:    'feather-dollar-sign',
            color:   'primary',
        ));

        session()->flash('success', 'Loan has been disbursed successfully.');
    }

    public function recordRepayment()
    {
        $this->validate([
            'repaymentAmount' => 'required|numeric|min:0.01|max:' . ($this->loan->balance + 100),
            'repaymentDate'   => 'required|date',
            'repaymentMethod' => 'required|string',
        ]);

        $this->loan->repayments()->create([
            'amount'         => $this->repaymentAmount,
            'paid_at'        => $this->repaymentDate,
            'payment_method' => $this->repaymentMethod,
            'notes'          => $this->repaymentNotes,
        ]);

        $this->loan->refresh();
        $fullyRepaid = $this->loan->balance <= 0;
        if ($fullyRepaid) {
            $this->loan->update(['status' => 'repaid', 'repaid' => true]);
            $this->loan->refresh();
        }

        NotifyService::toMember($this->loan->member_id, new InAppNotification(
            type:    $fullyRepaid ? 'loan.fully_repaid' : 'loan.repayment',
            title:   $fullyRepaid ? 'Loan fully repaid' : 'Repayment recorded',
            message: 'KES ' . number_format((float) $this->repaymentAmount, 2) . ' recorded.'
                    . ($fullyRepaid ? ' Your loan is now cleared.' : ' Remaining balance: KES ' . number_format($this->loan->balance, 2) . '.'),
            url:     route('loans.show', $this->loan),
            icon:    $fullyRepaid ? 'feather-award' : 'feather-credit-card',
            color:   $fullyRepaid ? 'success' : 'info',
        ));

        $this->reset(['repaymentAmount', 'repaymentNotes']);
        $this->repaymentDate = date('Y-m-d');

        session()->flash('success', 'Repayment recorded successfully.');
    }

    public function deleteRepayment($id)
    {
        LoanRepayment::findOrFail($id)->delete();
        $this->loan->refresh();
        if ($this->loan->balance > 0 && $this->loan->status == 'repaid') {
             $this->loan->update(['status' => 'disbursed', 'repaid' => false]);
        }
        session()->flash('success', 'Repayment deleted.');
    }

    public function render()
    {
        return view('livewire.loans.show', [
            'repayments' => $this->loan->repayments()->latest('paid_at')->get(),
            'approvals'  => $this->loan->approvals()->with('member')->latest()->get(),
            'current'    => $this->loan->approvals()->count(),
            'required'   => (int) (ChamaSetting::current()->min_loan_approvals ?? 1),
        ]);
    }
}
