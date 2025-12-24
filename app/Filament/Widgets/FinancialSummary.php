<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Contribution;
use App\Models\Loan;
use App\Models\Expenditure;
use App\Models\Withdrawal;

class FinancialSummary extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // 1. Total Inflows (Contributions)
        $totalShares = Contribution::sum('shares');
        $totalWelfare = Contribution::sum('welfare');
        $totalPenalty = Contribution::sum('penalty');
        $totalMerryGoRound = Contribution::sum('merry_go_round');

        // Sum of all contributions
        $totalContributions = $totalShares + $totalWelfare + $totalPenalty + $totalMerryGoRound;

        // 2. Loan Repayments (Principal + Interest)
        // Ideally we track repayments. For now, we can use `repaid_amount` from Loans or sum of Repayments if you have a separate table.
        // Assuming Loan has `repaid_amount` which is updated.
        $totalRepaidLoans = Loan::sum('repaid_amount');

        // Total Cash In
        $totalCashIn = $totalContributions + $totalRepaidLoans;


        // 3. Outflows
        $totalDisbursedLoans = Loan::whereNotNull('disbursed_at')->sum('amount'); // Principal given out
        $totalExpenditures = Expenditure::sum('amount');
        $totalWithdrawals = Withdrawal::sum('amount');

        // Total Cash Out (Money leaving the pool)
        // Note: Disbursed loans are money leaving the 'cash at hand', but they are assets.
        // If "Financial Summary" means "Cash Flow", we subtract disbursed loans.
        // If it means "Net Worth", we count loans receivable as assets.
        // Given "withdraw money" context, the user likely wants "Cash at Hand".

        // Cash at Hand = (Contributions + Repaid Loans + Other Income) - (Loans Disbursed + Expenditures + Withdrawals)
        $totalCashOut = $totalDisbursedLoans + $totalExpenditures + $totalWithdrawals;

        $cashAtHand = $totalCashIn - $totalCashOut;

        return [
            Stat::make('Total Contributions', 'KES ' . number_format($totalContributions))
                ->description('Shares, Welfare, Penalty, MGR')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Withdrawals', 'KES ' . number_format($totalWithdrawals))
                ->description('Money withdrawn from pool')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Total Expenditures', 'KES ' . number_format($totalExpenditures))
                ->description('Expenses incurred')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Net Cash Balance', 'KES ' . number_format($cashAtHand))
                ->description('Cash available in account')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($cashAtHand >= 0 ? 'success' : 'danger'),
        ];
    }
}
