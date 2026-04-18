<?php

namespace App\Livewire\Portal;

use App\Models\Contribution;
use App\Models\Income;
use App\Models\Loan;
use App\Models\Member;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public ?Member $member = null;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->member_id, 403);
        $this->member = Member::find($user->member_id);
        abort_unless($this->member, 404);
    }

    public function render()
    {
        $memberId = $this->member->id;

        $totalContributions = (float) Contribution::where('member_id', $memberId)
            ->sum(\DB::raw('shares + welfare + merry_go_round'));

        $activeLoan = Loan::where('member_id', $memberId)
            ->where('status', 'disbursed')
            ->latest()->first();

        $pendingLoan = Loan::where('member_id', $memberId)
            ->where('status', 'applied')
            ->latest()->first();

        $totalFines = (float) Income::where('member_id', $memberId)
            ->where('category', 'fine')->sum('amount')
            + (float) Contribution::where('member_id', $memberId)->sum('penalty');

        return view('livewire.portal.dashboard', [
            'totalContributions' => $totalContributions,
            'activeLoan'         => $activeLoan,
            'pendingLoan'        => $pendingLoan,
            'totalFines'         => $totalFines,
        ]);
    }
}
