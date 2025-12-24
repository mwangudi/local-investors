<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Loan;

class LoanSummary extends StatsOverviewWidget
{
    protected ?string $heading = 'Loan Summary';
    protected int|string|array $columnSpan = [
        'lg' => 4,
        'md' => 2,
        'default' => 1,
    ];
    protected static ?int $sort = 3;
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Outstanding Loans',
                'KES ' . number_format(
                    Loan::where('repaid', false)
                        ->whereNotNull('disbursed_at')
                        ->sum('amount'),
                    2
                )
            )
                ->color('danger')
                ->icon('heroicon-o-exclamation-circle'),

            Stat::make(
                'Loans Fully Paid',
                'KES ' . number_format(
                    Loan::where('repaid', true)->sum('amount'),
                    2
                )
            )
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make(
                'Overdue Loans',
                Loan::where('repaid', false)
                    ->where('due_at', '<', now())
                    ->count()
            )
                ->description('Loans past the due date')
                ->color('warning')
                ->icon('heroicon-o-clock'),
        ];
    }
}