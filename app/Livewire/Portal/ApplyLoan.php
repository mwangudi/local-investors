<?php

namespace App\Livewire\Portal;

use App\Models\ChamaSetting;
use App\Models\Loan;
use App\Models\Member;
use App\Notifications\InAppNotification;
use App\Services\NotifyService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ApplyLoan extends Component
{
    public ?Member $member = null;

    public $amount;
    public $term_months;
    public $reason;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->member_id, 403);
        $this->member = Member::find($user->member_id);
        abort_unless($this->member, 404);

        // Block if member already has an active loan
        $existingLoan = Loan::where('member_id', $this->member->id)
            ->whereIn('status', ['applied', 'approved', 'disbursed'])
            ->first();

        if ($existingLoan) {
            session()->flash('error', 'You already have an active loan (Loan #' . $existingLoan->id . '). Please clear it before applying for a new one.');
            $this->redirect(route('portal.dashboard'));
            return;
        }

        $settings = ChamaSetting::current();
        $this->term_months = $settings->loan_duration_months ?? 3;
    }

    protected function rules(): array
    {
        return [
            'amount'      => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1|max:60',
            'reason'      => 'nullable|string|max:500',
        ];
    }

    public function submit(): void
    {
        $data = $this->validate();

        $settings = ChamaSetting::current();

        $loan = Loan::create([
            'member_id'        => $this->member->id,
            'amount'           => $data['amount'],
            'interest_percent' => $settings->standard_interest_percent ?? 10,
            'term_months'      => $data['term_months'],
            'status'           => 'applied',
        ]);

        NotifyService::toAdmins(new InAppNotification(
            type:    'loan.requested',
            title:   'New loan request',
            message: $this->member->full_name . ' requested KES ' . number_format($loan->amount, 2) . '.',
            url:     route('loans.show', $loan),
            icon:    'feather-file-plus',
            color:   'warning',
        ));

        session()->flash('success', 'Your loan application has been submitted and is pending approval.');
        $this->redirect(route('portal.dashboard'));
    }

    public function render()
    {
        return view('livewire.portal.apply-loan');
    }
}
