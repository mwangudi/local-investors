<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\LatestContributions;
use App\Filament\Widgets\MonthlyContributionsChart;
use App\Filament\Widgets\LoanSummary;
use App\Filament\Widgets\WelfareSharesComparisonChart;
use App\Filament\Widgets\LatestLoans;


class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            // Top Stats Row
            StatsOverview::class,

            // Second Row - Contributions + Loans
            LoanSummary::class,
            MonthlyContributionsChart::class,

            // Third Row - Comparison + Member Growth
            WelfareSharesComparisonChart::class,
            //MemberGrowthChart::class,

            // Last Row - Latest Contributions Table
            LatestLoans::class,
            LatestContributions::class,
        ];
    }
}