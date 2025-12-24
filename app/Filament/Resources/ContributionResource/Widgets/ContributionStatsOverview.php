<?php

namespace App\Filament\Resources\ContributionResource\Widgets;

use App\Filament\Resources\ContributionResource\Pages\ListContributions;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;

class ContributionStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListContributions::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $shares = (clone $query)->sum('shares');
        $welfare = (clone $query)->sum('welfare');
        $mgr = (clone $query)->sum('merry_go_round');
        $penalty = (clone $query)->sum('penalty');

        return [
            Stat::make('Total Shares', 'KES ' . number_format($shares, 2)),
            Stat::make('Total Welfare', 'KES ' . number_format($welfare, 2)),
            Stat::make('Total MGR', 'KES ' . number_format($mgr, 2)),
            Stat::make('Total Penalty', 'KES ' . number_format($penalty, 2)),
        ];
    }
}
