<?php

namespace App\Livewire\Members;

use App\Models\Contribution;
use App\Models\Income;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Withdrawal;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Statement extends Component
{
    public Member $member;

    public function mount(Member $member): void
    {
        // If the user is a portal member, force them to their own record.
        $user = auth()->user();
        if ($user && $user->hasRole('member') && $user->member_id !== $member->id) {
            abort(403);
        }
        $this->member = $member;
    }

    public function render()
    {
        $memberId = $this->member->id;

        $contributions = Contribution::where('member_id', $memberId)
            ->orderByDesc('paid_at')->get();

        $loans = Loan::where('member_id', $memberId)
            ->withCount('approvals')
            ->latest()->get();

        $repayments = LoanRepayment::whereIn('loan_id', $loans->pluck('id'))
            ->orderByDesc('paid_at')->get();

        $fines = Income::where('member_id', $memberId)
            ->where('category', 'fine')
            ->orderByDesc('received_at')->get();

        $withdrawals = Withdrawal::where('member_id', $memberId)
            ->orderByDesc('withdrawn_at')->get();

        // Aggregates
        $totalShares        = (float) $contributions->sum('shares');
        $totalWelfare       = (float) $contributions->sum('welfare');
        $totalMerry         = (float) $contributions->sum('merry_go_round');
        $totalPenalties     = (float) $contributions->sum('penalty')
                              + (float) $fines->sum('amount');
        $totalContributed   = $totalShares + $totalWelfare + $totalMerry;
        $totalWithdrawn     = (float) $withdrawals->sum('amount');
        $netSavings         = $totalContributed - $totalWithdrawn;

        $activeLoansBalance = $loans->where('status', 'disbursed')->sum(fn ($l) => $l->balance);
        $totalDisbursed     = (float) $loans->whereIn('status', ['disbursed', 'repaid'])->sum('amount');

        return view('livewire.members.statement', [
            'contributions'      => $contributions,
            'loans'              => $loans,
            'repayments'         => $repayments,
            'fines'              => $fines,
            'withdrawals'        => $withdrawals,
            'totalShares'        => $totalShares,
            'totalWelfare'       => $totalWelfare,
            'totalMerry'         => $totalMerry,
            'totalPenalties'     => $totalPenalties,
            'totalContributed'   => $totalContributed,
            'totalWithdrawn'     => $totalWithdrawn,
            'netSavings'         => $netSavings,
            'activeLoansBalance' => $activeLoansBalance,
            'totalDisbursed'     => $totalDisbursed,
        ]);
    }
}
