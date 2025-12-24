<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;

class FinancialReport extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Financials';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.financial-report';

    public ?array $data = [];

    public function mount()
    {
        $this->form->fill([
            'filterYear' => now()->year,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('filterYear')
                    ->label('Filter by Reporting Year')
                    ->options($this->getYears())
                    ->default(now()->year)
                    ->live()
                    ->native(false)
                    ->searchable()
                    ->selectablePlaceholder(false)
                    ->columnSpan('full'),
            ])
            ->statePath('data');
    }

    public function getYears(): array
    {
        // Generate last 5 years
        return collect(range(now()->year, now()->year - 4))
            ->mapWithKeys(fn($year) => [$year => $year])
            ->toArray();
    }

    public function getReportDataProperty()
    {
        $year = $this->data['filterYear'] ?? now()->year;

        // Contributions
        $shares = \App\Models\Contribution::whereYear('created_at', $year)->sum('shares');
        $welfare = \App\Models\Contribution::whereYear('created_at', $year)->sum('welfare');
        $penalty = \App\Models\Contribution::whereYear('created_at', $year)->sum('penalty');
        $mgr = \App\Models\Contribution::whereYear('created_at', $year)->sum('merry_go_round');

        // MGR is pass-through, so it is NOT included in account inflows
        $totalContributions = $shares + $welfare + $penalty;

        // Loans
        $loansDisbursed = \App\Models\Loan::whereYear('disbursed_at', $year)->sum('amount');
        // Assuming repayments are tracked via 'repaid_amount' update time or just checking if loan was *repaid* this year...
        // Ideally we need a Repayment model.
        // For now, let's just sum `repaid_amount` of loans updated this year (approximation) 
        // OR better: Just use total repaid amount for loans *created* this year? No, that's wrong.
        // Since we don't have a Repayment table, we can't accurately track *when* repayment happened.
        // We will default to: Sum of `repaid_amount` for ALL loans modified this year (Still inaccurate but likely best effort without Repayment model).
        // Actually, let's just show "Total Loans Repaid (All Time)" vs "This Year" is hard.
        // Let's rely on Loans created this year for "Disbused".
        // And for Repayment, maybe just sum `repaid_amount` of loans where `updated_at` is this year?
        $loansRepaid = \App\Models\Loan::whereYear('updated_at', $year)->sum('repaid_amount');

        // Expenditures
        $expenditures = \App\Models\Expenditure::whereYear('spent_at', $year)->sum('amount');

        // Withdrawals (Funds taken out to pay for expenditures)
        $withdrawals = \App\Models\Withdrawal::whereYear('withdrawn_at', $year)->sum('amount');

        // Cash Returns (Unspent funds returned to Zimele)
        $cashReturns = \App\Models\CashReturn::whereYear('returned_at', $year)->sum('amount');

        // Net Cash Flow for the Group
        // Inflow = Contributions + Repayments
        // Outflow = Disbursed + Expenditures (Actual spending)
        // Note: Cash Returns are just moving money back to bank, they don't change "Group Wealth" (Net Assets), 
        // essentially they reverse the "Withdrawal" operation if we were tracking bank balance, 
        // but for "Income/Expense" statement, they are neutral.
        // HOWEVER, if we consider "Zimele Account Balance", then Inflows should include Returns?
        // Let's stick to "Income vs Expense" for Net Cash Flow.
        $inflows = $totalContributions + $loansRepaid;
        $outflows = $loansDisbursed + $expenditures;
        $net = $inflows - $outflows;

        // Reconciliation: Cash Withdrawn vs Cash Spent vs Returned
        // Cash at Hand = Withdrawn - Spent - Returned
        $cash_balance = $withdrawals - $expenditures - $cashReturns;
        $float_target = 5000;

        return [
            'year' => $year,
            'shares' => $shares,
            'welfare' => $welfare,
            'penalty' => $penalty,
            'mgr' => $mgr,
            'total_contributions' => $totalContributions,
            'loans_disbursed' => $loansDisbursed,
            'loans_repaid' => $loansRepaid,
            'expenditures' => $expenditures,
            'withdrawals' => $withdrawals,
            'cash_returns' => $cashReturns,
            'cash_balance' => $cash_balance,
            'float_target' => $float_target,
            'inflows' => $inflows,
            'outflows' => $outflows,
            'net' => $net,
        ];
    }
}
