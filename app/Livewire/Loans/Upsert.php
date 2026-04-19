<?php

namespace App\Livewire\Loans;

use App\Models\Loan;
use App\Models\Member;
use App\Notifications\InAppNotification;
use App\Services\NotifyService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Upsert extends Component
{
    public ?Loan $loan = null;
    
    // Form fields
    public $member_id = '';
    public $amount = 0;
    public $interest_percent = 10;
    public $term_months = 2;
    public $disbursed_at;
    public $status = 'applied';

    // Data
    public $members = [];

    protected function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0',
            'interest_percent' => 'required|numeric|min:0',
            'term_months' => 'required|integer|min:1',
            'status' => 'required|in:applied,approved,disbursed,repaid,rejected',
            'disbursed_at' => 'nullable|date|required_if:status,disbursed',
        ];
    }

    public function mount(?Loan $loan = null): void
    {
        $this->members = Member::orderBy('first_name')->get();

        if ($loan && $loan->exists) {
            $this->loan = $loan;
            $this->member_id = $loan->member_id;
            $this->amount = $loan->amount;
            $this->interest_percent = $loan->interest_percent;
            $this->term_months = $loan->term_months;
            $this->status = $loan->status;
            $this->disbursed_at = $loan->disbursed_at ? $loan->disbursed_at->format('Y-m-d') : null;
        } else {
            // Defaults
            $this->status = 'applied';
        }
    }

    public function updated(string $propertyName): void
    {
        $this->resetErrorBag($propertyName);
    }

    public function save(): void
    {
        $validated = $this->validate();
        
        // Calculate due date if disbursed_at is set
        if (!empty($validated['disbursed_at'])) {
            $disbursedDate = \Carbon\Carbon::parse($validated['disbursed_at']);
            $validated['due_at'] = $disbursedDate->addMonths($validated['term_months']);
        }

        if ($this->loan) {
            $this->loan->update($validated);
            session()->flash('success', 'Loan updated successfully.');
        } else {
            // Block if member already has an active loan
            $existing = Loan::where('member_id', $validated['member_id'])
                ->whereIn('status', ['applied', 'approved', 'disbursed'])
                ->first();

            if ($existing) {
                $this->addError('member_id', 'This member already has an active loan (#' . $existing->id . ').');
                return;
            }

            $loan = Loan::create($validated);

            // In-app notification: new loan request → admins
            $member = Member::find($loan->member_id);
            $memberName = $member ? trim($member->first_name . ' ' . $member->last_name) : 'A member';
            NotifyService::toAdmins(new InAppNotification(
                type:    'loan.requested',
                title:   'New loan request',
                message: $memberName . ' applied for a loan of KES ' . number_format($loan->amount, 2) . '.',
                url:     route('loans.show', $loan),
                icon:    'feather-file-plus',
                color:   'warning',
            ));

            // Confirmation to the member (if they have a portal user)
            NotifyService::toMember($loan->member_id, new InAppNotification(
                type:    'loan.requested.self',
                title:   'Loan application submitted',
                message: 'Your loan application of KES ' . number_format($loan->amount, 2) . ' is pending approval.',
                url:     route('loans.show', $loan),
                icon:    'feather-clock',
                color:   'info',
            ));

            session()->flash('success', 'Loan created successfully.');
        }

        $this->redirect(route('loans.index'));
    }

    public function render()
    {
        return view('livewire.loans.upsert');
    }
}
