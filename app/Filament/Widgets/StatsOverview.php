<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\Member;
use App\Models\Contribution;
use App\Models\Loan;

class StatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = [
        'lg' => 4,
        'md' => 2,
        'default' => 1,
    ];
    protected function getStats(): array
    {
        $totalShares = Contribution::sum('shares');
        $totalWelfare = Contribution::sum('welfare');

        return [
            Stat::make('Members', Member::count())
                ->description('Active Chama Members')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->icon('heroicon-o-user-group'),

            Stat::make('Total Shares Contributions', 'KES ' . number_format($totalShares, 2))
                ->description('All time shares contributions')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Total Welfare Contributions', 'KES ' . number_format($totalWelfare, 2))
                ->description('All time welfare contributions')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('warning')
                ->icon('heroicon-o-credit-card'),
        ];
    }
}