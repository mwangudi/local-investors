<?php

namespace App\Livewire\Dashboard;

use App\Models\Contribution;
use App\Models\Expenditure;
use App\Models\Income;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Withdrawal;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Crm extends Component
{
    public $totalMembers;
    public $activeMembers;
    public $totalContributions;
    public $monthlyContributions;
    public $activeLoansCount;
    public $totalLoansCount;
    public $activeLoansAmount;
    public $outstandingBalance;
    public $totalRepaid;

    // New KPIs
    public $totalIncome;
    public $totalExpenditure;
    public $netPosition;
    public $overdueLoansCount;
    public $pendingLoansCount;
    public $cashOnHand;

    public $recentContributions;
    public $recentLoans;
    public $memberSnapshot;

    public function mount()
    {
        // Members stats
        $this->totalMembers = Member::count();
        $this->activeMembers = Member::where('is_active', true)->count();
        if ($this->activeMembers == 0) {
            $this->activeMembers = $this->totalMembers;
        }

        // Contributions stats
        $this->totalContributions = Contribution::sum('shares') + Contribution::sum('welfare') + Contribution::sum('merry_go_round');
        $this->monthlyContributions = Contribution::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('shares') + Contribution::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('welfare') + Contribution::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('merry_go_round');

        // Loans stats
        $this->activeLoansCount = Loan::where('status', 'disbursed')->count();
        $this->totalLoansCount = Loan::count();
        $this->activeLoansAmount = Loan::where('status', 'disbursed')->sum('amount');
        $this->pendingLoansCount = Loan::where('status', 'applied')->count();

        $this->totalRepaid = LoanRepayment::sum('amount');
        $this->outstandingBalance = max(0, $this->activeLoansAmount - $this->totalRepaid);

        // Overdue: disbursed loans past due date with non-zero balance
        $this->overdueLoansCount = Loan::where('status', 'disbursed')
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', now())
            ->get()
            ->filter(fn ($l) => $l->balance > 0)
            ->count();

        // Cashbook
        $this->totalIncome = (float) Income::sum('amount');
        $this->totalExpenditure = (float) Expenditure::sum('amount');
        $totalWithdrawals = (float) Withdrawal::sum('amount');

        // Cash on hand = contributions + income + loan repayments - loan disbursements - expenditure - withdrawals
        $this->cashOnHand = $this->totalContributions
            + $this->totalIncome
            + $this->totalRepaid
            - $this->activeLoansAmount
            - $this->totalExpenditure
            - $totalWithdrawals;

        $this->netPosition = $this->totalIncome - $this->totalExpenditure;

        // Recent activity
        $this->recentContributions = Contribution::with('member')->latest('paid_at')->take(5)->get();
        $this->recentLoans = Loan::with('member')->latest()->take(5)->get();

        // Member Snapshot — per-member summary
        $this->memberSnapshot = Member::where('is_active', true)
            ->with(['contributions', 'loans.repayments'])
            ->get()
            ->map(function ($member) {
                $totalContributions = $member->contributions->sum('shares')
                    + $member->contributions->sum('welfare')
                    + $member->contributions->sum('merry_go_round');
                $totalShares = $member->contributions->sum('shares');

                $activeLoans = $member->loans->where('status', 'disbursed');
                $activeLoansCount = $activeLoans->count();
                $loanBalance = $activeLoans->sum(function ($loan) {
                    $principal = $loan->amount;
                    $interest = ($loan->interest_percent / 100) * $principal;
                    $totalDue = $principal + $interest;
                    $repaid = $loan->repayments->sum('amount');
                    return max(0, $totalDue - $repaid);
                });

                return [
                    'name'               => $member->full_name,
                    'total_contributions' => $totalContributions,
                    'total_shares'        => $totalShares,
                    'active_loans'       => $activeLoansCount,
                    'loan_balance'       => $loanBalance,
                ];
            })
            ->sortByDesc('total_contributions')
            ->values();
    }

    public function render()
    {
        return view('livewire.dashboard.crm');
    }
}
